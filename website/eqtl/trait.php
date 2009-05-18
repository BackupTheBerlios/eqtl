<?php
	require_once("header.php");
	show_small_header("Selection of eTraits",TRUE);

	foreach(array("direct",
			"submitted",
			"MeanMin","MeanMax","SdMin","SdMax","MedianMin", "MedianMax", "VarianceMin", "VarianceMax",
			"traitlist", "traits",
			"limit","order") as $vname)
	{
		if (isset($_POST[$vname])) {
			$$vname = $_POST[$vname];
		}
		elseif(isset($_GET[$vname])) {
			$$vname = $_GET[$vname];
		}
	}

	// specification of attributes to be shown in table
// 	$a=array("Trait"=>1, "genes_associated"=>1, "MMSV_data"=>1, "LocusOfGene"=>1, "swissprot_ID"=>1);
	$a=array("Trait"=>1, "Rat_gene_associated"=>1, "Human_ontholog_gene"=>1, "transcript"=>1,
		"mean_sd_variance"=>1,
		#"mean"=>1, "median"=>1, "sd"=>1, "variance"=>1,
		"positive_correlation"=>1,
		"negative_correlation"=>1,
		"phen_correlation"=>1,
#		 "traits_pos_cor"=>1, "traits_pos_cor_rho"=>1,
#		 "traits_pos_cor_most"=>1, "traits_pos_cor_most_rho"=>1,
#		 "traits_neg_cor"=>1, "traits_neg_cor_rho"=>1,
#		 "traits_neg_cor_most"=>1, "traits_neg_cor_most_rho"=>1,
		 "Chromosome"=>1, "start"=>1, "stop"=>1);

	foreach ($a as $vname =>$v) {
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
	if (!empty($traitlist)) {
		if (is_array($traitlist)) {
			$traits=join(",",$traitlist);
		}
		else {
			$traits=$traitlist;
		}
	}

	if (empty($limit)) {
		$limit=10;
	}
		
	if (empty($direct) and empty($submitted)) {
?>
		<form action=trait.php method=get>
		<input type=hidden name=submitted value=1>
		<table><tr><td valign=top>
			<table cellspacing=5>
			<tr><th align=right class=r>Trait ID:</th>
			    <td><input type=text name=traits lenth=70<?php if (!empty($traits)) echo " value=$traits"; ?>>
			    </td>
			</tr>
<!--			<tr><th align=right>Description:</th>
			    <td><input type=text name=description lenth=70>
			    </td>
			</tr>-->
			<tr><th align=right>Mean:</th>
						<td>
							<input type=text name=MeanMin size=7>
						-
							<input type=text name=MeanMax size=7>
						</td></tr>
			<tr><th align=right>Median:</th>
						<td>
							<input type=text name=MedianMin size=7>
						-
							<input type=text name=MedianMax size=7>
						</td></tr>
			<tr><th align=right>Sd:</th>
						<td>
							<input type=text name=SdMin size=7>
						-
							<input type=text name=SdMax size=7>
						</td></tr>
			<tr><th align=right>Variance:</th>
						<td>
							<input type=text name=VarianceMin size=7>
						-
							<input type=text name=VarianceMax size=7>
						</td></tr>
			<tr><th align=left>Correlation</th>
						<td>
							<i>To be implemented:</i> 
							Positive: <input type=text name=traits_pos_cor_most size=7>
							<br>
							Negative: <input type=text name=traits_pos_cor_most size=7>
						</td>
			<tr><th align=right>order by:</th><td>
						<select name=order>
						<option value=probeset_id>Trait Number</option>
						<option value="mean DESC">Mean</option>
						<option value="median DESC">Median</option>
						<option value="sd DESC">Standard deviation</option>
						<option value="variance DESC">Variance</option>
						</select>
						</td></tr>
			<tr><th align=right>Limit lines shown:</th><td><input type=t_id limit 150 ext name=limit value=150></td></tr>
			<tr><td>&nbsp;</td><td></td></tr>
			<tr><td class=r><input type=submit></td><td align=left><input type=reset></td></tr>
			</table>
		</td><td align=center>
			<small><small>
			<table border=0>
			<tr><th bgcolor=black align=left><font color=orange><small>Show Field</small></font></th></tr>
<?php
			foreach($a as $i=>$v) {
				echo "<tr><td align=left><input type=checkbox name=show_"
					.$i.(empty($v)?"":" checked").">$i</td></tr>\n";
			}
?>
			</table>
		</tr>
		</table>
		</form>
<?php
	}
	else {
		require_once("func_connect.php");
		require_once("func_species.php");

		if (empty($linkLocal)) {
			echo "<p>Could not create link to database.</p>";
			exit;
		}
		
		$whereB=FALSE;
		$where = 'WHERE ';
		$from = 'FROM BEARatChip ';
		$query  = "SELECT probeset_id AS Trait ";
		

		$joinedTrait = false;

		if( !empty($show_swissprot_ID) ) {
			$query .= ", swissprot_ID ";
		}

		foreach ($a as $n=>$v) {
			$showname="show_$n";
			#echo "$showname<br>\n";
			if (!empty($$showname)) {
				if ("Chromosome" == "$n") {
				}
				else if ("Trait" == "$n") {
				}
				else if("Rat_gene_associated" == "$n") {
					$query .= ", gene_stable_id_rat as Rat_gene_associated";
				}
				else if ("Human_ontholog_gene" == "$n") {
					$query .= ", hum_onth_ens as Human_ontholog_gene";
				}
				else if("transcript" == "$n") {
					$query .= ", gene_assignment AS transcript ";
				}
				else if ("mean_sd_variance"=="$n") {
					$query .= ",mean,sd,median,variance";
				}
				else if ("positive_correlation"=="$n") {
					$query .= ",traits_pos_cor, traits_pos_cor_rho";
					$query .= ",traits_pos_cor_most, traits_pos_cor_most_rho";
				}
				else if ("negative_correlation"=="$n") {
					$query .= ",traits_neg_cor, traits_neg_cor_rho";
					$query .= ",traits_neg_cor_most, traits_neg_cor_most_rho";
				}
				else if ("phen_correlation"=="$n") {
				# the one-to-many relationship cannot be reasonably well
				# resolved for the display of the data. Instead, a second
				# query will be performed.
				#	$query .= ",trait_phen_cor.phen";
				#	$query .= ",trait_phen_cor.rho";
				#	$query .= ",trait_phen_cor.p";
				#	$from  .= " left join trait_phen_cor using(trait_id) ";
				}
				else {
					$query .= ", $n";
				}
				if ("transcript"=="$n" or "Chromosome"=="$n"
				      or "start"=="$n" or "stop"=="$n")
				{
					if( !$joinedTrait ) {
						$from .= " LEFT JOIN trait ON (trait.trait_id=BEARatChip.probeset_id) ";
						$joinedTrait = true;
					}
				}
			}
		}

		if (!empty($traits)) {
			$traitsArray=preg_split("/[, \t\n]+/",$traits);
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " BEARatChip.probeset_id IN ('".join("','",$traitsArray)."') ";
		}

		if( !empty($MedianMin) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " median >= ".$MedianMin;
		}

		if( !empty($MedianMax) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " median <= ".$MedianMax;	
		}

		if( !empty($MeanMin) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " mean >= ".$MeanMin;	
		}

		if( !empty($MeanMax) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " mean <= ".$MeanMax;
		}

		if( !empty($SdMin) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " sd >= ".$SdMin;
		}

		if( !empty($SdMax) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " sd <= ".$SdMax;
		}

		if( !empty($VarianceMin) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " variance >= ".$VarianceMin;
		}

		if( !empty($VarianceMax) ) {
			if ($whereB) $where .= " AND ";
			else {
				$whereB = TRUE;
			}
			$where .= " variance <= ".$VarianceMax;
		}


		if( !$whereB ) {
			$query .= " " . $from;
		} else {
			$query .= " " . $from .$where;
		}
// 		$query  .= " group by trait_id, name, mean, sd";

		if (!empty($order)) {
			$query .= " ORDER BY ".$order." ";
		}
		if (!empty($limit)) {
			$query .= " LIMIT ".$limit." ";
		}

		echo "query: $query<br>";

		$result = mysql_query($query,$linkLocal);
		if (empty($result)) {
			echo "<p>".mysql_error($linkLocal)."</p>";
			mysql_close($linkLocal);
			exit;
		}
		$firstRow=true;
		echo "<small><table border=1>\n";
		while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if ($firstRow) {
				$firstRow=FALSE;
				echo "<tr bgcolor=yellow>";
				foreach($line as $n=>$l) {
					$f="show_".$n;
					if (!empty($$f)) {
						echo "<th><small>$n</small></th>";
						if ("LocusOfGene"==$n && !empty($show_LocusOfGene)) {
							echo "<th colspan=3><small>Locus chr,start,stop</small></th>";
						}
					}
					else if("traits_pos_cor"=="$n") {
						echo "<th>positive correlation</th>\n";
					}
					else if("traits_neg_cor"=="$n") {
						echo "<th>negative correlation</th>\n";
					}
				}
				if (!empty($show_phen_correlation)){
					echo "<th>phen correlation</th>\n";
				}
				if (!empty($show_mean_sd_variance)) {
					echo "<th>Stats</th>\n";
				}
// 				echo "<td>Images</td>\n";
				echo "</tr>\n";
			}
			echo "<tr>";
			$traitid="";
			foreach($line as $n=>$l) {
				$f="show_".$n;
				if (!empty($$f)) {
// 					echo "$n => $l\t!\t";
					if (!isset($l)||""==$l) echo "<td>&nbsp;</td>";
					else switch($n) {
					case "liNo":
					case "liA":
					case "liB":
					case "AlNo":
					case "BlNo":
					case "AlName":
					case "BlName":
						break;
					case "Trait":
						$traitid=$l;
						echo "<td align=right nowrap>"
						."<a href=\"".probe2ensemblUrl($l,$species_name_ensembl_core)
						."\">$l</a> ["
						."<a href=\"qtl.php?traitlist=$l\">q</a>"
						."<a href=\"interaction.php?traitlist=$l&type=X\">i</a>"
						."<a href=\"interaction.php?traitlist=$l&type=Y\">j</a>"
						."]</td>";
							break;
// 					case "LocusOfGene":
// 						echo "<td>".$line["Chr"]."</td><td>".$line["start"]."</td><td>".$line["stop"]."</td>";
// 						break;
					case "transcript":
						$b=explode(".",$l);
						echo "<td><a href=\""
							.gene2ensemblUrl($b[0],$species_name_ensembl_core)
							."\">$l</a></td>";
						break;
// 					case "MMSV_data":
// 						echo "<td>".$line["mean"]."</td><td>".$line["median"]."</td><td>".$line["sd"]."</td><td>".$line["variance"]."</td>";
// 						break;
// 					case "":
// 						echo "</td>".$line["gene_stable_id_rat"]."</td><td>".$line["hum_onth_ens"]."<td>";
// 						break;
					default:
						if (!isset($l)||""==$l) $l="<td>&nbsp;</td>";
						else echo "<td>$l</td>";
					}
				}
				else if("traits_pos_cor"==$n) {
					$traitsPos=preg_split("/,/",$l);
					$traitsPosRho=preg_split("/,/",$line["traits_pos_cor_rho"]);
					echo "<td valign=top>";
					foreach($traitsPos as $tp=>$tv) {
						if (0 < $tp) echo ", ";
						#echo "<a href=\"".probe2ensemblUrl($tp,$species_name_ensembl_core) . "\">$tv</a>";
						echo "<a href=\"trait.php?traits=$tv\">$tv</a>";
						echo " (".round($traitsPosRho[$tp],2).")";
						if (9 < $tp) break;
					}
					echo "</td>\n";
				}
				else if("traits_neg_cor"==$n) {
					$traitsNeg=preg_split("/,/",$l);
					$traitsNegRho=preg_split("/,/",$line["traits_neg_cor_rho"]);
					echo "<td valign=top>";
					foreach($traitsNeg as $tp=>$tv) {
						if (0 < $tp) echo ", ";
						#echo "<a href=\"".probe2ensemblUrl($tp,$species_name_ensembl_core) . "\">$tv</a>";
						echo "<a href=\"trait.php?traits=$tv\">$tv</a>";
						echo " (".round($traitsNegRho[$tp],2).")";
						if (9 < $tp) break;
					}
					echo "</td>\n";
				}
			}
			if (!empty($show_phen_correlation)){
				echo "<td valign=top>";
				if (empty($traitid)) {
					echo "<i>No trait specified in query.</i>";
				}
				else {
					$phenquery  = "SELECT";
					$phenquery .= " trait_phen_cor.phen";
					$phenquery .= ",trait_phen_cor.rho";
					$phenquery .= ",trait_phen_cor.p";
					$phenquery .= " FROM trait_phen_cor";
					$phenquery .= " WHERE trait_id = '$traitid'";
					$phenquery .= "   AND p<=0.05";
					$phenquery .= " ORDER BY p";
					$resultPhen = mysql_query($phenquery,$linkLocal);
					if (empty($resultPhen)) {
						echo "<p>".mysql_error($linkLocal)."</p>";
						mysql_close($linkLocal);
						exit;
					}
					$firstPhen=true;
					while ($linePhen = mysql_fetch_array($resultPhen,
									MYSQL_ASSOC)) {
						if($firstPhen){
							$firstPhen=FALSE;
							#print_r($line);
						}
						else{
							echo ", ";
						}
						echo "<b>".$linePhen["phen"]."</b>"
							." &rho;".round($linePhen["rho"],3)
							." <i>p</i>".round($linePhen["p"],4);

					}
					mysql_free_result($resultPhen);
				}
				echo "</td>\n";
			}
			if (!empty($show_mean_sd_variance)) {
				echo "<td>mean: ".$line["mean"]."<br/>\nsd: ".$line["sd"]."</td>\n";
			}
// 			echo "<td>"
// 			 . "<a href=\"images/".$line["Trait"]."_onescan.pdf\">one</a>"
// 			 ." <a href=\"images/".$line["Trait"]."_nosex_twoscan.pdf\">two</a>"
// 			 ." <a href=\"images/".$line["Trait"]."_sex_twoscan.pdf\">two-sex</a>"
// 			 ."</td>\n";
			echo "</tr>\n";
		}
		echo "</table></small>";
		mysql_free_result($result);
		mysql_close($linkLocal);
	}
	include("footer.php");
?>
    </body>
</html>
