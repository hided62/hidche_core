<?php
namespace sammo;

require_once('_common.php');

$session = Session::requireLogin();



// 외부 파라미터

RootDB::db()->insert('member_log',[
    'member_no'=>$session->userID,
    'action_type'=>'logout',
    'action'=>Json::encode([
        'ip'=>Util::get_client_ip(true)
    ])
]);

$session->access_token = null;
$session->logout();
$session->setReadOnly();
setcookie("hello", "", time()-3600);

Json::die([
    'result'=>true
]);