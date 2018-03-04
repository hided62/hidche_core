<?php
require_once(__dir__.'/../../d_setting/conf.php');

$_currentToken = '_token_';

function getDB__token_(){
    $host = '_host_';
    $user = '_user_';
    $password = '_password_';
    $dbName = '_dbName_';
    $port = _port_;
    $encoding = 'utf8';

    static $uDB = NULL;

    if($uDB === NULL){
        $uDB = new MeekroDB($host,$user,$password,$dbName,$port,$encoding);
        $uDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;
    }

    return $uDB;
}

//XXX: 단일 서버 기준이고 외부에서 접근할일이 없다면 굳이 이런 구조를 할 이유가 없는데!

if(!function_exists('getDB')){
/**
 * DB 객체 생성
 * 
 * @return MeekroDB 
 */
function getDB($getToken='_token_'){
    $func = "getDB_{$getToken}";
    return $func();
}
}

if (!function_exists('getServPrefix')) {
function getServPrefix($getToken='_token_')
{
    return '_prefix_';
}
}