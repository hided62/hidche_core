<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 6){
    Json::die([
        'result'=>false,
        'reason'=>'관리자 아님'
    ]);
}

$fullReset  = Util::getPost('full_reset', 'bool', false);
$host = Util::getPost('db_host');
$port = Util::getPost('db_port', 'int');
$username = Util::getPost('db_id');
$password = Util::getPost('db_pw');
$dbName = Util::getPost('db_name');

if(!$host || !$port || !$username || !$password || !$dbName){
    Json::die([
        'result'=>false,
        'reason'=>'입력 값이 올바르지 않습니다'
    ]);
}

if($fullReset && class_exists('\\sammo\\DB')){
    $mysqli_obj = DB::db()->get();

    if($mysqli_obj->multi_query(file_get_contents(__DIR__.'/sql/reset.sql'))){
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
    FileUtil::delInDir(__DIR__."/logs");
    FileUtil::delInDir(__DIR__."/data");
    if(file_exists(__DIR__.'/d_setting/DB.php')){
        @unlink(__DIR__.'/d_setting/DB.php');
    }
    if(file_exists(__DIR__.'/d_setting/UniqueConst.php')){
        @unlink(__DIR__.'/d_setting/UniqueConst.php');
    }
}

$db = new \MeekroDB($host,$username,$password,$dbName,$port,'utf8mb4');
$db->connect_options[MYSQLI_OPT_INT_AND_FLOAT_NATIVE] = true;

$db->addHook('run_failed', function(){
    Json::die([
        'result'=>false,
        'reason'=>'DB 접속에 실패했습니다.'
    ]);
});

$mysqli_obj = $db->get();

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

//최소한의 테이블 처리를 위해 수동으로 초기화하도록 하자
if($mysqli_obj->multi_query(file_get_contents(__DIR__.'/sql/reset.sql'))){
    while(true){
        if (!$mysqli_obj->more_results()) {
            break;
        }
        if(!$mysqli_obj->next_result()){
            break;
        }
    }

}

if($mysqli_obj->multi_query(file_get_contents(__DIR__.'/sql/schema.sql'))){
    while(true){
        if (!$mysqli_obj->more_results()) {
            break;
        }
        if(!$mysqli_obj->next_result()){
            break;
        }
    }
}

ServConfig::getServerList()[$prefix]->closeServer();

Json::die([
    'result'=>true,
    'reason'=>'success'
]);