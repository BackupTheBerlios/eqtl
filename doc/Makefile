#!/usr/bin/make -f

# This Makefile prepares a single file that can be distributed to serve
# as a documentation file for the expression QTL infrastructure at large.

# Perl scripts
#	all perl scripts are supposed to offer a description of themselves
#	in the form prescribed by POD.

PERLSCRIPTS=$(shell find .. -name "*.pl" )
PHPSCRIPTS=$(shell find .. -name "*.php" | grep -v 'website/index.php' | grep -v 'func_species.php' | grep -v 'help.php' | grep -v 'ensemblconf' )
SHELLSCRIPTS=$(shell find .. -name "*.sh" | sort | egrep -v 'website/.*update.sh' | egrep -v 'scripts/.*update.sh' | egrep -v 'doc/.*update.sh' | grep -v 'ltmain.sh' )

POD2MAN = pod2man --center="expression QTL infrastructure" 

TMPDIR=tmp

.SUFFIXES: .pl .ps

%.ps: %.pl
	test -x /usr/bin/groff
	test -x /usr/bin/pod2man
	$(POD2MAN) $< |groff -man > $@

%.ps: %.sh
	test -x /usr/bin/a2ps
	test -x /usr/bin/groff
	test -x /usr/bin/pod2man
	if grep -q -- '=head1' $<; then \
		$(POD2MAN) $< |groff -man > $@ ; \
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
		$(POD2MAN) $< |groff -man > $@ ; \
	elif grep -q STARTOFDOCUMENT $< | grep -q ENDOFDOCUMENT; then \
		cat $< | sed -e '1,/STARTOFDOCUMENTATION/d' -e '/ENDOFDOCUMENTATION/,$$d' | a2ps -o $@ --center-title $<; \
	else \
		echo "No help available for $<" | a2ps -o $@ --center-title $< ; \
	fi 

%.pdf: %.ps
	test -x /usr/bin/ps2pdf
	ps2pdf $<

documentation: general_information.pdf data_handling.pdf data_preparation_scripts.pdf webpages_phpscripts.pdf 

logo-knowarc.png: 
	wget http://www.knowarc.eu/styles/logo-knowarc.png

data_preparation_scripts.ps: perl shell $(wildcard $(TMPDIR)/shell/*.ps $(TMPDIR)/perl/*.ps)
	psmerge -odata_preparation_scripts.ps $(wildcard $(TMPDIR)/shell/*.ps) $(wildcard $(TMPDIR)/perl*.ps)

webpages_phpscripts.ps: php $(wildcard $(TMPDIR)/php/*.ps)
	psmerge -owebpages_phpscripts.ps $(TMPDIR)/php/*.ps

data_handling.pdf: data_handling.pod logo-knowarc.png
	test -x /usr/bin/pod2pdf 
	pod2pdf --icon logo-knowarc.png --title "Job Submission and Data Handling" --output-file $@ $<

general_information.pdf: general_information.pod
	test -x /usr/bin/pod2pdf 
	pod2pdf --icon logo-knowarc.png --title "Expression QTL Infrastructure" -footer-text "Introduction to concepts and source code" --output-file $@ $<

perl:	$(PERLSCRIPTS:.pl=.ps)
	if [ ! -d $(TMPDIR)/perl ]; then mkdir -p $(TMPDIR)/perl; fi
	mv $(PERLSCRIPTS:.pl=.ps) $(TMPDIR)/perl

shell: $(SHELLSCRIPTS:.sh=.ps)
	if [ ! -d $(TMPDIR)/shell ]; then mkdir -p $(TMPDIR)/shell; fi
	mv $(SHELLSCRIPTS:.sh=.ps) $(TMPDIR)/shell

php: $(PHPSCRIPTS:.php=.ps)
	if [ ! -d $(TMPDIR)/php ]; then mkdir -p $(TMPDIR)/php; fi
	mv $(PHPSCRIPTS:.php=.ps) $(TMPDIR)/php

clean:
	rm -rf $(TMPDIR) supporting_scripts.ps webpages_phpscripts.ps general_information.ps

.PHONY: clean documentation perl shell php
