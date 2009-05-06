conf_templates
==============

This folder conf contains files for the configuration of a local
project.  Any project-specific challenges should be abstracted away
into a configuration file, thus that the remainder of the source code
can be shared.

Filename              | Specifies
----------------------+----------------------------------------------------------
data.conf             | location of data
db.conf               | specification how to access databases
footer.conf           | presentation of images at bottom of page
footer.conf           | presentation of images at top of page
general.conf          | name and title of the current project, presentation on
                      | project startup page
intro.conf            | relative URL for PHP pages
layout.conf           | stylesheets
local.conf.template   | parts in which every installation is different, not only
                      | every project (e.g. path of installation)
logo.conf             | paths to logos
param.conf            | parameters used by getRscript.pl to create executable R-scripts
path.conf             | relative path names to put / get result files et al.
programming.conf      | the "don't edit this file" warning
recalc.conf           | key parameters for the recalc.pl script.
statistics.conf       | list of covariates that are currently in the system
