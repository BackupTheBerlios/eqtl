<?php
	header("Content-Type: text/plain");
	$accessible_fields_in_POST_or_GET = array(
							"chromosome",
							"cM",
							"begin_bp",
							"end_bp",
							"locus",
							"genes_within",
							"trait_genes",
							"scope",
							"get_bp",
						);

	$break = Explode('/', $_SERVER['PHP_SELF']);
	$pfile = $break[count($break) - 1];
	$tmp = split($pfile, $_SERVER['PHP_SELF']);
	$BASEPATH = "http://".$_SERVER['SERVER_NAME'].$tmp[0];

	foreach($accessible_fields_in_POST_or_GET as $vname)
	{
		if (isset($_POST[$vname])) {
			$$vname = $_POST[$vname];
		}
		elseif(isset($_GET[$vname])) {
			$$vname = $_GET[$vname];
		}
	}

	if( empty($scope) ) {
		$scope=10000;
	}

	if( empty($genes_within) && empty($trait_genes) && empty($get_bp) ) {
		echo "Either [genes_within=on] or [trait_genes=on] or [get_bp=on] is required as argument";
		exit;
	}

	$begin=-1;
	$end=-1;
	if( empty( $locus ) ) {
		if( empty($chromosome) || ( empty($cM) && (empty($begin_bp) || empty($end_bp))) ) {
			echo "Not enough data<br>parameters required: [locus] | [chromosome && [cM || [begin_bp && end_bp]]]";
			exit;
		} else {
			if( !empty($begin_bp) && !empty($end_bp) ) {
				$begin = $begin_bp;
				$end = $end_bp;
			} else {
				require_once("func_conversion_47.php");
				$begin = max(1,cM2bp($chromosome, $cM)-$scope);
				$end = cM2bp($chromosome, $cM)+$scope;
			}
			if( !empty($get_bp) ) {
				$out = ($begin+$end)/2;
				echo $out;
				exit;
			}
		}
	}

// 	echo $begin."\n".$end."\n".$scope."\n";
	
	if( !empty($genes_within) ){
		if( !empty($locus) ) {
			require_once("func_dbconfig.php");
			require_once("func_connect.php");
			
			$query = "SELECT Chr, cMorgan FROM locus WHERE Name=\"".$locus."\"";
	
			if (0<count($err)) {
				echo "<p>Please address the following error".(1<count($err)?"s":"").":<br>";
				foreach ($err as $e) {
					echo $e."<br>";
				}
				echo "</p>";
				mysql_close($link);
				exit;
			}
	
			$database=$databaseLocal;
			if (!mysql_select_db("$database",$link)) {
				echo "Could not select database '$database'. "
				. "Send an email to <a href=\"steffen.moeller a t uni-luebeck.de\""
				. ">steffen.moeller a t uni-luebeck.de</a><br>";
				mysql_close($link);
				exit;
			}
	
			$result = mysql_query($query,$link);
			if (empty($result)) {
				echo "<p>Error: ".mysql_error($link)."</p>";
				mysql_close($link);
				exit;
			}
			$first=true;
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if( $line["Chr"] != NULL && $line["Chr"] != "" ) {
					$chromosome = $line["Chr"];
					$cM = $line["cMorgan"];
					@readfile($BASEPATH."filehandler.php?chromosome=".$chromosome."&cM=".$cM."&genes_within=on&scope=".$scope);
				} else {
					echo "No such locus";
					exit;
				}
			}
			mysql_free_result($result);
			mysql_close($link);
		} else {
			@readfile($BASEPATH."genes_within.php?submitted=submitted&chr=".$chromosome."&bpFrom=".$begin."&bpTo=".$end."&ensemblhost=martdb.ensembl.org%3A3316&ensembluser=anonymous&ensemblorganism=rnorvegicus&ensemblversion=47&markerhost=pc13.inb.uni-luebeck.de&markeruser=qtl&markerdatabase=eQTL_Stockholm&plain=on");
		}
	}
	if( !empty($trait_genes) ) {
		if( !empty( $locus ) ) {
			require_once("func_dbconfig.php");
			require_once("func_connect.php");
			
			$query = "SELECT DISTINCT qtl.Locus,  bea.gene_stable_id_rat FROM ((SELECT Trait, Locus FROM qtl WHERE Locus=\"".$locus."\") UNION (SELECT Trait, A AS Locus FROM locusInteraction WHERE A=\"".$locus."\") UNION (SELECT Trait, B AS Locus FROM locusInteraction WHERE B=\"".$locus."\")) AS qtl LEFT JOIN (SELECT gene_stable_id_rat, probeset_id FROM BEARatChip) AS bea ON (bea.probeset_id=qtl.Trait)";
	
			if (0<count($err)) {
				echo "<p>Please address the following error".(1<count($err)?"s":"").":<br>";
				foreach ($err as $e) {
					echo $e."<br>";
				}
				echo "</p>";
				mysql_close($link);
				exit;
			}
	
			$database=$databaseLocal;
			if (!mysql_select_db("$database",$link)) {
				echo "Could not select database '$database'. "
				. "Send an email to <a href=\"steffen.moeller a t uni-luebeck.de\""
				. ">steffen.moeller a t uni-luebeck.de</a><br>";
				mysql_close($link);
				exit;
			}
	
			$result = mysql_query($query,$link);
			if (empty($result)) {
				echo "<p>Error: ".mysql_error($link)."</p>";
				mysql_close($link);
				exit;
			}
			$first=true;
			while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if( $line["gene_stable_id_rat"] != NULL ) {
					if($first) {
						echo $line["gene_stable_id_rat"];
						$first=false;
					} else {
						echo "\n".$line["gene_stable_id_rat"];
					}
				}
			}
			mysql_free_result($result);
			mysql_close($link);
		} else {
			echo "trait_genes does not accept [chromosome + position] as argument but requires [locus]";
			exit;
		}
	}
?>
