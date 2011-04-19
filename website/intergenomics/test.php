<?php 

include 'html/header.html';

require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();

$compara = connectToCompara(3306);

get_homologue_ens_ids_opt($compara,array('ENSMUSG00000017146','ENSMUSG00000068240'),3);

$t = tic();
print_r(get_homologue_ens_ids_opt($compara,array('ENSMUSG00000017146','ENSMUSG00000068240'),3));
toc($t, 'opt');
$t = tic();
print_r(get_homologue_ens_ids($compara,array('ENSMUSG00000017146','ENSMUSG00000068240'),3));
toc($t, 'normal');
?>