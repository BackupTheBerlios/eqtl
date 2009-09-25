#!/bin/sh

pathToHere=$(dirname $0)

echo "Arguments are: $*"

if [ ! -x $pathToHere/../../scripts/analyses/scatter_all.R ]; then
	echo "Could not find Script '$pathToHere/../../scripts/analyses/scatter_all.R' expected from `pwd`."
else
	echo "Invocation of scatter_all.R:"
	echo "$pathToHere/../../scripts/analyses/scatter_all.R $*"
	$pathToHere/../../scripts/analyses/scatter_all.R $*
	echo "Invocation completed."
fi
