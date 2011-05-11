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

function connectToQTL() {
	$targetdb = @new mysqli("localhost", "anonymous", "no", "qtl");
	if (mysqli_connect_errno()) {
		fatal_error('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
	}
	return $targetdb;
}

function get_all_QTL($db) {
	$sql = 'select start_bps, stop_bps, Chr, species from eae_qtl;';

	$query = $db->query($sql) or fatal_error('Query failed: '.$db->error);

	$QTL_table = $query->fetch_all();

	$query->close();

	return $QTL_table;
}

function getSyntenyRegionIDs($db, $bp){
	if ($bp[3] == 'Mus_musculus') {
		$genome_db_id = 57;
	}elseif ($bp[3] == 'Rattus_norvegicus'){
		$genome_db_id = 3;
	}elseif ($bp[3] == 'Homo_sapiens') {
		$genome_db_id = 90;
	}

	$sqlDnafrag = 'SELECT dfr.synteny_region_id FROM dnafrag_region as dfr INNER JOIN 
	dnafrag as df ON (dfr.dnafrag_start <= '.$bp[1].' AND 
	dfr.dnafrag_end >= '.$bp[0].' AND 
	dfr.dnafrag_id = df.dnafrag_id AND 
	df.name = "'.$bp[2].'" AND 
	df.genome_db_id = '.$genome_db_id.');';

	$fragQuery = $db->query($sqlDnafrag) or fatal_error('Query failed: '.$db->error);

	//$frag_table = $fragQuery->fetch_all();
	$str = "";
	$first = true;
		while ($row = $fragQuery->fetch_assoc()) {
			if ($first) {
				$str .= $row['synteny_region_id'];
				$first = false;
			}else {
				$str .= ",".$row['synteny_region_id'];
			}
		}
	
	$fragQuery->close();

	return $str;
}


$fptr = fopen('analysis/QTL.txt', 'w');

$targetdb = connectToQTL();
$comparaDB = connectToCompara();
$table = get_all_QTL($targetdb);

$str = "start_bps\t\tstop_bps\t\tChr\t\tspecies\t\tsyntenyID\r\n";

foreach ($table as $row) {
	if ($row[0] != NULL && $row[1] != NULL && $row[2] != NULL && $row[3] != NULL) {
		$tmp = getSyntenyRegionIDs($comparaDB,$row);
		$str .= $row[0]."\t\t".$row[1]."\t\t".$row[2]."\t\t".$row[3]."\t\t".$tmp."\n";
	}
}


fwrite($fptr, $str);




?>
