#!/usr/bin/perl -w

if( $#ARGV != 1 ){
	die "USAGE: ./SCRIPTNAME [source folder (base)] [destination folder (base)]";
}

if( !(-d $ARGV[1]) ){
	system("mkdir ".$ARGV[1]);
}
my $base = $ARGV[1];
my @toSort = <$ARGV[0]*>;
my @tmpl = ('107', '108', '109');
print "\tSORTING:\t\t\t";
for( my $i=0; $i<=$#toSort; $i++ ){
	if( -d $toSort[$i] ){
 		my @tmp = <$toSort[$i]/*>;
		foreach (@tmp){
			push( @toSort, $_ );
		}
	}else{
 		print "processing $toSort[$i]\t";
		my $fullpath = $toSort[$i];
		$fullpath =~ s/\(/\\\(/;
		$fullpath =~ s/\)/\\\)/;
		my @tmp = split( /\//, $toSort[$i] );
		my $currFile = $tmp[$#tmp];
		my @tmp_pre = split(/\(/,$currFile);
		@tmp = split( /_/,$tmp_pre[0] );
		my $case = 'scanone';
		my $indicator = 0;
		my $cnt = 0;
		if( $tmp[$cnt] eq 'scantwo' ){$case = 'scantwo';$cnt++;}
		elsif( $tmp[$cnt] eq 'scanone' ){$cnt++;}
		else{ $indicator = 1;}
		my $number = $tmp[$cnt++];
		my $thresh = $tmp[$cnt++];
		my $perms = $tmp[$cnt++];
		my $covars= $tmp_pre[1];
		$covars =~ s/\(//;
		$covars =~ s/\).*$//;		
		$covars =~ s/,/_/;
		foreach (@tmpl){
			my $num = $_;
			if( $number =~ /^$num/ ){
				my $currentFolder = $base."/".$case."/".$covars."/".$num."/";
				$currentFolder =~ s/\/\//\//g;
				if( !(-d $currentFolder) ){system("mkdir -p ".$currentFolder);}
				if( $indicator == 1 ){ 
					if( system("mv ".$fullpath." ".$currentFolder.$case."_".$number."_".$thresh."_".$perms."_\\(".$covars."\\).csv.gz") != 0 ){
 						print "ERROR!  i";
					}else{
 						print "moved to\t".$currentFolder;
					}
				}else{
#					system("mv ".$fullpath." ".$currentFolder);
 					if( system("mv ".$fullpath." ".$currentFolder) != 0 ){print "ERROR!";}
 					else{print "moved to\t".$currentFolder;}
				}
			}
		}
 		print "\n";
	}
}
print "\t\t[DONE]\n\n";
