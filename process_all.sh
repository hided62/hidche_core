#!/bin/bash



TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
echo [$TIME] --- All start. ---



HOUR=`date -d "0 hours" "+%H"`
HOUR=`expr ${HOUR} + 0`



TAR=/home/jwh1807/www/sam/che_close/
if [ -e $TAR ]
then
    TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
    echo [$TIME] che is closed.
else
    curl 62che.com/sam/che/proc.php
    TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
    echo [$TIME] che finish.
fi



if [ $HOUR -ge 9 -a $HOUR -lt 21 ]
then
    TAR=/home/jwh1807/www/sam/kwe_close/
    if [ -e $TAR ]
    then
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] kwe is closed.
    else
        curl 62che.com/sam/kwe/proc.php
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] kwe finish.
    fi

    TAR=/home/jwh1807/www/sam/pwe_close/
    if [ -e $TAR ]
    then
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] pwe is closed.
    else
        curl 62che.com/sam/pwe/proc.php
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] pwe finish.
    fi

    TAR=/home/jwh1807/www/sam/twe_close/
    if [ -e $TAR ]
    then
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] twe is closed.
    else
        curl 62che.com/sam/twe/proc.php
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] twe finish.
    fi

    TAR=/home/jwh1807/www/sam/hwe_close/
    if [ -e $TAR ]
    then
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] hwe is closed.
    else
        curl 62che.com/sam/hwe/proc.php
        TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
        echo [$TIME] hwe finish.
    fi
else
    TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
    echo [$TIME] Now is night.
fi

TIME=`date -d "0 hours" "+%H:%M:%S.%N"`
echo [$TIME] --- All finish. ---
