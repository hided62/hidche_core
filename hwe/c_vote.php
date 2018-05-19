<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$sel = Util::getReq('sel', 'int');
$comment = Util::getReq('comment');
$title = Util::getReq('title');
$str = Util::getReq('str');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

$query = "select develcost,vote,votecomment from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,vote,name,nation,horse,weap,book,item,npc from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($btn == "투표" && $me['vote'] == 0 && $sel > 0) {
    $develcost = $admin['develcost'] * 5;
    $query = "update general set gold=gold+{$develcost},vote='{$sel}' where owner='{$userID}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $log = [];
    $log = uniqueItem($me, $log, 1);
    pushGenLog($me, $log);
}
else if($btn == "댓글" && $comment != "") {
    $comment = str_replace("|", " ", $comment);
    $comment = str_replace(":", " ", $comment);
    $comment = trim($comment);
    $comment = addslashes(SQ2DQ($comment));

    $nation = getNationStaticInfo($me['nation']);

    if($admin['votecomment'] != "") { $admin['votecomment'] .= "|"; }
    $admin['votecomment'] .= "{$nation['name']}:{$me['name']}:{$comment}";

    $gameStor->votecomment = $admin['votecomment'];
}

if($session->userGrade < 5){
    header('location:a_vote.php');
    die();
}


if($btn == "수정") {
    if($title != "") {
        $vote = explode("|", $admin['vote']);
        $vote[0] = addslashes(SQ2DQ($title));
        $admin['vote'] = implode("|", $vote);
        $query = "update game set vote='{$admin['vote']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "추가") {
    if($str != "") {
        $str = addslashes(SQ2DQ($str));
        $admin['vote'] .= "|{$str}";
        $query = "update game set vote='{$admin['vote']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif($btn == "리셋") {
    $query = "update game set voteopen=1,vote='',votecomment=''";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "update general set vote='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "알림") {
    $query = "update general set newvote='1' where vote=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "숨김") {
    $query = "update game set voteopen=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "전체통계만") {
    $query = "update game set voteopen=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "전부") {
    $query = "update game set voteopen=2";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

header('location:a_vote.php');