#!/usr/bin/perl -w

use strict;

while(<>){
	next if /^Chr/i;
	my @a=split;
	$a[3]=~s/\.//g; # transforming Mbp into bp
	print "insert into map set chr='".$a[0]."',"
			         ."marker='".$a[1]."',"
			         ."cmorgan_rqtl=$a[2],"
			         ."bp=$a[3],"
			         ."Mbp=";
	my $v=$a[3]/1000/1000;
	print "$v;\n";
}
