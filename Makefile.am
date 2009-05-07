
lib_LTLIBRARIES = libeqtlarc.la
INCLUDES = @LIBXML_CFLAGS@  
LIBADD = @LIBXML_LIBS@ 
libeqtlarc_la_SOURCES = eqtlservice.cpp
libeqtlarc_la_LDFLAGS = -module -no-undefined
libeqtlarc_la_LIBADD = -lmysqlpp -larccommon -larcmessage -larcloader
mydatadir = $(datadir)/doc/libarc1-eqtl
mydata_DATA = taverna2-test.t2flow wsdl-viewer.xsl arched_eqtl.xml eqtl_arc.wsdl sample-request.xml

