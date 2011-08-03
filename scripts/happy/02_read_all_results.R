
source("02_func_all.R")

folder<-"outputs/mohan" 
folder<-"outputs/baines.selected.above.90" 
folder<-"outputs/basicWbaines.selected.above.90" 
if (!file.exists(folder)) {
	stop("Folder ",folder," does not exist.\n",sep="")
}

files<-dir(folder,pattern="*.csv",full.names=T)
outputfile    <- NA
outputfilename<- paste("summary_",basename(folder),".pdf",sep="")
probs         <- 1
add           <- FALSE
lty           <- 1
verbose       <- T
col           <- NULL

m<-prepare.matrix.from.files(files)
m.annotation <- attr(m,"annotation")

phens<-grep("^Root.Bacteria",unique(m.annotation[,"phen"]),invert=T,value=T)

if(!is.na(outputfilename)) {
	cat("I: Plotting to file '",outputfilename,"'.\n",sep="")
	pdf(outputfilename,width=14,title=paste("Data from folder '",basename(folder),"'",sep=""))
}
for(phen in phens) {
	s <- which(m.annotation[,"phen"]==phen & m.annotation[,"project"]=="basicWbaines.selected.above.90",useNames=F)
	if (length(s)==0) {
		cat("W: no data for phen '",phen,"'.\n",sep="");
		next;
	}
	names(s)<-NULL
	prepare.figure.from.matrix(m,
	                           subset=s,
				   main=paste("Performance of covariates for phen ",phen,sep=""),
	                           sub=paste("Experiment ID ",folder," with ",sum(s)," entries",sep=""))
}
if (!is.na(outputfilename)) dev.off()

for(phen in phens) {
	outputfilename<- paste("summary_",basename(folder),"_heatmaps_phen_",phen,"_reordered.jpg",sep="")
	if(!is.na(outputfilename)) {
		cat("I: Plotting to file '",outputfilename,"'.\n",sep="")
		#pdf(outputfilename,width=10,title=paste("Data from folder '",basename(folder),"' for phen ",phen,sep=""))
		jpeg(outputfilename,width=1280,height=1024)
	}
	cat("I: Preparing heatmaps for phenotype ",phen,"\n",sep="")
	s <- which(m.annotation[,"phen"]==phen & m.annotation[,"project"]=="basicWbaines.selected.above.90",
		   useNames=F)
	if (length(s)==0) {
		cat("W: no data for phen '",phen,"'.\n",sep="");
		next;
	}
	names(s)<-NULL
	colnames(m)<-sapply(strsplit(x=m.annotation[,"covars"],split="Root_Bacteria_"),function(X)X[2])
	# Heatmap of plain data
	if (T) {
		cat("I: plain\n")
		heatmap.2(m[,s],Rowv=F,Colv=F,scale="none",dendrogram="none",trace="none",cexRow=0.15,cexCol=0.4,
			main=paste("Plain representation for phenotype ",phen,sep=""),
			xlab="bacteria",ylab="markers")
	}
	if (F) {
		# Heatmap with column and rows clustered
		cat("I: reordered\n")
		heatmap.2(m[,s],Rowv=T,Colv=T,scale="none",dendrogram="none",trace="none",cexRow=0.15,cexCol=0.4,
			main=paste("Reordered data for phenotype ",phen,sep=""),
			xlab="bacteria",ylab="markers")
	}
	if (!is.na(outputfilename)) dev.off()
}





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
