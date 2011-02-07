<?php
//include 'html/header.html';

require_once 'qtl_functions.php';
require_once 'db_functions.php';
require_once 'utils.php';
//measure running-time
//$start = tic();
$qtldb = connectToQtlDB();
$compara = connectToCompara(3306);

//get is not working for now...
$loci_str = 'loci';
if(isset($_GET[$loci_str])) {
	$loci = $_GET[$loci_str];
}else{


	$speciesArray = array("Rattus norvegicus","Mus musculus");
	$genome_db_ids = array(57,3);
	$species2genome_db_ids = array("Rattus norvegicus" => 3,"Mus musculus"=>57);
	$genome_ids2dbs = array(57 => 'eqtl_rostock_eae', 3 =>'eqtl_stockholm_eae_logplier');
	$num_species = sizeof($speciesArray);
	$species_str = 'species';
	$reg_str = 'region';
	$chr_str = 'chr';
	$args = $_GET;

	$species1 = "Mus musculus";
	$species2 = "Rattus norvegicus";
	$chr1 = "2";
	$chr2 = "2";
	$region1 = "80-110";
	$region2 = "137-137.8";
	
	//	$region1 = "10-110";
	//	$region2 = "20-137.8";

	$pos = strpos($region1, "-");
	$start1 = substr($region1,0,$pos);
	$end1 = substr($region1,$pos+1);
	$pos = strpos($region2, "-");
	$start2 = substr($region2,0,$pos);
	$end2 = substr($region2,$pos+1);

	//  fetch loci
	$genome_id1 = $species2genome_db_ids[$species1];
	$db1 = $genome_ids2dbs[$genome_id1];
	$sql = 'select Name from '.$db1.'.Locus
where Chr = '.$chr1.' 
and cMorgan >= '.$start1.' 
and cMorgan <= '.$end1.';';
	$loci_ex1 = get_only_loci_from_sql($sql, $qtldb);

	$genome_id2 = $species2genome_db_ids[$species2];
	$db2 = $genome_ids2dbs[$genome_id2];
	$sql = 'select Name from '.$db2.'.Locus
where Chr = '.$chr2.' 
and cMorgan >= '.$start2.' 
and cMorgan <= '.$end2.';';
	$loci_ex2 = get_only_loci_from_sql($sql, $qtldb);

	//
	//	// mus whole chromosom
	//	$ex1 =  get_loci_from_sql('eqtl_rostock_eae', $qtldb, 'userinterval', $chromosomNo, $confidenceInterval, 80, 110);
	//	// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $maxEx1 = index -> (locus,groupNr)
	//	list($groups1, $mapEx1) = $ex1;
	//	// generates an arrays with index -> locinames
	//	$loci_ex1 = array_map('current',$mapEx1);
	//	// generates an assoc. arrays with locinames -> groupnr
	//	foreach ($mapEx1 as $arrayValue) {
	//		$groupLoci1[$arrayValue[0]]= $arrayValue[1];
	//	}
	//	// rat whole chromosom
	//	$ex2 =  get_loci_from_sql('eqtl_stockholm_eae_logplier', $qtldb, 'userinterval', $chromosomNo, $confidenceInterval, 137	,138);
	//	// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $maxEx2 = index -> (locus,groupNr)
	//	list($groups2, $mapEx2) = $ex2;
	//	// generates an arrays with index -> locinames
	//	$loci_ex2 = array_map('current', $mapEx2);
	//	// generates an assoc. arrays with locinames -> groupnr
	//	foreach ($mapEx2 as $arrayValue) {
	//		$groupLoci2[$arrayValue[0]]= $arrayValue[1];
	//	}
}



// display arguments:
//include 'display_args.php';
// SYNTENY
$species_names = array("Mus musculus","Rattus norvegicus");
$genome_db_ids = array(57,3);
$dbs = array('eqtl_rostock_eae','eqtl_stockholm_eae_logplier');
$synteny_ex12ex2 = getSynteny($qtldb, $compara, $loci_ex1, $loci_ex2, $species_names, $genome_db_ids,$dbs);
// Loci to genes
//"Mus musculus"
useDB('eqtl_rostock_eae', $qtldb);
//loci2stable_
$loci2stable_ids_ex1 = loci2stable_ids($loci_ex1,$qtldb);

$unique_ens_ids_ex1 = get_unique_vals_from_2d_array($loci2stable_ids_ex1[0]);
//"Rat"
useDB('eqtl_stockholm_eae_logplier', $qtldb);
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
//warn($traits12traits2);
// display -----------------------
include 'utils/write_all_table.php';

$cols = 227; // width of left offset
$rows = 138; // heigth of above offset
$scroll_width = 16;

echo '<?xml version="1.0" encoding="iso-8859-1"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<script type="text/javascript" src="js/table-scroll.js"></script>
</head>
<frameset onload="init()" framespacing="0" frameborder="0"
  cols="<?php echo $cols;?>,*">
  <!-- left -->
  <frameset id="links"
    rows="<?php echo $rows;?>,*,<?php echo $scroll_width;?>">
    <!-- above left -->
    <frame marginheight="0" marginwidth="10" scrolling="no" name="obLi"
      src="html/table_all.html" />
      <!-- left column -->
    <frame marginheight="0" marginwidth="10" scrolling="no" name="untLi"
      src="html/table_all.html" />
      <!-- scrollbar extension below -->
    <frame marginheight="0" scrolling="no" src="html/leer.html" />
  </frameset>
  
  <!-- right -->
  <frameset rows="<?php echo $rows;?>,*">
    <!-- above -->
    <frameset id="oben" cols="*,<?php echo $scroll_width;?>">
      <!-- above column -->
      <frame marginheight="0" marginwidth="10" scrolling="no"
        name="obRe" src="html/table_all.html" />
        <!-- scrollbar extension right -->
      <frame scrolling="no" src="html/leer.html" />
    </frameset>

    <!-- content -->
    <frame marginheight="0" marginwidth="10" scrolling="auto"
      name="untRe" src="html/table_all.html" />
  </frameset>
</frameset>
</html>
