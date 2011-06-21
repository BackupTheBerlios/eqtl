# This script will read in all result files of a particular folder
# and get the LOD scores into a big table.  
folder<-"outputs/baines" 

if (!file.exists(folder)) {
	stop("Folder ",folder," does not exist.\n",sep="")
}

files<-dir(folder,pattern="*.csv",full.names=T)
outputfile=NA
probs=1
add=FALSE
lty=1
verbose=T
col=NULL

prepare.figure.for.files<-function(files, outputfile=NA, probs=c(0,0.05,0.25,0.5,0.75,0.95,1),
					add=FALSE, lty=1, verbose=T, col=rainbox(length(probs)),
					chr.sep.col="gray", draw.legend=T, main=NULL, sub=NULL,
					type="s", legend.pos="left", lwd=1) {
	m<-NULL ; r<-NULL

	for(f in files) {
		cat(f,"\n")
		r<-read.table(f,sep=",",header=T)
		r.values<-as.numeric(r[,"additive.logP"])
		m<-cbind(m,r.values)
	}
	if (1==length(files)) dim(m)<-c(length(m),1)
	colnames(m)<-basename(files)
	cM<-as.numeric(r[,"cM"])
	cM.2<-c(cM[1],cM[1:(length(cM)-1)])
	chr.sep<-which(apply(rbind(cM,cM.2),2,function(X){X[1]<X[2]}))
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
		# label chromosomes
		if(verbose) cat("Plotting chromosome names: ",paste(chr.names,collapse=",",sep=""),"\n",sep="")
		text(x=(c(chr.sep,ncol(plot.me))+c(0,chr.sep))/2,y=max(plot.me),labels=chr.names,col="black")
		axis(2,labels=T)
		if (is.null(main)) main="Percentiles for LogP values"
		title(main=main, sub=sub, xlab="Marker Position",ylab="-LogP")
	}
}

outputfilename= paste("summary_",basename(folder),".pdf",sep="")
if(!is.na(outputfile)) pdf(outputfile)
prepare.figure.for.files(files)
if (!is.na(outputfile)) dev.off()


m.binary.95<-apply(m,1,function(X,thres){
	X>=quantile(X,probs=thres)
},thres=0.95)

grays<-palette(gray(seq(0,.9,len=25)))
#m.binary.95.cov<-cov(m.binary.95)
#image(m.binary.95.cov,col=grays)
m.t.cor<-cor(t(m))
pdf(paste("summary_",basename(folder),"_cor.pdf",sep=""))
image(abs(m.t.cor),col=grays,main="Absolute correlation between LogP values")
dev.off()



# Reading code
codes<-read.table("data/baines/codes/code_otu_unique_selected.txt",sep="\t",header=F,stringsAsFactors=F)

gram.negative<-c("Root;Bacteria;Proteobacteria;Alphaproteobacteria;Sphingomonadales;Sphingomonadaceae;Sphingomonas",
	"Root;Bacteria;Proteobacteria;Betaproteobacteria;Burkholderiales;Alcaligenaceae;Achromobacter",
	"Root;Bacteria;Proteobacteria;Gammaproteobacteria;Xanthomonadales;Xanthomonadaceae;Stenotrophomonas",
	"Root;Bacteria;Proteobacteria;Betaproteobacteria;Burkholderiales;Burkholderiaceae;Burkholderia",
	"Root;Bacteria;Proteobacteria;Betaproteobacteria;Burkholderiales;Burkholderiaceae;Ralstonia",
	"Root;Bacteria;Deferribacteres;Deferribacteres;Deferribacterales;Deferribacteraceae;Mucispirillum",
	"Root;Bacteria;Proteobacteria;Alphaproteobacteria;Sphingomonadales;Sphingomonadaceae",
	"Root;Bacteria;Proteobacteria;Epsilonproteobacteria;Campylobacterales;Helicobacteraceae;Helicobacter",
	"Root;Bacteria;Proteobacteria;Gammaproteobacteria;Enterobacteriales;Enterobacteriaceae;Pantoea",
	"Root;Bacteria;Proteobacteria;Gammaproteobacteria;Pseudomonadales;Pseudomonadaceae;Pseudomonas",
	"Root;Bacteria;Proteobacteria;Gammaproteobacteria;Xanthomonadales;Xanthomonadaceae;Stenotrophomonas",
	"Root;Bacteria;Proteobacteria;Alphaproteobacteria;Rhodobacterales;Rhodobacteraceae;Paracoccus"
)

gram.positive<-c("Root;Bacteria;Firmicutes;Clostridia;Clostridiales;Ruminococcaceae;Ruminococcus",
	"Root;Bacteria;Firmicutes;Erysipelotrichi;Erysipelotrichales;Erysipelotrichaceae",
	"Root;Bacteria;Deinococcus-Thermus;Deinococci;Deinococcales;Deinococcaceae;Deinococcus",
	"Root;Bacteria;Firmicutes;Bacilli;Bacillales;Staphylococcaceae;Jeotgalicoccus",
	"Root;Bacteria;Firmicutes;Bacilli;Lactobacillales;Enterococcaceae;Enterococcus",
	"Root;Bacteria;Actinobacteria;Actinobacteria;Actinobacteridae;Actinomycetales;Corynebacterineae",
	"Root;Bacteria;Firmicutes;Bacilli;Lactobacillales;Carnobacteriaceae;Carnobacteriaceae 1;Dolosigranulum",
	"Root;Bacteria;Firmicutes;Bacilli;Lactobacillales;Enterococcaceae;Enterococcus",
	"Root;Bacteria;Firmicutes;Bacilli;Lactobacillales;Lactobacillaceae;Lactobacillus",
	"Root;Bacteria;Firmicutes;Bacilli;Lactobacillales;Streptococcaceae;Lactococcus",
	"Root;Bacteria;Firmicutes;Bacilli;Lactobacillales;Streptococcaceae;Streptococcus",
	"Root;Bacteria;Firmicutes;Clostridia;Clostridiales;Lachnospiraceae"
)

gram.variable<-c("Root;Bacteria;Actinobacteria;Actinobacteria;Actinobacteridae;Actinomycetales;Micrococcineae;Micrococcaceae;Arthrobacter")


tax2file<-function(X) {
	paste("outputs/baines.unique.otu/analysis_happy_project_baines.unique.otu_phen_",
	      codes[codes[,2] %in% X,1],
	      "_subset_all_covars_none_chr_together_model_additive_permute_0.csv", sep="")
}

gram.positive.files<-tax2file(gram.positive)
gram.negative.files<-tax2file(gram.negative)

pdf("summary_influence_taxa.pdf")

col<-c("red","green")

prepare.figure.for.files(gram.positive.files,probs=c(.9),col=col[1],  lty=1,chr.sep.col="gray",draw.legend=F, type="h",
				main="Comparison of response to gram+ and gram- bacteria",sub="The 90% quantile of the LogP values are shown.")
prepare.figure.for.files(gram.negative.files,probs=c(.9),col=col[2],lty=1,add=T,type="s",lwd=3)
legend(x="left",legend=c("gram+","gram-"),col=col,fill=col)

prepare.figure.for.files(gram.positive.files,probs=c(1),col=col[1],  lty=1,chr.sep.col="gray",draw.legend=F, type="h",
				main="Comparison of response to gram+ and gram- bacteria",sub="The maximal LogP values are shown.")
prepare.figure.for.files(gram.negative.files,probs=c(1),col=col[2],lty=1,add=T,type="s",lwd=3)
legend(x="left",legend=c("gram+","gram-"),col=col,fill=col)

dev.off()
