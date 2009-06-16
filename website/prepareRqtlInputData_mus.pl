#!/usr/bin/perl -w

#    A T T E N T I O N:   Edit the template, not this file !!!!

=head1 NAME

genotypesAndEisen.pl

=head1 SYNOPSIS

=head1 DESCRIPTION

This script is executed by the webserver.
It provides files with expression information about the mice for each trait_id.
It needs the expression-file and the genotyping-file. Further it needs the file with the classical scoring data.

	USAGE: me.pl <scoreselection> "separator"  <geneselection> [test]

If executed with "test" as last argument, it's beeing presumed, that the script has been executed from the command line and therefore the script will provide output on the command line.

=AUTHOR

Steffen Moeller <moeller@inb.uni-luebeck.de>
Benedikt Bauer <bauer@informatik.uni-luebeck.de>

=cut


use CGI::Carp qw(fatalsToBrowser);
use strict;
use FCGI;
use Cwd;

my $request = FCGI::Request();

my %data =      (
                'genofile' => 'geneexpr_genotypes.csv',   #result
                'phenofile' => 'eisen.txt',  #eisen
                'scorefile' => 'mouse_scores_classical.csv'
                );

my $base="/nfshome/bauer/gitEqtlRepo.git/";
my $database="/nfshome/bauer/gitEqtlRepo.git/data_mus/";

my ($geno,$expr,$score,@geneselection);
my ($markers,$chromosomes,$mouseid,$l);
my (@experiments,$geneid,$probeid);
my @vals;
my $sep=",";
my $test=undef;

open(GENO, $base.$database.$data{'genofile'}) || open(GENO,$database.$data{'genofile'}) || open(GENO,$data{'genofile'}) || die "No such gene-file at ".$database.$data{'genofile'}.": ".$@;               #open the file wich contains probe-data

$markers=readline(GENO);
my $new=undef;
$new=1 if $markers=~/NEW/;

$chromosomes=readline(GENO);
#$chromosomes=~ s/Chr\.//gi;
$chromosomes=~ s/\.//gi;

my (@m,@c);
@m=split(/[,\t]/,$markers);
@c=split(/[,\t]/,$chromosomes);
shift @m; # Mouse ID
shift @c; # Mouse ID

my %genotyping;
while(<GENO>) {
        chomp;
        @vals=split(/[\t,]/,$_);
        $mouseid=shift @vals;
        if (!defined($mouseid)) {
                last;
                next;
        }
#        if (defined($new)) {
#		my $mouseid2=shift @vals;
#                #$mouseid=$mouseid2 if $mouseid2 =~ /^\d+$/;
#	}
	print STDERR "MouseID: $mouseid\n" if ($test);
	$genotyping{$mouseid}=join($sep,@vals);
}
close(GENO);

open(EXPR, $base.$database.$data{'phenofile'}) || open(EXPR,$database.$data{'phenofile'}) || open(EXPR,$data{'phenofile'}) || die "No such pheno-file at $database$data{'phenofile'}, executed from ".cwd().": $@";            #insert-file will  be expaneded by probe-column

                #
                # read in conversion table for new and old identifiers - the new ones count
                #

my %conversionON;  #ON like old-new
my %conversionNO;  #NO like new-old
my $openworks;
$openworks=open(CONVERSION,"<$database/old_new_numbers.csv") or die "Couldn't open Conversion-file\n";
if ($openworks) {
	while(<CONVERSION>) {
		chomp;
		my ($left,$right)=split(/[,\t]/);
		if (defined($right) && defined($left) && $right=~/^\d+$/ && $left=~/^\d+$/) {
			$conversionON{$left}=$right;
			$conversionNO{$right}=$left;
			print STDERR "Converting $left to $right\n" if ($test);
		}
	}
}

#
# read in expression data and store it in memory
#

@experiments=split(/[\t,]/,readline(EXPR));
my $expressionHasName=0;

my %experiment_position;
my $e_index=0;
foreach my $exp (@experiments) {
        if ($exp =~ /NAME/) {
		$expressionHasName=1;
		next;
	}
	elsif ($exp =~/UNIQID/) {
		next;
	}
	else {
		$exp=~ s/[-_][^-_]*$//;
		print STDERR "exp: $exp\n" if ($test);
		$experiment_position{$exp}=$e_index;
		if (exists($conversionNO{$exp})) {   # If there is a conversion from old to new, then set the index also for the old one
			$exp=$conversionNO{$exp};
			$experiment_position{$exp}=$e_index;
		}
		$e_index++;
	}
}

