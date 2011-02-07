<?php
require_once 'utils.php';

function connectToQtlDB($port = '3306') {
	$targetdb = @new mysqli('127.0.0.1', 'anonymous', 'no', '', $port);
	if (mysqli_connect_errno()) {
		fatal_error('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
	}
	return $targetdb;
}
/**
 * fill the correlation array with default values, if a correlation between an
 * trait and lous not exists.
 *
 * @param $storage here the default values are filled
 * @param $allTraits if in this array the trait exists
 */
function fillDefaults(&$storage,$allTraits) {
	$default = 0;
	foreach ($storage as $locus => $combined){
		$keys = array_keys($combined);
		$vals = array_values($combined);
		$diff = array_diff($allTraits,$keys);
		$new_keys = array_merge($keys,$diff);
		$new_vals = array_merge($vals,array_fill(0,sizeof($diff),$default));
		$storage[$locus] = array_combine($new_keys,$new_vals);
	}

}

/**
 * Get ensembls stable gene ids to a set of traits associated with the parameter-loci.
 *
 * supported target species are:
 * rat: stockholm "Rattus norvegicus"
 * mus: rostock "Mus musculus"
 *
 * @param $loci e.g. array('c9.loc48', 'c9.loc43', 'c9.loc40' , 'c9.loc39');
 * @param $targetdb the dbms WITH SELECTED DEFAULT DATABASE
 */
function loci2stable_ids($loci, $targetdb){
	$storage = array();
	$lociChromos = array();
	$sql = 'select Chr, Name from locus where Name in	(\''.implode("', '",$loci).'\');';
	//echo $sql;
	$result = $targetdb->query($sql) or fatal_error('loci2stable_ids(); Query failed: '.$targetdb->error);
	while ($row = $result->fetch_assoc()) {
		$lociChromos[$row['Name']] = $row['Chr'];
	}
	// XXX
	//warn(implode(", ",array_keys($lociChromos))."</br>".implode(", ",array_values($lociChromos)));

	foreach ($loci as $locus) {
		$chromos = array();
		$locus_chr = $lociChromos[$locus];
		$ids = locus2stable_ids($targetdb, $locus, $chromos, false);
		// output if the trait is cis or trans
		$nchr = sizeof($chromos);
		for ($i = 0; $i < $nchr; $i++) {
			if($chromos[$i]==$locus_chr){
				$chromos[$i] = true;
			}else{
				$chromos[$i] = false;
			}
		}
		$storage[0][$locus] = $ids;
		$storage[1][$locus] = $chromos;
	}
	return $storage;
}



function group2stable_ids($mapEx,$loci2stable_ids_ex){
	$lastElement = end($mapEx);
	for ($i = 0; $i <= $lastElement[1]; $i++) {
		$group2stable_ids_ex[$i] = array();
	}
	foreach ($mapEx as $lociArray) {
		$tempLocus = $lociArray[0];
		$tempGroupNr = $lociArray[1];
		$group2stable_ids_ex[$tempGroupNr] = array_merge($group2stable_ids_ex[$tempGroupNr],$loci2stable_ids_ex[0][$tempLocus]);
	}
	for ($i = 0; $i <= $lastElement[1]; $i++) {
		$group2stable_ids_ex[$i] = array_unique($group2stable_ids_ex[$i]);
	}
	return $group2stable_ids_ex;
}

/**
 * Get ensembls stable gene ids to a set of traits associated with the parameter-locus.
 *
 * FIXME GZ: We filter out ensemble-ids, that are to short.
 * The normal length of a ensemble stable gene id is 18.
 * Our minimum expected id-size is 16.
 *
 * @param $targetdb DBMS - search here for traits and stable ids
 * @param $locus the parameter-locus e.g. 'c9.loc48'
 * @param $chromos
 */
function locus2stable_ids($targetdb, $locus, &$chromos, $debug=FALSE) {
	$sql = 'select t.ensembl_stable_gene_id, t.chromosome from Trait as t inner join qtl on
	(t.trait_id = qtl.Trait AND qtl.Locus = "'.$locus.'") group by ensembl_stable_gene_id;';
	$result = $targetdb->query($sql) or fatal_error('Query failed: '.$targetdb->error);
	if(!$result->num_rows && $debug){
		error("qtl_functions.php locus2stable_ids():<br />No stable ids found for ".$locus);
		// XXX: this should be done better...
		// maybe search in ensemble for the gene...
		return array();
	}
	$ids = array();

	while ($row = $result->fetch_assoc()) {
		$id = $row['ensembl_stable_gene_id'];
		if($id!='' && strlen($id) > 15){
			// empty filtering: it is neccessary, cause some traits have no stable id!!
			$ids[] = $id;
			$chromos[] = $row['chromosome'];
		}/*else{
		error($locus);
		}*/
	}
	return $ids;
}

/**
 *
 * Filter the chromomosomes from the ensembl database with the ones in the qtl-database.
 * In the result array are only chromosomes, that are in both databases.
 *
 * @param $targetdb
 * @param $chromos
 * @param $debug
 * @author Georg 2011.01.28
 */
function filter_chromos($targetdb, $chromos, $debug=FALSE) {
	$sql = 'select Chr from locus group by Chr;';
	$result = $targetdb->query($sql) or fatal_error('Query failed: '.$targetdb->error);
	if(!$result->num_rows && $debug){
		fatal_error("qtl_functions.php filter_chromos():<br />No Chromoms found ");
		return array();
	}
	$qtl_chromos = array();
	while ($row = $result->fetch_assoc()) {
		$qtl_chr = $row['Chr'];
		if(isset($chromos[$qtl_chr])){
			$qtl_chromos[$qtl_chr] = $chromos[$qtl_chr];
		}
	}
	return $qtl_chromos ;
}

/**
 * Get the union of the value-arrays from a 2d array.
 *
 * @param $array a 2d array
 */
function get_unique_vals_from_2d_array($array) {
	$unique_vals = array();
	foreach ($array as $vals){
		$unique_vals = array_merge($unique_vals, $vals);
	}
	return array_unique($unique_vals);
}

/**
 * Get an array of loci from a sql statement.
 * The function expects that the first result-column is the locus.
 * This function shall be used, if the group is known.
 *
 * 2010-12-16 Georg
 *
 * @param $sql a sql statement.
 */
function get_only_loci_from_sql($sql, $qtldb){
	$res = $qtldb->query($sql) or fatal_error('Query failed: '.$qtldb->error);
	$loci = array();
	while ($row=$res->fetch_assoc()){
		$loci[] = current($row);
	}
	return $loci;
}
/**
 * Returns an grouped array of loci from a sql statement.
 * Returns also an array with every loci and its groupnumber.
 * The function expects that the first result-column is the locus.
 *
 * 2010-11-19 Michael
 *
 * @param $sql
 */
function get_loci_from_sql($databaseTable, $qtldb, $searchType, $chromosomNo, $confidenceInt, &$group2region, $intervalStart  = 0, $intervalEnd  = 0){
	if ($searchType == 'wholeGenome'){
		$sql = 'SELECT q.Chromosome, q.locus, l.cMorgan FROM '.$databaseTable.'.qtl as q inner join '.$databaseTable.'.locus as l on
		(q.Chromosome in ("'.implode('","', $chromosomNo).'") AND q.locus = l.name) GROUP BY q.locus ORDER BY q.Chromosome, l.cMorgan;';
		$res = $qtldb->query($sql) or fatal_error('Query failed: '.$qtldb->error);
		$lociArray[0] = $res->fetch_all();
	}elseif($searchType == 'userinterval'){
		for ($i = 0; $i < sizeof($chromosomNo); $i++) {
			$sql = 'SELECT q.Chromosome, q.locus, l.cMorgan FROM '.$databaseTable.'.qtl as q inner join '.$databaseTable.'.locus as l on
		(q.Chromosome = "'.$chromosomNo[$i].'" AND l.cMorgan >='.$intervalStart[$i].' AND l.cMorgan <='.$intervalEnd[$i].' AND
		q.locus = l.name) GROUP BY q.locus ORDER BY q.Chromosome, l.cMorgan;';	
			$res = $qtldb->query($sql) or fatal_error('Query failed: '.$qtldb->error);
			$lociArray[$i] = $res->fetch_all();
		}

	}else{
		fatal_error('undefined searchtype used!');
	}

	//check if something was found:
	if (!empty($lociArray)){
		//group loci...
		//initialize return arrays
		$group = array();
		$lociGroup = array();
		//groupNo
		$i = 0;
		//index of loci
		$j = 0;
		//we need this variable for the regions. if the user chooses 2 regions on the same
		//chromosome that are overlapping we need to make a new group-entry for the second of
		//that regions (else the second region could be treated as a part of the first region).
		//so we check if the oldRegion $kOld is different to $k (current region)
		$kOld = 0;
		//iterate over the given regions k
		for ($k = 0; $k < sizeof($lociArray); $k++) {
			$loci = $lociArray[$k]; //set current set of loci for this iteration
			//check if set is empty
			if (!empty($loci)) {
				//initialize first locus of set
				$currentLocus = current($loci);
				//initialize chromosome name of first locus
				$currentChromosome = $currentLocus[0];
				//now interate over the whole set of loci
				foreach ($loci as $locus) {
					//save the start and end with the confidenceInterval of the current locus
					$locusStart = $locus[2]-$confidenceInt;
					$locusEnd = $locus[2]+$confidenceInt;
					//check if the locus+confidenceInterval is in the user-search-interval || we search without an interval
					if (($intervalEnd == 0) || ($locusStart <= $intervalEnd[$k]) && ($locusEnd >= $intervalStart[$k])) {
						//check if we have a new group
						if (empty($group[$i])) {
							//generate array with: index -> (locusName, Groupnumber)
							$lociGroup[$j] = array($locus[1],$i);
							//generate groupArray
							$group[$i]['loci'][] = $locus[1];
							$group[$i]['start'] = $locusStart;
							$group[$i]['end'] = $locusEnd;
							$group[$i]['Chr'] = $locus[0];
							//here we fill the mapping array group->region
							$group2region[$i] = $k;
							//check if we have a new chromosome or a new Region
						}elseif (($currentChromosome != $locus[0])||($k!=$kOld)) {
							$kOld = $k; //needed for overlapping regions on one chromosome...
							//take the next groupnumber
							$i++;
							//and insert the details
							//update array
							$lociGroup[$j] = array($locus[1],$i);
							//generate groupArray
							$group[$i]['loci'][] = $locus[1];
							$group[$i]['start'] = $locusStart;
							$group[$i]['end'] = $locusEnd;
							$group[$i]['Chr'] = $locus[0];
							//set the new chromosome as current
							$currentChromosome = $locus[0];
							//here we fill the mapping array group->region
							$group2region[$i] = $k;
							//check if GroupNo i is the right group (do we have an overlap)
						}elseif (($group[$i]['end'] >= $locusStart) && ($currentChromosome == $locus[0])) {
							//fill array with new index: index -> (locusName, Groupnumber)
							$lociGroup[$j] = array($locus[1],$i);
							//generate groupArray
							$group[$i]['loci'][] = $locus[1];
							$group[$i]['end'] = $locusEnd;
							//the last possible branch: same chromosome but not in group interval
						}else{
							//take the next groupnumber
							$i++;
							//and insert the details
							//update array
							$lociGroup[$j] = array($locus[1],$i);
							//generate groupArray
							$group[$i]['loci'][] = $locus[1];
							$group[$i]['start'] = $locusStart;
							$group[$i]['end'] = $locusEnd;
							$group[$i]['Chr'] = $locus[0];
							//here we fill the mapping array group->region
							$group2region[$i] = $k;
						}
						//increase index counter of mapping array: index -> (locusName, Groupnumber)
						$j++;
					}//end of: is in interval conditions
				}//end of: 	foreach ($loci as $locus)
			}//end of:	if (!empty($loci))
		}//end of:	for ($k = 0; $k < sizeof($lociArray); $k++)
	}//end of:	if (!empty($lociArray))
	if (empty($group)||empty($lociGroup)) {
		//the result is empty
		return array();
	}else {
		//return 2 arrays $group and $lociGroup
		$res = array($group, $lociGroup);
		return $res;
	}
}

/* old code from locus 2 stable ids => could be deleted, but left for maybe reuse some day.
 * function locus2stable_ids($targetdb, $locus, $debug=FALSE) {
 $sql = 'select Trait from qtl where Locus = \''.$locus.'\' group by Trait;';
 //echo $sql;
 $result = $targetdb->query($sql) or fatal_error('Query failed: '.$targetdb->error);
 if(!$result->num_rows){
 if($debug){
 echo "No traits found for ".$locus;
 }
 return array();
 }
 while ($row = $result->fetch_assoc()) {
 $vals[] = $row['Trait'];
 }
 /*$rows = $result->fetch_all();
 $num_rows = $result->num_rows;
 if($num_rows==0){
 if($debug){
 echo "No traits found for ".$locus;
 }
 return array();
 }
 $vals = array_unique(array_map("current", $rows));*/

// search for stable ids
/*$sql = 'select ensembl_stable_gene_id from Trait where trait_id in
 (\''.implode("', '",$vals).'\');';
 //echo $sql;
 $result = $targetdb->query($sql) or fatal_error('Query failed: '.$targetdb->error);
 $rows = $result->fetch_all();
 if($result->num_rows==0){
 if($debug){
 echo "No stable ids found for ".$vals;
 }
 // XXX: this should be done better...
 // maybe search in ensemble for the gene...
 return array();
 }
 // XXX: could be done FASTER!!!
 $ids = array_unique(array_filter(array_map("current", $rows),"not_empty"));*
 $sql = 'select ensembl_stable_gene_id from Trait where trait_id in
 (\''.implode("', '",$vals).'\') group by ensembl_stable_gene_id;';
 //echo $sql;
 $result = $targetdb->query($sql) or fatal_error('Query failed: '.$targetdb->error);
 if($result->num_rows==0 && $debug){
 echo "No stable ids found for ".$vals;
 // XXX: this should be done better...
 // maybe search in ensemble for the gene...
 return array();
 }
 while ($row = $result->fetch_assoc()) {
 $id = $row['ensembl_stable_gene_id'];
 if($id!=''){
 $ids[] = $row['ensembl_stable_gene_id'];
 }
 }
 // XXX: could be done FASTER!!!
 $ids = array_unique(array_filter(array_map("current", $rows),"not_empty"));
 return $ids;
 }

 **
 * Helper for locus2stable_ids()
 * @param $str a string
 *
 function not_empty($str){return $str!='';}
 function loci2stable_ids_old($loci, $genome_db_ids,$targetdb){
 require_once 'db_functions.php';
 $storage = array();
 // XXX
 // species id is used to change the database if the current DB id different from the required
 // this will lead to problems, if for one species exist multiple databases!!!
 $old_species_id = -1;// this id never exists

 //genome_db_id for mus (this is fixed by ensembl!!)
 $mouse_id = 57;

 $num_loci = sizeof($loci);
 for ($i = 0; $i < $num_loci; $i++) {
 $genome_id = $genome_db_ids[$i];
 $locus = $loci[$i];
 if($old_species_id != $genome_id){
 //echo "using Rostock";
 if($genome_id==$mouse_id){//"Mus musculus"
 useDB('eqtl_rostock_eae',$targetdb);
 }else{
 useDB('eqtl_stockholm_eae_logplier',$targetdb);
 }
 }
 $ids = locus2stable_ids($targetdb, $locus,true);
 $storage[$locus] = $ids;
 //echo "<p> '".implode("', '",$ids)."' </p>";
 $old_species_id = $genome_id;
 }
 return $storage;
 }
 */