<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');
require('lib.join.php');

WebUtil::requireAJAX();


$session = Session::getInstance()->setReadOnly();

$access_token = $session->access_token;
if(!$access_token){
    Json::die('로그인 토큰 에러. 다시 로그인을 수행해주세요.');
}


$value = Util::getPost('value');
switch(Util::getPost('type')){
case 'nickname':
    Json::die(checkNicknameDup($value));
case 'username':
    Json::die(checkUsernameDup($value));
}

Json::die(false);
