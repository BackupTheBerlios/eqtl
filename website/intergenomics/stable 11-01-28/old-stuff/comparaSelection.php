<h4>Click on the options you want to search with!</h4>
<?php
require_once("db_functions.php");

$db = @new mysqli('ensembldb.ensembl.org', 'anonymous', '', 'ensembl_compara_47');
if (mysqli_connect_errno()) {
	die ('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
}

$sqlSpecies = 'select name
from genome_db 
group by name;';

$speciesQuery = $db->query($sqlSpecies) or trigger_error('Query failed: '.$db->error);
$species = $speciesQuery->fetch_all();
$speciesCount = $speciesQuery->num_rows;
?>
<form action="comparaSelection.php" method="get">
<table cellpadding="1" cellspacing="3" border="1">
<?php
// generate checkboxes for every species from the query
for ($i = 0; $i < $speciesCount; $i=$i+2) {
	echo '<tr> <td><input type="checkbox" name="speciesArray[]" value="'.$species[$i].'"></td>';
	echo " <td>".implode($species[$i])."</td>";
	if ($i+1 <= $speciesCount-1) {
		echo '<td><input type="checkbox" name="speciesArray[]" value="'.$species[$i+1].'"></td>';
		echo " <td>".implode($species[$i+1])."</td></tr>\n";
	}else echo '</tr>';
}
?>
</table><br />
<input type="submit" /></form>

<?php 
if(isset($_GET['speciesArray'])) {
	echo 'huuuuu';
	$chromosoms = getChromosoms($db,$name);
	echo 'huuuuu';
	echo $name;
	echo 'huuuuu';
	for ($i = 0; $i < 10; $i++) {
		echo $chromosoms[$i];
	}
}
?>