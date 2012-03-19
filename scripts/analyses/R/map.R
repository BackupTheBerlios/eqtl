
# Routines to create one approximating function per chromosome to
# Translate between cM and bp and back. It is sourced from 
# createVenn.R and scatter_all.R.


# retrieving marker data
rs.map<-dbGetQuery(con, "SELECT marker, cmorgan_rqtl, chr, bp FROM map WHERE bp IS NOT NULL AND cmorgan_rqtl IS NOT NULL ORDER BY chr,cmorgan_rqtl")
rs.map.cM2bp <- sapply(unique(rs.map[,"chr"]),function(chr){
				#print(paste("Chr:",X))
				subset<-(rs.map[,"chr"]==chr)

				if (2 > sum(subset)) {
					cat("subset: "); print(subset)
					t<-paste("rs.map.cM2bp: found no more than ",sum(subset)," markers for chromosome '",chr,"'.\n",sep="")
					cat(t)
					stop(t)
					return(NULL)
				} else {
					#print(which(subset))
					data.pairs.relevant<-rs.map[subset,c("cmorgan_rqtl","bp"),drop=F]
					cat("data.pairs.relevant:\n"); print(data.pairs.relevant)
					return(approxfun(data.pairs.relevant,#xout=c(-40,500),
						rule=2))
				}
			},USE.NAMES=TRUE)

rs.map.bp2cM <- sapply(unique(rs.map[,"chr"]),function(X){
				#print(paste("Chr:",X))
				subset<-(rs.map[,"chr"]==X)
				
				if (2 > length(subset)) {
					cat("subset: "); print(subset)
					t<-paste("rs.map.bp2cM: found no more than ",sum(subset)," markers for chromosome '",chr,"'.\n",sep="")
					cat(t)
					stop(t)
					return(NULL)
				} else {
					#print(which(subset))
					data.pairs.relevant<-rs.map[subset,c("bp","cmorgan_rqtl")]
					cat("data.pairs.relevant:\n"); print(data.pairs.relevant)
					return(approxfun(data.pairs.relevant,rule=2))
				}
			},USE.NAMES=TRUE)

