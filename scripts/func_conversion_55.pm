#!/usr/bin/perl -w

use strict;

use vars qw(%conv);

$conv{1} = [
[0 , 4404878],
[33.0626 , 25271050],
[59.6914 , 54516880],
[78.5345 , 90449000],
[104.874 , 128874535],
[114.263 , 145648743],
[119.419 , 157497945],
[127.678 , 172926307],
[132.416 , 183946847],
[147.621 , 205041048],
[154.974 , 206567644],
[189.253 , 234595191],
[202.64 , 259173327],
[207.498 , 262779963],
[248.1 , 248100601]
];
$conv{10} = [
[0 , 6172611],
[22.6322 , 23428128],
[35.1 , 35131432],
[43.0871 , 43361251],
[49.6 , 49618847],
[61.413 , 68842891],
[75.0213 , 81986621],
[90.339 , 91208778],
[111.837 , 105886242]
];
$conv{11} = [
[0 , 1270722],
[17.9566 , 27445617],
[38.504 , 46793415],
[64.838 , 81091342]
];
$conv{12} = [
[0 , 3438583],
[22.1012 , 20932560],
[27.3156 , 25367107],
[29.1 , 29130181],
[71.7127 , 44254904]
];
$conv{13} = [
[0 , 42627611],
[30.1037 , 74560243],
[51.5722 , 9.45e+07],
[59.2759 , 104400153]
];
$conv{14} = [
[0 , 4895895],
[2.35071 , 7697708],
[10.1528 , 17564856],
[21.6084 , 2.64e+07 ],
[33.4 , 33448038],
[52.8614 , 68771632],
[82.3438 , 9.82e+07] ,
[90.5594 , 107119264]
];
$conv{15} = [
[0 , 766501],
[25.5 , 25506389],
[63.0094 , 58412002],
[85.077 , 81981179],
[110.034 , 107378191]
];
$conv{16} = [
[0 , 350122],
[26.4375 , 35339930],
[50.2 , 50266029],
[51.7903 , 66606253],
[83.2969 , 77968643],
[84.0747 , 89009136]
];
$conv{17} = [
[0 , 16053310],
[25.9841 , 31007409],
[48.3653 , 64047700],
[84.828 , 96587775] # no information on marker 'D17Rat47' - skipped
];
$conv{18} = [
[0 , 11593055],
[16.0006 , 40600399],
[69.278 , 7.12e+07] ,
[70.5592 , 79643542]
];
$conv{19} = [
[0 , 7813538],
[36.4485 , 40109361],
[47.7195 , 4.36e+07],
[54.3603 , 49565031]
];
$conv{2} = [
[0 , 8678734],
[34.4586 , 48244850],
[47.0821 , 60124624],
[64.0351 , 108159563],
[78.2211 , 145455614],
[95.4989 , 181710215],
[163.667 , 238139979],
[175.409 , 2.505e+08],
[210.6 , 210636009]
];
$conv{20} = [
[0 , 10410005],
[22.184 , 26064462],
[47.5229 , 46690376]
];
$conv{3} = [
[0 , 10829519],
[31.8953 , 41420306],
[68.7441 , 9.52e+07],
[79.1218 , 120891188],
[135.988 , 161202737],
[143.5 , 143492509]
];
$conv{4} = [
[0 , 4410369],
[8.852 , 19044128],
[20.8858 , 36592629],
[39.9092 , 65822169],
[59.3719 , 114073977],
[85.8815 , 143753496],
[102.213 , 162283990],
[107.008 , 166051146],
[109.814 , 171204378],
[118.176 , 185398416],
[132.3 , 132262560],
[154.9 , 154937061]
];
$conv{5} = [
[0 , 7966797],
[9.11139 , 25602275],
[52.5 , 52530708],
[66.6881 , 8.06e+07],
[94.5619 , 125563751],
[110.656 , 147509595],
[124.075 , 1.567e+08],
[132.574 , 1.61449e+08],
[139.239 , 1.65e+08]
];
$conv{6} = [
[0 , 1301953],
[8.7901 , 14627484],
[47.3906 , 4.34e+07],
[63.8 , 63795021],
[99.8353 , 100709526],
[123.3 , 123341445],
[142.387 , 131029752]
];
$conv{7} = [
[0 , 21224426],
[30.1 , 30146703],
[35.9024 , 44462830],
[53.7378 , 74244787],
[75.8163 , 103612740],
[89.9001 , 89944922],
[107.671 , 134614034]
];
$conv{8} = [
[0 , 8294861],
[25.5693 , 31508525],
[43.3313 , 50354909],
[67.3312 , 8.44e+07],
[70 , 70009520],
[91.181 , 103684650],
[151.023 , 123415272]
];
$conv{9} = [
[0 , 7e+06],
[19.7245 , 20102047],
[34.6017 , 33496795],
[39.7 , 39691558],
[46.2 , 46242655],
[58.5 , 58516720],
[59.5 , 59463565],
[105.599 , 85186878],
[121.831 , 106234734]
];

