#include <config.h>

#include <arc/loader/Plugin.h>
#include <arc/message/PayloadSOAP.h>
#include <arc/message/PayloadRaw.h>
#include <arc/URL.h>

#include <stdio.h>

#define R_NO_REMAP 1
#define CSTACK_DEFNS 1
//otherwise R will redefine ERROR:
#define STRICT_R_HEADERS 1 

#include <R.h>
#include <Rembedded.h>
#include <Rinternals.h>
#include <Rinterface.h>


#include "eqtlservice.h"

extern "C" {
#include "cencode.h"
}

typedef enum {
    PARSE_NULL,
    PARSE_OK,
    PARSE_INCOMPLETE,
    PARSE_ERROR,
    PARSE_EOF
} ParseStatus;

extern "C" SEXP R_ParseVector(SEXP, int, ParseStatus *, SEXP);
extern "C" SEXP Rf_NewEnvironment(SEXP, SEXP, SEXP);

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


// using namespace Arc;

namespace ArcService
{

	ExpressionQtlService::ExpressionQtlService(Arc::Config *cfg) : Service(cfg),logger(Arc::Logger::rootLogger, "eQTL")
	{
		// get mysql connection parameters
		database = (std::string) cfg->Get("database");
		server = (std::string) cfg->Get("server");
		user = (std::string) cfg->Get("user");
		password = (std::string) cfg->Get("password");
		port = atoi( ((std::string) cfg->Get("port")).c_str() );

		// test connection to mysql server
		mysqlpp::Connection mysql( database.c_str(), server.c_str(), user.c_str(), password.c_str(), port );
		if(!mysql.connected())
			logger.msg(Arc::ERROR,"ExpressionQtlService: mysql connection failed.");
		mysql.disconnect();

		// synchronize this with eqtl_arc.wsdl
		// could also be used in config xml but is not
		ns_["arc"]="http://uni-luebeck.de/eqtl/arc/";

		// init embedded R
		char *argv[] = {(char*)"REmbeddedARCHED", (char*)"--gui=none", (char*)"--silent"};
		Rf_initEmbeddedR(sizeof(argv)/sizeof(argv[0]), argv);
		R_CStackLimit = -1; //unlimited R stack
	//	R_Interactive = FALSE; this will make it kill everything on error in tryEval

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

	std::string getBase64File(FILE* f) {
		std::string ret = "";
		const int readsize = 4096;
		char* plaintext = 0;
		char* code = 0;
		int plainlength;
		int codelength;
		base64_encodestate state;
		
		code = (char*)malloc(sizeof(char)*readsize*2);
		plaintext = (char*)malloc(sizeof(char)*readsize);
		
		base64_init_encodestate(&state);
		
		do
		{
			plainlength = fread((void*)plaintext, sizeof(char), readsize, f);
			codelength = base64_encode_block(plaintext, plainlength, code, &state);
			ret.append(code, codelength);
		}
		while (!feof(stdin) && plainlength > 0);
		
		codelength = base64_encode_blockend(code, &state);
		ret.append(code, codelength);
		
		free(code);
		free(plaintext);
		
		return ret;
	}

	
		//NOTE: this function was copied from Hopi.cpp
	static std::string GetPath(Arc::Message &inmsg,std::string &base) {
		base = inmsg.Attributes()->get("HTTP:ENDPOINT");
		Arc::AttributeIterator iterator = inmsg.Attributes()->getAll("PLEXER:EXTENSION");
		std::string path;
		if(iterator.hasMore()) {
			// Service is behind plexer
			path = *iterator;
			if(base.length() > path.length()) base.resize(base.length()-path.length());
		} else {
			// Standalone service
			path=Arc::URL(base).Path();
			base.resize(0);
		};
		return path;
	}
	
	

	
	Arc::MCC_Status ExpressionQtlService::fetchMysqlResultForRequest(Arc::XMLNode requestNode, mysqlpp::StoreQueryResult* result, Arc::Message& outmsg) {
		bool searchMarker = true;
		bool searchGene = true;
		if( ((std::string)requestNode["searchType"]).length() ) {
			std::string searchType = (std::string) requestNode["searchType"];
			if(searchType == "marker") searchGene = false;
			if(searchType == "gene") searchMarker = false;
		}
		Arc::XMLNode searchRequest = requestNode["searchRequest"];
		mysqlpp::Connection mysql( database.c_str(), server.c_str(), user.c_str(), password.c_str(), port );
		if(!mysql.connected()) {
			return makeFault(outmsg, "Could not connect to mysql.");
		}
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
		int maxNumResults = 500;
		if( ((std::string)searchRequest["maxNumResults"]).length() ) 
			maxNumResults = atoi( ((std::string) searchRequest["maxNumResults"]).c_str() );
		if(maxNumResults > 5000) maxNumResults = 5000;
		sql << " LIMIT "<< maxNumResults;
		
		logger.msg(Arc::DEBUG, "SQL Query: \"%s\"",sql.str());
		mysqlpp::StoreQueryResult res;
		try{
			res = sql.store();
		}catch(mysqlpp::Exception e) {
			return makeFault(outmsg, e.what());
		}
		logger.msg(Arc::DEBUG, "Number of results: \"%d\"",res.num_rows());
		
		*result = res;
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
		Glib::Mutex::Lock lock(r_single_thread, Glib::NOT_LOCK);
		
		logger.msg(Arc::DEBUG, "eQTL service started...");
		bool httpRequest = false;
		Arc::XMLNode requestNode(ns_, "dummy");
		
		/** check if this is HTTP GET and redirect if needed */
		std::string method = inmsg.Attributes()->get("HTTP:METHOD");
		if (method == "GET") {
			httpRequest = true;
			
			std::string base_url;
			std::string path = GetPath(inmsg,base_url);
			
			requestNode.Name("requestNodeForHtml");
			logger.msg(Arc::DEBUG, "Called HTML Operation: \"%s\" at path \"%s\" ",requestNode.Name(), path.c_str());
			
			std::string::size_type curPos = path.find('/', 0);
			if( curPos == std::string::npos || curPos > 1 ) curPos = 0;
			else curPos ++;
			while(1) {
				std::string::size_type nextPos = path.find('/', curPos);
				if( nextPos == std::string::npos ) break;
				std::string param = path.substr( curPos, nextPos - curPos );
				curPos = nextPos + 1;
				
				nextPos = path.find('/', curPos);
				if( nextPos == std::string::npos ) break;
				std::string value = path.substr( curPos, nextPos - curPos );
				curPos = nextPos + 1;
				
				logger.msg(Arc::DEBUG, "HTML Parameter: \"%s\" := \"%s\" ", param.c_str(), value.c_str() );
				Arc::XMLNode curNode = requestNode;
				std::string::size_type lastDot = 0;
				while(1) {
					std::string::size_type dotPos = param.find('.', lastDot);
					if(dotPos == std::string::npos) {
						curNode.NewChild( param.substr(lastDot) ) = value;
						break;
					} else {
						std::string str = param.substr(lastDot,dotPos-lastDot);
						if( curNode[str] ) curNode = curNode[str];
						else curNode = curNode.NewChild( str );
						lastDot = dotPos +1;
					}
				}
			}
			
			requestNode.NewChild("pathAfterLastSlash") = path.substr(path.rfind('/')+1); 
			
			if( requestNode["script-id"] ) {
				//we got a script id, so fetch the script from mysql
				mysqlpp::Connection mysql( database.c_str(), server.c_str(), user.c_str(), password.c_str(), port );
				if(!mysql.connected()) {
					return makeFault(outmsg, "Could not connect to mysql.");
				}
				mysqlpp::Query sql = mysql.query();
				sql << "SELECT script FROM r_scripts WHERE id=" << mysqlpp::quote << (std::string)requestNode["script-id"];
				mysqlpp::StoreQueryResult res;
				try{
					res = sql.store();
				}catch(mysqlpp::Exception e) {
					return makeFault(outmsg, e.what());
				}
				if(res.num_rows() > 0) 
					requestNode.NewChild("script") = res[0]["script"];
			}
										 
			std::string xmlTree;
			requestNode.GetXML(xmlTree);
			logger.msg(Arc::DEBUG, "HTML requestNode: %s ", xmlTree.c_str() );
		} 
		/** */
		
		/**  Extracting incoming payload */
		if(!httpRequest) {
			Arc::PayloadSOAP* inpayload  = NULL;
			try {
				inpayload = dynamic_cast<Arc::PayloadSOAP*>(inmsg.Payload());
			} catch(std::exception& e) { };
			if(!inpayload) {
				return makeFault(outmsg, "Received message was not a valid SOAP message.");
			}
			requestNode = inpayload->Child();
			logger.msg(Arc::DEBUG, "Called WSDL Operation: \"%s\"",requestNode.Name());
		}
		/** */

		/** Analyzing and execute request */
		mysqlpp::StoreQueryResult queryResults;
		Arc::MCC_Status resultStatus = fetchMysqlResultForRequest( requestNode, &queryResults, outmsg );
		if(!resultStatus.isOk()) 
			return resultStatus;
		/** */
		
		/** if this is an R request, do R postprocessing */
		std::vector<std::string> attachmentFileList;
		std::vector<std::string> evaluationResults;
	
		if( requestNode["script"] ) {
			// if we do R or http, from here on downwards, this function is single threaded. 
			// that way we can safely play with R and files, but get all the multithreading goodies for the mysql data aquiration part
			lock.acquire();
			
			// STEP 1: format data into R matrix
			
			int numCol = 1+3+3+1+4;
			int numRow = queryResults.num_rows();
			SEXP dataForR = Rf_allocMatrix(STRSXP, numRow, numCol);
			PROTECT( dataForR ); //we dont want R to garbage collect this
			for(size_t i=0;i<queryResults.num_rows();i++) {
				SET_STRING_ELT(dataForR, i + numRow*0, Rf_mkChar(queryResults[i]["lod"]));
				SET_STRING_ELT(dataForR, i + numRow*1, Rf_mkChar(queryResults[i]["marker_name"]));
				SET_STRING_ELT(dataForR, i + numRow*2, Rf_mkChar(queryResults[i]["marker_chromosome"]));
				SET_STRING_ELT(dataForR, i + numRow*3, Rf_mkChar(queryResults[i]["marker_positionBP"]));
				SET_STRING_ELT(dataForR, i + numRow*4, Rf_mkChar(queryResults[i]["genePosition_chromosome"]));
				SET_STRING_ELT(dataForR, i + numRow*5, Rf_mkChar(queryResults[i]["genePosition_fromBP"]));
				SET_STRING_ELT(dataForR, i + numRow*6, Rf_mkChar(queryResults[i]["genePosition_toBP"]));
				SET_STRING_ELT(dataForR, i + numRow*7, Rf_mkChar(queryResults[i]["geneEntrezID"]));
				SET_STRING_ELT(dataForR, i + numRow*8, Rf_mkChar(queryResults[i]["statistics_mean"]));
				SET_STRING_ELT(dataForR, i + numRow*9, Rf_mkChar(queryResults[i]["statistics_sd"]));
				SET_STRING_ELT(dataForR, i + numRow*10, Rf_mkChar(queryResults[i]["statistics_median"]));
				SET_STRING_ELT(dataForR, i + numRow*11, Rf_mkChar(queryResults[i]["statistics_variance"]));
			}
			
			SEXP colnames = Rf_allocVector(STRSXP, 12);
			SET_STRING_ELT(colnames, 0, Rf_mkChar("lod"));
			SET_STRING_ELT(colnames, 1, Rf_mkChar("marker_name"));
			SET_STRING_ELT(colnames, 2, Rf_mkChar("marker_chromosome"));
			SET_STRING_ELT(colnames, 3, Rf_mkChar("marker_positionBP"));
			SET_STRING_ELT(colnames, 4, Rf_mkChar("genePosition_chromosome"));
			SET_STRING_ELT(colnames, 5, Rf_mkChar("genePosition_fromBP"));
			SET_STRING_ELT(colnames, 6, Rf_mkChar("genePosition_toBP"));
			SET_STRING_ELT(colnames, 7, Rf_mkChar("geneEntrezID"));
			SET_STRING_ELT(colnames, 8, Rf_mkChar("statistics_mean"));
			SET_STRING_ELT(colnames, 9, Rf_mkChar("statistics_sd"));
			SET_STRING_ELT(colnames, 10, Rf_mkChar("statistics_median"));
			SET_STRING_ELT(colnames, 11, Rf_mkChar("statistics_variance"));
			// we have 2 dimensions and column names are the second one, thus index 1
			SEXP dimnames = Rf_allocVector(VECSXP, 2);
			SET_VECTOR_ELT(dimnames, 1, colnames);
			Rf_setAttrib(dataForR, R_DimNamesSymbol, dimnames);
			
			logger.msg(Arc::DEBUG, "Creating new environment inside R global environment...");
			SEXP calcenv = Rf_NewEnvironment(R_NilValue, R_NilValue, R_GlobalEnv);
			PROTECT(calcenv);
			logger.msg(Arc::DEBUG, "Registring data into R...");
			Rf_defineVar(Rf_install("data"), dataForR, calcenv);
			logger.msg(Arc::DEBUG, "Variable \"data\" set.");
			
			
			// STEP 2: parse R requests
			
			ParseStatus status;
			char* commandStr = strdup( ((std::string)requestNode["script"]).c_str() );
			logger.msg(Arc::DEBUG, "R script: %s", commandStr);
			
			SEXP str = Rf_mkString(commandStr);
			free(commandStr);
			PROTECT(str);
			
			SEXP commands = R_ParseVector(str, -1, &status, R_NilValue);
			if( status == PARSE_OK ) 
				logger.msg(Arc::DEBUG, "R_ParseVector succeeded.");
			else {
				logger.msg(Arc::DEBUG, "R_ParseVector failed with status %d.", status);
				evaluationResults.push_back("The script you provided could not be parsed by R using R_ParseVector.");
			}
			
			
			// STEP 3: execute R commands and log results
			
			PROTECT(commands);
			if( status == PARSE_OK ) {
	
				int nCommands = Rf_length(commands);
				for(int i=0;i<nCommands;i++) {
					int errorStatus;
					SEXP curCommand = VECTOR_ELT(commands,i);
					SEXP result = R_tryEval(curCommand, calcenv, &errorStatus);
					logger.msg(Arc::DEBUG, "R_tryEval number %d of %d returned error status %d.", i+1, nCommands, errorStatus);
					
					if( errorStatus == 0 ) {
						if( Rf_isVector(result) && i == nCommands-1 ) {
							
							SEXP printExpr = Rf_allocVector(EXPRSXP, 1);
							PROTECT(printExpr);
							SET_VECTOR_ELT(printExpr, 0, result);
							
							SEXP printCall, tmp;
							PROTECT(tmp = printCall = Rf_allocList(2));
							SET_TYPEOF(printCall, LANGSXP);
							SETCAR(tmp, Rf_install("capture.output")); 
							tmp = CDR(tmp);
							SETCAR(tmp, printExpr); 
							
							SEXP capturedString = R_tryEval(printCall, calcenv, &errorStatus);
							logger.msg(Arc::DEBUG, "R_tryEval to format result returned error status %d.", errorStatus);
							UNPROTECT(2); // printCall + printExpr
							
							if(errorStatus == 0) {
								logger.msg(Arc::DEBUG, "capturedString is of type %d.", TYPEOF(capturedString));
								int nLines = Rf_length(capturedString);
								for(int j=0;j<nLines;j++)
									evaluationResults.push_back( Rf_translateCharUTF8(STRING_ELT(capturedString,j)) );
							}
						}
					} else {
						std::stringstream statusStr;
						statusStr << "Error executing R command. Error status: ";
						statusStr << errorStatus;
						evaluationResults.push_back(statusStr.str());
					}
				}
				
			}
			UNPROTECT(3); //command str + commands + dataForR
			
			SEXP attachmentList;
			PROTECT( attachmentList = Rf_findVar(Rf_install("attachmentList"), calcenv) );
			logger.msg(Arc::DEBUG, "attachmentList is of type %d.", TYPEOF(attachmentList));
			if( Rf_isString(attachmentList) ) {
				int nAttachments = Rf_length(attachmentList);
				for(int j=0;j<nAttachments;j++) {
					SEXP attachment = STRING_ELT(attachmentList, j);
					std::string name = Rf_translateCharUTF8(attachment);
					attachmentFileList.push_back(name);
				}
			}
			UNPROTECT(1); // attachmentList
			
			UNPROTECT(1); // calcenv
		}
		/** */

		/** format results according to request */
		if( requestNode.Name() == "QTL_FindByPosition" ) {
			Arc::PayloadSOAP* outpayload = new Arc::PayloadSOAP(ns_);
			Arc::XMLNode resultNode = outpayload->NewChild("arc:QTL_FindByPositionResponse");
			
			for(size_t i=0;i<queryResults.num_rows();i++) {
				Arc::XMLNode curAdd = resultNode.NewChild("qtls");
				curAdd.NewChild("lod") = queryResults[i]["lod"];
				curAdd.NewChild("marker");
				curAdd["marker"].NewChild("name") = queryResults[i]["marker_name"];
				curAdd["marker"].NewChild("chromosome") = queryResults[i]["marker_chromosome"];
				curAdd["marker"].NewChild("positionBP") = queryResults[i]["marker_positionBP"];
				curAdd.NewChild("genePosition");
				curAdd["genePosition"].NewChild("chromosome") = queryResults[i]["genePosition_chromosome"];
				curAdd["genePosition"].NewChild("fromBP") = queryResults[i]["genePosition_fromBP"];
				curAdd["genePosition"].NewChild("toBP") = queryResults[i]["genePosition_toBP"];
				curAdd.NewChild("geneEntrezID") = queryResults[i]["geneEntrezID"];
				curAdd.NewChild("statistics");
				curAdd["statistics"].NewChild("mean") = queryResults[i]["statistics_mean"];
				curAdd["statistics"].NewChild("sd") = queryResults[i]["statistics_sd"];
				curAdd["statistics"].NewChild("median") = queryResults[i]["statistics_median"];
				curAdd["statistics"].NewChild("variance") = queryResults[i]["statistics_variance"];
			}
			
			outmsg.Payload(outpayload);
		} else if( requestNode.Name() == "QTL_FindByPosition_R" ) {
			Arc::PayloadSOAP* outpayload = new Arc::PayloadSOAP(ns_);
			Arc::XMLNode resultNode = outpayload->NewChild("arc:QTL_FindByPosition_RResponse");
			
			Arc::XMLNode scriptResults = resultNode.NewChild("scriptResults");
			for (int i=0; i<evaluationResults.size(); i++) {
				scriptResults.NewChild("output") = evaluationResults[i];
			}
			
			Arc::XMLNode attachmentResults = resultNode.NewChild("attachments");
			for (int i=0; i<attachmentFileList.size(); i++) {
				std::string name = attachmentFileList[i];
				FILE* f = fopen(name.c_str(),"rb");
				if(!f) continue;
				Arc::XMLNode curAtt = attachmentResults.NewChild("files");
				curAtt.NewChild("name") = name;
				curAtt.NewChild("data") = getBase64File(f);
				fclose(f);
			}
			
			outmsg.Payload(outpayload);
		} else if( httpRequest ) {
			Arc::PayloadRaw *buf = new Arc::PayloadRaw();			
			std::string errorString = "";
			
			if( requestNode["script"] ) {
				//we have a script, so format R output
				
				if( attachmentFileList.size() != 1 ) {
					errorString = "your script needs to produce exactly one attachment";
				} else {
					std::string name = attachmentFileList[0];
					
					FILE* f = fopen(name.c_str(),"rb");
					if(f) {
						fseek(f, 0, SEEK_END);
						int len = ftell(f);
						fseek(f, 0, SEEK_SET);
						char* data = (char*) malloc(len);
						fread(data,1,len,f);
						buf->Insert(data, 0, len);
						free(data);
						fclose(f);
					} else {
						errorString = " output file \"";
						errorString.append(name);
						errorString.append("\" was not readable");
						std::stringstream html;
					}				
				}
				
				if( errorString.length() > 0 ) {
					std::stringstream html;
					html << "<html><body><h1>Error: ";
					html << errorString;
					html << "</h1><br>Listing R output:<br>";
					for (int i=0; i<evaluationResults.size(); i++) {
						html << evaluationResults[i];
						html << "<br>";
					}
					html << "</body></html>";
					std::string htmls = html.str();
					buf->Insert(htmls.c_str(), 0, htmls.length());
				}
				
			} else {
				// no script => JSON
				std::stringstream json;
				json << requestNode["pathAfterLastSlash"] << "( [";
				
				for(size_t i=0;i<queryResults.num_rows();i++) {
					if(i>0) json << ",";
					json << "{lod:" << queryResults[i]["lod"];
					json << ",marker:{name:'" << queryResults[i]["marker_name"];
					json << "',chromosome:'" << queryResults[i]["marker_chromosome"];
					json << "',positionBP:" << queryResults[i]["marker_positionBP"];
					json << "},genePosition:{chromosome:'" << queryResults[i]["genePosition_chromosome"];
					json << "',fromBP:" << queryResults[i]["genePosition_fromBP"];
					json << ",toBP:" << queryResults[i]["genePosition_toBP"];
					json << "},geneEntrezID:'" << queryResults[i]["geneEntrezID"];
					json << "',statistics:{mean:" << queryResults[i]["statistics_mean"];
					json << ",sd:" << queryResults[i]["statistics_sd"];
					json << ",median:" << queryResults[i]["statistics_median"];
					json << ",variance:" << queryResults[i]["statistics_variance"];
					json << "}}";
				}
				
				json << "] );";
				
				std::string json_str = json.str();
				buf->Insert(json_str.c_str(), 0, json_str.length());
			}
			
			outmsg.Payload(buf);
		}
															   
		logger.msg(Arc::DEBUG, "eQTL service done...");
		return Arc::MCC_Status(Arc::STATUS_OK);
	}
	
	


}//namespace
