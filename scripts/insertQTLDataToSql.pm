package insertQTLDataToSql;

use strict;
use DBI;

use vars qw($VERSION 
	$exitcode
	%EXITCODE2TEXT
	$sth_loc
	$sth_loc_update
	$sth_loc_select
	$sth_jobexists
	$sth_reset_recalculate
	$sth_reset_queued
	$sth_getloc
	$sth_delete
	$sth_delete_interaction
	$sth_delete_qtl
	$sth_compute
	$sth_qtl_scantwo
	$sth_qtl_scanone
@ISA @EXPORT @EXPORT_OK);

require Exporter;

%EXITCODE2TEXT=(
	 "3" => "Job went technically fine, but contained no QTLs to upload.",
	 "2" => "Job was already completed, with no instruction to recalculate.",
	 "1" => "Job is in state QUEUED or RECALCULATE, and should not be available.",
	 "0" => "No error. Nothing uploaded either. The file may be a suprise.",
	"-1" => "Default error. Processing should not continue.",
	"-2" => "File corrupt. Processing should continue with another file.",
	"-3" => "Database constraints violated. Processing should not continue."
);

@ISA = qw(Exporter AutoLoader);
# Items to export into callers namespace by default. Note: do not export
# names by default without a very good reason. Use EXPORT_OK instead.
# Do not simply export all your public functions/methods/constants.
@EXPORT = qw(
	perform
);
$VERSION = '0.02';



# Helping functions

sub getLocus($$){
	$sth_getloc->execute( $_[0], $_[1], $_[1] );
	my $ret = $sth_getloc->fetchrow_array();
	$sth_getloc->finish;
	return $ret;
}

sub setLocus($$){
	my $chr = $_[0];
	my $cM = $_[1];
	my $name = 'ic'.$chr.'.loc'.$cM;
	$sth_loc->execute( $name, $chr, $cM, undef );
	$sth_loc->finish;
	return $name;
}

=head2 harmless 

This routine decides about how to deal with errors.

=cut

sub harmless($$) {
	my $message = shift;
	my $ecode = shift;
	print "$ecode\n";
	print STDERR "$message";
	#exit ($ecode);
	$exitcode=$ecode;
}

=head2 perform

This is function doing most of the work.

=cut

