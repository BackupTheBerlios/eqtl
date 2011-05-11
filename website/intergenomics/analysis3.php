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

// needed for homology
$species1 = $experiment1['species'];
$species2 = $experiment2['species'];

$genelist = file("analysis/rat.txt");
$keys = explode("\t", $genelist[0]);
#Locus	group	chr	start	stop	Trait	Status	Syngroup	chr	start	end	Trait	homotype
$size = count($genelist);
for($i=1; $i<$size; $i++) {// skip header
	$entry = array_combine($keys, explode("\t", $genelist[$i]));
	$chr = $entry['chr'];
	$start = cM2bp($chr, $entry['start'], $species1);
	$end = cM2bp($chr, $entry['end'], $species1);
	
}


include 'html/footer.html';
?>
