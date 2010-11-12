Generation of Documentation
===========================

All executable scripts are presenting usage information
about their purpose and usage upon their invocation
with -h or --help.

The documentation is prepared in the perl documentation
format "POD". The only exception are PHP scripts.
Shell scripts are invoking pod2man themselves to present
the help.

The doc subdirectory collects the information from the
source code and prepares the following manuals:

 * general_information.pdf - an overview of the many components
                             of the eQTL database.
                           
 * webpages_phpscripts.pdf - man pages of several dynamically prepared web pages
                             that present the content of the databases

 * data_preparation_scripts.pdf - man pages of scripts that
                             contribute to performing the computation
                             or that fill the database

 * data_handling.pdf       - details on how to prepare and run jobs,
                             collection of results,
                             upload to database


To produce those PDF documents, it is required to executed the
Makefile by invoking "make" at the command line. This requires
the Debian packages pod2man and pdftk to be installed, which
may or may not be available for your distribution.
<<<<<<< HEAD
=======

>>>>>>> 15a14e952511058ab885859f6fcc0f886c292c90
