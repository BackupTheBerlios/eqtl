<?php
// for display
// genome_db_id => name
//$speciesArray = array(3 => "Rattus norvegicus", 57 => "Mus musculus");
?>
<div id="infobox">
<div style="float: left; width: 40%;">
<h3>Input-loci Experiment 1 (Mus)</h3>
<?php
echo implode(", ",$loci_ex1);
?></div>
<div style="float: right; width: 40%;">
<h3>Input-loci Experiment 2 (Rat)</h3>
<?php
echo implode(", ",$loci_ex2);
?></div>
<div style="clear: both;"></div>
</div>
