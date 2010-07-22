<?php

/**

=head1 NAME

func_phenotypes.php - helper routines to retrieve phenotype information

=head1 SYNOPSIS

functionality on retrieving phenotype data with a recurrent use

=head1 DESCRIPTION

THe classical phenotypes are frequently used as covariates, but
they also have a life ontheir own.

=cut

/


**

=head2 list_phenotypes

=over 4

=item $dbh

data base handle to the expression QTL table with the phenotype correlations

=back

=cut


*/

function list_markers() {

	$queryMarkers = "SELECT DISTINCT marker,chr,cmorgan_rqtl as cM FROM map ORDER BY chr,cM";

	$result = mysql_query($queryMarkers);
	if (empty($result)) {
		mysql_close($linkLocal);
		errorMessage("Error: " . mysql_error()."</p><p>".$queryPhenotypes."</p>");
		//echo "LinkLocal: "; print_r($linkLocal);
		exit;
	}

	$markers=array();
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$markers[$line["marker"]]=$line["marker"]." ".$line["chr"].":".$line["cM"];
	}
	mysql_free_result($result);

	return($markers);

}
	
/*

=head1 AUTHOR

Steffen ME<ouml>ller <steffen.moeller@uk-sh.de>

=head1 COPYRIGHT

Universities of Rostock and LE<uuml>beck, Germany, 2003-2009

University Clinics of Schleswig-Holstein, Germany, 2010

=cut

*/

?>
</body>
</html>

