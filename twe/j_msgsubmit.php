<?php
include 'lib.php';
include 'func.php';

use utilphp\util as util;

//읽기 전용이다. 빠르게 세션 끝내자
session_write_close();

$post = parseJsonPost();

if(!isset($post['genlist']) || !isset($post['msg'])){
    returnJson([
        'result' => false,
        'reason' => '올바르지 않은 호출입니다.',
        'redirect' => NULL
    ]);
}


$destMailbox = $post['dest_mailbox'];
$msg = $post['msg'];
$datetime = new DateTime();
$date = $datetime->format('Y-m-d H:i:s');

//로그인 검사
if(!isSigned()){
    returnJson([
        'result' => false,
        'reason' => '로그인되지 않았습니다.',
        'redirect' => NULL
    ]);
}


$db = getDB();

$connect = dbConn();
increaseRefresh($connect, '서신전달', 1);

if(getBlockLevel() == 1 || getBlockLevel() == 3) {
    returnJson([
        'result' => false,
        'reason' => '차단되었습니다.',
        'redirect' => NULL
    ]);
}

$conlimit = $db->queryFirstField('select conlimit from game where no=1');

$me = $db->queryFirstRow('select `no`,`name`,`nation`,`level`,`msgindex`,`userlevel`,`con`,`picture`,`imgsvr` from `general` where `user_id` = %s_p_id',
    array('p_id'=>$_SESSION['p_id']));

$con = checkLimit($me['userlevel'], $me['con'], $conlimit);
if($con >= 2) { 
    returnJson([
        'result' => false,
        'reason' => '접속 제한입니다.',
        'redirect' => NULL
    ]);
 }

//FIXME: 원래는 필요없는 값이지만 예전 코드와 꼬일 수 있어 유지함.
$msg = str_replace('|', '', $msg);

//TODO: 몰라서 임시로 값 세팅해봄. 추후에 용도를 확인하고 수정 필요
//$s = 50;
//$msg = _String::SubStrForWidth($msg, $s, 198);

//SubStrForWidth는 반각은 1, 전각은 2로 측정하는듯 보이나, 대부분 글자수 단위로 카운트 하고 있어 mb_substr로 처리함.
$msg = mb_substr($msg, 0, 99, 'UTF-8');
$msg = trim($msg);

if($msg == ''){
    header('Content-Type: application/json');
    die(json_encode([
        'result' => true,
        'reason' => 'SUCCESS',
        'redirect' => 'msglist.php',
        'page_target' => '#msglist'
    ]));
}

$src = [
    'id' => $me['no'],
    'name' => $me['name'],
    'icon' => $me['picture'],
    'nation_id' => $me['nation'],
    'nation' => null
];
if($src['nation_id'] != 0) {
    $nation = $db->queryFirstRow('select nation,name,color from nation where nation=%i',$src_nation_id);
    $src['nation'] = $nation['name'];
    $src['color'] = '#'.$nation['color'];
    $src['color'] = str_replace('##', '#', $src['color']); //FIXME: nation table에서 color가 #포함된 걸로 바뀔 경우를 대비
}

// 전체 메세지
if($destMailbox == 9999) {
    sendMessage('public', $src, [], $msg, $date);
// 국가 메세지
} elseif($destMailbox >= 9000) {

    if($me['level'] < 5){
        $real_nation = $me['nation_id'];
    }
    else{
        $real_nation = $dest - 9000;
    }
    $nation = $db->queryFirstRow('select nation,name,color from nation where nation=%i',$real_nation);
    
    if($nation === NULL || empty($nation)){
        $dest = ['nation_id' => 0];
    }
    else{
        $color = '#'.$nation['color'];
        $color = str_replace('##', '#', $color); 
        //FIXME: nation table에서 color가 #포함된 걸로 바뀔 경우를 대비
        $dest = [
            'nation_id' => $nation['nation'],
            'color' => $color
        ];
    }

    sendMessage('national', $src, $dest, $msg, $date);

// 개인 메세지
} elseif($destMailbox > 0) {
    $last_msg = new DateTime(util::array_get($_SESSION['last_msg'], '0000-00-00'));

    $msg_interval = $datetime->getTimestamp() - $last_msg->getTimestamp();
    //NOTE: 여기서 유저 레벨을 구별할 코드가 필요할까?
    if($msg_interval < 2){
        returnJson([
            'result' => false,
            'reason' => '개인메세지는 2초당 1건만 보낼 수 있습니다!',
            'redirect' => NULL
        ]);
    }

    
    $destUser = $db->queryFirstRow('select `no`,`name`,`nation` from `general` where `user_id` = %s',$destMailbox);

    if($destUser == NULL || empty($destUser)){
        returnJson([
            'result' => false,
            'reason' => '존재하지 않는 유저입니다.',
            'redirect' => NULL
        ]);
    }

    $_SESSION['last_msg'] = $date;

    $dest = [
        'id' => $destMailbox,
        'name' => $dest_user['name']
    ];
    if($dest_user['nation'] != 0){
        $nation = $db->queryFirstRow('select nation,name,color from nation where nation=%i',$dest_user['nation']);
        
        $color = $nation['color'];
        $color = str_replace('##', '#', $color); 
        //FIXME: nation table에서 color가 #포함된 걸로 바뀔 경우를 대비
        $dest['color'] = $color;
        $dest['nation'] = $nation['name'];
        $dest['nation_id'] = $nation['nation'];
    }
    sendMessage('private', $src, $dest, $msg, $date);
}
else{
    returnJson([
        'result' => false,
        'reason' => '알 수 없는 에러',
        'redirect' => NULL
    ]);
}

returnJson([
    'result' => true,
    'reason' => 'SUCCESS',
    'redirect' => 'msglist.php',
    'page_target' => '#msglist'
]);