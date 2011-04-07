<?php

/**
 STARTOFDOCUMENTATION

 =pod

 =head1 NAME

 utils/write_detail_table.php -

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

// $ Id: 2010-12-19 gz exp$
// displays detailed homology information for two loci groups from different experiments
// Experiment 1 is over the colums
// Experiment 2 is over the rows

/**
 * Splitts the ensemble stable gene id in little - 3 chars big - parts
 * so that the string isn't to long.
 * if($cis_lookup[$key]) the header cell gets the class "ciss".
 *
 * @param $ens_id the ensemble stable gene id ($ens_id)
 * @param $key the key of the ensemble stable gene id ($ens_id)
 * @param $cis_lookup an array with boolean entries.
 * 	if($cis_lookup[$key]) the header cell gets the class "ciss".
 */
function split_and_ciss(&$ens_id,$key,$cis_lookup){
	$prefix = '<th';
	if($cis_lookup[$key]){
		$prefix .= ' class="ciss" title="ciss">';
	}else{
		$prefix .= ' title="trans">';
	}
	$ens_id = $prefix.chunk_split($ens_id,3,"<br />");
}

$refargs = $proj_str.'[]='.implode("+", explode(" ", $args[$proj_str][0])).'&'.$proj_str.'[]='.implode("+", explode(" ", $args[$proj_str][1])).'&region1='.$args[$region_str.'1'].'&region2='.$args[$region_str.'2'];
$fptr = fopen('html/table.html', 'w');

$str = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="de" xmlns="http://www.w3.org/1999/xhtml" lang="de"><head>
<!--IE7 in Quirksmode bitte-->
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<script type="text/javascript">
if (document.layers) {
 window.location.replace("leer.html");
} else {
 if(self == parent) {
  window.location.replace("frameset.html");
 }
}
window.onscroll = function () { parent.scrollen (); };
</script>

<link href="/css/style.css" rel="stylesheet" type="text/css" />
</head><body onmouseover="parent.aktFrame=window.name;">

<div id="cont" style="font-size: small;"><!-- the display table -->
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <!-- ID-column header -->
      <th rowspan="2" colspan="2">
      <div id="refargs" style="display: none;">
      	'.$refargs.'
	  </div>
      <div align="right">
      hide <input type="checkbox" name="checkHor" value="emptyEx1">
      </div>
      <div align="center">
      homologue <br />
      Ensembl <br />
      stable <br />
      IDs <br />
      </div>
      <div align="left" >
      <input type="checkbox" name="checkVer" value="emptyEx2"> hide
      </div>
      </th>';
fwrite($fptr, $str);
$str = "";
$tmpIDs = "";
$showNotEx1 = false;
foreach ($loci2stable_ids_ex1[0] as $locus_ex1 => $ens_ids_ex1){
	if(empty($ens_ids_ex1)){
		// FIXME: If a locus does not affect any genes
		// we skip it here.
		continue;
	}
	$str.= '<th colspan="'.sizeof($ens_ids_ex1).'" title="locus of species 1">'.$locus_ex1.'</th>';

	$tmp = $ens_ids_ex1;
	/*
	foreach ($ens_ids_ex1 as $ens_id_ex1){
		if($showNotEx1 && $$is_homo1[''])
	}
	$prefix = '<th';
	if($cis_lookup[$key]){
		$prefix .= ' class="ciss" title="ciss">';
	}else{
		$prefix .= ' title="trans">';
	}
	$ens_id = $prefix.chunk_split($ens_id,3,"<br />");
	*/
	array_walk($tmp, "split_and_ciss", $loci2stable_ids_ex1[1][$locus_ex1]);
	$tmpIDs.= implode('</th>',$tmp)."</th>\n";
}

fwrite($fptr, $str."</tr><tr>".$tmpIDs."</tr></thead><tbody>");


//iterate over locinames
foreach ($loci2stable_ids_ex2[0] as $locus_ex2 => $ens_ids_ex2){
	if(empty($ens_ids_ex2)){
		// FIXME: If a locus does not affect any genes
		// we skip it here.
		continue;
	}

	//initialize parameter to check if whole locus-entry is empty
	//$boolNonEmptyLocus = false;
	$firstrow = true;
	$i = 0;
	//$rowCount = 0;
	$str = "";
	foreach ($ens_ids_ex2 as $ens_id_ex2) {
		//$rowBool = false;
		if($loci2stable_ids_ex2[1][$locus_ex2][$i++]){
			$rowString = '<th class="ciss" title="ciss">';
		}else{
			$rowString = '<th title="trans">';
		}
		$rowString.= $ens_id_ex2."</th>";
		foreach ($loci2stable_ids_ex1[0] as $locus_ex1 => $ens_ids_ex1) {
			foreach ($ens_ids_ex1 as $ens_id_ex1){
				if(in_array($ens_id_ex2, $traits12traits2[$ens_id_ex1])){
					//$boolNonEmptyLocus = true;
					//$rowBool = true;
					$rowString.= '<td class="homologue" title="homology">Hom</td>';
				}else{
					$rowString.= '<td />';
				}
			}
		}
		$rowString.= "</tr>\n";

		//if ($rowBool || $showAll) {
		//	$rowCount++;
			if($firstrow){
				$firstrow = false;
				$str .= $rowString;
			}else{
				$rowString = "<tr>".$rowString;
				$str .= $rowString;
			}

		//}

	}

	$str = '<tr><th rowspan="'.count($ens_ids_ex2).'" title="locus of species 2">'.$locus_ex2.'</th>'.$str;

	//if ($boolNonEmptyLocus || $showAll) {
		fwrite($fptr, $str);
	//}

}

fwrite($fptr, "</tbody>
</table>
</div>
</body></html>
");
fclose($fptr);
?>
