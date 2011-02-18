
<h4>Click on the desired species you want to search with!</h4>

<?php
require_once 'db_functions.php';

date_default_timezone_set('Europe/Berlin');
//connect to ensembldb.ensembl.org
$db = connectToCompara();

//get every species that is in the database
$species = getAllSpeciesNames($db);
//count the species
$speciesCount = sizeof($species);
?>
<!-- Form for selection of the desired species starts here -->
<form action="listSelection.php" method="get"><select
	name="speciesList[]" size="<?php $speciesCount?>" multiple="multiple">
	<?php
	// generate list with every species from the query
	for ($i = 0; $i < $speciesCount; $i++) {
		echo '<option name="speciesList[]" value="'.$species[$i][0].'">'.$species[$i][0].'</option>';
	}
	?>
</select> <br />
<input type="submit" /></form>

<?php
//check if the user has selected one or more species
if(isset($_GET['speciesList'])) {
	//mainform of selection starts here (search options)
	echo '<form action="results.php" method="get">';
	echo '<h4>Select the options for each selected species!</h4>';
	//display possible searchoptions for each selected species in a table
	echo '<table cellpadding="1" cellspacing="1" border="1">';
	echo '<th>selected species</th>';
    echo '<th>chromosoms</th>';
    echo '<th>target species</th>';
	
	foreach ($_GET["speciesList"] as $val) {
		echo '<tr>';
		echo '<td style="text-align: center">'.$val.'</td>';
		//get the chromosoms of the selected species...
		$chromosoms = getChromosoms($db,$val);
		$chromoCount = sizeof($chromosoms);
		echo '<td>';
		//Version 1: with checkboxes inside a table
		echo '<table cellpadding="0" cellspacing="1" border="0">';
		for ($i = 0; $i < $chromoCount; $i++) {
			echo "<td>";
			echo '<input type="checkbox" name="targetChromosoms[]" value="'.$chromosoms[$i][0].'">&nbsp;'.$chromosoms[$i][0].'&nbsp;';
			echo "</td>";
			if (($i+1) % 6 == 0) {
				echo '<tr>';
			}
		}
		echo '</table>';
		//Version 2 with a list
//		echo '<select name="chromoList[]" size="6" multiple="multiple">';
//		for ($i = 0; $i < $chromoCount; $i++) {
//			echo '<option name="chromoList[]" value="'.$chromosoms[$i][0].'">'.$chromosoms[$i][0].'</option>';
//		}
//		echo '</select>';
		
		echo '</td>';
		//TODO: hardcoded entries. We only use mouse, rat and human for now... changed later?
		echo '<td><input type="checkbox" name="targetSpecies[]" value="mouse">&nbsp;Mus musculus<br />';
		echo '<input type="checkbox" name="targetSpecies[]" value="rat">&nbsp;Rattus norvegicus<br />';
		//echo '<input type="checkbox" name="targetSpecies[]" value="human">&nbsp;Homo sapiens</td>';
		
		echo '</tr>';
	}
	echo '</table>';
	echo '<input type="submit" /></form>';
}
?> 
