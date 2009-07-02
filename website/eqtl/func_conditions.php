
<?php

/**

=head1 NAME

func_conditions.php - management of lists of constraits

=head1 SYNOPSIS

to be included once by PHP scripts that show constraints to be selected from

=head1 DESCRIPTION

When attributes are queried against that are shared between forms,
then the associated code should move into this file. The constraint themselves
are defined as an array of "name","SQL code" pairs. This shall help to reduce
the amount of redundant code between web forms.

=head1 AUTHORS

Steffen Moeller <moeller@inb.uni-luebeck.de>

=cut

*/

function print_condition_form_element($conditionList,$prompt,$condition,$radioOrCheckbox="checkbox") {
	if (empty($condition)) {
		$condition=array();
	}
	echo "<p>$prompt<br>";
	echo "<table>";
	if ("radio"=="$radioOrCheckbox") {
		echo "<tr>";
		echo "<td nowrap valign=top><input type=$radioOrCheckbox name=condition[] value=\"\"";
		if (0==count($condition)) echo " checked";
		echo ">";
		echo " neither :</td><td><i>don't perform</i></td>";
		echo "</tr>\n";
	}
	elseif ("checkbox"!="$radioOrCheckbox") {
		errorMessage("print_condition_form_element: unknown type '$radioOrCheckbox'.");
		exit;
	}
	foreach($conditionList as $n=>$c) {
		//print_r($c);
		echo "<tr>";
		echo "<td nowrap valign=top><input type=$radioOrCheckbox name=condition[] value=\"$n\"";
		if (in_array($n,$condition)) echo " checked";
		echo ">";
		echo " $n :</td><td><i>".$c["description"]."</i></td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</p>";
}

?>
