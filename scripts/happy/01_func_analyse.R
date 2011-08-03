analyse.split.chromosomes<-function(phen,chr,data.phenotype.source,phenotypes.collection,
                                    generations,model,family,inputdir="./",outputdir="./", missing.code="NA") {
	if (! data.phenotype.source %in% names(phenotypes.collection)) {
		stop("Specification of data.phenotype.source (",data.phenotype.source,") not found. ",
		     "It should be one of ",paste(names(phenotypes.collection),collapse=", ",sep=""),".\n")
	}
	read.table.phenotypes <- phenotypes.collection[[data.phenotype.source]]
	markers.filename.chr<-paste(inputdir,"/","markers_chr_",chr,".input",sep="")
	if (!file.exists(markers.filename.chr)) stop(paste("Cannot find marker file for chromosome",chr,"\n",sep=""))

	for (individuals.subset in c("3m","6m","all")) {
							# covariates do not influence parameters stored
		fname<-paste(inputdir,"/","happy_project_",simpler.name(project.name),"_",
		             individuals.subset,"_",phen,name.suffix,"_chr_",chr,".input",sep="")
		if (!file.exists(fname)) {
			cat("  cannot find input file expected at '",fname,"'\n",sep="") ; next
		}

		cat("\n\nWorking on '",fname,"'\n\n",sep="")
		a<-grep(pattern="^h$",x=ls(),value=TRUE) ; b<-grep(pattern="^fit",x=ls(),value=TRUE)
		if(!is.null(a)) rm(list=c(a,b))

		h<-happy(datafile=fname,allelesfile=markers.filename.chr,generations=4,phase="unknown",
			file.format="happy",missing.code="NA")
		if(0 == var(h$phenotypes)) {
			cat("W: 0 == var(h$phenotypes) for phen '",phen,name.suffix,covariates.suffix," on chr ",chr,".  Skipping.\n")
			next
		}

		# Every chromosome may have a different set of individuals
		if(!is.null(data.covariates)) {
			covariatematrix<-as.matrix(read.table.phenotypes[h$subjects,data.covariates,drop=FALSE])
		}

		fit.0<-hfit(h, permute=0,verbose=TRUE,model=model,family=family,covariatematrix=covariatematrix)
		cat("\n",phen,name.suffix,covariates.suffix,"@",chr," fit.0$mapx: ",fit.0$maxp,"\n",sep="")
		fit.permute<-hfit(h, permute=permute,verbose=TRUE,model=model,family=family,covariatematrix=covariatematrix)
		cat("\n",phen,name.suffix,covariates.suffix,"@",chr," fit.permute$permdata$p01: ",fit.permute$permdata$p01,"\n",sep="")
		cat(     phen,name.suffix,covariates.suffix,"@",chr," fit.permute$permdata$p05: ",fit.permute$permdata$p05,"\n",sep="")

		columns.selected= (snps.selected.chromosomes==chr)
		l<-list(POSITION=snps.selected.cM[columns.selected],
			text=snps.selected.names[columns.selected])

		# Plot of F statistics
		happyplot(fit.0,
			main=paste(individuals.subset,": ",phen,name.suffix,covariates.suffix,"@chr",chr,sep=""),
			sub=paste("p01:",round(fit.permute$permdata$p01,2)," ",
				  "p05:",round(fit.permute$permdata$p05,2)," ",
					date(),
			     sep=""), pch=3
		)
		abline(h=log(fit.permute$permdata$p05)/log(10),v=0,col="green",lty=3)
		abline(h=log(fit.permute$permdata$p01)/log(10),v=0,col="green",lty=2)

		# Empirical significance
		happyplot(fit.permute, labels=l, pch=3,
			sub=paste(individuals.subset,":",phen,name.suffix,covariates.suffix,"@chr",chr))
		write.csv(file=paste(outputdir,"/","happy_",individuals.subset,"_",phen, name.suffix,
			covariates.suffix,"_chr_",chr,"_maxLodP_",fit.0$maxp,".csv",sep=""), x=fit.0$table)
		if(permute>0) {
			write.csv(file=paste(outputdir,"/","happy_",individuals.subset,"_",phen,
						name.suffix,covariates.suffix,"_chr_",chr,"_maxLodP_",fit.permute$maxp,"_permutation.csv",sep=""),
				  x=fit.permute$permdata$permutation.pval)
		}
	}
}

