<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 detailHomology.php -

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

//include 'html/header.html';
require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();

// databases:

$args = $_GET;

$proj_str = 'projects';
$region_str = 'region';
$hide_str = 'hide';

if(isset($args[$proj_str])&&(count($args[$proj_str])==2)){
	connectToQtlDBs($args[$proj_str]);
}else{
	fatal_error('No projects found or wrong number of projects!');
}

$compara = connectToCompara(3306);
$proj1 = $args[$proj_str][0];
$proj2 = $args[$proj_str][1];
$experiment1 = $compara_array[$proj1];
$experiment2 = $compara_array[$proj2];

$species1 = $experiment1['species'];
$species2 = $experiment2['species'];
$genome_db_ids = array($experiment1['genome_db_id'],$experiment2['genome_db_id']);
$speciesArray = array($species1,$species2);
$species2genome_db_ids = array($species1 => $experiment1['genome_db_id'],$species2=>$experiment2['genome_db_id']);
$genome_ids2dbs = array($experiment2['genome_db_id'] => $experiment2['db_name'], $experiment1['genome_db_id'] =>$experiment1['db_name']);
$num_species = sizeof($speciesArray);


function getReg($str, &$chr,&$start,&$end) {
	$pos = strpos($str, ":");
	$chr = substr($str,0,$pos);
	$reg = substr($str,$pos+1);
	$pos = strpos($reg, "-",1);
	$start = substr($reg,0,$pos);
	$end = substr($reg,$pos+1);
}
if(!isset($args[$region_str.'1'])) {// no region selected
	$region1 = "2:100-110";
	$region2 = "2:137-137.8";
	// header() TODO add refer to itself so one sees the arguments
}else{
	$region1 = $args[$region_str.'1'];
	$region2 = $args[$region_str.'2'];
}
if(isset($args[$hide_str])){
	$hide = $args[$hide_str];
}else{
	$hide = 3;
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
$loci_ex1 = get_only_loci_from_sql($sql, $experiment1['connection']);

$genome_id2 = $species2genome_db_ids[$species2];
$db2 = $genome_ids2dbs[$genome_id2];
$sql = 'select Name from '.$db2.'.Locus
where Chr = '.$chr2.' 
and cMorgan >= '.$start2.' 
and cMorgan <= '.$end2.';';
$loci_ex2 = get_only_loci_from_sql($sql, $experiment2['connection']);
// Loci to genes
useDB($db1,$experiment1['connection']);
$loci2stable_ids_ex1 = loci2stable_ids($loci_ex1,$experiment1['connection']);
$unique_ens_ids_ex1 = get_unique_vals_from_2d_array($loci2stable_ids_ex1[0]);

useDB($db2,$experiment2['connection']);
$loci2stable_ids_ex2 = loci2stable_ids($loci_ex2,$experiment2['connection']);
$unique_ens_ids_ex2 = get_unique_vals_from_2d_array($loci2stable_ids_ex2[0]);
//exit('Exit: Debbuging in compara.php!');

// HOMOLOGY => do it on the fewer genes
$n_ens_ids_ex1 = sizeof($unique_ens_ids_ex1);
$n_ens_ids_ex2 = sizeof($unique_ens_ids_ex2);
$traits12traits2 = array();
if($n_ens_ids_ex1 < $n_ens_ids_ex2){
	$homology_ex1 = get_homologue_ens_ids($compara,$unique_ens_ids_ex1,$genome_id2);
	//intersection
	foreach ($homology_ex1 as $unique_id_ex1 => $corr_homologue_ens_ids_ex1) {
		$intersect = array_intersect(array_keys($corr_homologue_ens_ids_ex1),
		$unique_ens_ids_ex2);
		foreach ($intersect as $id_ex2){
			$traits12traits2[$unique_id_ex1][$id_ex2] = $corr_homologue_ens_ids_ex1[$id_ex2];
		} 
	}
}else{
	$homology_ex2 = get_homologue_ens_ids($compara,$unique_ens_ids_ex2,$genome_id1);
	//intersection

	foreach ($unique_ens_ids_ex1 as $id_ex1){
		$traits12traits2[$id_ex1] = array();
	}
	foreach ($homology_ex2 as $unique_id_ex2 => $corr_homologue_ens_ids_ex2) {
		$intersect = array_intersect(array_keys($corr_homologue_ens_ids_ex2), $unique_ens_ids_ex1);
		foreach ($intersect as $id_ex1){
			$traits12traits2[$id_ex1][$unique_id_ex2] = $corr_homologue_ens_ids_ex2[$id_ex1];
		}
	}
}

function deleteNonHomos(&$loci2stable_ids_ex,$is_homo){
	foreach ($loci2stable_ids_ex[0] as $locus => $traits){
		foreach ($traits as $numkey => $trait){
			if(!$is_homo[$trait]){
				unset($traits[$numkey]);
			}
		}
		$loci2stable_ids_ex[0][$locus] = $traits;
	}
}
//SET THIS BOOLEAN TO TRUE FOR THE WHOLE TABLE (also empty rows and columns will be shown)
//$showAll = $hide==0;
$homos_exist = false;

$notShowAll1 = 1 & $hide;
$notShowAll2 = 2 & $hide;

switch ($hide) {
	case 3: // hide both
		$is_homo1 = array_combine($unique_ens_ids_ex1,array_fill(0,$n_ens_ids_ex1,false));
		$is_homo2 = array_combine($unique_ens_ids_ex2,array_fill(0,$n_ens_ids_ex2,false));

		foreach ($traits12traits2 as $trait1 => $homos2){
			if (!empty($homos2)){
				// $trait1 has homologies
				$is_homo1[$trait1] = true;
				$homos_exist = true;
				foreach ($homos2 as $homo2 => $desc) {
					$is_homo2[$homo2] = true;;
				}
			}
		}
		deleteNonHomos($loci2stable_ids_ex1,$is_homo1);
		deleteNonHomos($loci2stable_ids_ex2,$is_homo2);
		break;
	case 1:// hide first
		$is_homo1 = array_combine($unique_ens_ids_ex1,array_fill(0,$n_ens_ids_ex1,false));

		foreach ($traits12traits2 as $trait1 => $homos2){
			if (!empty($homos2)){
				// $trait1 has homologies
				$is_homo1[$trait1] = true;
				$homos_exist = true;
			}
		}
		deleteNonHomos($loci2stable_ids_ex1,$is_homo1);
		break;
	case 2:// hide second
		$is_homo2 = array_combine($unique_ens_ids_ex2,array_fill(0,$n_ens_ids_ex2,false));

		foreach ($traits12traits2 as $trait1 => $homos2){
			if (!empty($homos2)){
				// $trait1 has homologies
				$homos_exist = true;
				foreach ($homos2 as $homo2 => $desc) {
					$is_homo2[$homo2] = true;;
				}
			}
		}
		deleteNonHomos($loci2stable_ids_ex2,$is_homo2);
		break;

	default:
		$homos_exist = true;
		break;
}


//warn($loci2stable_ids_ex1[0]);
//exit();

if(!$homos_exist){
	// no homologies found
	require_once '../eqtl/header.php';
	show_large_header("Intergenomics",true,"Ensembl Compara interface for Expression QTL",
	'../eqtl/', array('css/style.css','css/prettyPhoto.css'));
	warn("Sorry, no homologies found for the given region.");
	echo <<<END
	<button onclick="javascript:history.back();">Back to the syntenic regions.</button>
END;
	include '../eqtl/footer.php';
	include 'html/footer.html';
	exit();
}

//warn($traits12traits2);
// display -----------------------
$cols = 227; // width of left offset
$rows = 138; // heigth of above offset

include 'utils/write_detail_table.php';

$scroll_width = 16;

echo '<?xml version="2.0" encoding="iso-8859-1"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<script type="text/javascript"
	src="js/table-scroll.php?cols=<?php echo $cols.'&rows='.$rows;?>"></script>
<script type="text/javascript"
	src="js/homology.js"></script>
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
			<frame marginheight="0" marginwidth="10" scrolling="no" name="obRe"
				src="html/table.html" />
			<!-- scrollbar extension right -->
			<frame scrolling="no" src="html/leer.html" />
		</frameset>

		<!-- content -->
		<frame marginheight="0" marginwidth="10" scrolling="auto" name="untRe"
			src="html/table.html" />
	</frameset>
</frameset>
</html>
