<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Session.php');
require_once(ROOT.'/f_func/class._String.php');
require_once(ROOT.'/f_config/DB.php');

use utilphp\util as util;

$SESSION = new _Session();
if($SESSION->isLoggedIn()){
    $SESSION->logout();
}

$username = util::array_get($_POST['username']);
$password = util::array_get($_POST['password']);

if(!$username || !$password){
    returnJson([
        'result'=>false,
        'reason'=>'올바르지 않은 입력입니다.'
    ]);
}

$userInfo = getRootDB()->queryFirstRow(
    'SELECT `no`, `id`, `name`, `grade` '.
    'from member where id=%s_username AND '.
    'pw=sha2(concat(salt, %s_password, salt), 512)',[
        'username'=>$username,
        'password'=>$password
]);

if(!$userInfo){
    returnJson([
        'result'=>false,
        'reason'=>'아이디나 비밀번호가 올바르지 않습니다.'
    ]);
}

$SESSION->login($userInfo['no'], $userInfo['id'], $userInfo['grade']);
returnJson([
    'result'=>true,
    'reason'=>'로그인 되었습니다.'
]);