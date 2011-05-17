<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 writeQTL-table.php -

 =head1 SYNOPSIS

 =head1 DESCRIPTION

 =head1 AUTHOR

 Michael Brehler <brehler@informatik.uni-luebeck.de>,
 Georg Zeplin <zeplin@informatik.uni-luebeck.de>,

 =head1 COPYRIGHT

 University of LE<uuml>beck, Germany, 2011

 =cut

 ENDOFDOCUMENTATION
 */

require_once 'db_functions.php';

require_once 'analysis/func_analysis.php';

$fptr = fopen('analysis/QTL.txt', 'w');

echo "a";
$targetdb = connectToQTL();
echo "a";
$comparaDB = connectToCompara();
echo "a";
$table = get_all_QTL($targetdb);
echo "a";

$str = "start_bps\t\tstop_bps\t\tChr\t\tspecies\t\tsyntenyID\r\n";

foreach ($table as $row) {
	if ($row[0] != NULL && $row[1] != NULL && $row[2] != NULL && $row[3] != NULL) {
		$tmp = getSyntenyRegionIDs($comparaDB,$row);
		$str .= $row[0]."\t\t".$row[1]."\t\t".$row[2]."\t\t".$row[3]."\t\t".$tmp."\n";
	}
}


fwrite($fptr, $str);


fclose($fptr);

?>
