
# This script expects the data to arrive from Andreia's Excel setup.
# It describes a series of manual steps to be performed in OpenOffice 
# to derive the tab-separated files and then the subsequent commands
# to prepare the input for happy.

#  C O N T R O L


#  P A R A M E T E R S

require(happy.hbrem)

happy.start <- function(project.name,generations=4,model="additive", permute=0, data.covariates=NULL, data.binary=F, data.prepare=F, markers.filenam="markers.txt", missing.code="NA", split.chromosomes=F, overwrite=F, verbose=F) {

	print(ls())

	inputdir<-paste("inputs",if(is.null(project.name)) "unnamed" else project.name,sep="/")
	if (!file.exists(inputdir)) {
		dir.create(inputdir,recursive=T)
	}

	outputdir<-paste("outputs",if(is.null(project.name)) "unnamed" else project.name,sep="/")
	if (!file.exists(outputdir)) {
		cat(paste("Creating output directory at '",outputdir,"'.\n",sep=""))
		dir.create(outputdir,recursive=T)
	}

	name.suffix<-""
	#if (data.binary) name.suffix <- ".binary"

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
		alleles_concatenated<-paste(X,collapse="",sep="")
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

		source("01_func_write_marker_file.R")

		if (!split.chromosomes) {
		     # A single file for everything
		     happy.prepare.marker.file(file=paste(markers.filename,sep=""),
				genotype.matrix=read.table.snps.selected)
		} else {
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

    phenotypes.extra <- NULL
    phenotypes.mohan<- NULL
    phenotypes.susen<- NULL
    phenotypes.susen.details<- NULL

    phenotypes.baines<- NULL
    phenotypes.baines.otu.relative <- NULL
    phenotypes.baines.phylum<- NULL
    phenotypes.baines.genus<- NULL
    phenotypes.baines.otu.unique<- NULL
    phenotypesbaines.selected.above.90<- NULL

    if ("baines" %in% project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.table("data/baines/otu_table/otu_table.txt",sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	phenotypes.baines<-t(r[-1,])
	rownames(phenotypes.baines)<-as.character(r[1,])
	rm(r)
    } else if ("baines.relative.otu"  %in%  project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.table("data/baines/relative_values/otu_table.txt",sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	phenotypes.baines.relative.otu<-t(r[-1,])
	rownames(phenotypes.baines.otu.relative)<-as.character(r[1,])
	rm(r)
    } else if ("baines.phylum"  %in%  project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.delim("data/baines/taxon/phylum_code.txt",sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	phenotypes.baines.phylum<-t(r[-1,])
	rownames(phenotypes.baines.phylum)<-as.character(r[1,])
	rm(r)
    } else if ("baines.genus"  %in%  project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.delim("data/baines/taxon/genus_code.txt",sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	phenotypes.baines.genus<-t(r[-1,])
	rownames(phenotypes.baines.genus)<-as.character(r[1,])
	rm(r)
    } else if ("baines.unique.otu"  %in%  project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.delim("data/baines/otu_unique_selected/otu_unique.txt",sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	phenotypes.baines.otu.unique<-t(r[-1,])
	rownames(phenotypes.baines.otu.unique)<-as.character(r[1,])
	rm(r)
    } else if ("baines.selected.above.90"  %in%  project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.delim("data/baines/selected_taxon/selected_taxon.txt",sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	phenotypes.baines.selected.above.90<-t(r[-1,])
	rownames(phenotypes.baines.selected.above.90)<-as.character(r[1,])
	rm(r)
    }

    if ("mohan" %in% project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.table("data/mohan/mohan_all_phenotypes.tsv",sep="\t",header=T,stringsAsFactors=F,colClasses=integer())
	r.good<-(!is.na(r[,2])) & !duplicated(r[,2])
	r2 <- r[r.good,]
	r2.good<-(!is.na(r2[,1])) & !duplicated(r2[,1])
	r3 <- r2[r2.good,]
	phenotypes.mohan<-r3[,grep("rank$",colnames(r2))]
	rownames(phenotypes.mohan)<-r3[,1]
	rm(r,r2,r3,r2.good,r.good)
    }

    if ("susen" %in% project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.table("data/susen/Immu_mColVIIc_IgG_C3_data.csv",sep="\t",header=T,stringsAsFactors=F,colClasses=integer())
	phenotypes.susen<-r[,-1]
	rownames(phenotypes.susen)<-r[,1]
	rm(r)
    }

    if ("susen.details" %in% project.name) {
	cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	r<-read.table("data/susen/Immu_mColVIIc_IgG_C3_details_data.csv",sep="\t",header=T,stringsAsFactors=F,colClasses=integer())
	phenotypes.susen.details<-r[,-1]
	rownames(phenotypes.susen.details)<-r[,1]
	rm(r)
    }



    # C H E C K   F O R   C O N S I S T E N C Y

    if (!all(rownames(read.table.phenotypes) %in% rownames(read.table.snps))) {
	stop("Found some individuals of the phenotypes not to be available in genotypes table.\n")
    }

    if ("baines" %in% project.name) {
	if (!all(rownames(phenotypes.baines) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phenotypes.baines) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			project.name,
			"' not to be available in genotypes table.\n",sep=""))
		phenotypes.baines<-phenotypes.baines[rownames(phenotypes.baines) %in% rownames(read.table.snps),]
	}
	cat("Selecting of phenotypes of interest")
	number.of.individuals.affected.per.phenotype<-apply(phenotypes.baines>0,2,sum)
	a<-which(number.of.individuals.affected.per.phenotype>=30)
	phenotypes.baines<-phenotypes.baines[,a]
    }

    if ("baines.relative.otu" %in% project.name) {
	if (!all(rownames(phenotypes.baines.otu.relative) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phenotypes.baines.otu.relative) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			project.name,
			"' not to be available in genotypes table.\n",sep=""))
		phenotypes.baines.otu.relative<-phenotypes.baines.otu.relative[rownames(phenotypes.baines.otu.relative) %in% rownames(read.table.snps),]
	}
    }

    if ("baines.phylum" %in% project.name) {
	if (!all(rownames(phenotypes.baines.phylum) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phenotypes.baines.phylum) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			project.name,
			"' not to be available in genotypes table.\n",sep=""))
		phenotypes.baines.phylum<-phenotypes.baines.phylum[rownames(phenotypes.baines.phylum) %in% rownames(read.table.snps),]
	}
    }

    if ("baines.genus" %in% project.name) {
	if (!all(rownames(phenotypes.baines.genus) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phenotypes.baines.genus) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			project.name,
			"' not to be available in genotypes table.\n",sep=""))
		phenotypes.baines.genus<-phenotypes.baines.genus[rownames(phenotypes.baines.genus) %in% rownames(read.table.snps),]
	}
	cat("Selecting of phenotypes of interest")
		cat("Selecting of phenotypes of interest")
	number.of.individuals.affected.per.phenotype<-apply(phenotypes.baines>0,2,sum)
	a<-which(number.of.individuals.affected.per.phenotype>=0.40)
	phenotypes.baines<-phenotypes.baines[,a]<-apply(phenotypes.baines.genus>0,2,sum)
	a<-which(number.of.individuals.affected.per.phenotype>=110)
	phenotypes.baines.genus<-phenotypes.baines.genus[,a]
    }

    if ("baines.unique.otu" %in% project.name) {
	if (!all(rownames(phenotypes.baines.otu.unique) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phenotypes.baines.otu.unique) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			project.name,
			"' not to be available in genotypes table.\n",sep=""))
		phenotypes.baines.otu.unique<-phenotypes.baines.otu.unique[rownames(phenotypes.baines.otu.unique) %in% rownames(read.table.snps),]
	}
    }


    if ("baines.selected.above.90" %in% project.name) {
	if (!all(rownames(phenotypes.baines.selected.above.90) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phenotypes.baines.selected.above.90) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			project.name,
			"' not to be available in genotypes table.\n",sep=""))
		phenotypes.baines.selected.above.90<-phenotypes.baines.selected.above.90[rownames(phenotypes.baines.selected.above.90) %in% rownames(read.table.snps),]
	}
    }


    if ("mohan" %in% project.name) {
	if (!all(rownames(phenotypes.mohan) %in% rownames(read.table.snps))) {
		warning("The following some individuals of the phenotypes are not found in genotypes table:\n",
			paste(rownames(phenotypes.mohan)[!rownames(phenotypes.mohan) %in% rownames(read.table.snps)],collapse=",",sep=""))
	}
	phenotypes.mohan<-phenotypes.mohan[rownames(phenotypes.mohan) %in% rownames(read.table.snps),]
    }

    if ("susen" %in% project.name) {
	if (!all(rownames(phenotypes.susen) %in% rownames(read.table.snps))) {
		warning("The following some individuals of the phenotypes are not found in genotypes table:\n",
			paste(rownames(phenotypes.susen)[!rownames(phenotypes.susen) %in% rownames(read.table.snps)],collapse=",",sep=""))
	}
	phenotypes.susen<-phenotypes.susen[rownames(phenotypes.susen) %in% rownames(read.table.snps),]
    }

    if ("susen.details" %in% project.name) {
	if (!all(rownames(phenotypes.susen.details) %in% rownames(read.table.snps))) {
		warning("The following some individuals of the phenotypes are not found in genotypes table:\n",
			paste(rownames(phenotypes.susen.details)[!rownames(phenotypes.susen.details) %in% rownames(read.table.snps)],collapse=",",sep=""))
	}
	phenotypes.susen.details<-phenotypes.susen.details[rownames(phenotypes.susen.details) %in% rownames(read.table.snps),]
    }

    # P R E P A R A T I O N   O F   H A P P Y   I N P U T  F I L E S

    if (data.prepare) {

        individuals.3m <-rownames(read.table.phenotypes)[phenotypes.victimised.3m]
        individuals.6m <-rownames(read.table.phenotypes)[phenotypes.victimised.6m]
        individuals.all<-rownames(read.table.phenotypes)[phenotypes.not.parental]

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

        # Determine which individuals are interesting to investigate
        interesting.individuals<-function(M,thresh=0.99) {
	    interesting<-apply(M,1,function(X){
		r<-(sum(!(is.na(X)|"ND"==X|"NA"==X|missing.code==X))/length(X))>thresh
	    })
	    #print(interesting)
	    #print(dim(interesting))
	    return(interesting)
        }

        if (!split.chromosomes) {

	    # P R E P A R E   I N P U T  F I L E S   For  C H R O M O S O M E -- I N D E P E N D E N T   A N A L Y S I S


	    # preparing a happy-formatted variant of all the genotypes, except for the parents
	    snps<-merged2happy(read.table.snps.selected,selected.rows=individuals.all)
	    # Testing the dimensions
	    if (! (dim(read.table.snps.selected)[2])*2==(dim(snps)[2])) {
		stop("E: ! (dim(read.table.snps.selected)[2])*2==(dim(snps)[2])")
	    }

	    # Write genotype data files

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
			write.table(file=paste("happy_3m_", phen,".input",sep=""),input.3m[!is.na(phens.3m),],  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen," 3m series, missing data.\n",sep="")
		if (sum(!is.na(phens.6m))>100) {
			input.6m <-cbind(individuals.6m, phens.6m, snps.6m )
			write.table(file=paste("happy_6m_", phen,".input",sep=""),input.6m[!is.na(phens.6m),],  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen," 6m series, missing data.\n",sep="")
		if (sum(!is.na(phens.all))>100) {
			input.all<-cbind(individuals.all,phens.all,snps.all)
			write.table(file=paste("happy_all_",phen,".input",sep=""),input.all[!is.na(phens.all),], col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("Skipping ",phen," series, missing data.\n",sep="")
	    }

	    # Continuing for other projects
	    if ("baines" %in% project.name) {
		snps.baines<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.baines))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.baines)
		for (phen in colnames(phenotypes.baines)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phens.baines.all<-phenotypes.baines[,phen]
			if (sum(!is.na(phens.baines.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="") ; written.act <- written.act+1
				input.baines.all<-cbind(rownames(phenotypes.baines),phens.baines.all,snps.baines)
				write.table(file=ifile, input.baines.all[!is.na(phens.baines.all),], col.names=F, row.names=F, quote=F, sep="\t")
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

            if ("baines.relative.otu" %in% project.name) {
		snps.baines<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.baines.otu.relative))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.baines.otu.relative)
		for (phen in colnames(phenotypes.baines.otu.relative)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phenotypes.baines.otu.relative.all<-phenotypes.baines.otu.relative[,phen]
			if (sum(!is.na(phenotypes.baines.otu.relative.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="") ; written.act <- written.act+1
				input.baines.otu.relative.all<-cbind(rownames(phenotypes.baines.otu.relative),phenotypes.baines.otu.relative.all,snps.baines)
				write.table(file=ifile, input.baines.otu.relative.all[!is.na(phenotypes.baines.otu.relative.all),], col.names=F, row.names=F, quote=F, sep="\t")
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

            if ("baines.phylum" %in% project.name) {
		snps.baines<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.baines.phylum))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.baines.phylum)
		for (phen in colnames(phenotypes.baines.phylum)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phenotypes.baines.phylum.all<-phenotypes.baines.phylum[,phen]
			if (sum(!is.na(phenotypes.baines.phylum.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="") ; written.act <- written.act+1
				input.baines.phylum.all<-cbind(rownames(phenotypes.baines.phylum),phenotypes.baines.phylum.all,snps.baines)
				write.table(file=ifile, input.baines.phylum.all[!is.na(phenotypes.baines.phylum.all),], col.names=F, row.names=F, quote=F, sep="\t")
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

            if ("baines.genus" %in% project.name) {
		snps.baines<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.baines.genus))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.baines.genus)
		for (phen in colnames(phenotypes.baines.genus)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phenotypes.baines.genus.all<-phenotypes.baines.genus[,phen]
			if (sum(!is.na(phenotypes.baines.genus.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="") ; written.act <- written.act+1
				input.baines.genus.all<-cbind(rownames(phenotypes.baines.genus),phenotypes.baines.genus.all,snps.baines)
				write.table(file=ifile, input.baines.genus.all[!is.na(phenotypes.baines.genus.all),], col.names=F, row.names=F, quote=F, sep="\t")
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

	    if ("baines.unique.otu" %in% project.name) {
		snps.baines<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.baines.otu.unique))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.baines.otu.unique)
		for (phen in colnames(phenotypes.baines.otu.unique)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phenotypes.baines.otu.unique.all<-phenotypes.baines.otu.unique[,phen]
			if (sum(!is.na(phenotypes.baines.otu.unique.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="") ; written.act <- written.act+1
				input.baines.otu.unique.all<-cbind(rownames(phenotypes.baines.otu.unique),phenotypes.baines.otu.unique.all,snps.baines)
				write.table(file=ifile, input.baines.otu.unique.all[!is.na(phenotypes.baines.otu.unique.all),], col.names=F, row.names=F, quote=F, sep="\t")
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

	    p.n <- "baines.selected.above.90"
	    if (p.n %in% project.name) {
		snps.baines<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.baines.selected.above.90))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.baines.selected.above.90)
		for (phen in colnames(phenotypes.baines.selected.above.90)) {
			ifile<-paste(inputdir,"/happy_project_",p.n,"_all_", phen,".input",sep="")
			phenotypes.baines.selected.above.90.all<-phenotypes.baines.selected.above.90[,phen]
			if (sum(!is.na(phenotypes.baines.selected.above.90.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="") ; written.act <- written.act+1
				input.baines.selected.above.90.all<-cbind(rownames(phenotypes.baines.selected.above.90),phenotypes.baines.selected.above.90.all,snps.baines)
				write.table(file=ifile, input.baines.selected.above.90.all[!is.na(phenotypes.baines.selected.above.90.all),], col.names=F, row.names=F, quote=F, sep="\t")
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",p.n,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }


	    p.n <- "mohan"
	    if (p.n %in% project.name) {
		snps.mohan<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.mohan))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.mohan)
		for (phen in colnames(phenotypes.mohan)) {
			ifile<-paste(inputdir,"/happy_project_",p.n,"_all_", phen,".input",sep="")
			phens.mohan.all<-phenotypes.mohan[,phen]
			if (sum(!is.na(phens.mohan.all))>100) {
				cat("Creating file '",ifile,"'.\n",sep="")
				input.mohan.all<-cbind(rownames(phenotypes.mohan),phens.mohan.all,snps.mohan)
				write.table(file=ifile, input.mohan.all[!is.na(phens.mohan.all),], col.names=F, row.names=F, quote=F, sep="\t")
				written.act <- written.act+1
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",p.n,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

	    if ("susen" %in% project.name) {
		snps.susen<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.susen))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.susen)
		for (phen in colnames(phenotypes.susen)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phens.susen<-phenotypes.susen[,phen]
			if (sum(!is.na(phens.susen))>100) {
				cat("Creating file '",ifile,"'.\n",sep="")
				input.susen<-cbind(rownames(phenotypes.susen),phens.susen,snps.susen)
				write.table(file=ifile, input.susen[!is.na(phens.susen),], col.names=F, row.names=F, quote=F, sep="\t")
				written.act <- written.act+1
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

	    if ("susen.details" %in% project.name) {
		snps.susen.details<-merged2happy(read.table.snps.selected,selected.rows=rownames(phenotypes.susen.details))
		if (!file.exists(inputdir)) {
			if (!dir.create(inputdir)) {
				stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
			}
		}
		written.act<-0;written.max<-ncol(phenotypes.susen.details)
		for (phen in colnames(phenotypes.susen.details)) {
			ifile<-paste(inputdir,"/happy_project_",project.name,"_all_", phen,".input",sep="")
			phens.susen.details<-phenotypes.susen.details[,phen]
			if (sum(!is.na(phens.susen.details))>100) {
				cat("Creating file '",ifile,"'.\n",sep="")
				input.susen.details<-cbind(rownames(phenotypes.susen.details),phens.susen.details,snps.susen.details)
				write.table(file=ifile, input.susen.details[!is.na(phens.susen.details),], col.names=F, row.names=F, quote=F, sep="\t")
				written.act <- written.act+1
			} else {
				stop(paste("Too many NA values for phenotype '",phen,"' of project '",project.name,"'.\n",sep=""))
			}
		}
		cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
	    }

        } else {

        ## P R E P A R E  I N P U T  F I L E S  FOR   E V E R Y  P H E N   AND  C H R O M O S O M E

    # The markers and the genotype files are prepared individually for
    # every phenotype and every chromosome. The dependency on the phenotype
    # still needs to be implemented.
            for(phen in phenotypes) {
	        # Writing a genotype data file for every phenotype
	        cat("Working on phen",phen,name.suffix,"\n",sep="")

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
    }

    source("01_func_analyse.R")


    if (!split.chromosomes) {
	cat("\n")
	cat("\n")
	cat("-------------------------------------------------------------------------------------\n")
	cat("               Analysis on all chromosomes together for project ", project.name,    "\n")
	cat("-------------------------------------------------------------------------------------\n")

	if (!file.exists(markers.filename)) {
		stop("Cannot find markers file expected at '",markers.filename,"\n")
	}
	if (project.name %in% c("baines","baines.relative.otu","baines.phylum","baines.genus","baines.unique.otu","baines.selected.above.90")) {
		phens<-NULL
		if ("baines" %in% project.name) {
			phens <- colnames(phenotypes.baines)
		} else if ("baines.relative.otu" %in% project.name) {
			phens <- colnames(phenotypes.baines.relative.otu)
		} else if ("baines.phylum" %in% project.name) {
			phens <- colnames(phenotypes.baines.phylum)
		} else if ("baines.genus" %in% project.name) {
			phens <- colnames(phenotypes.baines.genus)
		}else if ("baines.unique.otu" %in% project.name) {
			phens <- colnames(phenotypes.baines.otu.unique)
		}else if ("baines.selected.above.90" %in% project.name) {
			phens <- colnames(phenotypes.baines.selected.above.90)
		}else stop("This program is inconsistent.")

		current.cpu<-5
		number.of.cpus<-12
		for(phen.pos in 1:length(phens)) {
			phen<-phens[phen.pos]
			cat("\n"                                                                        )
			cat(      "**********************************************************\n"        )
			cat(paste("****** ",                        phen,            " ******\n",sep=""))
			cat(paste("       ",   "CPU ",current.cpu, " of ",number.of.cpus,  "       \n",sep=""))
			cat(      "**********************************************************\n"        )
			cat("\n"                                                                        )
			if (current.cpu != phen.pos %% number.of.cpus) {
				cat(paste("skipped: ",phen.pos, "-> ",phen,"\n",sep=""))
				next;
			}
			ok<-analyse.all.chromosomes.together(phen=phen,individuals.subset="all",
							     generations=generations, model=model,
							     data.covariates=data.covariates,
							     name.suffix=name.suffix,project.name=project.name,overwrite=overwrite,
							     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
			if (!ok) {
				stop(paste("Problem occurred for phen '",phen,"'.\n",sep=""))
			} else {
				cat(paste("[",phen.pos,":",phen,"]\n",sep=""))
			}
		}
	} else if ("mohan" %in% project.name) {
		for(phen in colnames(phenotypes.mohan)) {
			ok<-analyse.all.chromosomes.together(phen=phen,individuals.subset="all",
							     generations=generations, model=model,
							     data.covariates=data.covariates,
							     name.suffix=name.suffix,project.name=project.name,overwrite=overwrite,
							     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
			if (!ok) {
				stop(paste("Problem occurred for phen '",phen,"'.\n",sep=""))
			} else {
				cat("[",phen,"]\n")
			}
		}
	} else if ("susen" %in% project.name) {
		for(phen in colnames(phenotypes.susen)) {
			ok<-analyse.all.chromosomes.together(phen=phen,individuals.subset="all",
							     generations=generations, model=model,
							     data.covariates=data.covariates,
							     name.suffix=name.suffix,project.name=project.name,overwrite=overwrite,
							     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
			if (!ok) {
				stop(paste("Problem occurred for phen '",phen,"'.\n",sep=""))
			} else {
				cat("[",phen,"]\n")
			}
		}
	} else if ("susen.details" %in% project.name) {
		for(phen in colnames(phenotypes.susen.details)) {
			ok<-analyse.all.chromosomes.together(phen=phen,individuals.subset="all",
							     generations=generations, model=model,
							     data.covariates=data.covariates,
							     name.suffix=name.suffix,project.name=project.name,overwrite=overwrite,
							     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
			if (!ok) {
				stop(paste("Problem occurred for phen '",phen,"'.\n",sep=""))
			} else {
				cat("[",phen,"]\n")
			}
		}
	} else {
		#for(phen in colnames(read.table.phenotypes))
		for(phen in phenotypes) {
			one.was.omitted<-FALSE
			for (individuals.subset in c("3m","6m","all")) {
				if ("all"==individuals.subset && one.was.omitted) {
					cat("\nPhenotype ",phen,": There was already some subset not readable/omitted for other reasons. Skipping otherwise missleading 'all'.\n",sep="")
					next
				}
				ok<-analyse.all.chromosomes.together(phen=phen,individuals.subset=individuals.subset,
								     generations=generations, model=model.el,
								     data.covariates=data.covariates,name.suffix=name.suffix,overwrite=overwrite,
								     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
				if (!ok) one.was.omitted<-TRUE
			} #stop("Just stopping to help development.")
		}
	}

    } else {

	# Perform analysis for every chromosome
	for(phen in phenotypes) {

		cat("\n**************\n")
		cat("               Analysis on individual chromosomes "                                    )
		cat("      ",phen,name.suffix,covariates.suffix,"\n",sep="")
		cat("\n**************\n\n")

		if (phen %in% data.covariates) {
			cat("W: phen ",phen," also appears in data.covariates, ... skipping.\n")
			next
		}

		pdf(paste(outputdir,"/","analysis_happy_phen_",phen,name.suffix,covariates.suffix,"_chr_all.pdf",sep=""))
		chromosomes<-unique(sort(as.character(snps.selected.chromosomes)))
		for(chr in chromosomes) {
			analyse.split.chromosomes(phen,chr,
					generations=generations, model=model,
					inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
		}
		dev.off()
	}
    }

}
