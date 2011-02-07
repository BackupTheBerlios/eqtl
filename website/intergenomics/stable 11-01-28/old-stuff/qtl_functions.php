<?php
/**
 * fill the correlation array with default values, if a correlation between an
 * trait and lous not exists.
 *
 * @param $storage here the default values are filled
 * @param $allTraits if in this array the trait exists
 */
function fillDefaults(&$storage,$allTraits) {
	$default = 0;
	foreach ($storage as $locus => $combined){
		$keys = array_keys($combined);
		$vals = array_values($combined);
		$diff = array_diff($allTraits,$keys);
		$new_keys = array_merge($keys,$diff);
		$new_vals = array_merge($vals,array_fill(0,sizeof($diff),$default));
		$storage[$locus] = array_combine($new_keys,$new_vals);
	}

}