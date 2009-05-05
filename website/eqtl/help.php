<?php
   require_once("header.php");
   show_small_header("Documentation",TRUE);
?>
   <h1>Expression QTL for Murine Experimental Autoimmune Encephalomyelits</h1>
   
   <h2>Table of Contents</h2>
   	<nl>
	<li><a href="#locus">Locus
	<li><a href="#qtl">QTL
	<li><a href="#interaction">Interactions
	<li><a href="#expression">Expression data
		<ul>
		<li><a href="#qtl">Traits
		<li><a href="#qtl">Parental data
		</ul>
	<li><a href="#installation">Installation
	</nl>
   <h2 name=locus>Locus</h2>
	Loci represent regions on the murine chromosome that are associated with 
	differences between the strains that are associated with the disease.
	These are characterised by a microsatellite polymorphic marker in which
	the strains differ, which is referred to as a <i>marker</i>.
	The web interface allows to query for 
	<ul>
	<li>Chromosome
	<li>Morgan-distance from the first marker on that chromosome
	</ul>
	and other parameters that refer to the <a href=#qtl>QTL</a> and
	<a href=#interaction>interaction</a> tables and are explained there.
   <h2 name=qtl>Quantitative Trait Locus (QTL)</h2>
   	The QTL refers to a region of a chromosome that is associated with a phenotype of the disease.
   <h2 name=interaction>Interactions</h2>
   <h2 name=expression>Expression data</h2>
   The expression of genes as measured by the Illumina BeadChip technology is
   the major trait investigated in this study. The expression data referring to can be
   retrieved for each of the gene on the chip.
   <h3 name=traits>Traits of eQTLs</h3>
   <h3 name=>Data of parental strains</h3>
   <h3 name=correlation>Correlation between classical and expression Traits</h3>
   The phenotype is the result of multiple genes interacting in multiple
   tissues while the expression data, so the hypothesis of this study,
   has a more limited range of influences and is thus more indicative of
   single functional differences between the strains. The web interface
   at <a href="">expression_trait_correlation.php</a> presents the raw data
   and a graphical representation of classical and expression traits
   for the same mouse as a scatterplot.

   <h1>Appendix</h1>
   <h2>Installation</h2>
   <h3>Download</h3>
   <h3>Requirements for local machine</h3>
   	<ul>
	<li>MySQL database, compatibility with other DBs is expected
	<li>Web server
	<li>PHP with jsgraph extension
	<li>Perl
	<li>R with R/qtl package
	</ul>
   <h3>Database setup</h3>
   <h3>Web interface setup</h3>
<?
   include("footer.php");
?>

