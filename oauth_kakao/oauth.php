<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

WebUtil::setHeaderNoCache();

$auth_code = Util::getReq('code');
if(!$auth_code){

    header('Location:oauth_fail.html');
}


//TODO: /oauth/token

$restAPI = new Kakao_REST_API_Helper('');
$result = $restAPI->create_access_token($auth_code);

//항상 scope = account_email profile임
//항상 token_type = bearer임

//토큰을 받아옴.



if (Util::array_get($result['expires_in'], -1) <= 0) {
    $errCode = $result['error_code'] ?? '(NoCode)';
    $error = $result['error'] ?? '(NoError)';
    $errorDesc = $result['error_description'] ?? '(NoDesc)';
    $msg = $result['msg'] ?? '(NoMsg)';

    die("알 수 없는 에러: {$errCode},{$error},{$errorDesc},{$msg}");
}

$session = Session::getInstance();

$restAPI->set_access_token($result['access_token']);
$now = new \DateTimeImmutable();
$session->access_token = $result['access_token'];
$session->expires =  $now->add(TimeUtil::secondsToDateInterval($result['expires_in']))->format('Y-m-d H:i:s');
$session->refresh_token = Util::array_get($result['refresh_token']);
$session->refresh_token_expires = $now->add(TimeUtil::secondsToDateInterval($result['refresh_token_expires_in']))->format('Y-m-d H:i:s');

$session->tmpx = Json::encode($result);

//echo "<br>\n";
$me = $restAPI->meWithEmail();

$oauth_mode = 'login';

$me['code'] = Util::array_get($me['code'], 0);
$signed = $me['has_signed_up']??false;
$kakao_account = $me['kakao_account']??[];
if(!($kakao_account['has_email']??false)||!($kakao_account['email']??false)){
    $oauth_mode = 'req_email';
}
else if(!$signed){
    $oauth_mode = 'join';
}
else if($me['code']< 0){
    switch(Util::array_get($me['msg'])){
    case 'NotRegisteredUserException':
        $oauth_mode = 'join';
        break;
    default:
        $oauth_mode = 'error';
    }
}
else{
    $session->kaccount_email = $kakao_account['email'];
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<script>
opener.location.href="javascript:postOAuthResult('<?=$oauth_mode?>');";
window.close();
</script>
</head>
<body>
로그인 완료. 곧 페이지로 이동합니다.
</body>
</html>