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
source code and prepares a manual.

