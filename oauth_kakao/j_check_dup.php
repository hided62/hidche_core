<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');
require('lib.join.php');




session_start();

$access_token = Util::array_get($_SESSION['access_token']);
if(!$access_token){
    Json::die('로그인 토큰 에러. 다시 로그인을 수행해주세요.');
}


$value = Util::array_get($_POST['value']);
switch(Util::array_get($_POST['type'])){
case 'nickname':
    Json::die(checkNicknameDup($value));
case 'username':
    Json::die(checkUsernameDup($value));
}

Json::die(false);
