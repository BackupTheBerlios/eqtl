<?php
require_once("func_conversion.php");
mysql_connect("localhost","root","");
mysql_select_db("eQTL_Stockholm");
mysql_query("DROP TABLE IF EXISTS cache_locus_to_position");
mysql_query("CREATE TABLE cache_locus_to_position (
  locus_id int(11) NOT NULL,
  positionBP int(11) NOT NULL,
  PRIMARY KEY  (locus_id)
) ENGINE=MyISAM");

$result = mysql_query("SELECT No, Chr, cMorgan FROM locus");
while ($row = mysql_fetch_assoc($result)) {
    echo $row["No"];
    echo $row["Chr"];
    echo $row["cMorgan"];
    $pos = cM2bp($row["Chr"],  $row["cMorgan"]);
    echo " ".$pos."<br>\n";
    mysql_query("INSERT INTO cache_locus_to_position SET locus_id='".$row["No"]."', positionBP='".$pos."' ");
}
?>
