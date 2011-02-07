<script>
function showMe (it, box) {
  var vis = (box.checked) ? "block" : "none";
  document.getElementById(it).style.display = vis;
}
</script>
<?php
        require_once("header.php");
	show_small_header("MODULES",TRUE);
        $con = mysql_connect("localhost","root","yask123");
        mysql_select_db("eqtl_stockholm_eae_logplier", $con);
        $mod_query = mysql_query("select distinct moduleColor from module2");
        echo "<form action=\"module1.php\" method='get'>";
        echo "<table width=100% border=0><tr><td><table border=0>";
        echo "<tr><th align=right>MODULE COLOR : </th><td>";
        echo "<select name=\"modcolor\">";
        while($row = mysql_fetch_array($mod_query))
              {
               echo "<option>".$row["moduleColor"]."</option>";
              }
        echo "</select><font color=blue> #A module is a group of genes which have high correlation in term of scale free topology and Topological overlap</font></td></tr>";
        $mod_query = mysql_query("SHOW COLUMNS FROM module1 where Field like \"%p_GS%\"");
        echo "<tr><td><br></td></tr>";
        echo "<tr><th align=right>CLINICAL PHENOTYPE : </th><td>";
        echo "<select name=\"clinical\">";
        if (mysql_num_rows($mod_query) > 0) {
               while ($row5 = mysql_fetch_assoc($mod_query)) {
                       $clinical = $row5["Field"];
                       $cli= preg_replace("/p_GS_/","",$clinical);
                       echo "<option>".$cli."</option>";
                                                             }
                                            }
        echo "</select></td></tr><tr><td><br></td></tr>";
        echo "<tr><td><font color=red>(optional)</font></td></tr>";
        echo "</td></tr><tr><td><br></td></tr>";
        echo "<tr><th align=right>GENE SIGNIFICANCE : </th><td><input type=\"text\" name=\"gs\" size=3><font color=blue> #Gene significance implies the significance of gene for the phenotype in the given module </td></tr><br>"; 
        echo "</select></td></tr><tr><td><br></td></tr>";
        echo "<tr><th align=right>MODULE MEMBERSHIP : </th><td><input type=\"text\" name=\"mm\" size=3><font color=blue> #Module Membership implies the membership of each gene in the given module </td></tr><br>"; 
        echo "<tr><td><br></tr></td>";
        echo "<tr><td><input type=\"submit\" name=\"submit\"/>";
        echo "</td><tr>";
        echo "</td></tr></table></form>";
?>
<?php
        echo '<input type="checkbox" name="c1" onclick="showMe(\'div1\', this)"><b>Please check Module Trait Relationship graph to find Module of your need !</b>';
        echo '<div id="div1"style="display:none"';
        echo "<a><img src=\"tmp_images/try.png\" ISMAP/></a>";
        echo '</div>';
        echo "<hr>";
        $mod = $_GET["modcolor"];
        $clin = $_GET["clinical"];
        $mm = $_GET["mm"];
        $gs = $_GET["gs"];
        echo "<a href=\"demo/visant.php?mod=".$mod."\"><b>See gene network for module</b></a>";
        echo "<p></p>";
        if(empty($mm) and empty($gs))
           {
            $option="";
           }
        elseif(!empty($mm) and empty($gs))
            {
             $option = "and module2.MM_".$mod." >= ".$mm;
            }
         elseif(empty($mm) and !empty($gs))
            {
             $option = "and module1.GS_".$cli." >= ".$gs;
            }
         elseif(!empty($mm) and !empty($gs))
            {
             $option = "and module1.GS_".$cli." >= ".$gs." and module2.MM_".$mod." >= ".$mm;
            }
        $rec_query=mysql_query("select module2.TRAIT,bearatchip.first_name,module1.GS_".$cli.",module2.MM_".$mod.",bearatchip.pathway from module2 left join bearatchip on module2.TRAIT=bearatchip.probeset_id left join module1 on module2.TRAIT=module1.TRAIT where module2.moduleColor=\"".$mod."\" ".$option." and bearatchip.first_name != \"\"");
        echo "QUERY : select module2.TRAIT,bearatchip.first_name,module1.GS_".$cli.",module2.MM_".$mod.",bearatchip.pathway from module2 left join bearatchip on module2.TRAIT=bearatchip.probeset_id left join module1 on module2.TRAIT=module1.TRAIT where module2.moduleColor=\"".$mod."\" ".$option." and bearatchip.first_name != \"\"";
        echo "<p></p>";
        echo "<table border=1 cellspacing=0 width=100%>";
        echo "<thread align><tr bgcolor=yellow><th>Trait ID</th><th>FUNCTION</th><th>Gene Significane(".$clin.")</th><th>Module Membership(".$mod.")</th><th>Pathway</th></thread>";
           while ($row1 = mysql_fetch_assoc($rec_query))
                  {
                   echo "<tr>";
	               foreach ($row1 as $n => $v) {
	                   echo "<td>$v</td>";
	                                           }
	           echo "</tr>\n";
                  }
include("footer.php");
?>
