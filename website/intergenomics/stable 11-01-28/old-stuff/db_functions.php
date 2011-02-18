<?php
require_once 'bp2cM_conversion.php';

/**
 * Returns the name of the chomosome determined by dnafrag_id "id" from compara-connection "db".
 *
 * @param unknown_type $db compara-connection
 * @param unknown_type $id dnafrag_id
 */
function getSpeciesName($db,$id) {
	$sqlSpeciesName = 'select name
	from genome_db 
	where genome_db_id =(
		select genome_db_id 
		from dnafrag 
		where dnafrag_id='.$id.');';
	$resultSpeciesName =  $db->query($sqlSpeciesName)or trigger_error('Query failed: '.$db->error);
	$rowsSpeciesName = $resultSpeciesName->fetch_all();
	return $rowsSpeciesName[0][0];
}

/**
 * Returns the parameters coord_system_name, name, length of the chomosome
 * determined by dnafrag_id "id" from compara-connection "db" in an associative array.
 *
 * @param $db compara-connection
 * @param $id dnafrag_id
 */
function getDnafragParameter($db,$id){
	$sqlSpeciesPara = 'select coord_system_name, name,length from dnafrag where dnafrag_id ='.$id.';';
	$resultSpeciesPara =  $db->query($sqlSpeciesPara)or trigger_error('Query failed: '.$db->error);
	$rowsSpeciesPara = $resultSpeciesPara->fetch_assoc();
	return $rowsSpeciesPara;
}
/**
 * XXX continue
 * @param unknown_type $db
 * @param unknown_type $name
 */
function getChromosomsAndLengths($db,$name){
	$sqlChromosoms = 'select d.name, d.length from dnafrag as d 
	inner join genome_db as g on(
		g.genome_db_id = d.genome_db_id 
		and g.genome_db_id = "'.$name.'"  
		AND d.coord_system_name = "chromosome");';
	$resultChromosoms =  $db->query($sqlChromosoms)or trigger_error('Query failed: '.$db->error);
	if(!$resultChromosoms->num_rows){
		warn('getChromosoms(): No cromosomes found to species '.$name.'!');
		return array();
	}
	$chrs = array();
	while ($row = $resultChromosoms->fetch_assoc()){
		$chrs[$row['name']] = $row['length'];
	}
	return $chrs;
}

function getChromosoms_old($db,$name){
	$sqlChromosoms = 'select name from dnafrag where genome_db_id =(select genome_db_id from genome_db where name="'.$name.') AND CHAR_LENGTH(name) < 3;';
	$resultChromosoms =  $db->query($sqlChromosoms)or trigger_error('Query failed: '.$db->error);
	$rowsChromosoms = $resultChromosoms->fetch_all();
	return $rowsChromosoms;
}

/**
 * Returns the names of all specias from compara-connection "db".
 *
 * @param unknown_type $db compara-connection
 */
function getAllSpeciesNames($db){
	$sqlSpecies = 'select name from genome_db group by name;';
	$speciesQuery = $db->query($sqlSpecies) or trigger_error('Query failed: '.$db->error);
	$species = $speciesQuery->fetch_all();
	return $species;
}
/**
 * Switches the current database to $name.
 * @param unknown_type $name
 * @param unknown_type $db
 */
function useDB($name, $db){
	$sql = 'use '.$name.';';
	$db->query($sql)or
	trigger_error('Could not use database '.$name.' ('.$db->error.')');
}

/**
 * returns a connection to the compara database (default Port: 5306)
 * @param $port (default 5306)
 */
function connectToCompara($port = '3306', $local=false) {
	if ($local) {
		$db = @new mysqli('127.0.0.1', 'anonymous', 'no', 'ensembl_compara_59', $port);
	}else{
		if($port=='5306'){
			$database = 'ensembl_compara_57';
		}else{
			$database = 'ensembl_compara_47';
		}
		$db = @new mysqli('ensembldb.ensembl.org', 'anonymous', '', $database, $port);
	}
	if (mysqli_connect_errno()) {
		trigger_error('Could not connect to database: '.mysqli_connect_error().'('.
		mysqli_connect_errno().')', E_USER_ERROR);
	}
	return $db;
}

