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

 * GeneralInformation.ps - an overview of the many components
                           of the eQTL database.
                           
 * eqtl-php-interface.ps - a description of the purpose
                           of several dynamically prepared web pages
                           that present the content of the databases

 * eqtl-scripts.ps       - a description of several scripts that
                           contribute by performing the computation
                           or to fill the database

                      

