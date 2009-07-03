
doc<-"

=head1 NAME

scatter_all.R - prepare scatter plots for all covariates

=head1 SYNOPSIS

to be started from within the R shell with no parameters

=head1 DESCRIPTION

This script plots the chromosomal location of the QTL 
against the chromosomal location of the gene it affects.

=head1 AUTHORS

Steffen Moeller <moeller@inb.uni-luebeck.de>,
Melanie Thessen-Hedreul,
Maja Jagodic,

=head1 COPYRIGHT

University of Luebeck and Karolinska Institute, Stockholm

=cut

"

library(RMySQL)

con <- dbConnect(MySQL(),host="pc15.inb.uni-luebeck.de",db="eQTL_Stockholm",user="qtl")

# find maximal cM for every chromosome

q <- "SELECT Chromosome, MAX(cMorgan_Peak) as max FROM qtl GROUP BY Chromosome;"
chromosomal.length.cM<-dbGetQuery(con,q)
rownames(chromosomal.length.cM)<-chromosomal.length.cM[,1]
cM.offset<-c(0,cumsum(chromosomal.length.cM[c(1:20,"X"),"max"]))
names(cM.offset)<-c(1:20,"X","Y")


# find maximal bp for every chromosome

q <- "SELECT seqname, MAX((start+stop)/2) as max FROM BEARatChip GROUP BY seqname;"
chromosomal.length.bp<-dbGetQuery(con,q)
rownames(chromosomal.length.bp)<-chromosomal.length.bp[,1]
bp.offset<-c(0,cumsum(chromosomal.length.bp[c(1:20,"X"),"max"]))
names(bp.offset)<-c(1:20,"X","Y")


# Find covariates to work on

q <- "select distinct covariates from qtl;"
covariates<-dbGetQuery(con,q)[,"covariates"]




# do it for all covariates


pdf("/nfshome/moeller/public_html/scatter_all.pdf")

for(cov in covariates) {

  cat("Working on covariate '",cov,"'\n")

  # Input for plot

  q <- paste("SELECT ",
	      "Trait,LOD,Chromosome,cMorgan_Peak,covariates,seqname,(start+stop)/2 as pos ",
           "FROM ",
              "qtl JOIN BEARatChip ON probeset_id=Trait JOIN trait ON probeset_id=trait_id ",
           "WHERE ",
              "covariates='",cov,"' AND LOD>=4 AND mean>=100",
	   ";",sep="")

Trait, Locus, LOD, covariates, Chromosome, cMorgan_Peak, cMorgan_Min, cMorgan_Max, Quantile, (LOD-Quantile) as LODdiff , unigene, swissprot_ID, gene_assignment, first_symbol,
       first_name, ProbeSequence, seqname as chr_name, strand, start as gene_chrom_start,stop as gene_chrom_end,
       trait.mean, trait.sd 

 SELECT Trait, Locus, LOD, covariates, Chromosome, cMorgan_Peak, cMorgan_Min, cMorgan_Max, Quantile, (LOD-Quantile) as LODdiff , unigene, swissprot_ID, gene_assignment, first_symbol,
       first_name, ProbeSequence, seqname as chr_name, strand, start as gene_chrom_start,stop as gene_chrom_end,
       trait.mean, trait.sd 
 FROM qtl left join BEARatChip as c on Trait=c.probeset_id join trait on qtl.Trait=trait.trait_id WHERE Chromosome in ('2') AND mean >= 100 AND covariates='bd_int' AND LOD >= 4 ORDER BY LOD DESC



  selected.qtl<-dbGetQuery(con,q)


  pos.linear.cM<-apply(selected.qtl,
      1,
      function(X){
	 linearCentiMorgan<-cM.offset[X[3]]
	 offset<-as.numeric(X[4])
         return(linearCentiMorgan+offset)
      }
  )

  pos.linear.bp<-apply(selected.qtl,
      1,
      function(X){
	 linearBP<-bp.offset[X[6]]
	 offset<-as.numeric(X[7])
         return(linearBP+offset)
      }
  )


  plot.new()
  plot(pos.linear.cM,pos.linear.bp,
     xlim=c(0,max(cM.offset)),ylim=c(0,max(bp.offset)),
     axes=FALSE,main=cov,sub=paste("LODs>4,6,8, mean>100"))
  abline(v=cM.offset, col="lightgray")
  abline(h=bp.offset, col="lightgray")

  in.red<-selected.qtl[,"LOD"]>=6
  points(pos.linear.cM[in.red],  pos.linear.bp[in.red],  col="red")
 
  in.green<-selected.qtl[,"LOD"]>=8
  points(pos.linear.cM[in.green],pos.linear.bp[in.green],col="green")

  axis(1, at=cM.offset[1:length(cM.offset)-1],
        labels=c(1:20,"X"))
  axis(2, at=bp.offset[1:length(bp.offset)-1],
        labels=c(1:20,"X"))

}

dev.off()
