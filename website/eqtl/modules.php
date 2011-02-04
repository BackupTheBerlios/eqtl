<script>
function showMe (it, box) {
  var vis = (box.checked) ? "block" : "none";
  document.getElementById(it).style.display = vis;
}
</script>
<?php
        require_once("header.php");
        require_once("func_connecti.php");
	show_small_header("Modules",TRUE);

        $mod_query = mysqli_query($linkLocali,"select distinct moduleColor from module_trait_moduleMembership");

	echo "<p>This page presents all traits (gene expressions) that belong to the same module. The module is named by a colour, which means nothing more than to be easily remembered and to look nicer than mere numbers. The genes in the module are offered to be ranked for their meaning in the context of a clinical phenotype and varies between genes. Module-membership of traits is independently calculcated from those phenotypes. For that, the very same genes may have different significances for an association with the different phenotypes, but always the same affinity to a particular module. What module to look at, you select from <i>Module Colour</i> selection box. To select the <i>Clinical Phenotype</i>, use the respective other selection box.</p>";
	echo "<p>The output table also shows the QTL for every molecular trait. You can constrain the search by specifying three minimal values for the module membership of a trait (<i>module membership</i>), the association with a phenotype (<i>gene significance</i>) and the association with a chromosomal locus (<i>LOD score</i> - coming later tonight). The wording from this page relects that of R's CRAN <a href='http://www.genetics.ucla.edu/labs/horvath/CoexpressionNetwork/Rpackages/WGCNA/'>WGCRA</a> software package that is used to perform those analyses.</p>";
	echo "<p>The analysis should start by a click to the checkbox right below the entry form to have a diagram popped up that presents the association of modules to all the clinical phenotypes. At some time we hope to generate the image dynamically and allow you to click on it. For the time speaking, please just look at the writing at the left and at the bottom to find the values to select from the respective modules and phenotype selection dropdown boxes. Suggestions on how to present the data more easily please let flow in. More work on the linking to the GeneOntology and pathway analyses is on its way, some surprises are already scheduled for Monday.</p>";

        echo "<form action='modules.php' method='get'>";
        echo "<table width=100% border=0><tr><td><table border=0>";
        echo "<tr><th align=right>Module Colour : </th><td>";
        echo "<select name='modcolor'>";
        while($row = mysqli_fetch_array($mod_query)) {
               echo "<option>".$row["moduleColor"]."</option>";
        }
        echo "</select><font color=blue> #A module is a group of genes which have high correlation in term of scale free topology and Topological overlap</font></td></tr>";
        $mod_query = mysqli_query($linkLocali,"SHOW COLUMNS FROM module_trait_pheno_geneSignificance WHERE Field LIKE '%p_GS%'");
        echo "<tr><td><br></td></tr>";
        echo "<tr><th align=right>Clinical Phenotype: </th><td>";
        echo "<select name='clinical'>";
        if (mysqli_num_rows($mod_query) > 0) {
               while ($row5 = mysqli_fetch_assoc($mod_query)) {
                       $clinical = $row5["Field"];
                       $cli= preg_replace("/p_GS_/","",$clinical);
                       echo "<option>".$cli."</option>";
		}
	}
        echo "</select></td></tr><tr><td><br></td></tr>";
        echo "<tr><td><font color=red>(optional)</font></td></tr>";
        echo "</td></tr><tr><td><br></td></tr>";
        echo "<tr><th align=right>Gene Significance: </th><td><input type='text' name='gs' size=3><font color=blue> #Gene significance implies the significance of gene for the phenotype in the given module </td></tr><br>"; 
        echo "</select></td></tr><tr><td><br></td></tr>";
        echo "<tr><th align=right>Module Membership: </th><td><input type='text' name='mm' size=3><font color=blue> #Module Membership implies the membership of each gene in the given module </td></tr><br>"; 
        echo "<tr><td><br></tr></td>";
        echo "<tr><td><input type='submit' name='submit'/>";
        echo "</td><tr>";
        echo "</td></tr></table></form>";
