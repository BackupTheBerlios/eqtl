<html>
<head><title>Compara</title>
<link href="http://www.ihop-net.org/UniPub/html/css/general.css" rel="stylesheet" media="screen"/>
<link href="http://www.ihop-net.org/UniPub/html/css/unipub_hop.css" rel="stylesheet" media="screen"/>
<link href="http://eqtl.berlios.de/stylesheets/eqtl.css" rel="stylesheet" media="screen"/>
</head>
<body>
<h1>Georg and Michael implement an interface to Compara for Expression QTL</h1>
<ul>
<li><a href="compara.php">compara.php</a> - a rather compute intensitve overview on all syntenic QTL ... still under development
</ul>
<table width="100%"><tr><td>
<h2>PHP files in directory:</h2>
<?php
	if ($handle = opendir('.')) {
	    echo "<ul>\n";
	    while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && preg_match('/.php$/',$file)) {
		    echo "<li><a href=\"$file\">$file</a></li>\n";
		}
	    }
	    closedir($handle);
	    echo "</ul>\n";
	}
?>
</td><td valign=top>
<h2>Non-PHP files in local directory:</h2>
<?php
	if ($handle = opendir('.')) {
	    echo "<ul>\n";
	    while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != ".." && !preg_match('/.php$/',$file)) {
		    echo "<li><a href=\"$file\">$file</a></li>\n";
		}
	    }
	    closedir($handle);
	    echo "</ul>\n";
	}
?>
</td></tr></table>
</body>
</html>
