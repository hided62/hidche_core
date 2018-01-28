<?php
require_once(__dir__.'/../../d_setting/conf.php');

/**
 * DB 객체 생성
 * 
 * @return MeekroDB 
 */
function getDB(){
    $host = '_host_';
    $user = '_user_';
    $password = '_password_';
    $dbName = '_dbName_';
    $port = _port_;
    $encoding = 'utf8';

    static $uDB = NULL;

    if($uDB === NULL){
        $uDB = new MeekroDB($host,$user,$password,$dbName,$port,$encoding);
    }

    return $uDB;
}