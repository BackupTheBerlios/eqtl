<?php
// Experiment 1 (mus) is over the colums
// Experiment 2 (rat) is over the rows

include 'utils/colors.php';
prepareColors($groups1,$groups2);
?>

<script src="js/mouseposition.js"></script>
<script src="js/jquery-1.4.4.min.js"></script>

<p>Spezies of exp.1 over the columns: <span id="species1" class="display_species"><?php echo $species_names[0];?></span>;
Spezies of exp.2 over the rows: <span id="species2" class="display_species"><?php echo $species_names[1];?></span>.</p>
<div style="font-size: small;"><!-- the display table -->
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <th> syntenic <br />
      loci <br />
      groupnumbers</th>
      <?php
      $colorArray = array("#BDB76B","#B8860B","#DAA520","#FFD700");
	  $colIndex = 0;
	  $regionNo = $group2region[0];
      for ($i = 0; $i < sizeof($groups1); $i++) {
        if ($regionNo != $group2region[$i]) {
			$colIndex++;	
			$regionNo = $group2region[$i];
      	}
      	if ($colIndex >= sizeof($colorArray)) {
      		$colIndex =0;
      	}
      	echo '<th bgcolor='.$colorArray[$colIndex].' title="region '.($group2region[$i]+1).'">'.$groups1[$i]['Chr'].'<br />'.round($groups1[$i]['start']).'<br />-<br />'.round($groups1[$i]['end']).'</th>';
      }
      ?>
    </tr>
  </thead>
  <tbody>
  <?php
  for ($j = 0; $j < sizeof($groups2); $j++) {
  	echo '<tr><th bgcolor="#F5F5F5">'.$groups2[$j]['Chr'].'<br />'.round($groups2[$j]['start']).' - '.round($groups2[$j]['end']).'</th>';
  	for ($i = 0; $i < sizeof($groups1); $i++) {
  		if (in_array($j, $groupSynteny_ex12ex2[$i])) {
  			//'.getColor(sizeof($groups2[$j]['loci'])+sizeof($groups2[$j]['loci'])).'
  			$cnt2 = sizeof($groups2[$j]['loci']);
  			$cnt1 = sizeof($groups1[$i]['loci']);
  			echo '<td class="syn"'.getColor($cnt1+$cnt2).' title="click for detail view">'.$cnt2.' : '.$cnt1.'</td>';
  			//echo '<td class="syn">'.$cnt2.' : '.$cnt1.'</td>';
  		}else {
  			echo '<td></td>';
  		}
  	}
  	echo "</tr>\n";
  }

  ?>
  </tbody>
</table>
</div>
<script type="text/javascript">
window.document.onclick = call_detail_view;
</script>