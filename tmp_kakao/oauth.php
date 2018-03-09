<?php
require('_common.php');
require(ROOT.'/f_config/DB.php');
require(ROOT.'/f_func/class._Time.php');
require('kakao.php');
use utilphp\util as util;

$auth_code = util::array_get($_GET['code']);
if(!$auth_code){
    
    header('Location:oauth_fail.html');
}


//TODO: /oauth/token

$restAPI = new Kakao_REST_API_Helper();
$result = $restAPI->create_access_token($auth_code);

//항상 scope = account_email profile임
//항상 token_type = bearer임

//토큰을 받아옴.



if(util::array_get($result['expires_in'], -1) > 0){
    session_start();
    $restAPI->set_access_token($result['access_token']);
    $now = _Time::DatetimeNow();
    $_SESSION['access_token'] = $result['access_token'];
    $_SESSION['expires'] = _Time::DatetimeFromSecond($now, $result['expires_in']);
    $_SESSION['refresh_token'] = util::array_get($result['refresh_token']);
    $_SESSION['refresh_token_expires'] = _Time::DatetimeFromSecond($now, $result['refresh_token_expires_in']);
}
else{
    die('알 수 없는 에러:'.$me['msg']);
}

$_SESSION['tmpx'] = json_encode($result,JSON_UNESCAPED_UNICODE);

//echo "<br>\n";
$me = $restAPI->meWithEmail();

var_dump($me);

$me['code'] = util::array_get($me['code'], 0);
if($me['code']< 0){
    switch($me['msg']){
    case 'NotRegisteredUserException':
        header('Location:join.php', true, 303);
        die();
    default:
        die('알 수 없는 에러:'.$me['msg']);
    }
}



//이메일 주소를 받아옴

//
//$db = getRootDB();
//TODO: 로그인된 유저인지 확인해야함.
