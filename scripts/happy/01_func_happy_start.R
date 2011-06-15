
# This script expects the data to arrive from Andreia's Excel setup.
# It describes a series of manual steps to be performed in OpenOffice 
# to derive the tab-separated files and then the subsequent commands
# to prepare the input for happy.

#  C O N T R O L


#  P A R A M E T E R S

require(happy.hbrem)

"%w/o%" <- function(x, y) x[!x %in% y]

simpler.name<-function(n) { paste(n,collapse="W",sep="") }
debug <- F

happy.start <- function(project.name,generations=4,model="additive", permute=0,
                        data.covariates=NULL, data.binary=F, data.prepare=F, subset.phenotype=NULL,
			markers.filenam="markers.txt", missing.code="NA", split.chromosomes=F,
			overwrite=F, verbose=F) {


	inputdir<-paste("inputs",if(is.null(project.name)) "unnamed" else simpler.name(project.name),sep="/")
	if (!file.exists(inputdir)) {
		dir.create(inputdir,recursive=T)
	}

	outputdir<-paste("outputs",if(is.null(project.name)) "unnamed" else simpler.name(project.name),sep="/")
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

	if (data.prepare & data.prepare.marker) {
		cat("Preparing Marker Files\n")

		source("01_func_write_marker_file.R",local=FALSE)

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

    phenotypes.collection<-list()
    phenotypes.collection[["basic"]]<-read.table.phenotypes

    phenotypes.not.parental<- !is.na(read.table.phenotypes[,"sex"])
    phenotypes.victimised.6m<- as.integer(rownames(read.table.phenotypes)) >= 333
    phenotypes.victimised.6m[is.na(phenotypes.victimised.6m)]<-F
    phenotypes.victimised.3m<- as.integer(rownames(read.table.phenotypes)) < 333
    phenotypes.victimised.3m[is.na(phenotypes.victimised.3m)]<-F
    # The phenotypes.*.* are binary vectors, all of the very same length

    #phenotypes.extra <- NULL
    #phenotypes.mohan<- NULL
    #phenotypes.susen<- NULL
    #phenotypes.susen.details<- NULL

    #phenotypes.baines<- NULL
    #phenotypes.baines.otu.relative <- NULL
    #phenotypes.baines.phylum<- NULL
    #phenotypes.baines.genus<- NULL
    #phenotypes.baines.otu.unique<- NULL
    #phenotypes.baines.selected.above.90<- NULL

    baines.project.filenames<-list(
	"baines.selected.above.90"="data/baines/selected_taxon/selected_taxon.txt"
    )

    simple.reads.filenames<-list(
    	"susen"="data/susen/Immu_mColVIIc_IgG_C3_data.csv",
	"susen.details"="data/susen/Immu_mColVIIc_IgG_C3_details_data.csv"
    )

    possible.projects <- c(union(names(baines.project.filenames),names(simple.reads.filenames)),"mohan")

    for (p.n in intersect(possible.projects,project.name)) {
       if (!is.null(baines.project.filenames[[p.n]])) {
          data.filename<-baines.project.filenames[[p.n]]
	  cat(paste("Running extra code for project '",p.n,"' with data at '",data.filename,"'.\n",sep=""))
	  r<-read.table(data.filename,sep="\t",header=F,row.names=1,stringsAsFactors=F,colClasses=integer())
	  phenotypes.baines<-t(r[-1,])
	  rownames(phenotypes.baines)<-as.character(r[1,])
	  phenotypes.collection[[p.n]]<-phenotypes.baines
	  rm(r,phenotypes.baines)
       } else if (p.n %in% names(simple.reads.filenames)) {
          data.filename<-simple.reads.filenames[[p.n]]
	  cat(paste("Running extra code for project '",p.n,"' with data at '",data.filename,"'.\n",sep=""))
	  r<-read.table(data.filename,sep="\t",header=T,stringsAsFactors=F,colClasses=integer())
	  phenotypes.simple<-r[,-1]
	  rownames(phenotypes.simple)<-r[,1]
	  phenotypes.collection[[p.n]]<-phenotypes.simple
	  rm(r,phenotypes.simple)
       } else if ("mohan" == p.n) {
	  cat(paste("Running extra code for project '",project.name,"'.\n",sep=""))
	  r<-read.table("data/mohan/mohan_all_phenotypes.tsv",sep="\t",header=T,stringsAsFactors=F,colClasses=integer())
	  r.good<-(!is.na(r[,2])) & !duplicated(r[,2])
	  r2 <- r[r.good,]
	  r2.good<-(!is.na(r2[,1])) & !duplicated(r2[,1])
	  r3 <- r2[r2.good,]
	  phenotypes.mohan<-r3[,grep("rank$",colnames(r2))]
	  rownames(phenotypes.mohan)<-r3[,1]
	  phenotypes.collection[[p.n]]<-phenotypes.mohan
	  rm(r,r2,r3,r2.good,r.good,phenotypes.mohan)
       }
    }

    # C H E C K   F O R   C O N S I S T E N C Y

    if (!all(rownames(read.table.phenotypes) %in% rownames(read.table.snps))) {
	stop("Found some individuals of the phenotypes not to be available in genotypes table.\n")
    }

    for(p.n in names(phenotypes.collection)) {
        cat("Testing phenotypes for project ",p.n," for consistency.\n")
    	phens.tmp <-phenotypes.collection[[p.n]]

	if (!all(rownames(phens.tmp) %in% rownames(read.table.snps))) {
		"%w/o%" <- function(x, y) x[!x %in% y]
		unknown.individuals <- rownames(phens.tmp) %w/o% rownames(read.table.snps)
		warning(paste("Found some individuals (",
			paste(unknown.individuals,collapse=",",sep=""),
			") of the phenotypes of project '",
			p.n,
			"' not to be available in genotypes table.\n",sep=""))
		phens.tmp<-phens.tmp[rownames(phens.tmp) %in% rownames(read.table.snps),]
		phenotypes.collection[[p.n]]<-phens.tmp
	}
	rm(phens.tmp)
    }


    # P R E P A R A T I O N   O F   H A P P Y   I N P U T  F I L E S

    if (data.prepare) {

        cat("I: Preparing data input files\n")

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
	    cat("I: Get SNP data in sync with phenotype")
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
	    cat("I: Writing basic input files\n")
	    for(phen in phenotypes) {
		cat("   Working on phen",phen,"\n")
		phens.3m <-read.table.phenotypes[individuals.3m,  phen]
		phens.6m <-read.table.phenotypes[individuals.6m,  phen]
		phens.all<-read.table.phenotypes[individuals.all, phen]

		if (sum(!is.na(phens.3m))>100) {
			input.3m <-cbind(individuals.3m, phens.3m, snps.3m )
			write.table(file=paste("happy_3m_", phen,".input",sep=""),
			   input.3m[!is.na(phens.3m),],  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("W: Skipping ",phen," 3m series, missing data.\n",sep="")
		if (sum(!is.na(phens.6m))>100) {
			input.6m <-cbind(individuals.6m, phens.6m, snps.6m )
			write.table(file=paste("happy_6m_", phen,".input",sep=""),
			   input.6m[!is.na(phens.6m),],  col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("W: Skipping ",phen," 6m series, missing data.\n",sep="")
		if (sum(!is.na(phens.all))>100) {
			input.all<-cbind(individuals.all,phens.all,snps.all)
			write.table(file=paste("happy_all_",phen,".input",sep=""),
			   input.all[!is.na(phens.all),], col.names=F, row.names=F, quote=F, sep="\t")
		} else cat("W: Skipping ",phen," series, missing data.\n",sep="")
	    }

	    cat("I: Writing additional input files\n")
	    for(p.n in names(phenotypes.collection)) {
		if (p.n %in% project.name) {

			phenotypes.from.collection<-phenotypes.collection[[p.n]]
			if (is.null(phenotypes.from.collection)) {
				stop("Could not retrieve phenotypes from hash for project ",p.n,".\n")
			}

			snps.read.from.file<-merged2happy(read.table.snps.selected,
							  selected.rows=rownames(phenotypes.from.collection))

			if (!file.exists(inputdir)) {
				if (!dir.create(inputdir)) {
					stop(paste("Could not create directory '",inputdir,"'.\n",sep=""))
				}
			}
			written.act<-0;written.max<-ncol(phenotypes.from.collection)

			# Iterating over all phenotypes stored in file
			for (phen in colnames(phenotypes.from.collection)) {
				ifile<-paste(inputdir,"/happy_project_",p.n,"_all_", phen,".input",sep="")
				# retrieving particular phenotype
				phenotypes.from.collection.single<-phenotypes.from.collection[,phen]
				if (sum(!is.na(phenotypes.from.collection.single))<=100) {
					stop(paste("Too many NA values for phenotype '",phen,"' of project '",p.n,"'.\n",sep=""))
				}

				cat("Creating file '",ifile,"'.\n",sep="")
				written.act <- written.act+1

				input.all<-cbind(rownames(phenotypes.from.collection),
					         phenotypes.from.collection.single,
						 snps.read.from.file)
				write.table(file=ifile, input.all[!is.na(phenotypes.from.collection.single),],
					col.names=F, row.names=F, quote=F, sep="\t")
			}
			cat("Created input files for ",written.act," of ",written.max," phenotypes.\n",sep="")
			rm(snps.read.from.file,phenotypes.from.collection)
		}
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
		    } else cat("W: Skipping ",phen,name.suffix," 3m series, missing data.\n",sep="")

		    if (sum(!is.na(phens.6m))>100) {
			i<-!is.na(phens.6m)
			if (any(!i)) {
				cat("...",phen,name.suffix," 6m ... omitting ",sum(!i)," of ",length(i)," individuals",
					paste(which(!i),collapse=",",sep=""),"\n")
			}
			input.6m <-cbind(rownames(i)[i], phens.6m[i], snps.6m.chr[i,] )
			write.table(file=paste(inputdir,"/","happy_6m_", phen,name.suffix,"_chr_",chr,".input",sep=""),
					input.6m,  col.names=F, row.names=F, quote=F, sep="\t")
		    } else cat("W: Skipping ",phen,name.suffix," 6m series, missing data.\n",sep="")

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
		    } else cat("W: Skipping ",phen,name.suffix," all series, missing data.\n",sep="")
    	        }
            }
        }
    }

    source("01_func_analyse.R",local=FALSE)


    if (info) {
	cat("\n")
        cat("split.chromosomes: ",split.chromosomes,"\n")
        cat("Objects in system: "); print(ls())
        cat("names(phenotypes.collection): ");print(names(phenotypes.collection))
    }



    if (!split.chromosomes) {
	cat("\n")
	cat("\n")
	cat("-------------------------------------------------------------------------------------\n")
	cat("               Analysis on all chromosomes together for project ", project.name,    "\n")
	cat("-------------------------------------------------------------------------------------\n")

	if (!file.exists(markers.filename)) {
		stop("Cannot find markers file expected at '",markers.filename,"\n")
	}

	if (1 == length(project.name)) {

	    if (!perform.singular.analysis) {
	    	stop("Configured not to run singular analyses but only got a single project")
	    }

	    if (project.name %in% names(phenotypes.collection)) {

		phens<-colnames(phenotypes.collection[[project.name]])
		if (is.null(phens)) {
			stop("This program is inconsistent. Have not found column names for phenotypes of project ",
			     project.name,".\n")
		}

		cat("I: Performing project ",project.name,"\n")

		current.cpu<-0
		number.of.cpus<-1
		for(phen.pos in 1:length(phens)) {
			phen<-phens[phen.pos]
			cat("\n"                                                                        )
			cat(      "**********************************************************\n"        )
			cat(paste("****** ",                        phen,            " ******\n",sep=""))
			cat(paste("       ",   "CPU ",current.cpu, " of ",number.of.cpus,   "\n",sep=""))
			cat(      "**********************************************************\n"        )
			cat("\n"                                                                        )
			if (current.cpu != phen.pos %% number.of.cpus) {
				cat(paste("skipped: ",phen.pos, "-> ",phen,"\n",sep=""))
				next;
			}

			ok<-analyse.all.chromosomes.together(phen=phen,individuals.subset="all",
							     data.phenotypes.source=project.name,
							     phenotypes.collection=phenotypes.collection,
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
							             data.phenotypes.source=project.name,
							     	     phenotypes.collection=phenotypes.collection,
								     generations=generations, model=model.el,
								     data.covariates=data.covariates,name.suffix=name.suffix,overwrite=overwrite,
								     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
				if (!ok) one.was.omitted<-TRUE
			} #stop("Just stopping to help development.")
		}
	    }
	} else {
		cat("*******************************\n")
		cat("*** combinatorical analyses ***\n")
		cat("*******************************\n")

		# Calculate all the single effects

		if (perform.singular.analysis) for(p.n in project.name) {
			phens<-colnames(phenotypes.collection[[p.n]])
			cat("Singular analysis of phens for '",p.n,"'.\n",sep="")
			num<-0
			for(p in phens) {
				if (p.n == "basic" && p %in% c("sex")) next;
				cat("               phen '",p,"'.\n",sep="")
				ok<-analyse.all.chromosomes.together(phen=p,
				     individuals.subset="all",
				     data.phenotypes.source=p.n,
				     phenotypes.collection=phenotypes.collection,
				     generations=generations, model=model,
				     data.covariates=NULL,
				     name.suffix=name.suffix,project.name=p.n,overwrite=overwrite,
				     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
				num <- num+1
				cat("I: Complete job #",num," of ",length(phens)," (",round(100*num/length(phens),2),"%).\n")
				cat("Memory garbage collector:\n"); print(gc())
			}
		}
		
		# Look at all the combinatorics

		num<-0
		for(p.n.outer in project.name) {
			cat("p.n.outer:",p.n.outer,"\n")
			for(p.n.inner in project.name) {
				cat("p.n.inner:",p.n.outer,"\n")
				if (p.n.inner == p.n.outer) next;
				phens.outer<-colnames(phenotypes.collection[[p.n.outer]])
				if (is.null(phens.outer)) stop("Phens outer is null")
				phens.inner<-colnames(phenotypes.collection[[p.n.inner]])
				if (is.null(phens.inner)) stop("Phens inner is null")

				for(p.outer in phens.outer) {
					if (p.n.outer == "basic" && p.outer %in% c("sex")) next;
					for(p.inner in phens.inner) {
						if (p.inner == p.outer) next;
						if (p.n.inner == "basic" && p.inner %in% c("sex","color")) next;
						cat("Running outer (",p.n.outer,":",p.outer,") against inner (",p.n.inner,":",p.inner,").\n")
						ok<-analyse.all.chromosomes.together(phen=p.outer,
						     individuals.subset="all",
						     #read.table.phenotypes=project.name,
						     data.phenotypes.source=p.n.outer,
				     		     phenotypes.collection=phenotypes.collection,
						     generations=generations, model=model,
						     data.covariates.source=p.n.inner,
						     data.covariates=p.inner,
						     name.suffix=name.suffix,
						     project.name=project.name,
						     overwrite=overwrite,
						     inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
						num<-num+1
						if (debug) {
						     cat("BREAK inner\n")
						     break
						}
						cat("I: Complete job #",num," of ",length(phens)," (",round(100*num/length(phens),2),"%).\n")
						cat("Memory garbage collector:\n"); print(gc())
					}
					if (debug) {
					     cat("BREAK outer\n")
					     break
					}
					break;
				}
			}
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
					read.table.phenotypes=read.table.phenotypes,
					generations=generations, model=model,
					inputdir=inputdir, outputdir=outputdir, missing.code=missing.code)
		}
		dev.off()
	}
    }

    return(list("phenotypes.colletion"=phenotypes.collection))

}
