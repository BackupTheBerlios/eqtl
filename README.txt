This directory holds all the sources to prepare
for the analysis of expression QTL.

conf_template        template files for the configuration of a local project
scripts     commands of general applicability
website     Web interface to the generated data


install.pl
==========
Is removed at the moment because the information in the file is specific for a certain project
Will be added as editable template late
Is removed at the moment because the information in the file is specific for a certain project
Will be added as editable template later
Is used to set up both path.conf and param.conf in conf folder.
Can be called on its own but will be called by update.sh if:
(a) conf/path.conf | conf/param.conf is|are missing
(b) update.sh is called using "-r" parameter to indicate refresh of all data is requested

If param.conf is updated by install.pl getRscript.pl will be regenerated from getRscript.pl.template

Parameter:
----------
-v = verbose
-s = set params manually 		IO via console
-p = set params for R-scripts only	can be used in cooperation with -s or direct input to specify new parameters or alone to set parameters to defaults

It is possible to enter script-parameters directly:
[ steps=X | draws=X | errorP=X | threshold=X | perms=X | alpha=X | epsilon=X ]
script-parameters or their abbreviations should be self-explanatory.



update.sh
=========
Is used to get all template variables from conf files and regenerate all perl files from their template.

Parameter:
----------
-r = refresh		forces to update all .conf files created by install.pl
