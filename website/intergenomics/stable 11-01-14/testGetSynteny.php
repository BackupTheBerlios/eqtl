<?php
include 'html/header.html';
require_once 'db_functions.php';
require_once 'utils.php';
//die entsprechende Datenbank von Steffen (stockholm oder rostock)
//$targetdb = @new mysqli('127.0.0.1', 'root', 'DBconnect', 'eqtl_rostock_eae');
$targetdb = @new mysqli('127.0.0.1', 'anonymous', 'no', 'eqtl_rostock_eae');
if (mysqli_connect_errno()) {
	trigger_error('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
}
//die Comparadatenbank
$comparadb = connectToCompara();
//der Lokus der mit allen anderen verglichen wird
//$locus = 'c5.loc57';
//der Name der Targetspecies! also der von der die anderen Loci kommen...
//$species_name = 'Mus musculus';
//die genome_db_id von der Targetspecies... wäre vermutlich toller wenn sie die funktion die selbst raussucht aber nun ist es erstmal so
//$genome_db_id = 57;

$species_names = array("Rattus norvegicus","Mus musculus");
$genome_db_ids = array(3,57);
$dbs = array('eqtl_stockholm_eae_logplier', 'eqtl_rostock_eae');

//warn($species_names);
//warn($genome_db_ids);

//rat
$loci_ex1 =  array('c1.loc91', 'c1.loc92', 'c1.loc93', 'c1.loc94', 'c1.loc95', 'c1.loc96', 'c1.loc97', 'c1.loc98',
	'c1.loc99', /*'c10.loc10', 'c10.loc100', 'c10.loc101', 'c10.loc102', 'c10.loc103', 'c10.loc104',
	'c10.loc111', 'c10.loc12', 'c10.loc13', 'c10.loc14', 'c10.loc15', 'c10.loc16', 'c10.loc17',
	'c10.loc18', 'c10.loc19', 'c10.loc20', 'c10.loc21', 'c10.loc22', 'c10.loc23', 'c10.loc24',
	'c10.loc25', 'c10.loc26', 'c10.loc27', 'c10.loc28', 'c10.loc29', 'c10.loc3', 'c10.loc30',
	'c11.loc54', 'c11.loc55', 'c11.loc56', 'c11.loc57', 'c11.loc58', 'c11.loc59', 'c11.loc6',
	'c11.loc60', 'c11.loc61', 'c11.loc62', 'c11.loc63', 'c11.loc64', 'c11.loc7', 'c11.loc8', */
   'c11.loc9', 'c12.loc1', /*'c12.loc10', 'c12.loc11', 'c12.loc12', 'c12.loc13', 'c12.loc14', 
   'c12.loc15', 'c12.loc16',*/ 'c12.loc17', 'c12.loc18');

//mus
$loci_ex2 = array('c14.loc25','cX.loc14','c10.loc38','c11.loc46','D18Mit19','c14.loc10','D11Mit86',
'c8.loc50','c1.loc90','c12.loc12','D4Mit256','c3.loc53','c3.loc54','c17.loc23','c9.loc60',
'c9.loc61','c17.loc39','c6.loc6','c11.loc73','c2.loc116','c2.loc112');

//tolle Ergebnis ausgabe :)
$syntenyArray = getSynteny($targetdb,$comparadb,$loci_ex1,$loci_ex2,$species_names,$genome_db_ids,$dbs);
foreach ($syntenyArray as $locus_ex1 => $synthenic_ex2){
	echo $locus_ex1;
	warn($synthenic_ex2);
}
//var_export($syntenyArray);

include 'html/footer.html';
?>