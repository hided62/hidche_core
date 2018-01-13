#!/bin/bash

while [ 1 ]
do

    php -c /etc/php.ini -f ./proc.php
    sleep 60

done

