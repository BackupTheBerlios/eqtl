#!/usr/bin/make -f

# This Makefile prepares a single file that can be distributed to serve
# as a documentation file for the expression QTL infrastructure at large.

# Perl scripts
#	all perl scripts are supposed to offer a description of themselves
#	in the form prescribed by POD.

PERLSCRIPTS=$(shell find .. -name "*.pl" )
PHPSCRIPTS=$(shell find .. -name "*.php" )
SHELLSCRIPTS=$(shell find .. -name "*.sh" | sort | egrep -v 'website/.*update.sh' | egrep -v 'scripts/.*update.sh' | egrep -v 'doc/.*update.sh')

.SUFFIXES: .pl .ps

%.ps: %.pl
	test -x /usr/bin/groff
	test -x /usr/bin/pod2man
	pod2man $< |groff -man > $@

%.ps: %.sh
	test -x /usr/bin/a2ps
	( if grep -- --help $<; then \
		$< --help;  \
	else \
		echo "No help available for $<"; \
	fi ) | a2ps -o $@ --center-title $<

%.ps: %.php
	test -x /usr/bin/a2ps
	(if grep -q STARTOFDOCUMENT $< | grep -q ENDOFDOCUMENT; then \
		cat $< | sed -e '1,/STARTOFDOCUMENTATION/d' -e '/ENDOFDOCUMENTATION/,$$d' ; \
	fi) | a2ps -o $@ --center-title $<

documentation.ps: shell perl
	psmerge -o documentation.ps tmp/*.ps

perl:	$(PERLSCRIPTS:.pl=.ps)
	if [ ! -d tmp ]; then mkdir tmp; fi
	mv $(PERLSCRIPTS:.pl=.ps) tmp/

shell: $(SHELLSCRIPTS:.sh=.ps)
	if [ ! -d tmp ]; then mkdir tmp; fi
	mv $(SHELLSCRIPTS:.sh=.ps) tmp/

php: $(PHPSCRIPTS:.php=.ps)
	if [ ! -d tmp ]; then mkdir tmp; fi
	mv $(PHPSCRIPTS:.php=.ps) tmp/

clean:
	rm -rf tmp scripts.ps

.PHONY: perl clean
