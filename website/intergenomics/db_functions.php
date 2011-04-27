<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 db_functions.php -

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

require_once 'bp2cM_conversion.php';

/**
 *
 * Select the genome db ids to ensembl species names.
 * 
 * @param unknown_type $compara
 * @param unknown_type $ens_species
 * @author Georg 2011.04.25
 */
function getGenomeDBIDs($compara,$ens_species) {
	$sql = 'select name, genome_db_id from genome_db where name in ("'.implode('","', $ens_species).'");';
	$result =  $compara->query($sql)or fatal_error($db->error);
	if(!$result->num_rows){
		warn('No genome db ids found to species with names: '.implode('","', $ens_species).'!<br />
		Maybe the ensemble version is too old?');
		return array();
	}
	$ids = array();
	while ($row = $result->fetch_row()){
		$ids[$row[0]] = $row[1];
	}
	$res = array();
	foreach ($ens_species as $species) {
		if (!isset($ids[$species])) {
			fatal_error($species." has no geneome db id in Ensembl!");
		}
		$res[] = $ids[$species];
	}
	return $res;
}

/**
 *
 * Get dnafrag ids to chromosome names in a mapping array ([chromo. name] => [dnafrag_id]).
 *
 * @param $db
 * 		instance of compara
 * @param $genome_db_id
 * 		genome db id of the selected species
 * @param $chromosomes
 *	 	chromosome names
 * @return dnafrag ids
 * @author Georg
 */
function get_dnafragids($db, $genome_db_id, $chromosomes) {
	$sql = 'select name, dnafrag_id from dnafrag where name in("'.implode('","', $chromosomes).'")
	and genome_db_id = '.$genome_db_id.';';

	$query = $db->query($sql) or fatal_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $query->fetch_assoc()) {
		$frag_ids[$row['name']] = $row['dnafrag_id'];
	}
	return $frag_ids;
}

function get_all_dnafragids($db, $genome_db_id) {
	$sql = 'select name, dnafrag_id from dnafrag
	where genome_db_id = '.$genome_db_id.';';

	$query = $db->query($sql) or fatal_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $query->fetch_assoc()) {
		$frag_ids[$row['dnafrag_id']] = $row['name'];
	}
	return $frag_ids;
}

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
 * Get an array with the Chromosomes to a species. Used by compara.php.
 *
 * @param $db compara
 * @param $species_name species name in compara syntax eg. rattus_norvegicus
 */
function getChromosomes($db, $species_name){
	$sqlChromosoms = 'select d.name from dnafrag as d
	inner join genome_db as g on(
		g.genome_db_id = d.genome_db_id 
		and g.genome_db_id = (select genome_db_id from genome_db where name="'.$species_name.'")  
		AND d.coord_system_name = "chromosome");';
	$resultChromosoms =  $db->query($sqlChromosoms)or trigger_error('Query failed: '.$db->error);
	if(!$resultChromosoms->num_rows){
		warn('getChromosomes(): No cromosomes found to species with name: '.$species_name.'!');
		return array();
	}
	$chrs = array();
	while ($row = $resultChromosoms->fetch_assoc()){
		$chrs[] = $row['name'];
	}
	return $chrs;
}

/**
 * Get an assoziative array [Chromosome] => [length]. Used by regions.php.
 * @param $db compara
 * @param $species_name species name in compara syntax eg. rattus_norvegicus
 */
function getChromosomesAndLengths($db, $species_name){
	$sqlChromosoms = 'select d.name, d.length from dnafrag as d
	inner join genome_db as g on(
		g.genome_db_id = d.genome_db_id 
		and g.genome_db_id = (select genome_db_id from genome_db where name = "'.$species_name.'")  
		AND d.coord_system_name = "chromosome");';
	$resultChromosoms =  $db->query($sqlChromosoms)or trigger_error('Query failed: '.$db->error);
	if(!$resultChromosoms->num_rows){
		warn('getChromosomesAndLengths(): No cromosomes found to species with name: '.$species_name.'!');
		return array();
	}
	$chrs = array();
	while ($row = $resultChromosoms->fetch_assoc()){
		$chrs[$row['name']] = $row['length'];
	}
	return $chrs;
}

/**
 * Returns the names of all specias from compara-connection "db".
 *
 * @param  $db compara-connection
 */
