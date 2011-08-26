    happy.prepare.marker.file <- function(file="", genotype.matrix=NULL) {

	genotype.matrix.parentals<-as.matrix(genotype.matrix[parental.strains,])
	genotype.matrix.parentals.distribution<-apply(genotype.matrix.parentals,2,function(X){
		if (any(is.na(X))) stop("Found NA in parental strains' genotyping")
		alleles_concatenated<-paste(X,collapse="",sep="");
		#print(alleles_concatenated)
		alleles_table<-table(unlist(strsplit(split="",x=alleles_concatenated)))
		#print(alleles_table)
	})

	if (!is.matrix(genotype.matrix.parentals.distribution)) {
		stop("Every SNP was supposed to have exactly to possible values.")
	}

	cat(file=file,"markers ",ncol(genotype.matrix.parentals.distribution),
		      " strains ",length(parental.strains),"\n",append=F,sep="")
	cat(file=file,"strain_names ",paste(parental.strains,collapse=" ",sep=""),
			"\n",append=T,sep="")
	for(n in colnames(genotype.matrix.parentals.distribution)) {
		d<-genotype.matrix.parentals.distribution[,n]
		chr<-as.character(genotype.matrix["Chr",n])
		pos<-as.numeric(as.character(genotype.matrix["Position",n]))
		#cat("chr",chr,", pos",pos,"\n"); print(d)
		cat(file=file,"marker ",n," ",(length(d)+1)," ",
				chr," ",bp2cM(pos),"\n",sep="",append=T)
		cat(file=file,paste("allele ",
				    missing.code,
		 	            paste(rep(1/length(parental.strains),length(parental.strains)),
				          collapse=" ",sep=""),"\n"),append=T)
		for(nucleotide in names(d)) {	
			cat(file=file,"allele ",nucleotide,sep="",append=T)
			for(s in parental.strains) {
				#print(s); print(n)
				nn<-unlist(strsplit(split="",x=as.character(genotype.matrix.parentals[s,n])))
				#print(nn)
				nn.matching.num<-sum(nn==nucleotide)
				#print(nn.matching.num)
				nn.total<-d[[nucleotide]]
				#print(nn.total)
				cat(file=file," ",nn.matching.num/nn.total,sep="",append=T)
			}
			cat(file=file,"\n",append=T)
		}
	}
    }
