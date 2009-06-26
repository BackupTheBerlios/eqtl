#!/usr/bin/make -f

# This Makefile prepares a single file that can be distributed to serve
# as a documentation file for the expression QTL infrastructure at large.

# Perl scripts
#	all perl scripts are supposed to offer a description of themselves
#	in the form prescribed by POD.

PERLSCRIPTS=$(shell find .. -name "*.pl" )
PHPSCRIPTS=$(shell find .. -name "*.php" | grep -v 'website/index.php' )
SHELLSCRIPTS=$(shell find .. -name "*.sh" | sort | egrep -v 'website/.*update.sh' | egrep -v 'scripts/.*update.sh' | egrep -v 'doc/.*update.sh' )

.SUFFIXES: .pl .ps

%.ps: %.pl
	test -x /usr/bin/groff
	test -x /usr/bin/pod2man
	pod2man $< |groff -man > $@

%.ps: %.sh
	test -x /usr/bin/a2ps
	test -x /usr/bin/groff
	test -x /usr/bin/pod2man
	if grep -q -- '=head1' $<; then \
		pod2man $< |groff -man > $@ ; \
	elif grep -- --help $<; then \
		bash $< --help | a2ps -o $@ --center-title $< ; \
	else \
		echo "No help available for $<" | a2ps -o $@ --center-title $< ; \
	fi

%.ps: %.php
	test -x /usr/bin/a2ps
	test -x /usr/bin/groff
	test -x /usr/bin/pod2man
	if grep -q '=head1' $<; then \
		pod2man $< |groff -man > $@ ; \
	elif grep -q STARTOFDOCUMENT $< | grep -q ENDOFDOCUMENT; then \
		cat $< | sed -e '1,/STARTOFDOCUMENTATION/d' -e '/ENDOFDOCUMENTATION/,$$d' | a2ps -o $@ --center-title $<; \
	else \
		echo "No help available for $<" | a2ps -o $@ --center-title $< ; \
	fi 

documentation: data_preparation_scripts.ps webinterface_scripts.ps

data_preparation_scripts.ps: perl shell $(wildcard tmp/shell/*.ps tmp/perl/*.ps)
	psmerge -odata_preparation_scripts.ps $(wildcard tmp/shell/*.ps) $(wildcard tmp/perl*.ps)

webinterface_scripts.ps: php $(wildcard tmp/php/*.ps)
	psmerge -owebinterface_scripts.ps tmp/php/*.ps

perl:	$(PERLSCRIPTS:.pl=.ps)
	if [ ! -d tmp/perl ]; then mkdir -p tmp/perl; fi
	mv $(PERLSCRIPTS:.pl=.ps) tmp/perl

shell: $(SHELLSCRIPTS:.sh=.ps)
	if [ ! -d tmp/shell ]; then mkdir -p tmp/shell; fi
	mv $(SHELLSCRIPTS:.sh=.ps) tmp/shell

php: $(PHPSCRIPTS:.php=.ps)
	if [ ! -d tmp/php ]; then mkdir -p tmp/php; fi
	mv $(PHPSCRIPTS:.php=.ps) tmp/php

clean:
	rm -rf tmp supporting_scripts.ps webinterface_scripts.ps

.PHONY: clean documentation perl shell php
