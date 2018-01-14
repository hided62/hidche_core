#!/bin/bash

SAM=che
SRC=~/svn/trunk/sam/che/
TAR=~/www/sam/${SAM}/
ALT=~/www/sam/${SAM}/

if [ -e $TAR ]
then
    echo The server is closed. Location: $TAR
    rsync -av --delete --exclude=.* --exclude=logs --exclude=data --exclude=d_setting $SRC $TAR
else
    echo The server is on service. Location: $ALT
    rsync -av --exclude=.* --exclude=logs --exclude=data --exclude=d_setting $SRC $ALT
fi
