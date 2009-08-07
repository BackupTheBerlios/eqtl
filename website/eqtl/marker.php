<?php

/*

=pod

=head1 NAME

marker.php

=head1 SYNOPSIS

dynamic web page to be invoked without arguments

=head1 DESCRIPTION

The page presents, with help by Ensembl, the chromosomal markers used
for the analysis, the base pair positions, and prepares a routine for
the conversion between Morgan and bp units.

=head2 Common attributes

=over 4

=item 

=back

=head2 Preparation of updated func_conversion.php

For every organism with every new release of Ensembl - or at least with
every new genome assembly - the function must be updated the performs
the translation between centi-Morgan units and base pairs. And also
this function need to be updated when R/qtl is passed different 
centi-Morgan information for the markers it works with - and passes on
as a result. 

To prepare a new version, one needs to specify the ensembl database
from which the marker-bp assignment can be retrieved. The respective
fields are offered at the head of the entry form, prefilled with the
current setting. If the "prepare conversion script" checkbox is selected,
upon submission the conversion script will be shown in a separate field,
next to the previous version. The administrator of the site should 
inspect it and save it to substitute the previous version. The Ensembl
version is a part of the filename of the script, hence multiple versions
can be held in parallel.

For markers that are not known to Ensembl, the user should manually
search for the marker sequences and thus specify the genomic location
in base pairs manually.


=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

Universities of Rostock and LE<uuml>beck, 2003-2009

=cut

*/

require_once("header.php");
require_once("func_species.php");
require_once("func_dbconfig.php"); //überflüssig da weiter unten nochmal? -- Benedikt 7.8.09

@show_small_header("Marker Selection",TRUE);

$requiredFields=array(
	"markerhost","markeruser","markerdatabase",
	"ensemblhost","ensembluser","ensemblversion" #,"ensembldatabase"
);

global $ensemblhost,$ensembluser,$ensemblversion,$ensembldatabase;
global $markerhost,$markeruser,$markerdatabase;

$tmp=$requiredFields;
array_push($tmp,
	"submitted",
	"chrlist","cMminRqtl","cMmaxRqtl","cMmin","cMmax",
	"order","limit",
	"show_conversion_code"
);

$attributeArray=array(
	"marker"=>1, "LocusOfMarker"=>1, "codeForConversion"=>1,
	"chr"=>1, "cmorgan_rqtl"=>1, "cmorgan_ensembl"=>1,
	"bp"=>1, "chr_start"=>1, "chr_end"=>1, "chr_name"=>1,
	"chromosome_id"=>1,"type"=>0);

foreach($attributeArray as $vname=>$v) {
	array_push($tmp,"show_${vname}");
}

foreach($tmp as $vname) 
{
	if (isset($_POST[$vname])) {
		$$vname=$_POST[$vname];
	}
	elseif (isset($_GET[$vname])) {
		$$vname=$_GET[$vname];
	}
}

require_once("func_dbconfig.php"); // cM2bp

$funcConversionExists = file_exists("func_conversion_".$ensemblversionLocal.".php");


if($funcConversionExists) {
	require_once("func_conversion_".$ensemblversionLocal.".php"); // cM2bp
}
	
function physicalMarkerPosition($marker) {
	global $linkEnsembl,$ensemblversion,$ensembldatabase;
	global $species_name_ensembl_mart;

	$queryEnsembl = "select "
		.($ensemblversion>19?
			"glook_marker_start as marker,olook_chr_name as chr,filt_chrom_start as chr_start,filt_chrom_end as chr_end"
			:
			"id as marker, chr,chr_start"
		 ) . " from "
		.($ensemblversion>19?
			("$ensembldatabase.".$species_name_ensembl_mart."_marker_start__marker_start__main")
			:
			("$ensembldatabase.".$species_name_ensembl_mart."_marker_lookup")
		) . " as a where a."
		.($ensemblversion>19?
			"glook_marker_start"
			:
			"id"
		);
// 	echo $queryEnsembl;
	if (is_array($marker)) {
		#print_r($marker);
		$queryEnsembl .= " in ('".implode("','",$marker)."') ";
	}
	else {
		$queryEnsembl .= "='$marker'";
	}
	$queryEnsembl .= " order by chr,chr_start";
	if (!empty($limit)) $queryEnsembl .= " limit ".$limit;

	$resultEnsembl = mysql_query($queryEnsembl,$linkEnsembl);
	if (empty($resultEnsembl)) {
		echo "<td colspan=2>";
		errorMessage("Error in retrieval of chromosomal location for marker"
			.$line["marker"].": "
			.mysql_error($linkEnsembl)."<br>"
			."ensemblversion: $ensemblversion, ensemblhost: $ensemblhost, ensembldatabase: $ensembldatabase");
		echo "</tr></table></body></html>";
		@mysql_close($linkEnsembl);
		@mysql_close($linkLocal);
		exit;
	}
	$retHash=array();
//  	echo "LineInner: for marker '$marker'";
	while ($lineInner = mysql_fetch_array($resultEnsembl, MYSQL_ASSOC)) {
// 		print_r($lineInner);
		// always takes the last which may be unfortunate
		$retHash[$lineInner["marker"]]=$lineInner;
	}
	#print_r($retHash);
	return($retHash);
}

