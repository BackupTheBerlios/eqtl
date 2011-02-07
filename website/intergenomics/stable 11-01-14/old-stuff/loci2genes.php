<?php
include 'header.html';
date_default_timezone_set('Europe/Berlin');
require_once 'bp2cM_conversion.php';
require_once 'db_functions.php';

$targetdb = @new mysqli('127.0.0.1', 'anonymous', 'no', 'eqtl_rostock_eae');
if (mysqli_connect_errno()) {
	trigger_error('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
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
<p>Species: <select name="species" size="1">
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
</select>, Chromosome: <input type="text" name="chr"
  value="<?php echo $chr;?>" size="3"
/>, start: <input type="text" name="bpstart" size="12"
  value="<?php echo $bpstart;?>"
/>, end <input type="text" name="bpend" size="12"
  value="<?php echo $bpend;?>"
/>&nbsp; <input type="submit" value="refresh" /></p>
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
$vals = array_map("current", $rows);
echo '<p>Loci: '.implode(", ",$vals).' </p>';
unset($rows);

// 2.
$sql2 = 'select Trait from qtl
 where Locus in (\''.implode("', '",$vals).'\');';
//echo $sql2;
$result2 = $targetdb->query($sql2) or trigger_error('Query failed: '.$targetdb->error);
$rows = $result2->fetch_all();
$num_rows = $result2->num_rows;
$vals = array_map("current", $rows);
echo '<p>Traits: '.implode(", ",$vals).' </p>';
unset($rows);

// 3.
$sql3 = 'select gene_name
 from Trait
 where trait_id in(\''.implode("', '",$vals).'\');';
//echo $sql3;
$result3 = $targetdb->query($sql3) or trigger_error('Query failed: '.$targetdb->error);
$rows = $result3->fetch_all();
$num_rows = $result3->num_rows;
$vals = implode(", ",array_map("current", $rows));
echo '<p>Traitnames: '.$vals.' </p>';
unset($rows);
include 'footer.html';
?>
