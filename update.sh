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

	The configuration files are expected in a folder 'conf' or,
	if this is not present or the projectname is specified, then in
	conf_<projectname>.

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
	if ! git pull; then
		echo "Could not properly pull from the archive - exiting. Call with '--no-pull' to circumvent the problem."
		exit -1
	fi
fi

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
			internal_projectname=$projectname
		elif [ -d "conf" -o -L "conf" ]; then
			internal_projectname=""
		else
			echo "Projectname not set. The Projectname must correspond with the config folder name: config_projectname"
			exit -1;
		fi
	;;
esac

configuration_directory="conf_$internal_projectname"

if [ -d conf -o -L conf ] && [ -z "$internal_projectname" ]
then
	configuration_directory="conf"
elif [ ! -d "$configuration_directory" ]
then
	echo -n "Configuration directoriy '$configuration_directory' does not exist. "
	echo    "Please check if you used the right projectname"
	exit -1
fi


if [ -x install.pl -a -f "$configuration_directory/path.conf" -a -f "$configuration_directory/param.conf" ]
then
	echo "    Manually rerun './install.pl $internal_projectname' to  update your configuration if necessary! or update your conf files"
elif [ -x install.pl ]; then
	./install.pl $projectname $redirect
fi


AUTOTRANSFORMSCRIPT="scripts/programming/autoTransformTemplate.pl" 
if [ -x "$AUTOTRANSFORMSCRIPT" ]; then
	echo "Now auto-transforming templates."
	if [ -n "$internal_projectname" ]; then
		eval "$AUTOTRANSFORMSCRIPT" --projectname $internal_projectname `find . -name "*.template" | grep -v "^./conf"` $redirect
	else 
		eval "$AUTOTRANSFORMSCRIPT" `find . -name "*.template" | grep -v "^./conf"` $redirect
	fi
else
	echo "Could not find script to transform templates."
fi

