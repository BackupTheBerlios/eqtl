<?php
include 'html/header.html';
# supported target species:
# rat: stockholm "Rattus norvegicus"
# mus: rostock "Mus musculus"

/**
 *
 * Maybe this shall be in its own file
 * @param $projects
 * @param $isSource
 */
function showProjectList($projects, $isSource){
	global $compara_array;

	$index = $isSource ? 0 : 1;

	echo'<select onclick="submit_page(\'this\')" id="projects'.$index.'" size="'.
	count($compara_array).'">';

	foreach ($compara_array as $project_name => $project_info) {
		echo '<option value="'.implode("+", explode(" ", $project_name)).'" '.
		($project_name == $projects[$index] ? 'selected="selected">': '>').
		$project_name.' ('.$project_info['species'].')</option>';
	}
	echo '</select>';
}


$speciesArray = array("Rattus norvegicus","Mus musculus");
$genome_db_ids = array(57,3);
$species2genome_db_ids = array("Rattus norvegicus" =>3,"Mus musculus"=>57);
$genome_ids2dbs = array(57 => 'eqtl_rostock_eae', 3 =>'eqtl_stockholm_eae_logplier');
$num_species = sizeof($speciesArray);
$species_str = 'species';
$reg_str = 'regions';
$args = $_GET;

require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';

fill_compara_array();
global $compara_array;

$proj_str = 'projects';
if(!isset($args[$proj_str])){//no project selected
	$projects = array();
}else{
	$projects = $args[$proj_str];
}
// enlarge project array with NULLs
$n = count($projects);
while ($n<2) {
	$projects[] = NULL;
	$n++;
}


?>
<script
  type="text/javascript" src="js/regions.js"></script>

<div class="lr">
<fieldset>
<h3>Compare source project</h3>
<?php
showProjectList($projects,true);
?></fieldset>
</div>
<div class="lr">
<fieldset>
<h3>...with target project:</h3>
<?php
showProjectList($projects,false);
?></fieldset>
</div>
<br style="clear: both;" />
<?php

if($projects[0]==NULL){
	include 'html/footer.html';
	exit();
}

// only the database of the source project needs to be opened
$src_proj = $projects[0];
connectToQtlDBs(array($src_proj));
$qtldb = $compara_array[$src_proj]['connection'];
$compara = connectToCompara(3306);


// region selection

// fetch chromosomes to species id
$genome_db_id = $compara_array[$src_proj]['genome_db_id'];
$chrs = getChromosomsAndLengths($compara,$genome_db_id);
// addition filtering
//$database = $genome_ids2dbs[$genome_db_id];
//useDB($database, $qtldb);
$chrs = filter_chromos($qtldb, $chrs);

// get selected regions
$chr2reg = array();
if(isset($args[$reg_str])){
	$regs = $args[$reg_str];
	foreach ($regs as $reg){
		$pos = strpos ($reg, ":");
		$chr2reg[substr($reg,0,$pos)][] = substr($reg,$pos+1);
	}
}

//confidence intervall
$confidence_int_str = 'confidence_int';
if(isset($args[$confidence_int_str])){
	$confidence_int_len = $args[$confidence_int_str];
}else{// default
	$confidence_int_len = 1;
}
?>

<h3>Add regions for species <?php echo $species;?></h3>
<table border="1" cellpadding="3" cellspacing="0">
  <tr>
    <th>Chromosome</th>
    <th>Length</th>
    <th>add region</th>
    <th>selected regions</th>
  </tr>
  <?php
  foreach ($chrs as $chr => $length){
  	// name and length
  	echo "<tr><th>".$chr."</th>";
  	echo "<td>".$length."</td>";

  	// add region column
  	echo '<td>
  	<label for="start'.$chr.'">start </label><input
  id="start'.$chr.'" type="text" size="10" value="1" /> <label
  for="end'.$chr.'">end </label><input id="end'.$chr.'" type="text"
  size="10" value="'.$length.'" />
  <input type="button" value="add" onclick="addRegion(\''.$chr.'\')"/>
  	</td>';
  	// selected regions
  	if(isset($chr2reg[$chr])){
  		echo '<td>';
  		foreach ($chr2reg[$chr] as $i => $reg){
  			//id="'.$chr.'-'.$i.'" href="noJS.php"
  			echo '<input name="'.$reg_str.'[]" id="'.$chr.'-'.$i.'" type="text"  value="'.$reg.'" size="'.(strlen($reg)).'"/>
  				<a href="javascript:deleteRegion(\''.$chr.'-'.$i.'\')"><sup class="close">X</sup></a>&nbsp;';
  		}
  		echo '</td>';
  	}else{
  		echo '<td>
  		<input type="text" id="'.$chr.'"/>
  		</td>';
  	}
  	echo "</tr>\n";
  }
  ?>
</table>
<p><label for="conf">Length of confidence intervall around each locus: </label><input
  id="conf" type="text" size="4"
  value="<?php echo $confidence_int_len; ?>" /> cM</p>
<p>&nbsp;&nbsp;<input type="button" onclick="submit_page('overview')"
  value="Overview" /> &nbsp;&nbsp; <input type="button"
  onclick="submit_page('all')" value="Display all" /></p>
</form>
  <?php
  include 'html/footer.html';
  ?>
