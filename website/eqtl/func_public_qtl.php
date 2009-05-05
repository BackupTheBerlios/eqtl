
<?php

	require_once("func_dbconfig.php");
	global $databaseqtl;
	require_once("func_species.php");

/*
	//echo "Connecting as $usernameqtl to $hostnameqtl<br>\n";
	$linkqtl=mysql_connect($hostnameqtl,$usernameqtl,"");

	if (empty($linkqtl)) {
		echo "<p>Could not create link to database.</p>";
		echo "Send an email to <a href=\"$maintainerEmail\">$maintainerEmail</a><br>";
		echo "</body></html>";
		exit;
	}

	if (!mysql_select_db($databaseqtl,$linkqtl)) {
		echo "<p>Could not open database to access qtl.</p>\n";
		echo "</body></html>";
		exit;
	}
*/

	$qtlsCache="";

	/**
	 * Function to return an internal representation of QTLs that are publicly
	 * discussed.
	 */
	function get_public_qtls($dbh) {

		global $databaseqtl;
		global $species_name_ensembl_core;

		if ("" != $qtlsCache) {
			return($qtlsCache);
		}

		$query="select name,chr,start_bps,stop_bps from $databaseqtl.eae_qtl where species='$species_name_ensembl_core'";
		#print_r($query);
/*
	+-------+-----+-----------+-----------+
	| name  | chr | start_bps | stop_bps  |
	+-------+-----+-----------+-----------+
	| EAE6a | 11  |   2600000 |  25200000 |
	| EAE1  | 17  |  32550000 |  37120000 |
	| EAE2  | 15  |   9700000 |  39700000 |
	| EAE3  | 3   |  89700000 | 119800000 |
	| EAE4  | 7   |  40300000 |  99600000 |
	| EAE5  | 17  |  37100000 |  45400000 |
	| EAE6b | 11  |  38500000 |  55100000 |
	| EAE7  | 11  |  76800000 |  92400000 |
	| EAE8  | 2   | 176000000 | 180000000 |
	| EAE9  | 9   |  45100000 |  69000000 |
	| EAE10 | 3   | 126200000 | 155400000 |
	| EAE11 | 16  |  27800000 |  75900000 |
	| EAE12 | 7   |  17000000 |  30800000 |
	| EAE13 | 13  |  53300000 |  69800000 |
	| EAE14 | 8   |  18100000 |  71800000 |
	| EAE15 | 10  |  11000000 |  28300000 |
	| EAE16 | 12  |  10000000 |  70000000 |
	| EAE17 | 10  |  25900000 |  94800000 |
	| EAE18 | 18  |         0 |         0 |
	| EAE19 | 19  |  26100000 |  57700000 |
	| EAE20 | 3   |   8300000 |  51200000 |
	| EAE21 | 2   |  41200000 | 125800000 |
	| EAE22 | 11  |  81300000 | 119200000 |
	| EAE23 | 11  |  53700000 |  80600000 |
	| EAE   | 17  |  33810000 |  33820000 |
	| EAE24 | 8   |  17300000 |  41300000 |
	| EAE25 | 18  |  61500000 |  83000000 |
	| EAE26 | 7   |  98500000 | 106100000 |
	+-------+-----+-----------+-----------+
*/

		$result = mysql_query($query,$dbh);
		if (empty($result)) {
			errorMessage(mysql_error($dbh)."</p><p>".$query."</p>");
			mysql_close($dbh);
			echo "</body></html>\n";
			exit;
		}

		$qtls=array();
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			#print_r($line);
			$qtls[$line["name"]]=$line;
		}

		//mysql_close($linkqtl);
		$qtlsCache = $qtls;
		return($qtls);
	}
	

	/**
	 * Returns subset of QTLs that are overlapping with a particular
	 * chromosomal location.
	 */
	function withinthefollowingqtls($chr,$peakbp,$qtlsSelection) {

		if (!is_array($qtlsSelection)) {
			errorMessage("withinthefollowingqtls: qtlsSelection is not an array: '$qtlsSelection'.\n");
		}

		if (empty($chr)) {
			echo "<p>withinthefollowingqtls: first argument is empty</p>";
		}
		if (empty($peakbp)) {
			echo "<p>withinthefollowingqtls: second argument is empty</p>";
		}
		$qs=array();
		foreach($qtlsSelection as $n=>$q) {
			$chrqtl=$q["chr"];
			#echo "<p>$chrqtl, $chr</p>";
			if ($q["chr"]==$chr) {
				if ($peakbp>=$q["start_bps"] && $peakbp<=$q["stop_bps"]) {
					array_push($qs,$n);
				}
			}
		}
		return($qs);
	}
?>
