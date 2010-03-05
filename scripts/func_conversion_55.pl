#!/usr/bin/perl -w

=head1 NAME

func_conversion_55.pl - testing of the cM <-> bp conversion

=head1 SYNOPSIS

./scripts/func_conversion_55.pl

=head1 DESCRIPTION

=head1 AUTHORS

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, Germany, 2010

=cut

BEGIN {
	for my $d (("./scripts","./","../scripts","..")) {
		push @INC,"$d" if -d "$d";
	}
}


use strict;
use func_conversion_55;

print "Now some tests.\n";
foreach my $chr (keys %conv) {
	print "***************\n";
	print "** $chr : cm\tbp\tbp2cM\tcM2bp\n";
	print "***************\n";
	my $aref=$conv{$chr};
	my @a=@$aref;
	my $bppref=undef;
	my $cmpref=undef;
	foreach my $k (@a) {
		my ($cm,$bp)=@{$k};
		if (defined($bppref)) {
			my $meanBp=($bp+$bppref)/2;
			my $meanCm=($cm+$cmpref)/2;
			print "Mid:\t$meanCm\t$meanBp\t";
			print bp2cM($chr,$meanBp)."\t".cM2bp($chr,$meanCm)."\n";
		}
		print "\t".join("\t",@{$k})."\t->\t". bp2cM($chr,$bp)."\t".cM2bp($chr,$cm)."\n";
		print "\n";
		($cmpref,$bppref)=@{$k};
	}
	last;
}
