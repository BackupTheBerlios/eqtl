#!/usr/bin/make -f

default:
	@echo "Possible targets are:"
	@echo "  clean	- remove all generated files"
	@echo "  update - execute update.sh to generate files" 

update:
	update -np -q

clean:
	a=`find . -name "*.template"` ; \
	for i in $$a; do \
		ii=`echo $$i|sed -e 's/.template$$//'` ; \
		if [ `basename $$ii` == "local.conf" ]; then \
			continue ; \
		fi ; \
		echo removing $$ii ; \
		rm $$ii
	done

count:
	wc -l `find scripts \( -name "*.pl" -o -name "*.R" -o -name "*.php" -o -name "*.sh" \) -a \! -type l` `find website \( -name "*.pl" -o -name "*.R" -o -name "*.php" -o -name "*.sh" \) -a \! -type l`

.PHONY: udpate clean count
