<?php
require_once("db_functions.php");
// git push ssh://georgzeplin@git.berlios.de/gitroot/eqtl Praktikum
date_default_timezone_set('Europe/Berlin');
$comparaSource=$_POST["comparaSource"];
#print_r($comparaSource);

?>
<INPUT TYPE=BUTTON VALUE="Back"
  onClick="history.back()">
<br />
<br />
<?php
foreach ($comparaSource as $xx => $dnafrag_region){
	$data = explode('|', $dnafrag_region);
	$start = $data[2];
	$end = $data[3];
	$strand = $data[4];
	$id = $data[1];


	$db = connectToCompara();

	?>
<h4>Overlapping segments and syntenies for species " <?php 
$sourceSpecies = getSpeciesName($db, $id);
$para = getDnafragParameter($db, $id);
echo $sourceSpecies.'" on '.$para['coord_system_name'].' '.$para['name'].' with length: '.$para['length'];
?></h4>

<table cellpadding="1" cellspacing="3" border="1">
  <tr>
    <th>source begin</th>
    <th>source end</th>
    <th>syntenic id</th>
    <th>target species name</th>
    <th>target <br>
    chromosome <br>
    name</th>
    <th>target begin</th>
    <th>target end</th>
    <th>view Traits</th>
  </tr>
  <?php
  $sql = 'select *
from dnafrag_region 
where (dnafrag_start >='.$start.' AND dnafrag_start <= '.$end.' 
 OR dnafrag_end <= '.$end.' 
 AND dnafrag_end >= '.$start.') 
 AND dnafrag_id = '.$id.' 
 AND dnafrag_strand = '.$strand.';';

  $result = $db->query($sql)or trigger_error('Query failed: '.$db->error);

  $anzahlspalten = mysqli_num_fields($result);

  $rows = $result->fetch_all();
  $num_rows = $result->num_rows;
  // Output of the contents
  for ($i = 0; $i < $num_rows; $i++) {
  	$row = $rows[$i];

  	// Output of the begin and end of the source
  	echo "<tr> <td>".$row[2].'</td><td>'.$row[3]."</td>\n";

  	// select syntenic species
  	$syntenicSQL = 'select r.synteny_region_id, g.name, d.name, r.dnafrag_start, r.dnafrag_end
	from dnafrag_region as r, dnafrag as d, genome_db as g
	where r.synteny_region_id ='.$row[0].' 
	AND d.dnafrag_id = r.dnafrag_id  
	AND g.genome_db_id = d.genome_db_id
	and not(d.dnafrag_id ='.$row[1].');';

  	$syntenicResult = $db->query($syntenicSQL) or trigger_error('Query failed: '.$db->error);
  	# only one dnafrag is syntenic to another dnafrag with the same synteny_region_id
  	$syntenicRow = $syntenicResult->fetch_all();
  	$val = $syntenicRow[0];

  	echo " <td>";
  	echo implode("</td><td>",$val);
  	echo "</td>\n";
  	$linkvars = 'species='.str_replace(" ","+",$val[1]).'&chr='.$val[2].'&bpstart='.$val[3].'&bpend='.$val[4];
  	echo '<td><a href="qtl_visual.php?'.$linkvars.'">view qtls</a></td>';
  	echo '</tr>';

  }

  ?>
</table>
<br />
  <?php }# end loop on checkboxes
  ?>