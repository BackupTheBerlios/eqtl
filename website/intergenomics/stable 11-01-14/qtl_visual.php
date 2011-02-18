<?php
date_default_timezone_set('Europe/Berlin');
include 'html/visualheader.html';
require_once 'bp2cM_conversion.php';
require_once 'db_functions.php';
require_once 'qtl_functions.php';


$targetdb = @new mysqli('127.0.0.1', 'anonymous', 'no', 'eqtl_rostock_eae');
if (mysqli_connect_errno()) {
	trigger_error('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')',
	E_USER_ERROR);
}

# supported target species:
# rat: stockholm "Rattus norvegicus"
# mus: rostock "Mus musculus"

if (empty($_GET)){
	$bpstart = 115311888;
	$bpend = 125710881;
	$chr = "X";
	$targetSpecies = "Rattus norvegicus";
}else {
	$chr = $_GET['chr'];
	$bpstart = $_GET['bpstart'];
	$bpend = $_GET['bpend'];
	$targetSpecies = $_GET['species'];
}
if($targetSpecies=="Mus musculus"){
	useDB('eqtl_rostock_eae',$targetdb);
}else{
	useDB('eqtl_stockholm_eae_logplier',$targetdb);
}

// display arguments:
?>
<form method="get">
<div id="infobox">
<h3>DNA fragment region information</h3>
<p>Species: <select
  name="species"
  size="1"
>
<?php
for ($i = 0; $i < sizeof($speciesArray); $i++) {
	echo '<option name="species" value="'.$speciesArray[$i].'" ';
	if($speciesArray[$i]==$targetSpecies){
		echo ' selected >';
	}else{
		echo ' >';
	}
	echo $speciesArray[$i].'</option>';
}
?>
</select>, Chromosome: <input
  type="text"
  name="chr"
  value="<?php echo $chr;?>"
  size="3"
/>, start: <input
  type="text"
  name="bpstart"
  size="12"
  value="<?php echo $bpstart;?>"
/>, end <input
  type="text"
  name="bpend"
  size="12"
  value="<?php echo $bpend;?>"
/>&nbsp; <input
  type="submit"
  value="refresh"
/></p>
</div>
</form>
<?php

// 1.
$sql1 = 'select Name from locus
		where Chr=\''.$chr.'\'
		and cMorgan >= '.bp2cm($chr,$bpstart,$targetSpecies).'
		and cMorgan <= '.bp2cm($chr,$bpend,$targetSpecies).';';

$result1 = $targetdb->query($sql1) or trigger_error('Query failed: '.$targetdb->error);
$rows = $result1->fetch_all();
if(!$result1->num_rows){
	trigger_error("Could not fetch any Loci for your region!", E_USER_ERROR);
}
$loci = array_map("current", $rows);
unset($rows);
$storage = array();
$allTraits = array();

foreach ($loci as $idx => $locus ){
	$sql2 = 'select Trait, pvalue from qtl
 where Locus = \''.$locus.'\';';
	//echo $sql2;
	$result2 = $targetdb->query($sql2) or trigger_error('Query failed: '.$targetdb->error);
	$rows = $result2->fetch_all();
	$num_rows = $result2->num_rows;
	$ids = array_map("current", $rows);
	$pvals = array_map("next", $rows);
	$combined = array_combine($ids,$pvals);
	$storage[$locus] = $combined;
	$allTraits = array_unique(array_merge($allTraits,$ids));
}



/*
 * fill the correlation array with default values, if a correlation between an
 * trait and lous not exists.
 */
fillDefaults($storage,$allTraits);

?>
<script type="text/javascript">
	google.setOnLoadCallback(drawHeatMap);
	function drawHeatMap() {
		var data = new google.visualization.DataTable();
		<?php
		// dynamic data filling in the heatmap. This procedure is slow. 
		// Possible improvements are: Reading Data from csv-File 
		echo "data.addColumn('string', 'Gene Name');\n";
		foreach ($storage as $locus => $combined){
			echo "data.addColumn('number', '".$locus."');\n";
		}
		echo "data.addRows(".sizeof($allTraits).");\n\n";
		$trait_idx = -1;
		foreach ($allTraits as $trait_id) {
			echo "data.setCell(".++$trait_idx.", 0, '".$trait_id."');\n";
			$i = 1;
			foreach ($storage as $locus => $combined) {
				$val = $combined[$trait_id];
				if ($val==$default) {
					echo "data.setCell(".$trait_idx.", ".$i++.", null);\n";
				}else{
					echo "data.setCell(".$trait_idx.", ".$i++.", ".$val.");\n";
				}
			}
		}
		?>
		
		heatmap = new org.systemsbiology.visualization.BioHeatMap(document
				.getElementById('heatmapContainer'));
		heatmap.draw(data, {});
	}
</script>

<div id="heatmapContainer"></div>
		<?php
		include 'html/footer.html';
		?>