$conv{"X"} = [
[0 , 26298304],
[24.1252 , 83729959],
[49.4121 , 1.585e+08]
];

if (!exists($conv{"X"})) {
	$conv{21}=$conv{"X"};
}
if (!exists($conv{"Y"})) {
	$conv{21 + 1}=$conv{"Y"};
}

# for increased compatibility with PHP code
sub empty($) {
	my $v=shift;
	return(defined($v) or 0 == $v);
}
sub round($) {
	my $v=shift;
	return(int($v+0.5));
}

#my $debug=1;

sub cM2bp($$) {
	my $chr = shift;
	die "cM2bp needs chromosome specified as first argument, bp comes second" unless defined($chr);
	my $cm = shift;

	my @chrconv;
	if (exists($conv{$chr})) {
		@chrconv=@{$conv{$chr}};
	}
	else {
		print STDERR "func_conversion.pm: No information for chromosome '$chr'.\n";
		return (-2);
	}
	my $cMmin=-1; my $bpmin=-1;
	my $cMmax=-1; my $bpmax=-1;
	my $actbp=-1; my $lastbp=-1;
	my $actcM=-1; my $lastcM=-1;
	my $found=0;
	my $prevcM=-1; my $secondCM=-1;
	my $prevbp=-1; my $secondBP=-1;
	if(-1 == $#chrconv) {
		print STDERR "func_conversion.pm: No information for chromosome '$chr'.\n";
		return (-2);
	} else {
		foreach my $k (@chrconv) {
			my ($cMorgan,$bp) = @$k;
			#print "Learning ($chr): cMorgan: $cMorgan, bp:$bp\n" if $debug;
			if (-1 == $cMmin) {
				$cMmin=$cMmax=$cMorgan;
				$bpmin=$bpmax=$bp;
			}
			else {
				if (-1 == $secondCM && !empty($cMorgan)) {
					$secondCM=$cMorgan;
					$secondBP=$bp;
				}
				if ($bp>$bpmax) {
					$bpmax=$bp;
					$cMmax=$cMorgan;
				}
			}

			if ($found) {
			}
			else {
				if ($cm<$cMorgan) {
					#print "cm:$cm < cMorgan:$cMorgan\n" if $debug;
					$found=1;
					if (-1 == $actcM) {
						$actcM=$cMorgan;
						$actbp=$bp;
						$lastcM=$prevcM;
						$lastbp=$prevbp;
					}
					last;
					next;
				}
				else {
					#print "cm:$cm >= cMorgan:$cMorgan\n" if $debug;
				}
			}
			$prevcM=$cMorgan;
			$prevbp=$bp;
		}

		my $ret;

		if ($cm<=$cMmin) {
			# cM requested upstream of first marker
			$ret=$bpmin+($cm-$cMmin)/($secondCM-$cMmin)*($secondBP-$bpmin);
		} elsif (-1 != $actcM) {
			$ret=$lastbp+($cm-$lastcM)/($actcM-$lastcM)*($actbp-$lastbp);
		} else {
			# downstream of rightmost marker
			if ($cMmax==$cMmin) {
				# we only haev a single marker - helpless?
				# FIMXE: implement extra point (0,0)
				print "<p>cMmax==cMmin ($cMmax==$cMmin)\n<p>";
				$ret=-1;
			}
			else {
				# extrapolating from the first to the last marker
				$ret=$bpmin+($cm-$cMmin)/($cMmax-$cMmin)*($bpmax-$bpmin);
			}
		}
		return(round($ret));
	}
}

