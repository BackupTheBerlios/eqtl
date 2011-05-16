
# This script expects the data to arrive from Andreia's Excel setup.
# It describes a series of manual steps to be performed in OpenOffice 
# to derive the tab-separated files and then the subsequent commands
# to prepare the input for happy.

#  C O N T R O L

# say what project to be actually working on
#project.name<-"baines"
#project.name<-"baines.relative.otu"
#project.name<-"baines.phylum"
#project.name<-"baines.genus"
#project.name<-"baines.unique.otu"
#project.name<-"baines.selected.above.90"
#project.name<-"mohan"
project.name<-"susen"
#project.name<-"susen.details"
#project.name<-NULL

# set to TRUE if data needs to be prepared, too
data.covariates<-NULL
#data.covariates<-c("sex","weight.6m")


#  P A R A M E T E R S

library(happy.hbrem)

markers.filename<-"markers.txt"
#missing.code="NA"
#missing.code="ND"

permute<-1000
#permute<-0

verbose<-F

# set to FALSE to skip tasks that seem to have been already computed
overwrite<-F

# set to true if existing files should be recreated for runs of happy
data.prepare<-F

# set if the data investigates has only two possible phenotypes
# but inspect the code again before doing so
data.binary <- F

model="additive"

generations=4

source("01_func_happy_start.R")
happy.start(project.name=project.name,
	generations=generations,
	model=model,
	permute=permute,
	data.covariates=data.covariates,
	data.prepare=data.prepare,
	data.binary=data.binary,
	split.chromosomes=F,
	overwrite=overwrite,
	verbose=verbose
)
