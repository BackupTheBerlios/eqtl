#include <arc/loader/Plugin.h>
#include <arc/message/PayloadSOAP.h>

#include <stdio.h>

#include "eqtlservice.h"


/**
 * Initializes the expression qtl service and returns it.
 */
static Arc::Plugin* get_service(Arc::PluginArgument* arg)
{
	Arc::ServicePluginArgument* mccarg = dynamic_cast<Arc::ServicePluginArgument*>(arg);
	if(!mccarg) return NULL;
	return new ArcService::ExpressionQtlService((Arc::Config*)(*mccarg));
}

/**
 * This PLUGINS_TABLE_NAME is defining basic entities of the implemented .
 * service. It is used to get the correct entry point to the plugin.
 * FORMAT: {name, kind, version, constructor}, null terminated
 */
Arc::PluginDescriptor PLUGINS_TABLE_NAME[] = {
	{"eQTL","HED:SERVICE",1,&get_service},
	{ NULL, NULL, 0, NULL }
};


using namespace Arc;

namespace ArcService
{

	ExpressionQtlService::ExpressionQtlService(Arc::Config *cfg) : Service(cfg),logger(Logger::rootLogger, "eQTL")
	{

		// synchronize this with eqtl_arc.wsdl
		ns_["arc"]="http://uni-luebeck.de/eqtl/arc/";

		//read config like this: prefix_=(std::string)((*cfg)["prefix"]);
	}

	ExpressionQtlService::~ExpressionQtlService(void) 
	{
	}

	/**
	* Method which creates a fault payload 
	*/
	Arc::MCC_Status ExpressionQtlService::makeFault(Arc::Message& outmsg, const std::string &reason) 
	{
		logger.msg(Arc::WARNING, "Creating fault! Reason: \"%s\"",reason);
		// The boolean true indicates that inside of PayloadSOAP, 
		// an object SOAPFault will be created inside.
		Arc::PayloadSOAP* outpayload = new Arc::PayloadSOAP(ns_,true);
		Arc::SOAPFault* fault = outpayload->Fault();
		if(fault) {
			fault->Code(Arc::SOAPFault::Sender);
			fault->Reason(reason);
		};
		outmsg.Payload(outpayload);
		
		return Arc::MCC_Status(Arc::STATUS_OK);
	}

	/**
	* Processes the incoming message and generates an outgoing message.
	* @param inmsg incoming message
	* @param outmsg outgoing message
	* @return Status of the result achieved
	*/
	Arc::MCC_Status ExpressionQtlService::process(Arc::Message& inmsg, Arc::Message& outmsg) 
	{
		logger.msg(Arc::DEBUG, "eQTL service started...");

		/**  Extracting incoming payload */
		Arc::PayloadSOAP* inpayload  = NULL;
		try {
			inpayload = dynamic_cast<Arc::PayloadSOAP*>(inmsg.Payload());
		} catch(std::exception& e) { };
		if(!inpayload) {
			logger.msg(Arc::ERROR, "Input is not SOAP");
			return makeFault(outmsg, "Received message was not a valid SOAP message.");
		};
		/** */

		/** Analyzing and execute request */
/*
 <arc:QTL_FindByPosition>
         <!--Optional:-->
         <searchType>?</searchType>
         <searchRequest>
            <!--Optional:-->
            <lodScore>
               <from>?</from>
               <to>?</to>
            </lodScore>
            <!--Zero or more repetitions:-->
            <position>
               <chromosome>?</chromosome>
               <fromBP>?</fromBP>
               <toBP>?</toBP>
            </position>
            <!--Optional:-->
            <sameChromosome>?</sameChromosome>
            <!--Optional:-->
            <locusToGeneDistance>
               <from>?</from>
               <to>?</to>
            </locusToGeneDistance>
            <!--Optional:-->
            <orderBy>?</orderBy>
            <!--Optional:-->
            <maxNumResults>?</maxNumResults>
         </searchRequest>
      </arc:QTL_FindByPosition>
*/

		Arc::XMLNode requestNode  = inpayload->Child();
		logger.msg(Arc::DEBUG, "Called WSDL Operation: \"%s\"",requestNode.Name());
		if( requestNode.Name() == "QTL_FindByPosition" ) {
			std::string searchType = "both";
			Arc::XMLNode searchTypeNode = requestNode["searchType"];
			if( searchTypeNode ) searchType = searchTypeNode;
		} 

		Arc::XMLNode sayNode      = requestNode["echo:say"];
		std::string operation = (std::string) sayNode.Attribute("operation");
		std::string say       = (std::string) sayNode;
		std::string hear      = "";
		logger.msg(Arc::DEBUG, "Say: \"%s\"  Operation: \"%s\"",say,operation);
		/** */

		/** Create response */
		Arc::PayloadSOAP* outpayload = new Arc::PayloadSOAP(ns_);
		outpayload->NewChild("echo:echoResponse").NewChild("echo:hear")=hear;
		outmsg.Payload(outpayload);
		/** */

		logger.msg(Arc::DEBUG, "eQTL service done...");
		return Arc::MCC_Status(Arc::STATUS_OK);
	}

}//namespace