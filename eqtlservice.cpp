#include <arc/loader/Plugin.h>
#include <arc/message/PayloadSOAP.h>

#include <stdio.h>

#define R_NO_REMAP 1
#include <R.h>
#include <Rembedded.h>
#include <Rinternals.h>


#include "eqtlservice.h"

typedef enum {
    PARSE_NULL,
    PARSE_OK,
    PARSE_INCOMPLETE,
    PARSE_ERROR,
    PARSE_EOF
} ParseStatus;

extern "C" SEXP R_ParseVector(SEXP, int, ParseStatus *, SEXP);



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

		// init embedded R
		char *argv[] = {"R", "--gui=none", "--silent"};
		Rf_initEmbeddedR(sizeof(argv)/sizeof(argv[0]), argv);

		// read config like this: prefix_=(std::string)((*cfg)["prefix"]);
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
//FIXME: causes compile error. dunno why 			logger.msg(Arc::ERROR,"Input is not SOAP");
			return makeFault(outmsg, "Received message was not a valid SOAP message.");
		};
		/** */

		/** Analyzing and execute request */
		Arc::PayloadSOAP* outpayload = new Arc::PayloadSOAP(ns_);

		Arc::XMLNode requestNode  = inpayload->Child();
		logger.msg(Arc::DEBUG, "Called WSDL Operation: \"%s\"",requestNode.Name());
		if( requestNode.Name() == "QTL_FindByPosition" || requestNode.Name() == "QTL_FindByPosition_R" ) {
			bool searchMarker = true;
			bool searchGene = true;
			if( ((std::string)requestNode["searchType"]).length() ) {
				std::string searchType = (std::string) requestNode["searchType"];
				if(searchType == "marker") searchGene = false;
				if(searchType == "gene") searchMarker = false;
			}
			Arc::XMLNode searchRequest = requestNode["searchRequest"];
			mysqlpp::Query sql = mysql.query();
			sql << "SELECT * FROM hajo_qtl_nocov WHERE 1 ";

			if( ((std::string)searchRequest["lodScore"]["from"]).length() ) 
				sql << " AND lod >= " << mysqlpp::quote << (std::string) searchRequest["lodScore"]["from"];
			if( ((std::string)searchRequest["lodScore"]["to"]).length() ) 
				sql << " AND lod <= " << mysqlpp::quote << (std::string) searchRequest["lodScore"]["to"];

			Arc::XMLNode position = searchRequest["position"];
			if( position.Size()>0 ) {
				sql << " AND ( FALSE ";

				while(position) {
					if( searchMarker ) {
						sql << " OR ( marker_chromosome=" << mysqlpp::quote << (std::string) position["chromosome"];
						if( ((std::string)position["fromBP"]).length() )
							sql << " AND marker_positionBP >= " << mysqlpp::quote << (std::string) position["fromBP"];
						if( ((std::string)position["toBP"]).length() )
							sql << " AND marker_positionBP <= " << mysqlpp::quote << (std::string) position["toBP"];
						sql << ") ";
					}
					if( searchGene ) {
						sql << " OR ( genePosition_chromosome=" << mysqlpp::quote << (std::string) position["chromosome"];
						if( ((std::string)position["fromBP"]).length() )
							sql << " AND genePosition_toBP >= " << mysqlpp::quote << (std::string) position["fromBP"];
						if( ((std::string)position["toBP"]).length() )
							sql << " AND genePosition_fromBP <= " << mysqlpp::quote << (std::string) position["toBP"];
						sql << ") ";
					}
					++position;
				}
				sql << " ) ";
			}

			if( ((std::string)searchRequest["sameChromosome"]).length() ) {
				int what = atoi( ((std::string) searchRequest["sameChromosome"]).c_str() );
				if( what == 1 ) sql << " AND sameChromosome='1' ";
				else if( what == -1 ) sql << " AND sameChromosome='0' ";
			}

			if( ((std::string)searchRequest["locusToGeneDistance"]["from"]).length() ) 
				sql << " AND locusToGeneDistance >= " << mysqlpp::quote << (std::string) searchRequest["locusToGeneDistance"]["from"];
			if( ((std::string)searchRequest["locusToGeneDistance"]["to"]).length() ) 
				sql << " AND locusToGeneDistance <= " << mysqlpp::quote << (std::string) searchRequest["locusToGeneDistance"]["to"];

			std::string orderBy = "lod DESC";
			if( ((std::string)searchRequest["orderBy"]).length() ) {
				std::string order = searchRequest["orderBy"];
				if( order == "LodScore" ) orderBy = "lod DESC";
			}
			sql << " ORDER BY " << orderBy;
			if( ((std::string)searchRequest["maxNumResults"]).length() ) 
				sql << " LIMIT "<< atoi( ((std::string) searchRequest["maxNumResults"]).c_str() );

			logger.msg(Arc::DEBUG, "SQL Query: \"%s\"",sql.str());
			mysqlpp::StoreQueryResult res;
			try{
				res = sql.store();
			}catch(mysqlpp::Exception e) {
				return makeFault(outmsg, e.what());
			}
			logger.msg(Arc::DEBUG, "Number of results: \"%d\"",res.num_rows());

			if( requestNode.Name() == "QTL_FindByPosition" ) {
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
			} else if( requestNode.Name() == "QTL_FindByPosition_R" ) {
				Arc::XMLNode addToMe = outpayload->NewChild("arc:QTL_FindByPosition_RResponse");
				int numCol = 1+3+3+1+4;
				int numRow = res.num_rows();
				SEXP dataForR = Rf_allocMatrix(VECSXP, numRow, numCol);
				PROTECT( dataForR ); //we dont want R to garbage collect this
				for(size_t i=0;i<res.num_rows();i++) {
					int rowOff = i * numCol;
					SET_VECTOR_ELT(dataForR, rowOff+0, Rf_mkString(res[i]["lod"]));
					SET_VECTOR_ELT(dataForR, rowOff+1, Rf_mkString(res[i]["marker_name"]));
					SET_VECTOR_ELT(dataForR, rowOff+2, Rf_mkString(res[i]["marker_chromosome"]));
					SET_VECTOR_ELT(dataForR, rowOff+3, Rf_mkString(res[i]["marker_positionBP"]));
					SET_VECTOR_ELT(dataForR, rowOff+4, Rf_mkString(res[i]["genePosition_chromosome"]));
					SET_VECTOR_ELT(dataForR, rowOff+5, Rf_mkString(res[i]["genePosition_fromBP"]));
					SET_VECTOR_ELT(dataForR, rowOff+6, Rf_mkString(res[i]["genePosition_toBP"]));
					SET_VECTOR_ELT(dataForR, rowOff+7, Rf_mkString(res[i]["geneEntrezID"]));
					SET_VECTOR_ELT(dataForR, rowOff+8, Rf_mkString(res[i]["statistics_mean"]));
					SET_VECTOR_ELT(dataForR, rowOff+9, Rf_mkString(res[i]["statistics_sd"]));
					SET_VECTOR_ELT(dataForR, rowOff+10, Rf_mkString(res[i]["statistics_median"]));
					SET_VECTOR_ELT(dataForR, rowOff+11, Rf_mkString(res[i]["statistics_variance"]));
				}
/*
				SEXP colnames = Rf_GetColNames(dataForR);
				SET_VECTOR_ELT(colnames, 0, Rf_mkString("lod"));
				SET_VECTOR_ELT(colnames, 1, Rf_mkString("marker_name"));
				SET_VECTOR_ELT(colnames, 2, Rf_mkString("marker_chromosome"));
				SET_VECTOR_ELT(colnames, 3, Rf_mkString("marker_positionBP"));
				SET_VECTOR_ELT(colnames, 4, Rf_mkString("genePosition_chromosome"));
				SET_VECTOR_ELT(colnames, 5, Rf_mkString("genePosition_fromBP"));
				SET_VECTOR_ELT(colnames, 6, Rf_mkString("genePosition_toBP"));
				SET_VECTOR_ELT(colnames, 7, Rf_mkString("geneEntrezID"));
				SET_VECTOR_ELT(colnames, 8, Rf_mkString("statistics_mean"));
				SET_VECTOR_ELT(colnames, 9, Rf_mkString("statistics_sd"));
				SET_VECTOR_ELT(colnames, 10, Rf_mkString("statistics_median"));
				SET_VECTOR_ELT(colnames, 11, Rf_mkString("statistics_variance"));
*/ 
				logger.msg(Arc::DEBUG, "Registring data into R...");
				Rf_defineVar(Rf_install("data"), dataForR, R_GlobalEnv);
				logger.msg(Arc::DEBUG, "Variable \"data\" set.");

				ParseStatus status;
				char* commandStr = strdup( ((std::string)requestNode["script"]).c_str() );
				logger.msg(Arc::DEBUG, "R script: %s", commandStr);

				SEXP str = Rf_mkString(commandStr);
				free(commandStr);
				PROTECT(str);
				//Rf_PrintValue(str);

				SEXP commands = R_ParseVector(str, -1, &status, R_NilValue);
				if( status == PARSE_OK ) 
					logger.msg(Arc::DEBUG, "R_ParseVector succeeded.");
				else {
					logger.msg(Arc::DEBUG, "R_ParseVector failed with status %d.", status);
					return makeFault(outmsg, "The script you provided could not be parsed by R using R_ParseVector.");
				}
				PROTECT(commands);

				int nCommands = Rf_length(commands);
				for(int i=0;i<nCommands;i++) {
					Arc::XMLNode lineResult = addToMe.NewChild("scriptResults");
					int errorStatus;
					SEXP curCommand = VECTOR_ELT(commands,i);
					//Rf_PrintValue(curCommand);
					SEXP result = R_tryEval(curCommand, R_GlobalEnv, &errorStatus);
					logger.msg(Arc::DEBUG, "R_tryEval number %d of %d returned error status %d.", i+1, nCommands, errorStatus);

					if( errorStatus == 0 ) {
						//Rf_PrintValue(result);
						logger.msg(Arc::DEBUG, "Returned SEXP is of type %d.", TYPEOF(result));
						if( ! Rf_isNull(result) ) {

							SEXP printExpr = Rf_allocVector(EXPRSXP, 1);
							PROTECT(printExpr);
							SET_VECTOR_ELT(printExpr, 0, result);
							//Rf_PrintValue(printExpr);

							SEXP printCall, tmp;
							PROTECT(tmp = printCall = Rf_allocList(2));
							SET_TYPEOF(printCall, LANGSXP);
							SETCAR(tmp, Rf_install("capture.output")); 
							tmp = CDR(tmp);
							SETCAR(tmp, printExpr); 
							//Rf_PrintValue(printCall);

							SEXP capturedString = R_tryEval(printCall, R_GlobalEnv, &errorStatus);
							logger.msg(Arc::DEBUG, "R_tryEval to format result returned error status %d.", errorStatus);
							UNPROTECT(2); // printCall + printExpr
	
							if(errorStatus == 0) {
								logger.msg(Arc::DEBUG, "capturedString is of type %d.", TYPEOF(capturedString));
								int nLines = Rf_length(capturedString);
								for(int j=0;j<nLines;j++)
									lineResult.NewChild("output") = Rf_translateCharUTF8(STRING_ELT(capturedString,j));
							}
						}
					} else {
						std::stringstream statusStr;
						statusStr << errorStatus;
						lineResult.NewChild("errorStatus") = statusStr.str();
					}
				}
				UNPROTECT(3); //command str + commands + dataForR
			}
		} 

		logger.msg(Arc::DEBUG, "eQTL service done...");
		outmsg.Payload(outpayload);
		return Arc::MCC_Status(Arc::STATUS_OK);
	}

}//namespace
