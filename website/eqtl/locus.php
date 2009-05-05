<?php
	require_once("header.php");
	require_once("func_dbconfig.php");
	require_once("func_covariates.php");
	$database=$databaseeqtl;
	require_once("func_connect.php");
	show_small_header("Selection of eLoci",TRUE);
	$err=array();

	foreach(array(  "Name",
			"direct",
			"submitted",
			"show_LocusOfGene",
			"show_Trait",
			"show_mean",
			"show_sd",
			"show_unigene",
			"show_swissprot_ID",
			"show_gene_assignment",
			"show_first_symbol",
			"show_Description",
			"show_ProbeSequence",
			"MeanMin","MeanMax","SdMin","SdMax",
			"traitlist",
			"locus",
			"chrlist",

			"limit","order") as $vname)
	{
		if (isset($_POST[$vname])) {
			$$vname = $_POST[$vname];
		}
		elseif(isset($_GET[$vname])) {
			$$vname = $_GET[$vname];
		}
	}

	$break = Explode('/', $_SERVER['PHP_SELF']);
	$pfile = $break[count($break) - 1];
	$tmp = split($pfile, $_SERVER['PHP_SELF']);
	$BASEPATH = "http://".$_SERVER['SERVER_NAME'].$tmp[0];

	$a=array("Name"=>1,"Chr"=>1,"cMorgan"=>1,"Organism"=>0,"qtl"=>0,"locusInteraction" => 0,"VennLink"=>0, "Genes_in_locus" =>1, /*"trait"=>0, */"genes_effected"=>1
	);

	foreach($a as $vname => $x)
	{
		$vname = "show_".$vname;
		if (isset($_POST[$vname])) {
			$$vname = $_POST[$vname];
		}
		elseif(isset($_GET[$vname])) {
			$$vname = $_GET[$vname];
		}
	}

	if (!empty($direct)) {
		foreach($a as $i=>$v) {
			$n="show_".$i;
			$$n=$v;
		}
	}

	if (empty($direct) and empty($submitted)) {
?>
		<form action=locus.php method=get>
		<input type=hidden name=submitted value=1>
		<table width=100%><tr><td valign=top>
			<table>
			<tr><th class=r>Locus Name:</th>
			    <td><input type=text name=Name length=70
<?php
				if (!empty($Name)) {
					echo "value=";
					if (is_array($Name)) {
						echo "\"".join(",",$Name)."\"";
					}
					else {
						echo "\"$Name\"";
						$locus = preg_split("/[ ,;\t\n]+/",$Name);
					}
				}
?>
			    >
			    </td>
			</tr>
			<tr><th class=r>Chromosomes of interest:</th><td><input type=text name=chrlist size=30 maxsize=70
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
			<tr><th class=r>centiMorgan span:</th>
						<td>
						<input type=text name=cMmin size=4>
						-
						<input type=text name=cMmax size=4>
						</td></tr>
			<tr><th class=h align=center colspan=2>Information on traits assigned to loci</th>
			<tr><th align=right valign=top>Covariates:</th><td>
				<?php select_covariates($linkLocal,"both"); ?>
			</td></tr>
			<tr><th class=r>Trait names:</th><td><input type=text name=traitlist length=70
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
		<tr><th class=r>Interaction Type:</th><td><select name=type>
						<option value="" selected>Don't care, show either</option>
						<option value="int">LOD int</option>
						<option value="LEFT JOINt">LOD LEFT JOINt</option>
						</select>
						</td></tr>
			<tr><th class=r>Locus Type</th><td><select name=locustype>
						<option value="">Don't care, show either</option>
						<option value="scanone">Results of R/qtl scanone function (regular QTLs)</option>
						<option value="scantwo">Results of R/qtl scantwo function (epistatic effects)</option>
						</select>
						</td></tr>
			<tr><th class=r>Order by:</th><td>
						<select name=order>
						<option value="l.Chr,l.cMorgan">Chromosome, cMorgan</option>
						<option value="qtl DESC">Number of QTLs involved</option>
						<option value="trait DESC">Number of Traits involved</option>
						<option value="locusInteraction DESC">locusInteractions involved</option>
						</select>
						</td></tr>
			<tr><th class=r>Limit lines shown:</th><td><input type=text name=limit value=150></td></tr>
			<tr><th class=r>Format:</th><td><input type=radio name=format value="html" checked>HTML</input>&nbsp;<input type=radio name=format value="text">Text</input></td></tr>
			<tr><td>&nbsp;</td><td></td></tr>
			<tr><td class=r><input type=submit></td><td align=left><input type=reset></td></tr>
			</table>
		</td><td align=center valign=top>
			<small><small>
			<table border=0>
			<tr bgcolor=black><th align=left colspan=2><small><font color=orange>Show Field</font></small></th></tr>
<?php
			foreach($a as $i=>$v) {
				echo "<tr><td align=left colspan=2 nowrap><input type=checkbox name=show_".$i.(empty($v)?"":" checked").">"
					//."</td><td>"
					." $i</td></tr>\n";
			}
?>
			</table>
			</small></small>
		</td><td valign=top>
			<table border=0>
			<tr bgcolor="red"><th colspan=4>QTLs - Select Locus</th></tr>
			<tr bgcolor="orange"><th>Name</th><th>Chr</th><th><small>bp From</small></th><th><small>bp To</small></th></tr>
<?php
		include_once("func_public_qtl.php");
		$qtls = get_public_qtls($linkLocal);
		foreach ($qtls as $q) {
			echo "<tr><td><small>".$q["name"]."</small></td>";
			echo "<td align=right><small><a href=locus.php?chrlist=".$q["chr"].">".$q["chr"]."</a></small></td>";
			echo "<td align=right><small><small>".$q["start_bps"]."</small></small></td>";
			echo "<td align=right><small><small>".$q["stop_bps"]."</small></small></td>";
			echo "</tr>\n";
		}
?>
			</table>
		</td>
		</tr>
		</table>
		</form>
<?php
	}
	else {
		require_once("func_dbconfig.php");
		require_once("func_conversion_47.php");
		require_once("func_public_qtl.php"); // sets variable qtls

		foreach (array(
			"show_NumLoci","show_NumEQTLPeak","show_NumEQTLTraitPeak","show_NumEQTLPeak",
			"show_NumEQTLPeakAvg","show_NumEQTL","show_NumEQTL8","show_NumEInteractions",
			"show_NumEInteractionsTrait","show_NumEQTLTraitAvg",
			"cMinDist","cMaxDist","Name","format","locustype","type",
			"order","limit"
			) as $vname) {
			if (isset($_POST[$vname])) {
				$$vname = $_POST[$vname];
			}
			elseif (isset($_GET[$vname])) {
				$$vname = $_GET[$vname];
			}
		}

		$whereB=FALSE;
		$where;
		$from = ' FROM locus AS l ';
		$query = "SELECT l.Name, l.Chr, l.cMorgan ";

		if (empty($cMinDist)) $cMinDist=0;
		if (empty($cMaxDist)) $cMaxDist=0;
		if (!is_numeric($cMinDist)) { array_push($err,"Minimum distance is not numeric ($cMinDist)"); }
		if (!is_numeric($cMaxDist)) { array_push($err,"Maximum distance is not numeric ($cMaxDist)"); }

		if (!empty($Name)) {
			if (is_array($Name)) {
				$names=$Name;
			}
			else {
				// should not be reached
				$names=preg_split("/[;\t \n,]+/",$Name);
			}
			if (count($names)>0) {
				if ( $whereB ) $query .= " AND ";
				else {
					$whereB = TRUE;
					$where = " WHERE ";
				}
				if (1==count($names)) {
					$where .= " l.Name='".$names[0]."'";
				}
				else {
					$where .= " l.Name in ('".join("','",$names)."')";
				}
			}
		}

		if (!empty($chrlist)) {
			if ( $whereB ) $query .= " AND ";
			else {
				$whereB = TRUE;
				$where = " WHERE ";
			}
			$where .= " l.Chr in ('".join("','",preg_split("/[, ;]/",$chrlist))."') ";
		}

		if(!empty($show_qtl)/* && empty($show_Trait)*/) {
			$query .= ", qtl.cnt AS qtl ";
			$from .= " LEFT JOIN (SELECT Locus, COUNT(*) AS cnt FROM qtl GROUP BY Locus) AS qtl ON (l.Name=qtl.Locus) ";
		}

		

		if(!empty($show_locusInteraction)/* && empty($show_Trait)*/ ) {
			$query .= ", li.cnt AS locusInteraction ";
			$from .= " LEFT JOIN ((SELECT A AS loc, COUNT(*) AS cnt FROM locusInteraction GROUP BY A) UNION (SELECT B AS loc, COUNT(*) AS cnt FROM locusInteraction GROUP BY B)) AS li ON (l.Name=li.loc) ";
		}

// 		if(!empty($show_trait)) {
// 			$query .= ", tr.cnt AS trait ";
// 			$from .= "LEFT JOIN ( (SELECT DISTINCT Locus AS loc, COUNT(*) AS cnt FROM qtl GROUP BY loc, Trait)UNION(SELECT A AS loc, COUNT(*) AS cnt FROM locusInteraction GROUP BY A) UNION (SELECT B AS loc, COUNT(*) AS cnt FROM locusInteraction GROUP BY B)) AS tr ON (tr.loc=l.Name)";
// 		}

		if (!empty($cMmin) && !empty($cMmax)) {
			if (!is_numeric($cMmin)) array_push($err,"The lower boundary in cMorgan positions for loci under investigation must be numeric ($cMmin).\n");
			if (!is_numeric($cMmax)) array_push($err,"The upper boundary in cMorgan positions for loci under investigation must be numeric ($cMmax).\n");
			if ($whereB) $query .= " AND ";
			else {
				$whereB = TRUE;
				$where = " WHERE ";
			}
			$where .= " (l.cMorgan BETWEEN ".($cMmin-0.00001)." AND ".($cMmax+0.00001).") ";
		}
		else {
			if (!empty($cMmin)) {
				if (!is_numeric($cMmin)) array_push($err,"The lower boundary in cMorgan positions for loci under investigation must be numeric ($cMmin).\n");
				if ($whereB) $query .= " AND ";
				else {
					$whereB = TRUE;
					$where = " WHERE ";
				}
				$where .= " l.cMorgan >= ".($cMmin)." ";
			}
			if (!empty($cMmax)) {
				if (!is_numeric($cMmax)) array_push($err,"The upper boundary in cMorgan positions for loci under investigation must be numeric ($cMmax).\n");
				if ($whereB) $query .= " AND ";
				else {
					$whereB = TRUE;
					$where = " WHERE ";
				}
				$where .= " l.cMorgan <= ".($cMmax)." ";
			}
		}

		$query .= $from . $where;
		$query .= " GROUP BY l.Name ";

		if (!empty($order)) {
			$query .= " ORDER BY ".$order." ";
		}
		if (!empty($limit)) {
			$query .= " LIMIT ".$limit." ";
		}




		echo $query;



		if (0<count($err)) {
			echo "<p>Please address the following error".(1<count($err)?"s":"").":<br>";
			foreach ($err as $e) {
				echo $e."<br>";
			}
			echo "</p>";
			mysql_close($linkLocal);
			exit;
		}

		$database=$databaseLocal;
		if (!mysql_select_db("$database",$linkLocal)) {
			echo "Could not select database '$database'. "
			. "Send an email to <a href=\"steffen.moeller a t uni-luebeck.de\""
			. ">steffen.moeller a t uni-luebeck.de</a><br>";
			mysql_close($linkLocal);
			exit;
		}

		$result = mysql_query($query,$linkLocal);
		if (empty($result)) {
			echo "<p>Error: ".mysql_error($linkLocal)."</p>";
			mysql_close($linkLocal);
			exit;
		}
		
		$rowno=0;
		if ("html"==$format) {
			if( $show_VennLink ) {
				echo "<form action=\"venn.php\" method=\"get\">";
				echo 	"<p align=\"center\"><table align=\"center\"><tr><th bgcolor=\"yellow\" colspan=\"2\" align=\"center\">Venn Diagram options:<br>creating this diagram may take several minutes!</th></tr><tr>
						<td><input type=\"submit\" value=\"create diagram\"></td>
						<td><input type=\"reset\" value=\"clear form\"></td>
					</tr></p><br>";
			}
			echo "<small><table border=1>\n";
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$rowno++;
				if (1==$rowno) {				
					echo "<thead>\n<tr bgcolor=yellow><th>#</th>";
					foreach($line as $n=>$l) {

// 						echo $n;

						$f="show_".$n;
						if (!empty($$f)) {
							echo "<th><small>$n</small></th>";
							if ("Trait"==$n && !empty($show_LocusOfGene)) {
								echo "<th colspan=3><small>Trait chr,band,start</small></th>";
							}
						}
					}
					echo "<th>bp</th>";
					if( !empty($show_Genes_in_locus) ) {
						echo "<th>Genes at locus</th>";
					}
					if( !empty($show_genes_effected) ) {
						echo "<th>Genes effected</th>";
					}
					if( !empty($show_VennLink) ) {
						echo "<th>Select Group (VennDiagramm)</th>";
					}
					echo "<th>QTLs</th>";
					echo "</tr>\n</thead>\n<tbody>\n";
				}
				echo "<tr><td>$rowno</td>";
				foreach($line as $n=>$l) {
					$f="show_".$n;
					$c=$line["Chr"];
					$locus = $line["Name"];
					$cMorgan=$line["cMorgan"];
					if (!empty($$f)) {
						if (!isset($l)||""==$l) echo "<td>&nbsp;</td>";
						else switch($n) {
						case "Morgan":
							echo "<td align=right>".round($cMorgan,4)."</td>\n";
							break;
						case "Chr":
							echo "<td><a href=\"http://www.ensembl.org/Rattus_norvegicus/mapview?chr=$c\">$l";
							echo " <a href=\"http://www.ensembl.org/Rattus_norvegicus/syntenyview?otherspecies=Homo_sapiens&chr=$c\">Hs</a></td>\n";
							break;
						case "Name":
							echo "<td>"
							."<a href=\"http://www.ensembl.org/Rattus_norvegicus/contigview?chr=$c&vc_start="
		    					.(cM2bp($c,$cMorgan)-100000).'&vc_end='.(cM2bp($c,$cMorgan)+100000);
							echo "\">$l</a></td>\n";
							break;
						case "qtl":
							echo 	"<td align=\"right\">".$line["qtl"]
								." [<a href=\"qtl.php?Locus=".$locus."\">qtl</a> <a href=\"qtl.php?submitted=1&locus=".$locus."&chrlist=&LODmin=0&LODmax=&quantilemin=&quantilemax=&LODdiffmin=0&cM_Peak_Min=&cM_Peak_Max=&cM_within=&traitlist=&order=LOD+DESC&limit=500&show_Trait=on&show_LocusOfGene=on&show_Locus=on&show_LOD=on&show_LODdiff=on&show_Quantile=on&show_Covariates=on&show_Chromosome=on&show_cMorgan_Peak=on&show_cMorgan_Min=on&show_cMorgan_Max=on&show_Analysis=on&show_swissprot_ID=on&show_gene_assignment=on&show_first_symbol=on&show_Definition=on\">d</a>]"
								."</td>";
							break;
						case "locusInteraction":
							echo 	"<td align=\"right\">".$line["locusInteraction"]
								." [<a href=\"interaction_collapsed.php?locus1=".$locus."\">li</a> <a href=\"interaction_collapsed.php?submitted=1&type=&LODmin=0&LODmax=&quantilemin=&quantilemax=&LODdiffmin=0&chrlist=&locus1=".$locus."&locus2=&traitlist=&order=(lod_full-qlod_full)+DESC&limit=150&show_LocusInteraction=on&show_LocusInteractionTrait=on&show_Affected_genes=on&show_Covariates=on&show_lod_full_span=on&show_qlod_full_span=on&show_LOD_Diff=on&show_seqname=on\">d</a>]"
								."</td>";
							break;
						case "trait":
							echo 	"<td align=\"right\">".$line["trait"]
								." <a href=\"trait.php\">link follows</a>"
								."</td>";
							break;
						default:
							if (!isset($l)||""==$l) $l="<td>&nbsp;</td>";
							else echo "<td>$l</td>";
						}
					}
				}
				$bp=cM2bp($line["Chr"],$line["cMorgan"]);
				echo "<td align=right><small><small>$bp</small></small></td>";
				$from = $bp-10000;
				$to = $bp+10000;
				if( !empty($show_Genes_in_locus) ) {
 					$content = file_get_contents($BASEPATH."filehandler.php?genes_within=on&chromosome=".$line["Chr"]."&begin_bp=".$from."&end_bp=".$to );
					$arr = split("\n", $content);
					$link = $line["Chr"]."_".$from."_".$to;
					$arr = file($BASEPATH."filehandler.php?genes_within=on&chromosome=".$line["Chr"]."&begin_bp=".$from."&end_bp=".$to);
					$size = sizeof($arr);
					if( $size <= 5 ) {
						echo "<td align=\"center\"><small><small>";
						$first=true;
						foreach ($arr as $x) {
							if( $first ) {echo $x; $first=false;}
							else { echo "<br>".$x; }
						}
						echo "</small></small></td>";
					} else {
						echo "<td align=\"center\"><small><small>".$size." ".$link."</small></small></td>";
					}
				}
				if( !empty($show_genes_effected) ) {
					echo "<td align=\"center\"><small><small>";
					echo "<a href=\"filehandler.php?trait_genes=on&locus=".$line["Name"]."\">link</a>";
					echo "</small></small></td>";
				}
				if( !empty($show_VennLink) ) {
					$link = $line["Name"];
					echo "	<td>
							1<input type=checkbox name=\"1_".$link."\">
							2<input type=checkbox name=\"2_".$link."\">
							3<input type=checkbox name=\"3_".$link."\">
							4<input type=checkbox name=\"4_".$link."\">
							5<input type=checkbox name=\"5_".$link."\">
						</td>";
				}
				echo "<td>";
				$qs=withinthefollowingqtls($line["Chr"],$bp);
				if (0==count($qs)) {
					echo "&nbsp;";
				}
				else {
					echo join(",",$qs);
				}
				echo "</td>";
				echo "</tr>\n";
			}
			echo "</tbody>\n</table>\n";
			if( $show_VennLink ) {
				echo "</form>";
			}
		}
		else {
			echo "<pre>";
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				echo join("\t",$line)."\n";
			}
			echo "</pre>";
		}
		if (0==$rowno) {
			echo "<p>No records found matching criteria.</p>\n";
		}
		else {
			echo "<p>$rowno record".($rowno>1?"s":"")." found matching criteria.</p>\n";
		}
		echo "</small>\n";
		mysql_free_result($result);
	}
	include("footer.php");
?>
</body>
</html>
