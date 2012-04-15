<?php

/**

STARTOFDOCUMENTATION

=pod

=head1 NAME

qtl.php - present singular effects as expression QTL

=head1 SYNOPSIS

a dynamic weg page - may be invoked without parameters

=head1 DESCRIPTION

This page presents all traits (gene expressions) that belong to the same
module. The module is named by a colour, which means nothing more than
to be easily remembered and to look nicer than mere numbers. The genes
in the module are offered to be ranked for their meaning in the context
of a clinical phenotype and varies between genes. Module-membership of
traits is independently calculcated from those phenotypes. For that, the
very same genes may have different significances for an association with
the different phenotypes, but always the same affinity to a particular
module. What module to look at, you select from I<Module Colour>
selection box. To select the I<Clinical Phenotype>, use the respective
other selection box.

The output table also shows the QTL for every molecular trait. You
can constrain the search by specifying three minimal values for
the module membership of a trait (I<module membershipi>),
the association with a phenotype (I<gene significance>) and
the association with a chromosomal locus (I<LOD score> - coming
later tonight). The wording from this page relects that of R's CRAN <a
href='http://www.genetics.ucla.edu/labs/horvath/CoexpressionNetwork/Rpackages/WGCNA/'>WGCNA</a>
software package that is used to perform those analyses.

The analysis should start by a click to the checkbox right below the
entry form to have a diagram popped up that presents the association of
modules to all the clinical phenotypes. At some time we hope to generate
the image dynamically and allow you to click on it. For the time speaking,
please just look at the writing at the left and at the bottom to find
the values to select from the respective modules and phenotype selection
dropdown boxes. Suggestions on how to present the data more easily please
let flow in. More work on the linking to the GeneOntology.

The most recent addition was on pathway analyses, which are available
upon a mouse click for every module.

=head1 ATTRIUTES

=head2 Required

=over 4

=item module

a string identifying the I<colour> name of a module

=item phen

a string identifying the clinical phenotype

=back

=head2 Optional

=over 4

=item gs

the minimal value for the gene significance

=item mm

the minimal value for the module membership

=item LODmin

the minimal value a LOD score should sensibly have for this query

=back

=head1 AUTHORS

Yask Gupta <yask.gupta@uk-sh.de> with contributions by Steffen ME<ouml>ller

=head1 COPYRIGHT

UK-SH Schleswig-Holstein, LE<uuml>beck, Germany, 2010-2011

=cut

*/

  require_once("header.php");

?>

<script>
function showMe (it, box) {
  var vis = (box.checked) ? "block" : "none";
  document.getElementById(it).style.display = vis;
}
</script>