print STDERR "Experiments: ".join(",",@experiments)."\n" if ($test);
my %expressiondata;
while(<EXPR>) {
        chomp;
        $l=$_;
        @vals=split(/[\t,]/,$l);
        $probeid=shift @vals;
        chomp($probeid);
        if (!defined($probeid)) {
                        last;
                        next;
        }
        if ($expressionHasName) {
                $geneid=shift @vals;
                die "\nError reading expression data, probe $probeid has no gene ID defined.\n" unless defined($geneid);
                print STDERR "GeneID $geneid\tProbeID=$probeid\n" if ($test);
        }
        else {
                print STDERR "ProbeID=$probeid\n" if ($test);
        }
        if (exists($expressiondata{$probeid})) {
		print STDERR "Ignoring redundant entry for '$probeid'";
		print STDERR "Overwriting redundant entry for '$probeid'" if ($test);
	}
	$expressiondata{$probeid}=join($sep,@vals);
}
close(EXPR);

open(SCORE, $base.$database.$data{'scorefile'}) || open(SCORE,$database.$data{'scorefile'}) || open(SCORE,$data{'scorefile'}) || die "No such score-file at ".$database.$data{'scorefile'}.": ".$@;               #open the file wich contains score-data

#@scoreselection=("sex");
#while ($ARGV[0] ne "separator") {
##	push(@scoreselection,$ARGV[$i]) unless ($ARGV[$i] eq "sex" || $ARGV[$i] eq "pgm");
#	if ($ARGV[0] ne "sex") {
#		push(@scoreselection,shift(@ARGV));
#	}
#	else {
#		shift(@ARGV);
#	}
#}
#
#$geno=shift(@ARGV);
#$expr=shift(@ARGV);

my @index;
my $scoreline=readline(SCORE);
my @scoreline=split($sep,$scoreline);
shift @scoreline;
#for ($i=0;$i<=$#scoreline;$i++) {
#        my $j;
#        for ($j=0;$j<=$#scoreselection;$j++) {
#                if ($scoreline[$i] eq $scoreselection[$j]) {
#                        push(@index,$i);
#                        $j=$#scoreselection+1;
#                }
#        }
#}
my %scoredata;
while(<SCORE>) {
        chomp;
        @vals = split(/[\t,]/,$_);
        $mouseid=shift(@vals);
        if (!defined($mouseid)) {
                last;
                next;
        }
        $scoredata{$mouseid}=join($sep,@vals);
}

close(SCORE);

if ($test) {
        print STDERR "Successfully collected expression data for probe IDs:\n";
        print STDERR join(",",sort keys %expressiondata)."\n";
        print STDERR "\n";
}


my @ignore;
@ignore=("^SJL","^B10","^Illmn","^7213","^7214","^7218","^7220","_rep");

if ($test) {
	print STDERR "Markers: $markers\n";
	print STDERR "Positions $chromosomes\n";
}

