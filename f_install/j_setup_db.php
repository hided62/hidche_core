<?php

require('_common.php');
require(__DIR__.'/../f_config/SETTING.php');
use utilphp\util as util;

session_start();

function dbConnFail($params){
    returnJson([
        'result'=>false,
        'reason'=>'DB 접속에 실패했습니다.'
    ]);
}

function dbSQLFail($params){
    returnJson([
        'result'=>false,
        'reason'=>'SQL을 제대로 실행하지 못했습니다. DB상태를 확인해 주세요.'
    ]);
}

$host = util::array_get($_POST['db_host']);
$port = util::array_get($_POST['db_port']);
$username = util::array_get($_POST['db_id']);
$password = util::array_get($_POST['db_pw']);
$dbName = util::array_get($_POST['db_name']);

if(!$host || !$port || !$username || !$password || !$dbName){
    returnJson([
        'result'=>false,
        'reason'=>'입력 값이 올바르지 않습니다'
    ]);
}

if(file_exists(ROOT.'/d_setting/conf.php') && is_dir(ROOT.'/d_setting/conf.php')){
    returnJson([
        'result'=>false,
        'reason'=>'d_setting/conf.php 가 디렉토리입니다'
    ]);
}

if($SETTING->isExist()){
    returnJson([
        'result'=>false,
        'reason'=>'이미 conf.php 파일이 있습니다'
    ]);
}

//파일 권한 검사
if(file_exists(ROOT.'/d_pic') && !is_dir(ROOT.'/d_pic')){
    returnJson([
        'result'=>false,
        'reason'=>'d_pic 이 디렉토리가 아닙니다'
    ]);
}

if(file_exists(ROOT.'/d_log') && !is_dir(ROOT.'/d_log')){
    returnJson([
        'result'=>false,
        'reason'=>'d_log 가 디렉토리가 아닙니다'
    ]);
}

if(!file_exists(ROOT.'/d_setting')){
    returnJson([
        'result'=>false,
        'reason'=>'d_setting 이 존재하지 않습니다'
    ]);
}

if(!is_writable(ROOT.'/d_pic')){
    returnJson([
        'result'=>false,
        'reason'=>'d_pic 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if(!is_writable(ROOT.'/d_log')){
    returnJson([
        'result'=>false,
        'reason'=>'d_log 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if(!is_writable(ROOT.'/d_setting')){
    returnJson([
        'result'=>false,
        'reason'=>'d_setting 디렉토리의 쓰기 권한이 없습니다.'
    ]);
}

//기본 파일 생성
if(!file_exists(ROOT.'/d_pic')){
    mkdir(ROOT.'/d_pic');
}

if(!file_exists(ROOT.'/d_log')){
    mkdir(ROOT.'/d_log');
}

if(!file_exists(ROOT.'/d_log/.htaccess')){
    @file_put_contents(ROOT.'/d_log/.htaccess', 'Deny from  all');
}

if(!file_exists(ROOT.'/d_setting/.htaccess')){
    @file_put_contents(ROOT.'/d_setting/.htaccess', 'Deny from  all');
}

//DB 접근 권한 검사

$rootDB = new MeekroDB($host,$username,$password,$dbName,$port,'utf8');
$rootDB->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;

$rootDB->throw_exception_on_nonsql_error = false;
$rootDB->nonsql_error_handler = 'dbConnFail';
$rootDB->error_handler = 'dbSQLFail';

$mysqli_obj = $rootDB->get(); //로그인에 실패할 경우 자동으로 dbConnFail()이 실행됨.

$mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/common_schema.sql'));
$globalSalt = bin2hex(random_bytes(16));

$result = generateFileUsingSimpleTemplate(
    ROOT.'/d_setting/conf.orig.php',
    ROOT.'/d_setting/conf.php',[
        'host'=>$host,
        'user'=>$username,
        'password'=>$password,
        'dbName'=>$dbName,
        'port'=>$port,
        'globalSalt'=>$globalSalt
    ]
);

if($result !== true){
    returnJson([
        'result'=>false,
        'reason'=>$result
    ]);
}

returnJson([
    'result'=>true,
    'reason'=>'NYI'
]);