<?php
  require_once("func_connecti.php");
  show_small_header("Modules for $projecttitle",TRUE);

  // retrieva data from previous form execution

  $accessible_fields_in_POST_or_GET = array(
  	"modcolour","clinical","mm","gs","LODmin","order"
  );


  if (isset($_POST["debug"]) or isset($_GET["debug"])) {
		echo "<br>Retrieving info for: "; print_r($accessible_fields_in_POST_or_GET); echo "<br>\n";
  }
			
  foreach($accessible_fields_in_POST_or_GET as $vname)
  {
     if (isset($_POST[$vname])) {
        $$vname = ltrim(rtrim($_POST[$vname]));
        if (isset($_POST["debug"]) or isset($_GET["debug"])) {
           echo $vname."=".$$vname."\t";
        }
     }
     elseif(isset($_GET[$vname])) {
        $$vname = ltrim(rtrim($_GET[$vname]));
        if (isset($_POST["debug"]) or isset($_GET["debug"])) {
           echo $vname."=".$$vname."\t";
        }
     }
  }

  // build form with previous data shown

  $mod_query = mysqli_query($linkLocali,"select distinct moduleColor from module_trait_moduleMembership");

  echo "<p>This page presents all traits (gene expressions) that belong to the same module. The module is named by a colour, which means nothing more than to be easily remembered and to look nicer than mere numbers. The genes in the module are offered to be ranked for their meaning in the context of a clinical phenotype and varies between genes. Module-membership of traits is independently calculcated from those phenotypes. For that, the very same genes may have different significances for an association with the different phenotypes, but always the same affinity to a particular module. What module to look at, you select from <i>Module Colour</i> selection box. To select the <i>Clinical Phenotype</i>, use the respective other selection box.</p>";
  echo "<p>The output table also shows the QTL for every molecular trait. You can constrain the search by specifying three minimal values for the module membership of a trait (<i>module membership</i>), the association with a phenotype (<i>gene significance</i>) and the association with a chromosomal locus (<i>LOD score</i> - coming later tonight). The wording from this page relects that of R's CRAN <a href='http://www.genetics.ucla.edu/labs/horvath/CoexpressionNetwork/Rpackages/WGCNA/'>WGCRA</a> software package that is used to perform those analyses.</p>";
  echo "<p>The analysis should start by a click to the checkbox right below the entry form to have a diagram popped up that presents the association of modules to all the clinical phenotypes. At some time we hope to generate the image dynamically and allow you to click on it. For the time speaking, please just look at the writing at the left and at the bottom to find the values to select from the respective modules and phenotype selection dropdown boxes. Suggestions on how to present the data more easily please let flow in. More work on the linking to the GeneOntology and pathway analyses have just now been addeed.</p>";

  echo "<form action='modules.php' method='post'>";
  echo "<table width=100% border=0>";
  	// outer table for left and right column
  echo "<tr><td valign=top>\n";
  echo "<h2>Required</h2>\n";
  	// left column
  echo "<table border=0>";
  echo "<tr><th align=right nowrap>Module Colour:</th><td>";
  echo "<select name='modcolour'>";
  while($row = mysqli_fetch_array($mod_query)) {
      $r=$row["moduleColor"];
      echo "<option value=\"$r\" ".("$r"=="$modcolour"?" selected":"").">$r</option>";
  }
  echo "</select></td>";
  echo "<td><font color=blue>Modules are groups of genes that are pairwise connected since they strongly correlate with the same set of genes.</font></td>";
  echo "</tr>";

  $mod_query = mysqli_query($linkLocali,"SHOW COLUMNS FROM module_trait_pheno_geneSignificance "
                                                    ."WHERE Field LIKE '%p_GS%'");
  echo "<tr><th align=right nowrap>Clinical Phenotype: </th><td>";
  echo "<select name='clinical'>";
  if (mysqli_num_rows($mod_query) > 0) {
      while ($r = mysqli_fetch_assoc($mod_query)) {
           $cli= preg_replace("/p_GS_/","",$r["Field"]);
           echo "<option value=\"$cli\"".("$cli"=="$clinical"?" selected":"").">".$cli."</option>";
      }
  }
  echo "</select></td>";
  echo "<td><font color=blue>"
           ."All modules where evaluated for their association with each phenotype."
	   ."</font></td>";
  echo "</tr>\n";
  echo "</table>\n";

  echo "</td><td valign=top>\n";

  echo "<h2>Optional</h2>\n";

  echo "<table border=0>\n";
  echo "<tr><th align=right nowrap>Module Membership:</th>"
          ."<td><input type='text' name='mm' size=4".(empty($mm)?"":" value='$mm'")."></td>"
	  ."<td><font color=blue> Module Membership implies the membership of each gene in the given module</td>"
       ."</tr>\n"; 

  echo "<tr><th align=right nowrap>Gene Significance:</th>"
          ."<td><input type='text' name='gs' size=4".(empty($gs)?"":" value='$gs'")."></td>"
	  ."<td><font color=blue> Gene significance implies the significance of gene for the phenotype in the given module </td>"
       ."</tr>"; 
  echo "</td></tr>\n";

  echo "<tr><th align=right nowrap>Minimal LOD:</th>"
          ."<td><input type='text' name='LODmin' size=4".(empty($LODmin)?"":" value='$LODmin'")."></td>"
	  ."<td><font color=blue>Minimal LOD score for trait to show in list</td>"
       ."</tr>\n"; 

  echo "<tr><th align=right nowarp>Order:</th>"
          ."<td><select name='order'>";
  $entries = array(
     "Mol Trait (Chr, Pos), LOD" => "trait.chromosome ASC, pos ASC, qtl.LOD DESC",
     "Mol Trait (ID), LOD"       => "trait_id ASC, qtl.LOD DESC",
     "QTL (Chr,Pos), LOD"        => "qtl_Chromosome ASC, qtl_cMorgan_Peak ASC, qtl.LOD DESC",
     "Expression level, LOD"     => "trait.mean DESC, qtl.LOD DESC"
  );
  foreach ($entries as $n => $v) {
      echo "<option value=\"$v\" ".("$v"=="$order"?" selected":"").">$n</option>";
  }
  echo "</select></td>"
       ."</tr>\n";

  echo "</table>\n";

  echo "<tr><td colnum=2 align=center><input type='submit' name='submit'/></td><tr>";
  echo "</table>";
  echo "</form>";

  foreach (array("png","jpg") as $filetype) {
    if (!file_exists("tmp_images/module_trait_relationship_map.$filetype")) continue;
    echo '<input type="checkbox" name="c1" onclick="showMe(\'div1\', this)">';
    echo "<b>Check this box to see Module Trait Relationship diagram. Identify the modules that are highest in their association with clinical phenotypes. The selection perfom by the drop-down menus showing respective module colours and phenotypes.</b>\n";
    echo '<div id="div1"style="display:none"';
    echo "<a><img width='100%' src='tmp_images/module_trait_relationship_map.$filetype' ISMAP/></a>";
    echo "</div>\n";
    break;
  }

  echo "<hr />\n";

  // perform database search

  if (!empty($modcolour) and !empty($clinical)) {

    require_once("func_public_qtl.php");
    require_once("func_conversion_58.php");
    $public_qtls=get_public_qtls($linkLocali,"name");
    print_r($public_qtls);

    echo "<p><a href=\"networks/presentModule.php?modcolour=$modcolour\">"
            ."<b>Click to open gene network for module with Cytoscape</i>)</b>"
	    ."</a>"
	."</p>\n";
    $query = "SELECT module_trait_moduleMembership.trait_id as trait_id"
                   .",trait.gene_name as gene_name"
                   .",trait.chromosome as chromosome"
                   .",round((trait.start+trait.stop)/2)/1000000 as pos"
                   .",trait.mean as mean"
                   .",trait.sd as sd"
                   .",trait.name as name"
             #      .",BEARatChip.first_name,"
                   .",module_trait_pheno_geneSignificance.GS_".$cli
                   .",module_trait_moduleMembership.MM_".$modcolour
                   .",qtl.Chromosome as qtl_Chromosome"
                   .",qtl.cMorgan_Peak as qtl_cMorgan_Peak"
                   .",qtl.covariates as qtl_covariates"
                   .",qtl.LOD as qtl_lod"
                   #.",BEARatChip.pathway "
	     ." FROM module_trait_moduleMembership "
	     #     ." LEFT JOIN BEARatChip ON module_trait_moduleMembership.trait_id=BEARatChip.probeset_id "
                   ." LEFT JOIN module_trait_pheno_geneSignificance USING(trait_id) "
                   ." LEFT JOIN trait USING(trait_id) "
                   ." LEFT JOIN qtl on module_trait_moduleMembership.trait_id=qtl.Trait "
             ." WHERE module_trait_moduleMembership.moduleColor='$modcolour' "
                #  ."AND name != '' "
                   ."";

    if (!empty($mm)) {
        $query .= "AND module_trait_moduleMembership.MM_".$modcolour." >= ".$mm;
    }
    if (!empty($gs)) {
        $query .= "AND module_trait_pheno_geneSignificance.GS_".$cli." >= ".$gs;
    }
    if (!empty($LODmin)) {
        $query .= "AND qtl.LOD >= ".$LODmin." ";
    }

    if (empty($order)) {
	    $order .= "trait.chromosome ASC, trait_id, qtl.LOD DESC";
    }
    $query .= "ORDER BY $order";
        
    $rec_query = mysqli_query($linkLocali,$query);
    echo "<p><small>query: $query</small></p>\n";
    if (0 != mysqli_errno($linkLocali)) {
    	errorMessage("Could not execute query:".mysqli_error($linkLocali));
	mysqli_close($linkLocali);
        include("footer.php");
	exit;
    }

    echo "<table border=1 cellspacing=0 width=100%>";
    echo "<thead align>";
    echo "<tr bgcolor=yellow>";
    echo "<th>Trait ID</th>";
    echo "<th nowrap>Chromosome<br />Position (Mbp)</th>";
    echo "<th nowrap>Expression<br />mean +- sd</th>";
    echo "<th>Function</th><th>Gene Significane<br />($clinical)</th>";
    echo "<th>Module Membership<br />($modcolour)</th>";
    if (! FALSE === strpos($query,"athway")) {
        echo "<th>Pathway</th>";
    }
    echo "<th>Chromosome:Peak (cM):Covariates:LOD</th></tr></thead>\n";
    echo "<tbody>";
	
    $prevTrait="";
    while ($row1 = mysqli_fetch_assoc($rec_query)) {
        //print_r($row1);
	if (empty($prevTrait)) print_r($row1);
        if (!empty($prevTrait) and $prevTrait != $row1["trait_id"]) echo "</td></tr>\n";
        if ($prevTrait != $row1["trait_id"]) {
            echo "<tr>";
	    echo "<td valign=top>".$row1["trait_id"]."<br />".$row1["gene_name"]."</td>";
            foreach ($row1 as $n => $v) {
		if ("chromosome" == "$n") {
		    $pos=$row1["pos"];
                    echo "<td valign=top align=center>".(empty($v)?"&nbsp;":
                                              ("$v<br />".round($pos,3)))
                                              ."</td>";
                } else if ("mean" == "$n") {
		    $sd=$row1["sd"];
                    echo "<td valign=top align=center>".(empty($v)?"&nbsp;":
                                              (round($v)."<br /><small>+- ".round($sd,2)))
                                              ."</small></td>";
                } else if
		   ("$n" != "pos" and 
		    "$n" != "sd" and 
		    "$n" != "trait_id" and 
		    "$n" != "gene_name" and 
		    "$n" != "qtl_cMorgan_Peak" and 
		    "$n" != "qtl_Chromosome" and 
		    "$n" != "qtl_covariates" and 
		    "$n" != "qtl_lod")
                {
                    echo "<td valign=top>".(empty($v)?"&nbsp;":"$v")."</td>";
                }		
            }
	    /*
	    echo "<td nowrap>";
	    echo "<!-- QTL known to overlap with gene location -->\n";
	    echo "</td>\n";
	    */
            echo "<td nowrap>";
        }
		  
        if ($prevTrait == $row1["trait_id"]) {
            echo "<br />";
        }
        $col="black";
        if ($row1["qtl_Chromosome"]==$row1["chromosome"]) $col="red";
        echo "<font color='$col'>";
        echo $row1["qtl_Chromosome"].":".$row1["qtl_cMorgan_Peak"]." ".(empty($row1["qtl_covariates"])?"none":$row1["qtl_covariates"]).":".$row1["qtl_lod"];
	if (!empty($row1["qtl_Chromosome"]) and isset($row1["qtl_cMorgan_Peak"]) and "" != $row1["qtl_cMorgan_Peak"]) {
		$bp=cM2bp($row1["qtl_Chromosome"],$row1["qtl_cMorgan_Peak"]);
		//echo("!!!bp=$bp!!!");
		$overlapping_qtl = withinthefollowingqtls($row1["qtl_Chromosome"],$bp,$public_qtls,FALSE);
		if (!empty($overlapping_qtl) and 0 < count($overlapping_qtl)) {
			echo " (<font color='green'><b>";
			echo join(",",$overlapping_qtl);
			echo "</b></font>)";
		}
		//print_r($overlapping_qtl);
	}
        echo "</font>";
        $prevTrait=$row1["trait_id"];
    }
    if (!empty($prevTrait)) echo "</td></tr>\n";
    echo "</tbody></table>";
  }
  else if (empty($clinical) and empty($modcolour)) {
    echo "<p>Please select a clinical phenotype and a module, then press the <i>submit</i> button.</p>\n";
  }
  else if (empty($clinical)) {
    echo "<p>Please also select a clinical phenotype.</p>\n";
  }
  else if (empty($modcolour)) {
    echo "<p>Please also select a module.</p>\n";
  }
       
  mysqli_close($linkLocali);
  include("footer.php");
?>