function getAllSpeciesNames($db){
	$sqlSpecies = 'select name from genome_db group by name;';
	$speciesQuery = $db->query($sqlSpecies) or trigger_error('Query failed: '.$db->error);
	$species = $speciesQuery->fetch_all();
	return $species;
}
/**
 * Switches the current database to $name.
 * @param $name
 * @param $db
 */
function useDB($name, $db){
	$sql = 'use '.$name.';';
	$db->query($sql)or
	trigger_error('Could not use database '.$name.' ('.$db->error.')');
}

/**
 * returns a connection to the compara database (default Port: 3306)
 * @param $port (default 5306)
 * @param $local default false
 */
function connectToCompara($port = '5306', $local=false) {
	if ($local) {
		$db = @new mysqli('127.0.0.1', 'anonymous', 'no', 'ensembl_compara_59', '3306');
	}else{
		if($port == '5306'){
			$database = 'ensembl_compara_62';
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


/**
 * get the homologue ensemble ids of the target species to a given set of ensebl ids.
 *
 * @param unknown_type $compara
 * @param unknown_type $unique_ids
 * @param target_genome_db_id the genome of the target species for filtering (speed up)
 */
function get_homologue_ens_ids_slow($compara, $unique_ids, $target_genome_db_id) {
	$homology = array();

	$sql = 'select m.stable_id,hom.description from homology as hom,member as m inner join homology_member as h
		on (m.member_id = h.member_id
		and h.homology_id = hom.homology_id 
		and m.genome_db_id = ?)
		inner join homology_member as h2
		on h.homology_id = h2.homology_id
		inner join member as m2
		on m2.member_id = h2.member_id and m2.stable_id = ?
		group by m.stable_id;';
	$stmt = $compara->prepare($sql);
	foreach ($unique_ids as $unique_id) {

		/* bind parameters for markers */
		$stmt->bind_param("is", $target_genome_db_id, $unique_id);
		/* execute query */
		$stmt->execute();
		/* bind result variables */
		$stmt->bind_result($homo_id,$homo_descript);
		$homology[$unique_id] = array();
		/* fetch value */
		while($stmt->fetch()){
			$homology[$unique_id][] = $homo_id;
			$homology[$unique_id][$homo_id] = $homo_descript;
		}
		/*$result = $compara->query($sql) or fatal_error('Homology query failed: '.$compara->error);
		 $members = array();
		 while ($row = $result->fetch_assoc()){
			$members[] = $row['stable_id'];
			}
			$homology[$unique_id] = $members;*/
	}
	/* close statement */
	$stmt->close();

	return $homology;
}

/**
 * get the homologue ensemble ids of the target species to a given set of ensebl ids.
 *
 * @param unknown_type $compara
 * @param unknown_type $unique_ids
 * @param $target_species_name the name of the target species for filtering (speed up)
 * @author g 2011.04.26
 */
function get_homologue_ens_ids($compara, $unique_ids, $target_species_name) {

	$sql = 'select m.stable_id, m2.stable_id, hom.description
		from member as m 
		inner join homology_member as h on (
			m.member_id = h.member_id
			and m.genome_db_id = (select genome_db_id from genome_db where name="'.$target_species_name.'") 
		) inner join homology as hom on (
			h.homology_id = hom.homology_id 
		) inner join homology_member as h2 on (
			h.homology_id = h2.homology_id
		) inner join member as m2 on (
			m2.member_id = h2.member_id and m2.stable_id in ("'.implode('","', $unique_ids).'")
		) group by m.stable_id, m2.stable_id;';
	$result = $compara->query($sql) or fatal_error($compara->error);

	$homology = array_combine($unique_ids, array_fill(0,count($unique_ids),array()));
	while ($row = $result->fetch_row()) {
		$homology[$row[1]][$row[0]] = $row[2];
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

function groups2bps($qtl_db, $groups, $species){
	$bp = array();
	$i = 0;
	foreach ($groups as $group) {
		$bp[$i]['start'] = cM2bp($group['Chr'], $group['start'], $species);
		$bp[$i]['end'] = cM2bp($group['Chr'], $group['end'], $species);
		$i++;
	}
	return $bp;
}

function getSyntenyIDs($db, $bp, $genome_db_id){
	$sqlDnafrag = 'select dfr.synteny_region_id
	from dnafrag_region as dfr 
	inner join dnafrag as df 
	on (dfr.dnafrag_start <='.$bp.' AND
	dfr.dnafrag_end >= '.$bp.' 
	AND dfr.dnafrag_id = df.dnafrag_id 
	AND df.genome_db_id = '.$genome_db_id.');';

	$fragQuery = $db->query($sqlDnafrag) or trigger_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $fragQuery->fetch_assoc()) {
		$frag_ids[] = $row['synteny_region_id'];
	}
	return $frag_ids;
}


function getGroupSyntenyIDs_old($db, $bp, $genome_db_id){
	$sqlDnafrag = 'select dfr.synteny_region_id
	from dnafrag_region as dfr 
	inner join dnafrag as df 
	on (dfr.dnafrag_start <='.$bp['end'].' 
	AND	dfr.dnafrag_end >= '.$bp['start'].' AND dfr.dnafrag_id = df.dnafrag_id 
	AND df.genome_db_id = '.$genome_db_id.');';

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


function get_chromo_names_from_group($groups){
	$chr = array();
	foreach ($groups as $group) {
		$chr[] = $group['Chr'];
	}
	return array_unique($chr);
}

function getGroupSyntenyIDs($db, $bp, $dnafrag, $dnafrag2name, $species_name){
	$sqlDnafrag = 'select r2.dnafrag_start, r2.dnafrag_end, r2.dnafrag_id
	from dnafrag_region as r2 inner join dnafrag_region as r1
	on(r1.synteny_region_id	= r2.synteny_region_id
	and r1.dnafrag_start <='.$bp['end'].' 
	AND	r1.dnafrag_end >= '.$bp['start'].' 
	AND r1.dnafrag_id = '.$dnafrag.'
	and r2.dnafrag_id in ('.implode(',',array_keys($dnafrag2name)).'));';

	$fragQuery = $db->query($sqlDnafrag) or trigger_error('Query failed: '.$db->error);

	$regions = array();
	$i=0;
	while ($row = $fragQuery->fetch_assoc()) {
		$chr = $dnafrag2name[$row['dnafrag_id']];
		$tempChr = $chr;
		$tempStart = bp2cM($chr, $row['dnafrag_start'], $species_name);
		$tempEnd = bp2cM($chr, $row['dnafrag_end'], $species_name);
		if (($tempStart != NULL) || ($tempEnd != NULL)) {
			$regions[$i]['chr'] = $tempChr;
			$regions[$i]['start'] = $tempStart;
			$regions[$i]['end'] = $tempEnd;
			$i++;
		}
	}
	return $regions;
}

/**
 * Get synthenies between two sets of grouped loci.
 *
 * @param $qtldb database for eQTL
 * @param $comparadb
 * @param $groups1
 * 		= groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr')
 * @param $groups2
 * 		= groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr')
 * @param $species_names
 * 		at pos. 0 is name of species one, at pos 1 is name of species 2
 * @param $genome_db_ids
 * 		at pos. 0 is genome_db_id of species one, at pos 1 is genome_db_id of species 2
 * @param $databases
 * 		at pos. 0 is database of species one, at pos 1 is database of species 2
 *
 */
function getSyntenyGroups($qtldb, $comparadb, $groups1, $groups2, $species_names, $genome_db_ids, $databases){
	$synteny_ex1 = array();
	$result = array();
	useDB($databases[0],$qtldb);
	$bps1 = groups2bps($qtldb, $groups1, $species_names[0]);
	useDB($databases[1],$qtldb);
	$bps2 = groups2bps($qtldb, $groups2, $species_names[1]);

	$dnafragids1 = get_dnafragids($comparadb, $genome_db_ids[0], get_chromo_names_from_group($groups1));
	$dnafrag2name = get_all_dnafragids($comparadb, $genome_db_ids[1]);

	$group2region = array();
	for ($i = 0; $i < sizeof($bps1); $i++) {
		$group2region[$i] = getGroupSyntenyIDs($comparadb, $bps1[$i],$dnafragids1[$groups1[$i]['Chr']], $dnafrag2name,$species_names[1]);
	}

	$synteny1to2 = array();
	foreach ($group2region as $group1nr => $regions) {
		$synteny1to2[$group1nr] = array();
		foreach ($regions as $region) {
			foreach ($groups2 as $group2nr => $group2) {
				if($group2['Chr']==$region['chr']){
					if($group2['start']<=$region['end'] && $group2['end']>=$region['start']){
						//add the groupnumber
						$synteny1to2[$group1nr][] = $group2nr;
						//filter array for duplicate entries
						$synteny1to2[$group1nr] = array_unique($synteny1to2[$group1nr]);
					}
				}
			}
		}
	}
	return $synteny1to2;
}
?>

