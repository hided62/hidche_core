<?php
require(__dir__.'/../vendor/autoload.php');

/**
 * 비밀번호 해시용 전역 SALT 반환
 * 비밀번호는 sha512(usersalt|sha512(globalsalt|password|globalsalt)|usersalt); 순임
 * 
 * @return string
 */
function getGlobalSalt(){
    return '_tK_globalSalt_';
}

/**
 * 서버 주소 반환. 서버의 경로가 하부 디렉토리인 경우에 하부 디렉토리까지 포함
 * 
 * @return string
 */
function getServerBasepath(){
    return '_tK_serverBasePath_';
}

/**
 * DB 객체 생성
 * 
 * @return MeekroDB 
 */
function getRootDB(){
    $host = '_tK_host_';
    $user = '_tK_user_';
    $password = '_tK_password_';
    $dbName = '_tK_dbName_';
    $port = _tK_port_;
    $encoding = 'utf8';

    static $uDB = null;

    if($uDB === null){
        $uDB = new MeekroDB($host,$user,$password,$dbName,$port,$encoding);
        $uDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;
    }

    return $uDB;
}
