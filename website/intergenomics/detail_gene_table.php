<?php
// $ Id: 2010-12-19 gz exp$
// displays detailed homology information for two loci groups from different experiments
// Experiment 1 is over the colums
// Experiment 2 is over the rows

/**
 * Splitts the ensemble stable gene id in little - 3 chars big - parts
 * so that the string isn't to long.
 * if($cis_lookup[$key]) the header cell gets the class "ciss".
 *
 * @param $ens_id the ensemble stable gene id ($ens_id)
 * @param $key the key of the ensemble stable gene id ($ens_id)
 * @param $cis_lookup an array with boolean entries.
 * 	if($cis_lookup[$key]) the header cell gets the class "ciss".
 */
function split_and_ciss(&$ens_id,$key,$cis_lookup){
	$prefix = '<th rowspan="5"';
	if($cis_lookup[$key]){
		$prefix .= ' class="ciss">';
	}else{
		$prefix .= '>';
	}
	$ens_id = $prefix.chunk_split($ens_id,3,"<br />\n");
}
?>
<div style="font-size: small;"><!-- the display table -->
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <!-- THIS IS ASCII-ART! -->
      <th rowspan="6">\ Ex. 1<br />
      \&nbsp;&nbsp;&nbsp;&nbsp;<br />
      \<br />
      &nbsp;&nbsp;&nbsp;&nbsp;\<br />
      Ex. 2 \</th>
      <!-- ID-column header -->
      <th rowspan="6">homologue <br />
      Ensembl <br />
      stable <br />
      IDs</th>
      <?php
      foreach ($loci2stable_ids_ex1[0] as $locus_ex1 => $ens_ids_ex1){
      	if(empty($ens_ids_ex1)){
      		// FIXME: If a locus does not affect any genes
      		// we skip it here.
      		continue;
      	}
      	echo '<th colspan="'.sizeof($ens_ids_ex1).'">';
      	echo $locus_ex1.'</th>';
      }
      ?>
    </tr>
    <tr>
    <?php
    foreach ($loci2stable_ids_ex1[0] as $locus_ex1 => $ens_ids_ex1){
    	if(empty($ens_ids_ex1)){
    		// FIXME: If a locus does not affect any genes
    		// we skip it here.
    		continue;
    	}
    	$tmp = $ens_ids_ex1;
    	array_walk($tmp, "split_and_ciss", $loci2stable_ids_ex1[1][$locus_ex1]);
    	echo implode('</th>',$tmp)."</th>\n";
    }
    ?>
    </tr>
  </thead>
  <tbody>
  <?php
  foreach ($loci2stable_ids_ex2[0] as $locus_ex2 => $ens_ids_ex2){
  	if(empty($ens_ids_ex2)){
  		// FIXME: If a locus does not affect any genes
  		// we skip it here.
  		continue;
  	}
  	echo "<tr>";
  	echo '<th rowspan="'.sizeof($ens_ids_ex2).'">';
  	echo $locus_ex2.'</th>';
  	$firstrow = true;
  	$i = 0;
  	foreach ($ens_ids_ex2 as $ens_id_ex2) {
  		if($firstrow){
  			$firstrow = false;
  		}else{
  			echo "<tr>";
  		}
  		if($loci2stable_ids_ex2[1][$locus_ex2][$i++]){
  			echo '<th class="ciss">';
  		}else{
  			echo "<th>";
  		}
  		echo $ens_id_ex2."</th>";
  		foreach ($loci2stable_ids_ex1[0] as $locus_ex1 => $ens_ids_ex1) {
  			//  			if(in_array($locus_ex2, $synteny_ex12ex2[$locus_ex1])){
  			//  				$syntenic_cell = '<td class="syn"></td>';
  			//  			}else{
  			//  				$syntenic_cell = '<td></td>';
  			//  			}
  			//  			if(empty($ens_ids_ex1)){
  			//  				echo "<td/>";
  			//  			}//else loop don't run
  			foreach ($ens_ids_ex1 as $ens_id_ex1){
  				if(in_array($ens_id_ex2, $traits12traits2[$ens_id_ex1])){
  					// this is seldom so we spend a function call here
  					//  					if(strlen($syntenic_cell)>10){
  					//  						echo '<td class="homologue syn">Hom</td>';
  					//  					}else{
  					echo '<td class="homologue">Hom</td>';
  					//  					}
  				}else{
  					echo '<td />';
  				}
  			}
  		}
  		echo "</tr>\n";
  	}
  }
  ?>
  </tbody>
</table>
</div>
