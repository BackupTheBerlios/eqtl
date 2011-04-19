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

 $syn_groups2 = get_unique_vals_from_2d_array($groupSynteny_ex12ex2);
 $groupnrs2 = array_keys($groups2);
 $non_syn2 = array_diff($groupnrs2, $syn_groups2);

 /*
 $cnt_syn2 = 0;
 foreach ($non_syn2 as $group2nr) {
 $cnt_syn2 += count($groups1[$group2nr]['loci']);
 }
 echo $cnt_syn2;
 /*$cnt_syn1 = 0;
 foreach ($groupSynteny_ex12ex2 as $groupnr1 => $syn_group2) {
 if (empty($syn_group2)){
 warn(count($groups1[$groupnr1]['loci']));
 }
 }*/
//echo count($groups2).'  '.count(get_unique_vals_from_2d_array($groupSynteny_ex12ex2));
//echo count($groups1).'  '.$cnt_syn1;


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
$cnt_non1 = 0;
foreach ($loci2stable_ids_ex1[0] as $ens_ids1) {
	foreach ($ens_ids1 as $ens_id1) {
		if (!empty($traits12traits2[$ens_id1])) {
			$cnt_hom1[current($traits12traits2[$ens_id1])]++;
		}else{
			$cnt_non1++;
		}
	}
}

// REVERSE lookup
$traits22traits1 = array_combine($unique_ens_ids_ex2, array_fill(0, $n_ens_ids_ex2, array()));
foreach ($traits12traits2 as $trait1 => $traits2){
	foreach ($traits2 as $trait2 => $homotype) {
		$traits22traits1[$trait2][$trait1] = $homotype;
	}
}


// cnt homologue QTLs
$cnt_hom2 = array_combine($hom, array_fill(0, 4, 0));
$cnt_non2 = 0;
foreach ($loci2stable_ids_ex2[0] as $ens_ids2) {
	foreach ($ens_ids2 as $ens_id2) {
		if (!empty($traits22traits1[$ens_id2])) {
			$cnt_hom2[current($traits22traits1[$ens_id2])]++;
		}else {
			$cnt_non2++;
		}
	}
}
warn($cnt_hom1);
warn($cnt_hom2);
echo <<<END

<p> 
ex. 1:  homo eQTLs: $cnt_hom1 non: $cnt_non1<br>
ex. 2:  homo eQTLs : $cnt_hom2 non: $cnt_non2</p>

END;
exit();

/*
 $cnt_all_homo = 0;
 foreach ($traits12traits2 as $trait2traits2){
 if(!empty($intersect)){
 $cnt_all_homo += count($intersect);
 }
 }*/
/*
 echo <<<END
 <p>
 Ratte: 29215 Genes on chip; eQTLs: $n_ens_ids_ex1<br>
 Maus: 1031 Genes on chip; eQTLs : $n_ens_ids_ex2</p>

 END;
 print_r($cnt_homo);
 */

$qtl_sh1 = array();
$qtl_s1 = array();
$qtl_h1 = array();
$qtl_n1 = array();

$qtl_sh2 = array();
$qtl_s2 = array();
$qtl_h2 = array();
$qtl_n2 = array();

foreach ($groupSynteny_ex12ex2 as $group1 => $syn_group2){
	$loci1 = $groups1[$group1]['loci'];
	foreach ($syn_group2 as $group2){
		$loci2 = $groups2[$group2]['loci'];
		foreach ($loci1 as $locus1){
			foreach ($loci2 as $locus2){
				
				// LOCUS 1
				$traits1 = $loci2stable_ids_ex1[0][$locus1];
				foreach ($traits1 as $trait1){
					$intersect = array_intersect(array_keys($traits12traits2[$trait1]),$loci2stable_ids_ex2[0][$locus2]);
					if(!empty($intersect)){
						$trait2 = current($intersect);
						$qtl_sh1[$locus1][$trait1] = $traits12traits2[$trait1][$trait2];
						$qtl_sh2[$locus2][$trait2] = $traits12traits2[$trait1][$trait2];
					} else {
						$qtl_s1[$locus1][$trait1] = true;
					}
				}
				
				// LOCUS 2
				$traits2 = $loci2stable_ids_ex2[0][$locus2];
				foreach ($traits2 as $trait2){
					$intersect = array_intersect(array_keys($traits22traits1[$trait2]),$loci2stable_ids_ex1[0][$locus1]);
					if(!empty($intersect)){
						$trait1 = current($intersect);
						$qtl_sh1[$locus1][$trait1] = $traits12traits2[$trait1][$trait2];
						$qtl_sh2[$locus2][$trait2] = $traits12traits2[$trait1][$trait2];
					} else {
						$qtl_s2[$locus2][$trait2] = true;
					}
				}
				
			}
		}

	}
}

function count_syn_homos($qtl_sh) {
	$cnt_homo = array();
	foreach ($qtl_sh as $traits) {
		foreach ($traits as $homo_val) {
			if (isset($cnt_homo[$homo_val])){
				$cnt_homo[$homo_val]++;
			}else{
				$cnt_homo[$homo_val] = 0;
			}
		};
	};
	print_r($cnt_homo);
	echo "<br>";
	echo "<br>";
}

count_syn_homos($qtl_sh1);
echo "ex 2";
count_syn_homos($qtl_sh2);


$n_loci_ex1 = count($loci_ex1);
$n_loci_ex2 = count($loci_ex2);
// DISPLAY
echo <<<END
<p> 
ex. 2: $projects[0]<br>
ex. 2: $projects[1]</p>

<p> 
ex. 1: 29215 Genes on chip; eQTLs: $n_ens_ids_ex1<br>
ex. 2: 1031 Genes on chip; eQTLs : $n_ens_ids_ex2</p>

<p> 
all homologies: $cnt_all_homo </p>

END;

/*print_r($qtl_sh1);
 echo "<br><br>";
 print_r($qtl_sh1);

 /*
 function cnt_locus_per_chromo($groups, $chromosomsEx) {
 $chr2n_loci = array_combine($chromosomsEx, array_fill(0, count($chromosomsEx), 0));
 foreach ($groups as $group){
 $chr2n_loci[$group['Chr']] += count($group['loci']);
 }

 echo "<table border=\"1\"><tr><td>";
 echo implode("</td><td>", array_keys($chr2n_loci));
 echo "</td></tr><tr><td>";
 echo implode("</td><td>", array_values($chr2n_loci));
 echo "</td></tr></table>";
 }
 cnt_locus_per_chromo($groups1,$chromosomsEx1);
 echo '<br> Ex 2: <br>';
 cnt_locus_per_chromo($groups2,$chromosomsEx2);
 */
include 'html/footer.html';
?>