if (empty($submitted)) {
?>
	<h1>Markers</h1>
	<form action=marker.php method=post>
	<input type=hidden name=submitted value=1>
	<?php show_config(); ?>
	<h2>Specification of marker properties</h2>
	<table><tr><td valign=top>
		<table>
		<tr><th align=right>Marker ID</th>
		    <td><input type=text name=traits lenth=70>
		    </td>
		</tr>
	<tr><th class=r>Chromosomes of interest:</th>
	    <td><input type=text name=chrlist size=30 maxsize=70
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
	<tr><th align=right>centi-Morgan span of R/qtl:</th>
		<td>
			<input type=text name=cMminRqtl size=4<?php if (!empty($cMminRqtl)) echo " value=$cMmin";?>>
			-
			<input type=text name=cMmaxRqtl size=4<?php if (!empty($cMmaxRqtl)) echo " value=$cMmax";?>>
		</td>
	</tr>
	<tr><th align=right>order by:</th>
		<td>
				<select name=order>
				<option value=marker>Marker</option>
				<option value=chr,cmorgan_rqtl>Chromosome,Morgan R/qtl</option>
				<option value=chr,cmorgan_ensembl>Chromosome,Morgan EnsEMBL</option>
				<option value=chr,bp>Chromosome,bp (manually assigned)</option>
				<option value=chr_name,chr_start>Chromosome,EnsEMBL start</option>
				</select>
		</td></tr>
	<tr><th aligh=right>Limit lines shown:</th>
		<td><input type=text name=limit value=500></td></tr>
		<tr><td>&nbsp;</td><td></td></tr>
		<tr><td align=right><input type=submit></td><td align=left><input type=reset></td></tr>
		<tr><td></td><td>&nbsp;</td></tr>
		<tr><td></td><td><input type=checkbox name=show_conversion_code value=1>Show Conversion Code</td></tr>
		</table>
	</td><td align=center valign=top>
			<small><small>
			<table border=1>
			<tr><th>Show (y/n)</th><th>Field</th></tr>
<?php
			foreach($attributeArray as $i=>$v) {
				echo "<tr><td align=right><input type=checkbox name=show_".$i.(empty($v)?"":" checked")."></td><td>$i</td></tr>\n";
			}
?>
			</table>
		</tr>
		</table>
		</form>
<?php
	}
	else {
		if (empty($_POST)) {
			echo "empty(\$_POST)";
			exit;
		}

		$errs=array();
		foreach ($requiredFields as $n) {
			if (isset($_POST[$n])) {
				$$n = $_POST[$n];
				echo "Found setting of $n to " . $$n . "<br>\n";
			}
			//echo "$n=".$$n."<br>";
			if (!isset($n) or ""==$$n) {
				array_push($errs,$n);
			}
		}
		if (count($errs)>0) {
			echo "<p>Missing data for the following fields:<br><i>".implode($errs,",")."</p>\n";
			echo "</body></html>\n";
			exit;
		}


		$where=FALSE;
		
		$query  = "select marker, chr, bp, cmorgan_rqtl, cmorgan_ensembl ";
		$query	.= " from map ";

		if (!empty($chrlist)) {
			if ($where) $query .= " AND ";
			else {
				$query .= " WHERE ";
				$where = TRUE;
			}
			$query .= " chr in ('".join("','",preg_split("/[, ;]/",$chrlist))."') ";
		}
		if (!empty($cMminRqtl) && !empty($cMmaxRqtl)) {
			if (!is_numeric($cMminRqtl)) array_push($err,"The lower boundary in cMorgan positions for loci under investigation must be numeric ($cMminRqtl).\n");
			if (!is_numeric($cMmaxRqtl)) array_push($err,"The upper boundary in cMorgan positions for loci under investigation must be numeric ($cMmaxRqtl).\n");
			if ($where) $query .= " AND ";
			else {
				$query .= " WHERE ";
				$where = TRUE;
			}
			$query .= " (cmorgan_rqtl BETWEEN ".$cMminRqtl." AND ".$cMmaxRqtl.") ";
		}
		else {
			if (!empty($cMmin)) {
				if (!is_numeric($cMmin)) array_push($err,"The lower boundary in cMorgan positions for loci under investigation must be numeric ($cMmin).\n");
				if ($where) $query .= " AND ";
				else {
					$query .= " WHERE ";
					$where = TRUE;
				}
				$query .= " cmorgan_rqtl >= ".($cMmin/100)." ";
			}
			if (!empty($cMmax)) {
				if (!is_numeric($cMmax)) array_push($err,"The upper boundary in cMorgan positions for loci under investigation must be numeric ($cMmax).\n");
				if ($where) $query .= " AND ";
				else {
					$query .= " WHERE ";
					$where = TRUE;
				}
				$query .= " cmorgan_rqtl >= ".($cMmax/100)." ";
			}
		}
		if (!empty($order)) {
			$query .= " order by ".$order." ";
		}
		else {
			$query .= " order by chr,cmorgan_rqtl";
		}
		if (!empty($limit)) {
			$query .= " limit ".$limit." ";
		}

		//echo "query: $query<br>";

		$linkLocal=mysql_connect($markerhost,$markeruser,"");

		if (empty($linkLocal)) {
			echo "<p>Could not create link to database on '$markerhost'. ";
			echo "Please report to <a href=\"$maintainerEmail\">$maintainerEmail</a></p>";
			echo "</body></html>";
			exit;
		}

		$linkEnsembl=mysql_connect(martdbhost($ensemblversion),$ensembluser,"");
		if (empty($linkEnsembl)) {
			echo "<p>Could not create link to Ensembl database on ".martdbhost($ensemblversion)." as $ensembluser.<br> ";
			echo "Please report to <a href=\"$maintainerEmail\">$maintainerEmail</a></p>";
			mysql_close($linkLocal);
			echo "</body></html>";
			exit;
		}

		if (!mysql_select_db("$markerdatabase",$linkLocal)) {
			echo "<p>Could not select database '$markerdatabase' on host '$markerhost'. ";
			echo "Please report to <a href=\"$maintainerEmail\">$maintainerEmail</a></p>";
			mysql_close($linkLocal);
			mysql_close($linkEnsembl);
			exit;
		}

		//$ensembldatabase="ensembl_mart_$ensemblversion".($ensemblversion<=28?"_1":"");
		if (!mysql_select_db(martdbname($ensemblversion),$linkEnsembl)) {
			echo "Could not select database '".martdbname($ensemblversion)."' on host '$ensemblhost'. Please report to <a href=\"moeller@inb.uni-luebeck.de\">moeller@inb.uni-luebeck.de</a><br>";
			mysql_close($linkLocal);
			mysql_close($linkEnsembl);
			exit;
		}

		$result = mysql_query($query,$linkLocal);
		if (empty($result)) {
			echo "<p>Error contacting local database with marker data: ".mysql_error($linkLocal)." for query '$query'<br>";
			echo "<p>Please report to moeller@inb.uni-luebeck.de</p>";
			mysql_close($linkLocal);
			mysql_close($linkEnsembl);
			exit;
		}
		
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$markerHash[$line["marker"]]=$line;
		}

		if (count($markerHash)>0) {

			//print_r($markerHash);
			$firstRow=true;

			echo "<small><table border=1>\n";
			foreach($markerHash as $marker => $line) {
				if ($firstRow) {
					$firstRow=FALSE;
					echo "<thead>";
					echo "<tr bgcolor=yellow>";
					foreach($line as $n=>$l) {
						$f="show_".$n;
						if (!empty($$f)) {
							echo "<th><small>$n</small></th>";
							if ("bp"==$n && !empty($show_bp)) {
								echo "<th><small>Bp interpolated</small></th>";
							}
						}
					}
					if (!empty($show_LocusOfMarker)) {
						echo "<th colspan=2>Ensembl Chr + Start bp</th>";
					}
					echo "</tr>\n";
					echo "</thead><tbody>";
				}
				echo "<tr>";
				foreach($line as $n=>$l) {
					$f="show_".$n;
					if (!empty($$f)) {
						switch($n) {
						case "marker":
							if (!isset($l)||""==$l) {
								echo "<td>&nbsp;</td>";
							}
							else {
								echo "<td><a href=\"http://www.ensembl.org/Rattus_norvegicus/markerview?marker=$l\">$l</a></td>";
							}
							break;
						case "bp":
							if (!isset($l)||""==$l) {
								echo "<td>&nbsp;</td>";
							}
							else {
								echo "<td>$l</td>";
							}
							echo "<td>";
							if ($funcConversionExists) {
								round(cM2bp($line["chr"],$line["cmorgan_rqtl"]));
							}
							echo "</td>";
							break;
						default:
							if (!isset($l)||""==$l) echo "<td>&nbsp;</td>";
							else echo "<td>$l</td>";
						}
					}
				}
				if (!empty($show_LocusOfMarker)) {
					$positions=array();
					$positions=physicalMarkerPosition($line["marker"]);
					//echo "Was here ".$line["marker"]."->".count($positions);
					foreach($positions as $m=>$p) {
						echo "<td>".$p["chr"]  ."</td>"
						    ."<td>".$p["chr_start"]."</td>"
						    ."<td>".$p["chr_end"]."</td>";
					}
				}
				echo "</tr>\n";
			}
			echo "</tbody></table></small>";
		}
		else {
			echo "<p>No marker found.</p><br>";
		}
		mysql_free_result($result);

		
