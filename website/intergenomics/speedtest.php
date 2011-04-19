<?php

include 'html/header.html';

require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();

connectToQtlDBs(array('Ratte'));
global $compara_array;
$db = $compara_array['Ratte']['connection'];

useDB('eqtl_stockholm_eae_logplier', $db);
//useDB('eqtl_rostock_eae', $db);


$loci = array('c1.loc1','c1.loc10','c1.loc100','c1.loc101','c1.loc102','c1.loc103','c1.loc104',
'c1.loc105','c1.loc106','c1.loc107','c1.loc108','c1.loc109',
'c1.loc11','c1.loc110','c1.loc111','c1.loc112','c1.loc113','c1.loc114','c1.loc115');


//$loci = array('c9.loc48', 'c9.loc43', 'c9.loc40' , 'c9.loc39');

loci2stable_ids_new($loci,$db);
loci2stable_ids_new($loci,$db);

$t = tic();
print_r(loci2stable_ids_new($loci,$db));
print "<br>";
print "<br>";
toc($t, 'opt');
print "<br>";
print "<br>";
$t = tic();
print_r(loci2stable_ids($loci,$db));
print "<br>";
print "<br>";
toc($t, 'normal');

include 'html/footer.html';
?>