#!/usr/bin/perl

=head1 NAME

trait_upload.pl - transform traits into SQL statements

=head1 SYNOPSIS

trait_upload.pl filename

=head1 DESCRIPTION

The traits table is key to the organisation of the compute jobs
(workunits) and also for the display of detailed information about the
eQTLs that have been determined. Every project will have different
kinds of information about these traits available. This script is one
example on how to upload that data.

When using more common sources, e.g. a regular Affymetrix chip,
one may think very differently about the traits table inn general. 
But still, one has the option to either trust data from Affymetrix themselves
or use such that was derived from the Ensembl team. The result of that
decision may be forwarded to this traits table.

=head1 AUTHORS

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2008-2009

=cut

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
