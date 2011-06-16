<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php
    $var = $_GET['modcolour'];
    $file = "VisANTInput-".$var."_d.out";
    $fh = fopen($file,"r") or die("Can't open file '$file'.");
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
<html>
    <head>
        <title>Cytoscape Web example</title>
        
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
    </body>
</html>
