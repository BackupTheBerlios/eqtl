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


// $reg_str = 'regions';
// $chr2reg = array();
$species2genome_db_ids = array($experiment1['species'] => $experiment1['genome_db_id'],$experiment2['species']=>$experiment2['genome_db_id']);
$genome_ids2dbs = array($experiment2['genome_db_id'] => $experiment2['db_name'], $experiment1['genome_db_id'] =>$experiment1['db_name']);


//load informations (database and so on...)
$genome_db_ids = array($experiment1['genome_db_id'],$experiment2['genome_db_id']);
$species_names = array($experiment1['species'],$experiment2['species']);
$database1 = $experiment1['db_name'];
$database2 = $experiment2['db_name'];
$dbs = array($database1,$database2);

// needed for homology
$species1 = $experiment1['species'];
$species2 = $experiment2['species'];
//  genome db ids
$genome_id1 = $species2genome_db_ids[$species1];
$genome_id2 = $species2genome_db_ids[$species2];

// fetch loci and groups
$chrs = getChromosomsAndLengths($compara,$experiment1['genome_db_id']);
// additional filtering
useDB($genome_ids2dbs[$experiment1['genome_db_id']], $experiment1['connection']);
$chrs = filter_chromos($experiment1['connection'], $chrs);
$chromosomsEx1 = array_keys($chrs);//getChromosoms($compara, $experiment1['genome_db_id']);

//initialize array for mapping groupnumbers to regions
$group2region = array();
$ex1 =  get_loci_from_sql($database1, $experiment1['connection'], 'wholeGenome', $chromosomsEx1, $confidence_int, $group2region);
// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr') $mapEx1 = index -> (locus,groupNr)
list($groups1, $mapEx1) = $ex1;
// generates an arrays with index -> locinames
$loci_ex1 = array_map('current',$mapEx1);
$groupnr_ex1 = array_map('next',$mapEx1);
$loci2group1 = array_combine($loci_ex1, $groupnr_ex1);

$chrs = getChromosomsAndLengths($compara,$experiment2['genome_db_id']);
// additional filtering
useDB($genome_ids2dbs[$experiment2['genome_db_id']], $experiment1['connection']);
$chrs = filter_chromos($experiment1['connection'], $chrs);
$chromosomsEx2 = array_keys($chrs);//getChromosoms($compara, $experiment1['genome_db_id']);
//initialize array for mapping groupnumbers to regions
$group2region2 = array();
$ex2 =  get_loci_from_sql($database2, $experiment2['connection'], 'wholeGenome', $chromosomsEx2, $confidence_int, $group2region2);

// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $mapEx2 = index -> (locus,groupNr)
list($groups2, $mapEx2) = $ex2;
// generates an arrays with index -> locinames
$loci_ex2 = array_map('current',$mapEx2);


// SYNTENY

$groupSynteny_ex12ex2 = getSyntenyGroups($experiment1['connection'],$compara,$groups1,$groups2,$species_names,$genome_db_ids,$dbs);

// homo

useDB($database1,$experiment1['connection']);
$loci2stable_ids_ex1 = loci2stable_ids($loci_ex1,$experiment1['connection']);
$n_qtls1 = 0;
$unique_ens_ids_ex1 = get_unique_vals_from_2d_array($loci2stable_ids_ex1[0], $n_qtls1);


useDB($database2,$experiment2['connection']);
$loci2stable_ids_ex2 = loci2stable_ids($loci_ex2,$experiment2['connection']);
$n_qtls2 = 0;
$unique_ens_ids_ex2 = get_unique_vals_from_2d_array($loci2stable_ids_ex2[0],$n_qtls2);



// HOMOLOGY => do it on the fewer genes
$n_ens_ids_ex1 = sizeof($unique_ens_ids_ex1);
$n_ens_ids_ex2 = sizeof($unique_ens_ids_ex2);
$traits12traits2 = array();
//$cnt_homo = array();
if($n_ens_ids_ex1 < $n_ens_ids_ex2){// homology on experiment 1
	$homology_ex1 = get_homologue_ens_ids($compara,$unique_ens_ids_ex1,$genome_id2);
	//intersection
	foreach ($homology_ex1 as $unique_id_ex1 => $corr_homologue_ens_ids_ex2) {
		$intersect = array_intersect(array_keys($corr_homologue_ens_ids_ex2),
		$unique_ens_ids_ex2);
		foreach ($intersect as $id_ex2){
			$traits12traits2[$unique_id_ex1][$id_ex2] = $corr_homologue_ens_ids_ex2[$id_ex2];
			//$cnt_homo[$corr_homologue_ens_ids_ex2[$id_ex2]]++;
		}
	}
}else{
	$homology_ex2 = get_homologue_ens_ids($compara,$unique_ens_ids_ex2,$genome_id1);
	//intersection

	foreach ($unique_ens_ids_ex1 as $id_ex1){
		$traits12traits2[$id_ex1] = array();
	}
	foreach ($homology_ex2 as $unique_id_ex2 => $corr_homologue_ens_ids_ex1) {
		$intersect = array_intersect(array_keys($corr_homologue_ens_ids_ex1), $unique_ens_ids_ex1);
		foreach ($intersect as $id_ex1){
			$traits12traits2[$id_ex1][$unique_id_ex2] = $corr_homologue_ens_ids_ex1[$id_ex1];
			//$cnt_homo[$corr_homologue_ens_ids_ex1[$id_ex1]]++;
		}
	}
}
$hom = array('between_species_paralog', 'ortholog_one2one', 'ortholog_many2many', 'ortholog_one2many');


// cnt homologue QTLs
$cnt_hom1 = array_combine($hom, array_fill(0, 4, 0));

// REVERSE lookup
/*$traits22traits1 = array_combine($unique_ens_ids_ex2, array_fill(0, $n_ens_ids_ex2, array()));
 foreach ($traits12traits2 as $trait1 => $traits2){
 foreach ($traits2 as $trait2 => $homotype) {
 $traits22traits1[$trait2][$trait1] = $homotype;
 }
 }*/

$qtl_sh1 = 0;
$qtlsh1 = 0;
$qtl_s1 = 0;
$qtl_h1 = 0;
$qtl_n1 = 0;


foreach ($loci2stable_ids_ex1 as $locus1 => $stables1) {
	if(empty($groupSynteny_ex12ex2[$loci2group1[$locus1]])){
		// not syntenic
		foreach ($stables1 as $stable1) {
			if (empty($traits12traits2[$stable1])) {
				// not homologue
				$qtl_n1++;
			}else{
				$qtl_h1++;
			}
		}
	}else{
		// syntenic
		foreach ($stables1 as $stable1) {
			if (empty($traits12traits2[$stable1])) {
				// not homologue
				$qtl_s1++;
			}else{
				$traits2 = array_keys($traits12traits2[$stable1]);
				$done = false;
				$syngroups2 = $groupSynteny_ex12ex2[$loci2group1[$locus1]];
				foreach ($syngroups2 as $syngroup2) {
					if ($done) {
						break;
					}
					$synloci2 = $groups2[$syngroup2]['loci'];
					foreach ($synloci2 as $synlocus2) {
						$intersect = array_intersect($loci2stable_ids_ex2[0][$synlocus2], $traits2);
						if (!empty($intersect)) {
							$done = true;
							$qtl_sh1++;
							break;
						}
					}
				}
				if(!$done){
					$qtlsh1++;
				}
			}
		}
	}
}

include 'html/footer.html';
?>
