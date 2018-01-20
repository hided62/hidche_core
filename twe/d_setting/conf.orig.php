<?php
require_once(__dir__.'/../../d_setting/conf.php');

/**
 * DB 객체 생성
 * 
 * @return MeekroDB 
 */
function newDB(){
    $host = '_host_';
    $user = '_user_';
    $password = '_password_';
    $dbName = '_dbName_';
    $port = _port_;
    $encoding = 'utf8';

    return new MeekroDB($host,$user,$password,$dbName,$port,$encoding);
}