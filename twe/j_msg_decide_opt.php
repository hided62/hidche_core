<?php
include 'lib.php';
include 'func.php';

//{msgID: 1206, response: true}

use utilphp\util as util;

$generalID = getGeneralID();

if (!$generalID) {
    returnJson([
        'result'=>false,
        'reason'=>'로그인하지 않음'
    ]);
}

session_write_close(); // 이제 세션 안 쓴다

$jsonPost = parseJsonPost();

$msgID = toInt(util::array_get($jsonPost['msgID'], null), false);
$msgResponse = util::array_get($jsonPost['response'], null);

if ($msgID === null || !is_bool($msgResponse)) {
    returnJson([
        'result'=>false,
        'reason'=>'올바르지 않은 인자'
    ]);
}


$result = getSingleMessage($msgID, $generalID);

if (!$result[0]) {
    returnJson([
        'result'=>false,
        'reason'=>$result[1]
    ]);
}

$messageInfo = $result[1];

$msgType = $messageInfo['type'];

$validUntil = $messageInfo['valid_until'];
$date = date('Y-m-d H:i:s');
if ($validUntil < $date) {
    returnJson([
        'result'=>false,
        'reason'=>'만료된 메시지'
    ]);
}

$msgOption = json_decode(util::array_get($messageInfo['option'], '{}'));
$msgAction = util::array_get($msgOption['action'], null);
$messageInfo['option'] = $msgOption;

$msgSrc = json_decode($messageInfo['src']);
$messageInfo['src'] = $msgSrc;
$msgDest = json_decode($messageInfo['dest']);
$messageInfo['dest'] = $msgDest;

if (!$msgAction) {
    returnJson([
        'result'=>false,
        'reason'=>'응답 대상 메시지 아님'
    ]);
}

if ($msgType == 'diplomacy' && $msgSrc['nation_id'] != $msgDest['nation_id']) {
    //여기로 올일이 있나?
    returnJson([
        'result'=>false,
        'reason'=>'(버그) 외교 대상 동일'
    ]);
}

if($msgType == 'diplomacy'){
    if(!getNationStaticInfo($msgSrc['nation_id'])){
        returnJson([
            'result'=>false,
            'reason'=>'발신 국가 없음'
        ]);
    }

    if(!getNationStaticInfo($msgDest['nation_id'])){
        returnJson([
            'result'=>false,
            'reason'=>'수신 국가 없음'
        ]);
    }

    if($general['nation'] != $msgDest['nation_id']){
        returnJson([
            'result'=>false,
            'reason'=>'올바르지 않은 소속 국가'
        ]);
    }
    
    if($general['level'] < 6){
        returnJson([
            'result'=>false,
            'reason'=>'외교 권한 없음'
        ]);
    }    
}

if(!$msgResponse){
    switch($msgAction){
    case 'scout':
        $result = declineScout($messageInfo);
        break;
    case 'ally':
        $result = declineAlly($messageInfo);
        break;
        //TODO:기타 등등
    default:
        $result = [false, '처리 대상 아님'];
    }

    returnJson([
        'result' => $result[0],
        'reason' => $result[1]
    ]);
}

switch ($msgAction) {
case 'scout':
    //TODO: 등용장 받음. 함수 호출
    $result = acceptScout($messageInfo);
    break;
case 'ally':
    $result = acceptAlly($messageInfo);
    break;
    //TODO:기타 등등
case '':
    returnJson([]);
    break;
default:
    //구현이 정상적으로 된 경우 이쪽으로 오지 않음
    $result = [false, '처리 대상 아님'];
}


returnJson([
    'result' => $result[0],
    'reason' => $result[1]
]);