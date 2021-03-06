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
		warn('No genome db ids found to species with names: '.implode('","', $ens_species)."!<br />\n"
		    ."Maybe the ensemble version is too old?");
		return array();
	}
	$ids = array();
	while ($row = $result->fetch_row()){
		$ids[$row[0]] = $row[1];
	}
	$result->close();
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
	$sql = 'select name, dnafrag_id from dnafrag where name in("'.implode('","', $chromosomes).'") '
	      .'and genome_db_id = '.$genome_db_id.';';

	$query = $db->query($sql) or fatal_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $query->fetch_assoc()) {
		$frag_ids[$row['name']] = $row['dnafrag_id'];
	}
	$query->close();
	return $frag_ids;
}

function get_all_dnafragids($db, $genome_db_id) {
	$sql = 'select name, dnafrag_id from dnafrag where genome_db_id = '.$genome_db_id.';';

	$query = $db->query($sql) or fatal_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $query->fetch_assoc()) {
		$frag_ids[$row['dnafrag_id']] = $row['name'];
	}
	$query->close();
	return $frag_ids;
}

/**
 * Returns the name of the chomosome determined by dnafrag_id "id" from compara-connection "db".
 *
 * @param unknown_type $db compara-connection
 * @param unknown_type $id dnafrag_id
 */
function getSpeciesName($db,$id) {
	$sqlSpeciesName = 'select name from genome_db '
                         .'where genome_db_id = ('
			 .		'select genome_db_id '
			 .		'from dnafrag '
			 .		'where dnafrag_id='.$id.');';
	$resultSpeciesName =  $db->query($sqlSpeciesName) or trigger_error('Query failed: '.$db->error);
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
	$resultSpeciesPara =  $db->query($sqlSpeciesPara) or trigger_error('Query failed: '.$db->error);
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
	$sqlChromosomes = 'SELECT d.name FROM dnafrag as d INNER JOIN genome_db AS g ON ( '
					. '     g.genome_db_id = (SELECT genome_db_id FROM genome_db WHERE name="'.$species_name.'") '
                    . ' AND d.coord_system_name = "chromosome"'
					. ' AND g.genome_db_id = d.genome_db_id  '
					. ');';
	$resultChromosomes =  $db->query($sqlChromosomes) or trigger_error('Query failed: '.$db->error);
	if(!$resultChromosomes->num_rows){
		warn('getChromosomes(): No chromosomes found for species with name: "'.$species_name.'", query executed was "'.$sqlChromosomes.'"');
		return array();
	}
	$chrs = array();
	while ($row = $resultChromosomes->fetch_assoc()){
		$chrs[] = $row['name'];
	}
	$resultChromosomes->close();
	return $chrs;
}

/**
 * Get an assoziative array [Chromosome] => [length]. Used by regions.php.
 * @param $db compara
 * @param $species_name species name in compara syntax eg. rattus_norvegicus
 */
function getChromosomesAndLengths($db, $species_name){
	$sqlChromosomes = 'SELECT d.name, d.length '
	                .'FROM            dnafrag as d '
                        .    ' INNER JOIN genome_db as g on ( '
                        .                                   '      g.genome_db_id = d.genome_db_id  '
		        .                                   '  AND g.genome_db_id = (select genome_db_id from genome_db where name = "'.$species_name.'") '
		        .                                   '  AND d.coord_system_name = "chromosome"'
			.                                  ')'
		#	.'ORDER BY d.length DESC;' # irrelevant because of return as hash
			;
	$resultChromosomes =  $db->query($sqlChromosomes) or trigger_error('Query failed: '.$db->error);
	if(!$resultChromosomes->num_rows){
		warn('getChromosomesAndLengths(): No cromosomes found to species with name: "'.$species_name.'"!');
		return array();
	}
	$chrs = array();
	while ($row = $resultChromosomes->fetch_assoc()){
		$n=$row['name'];
		$chrs["$n"] = $row['length'];
	}
	$resultChromosomes->close();
	return $chrs;
}

/**
 * Returns the names of all specias from compara-connection "db".
 *
 * @param  $db compara-connection
 */
function getAllSpeciesNames($db){
	$sqlSpecies = 'SELECT name FROM genome_db GROUP BY name;';
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
	$sql = 'use `'.$name.'`;';
	$db->query($sql) or fatal_error('Could not use database '.$name.' ('.$db->error.')');
}

/**
 * returns a connection to the compara database (default Port: 3306)
 * @param $port (default 5306)
 * @param $local default false
 */
