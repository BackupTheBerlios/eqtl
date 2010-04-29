
# This script expects the data to arrive from Andreia's Excel setup.
# It describes a series of manual steps to be performed in OpenOffice 
# to derive the tab-separated files and then the subsequent commands
# to prepare the input for happy.

#  C O N T R O L

# set to TRUE if data needs to be prepared, too
data.prepare<-FALSE
data.binary<-F
data.covariates<-c("sex,weight.6m")
model<-"additive"

#  P A R A M E T E R S

library(happy.hbrem)

markers.filename<-"markers.txt"

inputdir<-"inputs"
if (!file.exists(inputdir)) {
	dir.create(inputdir)
}

outputdir<-"outputs"
if (!file.exists(outputdir)) {
	dir.create(outputdir)
}

permute<-150



#  G E N O T Y P E S

# When opening Andreia's file in OpenOffice:
# the first line and the first column are removed from the Excel file.
# Since OpenOffice cuts of column 1024ff, instead use UNIX tools:
#   sed -e 's/^[^\t]*\t//' G3+genotype+final+table+16+nov.txt_Andreia > G3+genotype+final+table+16+nov.tsv

# Resulting format:
#   The SNP IDs become the column headers.
#   The 2nd row shows the names of the chromosomes
#   The 3rd row shows the position in base pairs
#   Then the phenotyping starts. The first column has the names of the individuals.
#   The first four rows have the genotypes of the parental strains.
#   Then follow directly their offspring.


read.table.snps<-read.delim("G3+genotype+final+table+16+nov.tsv",sep="\t",
	header=TRUE,row.names=1,stringsAsFactors=F,na.strings=c("-","x","NA"),
	skip=2,fill=F)


parental.strains<-c("BxD2","NZM","MRL","CAST")

read.table.snps.parentals<-as.matrix(read.table.snps[parental.strains,])
read.table.snps.parentals.distribution<-apply(read.table.snps.parentals,2,function(X){
	if (any(is.na(X))) stop("Found NA in parental strains' genotyping")
	alleles_concatenated<-paste(X,collapse="",sep="");
	#print(alleles_concatenated)
	alleles_table<-table(unlist(strsplit(split="",x=alleles_concatenated)))
	#print(alleles_table)
})

read.table.snps.parentals.informative<-sapply(read.table.snps.parentals.distribution,function(X){
	1<length(X)
})
read.table.snps.selected<-read.table.snps[,read.table.snps.parentals.informative]

snps.selected.names<-colnames(read.table.snps.selected)
snps.selected.chromosomes<-as.character(read.table.snps.selected["Chr",])
snps.selected.bp<-read.table.snps.selected["Position",]
#snps.selected.split<-split(x=snps.selected.names,f=factor(unique(sort(snps.selected.chromosomes))))
snps.selected.split<-split(x=snps.selected.names,f=snps.selected.chromosomes)

# happy needs cM, we estimate it
bp2cM<-function(X) { return(X/2/1000/1000) }
snps.selected.cM<-bp2cM(as.integer(snps.selected.bp))


# Happy needs marker files to work. Here a function to prepare it from
# the observed frequencies of alleles in the parental strains

if (data.prepare) {

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
		      " strains ",length(parental.strains),"\n",sep="")
	cat(file=file,"strain_names ",paste(parental.strains,collapse=" ",sep=""),"\n",append=T,sep="")
	for(n in colnames(genotype.matrix.parentals.distribution)) {
		d<-genotype.matrix.parentals.distribution[,n]
		chr<-as.character(genotype.matrix["Chr",n])
		pos<-as.numeric(as.character(genotype.matrix["Position",n]))
		#cat("chr",chr,", pos",pos,"\n"); print(d)
		cat(file=file,"marker ",n," ",(length(d)+1)," ",chr," ",bp2cM(pos),"\n",sep="",append=T)
		cat(file=file,"allele NA ",
		 	      paste(rep(1/length(parental.strains),length(parental.strains)),
				    collapse=" ",sep=""),"\n",append=T)
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

    if (F) {
	# A single file for everything
	happy.prepare.marker.file(file=paste(inputdir,"/",markers.filename,sep=""),
				genotype.matrix=read.table.snps.selected)
    }

    # A file for every chromosome
    for(chr in unique(sort(snps.selected.chromosomes))) {
	fname<-paste(inputdir,"/","markers_chr_",chr,".input",sep="")
	cat("\n\nWorking on '",fname,"'\n\n",sep="")
	columns.selected= (snps.selected.chromosomes==chr)
	print(which(columns.selected))
	s<-read.table.snps.selected[,columns.selected]
	happy.prepare.marker.file(file=fname, genotype.matrix=s)
    }
}



