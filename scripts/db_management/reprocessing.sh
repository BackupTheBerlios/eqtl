#!/bin/sh

operation=`basename $0|sed -e 's/.sh$//'|tr "a-z" "A-Z"`

if [ "REPROCESSING" != "$operation" -a "PROCESSING" != "$operation" -a "CALCULATE" != "$operation" -a "RECALCULATE" != "$operation" ]
then
	echo "Unknown operation: $operation"
	exit -1
fi

if [ "-h" = "$1" -o "--help" = "$1" ]; then

: <<=cut

=head1 NAME

calculate.sh - resets a job to calculate state
recalculate.sh - resets a job to recalculate state
processing.sh - resets a job to processing state
reprocessing.sh - resets a job to reprocessing state

=head1 SYNOPSIS

`basename $0` <jobnames, expected to be file- or directory names>

=head1 DESCRIPTION

Whenever an upload to the database of a particular file was considered
to be problematic, it is not unlikely that the computation of that file should
be reperformed or some sort. This script presents the SQL to perform
an update of the status to '$operation'.

These scripts cannot create new entries to the computation table, but
change the instructions on how to deal with the data.

=head1 EXAMPLE

`basename $0` . | mysql -h eqtl.org

=head1 SEE ALSO

uploadExpectedFiles.pl to fill the 'computation' table.

=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2009

=cut
	pod2man $0 | nroff -man | less
	exit 1;
fi

function doit {
	for f in $*
	do
		probesetid=`zcat $f | head -n 1 | cut -f1 -d\) | cut -f2 -d\(`
		if [ -z "$probesetid" ]; then
			echo '# Skippted because of format: $f'
			continue;
		fi
		echo "update computation set status='$operation' where jobname='`basename $f`';"
	done
}

for i in $*
do
	if [ -d "$i" ]; then
		doit `find "$i" -name "*.csv.gz"`
	else
		if ! echo "$i" | egrep -q '.csv.gz$'; then
			echo "# Skipped since filename did not match *.csv.gz"
			continue
		else
			doit "$i"
		fi

	fi
done