function connectToCompara($port = '5306', $local=false) {
	$server = false;
	if($server){
		$db = @new mysqli('127.0.0.1', 'rostock_eae', '', 'ensembl_compara_62_small', '3306');
	}else if ($local) {
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
		$m='Could not connect to database: '.mysqli_connect_error().'('.  mysqli_connect_errno().')';
		echo "<p>$m</p></body></html>\n";
		trigger_error($m, E_USER_ERROR);
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

	$sql = 'SELECT m.stable_id,hom.description FROM homology AS hom,member AS m inner join homology_member AS h
		ON (m.member_id = h.member_id
		AND h.homology_id = hom.homology_id 
		AND m.genome_db_id = ?)
		INNER join homology_member AS h2
		ON h.homology_id = h2.homology_id
		INNER join member AS m2
		ON m2.member_id = h2.member_id AND m2.stable_id = ?
		GROUP BY m.stable_id;';
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

	$sql = 'SELECT m.stable_id, m2.stable_id, hom.description
		FROM member AS m 
		INNER JOIN homology_member AS h ON (
			m.member_id = h.member_id
			AND m.genome_db_id = (SELECT genome_db_id FROM genome_db WHERE name="'.$target_species_name.'") 
		) INNER join homology AS hom ON (
			h.homology_id = hom.homology_id 
		) INNER join homology_member AS h2 on (
			h.homology_id = h2.homology_id
		) INNER join member AS m2 on (
			m2.member_id = h2.member_id AND m2.stable_id IN ("'.implode('","', $unique_ids).'")
		) GROUP BY m.stable_id, m2.stable_id;';
	$result = $compara->query($sql) or fatal_error($compara->error);

	$homology = array_combine($unique_ids, array_fill(0,count($unique_ids),array()));
	while ($row = $result->fetch_row()) {
		$homology[$row[1]][$row[0]] = $row[2];
	}
	$result->close();
	
	return $homology;
}


function locus2bp($qtl_db, $locus_name, $species){
	$sqlChromo = 'SELECT Chr, cMorgan FROM locus WHERE name = "'.$locus_name.'";';
	$chromoQuery = $qtl_db->query($sqlChromo) or trigger_error('Query failed: '.$qtl_db->error);
	$bp = null;
	if($row = $chromoQuery->fetch_assoc()){
		$bp = cM2bp($row['Chr'], $row['cMorgan'], $species);
	}
	$chromoQuery->close();
	return $bp;
}

function loci2bps($qtl_db, $loci, $species){
	$searchString = implode('","', $loci);
	$sqlChromo = 'SELECT Name, Chr, cMorgan FROM locus WHERE name IN ("'.$searchString.'");';
	$chromoQuery = $qtl_db->query($sqlChromo) or trigger_error('Query failed: '.$qtl_db->error);

	$bp = array();
	while($row = $chromoQuery->fetch_assoc()){
		$bp[$row['Name']] = cM2bp($row['Chr'], $row['cMorgan'], $species);
	}
	$chromoQuery->close();
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
	$sqlDnafrag = 'SELECT dfr.synteny_region_id '
	             .'  FROM dnafrag_region as dfr  '
	             .        'INNER JOIN dnafrag as df  '
                     .               ' ON (    dfr.dnafrag_start <='.$bp
		     .                   ' AND dfr.dnafrag_end >= '.$bp
		     .                   ' AND dfr.dnafrag_id = df.dnafrag_id '
                     .                   ' AND df.genome_db_id = '.$genome_db_id.');';

	$fragQuery = $db->query($sqlDnafrag) or trigger_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $fragQuery->fetch_assoc()) {
		$frag_ids[] = $row['synteny_region_id'];
	}
	return $frag_ids;
}


function getGroupSyntenyIDs_old($db, $bp, $genome_db_id){
	$sqlDnafrag = 'SELECT dfr.synteny_region_id '
	             .'  FROM dnafrag_region as dfr  '
	             .' INNER JOIN dnafrag as df  '
	             .'    ON (    dfr.dnafrag_start <='.$bp['end']
	             .'        AND dfr.dnafrag_end >= '.$bp['start']
		     .'        AND dfr.dnafrag_id = df.dnafrag_id '
	             .'        AND   df.genome_db_id = '.$genome_db_id.');';

	$fragQuery = $db->query($sqlDnafrag) or trigger_error('Query failed: '.$db->error);

	$frag_ids = array();
	while ($row = $fragQuery->fetch_assoc()) {
		$frag_ids[] = $row['synteny_region_id'];
	}
	$fragQuery->close();
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
	$sqlDnafrag = 'SELECT r2.dnafrag_start, r2.dnafrag_end, r2.dnafrag_id '
	             .'  FROM dnafrag_region AS r2 
	             INNER JOIN dnafrag_region as r1 '
	             .                           ' ON (    r1.synteny_region_id = r2.synteny_region_id '
	             .                               ' AND r1.dnafrag_start <='.$bp['end'].'  '
	             .                               ' AND r1.dnafrag_end >= '.$bp['start'].'  '
	             .                               ' AND r1.dnafrag_id = '.$dnafrag.' '
	             .                               ' AND r2.dnafrag_id in ('.implode(',',array_keys($dnafrag2name)).'));';

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
function getSyntenyGroups($qtldbs, $comparadb, $groups1, $groups2, $species_names, $genome_db_ids, $databases){
	$synteny_ex1 = array();
	$result = array();
	useDB($databases[0],$qtldbs[0]);
	$bps1 = groups2bps($qtldbs[0], $groups1, $species_names[0]);
	useDB($databases[1],$qtldbs[1]);
	$bps2 = groups2bps($qtldbs[1], $groups2, $species_names[1]);

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

