<?php
require_once 'db_functions.php';

$db = connectToCompara(3306,true);

//TESTSET:
//,'ENSG00000215614','ENSG00000187979'
//
//Ratte loci c1.loc12 mit traits:
//10701486 mit stable_id hat keine
//10750074 mit stable_id ENSRNOT00000002158
//10820008 mit stable_id keine
//10905899 mit stable_id ENSRNOT00000039228
//10917116 mit stable_id ENSRNOT00000045356
//
//Ratte loci c1.loc10 mit traits:
//10700795 mit stable_id hat keine
//10740876 mit stable_id ENSRNOT00000004848
//10759431 mit stable_id ENSRNOT00000044547
//
$stable_id = array('ENSMUSG00000026048');
//echo 'stable_id lautet '.$stable_id[0].' und '.$stable_id[1].'<br />';
$homology_id = member2homology($db, $stable_id);
echo '<table cellpadding="1" cellspacing="1" border="1">';
$homologyCount = sizeof($homology_id);
echo '<tr>';
for ($i = 0; $i < $homologyCount; $i++) {
	echo '<th>homology_ids of '.$stable_id[$i].'</th>';
}
echo "</tr><tr>";
for ($j = 0; $j < $homologyCount; $j++) {
	echo "<td>";
	$entriesCount = sizeof($homology_id[$stable_id[$j]]);
	for ($k = 0; $k < $entriesCount; $k++) {
		echo $homology_id[$stable_id[$j]][$k].'<br />';
	}
	echo "</td>";
}
echo '</tr>';
echo '</table>';
//TODO: there can be more than 2 stable_ids... fixed parameters, be gone!
echo 'Intersection of homologies: '.implode(", ",array_intersect($homology_id[$stable_id[0]], $homology_id[$stable_id[1]]));
echo '<br />';
for ($l = 0; $l < $homologyCount; $l++) {
	echo '<br />stable_ids of '.$stable_id[$l].'<br />';
	//TODO: additional parameter: stable_id because of the return-array?
	$members = homology2member($db, $homology_id[$stable_id[$l]]);
	$result[$stable_id[$l]] = $members;
	echo '<table cellpadding="1" cellspacing="1" border="1">';
	echo '<tr><th>stable_id</th></tr>';
	$stableCount = sizeof($members);
	for ($m = 0; $m < $stableCount; $m++) {
		echo "<tr><td>";
		echo $members[$m];
		echo "</td></tr>";
	}
	echo '</table>';
}
	var_export($result);
?>