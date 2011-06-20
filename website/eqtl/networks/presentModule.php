<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php

/**

STARTOFDOCUMENTATION

=pod

=head1 NAME

networks/presentModule.php - present modules as pathways with Cytoscape

=head1 SYNOPSIS

a dynamic weg page - may be invoked without parameters

=head1 DESCRIPTION

Yask, please fill this.

=head1 AUTHORS

Yask Gupta <yask.gupta@uk-sh.de> with a review by Steffen ME<ouml>ller

=head1 COPYRIGHT

UK-SH Schleswig-Holstein, LE<uuml>beck, Germany, 2010-2011

=cut


*/

  require_once("../func_error.php");
  require_once("../header.php");

  show_small_header("Modules as Pathways with Cytoscape",true);

  if (array_key_exists("modcolour",$_GET) or array_key_exists("modcolour",$_POST)) {
    $var = $_POST['modcolour'];
    if (empty($var)) {
       $var = $_GET['modcolour'];
    }
    if (empty($var)) {
    	errorMessage("The argument 'modcolour' needs a value.");
    }
    $filename = "VisANTInput-".$var."_d.out";
    if (!file_exists($filename)) {
    	errorMessage("The file with data for module '$modcolour' was not found.");
    }
    $fh = fopen($filename,"r") or die("Can't open file '$filename'.");
    $array_edge = array();
    $array_node = array();
    while (!feof($fh)){
        $line = fgets($fh);
        if($line != NULL) {
            $line = preg_replace('/\n|\r|\t\t/','',  $line);
            $line = preg_replace('/\s\s+/',     '\s',$line);
            # print $line."<br>";
            $ids  = explode(" ",$line);
            $node1 = '{id:"'.$ids[0].'",label:"'.$ids[0].'"}';
            $node2 = '{id:"'.$ids[1].'",label:"'.$ids[1].'"}';
            if($ids[2] = -1) {
                $edge = '{id:"'.$ids[0].'to'.$ids[1].'",target:"'.$ids[0].'",source:"'.$ids[1]
		       .'",directed: true,label:"'.$ids[1].' to '.$ids[0].'",weight:"'.$ids[4].'"}';
            }
            else {
                $edge = '{id:"'.$ids[1].'to'.$ids[0].'",target:"'.$ids[1].'",source:"'.$ids[0]
		       .'",directed: true,label:"'.$ids[0].' to '.$ids[1].'",weight:"'.$ids[4].'"}';
            }
            array_push($array_edge,$edge);
            array_push($array_node,$node1);
            array_push($array_node,$node2);
        }
    }
    fclose($fh);

    $u_arr_node = array_unique($array_node);
    #array_pop($array_edge);
    #array_pop($u_arr_node);
    $var_edge = implode(",",$array_edge);
    $var_node = implode(",",$u_arr_node);
    $string1 = 'data:{nodes:['.$var_node.'],edges:['.$var_edge.']}';
    $string  = 'data:{nodes:[{id:"2",label:"2"},{id:"3",label:"3"},{id:"1",label:"1"}],edges:[{id:"2to1",target:"1",source:"2",label:"2 to 1",weight:0.7 },{id:"3to1",target:"1",source:"3",label:"3 to 1",weight:0.3}]}';
?>
        <script type="text/javascript" src="json2.min.js"></script>
        <script type="text/javascript" src="AC_OETags.min.js"></script>
        <script type="text/javascript" src="cytoscapeweb.min.js"></script>
        
        <script type="text/javascript">

             window.onload=function() {

                 // id of Cytoscape Web container div
                 var div_id = "cytoscapeweb";                

                 // create a network model object
                 var network_json = {
                        // you need to specify a data schema for custom attributes!
                        dataSchema: {
                    		nodes: [ { name: "label", type: "string" }
           		         	],
							edges: [ { name: "label", type: "string" },
							         { name: "weight", type: "string" }
							]
                    	},
                    	// NOTE the custom attributes on nodes and edges
                        data: {
                              nodes: [ <?php echo $var_node?>
                                       ], 
                                                 edges: [ <?php echo $var_edge?>
                                                   ]
                              }
                 };
                
                 // initialization options
                 var options = {
                    swfPath: "CytoscapeWeb",
                    flashInstallerPath: "playerProductInstall"
                 };
                
                 var vis = new org.cytoscapeweb.Visualization(div_id, options);
                 vis.draw({ network: network_json });
             };

        </script>
        <style>
             /* The Cytoscape Web container must have its dimensions set. */
             html, body { height: 100%; width: 100%; padding: 0; margin: 0; }
             #cytoscapeweb { width: 100%; height: 100%; }
        </style>
    </head>
    <body>
         <div id="cytoscapeweb">
             Cytoscape Web will replace the contents of this div with your graph.
         </div>
<?php
   }
   else {
   	echo "<p>The following networks are available:</p>\n";
	$matches=array();
	echo "<table border=0>";
	foreach (glob("VisANTInput-*_d.out") as $filename) {
	    if (0 < preg_match ("/^VisANTInput-([^_]+)_d.out/", $filename, $matches)) {
		echo "<tr><td>";
	    	echo "<a href=\"presentModule.php?modcolour=".$matches[1]."\">".$matches[1]."</a>";
		echo "</td><td align=right>";
		echo round(filesize($filename)/1000,0)." kB";
		echo "</td></tr>\n";
	    }
	    else {
		    echo "$filename"." (" . round(filesize($filename)/1024,0) . "kB)"."<br>\n";
	    }
	}
	echo "</table>";
   }
   include_once("../footer.php");
?>
