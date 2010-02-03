#!/bin/sh -e

. <<EOPOD

=head1 NAME

gitignoreProduce.sh - adds files to local .gitignore file

=head1 SYNOPSIS

From root of git checkout, execute with no arguments.

=head1 DESCRIPTION

Many files of this directory are generated automatically.
But others with the same suffix are not. The 'git status'
command becomes too long to become easily read, to update
the .gitignore file seems mandatory. This script helps
this task.

=head1 OPTIONS

none

=head1 AUTHOR

Steffen ME<ouml>ller  <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, 2010

=cut

EOPOD


if [ "-h" = "$1" -o "--help" = "$1" -o "-man" = "$1" -o "-help" = "$1" ]; then
	if which pod2man > /dev/null; then
		pod2man $0 | nroff -man | less
	else
		echo "Please install pod2man to the proper display of the online help."
	fi
	exit
fi

if [ ! -r .gitignore ]; then
	echo "There is no .gitignore file in the cwd. If you truly mean to execute this script, then first 'touch .gitignore'."
	exit
fi

(cat .gitignore ; find . -name "*.template" | sed -e 's/.template$//' ) > /tmp/gitignore_$$
mv /tmp/gitignore_$$ .gitignore
