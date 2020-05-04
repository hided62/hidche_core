<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $msg, $scoutmsg, $rate, $bill, $secretlimit

$btn = Util::getPost('btn');
$msg = Util::getPost('msg');
$scoutmsg = Util::getPost('scoutmsg');
$rate = Util::getPost('rate', 'int');
$bill = Util::getPost('bill', 'int');
$secretlimit = Util::getPost('secretlimit', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();

$me = $db->queryFirstRow('SELECT `no`,nation,`officer_level`,permission,penalty FROM general WHERE `owner`=%i', $userID);

//내가 수뇌부이어야함
$permission = checkSecretPermission($me);
if($permission < 0){
    header('location:b_myBossInfo.php', true, 303);
    exit();
}
else if ($me['officer_level'] < 5 && $permission != 4) {
    header('location:b_myBossInfo.php', true, 303);
    exit();
}

if($btn == "국가방침 수정") {
    $msg = mb_substr($msg, 0, 16384);
    //$msg = StringUtil::
    $db->update('nation', [
        'msg'=>WebUtil::htmlPurify($msg)
    ], 'nation=%i',$me['nation']);
} elseif($btn == "임관 권유문 수정") {
    $scoutmsg = mb_substr($scoutmsg, 0, 1000);
    $db->update('nation', [
        'scoutmsg'=>WebUtil::htmlPurify($scoutmsg)
    ], 'nation=%i',$me['nation']);
} elseif($btn == "세율") {
    $rate = Util::valueFit($rate, 5, 30);
    $db->update('nation', [
        'rate'=>$rate,
    ], 'nation=%i', $me['nation']);
} elseif($btn == "지급률") {
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

