<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 6){
    Json::die([
        'result'=>false,
        'reason'=>'관리자 아님'
    ]);
}

$fullReset  = Util::getReq('full_reset', 'bool', false);
$host = Util::getReq('db_host');
$port = Util::getReq('db_port', 'int');
$username = Util::getReq('db_id');
$password = Util::getReq('db_pw');
$dbName = Util::getReq('db_name');

if(!$host || !$port || !$username || !$password || !$dbName){
    Json::die([
        'result'=>false,
        'reason'=>'입력 값이 올바르지 않습니다'
    ]);
}

if($fullReset && class_exists('\\sammo\\DB')){
    $mysqli_obj = DB::db()->get();

    if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/reset.sql'))){
        while(true){
            if (!$mysqli_obj->more_results()) {
                break;
            }
            if(!$mysqli_obj->next_result()){
                break;
            }
        }
    }
}
if($fullReset){
    FileUtil::delInDir(__dir__."/logs");
    FileUtil::delInDir(__dir__."/data");
    if(file_exists(__dir__.'/d_setting/DB.php')){
        @unlink(__dir__.'/d_setting/DB.php');
    }
    if(file_exists(__dir__.'/d_setting/UniqueConst.php')){
        @unlink(__dir__.'/d_setting/UniqueConst.php');
    }
}

function dbConnFail($params){
    Json::die([
        'result'=>false,
        'reason'=>'DB 접속에 실패했습니다.'
    ]);
}


$db = new \MeekroDB($host,$username,$password,$dbName,$port,'utf8mb4');
$db->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;

$db->throw_exception_on_nonsql_error = false;
$db->nonsql_error_handler = 'dbConnFail';


$mysqli_obj = $db->get(); //로그인에 실패할 경우 자동으로 dbConnFail()이 실행됨.

$prefix = basename(__DIR__);

$result = Util::generateFileUsingSimpleTemplate(
    __DIR__.'/d_setting/DB.orig.php',
    __DIR__.'/d_setting/DB.php',[
        'host'=>$host,
        'user'=>$username,
        'password'=>$password,
        'dbName'=>$dbName,
        'port'=>$port,
        'prefix'=>$prefix
    ], true
);



if($result !== true){
    Json::die([
        'result'=>false,
        'reason'=>$result
    ]);
}

ResetHelper::clearDB();

AppConf::getList()[$prefix]->closeServer();

Json::die([
    'result'=>true,
    'reason'=>'success'
]);