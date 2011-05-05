<?php 

include 'html/header.html';

require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();
$fptr = fopen('analysis/rat.txt', 'w');

$str = "Locus\tchr\tgroup\tstart\tstop\tTrait\tStatus\tSyngroup\t\tchr\tstart\tend\tTrait\r\n";
fwrite($fptr, $str);
fclose($fptr);

?>