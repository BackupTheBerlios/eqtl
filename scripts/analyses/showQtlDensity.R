
doc <- "

=head1 NAME

showQtlDensity.R - graphical overview on the number of eQTL at various LOD scores

=head1 SYNOPSIS 

to be sourced within an R shell

=head1 DESCRIPTION

The script gathers code for the display of expression QTL
densities on a chromosomal level.

It is motivated by the observed shift in hot spots for QTL
when raising the threshold for LOD scores. Here, the selection
criterion and the chromosomal distribution are shown together.

=head1 AUTHORS

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>,
Ann-Kristin Grimm <grimm@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, Germany, 2009

=cut

"

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
show.qtl.density<-function(qtls,chromosome,cov, lod.thresholds=NULL, 
				interval.length=15, breaks=NULL,
				show.empty=FALSE, # continue even if there are no chromosomes
				show.absolute=FALSE, # show absolute values
				show.logarithmic=TRUE,
				col=NULL,label.marker=NULL
) {
  if (is.null(lod.thresholds)) {
    lod.thresholds<-3*(1:5)
  }
  i<-NULL
  b<-breaks
  counts.per.threshold<-rep(0,length(lod.thresholds))
  counts<-rep(0,length(lod.thresholds))
  for(thres.pos in 1:length(lod.thresholds)) {
    thres<-lod.thresholds[thres.pos]
    qtls.chrom<-qtls[chromosome==qtls[,"Chromosome"]& thres<=qtls[,"LOD"],,drop=FALSE]
    counts.per.threshold[thres.pos]<-nrow(qtls.chrom)
    #print(qtls.chrom)
    counts[thres.pos]<-nrow(qtls.chrom)
    d<-my.density(qtls.chrom[,"Mbp_Peak"],breaks=b,interval.length)
    if (is.null(b)) {
      b<-d$breaks
      interval.length=NA
    }
    i<-cbind(i,d$distribution)
    if (!show.absolute & 0<counts.per.threshold[thres.pos]) {
      i<-i/counts.per.threshold[thres.pos]
    }
  }

  if (show.logarithmic) {
	i<-log(1+i)
  }
  max.cM<-max(max(qtls[,"Mbp_Peak"]),max(b)+1)
  rownames(i)<-b
  colnames(i)<-lod.thresholds
  #print(t(i))
  image(y=c(b,max.cM),ylab="Marker(Mbp)",
	x=c(lod.thresholds,max(lod.thresholds)+1),xlab="min LOD",
	z=-t(i)[,nrow(i):1], # painting from top to bottom, like biologists do
	axes=FALSE,col=col)
  axis(1,lod.thresholds)
  if(is.null(label.marker)){
    label.marker<-c(b,max.cM)	
  }
  axis(2,c(b,max.cM),labels=rev(label.marker),las=1,cex.axis=0.5)
  test<-lod.thresholds+((lod.thresholds[2]-lod.thresholds[1])/2)
  axis(3,test,labels=counts,cex.axis=0.5,tick=F)
  axis(3,lod.thresholds,labels=F)
  title(main = paste("Chr",chromosome,"cov", cov), font.main = 4)
  #print(dimnames(i))
  print(counts)
  counts.per.threshold
}


# a function doing some layout of the plot
# to work on multiple chromosomes

