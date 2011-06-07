<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<HTML><HEAD><TITLE>Welcome to VisANT Home Page</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<SCRIPT language=JavaScript>
</SCRIPT>
<META content="Microsoft FrontPage 5.0" name=GENERATOR>
<base target="main">
</HEAD>
<BODY vLink=#ffffff aLink=#9bfc4e link=#ffffff leftMargin=2 topMargin=1>
<?php
$var = $_GET['mod'];
$var = "VisANTInput-".$var."-filtered.txt";
echo $var;
#$var = "VisANTInput-black-filtered.txt";
$myFile = "batch.txt";
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = "#!batch commands\nread=".$var."\nvisml=".$var.".xml";
fwrite($fh, $stringData);
shell_exec("java -Xmx512M -Djava.awt.headless=true -jar /home/yask/Desktop/cmd_line/VisAnt.jar -b batch.txt");
$new_file = $var.".xml";
$handle = fopen($new_file, "r");
$output = fread($handle,filesize($new_file));
$output=preg_replace("/M0039=/","",$output);
fclose($handle);
$fh = fopen($new_file,'w');
fwrite($fh, $output);
fclose($fh);
?>
  <APPLET NAME="visant"  codebase="."	ARCHIVE="./VisAnt.jar" 					
			CODE="cagt.bu.visant.VisAntApplet.class" WIDTH=1 HEIGHT=1 align="right">
      <PARAM  NAME='visible' Value = 'true'>
   <!--   <PARAM  NAME=DAIurl     Value = 'http://128.197.39.189:8080/vserver/vproxy?'> -->
    <!--  <PARAM  NAME=startup     Value = 'http://localhost/eqtl/eqtl/demo/VisANTInput-greenyellow.txt'> -->
<PARAM  NAME=startup     Value = '<?php echo $new_file?>'>
   <!--   <PARAM  NAME=kegg       Value = 'http://www.genome.ad.jp/dbget-bin/www_bget?'>
      <PARAM  NAME=KGML       Value = 'http://visant.bu.edu/sample/kegg/'>
      <PARAM  NAME='GI'       Value = 'NCBI Genebank=http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=Protein&dopt=GenPept&list_uids='>
      <PARAM  NAME='SGD ID'   Value = 'SGD Link=http://db.yeastgenome.org/cgi-bin/SGD/locus.pl?locus='>
      <PARAM  NAME='SP'       Value = 'Swiss-Prot NiceProt View=http://us.expasy.org/cgi-bin/niceprot.pl?'>
	 <PARAM  NAME='loc'      Value = 'Entrez Gene=http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?db=gene&cmd=Retrieve&dopt=Graphics&list_uids='>
	 <PARAM  NAME='mim'     Value = 'OMIM=http://www.ncbi.nlm.nih.gov/entrez/dispomim.cgi?id='>
	 <PARAM  NAME='ref_s'    Value = 'RefSeq=http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?val='>
	 <PARAM  NAME='gcard'    Value = 'GeneCard=http://bioinfo.weizmann.ac.il/cards-bin/carddisp?'>
	 <PARAM  NAME='gtest'    Value = 'GeneClinics/GeneTests=http://www.genetests.org/query?gene='>
	 <PARAM  NAME='O_N_hsa'    Value = 'HUGO=http://www.gene.ucl.ac.uk/cgi-bin/nomenclature/searchgenes.pl?field=symbol&anchor=equals&match='>
      <PARAM  NAME='wormbase'    Value = 'WormBase=http://www.wormbase.org/db/gene/gene?name='>
	  <PARAM  NAME='fbgn'    Value = 'Flybase Report=http://flybase.net/.bin/fbidq.html?'>
      <PARAM  NAME='rgd'    Value = 'Rat Genome Database=http://rgd.mcw.edu/tools/genes/genes_view.cgi?id='>
      <PARAM  NAME='ecocyc'    Value = 'EcoCyc=http://biocyc.org/ECOLI/NEW-IMAGE?type=NIL&object='>
	 <PARAM  NAME='ratmap'    Value = 'RatMap=http://ratmap.gen.gu.se/ShowSingleLocus.htm?accno='>
	 <PARAM  NAME='pubmed'    Value = 'http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids='>
-->
	<PARAM  NAME='toolbar'    Value = 'ON'>
	<PARAM  NAME='menu_file'    Value = 'ON'>
	<PARAM  NAME='menu_edit'    Value = 'ON'>
	<PARAM  NAME='menu_view'    Value = 'ON'>
	<PARAM  NAME='menu_filter'    Value = 'ON'>
	<PARAM  NAME='menu_filter_homo'    Value = 'ON'>
	<PARAM  NAME='menu_filter_expression'    Value = 'ON'>
	<PARAM  NAME='menu_node_query'    Value = 'ON'>
	<PARAM  NAME='menu_node_go'    Value = 'ON'>
	<PARAM  NAME='menu_node_links'    Value = 'ON'>
	<PARAM  NAME='menu_node_label'    Value = 'ON'>
	<PARAM  NAME='menu_node_edit'    Value = 'ON'>
	<PARAM  NAME='menu_node_grouping'    Value = 'ON'>
	<PARAM  NAME='menu_layout'    Value = 'ON'>
	<PARAM  NAME='menu_meta'    Value = 'ON'>
	<PARAM  NAME='menu_topo'    Value = 'ON'>
	<PARAM  NAME='menu_expression'    Value = 'ON'>
	<PARAM  NAME='menu_plugin'    Value = 'ON'>
	<PARAM  NAME='toolbox_species'    Value = 'ON'>
	<PARAM  NAME='toolbox_online'    Value = 'ON'>
			
	 <PARAM  NAME=Link1      Value = 'MenuName=HugeIndex Home Link=http://www.HugeIndex.org'>
      <PARAM  NAME=Link2      Value = 'MenuName=NCBI Home Link=http://www.ncbi.nlm.nih.gov'>
    </APPLET>


</BODY></HTML>
