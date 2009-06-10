#ifndef __EQTLSERVICE_H__
#define __EQTLSERVICE_H__

#if HAVE_CONFIG_H
	#include <config.h>
#endif

#include <arc/message/Service.h>
#include <arc/Logger.h>

#define MYSQLPP_MYSQL_HEADERS_BURIED 1
#include <mysql++/mysql++.h>

namespace ArcService
{

class ExpressionQtlService: public Arc::Service
{

protected:
	Arc::Logger logger;
	Arc::NS ns_;
	std::string database, server, user, password;
	int port;
	Glib::Mutex r_single_thread;
	
	/**
	 * Method to return an error. 
	 * Creates a fault message and returns a status.
	 * @param outmsg outgoing message
	 * @return always Arc::MCC_Status(Arc::STATUS_OK)
	 */
	Arc::MCC_Status makeFault(Arc::Message& outmsg, const std::string &reason);
	
	/**
	 * Do as the name says and load results for a request from mysql database. 
	 * @param requestNode the XML request node
	 * @param queryResults this will be filled with fresh mysql data
	 * @param outmsg the output arc message, in case we need to throw errors
	 * @return return value of makeFault if bad things happen, Arc::MCC_Status(Arc::STATUS_OK) otherwise
	 */
	Arc::MCC_Status fetchMysqlResultForRequest(Arc::XMLNode requestNode, mysqlpp::StoreQueryResult* queryResults, Arc::Message& outmsg);
	
	
public:
	
	/**
	 * Constructor which is capable to extract prefix and suffix
	 * for the echo service.
	 */
	ExpressionQtlService(Arc::Config *cfg);
	
	/**
	 * Destructor.
	 */
	virtual ~ExpressionQtlService(void);
	
	/**
	 * Implementation of the virtual method defined in MCCInterface
	 * (to be found in MCC.h). 
	 * @param inmsg incoming message
	 * @param inmsg outgoing message
	 * @return Status of the result achieved
	 */
	virtual Arc::MCC_Status process(Arc::Message& inmsg,Arc::Message& outmsg);
}; 

} //namespace ArcService

#endif 
