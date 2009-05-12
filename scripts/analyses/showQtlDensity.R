

# show QtlDensity.R
#
# The script gathers code for the display of expression QTL
# densities on a chromosomal level.
#
# It is motivated by the observed shift in hot spots for QTL
# when raising the threshold for LOD scores. Here, the selection
# criterion and the chromosomal distribution are shown together.
#
# Steffen Möller, University of Lübeck, 2009

# substituting the 'hist' function to derive density data
my.density <-function(data,breaks=NULL,interval.length=NA,max.value=NA) {
  if(!is.vector(data)) {
    stop("my.density: data should be an array.")
  }
  if(!is.null(breaks) & !is.na(interval.length)) {
    stop("my.density: cannot have both breaks and interval.length set.")
  }
  else if (!is.na(interval.length)) {
    max.observed<-max.value
    if (is.na(max.observed)) {
      max.observed<-max(data)
    }
    breaks<-0:(max.observed%/%interval.length)*interval.length
  }
  else if (is.null(breaks) & is.na(interval.length)) {
    stop("my.density: not yet implemented, specify interval.length or breaks")
  }
  counts<-rep(0,length(breaks))
  assignment<-sapply(data,function(X,breaks){sum(X>breaks)},breaks=breaks)
  distrib<-sapply(1:length(breaks),function(X,data){sum(X==assignment)},data=assignment)
  return(list(breaks=breaks,assignment=assignment,distribution=distrib))
}

# qtl - quantitative trait locus
show.qtl.density<-function(qtls,chromosome, lod.thresholds=NULL, 
				interval.length=15, breaks=NULL,
				show.empty=FALSE, # continue even if there are no chromosomes
				show.absolute=FALSE # show absolute values
) {
  if (is.null(lod.thresholds)) {
    lod.thresholds<-3*(1:5)
  }
  i<-NULL
  b<-breaks
  counts.per.threshold<-rep(0,length(lod.thresholds))
  
  for(thres.pos in 1:length(lod.thresholds)) {
    thres<-lod.thresholds[thres.pos]
    qtls.chrom<-qtls[chromosome==qtls[,"Chromosome"]& thres<=qtls[,"LOD"],,drop=FALSE]
    counts.per.threshold[thres.pos]<-nrow(qtls.chrom)
    print(qtls.chrom)
    d<-my.density(qtls.chrom[,"cMorgan"],breaks=b,interval.length)
    if (is.null(b)) {
      b<-d$breaks
      interval.length=NA
    }
    i<-cbind(i,d$distribution)
    if (!show.absolute & 0<counts.per.threshold[thres.pos]) {
      i<-i/counts.per.threshold[thres.pos]
    }
  }
  rownames(i)<-b
  colnames(i)<-lod.thresholds
  print(t(i))
  image(y=b,ylab="cMorgan",
	x=lod.thresholds,xlab="min LOD",
	z=t(i)[,nrow(i):1],axes=FALSE)
  axis(1,lod.thresholds)
  axis(2,b,labels=rev(b))
  title(main = paste("Chromosome",chromosome), font.main = 4)
  #print(dimnames(i))
  counts.per.threshold
}


# a function doing some layout of the plot
# to work on multiple chromosomes

show.multiple.qtl.densities <- function(data,show.absolute=FALSE,max.num.chrom.per.line=5) {

  chromosomes<-unique(sort(data[,1]))
  
  chrom.num.horizontal<-min(max.num.chrom.per.line,length(chromosomes))
  chrom.num.vertical<-1+length(chromosomes)%/%max.num.chrom.per.line

  par.orig<-par(mfrow=c(chrom.num.vertical,chrom.num.horizontal))
  sapply(chromosomes,function(chr){
    cat("Performing on chromosome ",chr,"\n")
    show.qtl.density(m,chr,show.absolute=show.absolute)
  })
  par(par.orig)
}

# some stupid test data, so the script
# does something in the first place
m<-matrix(c(
    1,10,3.4,
    1,11,5,
    1,70,9,
    2,50,6,
    2,50,8
 ),
  ncol=3,byrow=TRUE)
colnames(m)<-c("Chromosome","cMorgan","LOD")


# now calling our function to see it work
show.multiple.qtl.densities(m,show.absolute=FALSE)