sub bp2cM($$) {
	my $chr=shift;
	die "bp2cM needs chromosome specified as first argument, bp comes second" unless defined($chr);
	my $bpInput=shift;
	$bpInput=0 unless defined($bpInput);

	if (!exists($conv{$chr})) {
		print STDERR "func_conversion.pm: No information for chromosome '$chr'.\n";
		return (-2);
	}

	my @chrconv;
	if (exists($conv{$chr})) {
		@chrconv = @{$conv{$chr}};
	}
	my $cMmin=-1; my $bpmin=-1;
	my $cMmax=-1; my $bpmax=-1;
	my $actbp=-1; my $lastbp=-1;
	my $actcM=-1; my $lastcM=-1;
	my $found=0;
	my $prevcM=-1; my $secondCM=-1;
	my $prevbp=-1; my $secondBP=-1;
	#if (empty($chrconv)) {
	#} elsif (!is_array($chrconv)) {
	#	print "<p>func_conversion_55.php: Internal error. (chr '$chr').</p>\n";
	#	return (-3);
	#}
	if(-1 == $#chrconv) {
		print STDERR "func_conversion.pm: No information for chromosome '$chr'.\n";
		return (-2);
	} else {
		foreach my $k (@chrconv) {
			my ($cMorgan,$bp) = @$k;
			if (-1 == $cMmin) {
				# first time this loop was entered
				# this will also be the minimal value
				$cMmin=$cMmax=$cMorgan;
				$bpmin=$bpmax=$bp;
			}
			else {
				if (-1 == $secondCM && !empty($cMorgan)) {
					$secondCM=$cMorgan;
					$secondBP=$bp;
				}
				if ($bp>$bpmax) {
					$bpmax=$bp;
					$cMmax=$cMorgan;
				}
				if (! $bp > $bpmin) {  # avoiding the lt sign on web page by negation
					print  "Suddenly found bp value at $bp smaller than current minimum at $bpmin.";
					$bpmin=$bp;
					$cMmin=$cMorgan;
				}
			}

			if ($found) {
			}
			else {
				if ($bpInput >= $bp) {  # avoiding the lt sign for web presentation
				}
				else {
					$found=1;
					if (-1 == $actcM) {
						$actcM=$cMorgan;
						$actbp=$bp;
						$lastcM=$prevcM;
						$lastbp=$prevbp;
					}
					last;
					next;
				}
			}
			$prevcM=$cMorgan;
			$prevbp=$bp;
		}

	 	my $ret;
		if ($bpInput<=$bpmin) {
			# bp requested upstream of first marker
			$ret=$cMmin+($bpInput-$bpmin)/($secondBP-$bpmin)*($secondCM-$cMmin);
		} elsif (-1 != $actcM) {
			$ret=$lastcM+($bpInput-$lastbp)/($actbp-$lastbp)*($actcM-$lastcM);
		} else {
			# downstream of rightmost marker
			if ($cMmax==$cMmin) {
				# we only have a single marker - helpless?
				# FIMXE: implement extra point (0,0)
				print "<p>cMmax==cMmin ($cMmax==$cMmin)\n<p>";
				$ret=-1;
			}
			else {
				# extrapolating from the first to the last marker
				$ret=$cMmin+($bpInput-$bpmin)/($bpmax-$bpmin)*($cMmax-$cMmin);
			}
		}
		return($ret);
	}
}

# This conversion is based on ensembl database ensembl_mart_55 on host martdb.ensembl.org:5316

1;
