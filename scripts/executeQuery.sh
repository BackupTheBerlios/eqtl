#!/bin/sh

TIMEOUT=70 R CMD BATCH evaluateQuery.R 
cd myTmp
tar cf ../evaluatedQueries.tar *.gz
