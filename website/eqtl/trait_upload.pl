#!/usr/bin/perl
#FIXME:
# write comments
use strict;

my $mouseids=undef;
my $extended=0;

while(<>) {
	my @cols=split(/\t/,$_);
	if (!defined($mouseids)) {
		chomp;
		my $n=shift(@cols);
		die "The file is not in Eisen format, expected UNIQID to be first element (was $n).\n"
			unless $n=~/^UNIQID/i;
		if ($cols[0] =~ /^NAME/i) {
			shift @cols;
			$extended=1;
		}
		$mouseids=join(",",@cols);
	}
	else {
		my $u=shift(@cols);
		my $n=undef;
		if($extended){
			$n=shift @cols;
		}
		my $mean=0;
		foreach my $c (@cols) {
			$mean += $c;
		}
		$mean /= ($#cols + 1);

		my $sd=0;
		foreach my $c (@cols) {
			$sd += ($mean-$c)*($mean-$c);
		}
		$sd /= $#cols;

		my $vals=join(",",@cols);

		print "insert into `trait` set ";
		print "`trait_id`='$u'";
		print ", `name`='$n'" if defined($n);
		print ", `mean`=$mean, `sd`=$sd";
		print ", `vals`='$vals'";
		print ", `individuals`='$mouseids'";
		print ";\n";
		
	}
}