/*
		foreach (preg_split("/[, ;]/",$chrlist) as $chr) {
			echo "<table border=1><tr bgcolor=orange><th class=c colspan=2>Chr $chr</th></tr>\n";
			echo "<tr bgcolor=orange><th class=sub>cM</th><th class=sub>bp</th></tr>\n";

			for ($i=0; $i<200; $i+= 5) {
				echo "<tr><td>$i<td>".cm2bp($chr,$i)."</td></tr>\n";
			}
			echo "</table>\n";
		}
*/

		if (!empty($show_conversion_code)) {
			echo "<h2>Code performing conversion from centiMorgan to physical position in Ensembl</h2>\n";

			$queryMarker  = "select marker,cmorgan_rqtl from map";
			$resultMarker = mysql_query($queryMarker,$linkLocal);
			if (empty($resultMarker)) {
				echo "<p>".mysql_error($link)." in query $queryMarker.<br>Please report to moeller@inb.uni-luebeck.de</p>";
				mysql_close($linkLocal);
				mysql_close($linkEnsembl);
				exit;
			}

			$markerHash=array();
			while ($line = mysql_fetch_array($resultMarker, MYSQL_ASSOC)) {
				$markerHash[$line["marker"]]=$line;
			}
			#print_r($markerHash);

			if(mysql_errno($linkLocal)) {
				echo "<p>".mysql_error($link)." in execution of query $queryMarker.<br>Please report to moeller@inb.uni-luebeck.de</p>";
				mysql_close($linkLocal);
				mysql_close($linkEnsembl);
				exit;
			}

			//echo "Submitting ".count(array_keys($markerHash))." markers<br>";
			$resultEnsembl = physicalMarkerPosition(array_keys($markerHash));
			if (empty($resultEnsembl) || mysql_errno($linkEnsembl)) {
				echo "<p>".mysql_error($linkEnsembl)." in query for marker positions.<br>Please report to moeller@inb.uni-luebeck.de</p>";
				mysql_close($linkLocal);
				mysql_close($linkEnsembl);
				exit;
			}

			echo "<br>";
			echo "<table border=1><tr><th bgcolor=lightyellow>Current</th><th bgcolor=lightyellow>New</th></tr>\n";
			echo "<tr><td valign=top><small><small><pre>";
			$s=file_get_contents("func_conversion_47.php");
			$ss=implode("&lt;",preg_split("/</",$s));
			echo $ss;
			echo "</pre></small></td><td valign=top>\n";
			echo "<small><small><pre>\n";
			echo "&lt;?php\n";
			echo "\$conv=array();\n";
			$prevChr="0";
			$firstRow=true;

			#print_r($resultEnsembl);

			foreach($resultEnsembl as $marker=>$line) {
				#print_r($line);
				$currChr=$line["chr"];

				if ($currChr != $prevChr) {
					if (!empty($line["chr_start"])) {
						if ("0" != $prevChr) {
							echo "\n);\n";
						}
						echo '$conv[';
						if (!is_numeric($currChr)) echo '"';
						echo $line["chr"];
						if (!is_numeric($currChr)) echo '"';
						echo "] = array(\n";
						print "/* $marker */\t";
						echo '  "'.$markerHash[$marker]["cmorgan_rqtl"].'" => "'.$line["chr_start"].'"';
						$prevChr=$currChr;
					}
				}
				else {
					if (!empty($line["chr_start"])) {
						echo ",\n";
						print "/* $marker */\t";
						echo '"'.$markerHash[$marker]["cmorgan_rqtl"].'" => '.$line["chr_start"];
					}
				}
				$first=TRUE;
			}

			if ("0" != $prevChr) {
				echo "\n);\n";
			}
	echo '
if (!empty($conv["X"])) $conv[21]=$conv["X"];
if (!empty($conv["Y"])) $conv[22]=$conv["Y"];

';
	echo '
function cM2bp($chr,$cm=0) {
	global $conv;
	$chrconv=$conv[$chr];
	$cMmin=$bpmin=-1;
	$cMmax=$bpmax=-1;
	$actbp=$lastbp=-1;
	$actcM=$lastcM=-1;
	$found=FALSE;
	$prevcM=$secondCM=-1;
	$prevbp=$secondBP=-1;
	if (empty($chrconv)) {
		echo "&lt;p>marker.php: No information for chromosome \'$chr\'.&lt;/p>\n";
		return (-2);
	} elseif (!is_array($chrconv)) {
		echo "&lt;p>func_conversion_47.php: Internal error. (chr \'$chr\').&lt;/p>\n";
		return (-3);
	} else {
		foreach($chrconv as $cMorgan=>$bp) {
			if (-1 == $cMmin) {
				$cMmin=$cMmax=$cMorgan;
				$bpmin=$bpmax=$bp;
			}
			else {
				if (-1 == $secondCM && !empty($cMorgan)) {
					$secondCM=$cMorgan;
					$secondBP=$bp;
				}
				if ($bp>$bpmax) {
					$bpmax=$bp;
					$cMmax=$cMorgan;
				}
			}

			if ($found) {
			}
			else {
				if ($cm&lt;$cMorgan) {
					$found=TRUE;
					if (-1 == $actcM) {
						$actcM=$cMorgan;
						$actbp=$bp;
						$lastcM=$prevcM;
						$lastbp=$prevbp;
					}
				}
			}
			$prevcM=$cMorgan;
			$prevbp=$bp;
		}

		if ($cm<=$cMmin) {
			// cM requested upstream of first marker
			$ret=$bpmin+($cm-$cMmin)/($secondCM-$cMmin)*($secondBP-$bpmin);
		} elseif (-1 != $actcM) {
			$ret=$lastbp+($cm-$lastcM)/($actcM-$lastcM)*($actbp-$lastbp);
		} else {
			// downstream of rightmost marker
			if ($cMmax==$cMmin) {
				// we only haev a single marker - helpless?
				// FIMXE: implement extra point (0,0)
				echo "&lt;p>cMmax==cMmin ($cMmax==$cMmin)\n&lt;p>";
				$ret=-1;
			}
			else {
				// extrapolating from the first to the last marker
				$ret=$bpmin+($cm-$cMmin)/($cMmax-$cMmin)*($bpmax-$bpmin);
			}
		}
		return(round($ret));
	}
}
';
		echo "// This conversion is based on ensembl database ".martdbname($ensemblversion)." on host ".martdbhost($ensemblveresion)."\n";
	echo "?>\n";
			echo "</pre></small>\n";
			echo "</td></tr></table>\n";

			mysql_close($linkLocal);
			mysql_close($linkEnsembl);
		}
	}
?>
<?php
	include_once("footer.php");
?>
</body>
</html>
