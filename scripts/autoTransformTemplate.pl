#!/usr/bin/perl -w

=head1 NAME

autoTransformTemplates.pl - inspect conf/*.conf files and perform respective substitutions in files

=head1 SYNOPSIS

autoTransformTemplates.pl -projectname (rat|mus) <list of files>

=head DESCRIPTION

Configuration files can be read when a script is started. This script
allows for a different approach: the configuration files are read
once upon the installation of the software and from a given template
(the source code expected as "find . -name "*.template") with a set of
configuration files (expected as conf/*.conf) this script prepares
new files, without the ending ".template" that have all variables
substituted.

It was motivated by web pages that should be adaptable to various 
analogous projects.

=head1 OPTIONS

=over 4

=item -projectname <projectname> sets the name of the project. Configuration files
        are expected at the directory ./conf_<projectname>

=back

=cut

use strict;
use Getopt::Long;
use Pod::Usage;
my ($projectname,$help,$man);

GetOptions(
		"projectname=s"=>\$projectname,
		"help"=>\$help
) or pod2usage(2);

unless (defined($projectname)) {
	print STDERR "Projectname is not defined.\n";
	print "\n";
	pod2usage(1);
}

pod2usage(1) if $help;
pod2usage(-exitstatus => 0, -verbose => 2) if $man;

unless ( -d "conf_${projectname}") {
	print STDERR "Could not find directory with project's configuration files expected at 'conf_${projectname}'.\n";
	exit(-1);
}


my %vars;

foreach my $c (glob("conf_${projectname}/*.conf")) {
	print STDERR "\tFound configuration file '$c'.\n";
	open(CONF,"<$c") or die "\tCould not open configuration file '$c': $@\n";
	while(<CONF>) {
		my ($name,$val);
		chomp;
		if (($name,$val)=/([^\t]+)\t(.*)/) {
			$vars{$name}=$val;
		}
		else {
			#print STDERR "Ignoring: $_\n";
		}
	}
	close(CONF);
}

foreach my $n (keys %vars) {
	print STDERR "\t$n:".$vars{$n}."\n";
}

foreach my $f (@ARGV) {
	open(F,"<$f") or die "Could not open file '$f' to transform: $@\n";
	my $ftext = join("",<F>);
	
	foreach my $n (keys %vars) {
		my $dest=$vars{$n};
		$ftext =~ s/$n/$dest/g
	}
	$ftext =~ s/<LINEBREAK>/\n/g;
	$ftext =~ s/<TABULATOR>/\t/g;

	my $fnew;
	if (($fnew) = $f =~ /(.*)\.template$/m) {
		print STDERR "  Transforming '$f' to '$fnew'.\n";
		open(FNEW,">$fnew") or die "Could not write to file '$fnew', $@\n";
		print FNEW $ftext;
		close(FNEW);
	}
	else {
		print STDERR "  Transforming '$f'.\n";
		print STDOUT $ftext;
	}
	close(F);
}

=head1 AUTHORS

Steffen Moeller <moeller@inb.uni-luebeck.de>
2008-2009, University of Lübeck

=cut
