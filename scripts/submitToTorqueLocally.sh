#!/bin/bash

set -e

showHelp=""

TIMEOUT=$1
if [ -z "$TIMEOUT" ]; then
	TIMEOUT=6
fi

NUMBEROFJOBS=$2
if [ -z "$NUMBEROFJOBS" ]; then
	NUMBEROFJOBS=1
fi

RSCRIPTTOEXECUTE=${RSCRIPTTOEXECUTE:-evaluateQuery.R}
NODES=${NODES:-desk}
NAME=${NAME:-default}

if [ -z "$1" -o "-h" = "$1" -o "--help" = "$1" ]; then
	showHelp="yes"
fi

if [ -n "$showHelp" ]; then

: <<=cut

=head1 NAME

submitToTorqueLocally.sh - script to submit expression QTL analyses to a local queueing system

=head1 SYNOPSIS

NAME="some identifier" NODES="ANY" submitToTorqueLocally.sh <duration> <number>

=head1 DESCRIPTION

The default is to submit 1 job, otherwise the second argument
to the script should specify the number of jobs. The number of
jobs is not identical to the nubmer of workunits that are going
to be calculated. There are multiple workunits per job.

The duration is 4 hours (only time unit).  As many jobs as
possible will be created in that time. The cputime of the job
will be set to the duration specified this way.

=head1 ENVIRONMENT

=over 4

=item RSCRIPTTOEXECUTE

script to be executed by R in the queue

=item NODES

the name of the nodes to submit to

=item NAME

identifier to appear as eQTL_${NAME}_$jobnumber

=item CALCULATIONS

number of Scanone or Scantwo calculations to perform per submitted
queue job

=back

=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2008-2009

=cut
        pod2man $0 | nroff -man | less
        exit
fi

if [ ! -r "$RSCRIPTTOEXECUTE" ]; then
	echo "Could not find R script to execute at '$RSCRIPTTOEXECUTE'."
	exit -1
fi

for i in `seq 1 $NUMBEROFJOBS`
do
	cmd="R CMD BATCH $RSCRIPTTOEXECUTE"
	echo "Submitting job: $cmd"
	intern_nodesspec=",nodes=$NODES"
	if [ -z "$NODES" -o "any" = "$NODES" -o "all" = "$NODES" -o "ALL" = "$NODES" -o "ANY" = "$NODES" ]; then
		intern_nodesspec=""
	fi
	cat <<EOQSUB | qsub
#PBS -r y
#PBS -N eQTL_${NAME}_$i
#PBS -l cput=$TIMEOUT:59:00$intern_nodesspec
export TIMEOUT=$TIMEOUT
export JOBNOMAX=$CALCULATIONS
R CMD BATCH `pwd`/$RSCRIPTTOEXECUTE
EOQSUB
done



