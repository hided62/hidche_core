#!/bin/bash

SAM=pwe
SRC=~/svn/branches/che_devel/
TAR=~/www/sam/${SAM}/
ALT=~/www/sam/${SAM}/

if [ -e $TAR ]
then
    echo The server is closed. Location: $TAR
    rsync -avu --delete --exclude=.* --exclude=logs --exclude=data --exclude=d_setting $SRC $TAR
else
    echo The server is on service. Location: $ALT
    rsync -avu --exclude=.* --exclude=logs --exclude=data --exclude=d_setting $SRC $ALT
fi

