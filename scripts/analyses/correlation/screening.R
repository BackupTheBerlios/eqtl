
#    A T T E N T I O N:   Edit the template, not this file !!!!

source('basedir//scripts/analyses/R/maxcminc.R')
source('basedir//scripts/analyses/R/load.data.R')
source('basedir//scripts/analyses/R/analyseCorrelationForMarkers.R')


# For the stockholm dataset
# Interesting pairs
#	analyzeclusters(21504,105,draw=T)
#	analyzeclusters(6816,28400,draw=T)
#	analyzeclusters(2543,5908,draw=T)
#	analyzeclusters(23118,10615,draw=T)
#	analyzeclusters(8578,3725,draw=T)
#	analyzeclusters(10759,7348,draw=T)
#	analyzeclusters(22454,5680,draw=T)
# boring one
#	3558 17756
# mostly linear
#	21816 20956
#	2709 27492
#	23081 24478
e1 <- 1444
e2 <- 3821
m <- 134
#r<-evalresiduals(e1,e2)
#analyzeclusters(e1, e2, m, T)
#
#
#e1 <- 1444
#e2 <- 7199
#m <- 99
#analyzeclusters(e1, e2, m, T)
#
#

##### Analyze the difference of means for markers for different expression pairs
# the results are complex, thus the will be saved to file such that intermediate
# results can be recovered after crash
e1 <- 1444
#fname <- paste(c("test", e1, "csv"), collapse=".")
#for (e2 in (e1+1):dim(expr)[1]) {
#  r <- evalresiduals(e1, e2)
#  m <- maxc(r)
#  analyzeclusters(e1, e2, 134, F)
#  # This value function is for comparing, higher values are better
#  v <- max(r) / sqrt(var(r))
#  data <- paste(c(paste(c(e1, e2, m, v), collapse=", "), "\n"), collapse="")
#  # Save to file
#  cat(data, file=fname, append=T)
#}



##### Analyse one interesting pair (1444, 3821)
# Do a k-means analysis on given pair
cp <- c(1444, 3821)
kmeansres <- kmeans(t(expr[cp,]), 2)
# Draw the results
plot(t(expr[cp,]), col=kmeansres$cluster)
points(kmeansres$centers, col=1:2, pch=8, cex=2)

# Evaluate k-means clusters (this was just for testing and can be deleted in my opinion)
c1 <- which(kmeansres$cluster==1)
c2 <- which(kmeansres$cluster==2)
ce <- kmeansres$centers
d <- ce[2,] - ce[1,]
dis <- sqrt(d[1]^2 + d[2]^2)
p1 <- sqrt(var(colSums(d * expr[cp, c1])))
p2 <- sqrt(var(colSums(d * expr[cp, c2])))
evalPair <- function(expr, pair, show) {
  kmeansres <- kmeans(t(expr[pair,]), 2)
   if (show) {
    plot(t(expr[pair,]), col=kmeansres$cluster)
    points(kmeansres$centers, col=1:2, pch=8, cex=2)
  }
  c1 <- which(kmeansres$cluster==1)
  c2 <- which(kmeansres$cluster==2)
  ce <- kmeansres$centers
  d <- ce[2,] - ce[1,]
  dis <- sqrt(d[1]^2 + d[2]^2)
  p1 <- sqrt(var(colSums(d * expr[pair, c1])))
  p2 <- sqrt(var(colSums(d * expr[pair, c2])))
  return((p1 + p2) / dis)
}

# Find a high correleation of clusters to other expression values (10 examples)
maxi <- maxc(abs(cor(kmeansres$cluster, t(expr[-cp,]))), 10)
plot(t(expr[c(cp[1], cp[2]),]), col=kmeansres$cluster)
p<-par(ask)
par(ask=T)
for (i in 1:10) {
  plot(t(expr[c(cp[2],maxi[i]),]), col=kmeansres$cluster)
}
par(ask=p)

# Decision tree for clusters of cp on markers
library(rpart)
dtdata <- as.data.frame(kmeansres$cluster)
colnames(dtdata)[1] <- "cluster"
dtdata <- cbind(dtdata, t(marker))
dtdataframe <- data.frame(dtdata)
mnames <- rownames(marker)
# Create formula
f <- paste(c("cluster~", paste(mnames, collapse="+")), collapse="")
dtc <- rpart.control(xval=1, minbucket=2, minsplit=4, cp=0.02)
# Build tree
r <- rpart(formula = f, data = dtdataframe, method="class", control=dtc)
# Draw tree
plot(r, branch=.3, compress=T)
text(r)



##### 3D-Scatterplot example
scatterexample <- function() {
  c1 = rep(5:10, times=6)
  c2 = rep(5:10, each=6)
  c3 = 3 - 5*c1 - 5*c2 + c1 * c2 + rnorm(6*6)
  c <- data.frame(c1 = c1, c2 = c2, c3 = c3)
  s3d <- scatterplot3d(c, type="h", highlight.3d=TRUE, 
         angle=75, scale.y=0.7, pch=16, main="scatterplot3d - 5")
  my.lm <- lm(c3 ~ c1 + c2 + c1:c2)$coefficients
  c3p <- my.lm[1]+my.lm[2]*c1+my.lm[3]*c2+my.lm[4]*c1*c2
  s3d$points3d(data.frame(c1, c2, c3p))
}



##### Search for expression values corresponding to EAE
c <- cor(eae[[1]], t(expr[,eae[[2]]]))
numcand <- 15
cand <- minc(abs(c), numcand)
# Search for linear model for AEA
lmdata <- data.frame(t(rbind(eae = eae[[1]] , expr[cand, eae[[2]]])))
cn <- colnames(lmdata)
res <- c()
for (fac1 in 2:(length(cn)-1)) {
  for (fac2 in (fac1+1):length(cn)) {
    f <- as.formula(paste("eae ~ ", cn[fac1], "+", cn[fac2], "+", cn[fac1], ":", cn[fac2], collapse=""))
    mylm <- lm(f, data = lmdata)
    res <- rbind(res, c(fac1, fac2, mean(abs(mylm$residuals))))
  }
}
print(res)

x <- rnorm(15)
y <- 10*x + rnorm(15)
predict(lm(y ~ x))
# this is not finished yet, one could further used the linear model for a predictor
# and evaluate the quality of such a classification.



##### Build decision tree directly from markers
library(rpart)
dtdata <- matrix(eae[[1]])
colnames(dtdata)[1] <- "eae"
dtdata <- cbind(dtdata, t(marker[, eae[[2]]]))
dtdataframe <- data.frame(dtdata)
mnames <- rownames(marker)
# Create formula
f <- paste(c("eae~", paste(mnames, collapse="+")), collapse="")
dtc <- rpart.control(xval=1, minbucket=2, minsplit=4, cp=0.02)
# Build tree
r <- rpart(formula = f, data = dtdataframe, method="class", control=dtc)
# Draw tree
plot(r, branch=.3, compress=T)
text(r)





##### This stuff is old and not used anymore
#correl <- cor(eae, t(expr[,ms]))

testexpr <- abs(cor(t(expr[1:100,])))
testexpr[testexpr == 1] <- 0
which(testexpr > 0.992, arr.ind=T)


# Calculate entropy of probabilities p
entropy <- function(p) {
  p.sum <- sum(p)
  if (min(p) < 0 || p.sum <= 0)
    return(NA)
  p.norm <- p[p > 0] / p.sum
  return(-sum(log2(p.norm) * p.norm))
}


#entropy(c(sum(eae>0),sum(eae<0)))

# eae = sign(f1 * gen1 + f2 * gen2 + f3 * gen1 * gen2)


