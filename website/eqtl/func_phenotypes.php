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

function list_phenotypes($dbh) {

	$queryPhenotypes = "SELECT DISTINCT phen FROM trait_phen_cor";
	$queryPhenotypes .= " LIMIT 1000"; # just to be on the save side

	$result = mysqli_query($dbh,$queryPhenotypes);
	if (empty($result)) {
		mysqli_close($dbh);
		errorMessage("Error: " . mysqli_error($dbh)."</p><p>".$queryPhenotypes."</p>");
		//echo "LinkLocal: "; print_r($linkLocal);
		exit;
	}

	$phenotypes=array();
	while ($line = mysqli_fetch_array($result, MYSQL_ASSOC)) {
		array_push($phenotypes,$line["phen"]);
	}
	mysqli_free_result($result);

	return($phenotypes);

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
