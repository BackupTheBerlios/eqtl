<?php
date_default_timezone_set('Europe/Berlin');
require_once 'bp2cM_conversion.php';
require_once 'db_functions.php';

//TODO: -----------Change so we get the correct database from other file...--------------
$targetdb = @new mysqli('127.0.0.1', 'root', 'DBconnect', 'eqtl_rostock_eae');
if (mysqli_connect_errno()) {
	trigger_error('Could not connect to database: '.mysqli_connect_error().'('.mysqli_connect_errno().')');
}
$db = connectToCompara();
//----------------------------------------------------------------------------------------
if (empty($_GET)){
	$locus = 'c5.loc57';
}else {
	$locus = $_GET['locus'];
}
$chromoCM = locus2chromo($targetdb,$locus);
$chromoCmString = implode(", ", $chromoCM[0]);
?>
<form method="get">
<p>Locus: <input type="text" name="locus" value="<?php echo $locus;?>" /></p>
<input type="submit" /></form>
<h3>locus2chromo:<br /> Locus: <?php echo ($locus);?>Chromosom and cMorgan <?php echo $chromoCmString;?></h3>
<!-- TODO: -------------------------hardcoded Species name!!! not good...--------------------------- -->
<?php $species_name = 'Mus musculus';
$bp = cM2bp($chromoCM[0][0],$chromoCM[0][1],$species_name);?>
<h3>cm2br:<br />Conversion: CM <?php echo ($chromoCM[0][1]);?> to bp: <?php echo $bp;?></h3>

<?php
$genome_db_id = 57;
$syntenyRegions = getDnafragIDs($db, $bp, $genome_db_id);

$loci = array('c14.loc25','cX.loc14','c10.loc38','c11.loc46','D18Mit19','c14.loc10','D11Mit86',
'c8.loc50','c1.loc90','c12.loc12','D4Mit256','c3.loc53','c3.loc54','c17.loc23','c9.loc60',
'c9.loc61','c17.loc39','c6.loc6','c11.loc73','c2.loc116','c2.loc112');

$result = array();
foreach ($loci as $compLocus){
	$chromoCM = locus2chromo($targetdb,$compLocus);
	$bp = cM2bp($chromoCM[0][0],$chromoCM[0][1],$species_name);
	$compSyntenyRegions = getDnafragIDs($db, $bp, $genome_db_id);
	$tempIntersection = array_intersect($syntenyRegions, $compSyntenyRegions); 
	if (!empty($tempIntersection)) {
		$result[$locus][] = $compLocus;
	}
}
var_export($result);
?>