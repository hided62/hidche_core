<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$sel = Util::getReq('sel', 'int');
$comment = Util::getReq('comment');
$title = Util::getReq('title');
$str = Util::getReq('str');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$isVoteAdmin = in_array('vote', $session->acl[DB::prefix()]??[]);
$isVoteAdmin |= $session->userGrade >= 5;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$admin = $gameStor->getValues(['develcost', 'cost', 'vote_title', 'vote', 'votecomment']);

$me = $db->queryFirstRow('SELECT no,vote,name,nation,horse,weapon,book,item,npc from general where owner=%i', $userID);

if($btn == "투표" && $me['vote'] == 0 && $sel > 0) {
    $develcost = $admin['develcost'] * 5;
    $db->update('general', [
        'gold'=>$db->sqleval('gold + %i', $develcost),
        'vote'=>$sel
    ], 'owner=%i', $userID);

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
    header('location:a_vote.php', true, 303);
    die();
}


if($btn == "수정") {
    if($title != "") {
        $gameStor->vote_title = WebUtil::htmlPurify($title);
    }
} elseif($btn == "추가") {
    if($str != "") {
        if(!$admin['vote']){
            $admin['vote'] = [];
        }
        $admin['vote'][] = WebUtil::htmlPurify($str);
        $gameStor->vote=$admin['vote'];
    }
} elseif($btn == "리셋") {
    $gameStor->voteopen=1;
    $gameStor->vote=['-'];
    $gameStor->vote_title = '-';
    $gameStor->votecomment=[];

    $db->update('general', [
        'vote'=>0
    ], true);
} elseif($btn == "알림") {
    $db->update('general', [
        'newvote'=>1
    ], 'vote=0');
} elseif($btn == "숨김") {
    $gameStor->voteopen = 0;
} elseif($btn == "전체통계만") {
    $gameStor->voteopen = 1;
} elseif($btn == "전부") {
    $gameStor->voteopen = 2;
}

header('location:a_vote.php', true, 303);