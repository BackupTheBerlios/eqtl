<?php

/*

=head1 NAME

func_gprofiler.php - function to provide interface to g:Profiler

=head1 SYNOPSIS

To be included from PHP code without side-effects

=head1 DESCRIPTION

This file provides a function that shares the provisioning of an interface
for the manual calling of the g:Profiler and its automated invocation
with the parallel local caching.

=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

Universities of Rostock and LE<uuml>beck, 2003-2009

=cut

*/


function gprofilerlink($traits,$id="") {
	$traits_unique = array_unique($traits);
	$externalgprofiler  = "http://biit.cs.ut.ee/gprofiler/index.cgi?query=";
	$externalgprofiler .= join("+",$traits_unique);
	$externalgprofiler .= "&organism=ENSEMBLSPECIESMARTLONG&significant=1&output=png";
	$cachedgprofiler  = "http:?query=";
	$cachedgprofiler .= join(",",$traits_unique);
	$cachedgprofiler .= "&organism=ENSEMBLSPECIESMARTLONG&significant=1&output=png";
	echo "<h2>Characterisation</h2>\n";
	echo "<table><th rowspan=2>g:Profiler</th>\n";
	echo "<td><a href= $linkgprofiler>Manual inspection</a></td></tr>\n";
	echo "<td>";
	echo "<form action=\"http:characterisation.php\" method=\"POST\">";
	echo "<input type=checkbox name=gprofilesignificant value=checked />significant only<br>\n";
	echo "<input type=hidden value=\"".join("+",$traits)."\" />\n";
	echo "ID of analysis: <input name=id value=\"$id\" size=15/><br/>\n";
	echo "Traits: ".join(" ",$traits);
	echo "</form>";
	echo "</td></tr>";
	echo "</table>\n";
}

?>