function member2homology($db, $stable_ids) {
	//$stableCount = sizeof($stable_ids);
	foreach ($stable_ids as $stable_id) {
		$sqlHomology = 'select homology_id
		from homology_member 
		where member_id = (select member_id from member where stable_id = "'.$stable_id.'");';
		//warn($sqlHomology);
		$homologyQuery = $db->query($sqlHomology) or
		trigger_error('Query failed: '.$db->error);
		$homology = $homologyQuery->fetch_all();
		//var_dump($homology);
		if($homologyQuery->num_rows == 0){
			//echo "empty!!!<br />\n";
			$result[$stable_id] = array();
		}else{
			//echo "not empty!!!<br />\n";
			$homologies = array_map("current", $homology);
			$result[$stable_id] = $homologies;
		}
	}
	return $result;
}


function homology2member($db, $homology_id) {
	$sql = 'select distinct stable_id from member as m inner join homology_member as h
	on (m.member_id = h.member_id
	and h.homology_id IN ('.implode(',', $homology_id).'));';
	$result = $db->query($sql) or fatal_error('Query failed: '.$db->error);
	//var_export($member_ids);
	if(!$result->num_rows){
		warn('No genes are homologue to'.$homology_id.'!');
		return array();
	}
	$members = array();
	while ($row = $result->fetch_assoc()){
		$members[] = $row['stable_id'];
	}
	return $members;
}

function homology2member_old($db, $homology_id) {
	$searchString = implode('","', $homology_id);
	$sqlMember = 'select member_id from homology_member where homology_id IN ("'.$searchString.'") group by member_id;';
	$memberQuery = $db->query($sqlMember) or trigger_error('Query failed: '.$db->error);
	$member_ids = $memberQuery->fetch_all();
	//var_export($member_ids);
	if(empty($member_ids)){
		warn('member_ids empty!!');
		return array();
	}else{
		$members = array_map("current", $member_ids);
		$searchString = implode('","', $members);
		$sqlMember = 'select stable_id from member where member_id IN ("'.$searchString.'") group by stable_id;';
		$memberQuery = $db->query($sqlMember) or trigger_error('Query failed: '.$db->error);
		$member_ids = $memberQuery->fetch_all();
		$members = array_map("current", $member_ids);
		return $members;
	}
}

/**
 * get the homologue ensemble ids of the target species to a given set of ensebl ids.
 *
 * @param unknown_type $compara
 * @param unknown_type $unique_ids
 * @param target_genome_db_id the genome of the target species for filtering (speed up)
 */
function get_homologue_ens_ids($compara,$unique_ids,$target_genome_db_id) {
	foreach ($unique_ids as $unique_id) {
		$sql = 'select distinct m.stable_id from member as m inner join homology_member as h
		on (m.member_id = h.member_id
		and m.genome_db_id = '.$target_genome_db_id.')
		inner join homology_member as h2
		on h.homology_id = h2.homology_id
		inner join member as m2
		on m2.member_id = h2.member_id and m2.stable_id = "'.$unique_id.'";';
		$result = $compara->query($sql) or fatal_error('Query failed: '.$compara->error);
		$members = array();
		while ($row = $result->fetch_assoc()){
			$members[] = $row['stable_id'];
		}
		$homology[$unique_id] = $members;
	}
	return $homology;
}

/**
 * get the homologue ensemble ids to a given set of ensebl ids.
 *
 * @param unknown_type $compara
 * @param unknown_type $unique_ids
 */
function get_homologue_ens_ids_old($compara,$unique_ids,$dummy) {
	$homology_ids = member2homology($compara, $unique_ids);
	foreach ($unique_ids as $unique_id) {
		//warn($homology_ids[$unique_id]);
		$members = homology2member($compara, $homology_ids[$unique_id]);
		$homology[$unique_id] = $members;
	}
	return $homology;
}

function locus2bp($qtl_db, $locus_name, $species){
	$sqlChromo = 'select Chr, cMorgan from locus where name = "'.$locus_name.'";';
	$chromoQuery = $qtl_db->query($sqlChromo) or trigger_error('Query failed: '.$qtl_db->error);
	$bp = null;
	if($row = $chromoQuery->fetch_assoc()){
		$bp = cM2bp($row['Chr'], $row['cMorgan'], $species);
	}
	return $bp;
}

