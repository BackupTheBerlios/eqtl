README - Infrastructure for the analysis of expression QTL
==========================================================

Concept
=======

Organisation of the source code
-------------------------------

The source code is mostly dynamic web pages. The visual experience of
the data goes hand in hand with the source code and consequently it is
straight forward for newbies to extend the existing code base. We hoped
with the selection of git as the source code management system to stimulate
many collaborations between different sites and this eQTL Berlios project
pages to be come a central place for the exchange of individual progress.

The source tree is presentable via an Apache web server directly. There
is no compilation required. Consequently no installer is required. And
the system as a whole is easily portable across sites and platforms.

This directory holds all the sources to prepare for the analysis of expression QTL.

  conf_template - Template files for the configuration of a local project
  doc           - Excerpts on selected use cases for interacting with the system.
  scripts       - Commands of general applicability
  website       - Web interface to the generated data and data to be distributed
                  to remotely executed jobs.
  website/eqtl  - PHP scripts to present the results.

Every folder has a README file like this one to lay out the basic ideas behind what
it implemented.


Sharing PHP, Perl and HTML files between projects
-------------------------------------------------

All files underneath scripts and website should be applicable to every
expression QTL experiment. Or, conversely, modifications to improve
the analysis of one project should also be for the benefit of all other
projects. That concept can only work when the parts at which the projects differ
are somehow separated out.

The solution presented by this project was to have mere string
substitutions. A file ending with ".template" is expected to offer
keywords. Those keywords are defined in the files of the conf folder.
Separated by a tab sign, the left side defines a keyword and the right
defines a string that it shall be substituted with. When exchanging
projects, only the conf folder should be changed.


Sharing a database schema
-------------------------

Except for the covariates that are defined by the available clinical
data, all experiments shall have the same schema. Further exceptions
are in the trait table, the performs the linking from the probe ID of
the expression level towards a database entry in public databases and
presents further information, i.e. the chromosomal location of the gene.


Web interface
-------------

There are two kinds of web interfaces. The one in website/* performs the
distribution of data to remotely executed jobs. It is implemented with FastCGI
and Perl scripts.

The second web interface presents data to biologists. It is written in
PHP.


Scripts
-------

For the distribution of data, the collection of results, the upload to
the database or maybe even just for some curious analyses, a series of
scripts has been prepared. These are either Perl or BASH scripts.


Key tools
=========

All tools should allow to retrieve documentation by passing "--help" as an argument.
Here in the root of the source tree, there is only one key tool to be mentioned, this is:


update.sh
---------

Update.sh gets all template variables from conf files and regenerates all
perl files from their template. The conf files from the conf_template need 
to be adopted to your project and need to be in a new folder called
conf_yourprojectname. Update.sh needs to be called with your projectname as
one Parameter. It will be one of the first commands to
execute after having skimmed through the files in the doc folder. When
invoked, it will execute "git pull" and afterwards apply all the template
substitutions according to the settings in your config folder. The
folloing parameters may be of interest:

  --no-pull | -np   - skip invocation of git pull
  --quiet | -q      - minimize verbosity

