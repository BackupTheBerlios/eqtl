<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 analysis.php -

 =head1 SYNOPSIS

 =head1 DESCRIPTION

 =head1 AUTHOR

 Michael Brehler <brehler@informatik.uni-luebeck.de>,
 Georg Zeplin <zeplin@informatik.uni-luebeck.de>,

 =head1 COPYRIGHT

 University of LE<uuml>beck, Germany, 2011

 =cut

 ENDOFDOCUMENTATION
 */
include 'html/header.html';

require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();

$compara = connectToCompara(3306);

// args
$projects = array('Ratte', 'Maus');
$confidence_int = 1;


connectToQtlDBs($projects);

$experiment1 = $compara_array[$projects[0]];
$experiment2 = $compara_array[$projects[1]];


$reg_str = 'regions';
$chr2reg = array();
$species2genome_db_ids = array($experiment1['species'] => $experiment1['genome_db_id'],$experiment2['species']=>$experiment2['genome_db_id']);
$genome_ids2dbs = array($experiment2['genome_db_id'] => $experiment2['db_name'], $experiment1['genome_db_id'] =>$experiment1['db_name']);


//initialize array for mapping groupnumbers to regions
$group2region = array();
$group2region2 = array();

//load informations (database and so on...)
$genome_db_ids = array($experiment1['genome_db_id'],$experiment2['genome_db_id']);
$species_names = array($experiment1['species'],$experiment2['species']);
$database1 = $experiment1['db_name'];
$database2 = $experiment2['db_name'];
$dbs = array($database1,$database2);

// needed for homology
$species1 = $experiment1['species'];
$species2 = $experiment2['species'];
//  dbs
$genome_id1 = $species2genome_db_ids[$species1];
$db1 = $database1;

$genome_id2 = $species2genome_db_ids[$species2];
$db2 = $database2;


// fetch loci and groups
$chrs = getChromosomsAndLengths($compara,$experiment1['genome_db_id']);
// additional filtering
useDB($genome_ids2dbs[$experiment1['genome_db_id']], $experiment1['connection']);
$chrs = filter_chromos($experiment1['connection'], $chrs);
$chromosomsEx1 = array_keys($chrs);//getChromosoms($compara, $experiment1['genome_db_id']);

$ex1 =  get_loci_from_sql($database1, $experiment1['connection'], 'wholeGenome', $chromosomsEx1, $confidence_int, $group2region);
// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr') $mapEx1 = index -> (locus,groupNr)
list($groups1, $mapEx1) = $ex1;
// generates an arrays with index -> locinames
$loci_ex1 = array_map('current',$mapEx1);


$chrs = getChromosomsAndLengths($compara,$experiment2['genome_db_id']);
// additional filtering
useDB($genome_ids2dbs[$experiment2['genome_db_id']], $experiment1['connection']);
$chrs = filter_chromos($experiment1['connection'], $chrs);
$chromosomsEx2 = array_keys($chrs);//getChromosoms($compara, $experiment1['genome_db_id']);
$ex2 =  get_loci_from_sql($database2, $experiment2['connection'], 'wholeGenome', $chromosomsEx2, $confidence_int, $group2region2);

// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $mapEx2 = index -> (locus,groupNr)
list($groups2, $mapEx2) = $ex2;
// generates an arrays with index -> locinames
$loci_ex2 = array_map('current',$mapEx2);


// SYNTENY
$groupSynteny_ex12ex2 = getSyntenyGroups($experiment1['connection'],$compara,$groups1,$groups2,$species_names,$genome_db_ids,$dbs);


// homo

useDB($db1,$experiment1['connection']);
$loci2stable_ids_ex1 = loci2stable_ids($loci_ex1,$experiment1['connection']);
$unique_ens_ids_ex1 = get_unique_vals_from_2d_array($loci2stable_ids_ex1[0]);


useDB($db2,$experiment2['connection']);
$loci2stable_ids_ex2 = loci2stable_ids($loci_ex2,$experiment2['connection']);
$unique_ens_ids_ex2 = get_unique_vals_from_2d_array($loci2stable_ids_ex2[0]);


// HOMOLOGY => do it on the fewer genes
$n_ens_ids_ex1 = sizeof($unique_ens_ids_ex1);
$n_ens_ids_ex2 = sizeof($unique_ens_ids_ex2);
$traits12traits2 = array();
if($n_ens_ids_ex1 < $n_ens_ids_ex2){
	$homology_ex1 = get_homologue_ens_ids($compara, $unique_ens_ids_ex1, $genome_id2);
	//intersection
	foreach ($homology_ex1 as $unique_id_ex1 => $corr_homologue_ens_ids_ex1) {
		$traits12traits2[$unique_id_ex1] = array_intersect($corr_homologue_ens_ids_ex1,
		$unique_ens_ids_ex2);
	}
}else{
	$homology_ex2 = get_homologue_ens_ids($compara, $unique_ens_ids_ex2, $genome_id1);
	//intersection

	foreach ($unique_ens_ids_ex1 as $id_ex1){
		$traits12traits2[$id_ex1] = array();
	}
	foreach ($homology_ex2 as $unique_id_ex2 => $corr_homologue_ens_ids_ex2) {
		$intersect = array_intersect($corr_homologue_ens_ids_ex2, $unique_ens_ids_ex1);
		foreach ($intersect as $id_ex1){
			$traits12traits2[$id_ex1][] = $unique_id_ex2;
		}
	}
}


foreach ($groupSynteny_ex12ex2 as $group1 => $syn_group2){
	$loci1 = $groups1[$group1]['loci'];

	foreach ($syn_group2 as $group2){
		$loci2 = $groups2[$group2]['loci'];

		foreach ($loci1 as $locus1){

			foreach ($loci2 as $locus2){

				$traits1 = $loci2stable_ids_ex1[0][$locus1];
				foreach ($traits1 as $trait1){
					$intersect = array_intersect($traits12traits2[$trait1],$loci2stable_ids_ex2[0][$locus2]);
					if(!empty($intersect)){
						echo '<br>Rat: '.$locus1.': '.$trait1.' , Maus: '.$locus2;
						warn($intersect);
					}
				}
			}
		}
	}
}

$n_loci_ex2 = count($loci_ex2);
$n_loci_ex2 = count($loci_ex2);
// DISPLAY
echo <<<END
<p> 
Affected traits ex. 1: $n_ens_ids_ex1<br>
Affected traits ex. 2: $n_ens_ids_ex2</p>
<p> 
Affected traits ex. 1: $n_ens_ids_ex1<br>
Affected traits ex. 2: $n_ens_ids_ex2</p>

END;
include 'html/footer.html';
?>
