#!/usr/bin/R

doc<-"

=head1 NAME

regularExpressionAnalyses.R - prepare upload of correlation data

=head1 SYNOPSIS

echo source('regularExpressionAnalyses.R') | R --vanilla --no-save

=head1 DESCRIPTION

This script calculates 

=over 4

=item T-Test

=item Wilcoxin rank test

=back

For (initially) only the binary scores, i.e. EAE, sex and cross.

=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

University of LE<uuml>beck, Germany, 2009

University Clinics of Schleswig-Holstein, LE<uuml>beck, Germany, 2011

=cut

"

TEMPLATEWARNINGHASH

#
# Learning how to retrieve the various data files
#
source("R/load.data.R")

m<-t(load.data("expression")$numeric)
d.m<-load.data("scores")

# find binary phenotypes
d.m.quantity <- apply(d.m,2,function(X){
	length(table(X))
})

#m.test<-m[,1:200]

if (0) {
 # helping lines for debugging
 covar <- colnames(d.m)[1]
 covar <- colnames(d.m)[2]
}


for(covar in colnames(d.m) ) {

	cat("Working on phenotype '",covar,"'\n",sep="")
	covar.table<-table(d.m[,covar])

	if (2==d.m.quantity[covar]) {
		covar.table.names<-names(covar.table)
		#  indices for the expression data to decide what values to compare
		individuals.small<-which(d.m[,covar]==covar.table.names[1])
		individuals.large<-which(d.m[,covar]==covar.table.names[2])

	} else {
		# comparing quartiles
		quartiles<-quantile(probs=c(25,75)/100,d.m[,covar],na.rm=T)
		if (quartiles["25%"]==quartiles["75%"]) {
			warning(paste("Covar ",covar,": quartiles[25%]==quartiles[75%]==",quartiles["75%"]," - skipped\n",sep=""))
			next
		}
		individuals.small<-which(d.m[,covar]<=quartiles["25%"])
		individuals.large<-which(d.m[,covar]>=quartiles["75%"])
	}

	individuals.small.names<-rownames(d.m)[individuals.small]
	individuals.large.names<-rownames(d.m)[individuals.large]

	individuals.small.names.matching <- individuals.small.names[individuals.small.names %in% rownames(m)]
	individuals.large.names.matching <- individuals.large.names[individuals.large.names %in% rownames(m)]

	# ordering of expression data
	f<-apply(m,2,function(X){
		a<-t.test(X[individuals.small.names.matching],X[individuals.large.names.matching],paired=FALSE)
		b<-wilcox.test(X[individuals.small.names.matching],X[individuals.large.names.matching],paired=FALSE)
		return(c(t=a$p.value,wilcox=b$p.value))
	})
	write.table(t(f),file=paste("tests_",covar,".tsv",sep=""),
			append=FALSE,sep="\t",quote=FALSE,row.names=TRUE,col.names=TRUE)
}