function loci2bps($qtl_db, $loci, $species){
	$searchString = implode('","', $loci);
	$sqlChromo = 'select Name, Chr, cMorgan from locus where name in ("'.$searchString.'");';
	$chromoQuery = $qtl_db->query($sqlChromo) or trigger_error('Query failed: '.$qtl_db->error);

	$bp = array();
	while($row = $chromoQuery->fetch_assoc()){
		$bp[$row['Name']] = cM2bp($row['Chr'], $row['cMorgan'], $species);
	}
	return $bp;
}

/*
 function getDnafragIDs($qtl_db, $bp, $genome_qtl_db_id){
 $sqlDnafrag = 'select dnafrag_id from dnafrag_region where dnafrag_start <='.$bp.' AND dnafrag_end >= '.$bp.' AND dnafrag_id IN
 (SELECT dnafrag_id from dnafrag where genome_db_id = '.$genome_db_id.');';
 $fragQuery = $db->query($sqlDnafrag) or trigger_error('Query failed: '.$db->error);

 $frag_ids = array();
 while ($row = $fragQuery->fetch_assoc()) {
 $id = $row['dnafrag_id'];
 $frag_ids[] = $id;
 }
 return $frag_ids;
 }*/
function getSyntenyIDs($db, $bp, $genome_db_id){
	$sqlDnafrag = 'select dfr.synteny_region_id from dnafrag_region as dfr inner join dnafrag as df on (dfr.dnafrag_start <='.$bp.' AND
	dfr.dnafrag_end >= '.$bp.' AND dfr.dnafrag_id = df.dnafrag_id AND df.genome_db_id = '.$genome_db_id.');';

	$fragQuery = $db->query($sqlDnafrag) or trigger_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $fragQuery->fetch_assoc()) {
		$frag_ids[] = $row['synteny_region_id'];
	}
	return $frag_ids;
}

/**
 * Get synthenies between two sets of loci.
 *
 * @param $qtldb database for eQTL
 * @param $comparadb
 * @param $loci_ex1
 * @param $loci_ex2
 * @param $species_names
 * 		at pos. 0 is name of species one, at pos 1 is name of species 2
 * @param $genome_db_ids
 * 		at pos. 0 is genome_db_id of species one, at pos 1 is genome_db_id of species 2
 * @param $databases
 * 		at pos. 0 is database of species one, at pos 1 is database of species 2
 *
 * @return a maping array [loci exp. 1] => [syntenic loci ex. 2]
 */
function getSynteny($qtldb, $comparadb, $loci_ex1, $loci_ex2, $species_names, $genome_db_ids, $databases){
	// get all synteny ids from loci out of exp. one in a mapping array
	// to later intersect them with the synteny ids from loci out of ex. 2
	$synteny_ex1 = array();
	$result = array();
	useDB($databases[0],$qtldb);
	$bps = loci2bps($qtldb, $loci_ex1, $species_names[0]);	
	foreach ($loci_ex1 as $locus_ex1){
		$synteny_ex1[$locus_ex1] = getSyntenyIDs($comparadb, $bps[$locus_ex1], $genome_db_ids[0]);
		$result[$locus_ex1] = array();
	}
	// get all synteny ids from exp. two locis and intersect them with the maping array
	// entries out of $synteny_ex1 to determine
	useDB($databases[1],$qtldb);
	$bps = loci2bps($qtldb, $loci_ex2, $species_names[1]);
	foreach ($loci_ex2 as $locus_ex2){
		$syntenyIDs_locus_ex2 = getSyntenyIDs($comparadb, $bps[$locus_ex2], $genome_db_ids[1]);
		foreach ($loci_ex1 as $locus_ex1){
			$tempIntersection = array_intersect($syntenyIDs_locus_ex2, $synteny_ex1[$locus_ex1]);
			if (!empty($tempIntersection)) {
				$result[$locus_ex1][] = $locus_ex2;
			}
		}
	}
	unset($synteny_ex1);
	return $result;
}
?>

