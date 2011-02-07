<?php
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

$speciesArray = array("Rattus norvegicus","Mus musculus");

//$bp = 140000;
//$cM = bp2cm(2,$bp,$targetSpecies);
if (empty($_GET)){
	$chr = 10;
	$bp = 13000;
	$targetSpecies = "Rattus norvegicus";
}else {
	$chr = $_GET['chr'];
	$bp = $_GET['bp'];
	$targetSpecies = $_GET['species'];
}
if($targetSpecies=="Mus musculus"){
	useDB('eqtl_rostock_eae',$targetdb);
}else{
	useDB('eqtl_stockholm_eae_logplier',$targetdb);
}
?>
<form method="get">
<p>Basepairs: <input type="text" name="bp" value="<?php echo $bp;?>" /></p>
<p>Chromosome: <input type="text" name="chr" value="<?php echo $chr;?>" />
</p>
<p>Species: <select name="species" size="2">
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
</select></p>
<input type="submit" /></form>
<h3>Conversion: bp <?php echo ($bp);?> to cM: <?php echo bp2cm($chr,$bp,$targetSpecies);?></h3>

<?php 

?>