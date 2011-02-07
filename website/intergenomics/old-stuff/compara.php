<h4>Click on the dnafrag region you want to use for synteny search!</h4>
<?php
require_once "db_functions.php";
date_default_timezone_set('Europe/Berlin');

$speciesArray = array();
foreach ($_POST as $varname => $varvalue) {
	if (!"speciesArray"==$varname) {
		$speciesArray = explode('|',$varname);
	}
}

$speciesArray=$_POST["speciesArray"];
print_r($speciesArray);
$data = explode('|', $speciesArray[0]);

$db = connectToCompara();

//TODO: we need to integrate the selections from comparaSelection.php in this query...
$sql = 'select *
from dnafrag_region 
where (dnafrag_start >= 0 
AND dnafrag_start <= 10000000 
OR dnafrag_end <= 10000000 
AND dnafrag_end >= 0) limit 10;';

$result = $db->query($sql) or trigger_error('Query failed: '.$db->error);

?>
<form action="syntenySearch.php" method="post">
<table cellpadding="1" cellspacing="3" border="1">
  <tr>
    <th></th>
    <?php
    // Collumn names
    while ($finfo = $result->fetch_field()) {
    	echo "<th>".$finfo->name."</th>\n";
    }
    echo "</tr>";
    $rows = $result->fetch_all();
    $num_rows = $result->num_rows;

    // Ausgabe der Zeilen
    for ($i = 0; $i < $num_rows; $i++) {

    	echo '<tr> <td><input type="checkbox" name="comparaSource[]" value="'.implode("|",$rows[$i])
    	.'"></td>';

    	echo " <td>".implode("</td><td>",$rows[$i])."</td></tr>\n";

    }
    ?>

</table>
<input type="submit" /></form>
