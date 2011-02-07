<?php
include '../html/header.html';

require_once '../qtl_functions.php';
require_once '../db_functions.php';
require_once '../utils.php';
//measure running-time
$start = tic();
$qtldb = connectToQtlDB();
$compara = connectToCompara(3306);

//get is not working for now...
$loci_str = 'loci';
if(isset($_GET[$loci_str])) {
	$loci = $_GET[$loci_str];
//}else{
//
//	// mus
//	$loci_ex1 =  get_loci_from_sql('SELECT distinct locus FROM `eqtl_rostock_eae`.`qtl`
//		where Chromosome = "2";', $qtldb);
//	//$loci_ex1 = array('c2.loc2');
//	// rat
//	$loci_ex2 =  get_loci_from_sql('SELECT distinct locus FROM `eqtl_stockholm_eae_logplier`.`qtl`
//		where Chromosome = "2" limit 5,25;', $qtldb);
//	//$loci_ex2 = array('c2.loc117');
//}
}else{

	//TODO: delete hardcoded entries (only for debugging)
	//chromosom number
	$chromosomNo = 2;
	//userinterval (in cMorgan)
	$intervalStart = 10.0;
	$intervalEnd = 70.0;
	//search in this interval around the locus (in cMorgan)
	$confidenceInterval = 3;

	// mus whole chromosom
	$ex1 =  get_loci_from_sql('eqtl_rostock_eae', $qtldb, 'wholeChromosom', $chromosomNo, $confidenceInterval);
	// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $maxEx1 = index -> (locus,groupNr)
	list($groups1, $mapEx1) = $ex1;
	// generates an arrays with index -> locinames
	$loci_ex1 = array_map('current',$mapEx1);
	// generates an assoc. arrays with locinames -> groupnr
	foreach ($mapEx1 as $arrayValue) {
		$groupLoci1[$arrayValue[0]]= $arrayValue[1];
	}
	// rat whole chromosom
	$ex2 =  get_loci_from_sql('eqtl_stockholm_eae_logplier', $qtldb, 'wholeChromosom', $chromosomNo, $confidenceInterval);
	// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $maxEx2 = index -> (locus,groupNr)
	list($groups2, $mapEx2) = $ex2;
	// generates an arrays with index -> locinames
	$loci_ex2 = array_map('current',$mapEx2);
	// generates an assoc. arrays with locinames -> groupnr
	foreach ($mapEx2 as $arrayValue) {
		$groupLoci2[$arrayValue[0]]= $arrayValue[1];
	}
}
	

// display arguments:
include '../display_args.php';
// SYNTENY
$species_names = array("Mus musculus","Rattus norvegicus");
$genome_db_ids = array(57,3);
$dbs = array('eqtl_rostock_eae','eqtl_stockholm_eae_logplier');
$synteny_ex12ex2 = getSynteny($qtldb,$compara,$loci_ex1,$loci_ex2,$species_names,$genome_db_ids,$dbs);
// Loci to genes
//"Mus musculus"
useDB('eqtl_rostock_eae',$qtldb);
//loci2stable_
$loci2stable_ids_ex1 = loci2stable_ids($loci_ex1,$qtldb);

$unique_ens_ids_ex1 = get_unique_vals_from_2d_array($loci2stable_ids_ex1[0]);
//"Rat"
useDB('eqtl_stockholm_eae_logplier',$qtldb);
$loci2stable_ids_ex2 = loci2stable_ids($loci_ex2,$qtldb);

$unique_ens_ids_ex2 = get_unique_vals_from_2d_array($loci2stable_ids_ex2[0]);

// HOMOLOGY => do it on the fewer
$n_loci_ex1 = sizeof($unique_ens_ids_ex1);
$n_loci_ex2 = sizeof($unique_ens_ids_ex2);
$traits12traits2 = array();
if($n_loci_ex1 < $n_loci_ex2){
	$homology_ex1 = get_homologue_ens_ids($compara,$unique_ens_ids_ex1,$genome_db_ids[1]);
	//intersection
	foreach ($homology_ex1 as $unique_id_ex1 => $corr_homologue_ens_ids_ex1) {
		$traits12traits2[$unique_id_ex1] = array_intersect($corr_homologue_ens_ids_ex1,
		$unique_ens_ids_ex2);
	}
}else{
	$homology_ex2 = get_homologue_ens_ids($compara,$unique_ens_ids_ex2,$genome_db_ids[0]);
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
warn($traits12traits2);
// display -----------------------
include 'display_table.php';
include '../html/footer.html';
toc($start,'everything');
?>