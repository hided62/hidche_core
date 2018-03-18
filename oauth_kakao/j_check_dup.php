<?php
require_once('_common.php');
require('lib.join.php');


use utilphp\util as util;

session_start();

$access_token = util::array_get($_SESSION['access_token']);
if(!$access_token){
    returnJson('로그인 토큰 에러. 다시 로그인을 수행해주세요.');
}


$value = util::array_get($_POST['value']);
switch(util::array_get($_POST['type'])){
case 'nickname':
    returnJson(checkNicknameDup($value));
case 'username':
    returnJson(checkUsernameDup($value));
}

returnJson(false);