?>
<?php
        echo '<input type="checkbox" name="c1" onclick="showMe(\'div1\', this)"><b>Check this box to see Module Trait Relationship diagram. Identify the modules that are highest in their association with clinical phenotypes. The selection perfom by the drop-down menus showing respective module colours and phenotypes.</b>';
        echo '<div id="div1"style="display:none"';
        echo "<a><img width='100%' src='tmp_images/module_trait_relationship_map.png' ISMAP/></a>";
        echo '</div>';
        echo "<hr>";
        $mod = $_GET["modcolor"];
        $clin = $_GET["clinical"];
        $mm = $_GET["mm"];
        $gs = $_GET["gs"];
        echo "<a href=\"demo/visant.php?mod=".$mod."\"><b>See gene network for module></b></a>";
        echo "<p></p>";
        if (empty($mm) and empty($gs)) { $option=""; }
        elseif (!empty($mm) and empty($gs)) {
             $option = "and module_trait_moduleMembership.MM_".$mod." >= ".$mm;
        }
        elseif (empty($mm) and !empty($gs)) {
            $option = "and module_trait_pheno_geneSignificance.GS_".$cli." >= ".$gs;
        }
        elseif (!empty($mm) and !empty($gs)) {
             $option = "and module_trait_pheno_geneSignificance.GS_".$cli." >= ".$gs." and module_trait_moduleMembership.MM_".$mod." >= ".$mm;
        }
        
        $query = "SELECT module_trait_moduleMembership.trait_id as trait_id,"
				      ."trait.chromosome,round((trait.start+trait.stop)/2)/1000000 as pos,"
				      ."BEARatChip.first_name,module_trait_pheno_geneSignificance.GS_".$cli.","
				      ."module_trait_moduleMembership.MM_".$mod.","
				      ."qtl.Chromosome as qtl_Chromosome,"
				      ."qtl.cMorgan_Peak as qtl_cMorgan_Peak,"
				      ."qtl.covariates as qtl_covariates,"
				      ."qtl.LOD as qtl_lod,"
                                      ."BEARatChip.pathway "
	        ."FROM module_trait_moduleMembership LEFT JOIN BEARatChip ON module_trait_moduleMembership.trait_id=BEARatChip.probeset_id "
		."LEFT JOIN module_trait_pheno_geneSignificance USING(trait_id) "
		."LEFT JOIN trait USING(trait_id) "
		."LEFT JOIN qtl on module_trait_moduleMembership.trait_id=qtl.Trait "
		."WHERE module_trait_moduleMembership.moduleColor=\"".$mod."\" ".$option." and BEARatChip.first_name != \"\" ORDER BY trait.chromosome ASC"
	        ."";

	$rec_query = mysqli_query($linkLocali,$query);
        echo "QUERY : $query";
        echo "<p></p>";
        echo "<table border=1 cellspacing=0 width=100%>";
        echo "<thread align><tr bgcolor=yellow><th>Trait ID</th><th>Chromosome</th><th>Position(Mbp)</th><th>Function</th><th>Gene Significane(".$clin.")</th><th>Module Membership(".$mod.")</th><th>Pathway</th><th>Chromosome:Peak (cM):Covariates:LOD</th></thread>";
	
	$prevTrait="";
        while ($row1 = mysqli_fetch_assoc($rec_query)) {
		  if (!empty($prevTrait) and $prevTrait != $row1["trait_id"]) echo "</td></tr>\n";
		  if ($prevTrait != $row1["trait_id"]) {
		    echo "<tr>";
		    foreach ($row1 as $n => $v) {
			  if ("$n" != "qtl_cMorgan_Peak" and "$n" != "qtl_Chromosome" and "$n" != "qtl_covariates" and "$n" != "qtl_lod") {
				echo "<td valign=top>".(empty($v)?"&nbsp;":"$v")."</td>";
			  }		
		    }
		    echo "<td nowrap>";
		  }
		  
		  if ($prevTrait == $row1["trait_id"]) {
			echo "<br />";
		  }
		  echo $row1["qtl_Chromosome"].":".$row1["qtl_cMorgan_Peak"]." ".(empty($row1["qtl_covariates"])?"none":$row1["qtl_covariates"]).":".$row1["qtl_lod"];
		  $prevTrait=$row1["trait_id"];
	    }
	  if (!empty($prevTrait)) echo "</td></tr>\n";
       
include("footer.php");
?>
