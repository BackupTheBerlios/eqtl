#!/bin/bash
set -e
if [ -x ../update.sh ]; then
	cd ..
	./update.sh $*
else
	echo "Could not find ../update.sh to execute"
fi
