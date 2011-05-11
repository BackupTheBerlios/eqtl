<?php
require_once 'utils.php';

function connectToQTL() {
	$targetdb = @new mysqli("127.0.0.1", "anonymous", "no", "qtl");
	if (mysqli_connect_errno()) {
		fatal_error('Could not connect to QTL-DB: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
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

/**
 * 
 * Enter description here ...
 * @param unknown_type $db
 * @param array $bp start ende chr ens_species 
 */
function getSyntenyRegionIDs($db, $bp){
	$species = strtolower($bp[3]);
	if ($species == 'mus_musculus') {
		$genome_db_id = 57;
	}elseif ($species == 'rattus_norvegicus'){
		$genome_db_id = 3;
	}elseif ($species == 'homo_sapiens') {
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
