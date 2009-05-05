#!/bin/bash

if [ "-h" = "$1" -o "--help" = "$1" ]; then
	cat <<EOHELP

NAME
	update.sh - update cloned git repository with origin

SYNOPSIS

	update.sh [--no-pull|-np] <projectname>

DESCRIPTION

	Execute this script to update your local installation with the
	latest that is on the Git repository. The script pulls with
	default settings from origin and subsequently performs all
	substitutions for the templates.

OPTIONS

	--no-pull|-np	Don't execute 'git pull', only transform the templates.

	--quiet|-q	Be quiet.

AUTHORS

	Steffen Moeller <moeller@inb.uni-luebeck.de>
	Jan Kolbaum
	Ann-Kristin Grimm
	Benedikt Bauer

	University of Luebeck, 2008-2009

EOHELP
	exit 1
fi

set -e
redirect=""
nopull=""
projectname=""

for i in $*
do
	#echo "i=$i"
	if [ "--no-pull" == "$i" -o "-np" == "$i" ]; then
		nopull="true"
	elif [ "--quiet" == "$i" -o "-q" == "$i" ]; then
		redirect=">& /dev/null"
	elif [ -z "$projectname" ]; then
		projectname="$i"
	else
		echo "Attempt to set project name twice (was '$projectname', now '$i'.\n"
		exit -1
	fi
done

if [ -z "$nopull" ]; then
	git pull
fi

#TODO:
#modify that the script accepts all projectnames
internal_projectname=""
case "$projectname" in
	"mus"|"mouse")
		internal_projectname="mus"
	;;
	"rat"|"rattus")
		internal_projectname="rat"
	;;
	*) 
		if [ -n "$projectname" ]; then
			echo "Unknown projectname: $projectname"
			exit -1;
		else
			echo "Projectname not set. Use mus or mouse for mouse and rat or rattus for rat"
			exit -1;
		fi
	;;
esac


if [ -f "conf_$internal_projectname/path.conf" ] && [ -f "conf_$internal_projectname/param.conf" ] && [ "$1" != "-r" ]; then
	echo "    Manually rerun './install.pl $internal_projectname' to  update your configuration if necessary! or update your conf files"
else
	#./install.pl $projectname $redirect
fi

if [ -x scripts/autoTransformTemplate.pl ]; then
	echo "Now auto-transforming templates."
	eval ./scripts/autoTransformTemplate.pl --projectname $internal_projectname website/*.template scripts/*.template website/eqtl/*.template *.template $redirect
fi
