<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getPost('btn');
$sel = Util::getPost('sel', 'int');
$comment = Util::getPost('comment');
$title = Util::getPost('title');
$str = Util::getPost('str');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$isVoteAdmin = in_array('vote', $session->acl[DB::prefix()]??[]);
$isVoteAdmin |= $session->userGrade >= 5;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$admin = $gameStor->getValues(['develcost', 'cost', 'vote_title', 'vote', 'votecomment']);

$generalID = Session::getGeneralID();

$general = General::createGeneralObjFromDB($generalID, ['vote','horse','weapon','book','item','npc'], 1);

if($btn == "투표" && $general->getVar('vote') == 0 && $sel > 0) {
    $develcost = $admin['develcost'] * 5;
    $db->update('general', [
        'gold'=>$db->sqleval('gold + %i', $develcost),
        'vote'=>$sel
    ], 'owner=%i', $userID);

    if(tryUniqueItemLottery($general, '투표')){
        $general->applyDB($db);
    }
}
else if($btn == "댓글" && trim($comment) != "") {
    $comment = StringUtil::neutralize($comment);

    $nation = $general->getStaticNation();

    if(!$admin['votecomment']){
        $admin['votecomment'] = [];
    }
    $admin['votecomment'][] = [$nation['name'],$general->getName(),$comment];
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