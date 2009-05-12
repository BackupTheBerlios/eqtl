
lib_LTLIBRARIES = libeqtlarc.la
INCLUDES = -I/usr/local/lib/R/include @LIBXML_CFLAGS@
LIBADD = @LIBXML_LIBS@ 
libeqtlarc_la_SOURCES = cencode.c eqtlservice.cpp
libeqtlarc_la_LDFLAGS = -module -no-undefined
libeqtlarc_la_LIBADD = -lR -larccommon -larcloader -larcmessage -lmysqlpp
mydatadir = $(datadir)/doc/libarc1-eqtl
mydata_DATA = taverna2-test.t2flow wsdl-viewer.xsl arched_eqtl.xml eqtl_arc.wsdl sample-request.xml

noinst_HEADERS = cencode.h
