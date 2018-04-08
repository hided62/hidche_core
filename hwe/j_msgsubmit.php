<?php
namespace sammo;

include 'lib.php';
include 'func.php';


$session = Session::requireGameLogin([
    'newSeq' => false
]); 
$userID = Session::getUserID();
//NOTE: 전송 메시지 시간 계산을 위해 Session을 쓰기 가능 상태로 열어둠.

/**
 * 메시지 전송 코드.
 * 
 * TODO: 장기적으로 ajax는 한곳에 모을 필요가 있을 듯.
 */

$post = WebUtil::parseJsonPost();

if(!isset($post['genlist']) || !isset($post['msg'])){
    Json::die([
        'result' => false,
        'reason' => '올바르지 않은 호출입니다.',
        'newSeq' => false
    ]);
}


'@phan-var mixed[] $post';

$destMailbox = $post['dest_mailbox'];
$msg = $post['msg'];
$datetime = new \DateTime();
$date = $datetime->format('Y-m-d H:i:s');

$db = DB::db();
$connect=$db->get();

increaseRefresh('서신전달', 1);

if(getBlockLevel() == 1 || getBlockLevel() == 3) {
    Json::die([
        'result' => false,
        'reason' => '차단되었습니다.',
        'newSeq' => false
    ]);
}

$conlimit = $db->queryFirstField('select conlimit from game limit 1');

$me = $db->queryFirstRow('select `no`,`name`,`nation`,`level`,`con`,`picture`,`imgsvr` from `general` where `owner` = %i', $userID);

if(!$me){
    $session->logoutGame();
    Json::die([
        'result' => false,
        'reason' => '로그인되지 않았습니다.',
        'newSeq' => false
    ]);
}

$con = checkLimit($me['con'], $conlimit);
if($con >= 2) { 
    Json::die([
        'result' => false,
        'reason' => '접속 제한입니다.',
        'newSeq' => false
    ]);
 }

$msg = StringUtil::cutStringForWidth($msg, 100, '');

if($msg == ''){
    Json::die([
        'result' => true,
        'reason' => 'SUCCESS',
        'newSeq' => false
    ]);
}

$src = [
    'id' => $me['no'],
    'name' => $me['name'],
    'icon' => $me['picture'],
    'nation_id' => $me['nation'],
];

// 전체 메세지
if($destMailbox == 9999) {
    sendMessage('public', $src, [], $msg, $date);
// 국가 메세지
} elseif($destMailbox >= 9000) {

    if($me['level'] < 5){
        $real_nation = $me['nation_id'];
    }
    else{
        $real_nation = $destMailbox - 9000;
    }

    if(!getNationStaticInfo($real_nation)){
        $real_nation = $me['nation_id'];
    }

    $dest = [
        'nation_id' => $real_nation
    ];

    sendMessage('national', $src, $dest, $msg, $date);

// 개인 메세지
} elseif($destMailbox > 0) {
    $last_msg = new \DateTime($session->last_msg?:'0000-00-00');

    $msg_interval = $datetime->getTimestamp() - $last_msg->getTimestamp();
    //NOTE: 여기서 유저 레벨을 구별할 코드가 필요할까?
    if($msg_interval < 2){
        Json::die([
            'result' => false,
            'reason' => '개인메세지는 2초당 1건만 보낼 수 있습니다!',
            'newSeq' => false
        ]);
    }
    
    $destUser = $db->queryFirstRow('select `no`,`name`,`nation` from `general` where `no` = %s',$destMailbox);

    if($destUser == null || empty($destUser)){
        Json::die([
            'result' => false,
            'reason' => '존재하지 않는 유저입니다.',
            'newSeq' => false
        ]);
    }

    $session->last_msg = $date;

    $dest = [
        'id' => $destMailbox,
        'name' => $destUser['name'],
        'nation_id' => $destUser['nation']
    ];

    sendMessage('private', $src, $dest, $msg, $date);
}
else{
    Json::die([
        'result' => false,
        'reason' => '알 수 없는 에러',
        'newSeq' => false
    ]);
}

Json::die([
    'result' => true,
    'reason' => 'SUCCESS',
    'newSeq' => true
]);