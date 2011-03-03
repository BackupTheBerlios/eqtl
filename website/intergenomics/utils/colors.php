<?php

/**
STARTOFDOCUMENTATION

=pod

=head1 NAME

utils/colors.php - 

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

//require_once '../utils.php';

function getColor($val){
	global $minV,$valScale,$colStep,$minC;
	$val = ($val-$minV)/$valScale;
	$str = " style=\"background-color:rgb(";
	for ($i = 0; $i < 2; $i++) {
		$str .= round($minC[$i]+$val*$colStep[$i]).",";
	}
	$str .= round($minC[2]+$val*$colStep[2]).")\"";
	return $str;
}

$maxC = array(255,255,0);
$minC = array(0,0,255);

$colStep = array();
for ($i = 0; $i < 3; $i++) {
	$colStep[] = $maxC[$i]-$minC[$i];
}
$maxV = 100;
$minV = 0;
$valScale = 0;

//$values = array(0,2,4,80,100);

function fun($group){return sizeof($group['loci']);};

function prepareColors($groups1,$groups2){
	$vals1 = array_map("fun",$groups1);
	$max1 = max($vals1);
	$min1 = min($vals1);

	$vals2 = array_map("fun",$groups2);
	$max2 = max($vals2);
	$min2 = min($vals2);

	global $maxV, $minV, $valScale;
	$maxV = $max1 + $max2;
	$minV = $min1 + $min2;
	$valScale = ($maxV-$minV);
}

function color2rgb($colArray) {
	return "background-color:rgb(".implode(',', $colArray).')';
}

//function getColors($minV,$maxV,$minC,$maxC,$values,$quant) {
//	$valScale = ($maxV-$minV);
//	for ($i = 0; $i < 3; $i++) {
//		$colStep[] = $maxC[$i]-$minC[$i];
//	}
//	$valueCol = array();
//	$cnt = 0;
//	foreach ($values as $val) {
//		$valueCol[$cnt++] = array();
//		$val = ($val-$minV)/$valScale;
//		for ($i = 0; $i < 3; $i++) {
//			$valueCol[$cnt][] = round($minC[$i]+$val*$colStep[$i]);
//		}
//		warn(color2rgb($valueCol[$cnt]));
//	}
//}

//getColors($minV,$maxV,$minC,$maxC,$values,$quant);
