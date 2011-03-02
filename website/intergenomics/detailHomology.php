<?php
//include 'html/header.html';
require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';

// databases:
$qtldb = connectToQtlDB();
$compara = connectToCompara(3306);

# supported target species:
# rat: stockholm "Rattus norvegicus"
# mus: rostock "Mus musculus"

$speciesArray = array("Rattus norvegicus","Mus musculus");
$genome_db_ids = array(57,3);
$species2genome_db_ids = array("Rattus norvegicus" => 3,"Mus musculus"=>57);
$genome_ids2dbs = array(57 => 'eqtl_rostock_eae', 3 =>'eqtl_stockholm_eae_logplier');
$num_species = sizeof($speciesArray);
$species_str = 'species';
$region_str = 'region';
$args = $_GET;

function getReg($str, &$chr,&$start,&$end) {
	$pos = strpos($str, ":");
	$chr = substr($str,0,$pos);
	$reg = substr($str,$pos+1);
	$pos = strpos($reg, "-",1);
	$start = substr($reg,0,$pos);
	$end = substr($reg,$pos+1);
}
if(!isset($args[$species_str.'1'])) {// no species selected
	$species1 = "Mus musculus";
	$species2 = "Rattus norvegicus";
	$region1 = "2:100-110";
	$region2 = "2:137-137.8";
	// header() TODO add refer to itself so one sees the arguments
}else{
	$species1 = $args[$species_str.'1'];
	$species2 = $args[$species_str.'2'];
	$region1 = $args[$region_str.'1'];
	$region2 = $args[$region_str.'2'];
}
$start1 = $end1 = $chr1 = $start2 = $end2 = $chr2 = 0;
getReg($region1,$chr1,$start1,$end1);
getReg($region2,$chr2,$start2,$end2);
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
// Loci to genes
useDB($db1,$qtldb);
$loci2stable_ids_ex1 = loci2stable_ids($loci_ex1,$qtldb);
$unique_ens_ids_ex1 = get_unique_vals_from_2d_array($loci2stable_ids_ex1[0]);

useDB($db2,$qtldb);
$loci2stable_ids_ex2 = loci2stable_ids($loci_ex2,$qtldb);
$unique_ens_ids_ex2 = get_unique_vals_from_2d_array($loci2stable_ids_ex2[0]);
//exit('Exit: Debbuging in compara.php!');

// HOMOLOGY => do it on the fewer genes
$n_ens_ids_ex1 = sizeof($unique_ens_ids_ex1);
$n_ens_ids_ex2 = sizeof($unique_ens_ids_ex2);
$traits12traits2 = array();
if($n_ens_ids_ex1 < $n_ens_ids_ex2){
	$homology_ex1 = get_homologue_ens_ids($compara,$unique_ens_ids_ex1,$genome_id1);
	//intersection
	foreach ($homology_ex1 as $unique_id_ex1 => $corr_homologue_ens_ids_ex1) {
		$traits12traits2[$unique_id_ex1] = array_intersect($corr_homologue_ens_ids_ex1,
		$unique_ens_ids_ex2);
	}
}else{
	$homology_ex2 = get_homologue_ens_ids($compara,$unique_ens_ids_ex2,$genome_id2);
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
$cols = 227; // width of left offset
$rows = 138; // heigth of above offset

include 'utils/write_detail_table.php';

$scroll_width = 16;

echo '<?xml version="1.0" encoding="iso-8859-1"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<script type="text/javascript" src="js/table-scroll.php?cols=<?php echo $cols.'&rows='.$rows;?>"></script>
</head>
<frameset onload="init()" framespacing="0" frameborder="0"
  cols="<?php echo $cols;?>,*">
  <!-- left -->
  <frameset id="links"
    rows="<?php echo $rows;?>,*,<?php echo $scroll_width;?>">
    <!-- above left -->
    <frame marginheight="0" marginwidth="10" scrolling="no" name="obLi"
      src="html/table.html" />
      <!-- left column -->
    <frame marginheight="0" marginwidth="10" scrolling="no" name="untLi"
      src="html/table.html" />
      <!-- scrollbar extension below -->
    <frame marginheight="0" scrolling="no" src="html/leer.html" />
  </frameset>
  
  <!-- right -->
  <frameset rows="<?php echo $rows;?>,*">
    <!-- above -->
    <frameset id="oben" cols="*,<?php echo $scroll_width;?>">
      <!-- above column -->
      <frame marginheight="0" marginwidth="10" scrolling="no"
        name="obRe" src="html/table.html" />
        <!-- scrollbar extension right -->
      <frame scrolling="no" src="html/leer.html" />
    </frameset>

    <!-- content -->
    <frame marginheight="0" marginwidth="10" scrolling="auto"
      name="untRe" src="html/table.html" />
  </frameset>
</frameset>
</html>