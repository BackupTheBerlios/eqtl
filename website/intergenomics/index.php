<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 regions.php -

 =head1 SYNOPSIS

 =head1 DESCRIPTION

 Start page of the intergenomics project. Allows the selection of regions for comparison with another species.

 =head1 AUTHOR

 Michael Brehler <brehler@informatik.uni-luebeck.de>,
 Georg Zeplin <zeplin@informatik.uni-luebeck.de>,

 =head1 COPYRIGHT

 University of LE<uuml>beck, Germany, 2011

 =cut

 ENDOFDOCUMENTATION
 */

//include 'html/header.html';
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

function endPage(){
	include '../eqtl/footer.php';
    include 'html/footer.html';
}


$species_str = 'species';
$reg_str = 'regions';
$args = $_GET;

require_once 'db_functions.php';
require_once 'qtl_functions.php';
require_once 'utils.php';
require_once 'fill_related_projects.php';
fill_compara_array();
require_once '../eqtl/header.php';
$upper_tit = "<b>Ensembl Compara interface for Expression QTL</b>";
show_large_header("Intergenomics",true,$upper_tit,
	'../eqtl/', array('css/style.css','css/prettyPhoto.css'));
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

if(isset($args['err'])){
	if($args['err']=='src'){
		warn('Please select the source project first.');
	}else{
		warn('Please select the target project first.');
	}
}

?>

<script
  src="js/jquery-1.4.4.min.js" type="text/javascript" charset="utf-8"></script>
<script
  src="js/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script
  type="text/javascript" src="js/regions.js"></script>

<div class="lr" style="width: 30%;">
  <div class="prettybox">
    <h3>Description</h3>
    The intergenomics page allows you to search for syntenies and
    homologies in the genome of another species with a set of regions of
    your choice. 
    <ul>
    <li><a href="img/regions_l.png" rel="prettyPhoto[a]"
      title="Region selection on the start page."><img src="img/regions_s.png" width="100"
      height="65" alt="Step 1: Region selection on the start page." /> </a></li>
    <li><a href="img/synteny_l.png" rel="prettyPhoto[a]"
      title="Syntenic regions view for the selected chromosomes."><img src="img/synteny_s.png" width="49"
      height="65" alt="Step 2: syntenic regions for the selected chromosomes" /> </a></li>
      <li><a href="img/detail_homology_l.png" rel="prettyPhoto[a]"
      title="Homologue genes in in the detail view."><img src="img/detail_homology_s.png" width="119"
      height="65" alt="Step 3: Homologue genes in in the detail view." /> </a></li>
    </ul>

  </div>
</div>
<div class="lr">
  <div class="prettybox">
    <h3>Compare source project</h3>
    <?php
    showProjectList($projects,true);
    ?>
  </div>
</div>
<div class="lr">
  <div class="prettybox">
    <h3>...with target project</h3>
    <?php
    showProjectList($projects,false);
    ?>
  </div>
</div>
<br style="clear: both;" />
    <?php

    if($projects[0]==NULL || $projects[0]=="NULL"){
    	endPage();
    	exit();
    }

    // only the database of the source project needs to be opened
    $src_proj = $projects[0];
    connectToQtlDBs(array($src_proj));
    $qtldb = $compara_array[$src_proj]['connection'];
    $compara = connectToCompara(3306,true);


    // region selection

    // fetch chromosomes to species id
    $ens_species = $compara_array[$src_proj]['ensembl_species'];
    $chrs = getChromosomesAndLengths($compara,$ens_species);
    // additional filtering
    $chrs = filter_chromos($qtldb, $chrs);

    $species = $compara_array[$src_proj]['species'];

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
<div class="prettybox">
  <h3>
    Add regions for species
    <?php echo $species;?>
  </h3>
  <table border="1" cellpadding="3" cellspacing="0">
    <tr>
      <th>Chromosome</th>
      <th>length (bp)</th>
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
</div>
<p>
  <label for="conf">Length of confidence intervall around each locus: </label><input
    id="conf" type="text" size="4"
    value="<?php echo $confidence_int_len; ?>" /> cM
</p>
<p>
  &nbsp;&nbsp;<input type="button" onclick="submit_page('overview')"
    value="show Synteny" />
</p>
    <?php
    endPage();
    ?>
