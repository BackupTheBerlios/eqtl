# Read in markers
read.markers<-function(file="markers.txt") {
	ls<-readLines(file)
	ls.marker<-grep(pattern="^marker ",ls,value=T)
	ls.marker.split<-strsplit(ls.marker,split=" +",perl=T)
	r<-sapply(ls.marker.split,function(X) X)
	r.2<-t(r[c(2,4,5),])
	if (any(duplicated(r.2[,1]))) stop("Marker file shows duplicated marker name:",r[duplicated(r.2[,1]),],"\n")
	return(r.2)
}

# This function will read in all result files of a particular folder
# and get the LOD scores into a big table.  
prepare.matrix.from.files<-function(files,subset=NULL) {
	ff<-files
	if (!is.null(subset)) ff<-files[subset]

	m<-NULL ; r<-NULL

	for(f in ff) {
		cat(f,"\n")
		r<-read.table(f,sep=",",header=T)
		r.values<-as.numeric(r[,"additive.logP"])
		m<-cbind(m,r.values)
	}
	if (1==length(files)) dim(m)<-c(length(m),1)
	colnames(m)<-basename(ff)
	cM  <-as.numeric(r[,"cM"])
	marker<-r[,"marker"]
	rownames(m)<-marker
	cM.2<-c(cM[1],cM[1:(length(cM)-1)])
	chr.sep<-which(apply(rbind(cM,cM.2),2,function(X){X[1]<X[2]}))
	attr(m,"cM")<-cM
	attr(m,"chr.sep")<-chr.sep

	a<-strsplit(colnames(m),split="analysis_happy_project_|_subset_|_phen_|_covars_|_chr_|_model_|_permute_|.csv",perl=TRUE)
	m.annotation<-sapply(a,function(X) X)
	rownames(m.annotation)<-c("path","project","phen","subset","covars","chr","model","permute")
	colnames(m.annotation)<-colnames(m)
	attr(m,"annotation") <- t(m.annotation)

	return(m)
}


# This function will take the big matrix and prepare plots
# for it
prepare.figure.from.matrix<-function(m, subset=NULL,
				    outputfile=NA, probs=c(0,0.05,0.25,0.5,0.75,0.95,1),
				    add=FALSE, lty=1, verbose=T, col=rainbow(length(probs)),
				    chr.sep.col="gray", draw.legend=T, main=NULL, sub=NULL,
				    type="s", legend.pos="left", lwd=1) {
	cat("I: Preparing figure 1\n")

	m.annotation<-attr(m,"annotation")
	chr.sep<-attr(m,"chr.sep")
	if (!is.null(subset)) {
		if (nrow(m)<length(subset)) stop("nrow(m)==",nrow(m),"<",length(subset),"=length(subset)\n")
		# m is of marker x covariates
		m<-m[,subset]
		if (nrow(m.annotation)<length(subset)) stop("nrow(m.annotation)==",nrow(m.annotation),"<",length(subset),"=length(subset)\n")
		m.annotation<-m.annotation[subset,]
		cat("I: subsetted matrix to nrow(m)=",nrow(m),".\n",sep="")
	}
	cat("I: Preparing figure 2\n")

	plot.me<-apply(m,1,quantile,probs=probs)

	if(length(probs)==1) dim(plot.me)<-c(1,length(plot.me))

	if (verbose) {cat("dim(plot.me): "); print(dim(plot.me))}

	if (!add) {
		plot.new()
		plot.window(xlim=c(1,nrow(m)),ylim=c(0,max(plot.me)),col=col[1],lty=lty)
	}

	for(i in 1:nrow(plot.me)) lines(plot.me[i,],col=col[i],type=type,lty=lty,lwd=lwd)

	if (!add) {
		if (draw.legend) legend(x=legend.pos,legend=paste(as.character(probs*100),"%",sep=""),col=col,fill=col)
		# circumventing Happy bug
		chr.sep<-chr.sep[1:(length(chr.sep)/2)*2-1]
		chr.names<-c(1:length(chr.sep),"X")
		# plot vertical lines
		abline(v=chr.sep,col=chr.sep.col,lty=5,lwd=2)
		max.plot.me<-max(plot.me,na.rm=T)
		if (is.na(max.plot.me)) {
			stop("NA found as value to plot for phen ",phen,"\n")
		} else if (is.nan(max.plot.me)) {
			stop("Nan found as value to plot for phen ",phen,"\n")
		} else if (is.infinite(max.plot.me)) {
			stop("Infinite found as value to plot for phen ",phen,"\n")
		}
		# label chromosomes
		if(verbose) cat("Plotting chromosome names: ",paste(chr.names,collapse=",",sep=""),"\n",sep="")
		text(x=(c(chr.sep,ncol(plot.me))+c(0,chr.sep))/2,y=max(plot.me),labels=chr.names,col="black")
		axis(2,labels=T)
		if (is.null(main)) main="Percentiles for LogP values"
		title(main=main, sub=sub, xlab="Marker Position",ylab="-LogP")
	}
}
