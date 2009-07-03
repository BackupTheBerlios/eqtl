<?php

require_once("func_dbconfig.php"); // errorMessage

function print_input_text($name,$val,$td=FALSE,$size=7) {
   if ($td) echo "<td>";
   echo "<input type=text name=$name size=$size";
   if (!(""=="$val" or empty($val))) {
   	echo " value=\"$val\"";
   }
   echo ">";
   if ($td) echo "</td>";
}

function print_two_input_text($name1,$name2,$val1,$val2,$td=TRUE,$size=7,$interspace=" - ") {
   if ($td) echo "<td>";
   print_input_text($name1,$val1,FALSE,$size);
   echo "$interspace";
   print_input_text($name2,$val2,FALSE,$size);
   if ($td) echo "</td>";
}

function print_row_two_text_single($rname,$name1,$name2,$val1,$val2,$size=7,$interspace=" - ") {
   echo "<tr>";
   echo "<th align=right>$rname:</th>";
   print_two_input_text($name1,$name2,$val1,$val2,TRUE,$size,$interspace);
   echo "</tr>\n";
}

function print_row_one_text_single($rname,$name1,$val1,$size=7) {
   echo "<tr>";
   echo "<th align=right>$rname:</th>";
   print_input_text($name1,$val1,TRUE,$size);
   echo "</tr>\n";
}


function print_selection_form($properties) {

	global $LODmin,$LODmax,$quantilemin,$quantilemax,
	       $LODdiffmin, $LODdiffmax, $cM_Peak_Min, $cM_Peak_Max,
	       $cM_within, $groups, $locus, $chrlist,
	       $MeanMin, $MeanMax, $SdMin, $SdMax, $VarianceMin, $VarianceMax, $MedianMin, $MedianMax
	;


	if (empty($properties)) {
		errorMessage("Internal problem: print_selection_form() was called with empty argumentlist");
		exit;
	}

	if (!is_array($properties)) {
		switch($properties) {
		 case "all_qtl":
			$properties = array("groups", "locus", 
					    "chromosome", "LOD", "quantile", "LODdiff",
					    "peak", "flanks", "trait",
					    "mean", "sd");
			break;
		 case "all_qtl_groups":
			$properties = array("mean", "sd", "LOD");
			break;
		 
		 case "all_qtl_phen":
			$properties = array("mean", "sd", "median", "variance","limit");
			break;
		 
		 case "all_qtl_trait":
			$properties = array("mean", "sd", "median", "variance" #, "LOD"
			);
			break;
		 
		 default: 
			$properties = preg_split("/,/",$properties);
			break;
		}
	}

	
	foreach ($properties as $p) {

		switch($p) {

			case "groups":
?>
			<tr><th class=r>Groups:</th><td colspan=3><input type=text name=groups size=40 maxsize=700
<?php
			if (!empty($groups)) {
				echo "value=";
				if (is_array($groups)) {
					#FIXME: this should show the names of the groups, not the IDs
					echo "\"".join(",",$groups)."\"";
				}
				else {
					echo "\"$groups\"";
					$groups = preg_split("/[ ,;\t\n]+/",$groups);
				}
			}
?>
			></td></tr>
<?php
			break;

			case "locus":
?>
			<tr><th class=r>Locus:</th><td colspan=3><input type=text name=locus size=40 maxsize=700
<?php
			if (!empty($locus)) {
				echo "value=";
				if (is_array($locus)) {
					echo "\"".join(",",$locus)."\"";
				}
				else {
					echo "\"$locus\"";
					$locus = preg_split("/[ ,;\t\n]+/",$locus);
				}
			}
?>
			></td></tr>
<?php
			break;

			case "chrlist":
			case "chromosome":
?>
			<tr><th class=r>Chromosomes:</th><td colspan=3><input type=text name=chrlist size=40 maxsize=700
<?php
			if (!empty($chrlist)) {
				echo "value=";
				if (is_array($chrlist)) {
					echo "\"".join(",",$chrlist)."\"";
				}
				else {
					echo "\"$chrlist\"";
				}
			}
?>
			></td></tr>
<?php
			break;

			case "LOD":
				if (empty($LODmin)) $LODmin=3.9;
				print_row_two_text_single("LOD-score span","LODmin","LODmax",$LODmin,$LODmax,5);
				break;

			case "quantile":
				print_row_two_text_single("95% quantile span","quantilemin","quantilemax",
								$quantilemin,$quantilemax,4);
				break;

			case "LODdiff":
?>
<tr><th class=r>Min diff of LOD score to 95% quantile:</th>
    <td colspan=3>
	<input type=text name=LODdiffmin size=4 value= <?php echo empty($LODdiffmin)?"0":$LODdiffmin; ?>>
    </td></tr>
<?php
				break;

			case "peak":
				print_row_two_text_single("centi-Morgan span for peak","cM_Peak_Min","cM_Peak_Max",
								$cM_Peak_Min,$cM_Peak_Max,4);
				break;
			case "flanks":
?>
<tr><th class=r>centi-Morgan position<br>within flanking positions</th>
    <td colspan=3>
	<input type=text name=cM_within size=4<?php if (isset($cM_within)) echo " value=$cM_within";?>>
    </td>
</tr>
<?php
				break;

			case "traitlist":
			case "trait":
?>
			<tr><th class=r>Trait names:</th>
			    <td colspan=3>
			      <input type=text name=traitlist size=30 maxsize=255
<?php
	if (!empty($traitlist)) {
		if (is_array($traitlist)) {
			echo "value=\"".join(",",$traitlist)."\"";
		}
		else {
			echo "value=\"$traitlist\"";
		}
	}
?>
			></td></tr>
<?php
			break;


			case "mean":
				print_row_two_text_single("Expression Mean","MeanMin","MeanMax",$MeanMin,$MeanMax);
				break;

			case "median":
				print_row_two_text_single("Expression Median","MedianMin","MedianMax",$MedianMin,$MedianMax);
				break;

			case "variance":
				print_row_two_text_single("Expression Variance","VarianceMin","VarianceMax",$VarianceMin,$VarianceMax);
				break;

			case "sd":
				print_row_two_text_single("Expression SD","SdMin","SdMax",$SdMin,$SdMax);
				break;

			case "limit":
				if (empty($limit)) $limit=10;
				print_row_one_text_single("Limit","limit",$limit,4);
				break;

			default:
				errorMessage("print_selection_form: Unknown parameter '$p'\n");
				exit;
		} // switch
	} // for
} // function

?>
