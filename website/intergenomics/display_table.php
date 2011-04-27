<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 display_table.php -

 =head1 SYNOPSIS

 =head1 DESCRIPTION

 =head1 AUTHOR

 Michael Brehler <brehler@informatik.uni-luebeck.de>,
 Georg Zeplin <zeplin@informatik.uni-luebeck.de>,

 =head1 COPYRIGHT

 University of LE<uuml>beck, Germany, 2011

 =cut

 ENDOFDOCUMENTATION
 */

// Experiment 1 (mus) is over the colums
// Experiment 2 (rat) is over the rows

include 'utils/colors.php';
prepareColors($groups1,$groups2);
?>

<script
  src="js/mouseposition.js" type="text/javascript"></script>

<p>
  Experiment 1 (species name) over the columns: <span id="species1"
    class="display_species"><?php echo $projects[0]." (".$species_names[0].")";?>
  </span>; Experiment 2 (species name) over the rows: <span
    id="species2" class="display_species"><?php echo $projects[1]." (".$species_names[1].")";?>
  </span>.<br />click on a synthenic region for details or click <a href="img/synteny_l.png" rel="prettyPhoto" title="Syntenic regions view for the selected chromosomes.">HERE</a> for legend.
</p>
<div style="display: none;">
  <div id="exp1">
  <?php echo implode("+", explode(" ", $projects[0])) ?>
  </div>
  <div id="exp2">
  <?php echo implode("+", explode(" ", $projects[1])) ?>
  </div>
</div>

<div style="font-size: small;">
  <!-- the display table -->
  <table border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>syntenic <br /> loci <br /> groupnumbers</th>
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
        	echo '<th style="background-color:'.$colorArray[$colIndex].'" title="region '.($group2region[$i]+1).'">'.$groups1[$i]['Chr'].'<br />'.round($groups1[$i]['start']).'<br />-<br />'.round($groups1[$i]['end']).'</th>';
        }
        ?>
      </tr>
    </thead>
    <tbody>
    <?php
    for ($j = 0; $j < sizeof($groups2); $j++) {
    	$rowArray = array();
    	$boolNonEmpty = false;
    	for ($i = 0; $i < sizeof($groups1); $i++) {
    		if (in_array($j, $groupSynteny_ex12ex2[$i])) {
    			$cnt2 = sizeof($groups2[$j]['loci']);
    			$cnt1 = sizeof($groups1[$i]['loci']);
    			$rowArray[$i] = '<td class="syn"'.getColor($cnt1+$cnt2).' title="click for detail view">'.$cnt2.' : '.$cnt1.'</td>';
    			$boolNonEmpty = true;
    		}else{
    			$rowArray[$i] = '<td></td>';
    		}
    	}
    	 
    	//TODO: update "if" with an "else" that shows the user -> a row has been deleted!
    	if ($boolNonEmpty) {
    		echo '<tr><th>'.$groups2[$j]['Chr'].'<br />'.round($groups2[$j]['start']).' - '.round($groups2[$j]['end']).'</th>';
    		echo implode("", $rowArray);
    		echo "</tr>\n";
    	}
    }
     
    ?>
    </tbody>
  </table>
</div>
<script type="text/javascript">
window.document.onclick = call_detail_view;
</script>
