<?php
require_once(__dir__.'/../../d_setting/conf.php');

/**
 * DB 객체 생성
 * 
 * @return \MeekroDB 
 */
function getDB(){
    $host = '_tK_host_';
    $user = '_tK_user_';
    $password = '_tK_password_';
    $dbName = '_tK_dbName_';
    $port = _tK_port_;
    $encoding = 'utf8';

    static $uDB = null;

    if($uDB === null){
        $uDB = new \MeekroDB($host,$user,$password,$dbName,$port,$encoding);
        $uDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;
    }

    return $uDB;
}

function getServPrefix()
{
    return '_tK_prefix_';
}