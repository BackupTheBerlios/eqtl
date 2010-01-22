#!/bin/sh -e

. <<EOPOD

=head1 NAME

gitignoreProduce.sh - adds files to local .gitignore file

=head1 SYNOPSIS

From root of git checkout, execute with no arguments

=head1 OPTIONS

none

=head1 AUTHOR

Steffen ME<ouml>ller  <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2010

EOPOD

if [ ! -r .gitignore ]; then
	echo "There is no .gitignore file in the cwd. If you truly mean to execute this script, then first 'touch .gitignore'."
	exit
fi

(cat .gitignore ; find . -name "*.template" | sed -e 's/.template$//' ) > /tmp/gitignore_$$
mv /tmp/gitignore_$$ .gitignore