analyse.all.chromosomes.together<-function(
		phen,individuals.subset,
		data.phenotypes.source,
		phenotypes.collection,
		generations,model,family,
		data.covariates.source=NULL,
		data.covariates=NULL,
		name.suffix="",verbose=FALSE,
		vlines.chr.col="darkgreen",
		project.name="",
		overwrite=TRUE,
		inputdir="./",outputdir="./", missing.code="NA") {

	cat("analyse.all.chromosomes.together: 1\n")

	if (! data.phenotypes.source %in% names(phenotypes.collection)) {
		stop("Specification of data.phenotype.source (",data.phenotypes.source,") not found. ",
		     "It should be one of {",paste(names(phenotypes.collection),collapse=", ",sep=""),"}.\n")
	}
	read.table.phenotypes <- phenotypes.collection[[data.phenotypes.source]]

	if (is.null(read.table.phenotypes)) {
		stop("Could not retrieve sensible phenotypes from phenotypes.collection[[",data.phenotypes.source,"]] .\n")
	}

	cat("analyse.all.chromosomes.together: 2\n")

	if (!is.null(data.covariates)) {
		if (length(data.covariates) != length(data.covariates.source)) {
			stop("Length of covariate-names differs from length of sources for covariates.\n")
		}
	}

	cat("analyse.all.chromosomes.together: 3\n")

	covariates.suffix<-paste("_covars_",ifelse(is.null(data.covariates),"none",paste(data.covariates,collapse=",",sep="")),sep="")
	cat("\n"); cat("Investigating",data.phenotypes.source,"[,",phen,"]",name.suffix,"at times",individuals.subset,"and",covariates.suffix,"\n")

	if ("weight.6m" %in% data.covariates && "6m" != individuals.subset) {
		cat("weight.6m specified as covariate, need to work also with that subset.\n")
		return(FALSE);
	}
	if ("weight.3m" %in% data.covariates && "3m" != individuals.subset) {
		cat("weight.3m specified as covariate, need to work also with that subset.\n")
		return(FALSE);
	}

	# The input file is the same as for single and multiple covariates
	fname<-paste(inputdir,"/","happy_project_",data.phenotypes.source,"_",individuals.subset,"_",phen,name.suffix,".input",sep="")
	if (!file.exists(fname)) {
		cat("  cannot find input file expected at '",fname,"\n")
		return(FALSE);
	}

	ofile.pdf<-paste(outputdir,"/","analysis_happy_project_",simpler.name(project.name),"_phen_",phen,name.suffix,"_subset_",
				individuals.subset,covariates.suffix,"_chr_","together","_model_",model,"_family_",family,"_permute_",permute,".pdf",sep="")
	ofile.csv<-paste(outputdir,"/","analysis_happy_project_",simpler.name(project.name),"_phen_",phen,name.suffix,"_subset_",
				individuals.subset, covariates.suffix,"_chr_","together","_model_",model,"_family_",family,"_permute_",permute,".csv",sep="")
	if ( (!overwrite) && file.exists(ofile.pdf) && file.exists(ofile.csv)) {
		cat("\nSkipping: Results are already existing: ",ofile.pdf,", ",ofile.csv,"\n",sep="")
		return(TRUE)
	}

	if (verbose) cat("I: reading happy file.\n")
	h<-happy(datafile=fname,allelesfile=markers.filename,generations=generations,phase="unknown",file.format="happy",missing.code=missing.code)
	if (is.null(h$subjects)) stop("Happy datafile '",fname,"' did not produce h$subjects.\n",sep="")


	# Every chromosome may have a different set of individuals
	covariatematrix.raw<-NULL
	covariatematrix <- NULL
	if (length(data.covariates)>0) for(d.pos in 1:length(data.covariates)) {
		if (debug) cat("I: retrieving covariate data #",d.pos," (",data.covariates[d.pos],").\n")
		d       <-data.covariates[d.pos]
		d.source<-data.covariates.source[d.pos]
		if (debug) cat("I: from source ",d.source,"\n")
		pc<-phenotypes.collection[[d.source]]
		if (is.null(pc)) stop("Could not retrieve phenotype source '",d.source,"'.\n")
		if (debug) cat("I: successfully retrieved covariates.\n")
		if (is.null(rownames(pc))) stop("Covariates data does not have rownames.\n")
		if (is.null(colnames(pc))) stop("Covariates data does not have colnames.\n")
		if (! d %in% colnames(pc)) stop("Could not find colname ",d," for source ",d.source,". Available: ",colnames(pc),".\n")
		if (debug) cat("I: fetching correct colum \n")
		pc.right.column<-as.matrix(pc[,d,drop=F])
		if (!is.matrix(pc.right.column)) {
			cat("I: pc.right.column: "); print(pc.right.column)
			stop("expected matrix for 'pc.right.column'.\n")
		}
		#pc.cov<-pc[h$subjects,d,drop=FALSE]
		if (debug) cat("I: assigning individuals shared between data sets\n")
		pc.cov<-sapply(h$subjects,function(X,a,p){
			if (is.null(X)) stop("X is null")
			if (is.null(p)) stop("missing names of array.\n")
			if (is.null(a)) stop("missing array.\n")

			if (is.matrix(a)) {
				if(ncol(a)>1) stop("a has more than a single column, i.e. ",ncol(a),"\n")
				if (X %in% p) return(a[X,])
			} else {
				if (X %in% p) return(a[X])
			}
			#cat("W: Could not find individual '",X,"' in covariates.\n",sep="")
			return(NA)
		},a=pc.right.column,p=rownames(pc.right.column))
		if (debug) cat("I: rewriting as matrix\n")
		pc.cov<-as.matrix(pc.cov)
		if (debug) cat("I: reassigning column vector\n")
		colnames(pc.cov)<-d
		#cat("I: pc.cov: "); print(pc.cov)
		if (debug) cat("I: find if set is good enough\n")
		if (sum(is.na(pc.cov))>0.7*nrow(pc.cov)) {
			cat("W: too many covariates ",d.source,"[,",d,"] are NA. (",sum(!is.na(pc.cov))," of ",nrow(pc.cov),").\n",sep="")
			cat("   h$subjects: ");print(h$subjects)
			cat("   rownames(pc.cov): ");print(rownames(pc.cov))
			return(FALSE);
		} else {
			cat("I: acceptably many covariates of ",d.source,"[,",d,"] are NA. (",sum(!is.na(pc.cov))," of ",nrow(pc.cov),").\n",sep="")
			#cat("Covariates: "); print(pc.cov)
		}
		#if (debug) {cat("dimnames(pc.cov)):"); print(dimnames(pc.cov))}
		if (is.null(covariatematrix.raw)) {
			covariatematrix.raw<-pc.cov
		} else {
			covariatematrix.raw<-cbind(covariatematrix.raw,pc.cov)
		}
	}

	if (is.null(covariatematrix.raw)) {
		cat("I: No covariates assigned.\n")
		covariatematrix<-NULL
	} else {
		cat("I: built covariate matrix, dim:\n"); print(dim(covariatematrix.raw))
		covariatematrix<-as.matrix(covariatematrix.raw)
		cat("I: For phen '",phen,"': Raw covariate matrix for {",paste(data.covariates,collapse=", ",sep=""),"} ",
		                                           " of source '",data.covariates.source,"':\n",sep="")
		print(covariatematrix.raw[1:10,])
		cat("I: For phen '",phen,"': Covariate matrix for {",paste(data.covariates,collapse=", ",sep=""),"} ",
		                                           " of source '",data.covariates.source,"':\n",sep="")
		print(covariatematrix[1:30,])

		if (!is.numeric(covariatematrix)) {
			stop("The covariatematrix is not numeric! for covariates ",
				paste(data.covariates,collapse=","),
				" from ",
				paste(data.covariates.source,collapse="",sep=""),".\n",sep="")
		}
	}

	fit.0<-hfit(h, permute=0, verbose=verbose, model=model, family=family, covariatematrix=covariatematrix)
	cat("\n",phen,name.suffix,covariates.suffix,"@","all"," fit.0$mapx: ",fit.0$maxp,"\n",sep="")

	fit.permute<-NULL
	if (permute > 0) {
		fit.permute<-hfit(h, permute=permute,verbose=TRUE,model=model,family=family,covariatematrix=covariatematrix)
		cat("\n",phen,name.suffix,covariates.suffix,"@","all"," fit.permute$permdata$p01: ",fit.permute$permdata$p01,"\n",sep="")
		cat(     phen,name.suffix,covariates.suffix,"@","all"," fit.permute$permdata$p05: ",fit.permute$permdata$p05,"\n",sep="")
	}

	cat("Writing PDF to file '",ofile.pdf,"'\n") ; pdf(ofile.pdf,width=50,height=9)
	happyplot(fit.0,labels=T,together=TRUE,vlines.chr.lwd=3,vlines.chr.col=vlines.chr.col,
		main=paste("AIL for phenotype '",phen,"' on subset '",individuals.subset,"'",sep=""),
		sub=paste( ifelse(is.null(covariatematrix),"No covariates",paste("Covariates ",paste(data.covariates,collapse=",",sep=""))),
		           ", ",model," model of family ",family,sep="")
	)
	write.csv(file=ofile.csv, x=fit.0$table)
	if (!is.null(fit.permute)) {
		happyplot(fit.permute,labels=TRUE,together=TRUE,
			main=paste("Permutation data for AIL phenotype",phen),
			sub=paste(ifelse(is.null(covariatematrix),"No covariates",paste("Covariates ",paste(data.covariates,collapse=",",sep=""))),
		           ", ",model," model of family ",family,sep="")
		)
		write.csv(file=paste(outputdir,"/","happy_project_",simpler.name(project.name),"_subset_",individuals.subset,"_phen_",phen,
				name.suffix,covariates.suffix,"_chr_","together","_maxLodP_",fit.permute$maxp,"_permutation.csv",sep=""),
			  x=fit.permute$permdata$permutation.pval)
	}
	dev.off() ; cat("Created figure at '",ofile.pdf,"'\n",sep="")
	return(TRUE);
}
