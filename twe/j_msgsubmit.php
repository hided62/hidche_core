<?php
// $msg, $genlist

include "lib.php";
include "func.php";
require_once('../e_lib/util.php');
require_once('func_message.php');


$post = parseJsonPost();

if(!isset($post['genlist']) || !isset($post['msg'])){
    header('Content-Type: application/json');
    die(json_encode([
        'result' => false,
        'reason' => '올바르지 않은 호출입니다.',
        'redirect' => NULL
    ]));
}


$dest = $post['dest'];
$msg = $post['msg'];
$datetime = new DateTime();
$date = $datetime->format('Y-m-d H:i:s');

//로그인 검사
if(!CheckLoginEx($db)){
    header('Content-Type: application/json');
    die(json_encode([
        'result' => false,
        'reason' => '로그인되지 않았습니다.',
        'redirect' => NULL
    ]));
}


$db = newDB();

$connect = dbConn();
increaseRefresh($connect, "서신전달", 1);

//$msg,$genlist 두가지 값을 받

if(CheckBlock($connect) == 1 || CheckBlock($connect) == 3) {
    header('Content-Type: application/json');
    die(json_encode([
        'result' => false,
        'reason' => '차단되었습니다.',
        'redirect' => NULL
    ]));
}




$conlimit = $db->queryFirstField("select conlimit from game where no=1");

$me = $db->queryFirstRow('select `no`,`name`,`nation`,`level`,`msgindex`,`userlevel`,`con`,`picture`,`imgsvr` from `general` where `user_id` = %s_p_id',
    array('p_id'=>$_SESSION['p_id']));

$con = checkLimit($me['userlevel'], $me['con'], $conlimit);
if($con >= 2) { 
    header('Content-Type: application/json');
    die(json_encode([
        'result' => false,
        'reason' => '접속 제한입니다.',
        'redirect' => NULL
    ]));
 }

//FIXME: 원래는 필요없는 값이지만 예전 코드와 꼬일 수 있어 유지함.
$msg = str_replace("|", "", $msg);

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

$src = $me['no'];
$src_name = $me['name'];
$src_icon = $me['picture'];
$src_nation_id = $me['nation'];
if($src_nation_id == 0) {
    $src_nation = '재야';
    $src_color = '#FFFFFF';
}
else{
    $nation = $db->queryFirstRow("select nation,name,color from nation where nation=%d_nation",array(
        'nation' => $src_nation_id
    ));
    $src_nation = $nation['name'];
    $src_color = '#'.$nation['color'];
    $src_color = str_replace('##', '#', $src_color); //FIXME: nation table에서 color가 #포함된 걸로 바뀔 경우를 대비
}

