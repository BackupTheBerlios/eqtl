#!/usr/bin/perl -w

=head1 NAME

autoTransformTemplates.pl - inspect conf/*.conf files and perform respective substitutions in files

=head1 SYNOPSIS

autoTransformTemplates.pl -projectname (rat|mus) <list of files>

=head1 DESCRIPTION

Configuration files can be read when a script is started. This script
allows for a different approach: the configuration files are read
once upon the installation of the software and from a given template
(the source code expected as "find . -name "*.template") with a set of
configuration files (expected as conf/*.conf) this script prepares
new files, without the ending ".template" that have all variables
substituted.

The script was motivated by web pages that should be adaptable to various 
analogous projects.

=head1 OPTIONS

=over 4

=item -projectname <projectname> sets the name of the project. Configuration files
        are expected at the directory ./conf_<projectname>

=back

=head1 AUTHORS

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2008-2009

=cut

use strict;
use Getopt::Long;
use Pod::Usage;
my ($projectname,$help,$man);

GetOptions(
		"projectname=s"=>\$projectname,
		"help"=>\$help
) or pod2usage(2);

if (defined($projectname)) {
	if  ( ! -d "conf_$projectname" ) {
		print STDERR "Could not find directory with project's configuration files expected at 'conf_${projectname}'.\n";
		exit(-1);
	}
}
elsif ( -d "conf" ) {
		$projectname=""
}
else {
	print STDERR "Projectname is not defined and no 'conf' folder available.\n";
	print "\n";
	pod2usage(1);
}

pod2usage(1) if $help;
pod2usage(-exitstatus => 0, -verbose => 2) if $man;

my $configdir="conf";
if ("" ne "$projectname") {
	$configdir="conf_${projectname}";
}

my %vars;

foreach my $c (glob("$configdir/*.conf")) {
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


# returns string with transformed content
sub transform($) {
	my $f = shift;
	open(F,"<$f") or die "Could not open file '$f' to transform: $@\n";
	my $ftext = join("",<F>);
	
	foreach my $n (keys %vars) {
		my $dest=$vars{$n};
		$ftext =~ s/$n/$dest/g
	}
	$ftext =~ s/<LINEBREAK>/\n/g;
	$ftext =~ s/<TABULATOR>/\t/g;

	return $ftext;
}

foreach my $f (@ARGV) {
	my $fnew;
	if (($fnew) = $f =~ /(.*)\.template$/m) {
		if ( -M "$f" > -M  "$fnew" ) {
			print STDERR "  Skipping '$f', not newer than '$fnew'.\n";
			next;
		}
		
		print STDERR "  Transforming '$f' to '$fnew'.\n";
		open(FNEW,">$fnew") or die "Could not write to file '$fnew', $@\n";
		my $ftext = transform($f);
		print FNEW $ftext;
		close(FNEW);
		if ($ftext =~ /^#!/) {
			chmod(0755, $fnew) or die "Could not change permissions of '$fnew', $@\n";
		}
	}
	else {
		print STDERR "  Transforming '$f'.\n";
		print STDOUT transform($f);
	}
	close(F);
}

=head1 AUTHORS

Steffen Moeller <moeller@inb.uni-luebeck.de>
2008-2009, University of Lübeck

=cut
