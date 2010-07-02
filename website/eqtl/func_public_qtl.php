
<?php

/*

=head1 NAME

func_public_qtl.php - retrieval of classical QTL from local database

=head1 SYNOPSIS

to be included from PHP scripts

=head1 DESCRIPTION

The classical QTL are used for the filtering of expression QTL. This file
provides the functionality to present a list of classical QTL on the web
pages.

=cut

*/

	require_once("func_dbconfig.php");
	global $databaseqtl;
	require_once("func_species.php");

	# variable to store results and thus possibly reduce database IS
	$qtlsCache="";
	$qtlsCacheByChromosome="";

/**

=head2 get_public_qtls

Function to return an internal representation of QTLs that are publicly
discussed.

Attributes:

=over 4

=item dbh

database handle from which to read the QTLs

Specify "chromosome" as a second argument to retrieve 
an index by chromosome, "name" otherise (default).

=back

=cut

 */
	function get_public_qtls($dbh,$order="name") {

		global $databaseqtl;
		global $species_name_ensembl_core;
		global $qtlsCache,$qtlsCacheByChromosome;

		if ("" != $qtlsCache) {
			echo "<p>get_public_qtls: Returning cached value.</p>";
			if ("chromosome"=="$order") {
				return($qtlsCacheByChromosome);
			}
			else {
				return($qtlsCache);
			}
		}

		$query="SELECT name,chr,start_bps,stop_bps FROM $databaseqtl.eae_qtl WHERE species='$species_name_ensembl_core' ORDER BY chr,start_bps";
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
		$qtls_by_chromosome=array();
		while($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
			#print_r($line);
			$qtls[$line["name"]]=$line;
			if (empty($qtls_by_chromosome[$line["chr"]])) {
				$qtls_by_chromosome[$line["chr"]]=array();
			}
			$qtls_by_chromosome[$line["chr"]][]=$line;
		}

		$qtlsCache = $qtls;
		#echo "<br>qtlsCache: "; print_r($qtlsCache);

		$qtlsCacheChromosome = $qtls_by_chromosome;
		#echo "<br>qtlsCacheChromosome: "; print_r($qtlsCacheChromosome);

		if ("chromosome"=="$order") {
			return($qtlsCacheChromosome);
		}
		else {
			return($qtlsCache);
		}
	}
	

/**

=head2 withinthefollowingqtls

Returns subset of QTLs that are overlapping with a particular chromosomal location.

Attributes:

=over 4

=item chr

chromosome at which the QTLs should reside

=item peakbp

peak basepair position that should be covered by the QTL

=item qtlsSelection

the list of QTLs from which the fitting ones shall be selected

=back

=cut

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


/*

=head2 select_from_public_qtls

Prepares the table to collect QTLs from in the various input forms to specify filter attributes.

=cut

*/

	function select_from_public_qtls($dbh,$checkboxes=FALSE) {
		$qtlsByC = get_public_qtls($dbh,"chromosome");
		#echo "---------<p>qtlsByC:"; print_r($qtlsByC); echo "</p> ---------";
		echo "<table>";
		$orderOfChromosomes = list_chromosomes();
		foreach ($orderOfChromosomes as $c) {
			if (empty($qtlsByC["$c"])) {
				continue;
			}
			$qtls = $qtlsByC["$c"];
			# echo "<p>qtls:"; print_r($qtls); echo "</p>";
			foreach ($qtls as $q) {
				echo "<tr><td>";
				if ($checkboxes) echo "<input name=\"cqtl[]\" type=\"checkbox\" value=\"".$q["name"]."\" />";
				echo "<small>".$q["name"]."</small></td>";
				echo "<td align=right><small><a href=qtl.php?chrlist=".$q["chr"].">".$q["chr"]."</a></small></td>";
				echo "<td align=right><small><small>".$q["start_bps"]."</small></small></td>";
				echo "<td align=right><small><small>".$q["stop_bps"]."</small></small></td>";
				echo "</tr>\n";
			}
		}
		echo "</table>";
	}


/*

=head2 list_chromosomes

retrieve ordered array of chromosomes

=cut

*/

function list_chromosomes() {
	$orderOfChromosomes = array();
	for($i=1; $i<=20; $i++) {
		$orderOfChromosomes[]="$i";
	}
	$orderOfChromosomes[]="X";
	return($orderOfChromosomes);
}

/*

=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2008-2009

=cut

*/

?>
