<?php

#    A T T E N T I O N:   Edit the template, not this file !!!!

/**
 * scanns all "base paths" of the related projects to fill up the $compara_array.
 */
function fill_compara_array() {
	include 'compara_config.php';
	$a="[Ratte,URL,../../../ratte]";
	if (empty($a)) {
		return null;
	}
	$projects=preg_split('/(\]|\[)([[ \t])*/',"$a");
	foreach ($projects as $p) {
		if (empty($p)) {
			continue;
		}
		$details = preg_split("/,/",$p);
		if(!empty($details) && count($details)>2){
			// check of basepath ending with '/'
			include $details[2].((substr($details[2],-1) === '/')?'':'/').
					'website/intergenomics/compara_config.php';
		}
	}
}
