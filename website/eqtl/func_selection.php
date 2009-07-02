<?php

require_once("func_dbconfig.php"); // errorMessage

function print_selection_form($properties) {

	global $LODmin,$LODmax,$quantilemin,$quantilemax,
	       $LODdiffmin, $LODdiffmax, $cM_Peak_Min, $cM_Peak_Max,
	       $cM_within
	;


	if (empty($properties)) {
		errorMessage("Internal problem: print_selection_form() was called with empty argumentlist");
		exit;
	}

	if (!is_array($properties)) {
		$properties = preg_split("/,/",$properties);
	}
	
	foreach ($properties as $p) {

		switch($p) {

			case "all":
			case "LOD":
?>
<tr><th class=r>LOD-score span:</th>
    <td colspan=3>
	<input type=text name=LODmin size=4 value=<?php echo empty($LODmin)?"3.9":$LODmin;?>>
	-
	<input type=text name=LODmax size=4<?php if (!empty($LODmax)) echo " value=$LODmax";?>>
    </td></tr>
<?php
			break;
			case "all":
			case "quantile":
?>
<tr><th class=r>95% quantile span:</th>
    <td colspan=3>
	<input type=text name=quantilemin size=4 value= <?php echo empty($quantilemin)?"":$quantilemin; ?>>
	-
	<input type=text name=quantilemax size=4<?php if (!empty($quantilemax)) echo " value=$quantilemax";?>>
    </td></tr>
<?php
			break;
			case "all":
			case "LODdiff":
?>
<tr><th class=r>Min diff of LOD score to 95% quantile:</th>
    <td colspan=3>
	<input type=text name=LODdiffmin size=4 value= <?php echo empty($LODdiffmin)?"0":$LODdiffmin; ?>>
    </td></tr>
<?php
			break;
			case "all":
			case "peak":
?>
<tr><th class=r>centi-Morgan span for peak:</th>
    <td colspan=3>
	<input type=text name=cM_Peak_Min size=4<?php if (!empty($cM_Peak_Min)) echo " value=$cM_Peak_Min";?>>
	-
	<input type=text name=cM_Peak_Max size=4<?php if (!empty($cM_Peak_Max)) echo " value=$cM_Peak_Max";?>>
    </td>
</tr>
<?php
			break;
			case "all":
			case "flanks":
?>
<tr><th class=r>centi-Morgan position<br>within flanking positions</th>
    <td colspan=3>
	<input type=text name=cM_within size=4<?php if (isset($cM_within)) echo " value=$cM_within";?>>
    </td>
</tr>
<?php
			break;

			default:
				errorMessage("print_selection_form: Unknown parameter '$p'\n");
				exit;
		}
}

?>
