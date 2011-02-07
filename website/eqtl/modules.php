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
let flow in. More work on the linking to the GeneOntology and pathway
analyses is on its way, some surprises are already scheduled for Monday.

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
  	"modcolour","clinical","mm","gs","LODmin"
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
  echo "<p>The analysis should start by a click to the checkbox right below the entry form to have a diagram popped up that presents the association of modules to all the clinical phenotypes. At some time we hope to generate the image dynamically and allow you to click on it. For the time speaking, please just look at the writing at the left and at the bottom to find the values to select from the respective modules and phenotype selection dropdown boxes. Suggestions on how to present the data more easily please let flow in. More work on the linking to the GeneOntology and pathway analyses is on its way, some surprises are already scheduled for Monday.</p>";

  echo "<form action='modules.php' method='post'>";
  echo "<table width=100% border=0>";
  	// outer table for left and right column
  echo "<tr><td>\n";
  echo "<h2>Required</h2>\n";
  	// left column
  echo "<table border=0>";
  echo "<tr><th align=right nowrap>Module Colour:</th><td>";
  echo "<select name='modcolour'>";
  while($row = mysqli_fetch_array($mod_query)) {
      $r=$row["moduleColor"];
      echo "<option".("$r"=="$modcolour"?" selected":"").">$r</option>";
  }
  echo "</select></td>";
  echo "<td><font color=blue>Modules are groups of genes that are pairwise connected since they strongly correlate with the same set of genes.</font></td>";
  echo "</tr>";

  $mod_query = mysqli_query($linkLocali,"SHOW COLUMNS FROM module_trait_pheno_geneSignificance "
                                                    ."WHERE Field LIKE '%p_GS%'");
  echo "<tr><td><br></td></tr>";
  echo "<tr><th align=right nowrap>Clinical Phenotype: </th><td>";
  echo "<select name='clinical'>";
  if (mysqli_num_rows($mod_query) > 0) {
      while ($r = mysqli_fetch_assoc($mod_query)) {
           $cli= preg_replace("/p_GS_/","",$r["Field"]);
           echo "<option".("$cli"=="$clinical"?" selected":"").">".$cli."</option>";
      }
  }
  echo "</select></td>";
  echo "<td><font color=blue>"
           ."All modules where evaluated for their association with each phenotype."
	   ."</font></td>";
  echo "</tr>\n";
  echo "</table>\n";

  echo "</td><td>\n";

  echo "<h2>Optional</h2>\n";

  echo "<table border=0>\n";
  echo "<tr><th align=right nowrap>Module Membership: </th>"
          ."<td><input type='text' name='mm' size=4".(empty($mm)?"":" value='$mm'")."></td>"
	  ."<td><font color=blue> Module Membership implies the membership of each gene in the given module</td>"
       ."</tr>\n"; 

  echo "<tr><th align=right nowrap>Gene Significance:</th>"
          ."<td><input type='text' name='gs' size=4".(empty($gs)?"":" value='$gs'")."></td>"
	  ."<td><font color=blue> Gene significance implies the significance of gene for the phenotype in the given module </td>"
       ."</tr>"; 
  echo "</td></tr>\n";

  echo "<tr><th align=right nowrap>Minimal LOD: </th>"
          ."<td><input type='text' name='LODmin' size=4".(empty($LODmin)?"":" value='$LODmin'")."></td>"
	  ."<td><font color=blue>Minimal LOD score for trait to show in list</td>"
       ."</tr>\n"; 

  echo "</table>\n";

  echo "<tr><td colnum=2 align=center><input type='submit' name='submit'/></td><tr>";
  echo "</table>";
  echo "</form>";

  echo '<input type="checkbox" name="c1" onclick="showMe(\'div1\', this)"><b>Check this box to see Module Trait Relationship diagram. Identify the modules that are highest in their association with clinical phenotypes. The selection perfom by the drop-down menus showing respective module colours and phenotypes.</b>';

  echo '<div id="div1"style="display:none"';
  echo "<a><img width='100%' src='tmp_images/module_trait_relationship_map.png' ISMAP/></a>";
  echo '</div>';

  echo "<hr>";

  // perform database search

  if (!empty($modcolour) and !empty($clinical)) {

    echo "<p><a href=\"demo/visant.php?modcolour=$modcolour\">"
            ."<b>Click to open gene network for module with Visant (<i>coming Monday</i>)</b>"
	    ."</a>"
	."</p>";
    $query = "SELECT module_trait_moduleMembership.trait_id as trait_id,"
                   ."trait.chromosome,round((trait.start+trait.stop)/2)/1000000 as pos,"
                   ."BEARatChip.first_name,module_trait_pheno_geneSignificance.GS_".$cli.","
                   ."module_trait_moduleMembership.MM_".$modcolour.","
                   ."qtl.Chromosome as qtl_Chromosome,"
                   ."qtl.cMorgan_Peak as qtl_cMorgan_Peak,"
                   ."qtl.covariates as qtl_covariates,"
                   ."qtl.LOD as qtl_lod,"
                   ."BEARatChip.pathway "
	     ."FROM module_trait_moduleMembership LEFT JOIN BEARatChip ON module_trait_moduleMembership.trait_id=BEARatChip.probeset_id "
                  ."LEFT JOIN module_trait_pheno_geneSignificance USING(trait_id) "
                  ."LEFT JOIN trait USING(trait_id) "
                  ."LEFT JOIN qtl on module_trait_moduleMembership.trait_id=qtl.Trait "
             ."WHERE module_trait_moduleMembership.moduleColor='$modcolour' "
                  ."AND BEARatChip.first_name != '' "
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

    $query .= "ORDER BY trait.chromosome ASC";
        
    $rec_query = mysqli_query($linkLocali,$query);
    echo "<p><small>query: $query</small></p>\n";
    if (0 != mysqli_errno($linkLocali)) {
    	errorMessage("Could not execute query:".mysqli_error($linkLocali));
	mysqli_close($linkLocali);
        include("footer.php");
	exit;
    }

    echo "<table border=1 cellspacing=0 width=100%>";
    echo "<thread align><tr bgcolor=yellow><th>Trait ID</th><th>Chromosome</th><th>Position(Mbp)</th><th>Function</th><th>Gene Significane(".$clinical.")</th><th>Module Membership(".$modcolour.")</th><th>Pathway</th><th>Chromosome:Peak (cM):Covariates:LOD</th></thread>";
	
    $prevTrait="";
    while ($row1 = mysqli_fetch_assoc($rec_query)) {
        if (!empty($prevTrait) and $prevTrait != $row1["trait_id"]) echo "</td></tr>\n";
        if ($prevTrait != $row1["trait_id"]) {
            echo "<tr>";
            foreach ($row1 as $n => $v) {
                if ("$n" != "qtl_cMorgan_Peak" and 
		    "$n" != "qtl_Chromosome" and 
		    "$n" != "qtl_covariates" and 
		    "$n" != "qtl_lod")
                {
                    echo "<td valign=top>".(empty($v)?"&nbsp;":"$v")."</td>";
                }		
            }
            echo "<td nowrap>";
        }
		  
        if ($prevTrait == $row1["trait_id"]) {
            echo "<br />";
        }
        $col="black";
        if ($row1["qtl_Chromosome"]==$row1["chromosome"]) $col="red";
        echo "<font color='$col'>";
        echo $row1["qtl_Chromosome"].":".$row1["qtl_cMorgan_Peak"]." ".(empty($row1["qtl_covariates"])?"none":$row1["qtl_covariates"]).":".$row1["qtl_lod"];
        echo "</font>";
        $prevTrait=$row1["trait_id"];
    }
    if (!empty($prevTrait)) echo "</td></tr>\n";
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
