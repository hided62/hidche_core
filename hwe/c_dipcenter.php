<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $msg, $scoutmsg, $rate, $bill, $secretlimit

$btn = Util::getReq('btn');
$msg = Util::getReq('msg');
$scoutmsg = Util::getReq('scoutmsg');
$rate = Util::getReq('rate', 'int');
$bill = Util::getReq('bill', 'int');
$secretlimit = Util::getReq('secretlimit', 'int');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$me = $db->queryFirstRow('SELECT `no`,nation,`level` FROM general WHERE `owner`=%i', $userID);

//내가 수뇌부이어야함
if($me['level'] < 5) {
    header('location:b_myBossInfo.php');
    exit();
}

if($btn == "국가방침") {
    $msg = mb_substr($msg, 0, 16384);
    //$msg = StringUtil::
    $db->update('nation', [
        'msg'=>WebUtil::htmlPurify($msg)
    ], 'nation=%i',$me['nation']);
} elseif($btn == "임관권유") {
    $scoutmsg = mb_substr($scoutmsg, 0, 1000);
    $db->update('nation', [
        'scoutmsg'=>WebUtil::htmlPurify($scoutmsg)
    ], 'nation=%i',$me['nation']);
} elseif($btn == "세율") {
    $rate = Util::valueFit($rate, 5, 30);
    $db->update('nation', [
        'rate'=>$rate,
    ], 'nation=%i', $me['nation']);
} elseif($btn == "지급율") {
    $bill = Util::valueFit($bill, 20, 200);
    $db->update('nation', [
        'bill'=>$bill
    ], 'nation=%i',$me['nation']);
} elseif($btn == "기밀권한") {
    $secretlimit = Util::valueFit($secretlimit, 1, 99);
    $db->update('nation', [
        'secretlimit'=>$secretlimit
    ], 'nation=%i',$me['nation']);
} elseif($btn == "임관 금지") {
    $db->update('nation', [
        'scout'=>1
    ], 'nation=%i',$me['nation']);
} elseif($btn == "임관 허가") {
    $db->update('nation', [
        'scout'=>0
    ], 'nation=%i',$me['nation']);
} elseif($btn == "전쟁 금지") {
    $db->update('nation', [
        'war'=>1
    ], 'nation=%i',$me['nation']);
} elseif($btn == "전쟁 허가") {
    $db->update('nation', [
        'war'=>0
    ], 'nation=%i',$me['nation']);
}

header('location:b_dipcenter.php');