show.multiple.qtl.densities <- function(data,
					lod.thresholds=NULL,
					show.absolute=FALSE,
					interval.length=30,breaks=NULL,
					show.logarithmic=TRUE,
					marker=NULL,
					max.num.chrom.per.line=5,col=heat.colors(100)) {

  chromosomes<-unique(sort(data[,"Chromosome"]))
  covariates<-unique(sort(data[,"covariates"]))
  chrom.num.horizontal<-min(max.num.chrom.per.line,length(chromosomes))
  chrom.num.vertical<-1+length(chromosomes)%/%max.num.chrom.per.line
  #par.orig<-par(mfrow=c(chrom.num.vertical,chrom.num.horizontal))
  sapply(chromosomes,function(chr){
    cat("Performing on chromosome ",chr,"\n")
    chrom<-which(marker[,"chr"]==chr)
    break.values<-marker[marker[,"chr"]==chr,"Mbp"]
    labels<-c("start",marker[marker[,"chr"]==chr,"marker"],"end")
    marker.expression<-lapply(chrom,function(x){
      print(x)
      marker.tmp<-marker[x,"marker"]
      cmorgan.tmp<-marker[x,"Mbp"]
      return(paste(marker.tmp, '(',cmorgan.tmp,')'))	
    })
    labels<-c(marker.expression,"end")
    cat("break.values: ",break.values,"\n")
    pdf(file=paste("/nfshome/grimm/gitEqtlRepo/data/graphics/Chr_",chr,"_trans.pdf",sep=""))
    par.orig<-par(mfrow=c(1,length(covariates)/2))
    sapply(covariates,function(cov){
       data.tmp<-data[data[,"covariates"]==cov,]
    #   cat("Performing on covariate ",cov,"\n")
       show.qtl.density(data.tmp,chr,cov,show.absolute=show.absolute,interval.length=interval.length,breaks=break.values,col=col,lod.thresholds=lod.thresholds,show.logarithmic=show.logarithmic,label.marker=labels)
       
    })	
    par(par.orig)
    dev.off()
    cat("break.values: ",break.values,"\n")
  })
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
colnames(m)<-c("Chromosome","Mbp_Peak","LOD")


# now calling our function to see it works
#show.multiple.qtl.densities(m,show.absolute=FALSE)

require(RMySQL)
#example data for chromosome 1
con <- dbConnect(MySQL(),user="userqtl", password="", dbname="eqtl_rostock_eae", host="127.0.0.1")
rs<-dbGetQuery(con, "select covariates, Chromosome, Mbp_Peak, LOD from qtl where covariates in ('','eae_add','sud_add','wl0_add','dud_add','totalIgG_add') and LOD>Quantile and LOD>3.5 and ((cis=1 and cis_dist>20000000) or cis=0 )order by covariates,Chromosome, LOD")
#rs<-dbGetQuery(con, "select covariates, Chromosome, Mbp_Peak, LOD from qtl where covariates in ('','eae_add','sud_add','wl0_add','dud_add','totalIgG_add') and LOD>Quantile and LOD>3.5 and cis=1 and cis_dist<20000000 order by covariates,Chromosome, LOD")
#rs<-dbGetQuery(con, "select covariates, Chromosome, Mbp_Peak, LOD from qtl where covariates in ('','eae_add','sud_add','wl0_add','dud_add','totalIgG_add') and LOD>Quantile and LOD>3.5 order by covariates,Chromosome, LOD")
#get marker
marker<-dbGetQuery(con, "select marker,Mbp,chr from map")
dbDisconnect(con)
rs[,"Mbp_Peak"]<-round(rs[,"Mbp_Peak"])
cols=heat.colors(255)
#covariates<-unique(rs[,"covariates"])
#a<-lapply(covariates,function(x){
#   data.to.plot<-rs[which(rs[,"covariates"]==x),2:ncol(rs)]
#   x11()
#   show.multiple.qtl.densities(data.to.plot,show.absolute=TRUE,col=cols,interval.length=50,lod.thresholds=(0:10*3),show.logarithmic=TRUE)
#}
#)
breaks<-c(0,20,40,60,80)
chromosomes<-unique(rs[,"Chromosome"])
lapply(chromosomes,function(x){
   data.to.plot<-rs[which(rs[,"Chromosome"]==x),]
#   x11()
   #show.multiple.qtl.densities(data.to.plot,show.absolute=TRUE,col=cols,interval.length=15,lod.thresholds=(0:10*3),show.logarithmic=TRUE)
   show.multiple.qtl.densities(data.to.plot,show.absolute=TRUE,col=cols,interval.length=NA,breaks=breaks,marker=marker,lod.thresholds=(0:10*3),show.logarithmic=TRUE)
}
)

#show.multiple.qtl.densities(rs,show.absolute=TRUE,col=cols,interval.length=15,lod.thresholds=(0:10*3),show.logarithmic=TRUE)

