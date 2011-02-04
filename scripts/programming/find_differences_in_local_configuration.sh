#!/bin/bash

# This script compares the configuration parameters
# in conf/* and compares them with the settings in 
# cont_template/*. No arguments are expected.

confdir="conf"
templatedir="conf_template"

if [ -d "$confdir" -o -l "$confdir" ]; then
	echo "The script expects a configuration directory to be present as \"./conf\"."
	exit
fi

if [ -d "$templatedir" -o -l "$templatedir" ]; then
	echo "The script expects a configuration template directory to be present as \"./conf_template\"."
	exit
fi

templatefile=/tmp/eqtl.tmp.template.tmp
conffile=/tmp/eqtl.tmp.conf.tmp
# find configuration in template
cut -f1 conf_template/*.conf | grep -v "^#" | sort -u > $templatefile
cut -f1 conf/*.conf | grep -v "^#" | sort -u > $conffile

echo "The following configuration parameters are new:"
for i in $(join -v 1 $templatefile $conffile) ; do
	echo "	$i (" $(grep "^$i" $templatedir/*.conf) ")"
done

echo "The following configuration parameters seems to be no longer used:"
for i in $(join -v 2 $templatefile $conffile) ; do
	echo "	$i (" $(grep "^$i" $confdir/*.conf) ")"
done

rm $templatefile $conffile