# P H E N O T Y P E S

# The conversion to tab-delimited is performed manually by OpenOffice.
# decimals appear as strings, in OpenOffice, the string separator 
# should therefore be the empty string and numbers be reread as numbers
# from R. The first column was already removed in OpenOffice.
# 
# The column names for phenotypes have been modified to be non-redundant
# > colnames(read.table.phenotypes)
# [1] "sex"            "color"          "weight.3m"      "weight.6m"
# [5] "weight.spleen"  "Black.spleen"   "I.Pankreas"     "Interstitium"
# [9] "cia.max.score"  "cia.onset.week" "Col.VII"        "eba.max.score"
# [13] "eba.onset.week" "Aggressivity"


# The first ten lines should be skipped
read.table.phenotypes<-read.table("G3-first-phenotypes-2.tsv",sep="\t",na.strings="x",skip=10, row.names=1,header=T)
w<-read.table.phenotypes[,"weight.3m"]
w[is.na(w)]<-read.table.phenotypes[is.na(w),"weight.6m"]
read.table.phenotypes<-cbind(read.table.phenotypes,weight=w); rm(w)
phenotypes<-colnames(read.table.phenotypes)

phenotypes.not.parental<- !is.na(read.table.phenotypes[,"sex"])
phenotypes.victimised.6m<- as.integer(rownames(read.table.phenotypes)) >= 333
phenotypes.victimised.6m[is.na(phenotypes.victimised.6m)]<-F
phenotypes.victimised.3m<- as.integer(rownames(read.table.phenotypes)) < 333
phenotypes.victimised.3m[is.na(phenotypes.victimised.3m)]<-F
# The phenotypes.*.* are binary vectors, all of the very same length


# C H E C K   F O R   C O N S I S T E N C Y

if (!all(rownames(read.table.phenotypes) %in% rownames(read.table.snps))) {
	stop("Found some individuals of the phenotypes not to be available in genotypes table.\n")
}

# P R E P A R A T I O N   O F   H A P P Y   I N P U T  F I L E S

individuals.3m <-rownames(read.table.phenotypes)[phenotypes.victimised.3m]
individuals.6m <-rownames(read.table.phenotypes)[phenotypes.victimised.6m]
individuals.all<-rownames(read.table.phenotypes)[phenotypes.not.parental]

