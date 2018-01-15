<?php
// $msg, $genlist

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();
increaseRefresh($connect, "서신전달", 1);

if(CheckBlock($connect) == 1 || CheckBlock($connect) == 3) {
    $msg = "";
    $genlist = 0;
}

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,level,msgindex,userlevel,con,picture,imgsvr from general where user_id='$_SESSION['p_id']'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['userlevel'], $me['con'], $admin['conlimit']);
if($con >= 2) { echo "<script>window.top.main.location.replace('main.php');</script>"; exit(); }

$msg = str_replace("|", "", $msg);
$msg = trim($msg);

$msg = _String::SubStrForWidth($msg, $s, 198);

$date = date('Y-m-d H:i:s');

// 전체 메세지
if($genlist == 9999 && str_replace(" ", "", $msg) != "") {
    if($me['nation'] == 0) {
        $nation['name'] = '재야';
        $nation['color'] = 'FFFFFF';
    } else {
        $query = "select nation,name,color from nation where nation='$me['nation']'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);
    }
    PushMsg(1, 0, $me['picture'], $me['imgsvr'], "{$me['name']}:", $nation['color'], $nation['name'], $nation['color'], $msg);
// 국가 메세지
} elseif($genlist >= 9000 && $msg != "") {
    if($me['nation'] == 0) {
        $nation['name'] = '재야';
        $nation['color'] = 'FFFFFF';
    } else {
        $query = "select nation,name,color from nation where nation='$me['nation']'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);
    }

    $genlist -= 9000;
    $query = "select nation,name,color from nation where nation='$genlist'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dest = MYDB_fetch_array($result);

    if($nation['nation'] == $dest['nation']) {
        PushMsg(2, $nation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:", $nation['color'], $dest['name'], $dest['color'], $msg);
    } else {
        //타국에 보내는 경우
        PushMsg(2, $nation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$nation['name']}▶", $nation['color'], $dest['name'], $dest['color'], $msg);
        // 수뇌이면 발송, 아니면 자국으로 돌림
        if($me['level'] >= 5) {
            PushMsg(3, $dest['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$nation['name']}▶", $nation['color'], $dest['name'], $dest['color'], $msg);
        } else {
            PushMsg(2, $nation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$nation['name']}▶", $nation['color'], $dest['name'], $dest['color'], "반송");
        }
    }
// 개인 메세지
} elseif($genlist > 0 && $msg != "") {
    $query = "select name,msgindex from general where no='$genlist'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $you = MYDB_fetch_array($result);
    //발신, 수신인 코딩
    $who = $me['no'] * 10000 + $genlist;

    $msg = addslashes(SQ2DQ($msg));

    $query = "select msg{$me['msgindex']}_when as priv_when from general where no='$me['no']'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $prev_msg = MYDB_fetch_array($result);
    $diff_second = strtotime($date) - strtotime($prev_msg['priv_when']);
    
    if($diff_second < 3) {
        $who = 1 * 10000 + $me['no'];    // 운영자가 본인에게
        $msg = "개인메세지는 3초당 1건만 보낼 수 있습니다!";
        //자신에게 표시
        $me['msgindex']++;
        if($me['msgindex'] >= 10) { $me['msgindex'] = 0; }
        $query = "update general set msgindex='$me['msgindex']',msg{$me['msgindex']}='$msg',msg{$me['msgindex']}_type='10',msg{$me['msgindex']}_who='$who',msg{$me['msgindex']}_when='$date',newmsg=1 where no='$me['no']'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        //자신에게 표시
        $me['msgindex']++;
        if($me['msgindex'] >= 10) { $me['msgindex'] = 0; }
        $query = "update general set msgindex='$me['msgindex']',msg{$me['msgindex']}='$msg',msg{$me['msgindex']}_type='9',msg{$me['msgindex']}_who='$who',msg{$me['msgindex']}_when='$date' where no='$me['no']'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        
        // 상대에게 발송
        $you['msgindex']++;
        if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
        $query = "update general set msgindex='$you['msgindex']',msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_type='10',msg{$you['msgindex']}_who='$who',msg{$you['msgindex']}_when='$date',newmsg=1 where no='$genlist'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    
    $fp = fopen("logs/_gen_msg.txt", "a");
    //로그 파일에 기록
    fwrite($fp, _String::Fill($me['name'],12," ")." > "._String::Fill($you['name'],12," ")." | {$msg}\r\n");
    fclose($fp);
}

//echo "<script>location.replace('msglist.php');</script>";
echo 'msglist.php';//TODO:replace

