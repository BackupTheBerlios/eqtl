#!/usr/bin/perl -w

use strict;
use Cwd qw(realpath);

#-v = verbose
#-s = set params manually
#-p = set params for R-scripts only
#possible to enter scipt-parameters directly
# [ steps=X | draws=X | errorP=X | threshold=X | perms=X | alpha=X | epsilon=X ]
#
#every not committed value will be set to default
my %params = ( 
		'stepsSO' => 1,
		'stepsST' => 8,
		'draws' => 16,
		'errorP' => 0.01,
		'threshold' => 3.5,
		'perms' => 1000,
		'alpha' => 0.07,
		'epsilon' => 0.95,
		'baseURL' => 'http://grid64inb.inb.uni-luebeck.de:8080/stockholm/',
		'dblocalhost' => 'pc13.inb.uni-luebeck.de',
		'dblocalname' => 'eQTL_Stockholm',
		'dblocaluser' => 'qtl',
		'dblocaltype' => 'mysql'
	      );


foreach (@ARGV){
	if( m/^-/ ){
		$params{$_} = 1;
	}elsif( m/=/ ){
		my @tmp = split( /=/,$_ );
		$params{$tmp[0]} = $tmp[1];
	}else{
		die "\tERROR: unknown parameter!\n"
	}
}

#defaultparams installation
if( defined($params{'-s'}) && $params{'-s'} == 1 ){
	foreach my $key (keys %params){
        	print "$key: ";
                $params{$key} = <STDIN>;
	}
}

my $path = realpath($0);
$path =~s/install.pl//;

my (@pathOut, @paramOut, @dbOut, @urlOut);

#path.conf installation
if( ($params{'-p'} != 1) ){
	open(OUT, ">$path/conf/path.conf");
	@pathOut = (	"BASEDIR\t$path\n",
			"DOWNLOADDIR\tdownloads/\n",
			"CSVDATABASE\tdata/csv/\n",
			"XLSDATABASE\tdata/xls/\n",
			"MISCDATABASE\tdata/misc/\n",
			"RESULTSTORAGE\tdata/results/",
			"BORKENFILES\t.brokenFiles/",
			"EVALUATEQUERY\t~/exchange/evaluateQuery.R",
			"LOCALTMP\t~/myTmp"
		    );
	print OUT @pathOut;
	close(OUT);
}

#url.conf installation
if( ($params{'-p'} != 1) ){
        open(OUT, ">$path/conf/url.conf");
        @urlOut = (    	"URLGETDATA\t$params{'baseURL'}prepareRqtlInputData.pl\n",
                        "URLGETSCRIPT\t$params{'baseURL'}getRscript.pl\n",
                        "URLGETEXPR\t$params{'baseURL'}getMMSVdata.pl\n",
			"URLRECALC\t$params{'baseURL'}recalc\n",
			"URLCSVDEPOT\tdownloads/\n",
			"RECALCHOST\tgrid64inb.inb.uni-luebeck.de\n",
			"RECALCPORT\t8080\n",
			"RECALCDIR\t~/mytmp\n",
			"RECALCUNIT\thours\n",
			"RECALCTIMEOUT\t100\n",
			"RECALCPATH\tstockholm/recalc\n"
                    );
        print OUT @urlOut;
        close(OUT);
}

#db.conf installation
if( ($params{'-p'} != 1) ){
        open(OUT, ">$path/conf/db.conf");
        @dbOut = (    	"DATABASECON\t\"DBI:mysql:database=eQTL_Stockholm;host=pc13\", \"qtl\"\n",
			"DATABASEHOSTLOCAL\t$params{'dblocalhost'}\n",
			"DATABASEEQTLNAME\t$params{'dblocalname'}\n",
			"DATABASEQTLNAME\tqtl\n",
			"DATABASEEQTLUSER\t$params{'dblocaluser'}\n",
			"DATABASEQTLUSER\tqtl\n",
			"DATABASETYPELOCAL\t$params{'dblocaltype'}\n",
			"ENSEMBLVERSION\t47"
                    );
        print OUT @dbOut;
        close(OUT);
}

@paramOut = (	"STEPSSO\t$params{'stepsSO'}\n",
		"STEPSST\t$params{'stepsST'}\n",
               	"DRAWS\t$params{'draws'}\n",
               	"ERRORP\t$params{'errorP'}\n",
               	"THRESHOLD\t$params{'threshold'}\n",
               	"PERMS\t$params{'perms'}\n",
               	"ALPHA\t$params{'alpha'}\n",
               	"EPSILON\t$params{'epsilon'}\n"
	   );

open(OUT, ">$path/conf/param.conf");
print OUT @paramOut;
close(OUT);

system("./scripts/autoTransformTemplate.pl website/getRscript.pl.template");


if( defined($params{'-v'}) && $params{'-v'} == 1 ){ 
	print "\nChanges:\n--------\n";
	if( defined( $params{'-p'} ) && $params{'-p'} != 1 ){print @pathOut; print "\n"};
	print @paramOut;
	print @urlOut;
	print @dbOut;
}
