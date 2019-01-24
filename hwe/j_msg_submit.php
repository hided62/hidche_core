<?php
namespace sammo;

include 'lib.php';
include 'func.php';

$post = Json::decode(Util::getReq('data', 'string', '{}'));
'@phan-var mixed[] $post';

$v = new Validator($post);
$v->rule('required', ['mailbox','text']);
$v->rule('integer', 'mailbox');
$v->rule('lengthMin', 'text', 1);

if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr(),
        'msgID'=>null
    ]);
}

$mailbox = (int)$post['mailbox'];
$text = StringUtil::cutStringForWidth($post['text'], 199, '');

$session = Session::requireGameLogin([
    'msgID'=>null
]); 
$userID = Session::getUserID();
//NOTE: 전송 메시지 시간 계산을 위해 Session을 쓰기 가능 상태로 열어둠.

increaseRefresh('서신전달', 1);

if(getBlockLevel() == 1 || getBlockLevel() == 3) {
    Json::die([
        'result' => false,
        'reason' => '차단되었습니다.',
        'msgID'=>null
    ]);
}

/**
 * 메시지 전송 코드.
 * 
 * TODO: 장기적으로 ajax는 한곳에 모을 필요가 있을 듯.
 */

$now = new \DateTime();
$unlimited = new \DateTime('9999-12-31');

$db = DB::db();
$me = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`level`,`con`,`picture`,`imgsvr`,penalty,permission FROM general WHERE `owner`=%i', $userID);

if(!$me){
    $session->logoutGame();
    Json::die([
        'result' => false,
        'reason' => '로그인되지 않았습니다.',
        'msgID' => null
    ]);
}

$con = checkLimit($me['con']);
if($con >= 2) { 
    Json::die([
        'result' => false,
        'reason' => '접속 제한입니다.',
        'msgID' => null
    ]);
 }

$me['icon'] = GetImageURL($me['imgsvr'], $me['picture']);
$permission = checkSecretPermission($me);
$srcNation = getNationStaticInfo($me['nation']);

$src = new MessageTarget($me['no'], $me['name'], $srcNation['nation'], $srcNation['name'], $srcNation['color'], $me['icon']);

// 전체 메세지
if($mailbox == Message::MAILBOX_PUBLIC) {
    $msg = new Message(
        Message::MSGTYPE_PUBLIC, 
        $src, 
        $src,
        $text, 
        $now, 
        $unlimited, 
        []
    );
    $msgID = $msg->send();
    Json::die([
        'result' => true,
        'reason' => 'SUCCESS',
        'msgID' => $msgID
    ]);


} 

// 국가 메세지
if($mailbox >= Message::MAILBOX_NATIONAL) {

    if($permission < 4){
        $destNationID = $me['nation'];
    }
    else{
        $destNationID = $mailbox - Message::MAILBOX_NATIONAL;
    }

    if($destNationID == $me['nation']){
        $msgType = Message::MSGTYPE_NATIONAL;
    }
    else{
        $msgType = Message::MSGTYPE_DIPLOMACY;
    }

    $destNation = getNationStaticInfo($destNationID);

    $dest = new MessageTarget(0, '', $destNation['nation'], $destNation['name'], $destNation['color']);

    $msg = new Message(
        $msgType,
        $src,
        $dest,
        $text,
        $now,
        $unlimited,
        []
    );
    $msgID = $msg->send();
    Json::die([
        'result' => true,
        'reason' => 'SUCCESS',
        'msgID' => $msgID
    ]);
}

// 개인 메세지
if($mailbox > 0) {
    $lastMsg = new \DateTime($session->lastMsg??'0000-00-00');

    $msg_interval = $now->getTimestamp() - $lastMsg->getTimestamp();
    //NOTE: 여기서 유저 레벨을 구별할 코드가 필요할까?
    if($msg_interval < 2){
        Json::die([
            'result' => false,
            'reason' => '개인메세지는 2초당 1건만 보낼 수 있습니다!',
            'msgID' => null
        ]);
    }
    
    $session->lastMsg = $now->format('Y-m-d H:i:s');

    $destUser = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`level`,`con`,`picture`,`imgsvr`,permission,penalty FROM general WHERE `no`=%i',$mailbox);
    
    if(!$destUser){
        Json::die([
            'result' => false,
            'reason' => '존재하지 않는 유저입니다.',
            'msgID' => null
        ]);
    }

    $destPermission = checkSecretPermission($destUser, false);
    if($permission == 4 && $destPermission == 4){
        Json::die([
            'result' => false,
            'reason' => '외교권자끼리는 메시지를 보낼 수 없습니다.',
            'msgID' => null
        ]);
    }

    $destNation = getNationStaticInfo($destUser['nation']);

    $dest = new MessageTarget(
        $destUser['no'],
        $destUser['name'],
        $destNation['nation'],
        $destNation['name'],
        $destNation['color'],
        GetImageURL($destUser['imgsvr'], $destUser['picture'])
    );

    $msg = new Message(
        Message::MSGTYPE_PRIVATE,
        $src,
        $dest,
        $text,
        $now,
        $unlimited,
        []
    );
    $msgID = $msg->send();
    Json::die([
        'result' => true,
        'reason' => 'SUCCESS',
        'msgID' => $msgID
    ]);
}

Json::die([
    'result' => false,
    'reason' => '알 수 없는 에러',
    'msgID' => null
]);