sub perform {

	my ($dbh, $filename, $force, $verbose, $dryrun) = @_;

	$dryrun=0 unless defined($dryrun);

	$exitcode=-1; # default

	# Constants

	my $NULL=undef;

	# Queries to prepare (once)

	# Prepare queries

	unless (defined($sth_loc) and defined($sth_qtl_scanone)) {
		my $sql_loc = qq{INSERT INTO locus (Name, Chr, cMorgan, Organism, Marker) VALUES (?,?,?,"Rattus norvegicus",?)};
		my $sql_loc_update = qq{UPDATE locus set cMorgan =? where Name=?};
		my $sql_loc_select = qq{select Name, cMorgan from locus where Name=?};
		my $query_jobexists = "select computation_id, status, version from computation where jobname = ?;";
		my $reset_query_recalculate = "UPDATE computation SET status=\"RECALCULATE\" where jobname = ? ";
		my $reset_query_queued = "UPDATE computation SET status=\"QUEUED\" where jobname = ? ";			
		my $sql_getloc = qq{SELECT Name FROM locus WHERE Chr = ? AND ((cMorgan-?)>=-0.2) AND ((cMorgan-?)<=0.2)};
		my $sql_delete_interaction = "DELETE from locusInteraction where computation_id = ?";
		my $sql_delete_qtl = "DELETE from qtl where computation_id = ?";
		my $sql_compute = "UPDATE computation SET status=\"DONE\", version= ?, "
			  ."timestamp = ? where computation_id = ?";
		my $sql_qtl_scantwo = qq{INSERT INTO  locusInteraction (computation_id, Trait,A, B, LogP, covariates, lod_full, lod_fv1, lod_int, lod_add, lod_av1, qlod_full, qlod_fv1, qlod_int, qlod_add, qlod_av1 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)};
		my $sql_qtl_scanone = qq{INSERT INTO  qtl (computation_id, Locus, Trait, LOD, Chromosome, cMorgan_Peak, Quantile, covariates, phenocol) VALUES (?,?,?,?,?,?,?,?,?)};

		$sth_loc = $dbh->prepare( $sql_loc );
		$sth_loc_update = $dbh->prepare( $sql_loc_update );
		$sth_loc_select = $dbh->prepare( $sql_loc_select );
		$sth_jobexists = $dbh->prepare($query_jobexists);
		if ($@) {
			print STDERR "Could not prepare query 'sth_jobexists': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_reset_recalculate = $dbh->prepare($reset_query_recalculate);
		if ($@) {
			print STDERR "Could not prepare query 'sth_reset_calculate': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_reset_queued = $dbh->prepare($reset_query_queued);
		if ($@) {
			print STDERR "Could not prepare query 'sth_reset_queued': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_getloc = $dbh->prepare( $sql_getloc );
		if ($@) {
			print STDERR "Could not prepare query 'sth_getloc': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_delete_interaction = $dbh->prepare($sql_delete_interaction);
		if ($@) {
			print STDERR "Could not prepare query 'sth_delete_interaction': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_delete_qtl = $dbh->prepare($sql_delete_qtl);
		if ($@) {
			print STDERR "Could not prepare query 'sth_delete_qtl': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_compute = $dbh->prepare($sql_compute);
		if ($@) {
			print STDERR "Could not prepare query 'sth_compute': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_qtl_scantwo = $dbh->prepare($sql_qtl_scantwo);
		if ($@) {
			print STDERR "Could not prepare query 'sth_qtl_scantwo': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
		$sth_qtl_scanone = $dbh->prepare($sql_qtl_scanone);
		if ($@) {
			print STDERR "Could not prepare query 'sth_qtl_scanone': $@\n";
			harmless($DBI::errstr,-1);
			return($exitcode);
		}
	}

# @file represents all the lines in the file

if ($filename =~ /.gz$/) {
	open (FH,"gzip -dc '$filename' |") or die "Could not uncompress and open file '$filename': $@\n";
}
else {
	open (FH,"<$filename") or die "Could not open file '$filename': $@\n";
}

my @file = <FH>;
close(FH);


my ( $trait, $method, $perms, $threshold, $covars, $alpha, $offset, $steps, $errorP, $draws, @quant, $success, $phenocol );
my @covars_array;

my $cov_meth = 'int';
my $warnLevel = 'lazy';

my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime((stat($filename))[9]);
$year += 1900;
my $filetime = "$year-$mon-$mday $hour:$min:$sec";

my @filename_array = split(/\//, $filename);
# FIXME: "basename" should be used instead ... good for now
$filename = pop(@filename_array);

#check if file is corrupted
if( -1 == $#file or ($file[0] !~ /^<=/) or ($file[0] !~ /=>$/) ){
	harmless("\tERROR: wrong beginning of file!!!\n\t>>$file[0]<<\n",-2);
	return($exitcode);
}

## check if job is in database

my $cnt = 0;
my $compute_id;
my $status;
my $version;

if ($dryrun) {
	print "Testing if job exists.\n" if $verbose;
}
else {
	if ( ! $sth_jobexists->execute("$filename")) {
		harmless($DBI::errstr,-1);
		return($exitcode);
	}
	while( my @data = $sth_jobexists->fetchrow_array() ){
		$compute_id = $data[0];
		$status = $data[1];
		$version = $data[2];
		$cnt++;
	}
	$sth_jobexists->finish;
}

if($cnt>1){
	harmless("More than one entry for job found... please check database\n",-3); ## this is not expected, database likely to be corrupt
	return($exitcode);
}
if($cnt ==0){
	harmless("No entry for job found. Please verify database consistency.\n",-1);## every entry request should be existing
	return($exitcode);
}

if($status =~ /QUEUED/){
	$exitcode = 1;
}
elsif($status =~ /RECALCULATE/){
	$exitcode = 1;
}
elsif($status =~ /DONE/){
	$exitcode = 2;
	harmless("Job was already uploaded, please check if you want to upload it anyway\n",$exitcode);
#	return($exitcode);
}
elsif($status =~ /PROCESSING/){
	# This is where the job should be (PROCESSING or REPROCESSING)
	$exitcode = 0;
}
else {
	harmless("Unknown status: '$status'.\n",-1);
	return($exitcode);
}


if(($status =~ /QUEUED/ && !$force) or ($status =~ /DONE/ && !$force)){
	print "$exitcode\n";
	harmless("$filename was not marked to be processed. "
	        ."To upload the data anyway use the option -f\n",$exitcode);
	return($exitcode);
}

print "\tfilename: $filename status: $status\n";




##############################

my %committed_loci;

my $sectioncount = 0;
my $scantwo = 0;

## parse covariates from filename:

my @fields;
if (@fields=$filename=~/^([^_]+)_([^_]+)_([^_]+)_([^_]+)_\(([^)]+)\)/) {
	$covars = $fields[4];
}

#preparse
for( my $lineno=0; $lineno<=$#file; $lineno++ ){
	my $line = $file[$lineno];
	if( $line =~ /^<=/ && $line =~/=>$/ ){	#header line
		if( $lineno!=0 ){
			if( $warnLevel ne 'strict' ){
				print "\tWARNING: corrupted File, contains the header twice!!!\n";
				last;
			}else{
				#open( OUT, ">>/nfshome/kolbaum/gitEqtl/data/misc/toReDo" );
				#print OUT $trait."_".join(",", @covars_array)."\n";
				#close( OUT );
				print "RECORD WAS RESETTED.\n";
				##### datenbankeintrag zurücksetzen --- was heisst das denn? Steffen
				my $statement_to_execute;
				if($status =~/PROCESSING/){
					$statement_to_execute = $sth_reset_recalculate;
				}elsif($status =~ /REPROCESSING/){
					$statement_to_execute = $sth_reset_queued;
				}
				if ($dryrun) {
					print STDERR "Resetting database entry.\n";
				}
				else {
					if ( ! $statement_to_execute->execute("$filename") ) {
						harmless($DBI::errstr,-1);
						return($exitcode);
					}
					$statement_to_execute->finish;
				}

				
				###################################
				harmless("\tERROR: corrupted file, contains too much data!!!\n"
				        ."\t\tThis file will not be  comitted, but deleted, set to recalculate list as warninglevel is strict!",-2);
				return($exitcode);
			}
		}
		my @tmpLineCloseBrace = split(/\)\s/, $line);
		for( my $parameterNo=0; $parameterNo<=$#tmpLineCloseBrace; $parameterNo++ ){
			#FIXME: some more elegance should eventually find entry here
			if( $tmpLineCloseBrace[$parameterNo] =~ /scanone/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$trait = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /scantwo/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$trait = $temp[1];
				$scantwo = 1;
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /method/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$method = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /permutations/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$perms = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /threshold/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$threshold = $temp[1];
		#	}elsif( $tmpLineCloseBrace[$parameterNo] =~  /covariates/ ){		# covariates will be parsed from the filename at the moment
		#		my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
		#		$covars = $temp[1];
		#		if( !defined($covars) or $covars eq '' ){ $covars = 'none';}
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /alpha/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$alpha = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /offset/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$offset = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /steps/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$steps = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /errorP/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$errorP = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /draws/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$draws = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /success/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$success = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /cov_meth/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$cov_meth = $temp[1];
			}elsif( $tmpLineCloseBrace[$parameterNo] =~  /phenocol/ ){
				my @temp = split( /\(/, $tmpLineCloseBrace[$parameterNo]);
				$phenocol = $temp[1];
				if($phenocol =~ m/=/){$phenocol='';}
			}
		}
	}
	
	if( !defined( $phenocol ) ){
		$phenocol = $trait;
	}

	if( $line =~ /^<\// ){
		if( --$sectioncount < 0 ){
			harmless("\tERROR: can\'t close more sections\n",-2); #hier müsste das file kaputt sein
			return($exitcode);
		}
	}elsif( $line =~ /^</ && !($line =~ /^<=/) ){
		$sectioncount++;
	}

	if( $line =~ /<ENV>/ ){
		my $time = $file[$lineno+4];
		($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($time);
		$year += 1900;
		$filetime = "$year-$mon-$mday $hour:$min:$sec";
	
	}
	
	if( $line =~ /<SUMMARY::quants>/ ){
		$lineno++;
		if( $scantwo ){
			while( $file[++$lineno] !~ /5%/ ){}
			@quant = split( / /, $file[$lineno] );
		}else{
			my @tempComma = split(/,/,$file[++$lineno]);
			$quant[0] = $tempComma[6];
		}
	}
}

if( !($file[$#file] =~ /<\/DATA>/ ) && !($file[$#file] =~ /<\/WARNINGS>/ ) &&!($file[$#file]=~ /<\/ENV>/ ) ){
	open( OUT, ">>/nfshome/kolbaum/gitEqtl/data/misc/toReDo" );
	print OUT $trait."_".join(",", @covars_array)."\n";
	close( OUT );
	harmless("\tERROR: wrong ending of file!!!\n\t>>$file[$#file]<<\n",-2);
	return($exitcode);
}

if( $sectioncount > 0 ){
	open( OUT, ">>/nfshome/kolbaum/gitEqtl/data/misc/toReDo" );
	print OUT $trait."_".join(",", @covars_array)."\n";
	close( OUT );
	harmless("\tERROR: some opened sections have not been closed!!!\n",-2);
	return($exitcode);
}
if( $covars eq 'none' ){
	$cov_meth = 'none';
	@covars_array = ();
}else{
	my @cov_tmp = split(/,/,$covars);
	for( my $covNo=0; $covNo<=$#cov_tmp; $covNo++ ){
		if(${cov_tmp[$covNo]} =~ m/_add|_int/ ){
			push @covars_array,${cov_tmp[$covNo]};
		}
		else
		{	
		push @covars_array,"${cov_tmp[$covNo]}_${cov_meth}";
		}
	}
	if( $cov_meth eq 'add' ){
		$cov_meth = 'additive';
	}
	elsif( $cov_meth eq 'int' ){
		$cov_meth = 'interactive';
	}
	else {
		print STDERR "Unknown cov_math '$cov_meth'.\n";
		exit -1;
	}
}


my $sth_qtl;
if( $scantwo ){
	$sth_qtl = $sth_qtl_scantwo;
	$sth_delete = $sth_delete_interaction;
}else{
	$sth_qtl = $sth_qtl_scanone;
	$sth_delete = $sth_delete_qtl;
}
$version++;

	print "\tDeleting old entries for computation #".$compute_id." from database!\n"
		if $verbose;
	unless ($dryrun) {
		$sth_delete->execute($compute_id); 	
		$sth_delete->finish;
	}

   if( $scantwo ){
	if( $success > 0 ){
		print "\tWriting $success entr".(($success>1)?"ies":"y")." to database!\n";
		#postparse
		for( my $scantwolineno=0; $scantwolineno<=$#file; $scantwolineno++ ){
			my $scantwoline = $file[$scantwolineno];
			
			if( $scantwoline =~ /<SUMMARY::scantwo.S.P>/ ){	#getting qtl informations
				$scantwolineno++; # skip header line
				while( $file[++$scantwolineno] !~ /<\/SUMMARY>/ ){
					$scantwoline = $file[$scantwolineno];
					my @tmpQuote = split( /\"/, $scantwoline );		#"
					my @chr = split( /:/, $tmpQuote[1] );
					$chr[0] =~ s/c//; $chr[0] =~ s/ //;
					$chr[1] =~ s/c//; $chr[1] =~ s/ //;
					my @scantwoField = split( /,/, $tmpQuote[2] );
					if( $#scantwoField < 1 ){ @scantwoField = split(/ /, $scantwoField[0]); }
					for( my $scantwoFieldNo=0; $scantwoFieldNo<=$#scantwoField; $scantwoFieldNo++ ){
						$scantwoField[$scantwoFieldNo] =~ s/\"//g;		#"
					}
					my ($loc1, $loc2);
					$loc1 = getLocus( $chr[0], $scantwoField[1] );
					$loc2 = getLocus( $chr[1], $scantwoField[2] );
					if( !defined( $loc1 ) or $loc1 eq '' ){
						$loc1 = setLocus( $chr[0], $scantwoField[1] );
					}
					if( !defined( $loc2 ) or $loc2 eq '' ){
						$loc2 = setLocus( $chr[1], $scantwoField[2] );
					}
					print STDERR "Now writing scantwo data." if $verbose;
					unless ($dryrun) {
						# Writing scantwo data into table locusInteraction
						$sth_qtl->execute($compute_id, $trait, $loc1, $loc2, $NULL,
							join(",",@covars_array),
							$scantwoField[3], $scantwoField[5], $scantwoField[7],
							$scantwoField[11], $scantwoField[13],
							$quant[1], $quant[2], $quant[3], $quant[4], $quant[5])
								or print "could not write to locus interaction\n";
						$sth_qtl->finish;
					}
				}
			}
		}
	}
	else{
		$exitcode=3;
	}

	print "Update of status: $status -> done\n";
	unless ($dryrun) {
		$sth_compute->execute($version,$filetime,$compute_id) 
			or print "\t****** Could not update status ********\n";
		$sth_compute->finish;
	}
	
    }else{
	if( $success > 0 ){
		print "\tWriting $success entr".(($success>1)?"ies":"y")." to database!\n";
		#postparse
		for( my $scanonelineno=0; $scanonelineno<=$#file; $scanonelineno++ ){
			my $scanoneline = $file[$scanonelineno];
			
			if( $scanoneline =~ /<SUMMARY::scanone.S.P>/ ){	#getting qtl informations
				$scanonelineno++; # skip header line
				while( $file[++$scanonelineno] !~ /<\/SUMMARY>/ ){
					$scanoneline = $file[$scanonelineno];
					my @lineFields = split( /,/, $scanoneline );
					if( $#lineFields < 1 ){ @lineFields = split(/ /, $lineFields[0]); }
					for( my $lineFieldNo=0; $lineFieldNo<=$#lineFields; $lineFieldNo++ ){
						$lineFields[$lineFieldNo] =~ s/\"//g;		#"
					}
					###########hier scantwo ergebnisse schreiben und neue spalte mit einfügen
					print STDERR "Writing single effect into QTL table."
						if $verbose;
					unless ($dryrun) {
						$sth_qtl->execute($compute_id, $lineFields[0], $trait,
							$lineFields[3], $lineFields[1], $lineFields[2],
							$quant[0], join(",",@covars_array), $phenocol);
						$sth_qtl->finish;

						if( ! exists($committed_loci{$lineFields[0]}) ){
							print STDERR "Also preparing entry for locus.\n"
								if $verbose;
							my $loc_name = "";
							my $loc_pos;
							if ($dryrun) {
								print "Testing if locus exists.\n" if $verbose;
							}
							else {
								if ( ! $sth_loc_select->execute("$lineFields[0]")) {
									harmless($DBI::errstr,-1);
									return($exitcode);
								}		
							}
							while( my @data = $sth_loc_select->fetchrow_array() ){
								$loc_name = $data[0];
								$loc_pos = $data[1];
							}
							$sth_jobexists->finish;
							if($loc_name eq ""){
								$sth_loc->execute($lineFields[0],
										     $lineFields[1], 
											$lineFields[2], $NULL);
								$sth_loc->finish;
							}
							elsif ($loc_name eq $lineFields[0] and $loc_pos ne $lineFields[2]){
								$sth_loc_update->execute("$lineFields[2]","$loc_name")
							}
							$committed_loci{$lineFields[0]}="scanone";
						}
					}
				}
			}
		}
	}
	else{
		$exitcode=3;
	}
	print "\tUpdate of status: '$status' -> 'done'\n" if $verbose;
	unless($dryrun) {
		$sth_compute->execute($version,$filetime,$compute_id)
			or print "\t**** could not execute update status (compute_id=$compute_id)****\n";
	}
    }
    return $exitcode;
}

1;
