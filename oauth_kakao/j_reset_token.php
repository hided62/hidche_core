<?php
namespace sammo;

require(__DIR__.'/../vendor/autoload.php');

use \kakao\Kakao_REST_API_Helper as Kakao_REST_API_Helper;

$session = Session::requireLogin([
    'reason'=>'로그인이 되어있지 않습니다'
]);


$userID = Session::getUserID();

$tokenValidUntil = RootDB::db()->queryFirstField('SELECT token_valid_until from member where no=%i',$userID);

if(!$tokenValidUntil){
    Json::die([
        'result'=>false,
        'reason'=>'연장 가능한 로그인 상태가 아닙니다.'
    ]);
}

$now = TimeUtil::now();
$expectedDate = TimeUtil::nowAddDays(5);

if($expectedDate <= $tokenValidUntil){
    Json::die([
        'result'=>false,
        'reason'=>'아직 연장 가능하지 않습니다.'
    ]);
}

RootDB::db()->update('member', [
    'token_valid_until'=> null
], 'no=%i', $userID);

$session->logout();

Json::die([
    'result'=>true,
    'reason'=>'연장 완료.'
]);