while (FCGI::accept >= 0) {

        print   "Content-type: text/plain\n\n";                                 #sign page-type as plain

        #check if argument has been given
        my @params = split(/&/, $ENV{'QUERY_STRING'});
	my ($probeID,@scoreselection);
	@scoreselection=("sex");

	foreach my $key (@params){
		my @tmp = split(/=/,$key);
                if( $tmp[0] =~ m/probesetid/ ){
                        $probeID = $tmp[1];
                }
		elsif( $tmp[0] =~ m/score/ ){
			if ($tmp[1] ne "sex"){
				push(@scoreselection,$tmp[1]);
			}
		}
        }

	my $k;
	for ($k=0;$k<=$#scoreline;$k++) {
		my $j;
		for ($j=0;$j<=$#scoreselection;$j++) {
			if (lc($scoreline[$k]) eq lc($scoreselection[$j])) {
				push(@index,$k);
				$j=$#scoreselection+1;
			}
		}
	}
	@vals=@vals[@index];

        if( !defined( $probeID ) && $#ARGV == 2 ){
                $probeID = $ARGV[2];                            #id of probset to insert
        }elsif( !defined( $probeID ) && $#ARGV == 0 ){
                $probeID = $ARGV[0];                            #id of probset to insert
        }

	if( !defined( $probeID) ){
                print "Usage when accessed via web address :\t<url>?probesetid=\"probesetid\"\n",
                      "      When accessed via command line:\tinsertProbe_online.pl gene-file pheno-file probesetid\n";
                exit(-1);
        }
	
if (exists($expressiondata{$probeID})) {
                print "Expressiondata($probeID): ". $expressiondata{$probeID}."\n" if ($test);
        }
        else {
                print STDERR "Ignoring unique gene id '$probeID', no expression data available\n" if ($test);
                next;
        }

        my $ret;

	$ret = "Mouse".$sep."Expression".$sep.join($sep,@scoreselection).$sep.join($sep,@m);
	$ret .= "$sep$sep".$sep x ($#scoreselection).$sep.join($sep,@c);

	my @expressionlevels;
	@expressionlevels=split(/[\t,]/,$expressiondata{$probeID});

	my $offset;
	if ($expressionHasName) {
		$offset=2;
	}

	else {
		$offset=1;
	}

	my $numExperiments;
	$numExperiments=0;

	my %expseen;
	my $i=0;

	print "Experiment_position: " . join("\t",keys %experiment_position) . "\n" if ($test);

	foreach my $exp (keys %experiment_position) {

                next unless $exp =~ /[A-Za-z0-9]/;
                next if $exp =~ /NAME/i;
                next if $exp =~ /UNIQID/i;
                next if $exp =~ /illum/i;

                if (exists($conversionNO{$exp})) {
                        $exp=$conversionNO{$exp};
                }

                if (exists($expseen{$exp})) {
                        print STDERR "Mouse '$exp' for experiment $numExperiments of ".($i+1)." was already used.\n" if ($test);
                }
                else {
                        $expseen{$exp}=1;

                        my $useThisExperiment;
                        $useThisExperiment=1;
                        foreach my $t (@ignore) {
                                if ($exp =~ /$t/mi) {
                                        print STDERR "Expelling experiment '$exp' as requested by rule '$t'.\n" if ($test);
                                        $useThisExperiment=0;
                                        last;
                                }
                        }

                        if ($useThisExperiment) {
                                $numExperiments++;
                                print STDERR "Exp: $exp  num $numExperiments of ".($i+1)."\n" if ($test);
                                if (exists($genotyping{$exp})) {
                                        my @types=split(/[\t,]/,$genotyping{$exp});
                                        if (!exists($experiment_position{$exp})) {
                                                print STDERR "Could not find position for mouse '$exp'.\n" if ($test);
                                        }
                                        if (!exists($scoredata{$exp})) {
                                                print STDERR "Could not find scoredata for mouse '$exp'.\n" if ($test);
                                        }
                                        else {
						my @scoring=split($sep,$scoredata{$exp});
                                                @scoring=@scoring[@index];
                                                my $scoredata=join($sep,@scoring);
                                                $ret .= "$exp$sep".$expressionlevels[$experiment_position{$exp}]."$sep".$scoredata.$sep.join($sep,@types)."\n";
                                        }
                                }
				else {
                                        print STDERR "Genotyping for mouse '$exp' not found\n" if ($test);
                                        next;
                                }
                        }
                }
                $i++;
        }

	print $ret;

#	open(PROBEFILE, $base.$database."scripts/results/$probeID.csv.gz") || open(PROBEFILE,$database."scripts/results/$probeID.csv.gz") || open(PROBEFILE,"$probeID.csv.gz") || die "No such probeID: $probeID"               #open the file wich contains score-data

#	my @probeFile;
#	while(<PROBEFILE>){
#       	chomp;
#	      	push (@probeFile,$_);
#	}
#	close(PROBEFILE);
#
#	my $lastIndex=$#probeFile;
#	for(my $i=0; $i<=$lastIndex; $i++){
#		my @probe_line=split(',',$probeFile[$i]);
#		my @score_line=split(',',$scoreFile[$i]);
#		my $mouseid=shift(@score_line);
#		
#		my $ret=shift(@probe_line).$sep.shift(@probe_line).$sep;
#		if(0==$i){
#			$ret.=join($sep,@score_line).$sep.join($sep,@probe_line);
#		}
#		
#		elsif (1==$i){
#			$ret.=$sep x $#score_line .$sep.join($sep,@probe_line);
#		}
#		else {
#			$ret.=join($sep,@score_line).$sep.join($sep,@probe_line);
#		}
#               print $ret."\n";
#
#	}
}
#shift(@ARGV);
#if ($ARGV[$#ARGV] eq "test") {
#	$test=1;
#	pop(@ARGV);
#}
