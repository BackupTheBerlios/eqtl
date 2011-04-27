<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 compara.php - Overview on a syntenic regions of a selected set of regions

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

require_once 'qtl_functions.php';
require_once 'db_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();

//measure running-time
$start = tic();

$args = $_GET;

$compara = connectToCompara();

$proj_str = 'projects';
if(!isset($args[$proj_str])){
	fatal_error('No project given as argument!');
}
$projects = $args[$proj_str];
$n_proj = count($projects);
switch ($n_proj) {
	case 1:
		// is possible, if the referrer is locus.php

		;
		break;
	case 2:
		// is possible, if the referrer is regions.php
		connectToQtlDBs($projects);
		break;
	default:
		fatal_error('Wrong number of projects!');;
		break;
}
$experiment1 = $compara_array[$projects[0]];
$experiment2 = $compara_array[$projects[1]];

$reg_str = 'regions';
$chr2reg = array();

//get confidence interval from GET
if(isset($args['confidence_int'])){
	$confidence_int = $args['confidence_int'];
}else{
	fatal_error('No confidence interval found!');
}
$regs = $args[$reg_str];
foreach ($regs as $reg){
	$pos = strpos ($reg, ":");
	$regionChr[] = substr($reg,0,$pos);
	$regionBP = substr($reg,$pos+1);
	//build substrings with start and ending of region
	$pos = strpos ($regionBP, "-");
	//start of region in bp
	$regionStart[] = substr($regionBP,0,$pos);
	//end of region in bp
	$regionEnd[] = substr($regionBP,$pos+1);
}
//initialize array for mapping groupnumbers to regions
$group2region = array();
$group2region2 = array();

//load informations of experiment1 (database and so on...)
//$genome_db_ids = array($experiment1['genome_db_id'],$experiment2['genome_db_id']);
$species_names = array($experiment1['species'],$experiment2['species']);
$database1 = $experiment1['db_name'];
$database2 = $experiment2['db_name'];


//put the region start and region end in arrays
for ($i = 0; $i < sizeof($regionChr); $i++) {
	//bp to cM for start of region
	$intervalStart[$i] = bp2cM($regionChr[$i], (int)$regionStart[$i],$experiment1['species']);
	//bp to cM for end of region
	$intervalEnd[$i] = bp2cM($regionChr[$i], (int)$regionEnd[$i],$experiment1['species']);
}
$chromosomsEx1 = $regionChr;
warn("davor");
$ex1 =  get_loci_from_sql($database1, $experiment1['connection'], 'userinterval', $chromosomsEx1, $confidence_int, $group2region, $intervalStart, $intervalEnd);
if (!empty($ex1)) {
	// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr') $mapEx1 = index -> (locus,groupNr)
	list($groups1, $mapEx1) = $ex1;
}else {
	echo '<INPUT TYPE=BUTTON VALUE="back" onClick="history.back()">';
	echo '<br />';
	fatal_error('nothing found for the given region(-s)');
}

// generates an arrays with index -> locinames
// $loci_ex1 = array_map('current',$mapEx1);
$chromosomsEx2 = getChromosomes($compara, $experiment2['ensembl_species']);
//filter compara chromosoms for existing chromosoms in QTL-database
$chromosomsEx2 = filter_chromos($experiment2['connection'], array_flip($chromosomsEx2));
$chromosomsEx2 = array_flip($chromosomsEx2);

$ex2 =  get_loci_from_sql($database2, $experiment2['connection'], 'wholeGenome', $chromosomsEx2, $confidence_int, $group2region2);
// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $mapEx2 = index -> (locus,groupNr)
list($groups2, $mapEx2) = $ex2;
// generates an arrays with index -> locinames
// $loci_ex2 = array_map('current',$mapEx2);

// SYNTENY
$genome_db_ids = getGenomeDBIDs($compara,array($experiment1['ensembl_species'], $experiment2['ensembl_species']));
$dbs = array($database1,$database2);
$groupSynteny_ex12ex2 = getSyntenyGroups(array($experiment1['connection'], $experiment2['connection']),$compara,$groups1,$groups2,$species_names,$genome_db_ids,$dbs);

// display -----------------------
include 'display_table.php';
toc($start,'Synteny search');
include 'html/footer.html';
?>
