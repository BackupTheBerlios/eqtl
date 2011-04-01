<?php

/**
STARTOFDOCUMENTATION

=pod

=head1 NAME

utils.php - 

=head1 SYNOPSIS

=head1 DESCRIPTION

=head1 AUTHOR

Michael Brehler <brehler@informatik.uni-luebeck.de>,
Georg Zeplin <zeplin@informatik.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, Germany, 2011

=cut

ENDOFDOCUMENTATION
*/
require_once '../eqtl/func_error.php';
/*
 * Functions that are not related to Compara or eQTL. Can be used as toolbox.
 */

/**
 *
 * @param unknown_type $str
 */
function warn($str){
	echo '<div id="warnbox">';
	print_r($str);
	echo "</div>\n";
}

function error($str){
	echo '<div id="errorbox">';
	echo '<b>'.print_r($str).'</b>';
	echo "</div>\n";
}

function fatal_error($str){
	errorMessage($str,true);
	//trigger_error("\n\n<span id=\"fatal_error\">".$str."\n</span>", E_USER_ERROR);
}


#Recursive Funcition

function dump_table($var, $title=false, $level=0){
	if($level==0){
		echo '<table border="1" cellspacing="1" cellpadding="1" class="dump">';

		if($title){
			echo '<tr>
                     <th align="center" colspan="2">'.$title.'</th>
                   </tr>';
		}
		echo '          <tr>
            <th align="right">VAR NAME</th>
            <th align="left">VALUE</th>
          </tr>';
	}else{
		echo '<tr>
                <td colspan="2">
                    <table width="100%" border="0" cellspacing="3" cellpadding="3" class="dump_b">
                </td>
              </tr>';
	}

	foreach($var as $i=>$value){
		if(is_array($value) or is_object($value)){
			dump_table($value, false, ($level +1));
		}else{
			echo '<tr>
                        <td align="right" width="50%" >'.$i.'</th>
                        <td align="left" width="50%" >'.$value.'</th>
                       </tr>';
		}
	}
	echo '</table>';
}

function tic(){
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;

	return $starttime;
}

function toc($starttime,$name){
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	echo $name." was executed in ".$totaltime." seconds<br />";
}

function entry_to_get(&$val,$key){
	$val = $key.'='.$val;
}
function assoc2get($assoc){
	array_walk($assoc,"entry_to_get");
	return implode("&",$assoc);
}