if (data.prepare) {
    # the individuals.* are names of columns, so one needs now to think about 
    # sets, not about binary vectors any more. This has advantages when
    # using the vectors on data of varying dimensions.

    # routine to change SNPs to happy format, i.e. substitute NA with "NA,NA" as
    # two consecutive entries and have "a/b" to "a,b"
    merged2happy<-function(x=NULL,selected.rows=NULL,na.string="NA",verbose=FALSE) {
	snps.local<-NULL
	if (is.null(selected.rows)) selected.rows<-rownames(x)
	if (is.null(selected.rows)) selected.rows<-1:nrow(x)
	for(rname in selected.rows) { 
		X<-x[rname,]
		if (verbose) cat(rname," ")
		r<-NULL
		for(Y in X) {
			if (is.na(Y)) {
				#cat("Attaching NA,NA\n")
				r<-c(r,c(na.string,na.string))
			} else {
				#cat("Splitting",Y,"\n",sep=" ")
				v<-unlist(strsplit(split="",x=as.character(Y)))
				if (length(v) != 2) stop("Found '",Y,"' with length != 2!\n")
				r<-c(r,v)
			}
		}
		if (is.null(snps.local)) {
			snps.local<-r
		}
		else {
			snps.local<-rbind(snps.local,r)
		}
	}
	if (verbose) cat("\n")

	if (! (dim(x)[2])*2==(dim(snps.local)[2])) {
		stop("E: ! (dim(x)[2])*2==(dim(snps.local)[2])\n")
	}

	rownames(snps.local)<-selected.rows
	return(snps.local)
    }

    if (F) {
	# C H R O M O S O M E -- I N D E P E N D E N T   A N A L Y S I S

	# preparing a happy-formatted variant of all the genotypes, except for the parents
	snps<-merged2happy(read.table.snps.selected,selected.rows=individuals.all)
	# Testing the dimensions
	if (! (dim(read.table.snps.selected)[2])*2==(dim(snps)[2])) {
		stop("E: ! (dim(read.table.snps.selected)[2])*2==(dim(snps)[2])")
	}

	# Write genptype data files

	snps.3m <-snps[individuals.3m, ]
	snps.6m <-snps[individuals.6m, ]
	snps.all<-snps[individuals.all,]

	# Writing a genotype data file for the whole genome
	for(phen in phenotypes) {
		cat("Working on phen",phen,"\n")
		phens.3m <-read.table.phenotypes[individuals.3m,  phen]
		phens.6m <-read.table.phenotypes[individuals.6m,  phen]
		phens.all<-read.table.phenotypes[individuals.all, phen]

		if (sum(!is.na(phens.3m))>100) {
			input.3m <-cbind(individuals.3m, phens.3m, snps.3m )
			write.table(file=paste("happy_3m_", phen,".input",sep=""),input.3m,  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen," 3m series, missing data.\n",sep="")
		if (sum(!is.na(phens.6m))>100) {
			input.6m <-cbind(individuals.6m, phens.6m, snps.6m )
			write.table(file=paste("happy_6m_", phen,".input",sep=""),input.6m,  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen," 6m series, missing data.\n",sep="")
		if (sum(!is.na(phens.all))>100) {
			input.all<-cbind(individuals.all,phens.all,snps.all)
			write.table(file=paste("happy_all_",phen,".input",sep=""),input.all, col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen," series, missing data.\n",sep="")
	}
    }
}


if (data.prepare) {
    # Determine which individuals are interesting to investigate
    interesting.individuals<-function(M,thresh=0.99) {
	interesting<-apply(M,1,function(X){
		r<-(sum(!(is.na(X)|"ND"==X|"NA"==X))/length(X))>thresh
	})
	#print(interesting)
	#print(dim(interesting))
	return(interesting)
    }

    ## P R E P A R E  I N P U T  F I L E S  FOR   E V E R Y  P H E N   AND  C H R O M O S O M E

    # The markers and the genotype files are prepared individually for
    # every phenotype and every chromosome. The dependency on the phenotype
    # still needs to be implemented.
    for(phen in phenotypes) {
	name.suffix<-ifelse(data.binary,".binary","")
	# Writing a genotype data file for every phenotype
	cat("Working on phen",phen,name.suffix,"\n"i,sep="")

	chromosomes<-unique(sort(as.character(snps.selected.chromosomes)))
	for(chr in chromosomes) {
		# Writing a genotype data file for every chromosome
		cat("... chr ",chr,"\n")
		columns.selected= (snps.selected.chromosomes==chr)
		# Caveat! snps.x.chr have 2*columns.selected columns!!!! Happy format!!!!
		cat("\n3m: "); snps.3m.chr <-merged2happy(read.table.snps.selected[,columns.selected],individuals.3m)
		cat("\n6m: "); snps.6m.chr <-merged2happy(read.table.snps.selected[,columns.selected],individuals.6m)
		cat("\nall:"); snps.all.chr<-merged2happy(read.table.snps.selected[,columns.selected],individuals.all)
		i.3m <-interesting.individuals(snps.3m.chr)
		i.6m <-interesting.individuals(snps.6m.chr)
		i.all<-interesting.individuals(snps.all.chr)

		phens.3m <-read.table.phenotypes[names(i.3m),  phen, drop=F]
		phens.6m <-read.table.phenotypes[names(i.6m),  phen, drop=F]
		phens.all<-read.table.phenotypes[names(i.all), phen, drop=F]
		if (data.binary) { # dimentions are preserved
			binary.classifier<-function(X){if(is.na(X)) return(NA); if(X) 1 else 0;}
			phens.3m<-cbind(NULL,apply(phens.3m,1,binary.classifier))
			phens.6m<-cbind(NULL,apply(phens.6m,1,binary.classifier))
			phens.all<-cbind(NULL,apply(phens.all,1,binary.classifier))
		}

		#print(which(columns.selected))
		if (sum(!is.na(phens.3m))>100) {
			i<- !is.na(phens.3m)
			if (any(!i)) {
				cat("...",phen,name.suffix," 3m ... omitting ",sum(!i)," of ",length(i)," individuals",
					paste(which(!i),collapse=",",sep=""),"\n")
			}
			input.3m <-cbind(rownames(i)[i], phens.3m[i], snps.3m.chr[i,] )
			write.table(file=paste(inputdir,"/","happy_3m_", phen,name.suffix,"_chr_",chr,".input",sep=""),
					input.3m,  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen,name.suffix," 3m series, missing data.\n",sep="")
		if (sum(!is.na(phens.6m))>100) {
			i<-!is.na(phens.6m)
			if (any(!i)) {
				cat("...",phen,name.suffix," 6m ... omitting ",sum(!i)," of ",length(i)," individuals",
					paste(which(!i),collapse=",",sep=""),"\n")
			}
			input.6m <-cbind(rownames(i)[i], phens.6m[i], snps.6m.chr[i,] )
			write.table(file=paste(inputdir,"/","happy_6m_", phen,name.suffix,"_chr_",chr,".input",sep=""),
					input.6m,  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen,name.suffix," 6m series, missing data.\n",sep="")
		if (F&sum(!is.na(phens.all))>100) {
			i<-i.all & !is.na(phens.all)
			#i<-i.all
			if (any(!i)) {
				cat("...",phen,name.suffix," all ... omitting ",sum(!i)," of ",length(i)," individuals",
					paste(which(!i),collapse=",",sep=""),"\n")
			}
			input.all<-cbind(rownames(i)[i],phens.all[i],snps.all.chr[i,])
			write.table(file=paste(inputdir,"/","happy_all_",phen,name.suffix,"_chr_",chr,".input",sep=""),
				input.all, col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen,name.suffix," all series, missing data.\n",sep="")
	}
    }
}



if (F) {
	# Perform analysis over all chromosomes

	name.suffix<-if(data.binary){".binary"}else{""}

	if (!file.exists(markers.filename)) stop("Cannot find markers file expected at '",markers.filename,"\n")
	for(phen in colnames(read.table.phenotypes)) {
		for (individuals.subset in c("3m","6m","all")) {
			cat("Investigating",phen,name.suffix,"at times",individuals.subset,"\n")
			fname<-paste("happy_",individuals.subset,"_",phen,name.suffix,".input",sep="")
			if (!file.exists(fname)) {
				cat("  cannot find input file expected at '",fname,"\n")
				next
			}
			#happy(datafile=fname,allelesfile=markers.filename,generations=4,standardise=T,phase="unknown",file.format="happy",missing.code="ND")
			h<-happy(datafile=fname,allelesfile=markers.filename,generations=4,,phase="unknown",file.format="happy",missing.code="ND")
			fit<-hfit(h)
			break
		}
	}
}


analyse<-function(phen,chr) {
	markers.filename.chr<-paste(inputdir,"/","markers_chr_",chr,".input",sep="")
	if (!file.exists(markers.filename.chr)) stop(paste("Cannot find marker file for chromosome",chr,"\n",sep=""))

	for (individuals.subset in c("3m","6m","all")) {
							# covariates do not influence parameters stored
		fname<-paste(inputdir,"/","happy_",individuals.subset,"_",phen,name.suffix,"_chr_",chr,".input",sep="")
		if (!file.exists(fname)) {
			cat("  cannot find input file expected at '",fname,"\n") ; next
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
		covariatematrix<-NULL
		if(!is.null(data.covariates)) {
			covariatematrix<-as.matrix(read.table.phenotypes[h$subjects,data.covariates,drop=FALSE])
		}

		fit.0<-hfit(h, permute=0,verbose=TRUE,model=model,covariatematrix=covariatematrix)
		cat("\n",phen,name.suffix,covariates.suffix,"@",chr," fit.0$mapx: ",fit.0$maxp,"\n",sep="")
		fit.permute<-hfit(h, permute=permute,verbose=TRUE,model=model,covariatematrix=covariatematrix)
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
		abline(fit.permute$permdata$p05,0,col="green",lty=3)
		abline(fit.permute$permdata$p01,0,col="green",lty=2)

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


# Perform analysis for every chromosome
for(phen in phenotypes) {

	name.suffix<-ifelse(data.binary,".binary","")
	covariates.suffix<-ifelse(is.null(data.covariates),"",paste("_covars_",paste(data.covariates,collapse=",",sep=""),sep=""))
	cat("\n**************\n")
	cat("      ",phen,name.suffix,covariates.suffix,"\n",sep="")
	cat("\n**************\n\n")

	if (phen %in% data.covariates) {
		cat("W: phen ",phen," also appears in data.covariates, ... skipping.\n")
		next
	}

	pdf(paste(outputdir,"/","analysis_happy_phen_",phen,name.suffix,covariates.suffix,"_chr_all.pdf",sep=""))
	chromosomes<-unique(sort(as.character(snps.selected.chromosomes)))
	for(chr in chromosomes) {
		analyse(phen,chr)
	}
	dev.off()
}