// 전체 메세지
if($dest == 9999) {

    $db->insert('message', array(
        'address' => $dest,
        'type' => 'global',
        'src' => $src,
        'dest' => $dest,
        'time' => $date,
        'src_name' => $src_name,
        'src_nation' => $src_nation,
        'src_color' => $src_color,
        'src_icon' => $src_icon,
        'message' => $msg
    ));
    
    //PushMsg(1, 0, $me['picture'], $me['imgsvr'], "{$me['name']}:", $nation['color'], $nation['name'], $nation['color'], $msg);
// 국가 메세지
} elseif($dest >= 9000) {
    $real_nation = $dest - 9000;
    $query = "select nation,name,color from nation where nation='$genlist'";
    $nation = $db->queryFirstRow("select nation,name,color from nation where nation=%d_nation",array(
        'nation' => $real_nation
    ));
    
    if($nation === NULL || empty($nation)){
        $dest = 9998;
        $dest_nation = '재야';
        $dest_color = '#FFFFFF';   
    }
    else{
        $dest_nation = $nation['name'];
        $dest_color = '#'.$nation['color'];
        $dest_color = str_replace('##', '#', $dest_color); //FIXME: nation table에서 color가 #포함된 걸로 바뀔 경우를 대비
    }

    
    if($nation['nation'] == $me['nation'] || $me['level'] < 5){
        $db->insert('message', array(
            'address' => $dest,
            'type' => 'receive_nation',
            'src' => $src,
            'dest' => $dest,
            'time' => $date,
            'src_name' => $src_name,
            'src_nation' => $src_nation,
            'src_color' => $src_color,
            'src_icon' => $src_icon,
            'dest_nation' => $dest_nation,
            'dest_color' => $dest_color,
            'message' => $msg
        ));
    }
    else{
        $db->insert('message', array(
            'address' => $src_nation_id + 9000,
            'type' => 'send_nation',
            'src' => $src,
            'dest' => $dest,
            'time' => $date,
            'src_name' => $src_name,
            'src_nation' => $src_nation,
            'src_color' => $src_color,
            'src_icon' => $src_icon,
            'dest_nation' => $dest_nation,
            'dest_color' => $dest_color,
            'message' => $msg
        ));
        $db->insert('message', array(
            'address' => $dest,
            'type' => 'receive_nation',
            'src' => $src,
            'dest' => $dest,
            'time' => $date,
            'src_name' => $src_name,
            'src_nation' => $src_nation,
            'src_color' => $src_color,
            'src_icon' => $src_icon,
            'dest_nation' => $dest_nation,
            'dest_color' => $dest_color,
            'message' => $msg
        ));
    }

// 개인 메세지
} elseif($dest > 0) {
    $last_msg = new DateTime(util::array_get($_SESSION['last_msg'], '0000-00-00'));

    $msg_interval = $datetime->getTimestamp() - $last_msg->getTimestamp();
    if($msg_interval < 2){
        header('Content-Type: application/json');
        die(json_encode([
            'result' => false,
            'reason' => "개인메세지는 2초당 1건만 보낼 수 있습니다!",
            'redirect' => NULL
        ]));
    }

    $_SESSION['last_msg'] = $date;

    $dest_user = $db->queryFirstRow('select `no`,`name`,`nation` from `general` where `user_id` = %s_p_id',array(
        'p_id'=>$dest));

    if($dest_user == NULL || empty($dest_user)){
        header('Content-Type: application/json');
        die(json_encode([
            'result' => false,
            'reason' => '존재하지 않는 유저입니다.',
            'redirect' => NULL
        ]));
    }

    $dest_name = $dest_user['name'];
    if($dest_user['nation'] == 0){
        $dest_nation = $nation['name'];
        $dest_color = '#'.$nation['color'];
        $dest_color = str_replace('##', '#', $dest_color);
    }
    else{
        $nation = $db->queryFirstRow("select nation,name,color from nation where nation=%d_nation",array(
            'nation' => $dest_user['nation']
        ));
        $dest_nation = $nation['name'];
        $dest_color = '#'.$nation['color'];
        $dest_color = str_replace('##', '#', $dest_color); //FIXME: nation table에서 color가 #포함된 걸로 바뀔 경우를 대비
    }

    $db->insert('message', array(
        'address' => $src_nation_id + 9000,
        'type' => 'send',
        'src' => $src,
        'dest' => $dest,
        'time' => $date,
        'src_name' => $src_name,
        'src_nation' => $src_nation,
        'src_color' => $src_color,
        'src_icon' => $src_icon,
        'dest_name' => $dest_name,
        'dest_nation' => $dest_nation,
        'dest_color' => $dest_color,
        'message' => $msg
    ));
    $db->insert('message', array(
        'address' => $dest,
        'type' => 'receive',
        'src' => $src,
        'dest' => $dest,
        'time' => $date,
        'src_name' => $src_name,
        'src_nation' => $src_nation,
        'src_color' => $src_color,
        'src_icon' => $src_icon,
        'dest_name' => $dest_name,
        'dest_nation' => $dest_nation,
        'dest_color' => $dest_color,
        'message' => $msg
    ));
}
else{
    header('Content-Type: application/json');
    die(json_encode([
        'result' => false,
        'reason' => "알 수 없는 에러",
        'redirect' => NULL
    ]));
}

//echo "<script>location.replace('msglist.php');</script>";
//echo 'msglist.php';//TODO:debug all and replace

header('Content-Type: application/json');
echo json_encode([
    'result' => true,
    'reason' => 'SUCCESS',
    'redirect' => 'msglist.php',
    'page_target' => '#msglist'
]);