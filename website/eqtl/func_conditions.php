
<?php
function print_condition_form_element($conditionList,$prompt,$condition) {
	if (empty($condition)) {
		$condition=array();
	}
	echo "<p>$prompt<br>";
	echo "<table>";
	foreach($conditionList as $n=>$c) {
		//print_r($c);
		echo "<tr><td><input type=checkbox name=condition[] value=\"$n\"> $n :</td><td><i>".$c["description"]."</i></td</tr>\n";
	}
	echo "</table>\n";
	echo "</p>";
}
	?>
