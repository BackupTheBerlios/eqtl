<?php

/*

=head1 NAME

func_connect.php - direct establishment of database connection

=head1 SYNOPSIS

to be included when the connection to databases is needed.

=head1 DESCRIPTION

The script includes the func_dbconfig.php and uses those settings
to create the database connection that is then stored as $linkLocal.

=head1 SEE ALSO

=over 4

=item func_dbconfig.php

=back

=head1 AUTHOR

Steffen Moeller <moeller@inb.uni-luebeck.de>

=cut

*/


    global $hostname,$username,$database,$ensemblversion;

    require_once("func_dbconfig.php");

    foreach(array("hostname","username","database","ensemblversion") as $vname) {
	    if (!isset($$vname)) {
			if (isset($_POST[$vname])) {
				$$vname=$_POST[$vname];
			}
			elseif (isset($_GET[$vname])) {
				$$vname=$_GET[$vname];
			}
		}
	}

	if (!isset($hostname) or !isset($username) or !isset($database)) {
		errorMessage("func_dbconfig failed to specify hostname, username or database for func_connect.php");
		echo "</body></html>";
		exit;
	}

	if (empty($ensemblversion)) {
		errorMessage("func_dbconfig failed to specify ensemblversion");
		echo "</body></html>";
		exit;
// 		print "<p>Attention: func_connect: setting ensemblversion to $ensemblversion</p>\n";
	}

	//$link=mysql_connect($hostname,$username,"");
	$linkLocal=mysql_connect($hostnameqtl,$usernameqtl,"");

	if (empty($linkLocal)) {
		errorMessage("Could not create link to local database.");
		echo "</body></html>";
		exit;
	}

	if (!mysql_select_db($database,$linkLocal)) {
		errorMessage("Could not select local database '$database' on machine '$hostname' as '$username'.");
		echo "</body></html>";
		exit;
	}
?>
