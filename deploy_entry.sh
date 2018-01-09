#!/bin/bash

SRC=~/svn/trunk/sam/
TAR=~/www/sam/

echo Location: $SRC '<->' $TAR
rsync -avu --delete --exclude=.* --exclude=deploy* --exclude=d_* --exclude=e_* --exclude=che* --exclude=kwe* --exclude=pwe* --exclude=twe* --exclude=hwe* ~/svn/trunk/sam/ $TAR

