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

	ExpressionQtlService::ExpressionQtlService(Arc::Config *cfg) : Service(cfg),logger(Logger::rootLogger, "eQTL"),
	 mysql("eQTL_Stockholm","localhost","root","")
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
		Arc::PayloadSOAP* outpayload = new Arc::PayloadSOAP(ns_);

		Arc::XMLNode requestNode  = inpayload->Child();
		logger.msg(Arc::DEBUG, "Called WSDL Operation: \"%s\"",requestNode.Name());
		if( requestNode.Name() == "QTL_FindByPosition" ) {
			bool searchMarker = true;
			bool searchGene = true;
			if( requestNode["searchType"] ) {
				std::string searchType = (std::string) requestNode["searchType"];
				if(searchType == "marker") searchGene = false;
				if(searchType == "gene") searchMarker = false;
			}
			Arc::XMLNode searchRequest = requestNode["searchRequest"];
			mysqlpp::Query sql = mysql.query();
			sql << "SELECT * FROM hajo_qtl_nocov WHERE 1 ";

			if( searchRequest["lodScore"]["from"] ) 
				sql << " AND lod >= " << mysqlpp::quote << (std::string) searchRequest["lodScore"]["from"];
			if( searchRequest["lodScore"]["to"] ) 
				sql << " AND lod <= " << mysqlpp::quote << (std::string) searchRequest["lodScore"]["to"];
			
			Arc::XMLNode position = searchRequest["position"];
			if( position ) {
				sql << " AND ( FALSE ";

				while(position) {
					if( searchMarker ) {
						sql << " OR ( marker_chromosome=" << mysqlpp::quote << (std::string) position["chromosome"];
						if( position["fromBP"] )
							sql << " AND marker_positionBP >= " << mysqlpp::quote << (std::string) position["fromBP"];
						if( position["toBP"] )
							sql << " AND marker_positionBP <= " << mysqlpp::quote << (std::string) position["toBP"];
						sql << ") ";
					}
					if( searchGene ) {
						sql << " OR ( genePosition_chromosome=" << mysqlpp::quote << (std::string) position["chromosome"];
						if( position["fromBP"] )
							sql << " AND genePosition_toBP >= " << mysqlpp::quote << (std::string) position["fromBP"];
						if( position["toBP"] )
							sql << " AND genePosition_fromBP <= " << mysqlpp::quote << (std::string) position["toBP"];
						sql << ") ";
					}
					++position;
				}
				sql << " ) ";
			}

			if( searchRequest["sameChromosome"] ) {
				int what = atoi( ((std::string) searchRequest["sameChromosome"]).c_str() );
				if( what == 1 ) sql << " AND sameChromosome='1' ";
				else if( what == -1 ) sql << " AND sameChromosome='0' ";
			}

			if( searchRequest["locusToGeneDistance"]["from"] ) 
				sql << " AND locusToGeneDistance >= " << mysqlpp::quote << (std::string) searchRequest["locusToGeneDistance"]["from"];
			if( searchRequest["locusToGeneDistance"]["to"] ) 
				sql << " AND locusToGeneDistance <= " << mysqlpp::quote << (std::string) searchRequest["locusToGeneDistance"]["to"];

			std::string orderBy = "lod";
			if( searchRequest["orderBy"] ) {
				std::string order = searchRequest["orderBy"];
				if( order == "LodScore" ) orderBy = "lod";
			}
			sql << " ORDER BY " << mysqlpp::quote << orderBy;
			if( searchRequest["maxNumResults"] )
				sql << " LIMIT "<< atoi( ((std::string) searchRequest["maxNumResults"]).c_str() );

			logger.msg(Arc::DEBUG, "SQL Query: \"%s\"",sql.str());
			mysqlpp::StoreQueryResult res;
			try{
				res = sql.store();
			}catch(mysqlpp::Exception e) {
				return makeFault(outmsg, e.what());
			}
			logger.msg(Arc::DEBUG, "Number of results: \"%d\"",res.num_rows());

			Arc::XMLNode addToMe = outpayload->NewChild("arc:QTL_FindByPositionResponse");
			for(size_t i=0;i<res.num_rows();i++) {
				Arc::XMLNode curAdd = addToMe.NewChild("qtls");
				curAdd.NewChild("lod") = res[i]["lod"];
				curAdd.NewChild("marker");
				curAdd["marker"].NewChild("name") = res[i]["marker_name"];
				curAdd["marker"].NewChild("chromosome") = res[i]["marker_chromosome"];
				curAdd["marker"].NewChild("positionBP") = res[i]["marker_positionBP"];
				curAdd.NewChild("genePosition");
				curAdd["genePosition"].NewChild("chromosome") = res[i]["genePosition_chromosome"];
				curAdd["genePosition"].NewChild("fromBP") = res[i]["genePosition_fromBP"];
				curAdd["genePosition"].NewChild("toBP") = res[i]["genePosition_toBP"];
				curAdd.NewChild("geneEntrezID") = res[i]["geneEntrezID"];
				curAdd.NewChild("statistics");
				curAdd["statistics"].NewChild("mean") = res[i]["statistics_mean"];
				curAdd["statistics"].NewChild("sd") = res[i]["statistics_sd"];
				curAdd["statistics"].NewChild("median") = res[i]["statistics_median"];
				curAdd["statistics"].NewChild("variance") = res[i]["statistics_variance"];
			}
		} 

		logger.msg(Arc::DEBUG, "eQTL service done...");
		outmsg.Payload(outpayload);
		return Arc::MCC_Status(Arc::STATUS_OK);
	}

}//namespace