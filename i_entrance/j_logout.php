<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

$session = Session::requireLogin([
    'result'=>true,
    'reason'=>'로그인 되지 않았습니다'
]);
$userID = Session::getUserID();


// 외부 파라미터

RootDB::db()->insert('member_log',[
    'member_no'=>$userID,
    'action_type'=>'logout',
    'action'=>Json::encode([
        'ip'=>Util::get_client_ip(true)
    ])
]);

$session->logout();
$session->setReadOnly();
setcookie("hello", "", time()-3600);

Json::die([
    'result'=>true
]);