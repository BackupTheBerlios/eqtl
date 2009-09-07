#!/usr/bin/make -f

# This Makefile prepares a single file that can be distributed to serve
# as a documentation file for the expression QTL infrastructure at large.

# Perl scripts
#	all perl scripts are supposed to offer a description of themselves
#	in the form prescribed by POD.

PERLSCRIPTS=$(shell find .. -name "*.pl" )
PHPSCRIPTS=$(shell find .. -name "*.php" | grep -v 'website/index.php' | grep -v 'func_species.php' | grep -v 'help.php' | grep -v 'ensemblconf' )
SHELLSCRIPTS=$(shell find .. -name "*.sh" -a ! -type l | sort | egrep -v 'website/.*update.sh' | egrep -v 'scripts/.*update.sh' | egrep -v 'doc/.*update.sh' | grep -v 'ltmain.sh' )

POD2PDF=pod2pdf --icon logo-knowarc.png
POD2MAN = pod2man --center="expression QTL infrastructure" 

TMPDIR=tmp

.SUFFIXES: .pl .ps

#%.ps: %.pl
#	test -x /usr/bin/groff
#	test -x /usr/bin/pod2man
#	$(POD2MAN) $< |groff -man > $@

%.pdf: %.pl
	test -x /usr/bin/pod2pdf
	$(POD2PDF) $< > $@

#%.ps: %.sh
#	if grep -q -- '=head1' $<; then \
#		test -x /usr/bin/pod2pdf ; \
#		$(POD2MAN) $< |groff -man > $@ ; \
#	elif grep -- --help $<; then \
#		test -x /usr/bin/a2ps ; \
#		bash $< --help | a2ps -o $@ --center-title $< ; \
#	else \
#		echo "No help available for $<" | a2ps -o $@ --center-title $< ; \
#	fi

%.pdf: %.sh
	if grep -q -- '=head1' $<; then \
		test -x /usr/bin/pod2pdf ; \
		$(POD2PDF) $< > $@ ; \
	elif grep -- --help $<; then \
		test -x /usr/bin/a2ps ; \
		bash $< --help | a2ps -o $@ --center-title $< ; \
	else \
		echo "No help available for $<" | a2ps -o $@ --center-title $< ; \
	fi

#%.ps: %.php
#	if grep -q '=head1' $<; then \
#		test -x /usr/bin/groff ; \
#		test -x /usr/bin/pod2man ; \
#		$(POD2MAN) $< |groff -man > $@ ; \
#	elif grep -q STARTOFDOCUMENT $< | grep -q ENDOFDOCUMENT; then \
#		test -x /usr/bin/a2ps ; \
#		cat $< | sed -e '1,/STARTOFDOCUMENTATION/d' -e '/ENDOFDOCUMENTATION/,$$d' | a2ps -o $@ --center-title $<; \
#	else \
#		echo "No help available for $<" | a2ps -o $@ --center-title $< ; \
#	fi 

%.pdf: %.php
	if grep -q '=head1' $<; then \
		test -x /usr/bin/pod2pdf ; \
		$(POD2PDF) $< > $@ ; \
	elif grep -q STARTOFDOCUMENT $< | grep -q ENDOFDOCUMENT; then \
		test -x /usr/bin/a2ps ; \
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

data_preparation_scripts.pdf: perl shell $(wildcard $(TMPDIR)/shell/*.pdf $(TMPDIR)/perl/*.pdf)
	test -x /usr/bin/pdftk
	pdftk $(TMPDIR)/shell/*.pdf $(TMPDIR)/perl/*.pdf cat output $@

#data_preparation_scripts.ps: perl shell $(wildcard $(TMPDIR)/shell/*.ps $(TMPDIR)/perl/*.ps)
#	psmerge -odata_preparation_scripts.ps $(TMPDIR)/shell/*.ps $(TMPDIR)/perl*.ps

webpages_phpscripts.pdf: php $(wildcard $(TMPDIR)/php/*.ps)
	test -x /usr/bin/pdftk
	pdftk $(TMPDIR)/php/*.pdf cat output $@

#webpages_phpscripts.ps: php $(wildcard $(TMPDIR)/php/*.ps)
#	psmerge -owebpages_phpscripts.ps $(TMPDIR)/php/*.ps

data_handling.pdf: data_handling.pod logo-knowarc.png
	test -x /usr/bin/pod2pdf 
	$(POD2PDF) --title "Job Submission and Data Handling" --output-file $@ $<

general_information.pdf: general_information.pod logo-knowarc.png
	test -x /usr/bin/pod2pdf 
	$(POD2PDF) logo-knowarc.png --title "Expression QTL Infrastructure" -footer-text "Introduction to concepts and source code" --output-file $@ $<

#perl:	$(PERLSCRIPTS:.pl=.ps)
#	if [ ! -d $(TMPDIR)/perl ]; then mkdir -p $(TMPDIR)/perl; fi
#	mv $(PERLSCRIPTS:.pl=.ps) $(TMPDIR)/perl

perl:	$(PERLSCRIPTS:.pl=.pdf)
	if [ ! -d $(TMPDIR)/perl ]; then mkdir -p $(TMPDIR)/perl; fi
	mv $(PERLSCRIPTS:.pl=.pdf) $(TMPDIR)/perl

#shell: $(SHELLSCRIPTS:.sh=.ps)
#	if [ ! -d $(TMPDIR)/shell ]; then mkdir -p $(TMPDIR)/shell; fi
#	mv $(SHELLSCRIPTS:.sh=.ps) $(TMPDIR)/shell

shell: $(SHELLSCRIPTS:.sh=.pdf)
	if [ ! -d $(TMPDIR)/shell ]; then mkdir -p $(TMPDIR)/shell; fi
	mv $(SHELLSCRIPTS:.sh=.pdf) $(TMPDIR)/shell

#php: $(PHPSCRIPTS:.php=.ps)
#	if [ ! -d $(TMPDIR)/php ]; then mkdir -p $(TMPDIR)/php; fi
#	mv $(PHPSCRIPTS:.php=.ps) $(TMPDIR)/php

php: $(PHPSCRIPTS:.php=.pdf)
	if [ ! -d $(TMPDIR)/php ]; then mkdir -p $(TMPDIR)/php; fi
	mv $(PHPSCRIPTS:.php=.pdf) $(TMPDIR)/php

clean:
	rm -rf $(TMPDIR) supporting_scripts.ps webpages_phpscripts.ps general_information.ps

.PHONY: clean documentation perl shell php
