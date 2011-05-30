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
require_once 'bp2cM_conversion.php';
require_once 'fill_related_projects.php';
require_once 'analysis/func_analysis.php';

fill_compara_array();

$compara = connectToCompara();
global $compara_array;
// args
$projects = array_keys($compara_array);
$confidence_int = 1;

connectToQtlDBs($projects);

# if ex1 is 0 the use the rat
$experiment1 = $compara_array[$projects[0]];
$experiment2 = $compara_array[$projects[1]];

//load informations (database and so on...)
$species_names = array($experiment1['species'],$experiment2['species']);
$database1 = $experiment1['db_name'];
$database2 = $experiment2['db_name'];
$dbs = array($database1,$database2);

// species
$species1 = $experiment1['species'];
$species2 = $experiment2['species'];
$ens_species1 = $experiment1['ensembl_species'];
$ens_species2 = $experiment2['ensembl_species'];

$genelist = file("analysis/rat_out.txt",FILE_IGNORE_NEW_LINES);
$diseaselist = file("analysis/QTL.txt",FILE_IGNORE_NEW_LINES);

$fptr = fopen("analysis/rat_diseas.txt", 'w');
fwrite($fptr, $genelist[0]."\r\n");

# $keys = explode("\t", $genelist[0]);
# QTL
#start_bps		stop_bps		Chr		species		syntenyID

/**
 *
 * Copy syn_ids of the QTL-Table in array
 */
$species_d = array();
$synIDs_d = array();
$size_d = count($diseaselist);
for($i=1; $i<$size_d; $i++) {// skip header
	$entry = explode("\t\t", $diseaselist[$i]);
	$species_d[] = $entry[3];
	$synIDs_d[] = explode(",", $entry[4]);
}

# synreg
#Locus	group	chr	start	stop	Trait	Status	Syngroup	chr	start	end	Trait	homotype syn_ids

$size = count($genelist);
for($i=1; $i<$size; $i++) {// skip header
	$entry = explode("\t", $genelist[$i]);
	$syn_list = explode(",", $entry[13]);
	
	$tmp_species = array();
	for($i=0; $i<$size_d-1; $i++) {// skip header
		$intersect = array_intersect($syn_list, $synIDs_d[$i]);
		if(!empty($intersect)){
			// synteny
			
		}
	}
	$chr = $entry[2];
	$start = cM2bp($chr, $entry[3], $species1);
	$end = cM2bp($chr, $entry[4], $species1);
	$str = getSyntenyRegionIDs($compara, array($start,$end,$chr,$ens_species1));
	fwrite($fptr, $genelist[$i]."\t".$str."\r\n");
}

fclose($fptr);
echo "done";
include 'html/footer.html';
?>
