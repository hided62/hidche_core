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

$isVoteAdmin = in_array('vote', $session->acl[DB::prefix()]??[]);
$isVoteAdmin |= $session->userGrade >= 5;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

$admin = $gameStor->getValues(['develcost', 'cost', 'vote_title', 'vote', 'votecomment']);

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
else if($btn == "댓글" && trim($comment) != "") {
    $comment = trim($comment);

    $nation = getNationStaticInfo($me['nation']);

    if(!$admin['votecomment']){
        $admin['votecomment'] = [];
    }
    $admin['votecomment'][] = [$nation['name'],$me['name'],$comment];
    $gameStor->votecomment = $admin['votecomment'];
}

if(!$isVoteAdmin){
    header('location:a_vote.php');
    die();
}


if($btn == "수정") {
    if($title != "") {
        $gameStor->vote_title = $title;
    }
} elseif($btn == "추가") {
    if($str != "") {
        if(!$admin['vote']){
            $admin['vote'] = [];
        }
        $admin['vote'][] = $str;
        $gameStor->vote=$admin['vote'];
    }
} elseif($btn == "리셋") {
    $gameStor->voteopen=1;
    $gameStor->vote=['-'];
    $gameStor->vote_title = '-';
    $gameStor->votecomment=[];

    $query = "update general set vote='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "알림") {
    $query = "update general set newvote='1' where vote=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "숨김") {
    $gameStor->voteopen = 0;
} elseif($btn == "전체통계만") {
    $gameStor->voteopen = 1;
} elseif($btn == "전부") {
    $gameStor->voteopen = 2;
}

header('location:a_vote.php');