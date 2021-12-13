<?php

namespace sammo;

include "lib.php";
include "func.php";
// $btn, $msg, $scoutmsg, $rate, $bill, $secretlimit

$btn = Util::getPost('btn');
//$msg = Util::getPost('msg');
//$scoutmsg = Util::getPost('scoutMsg');
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
if ($permission < 0) {
    header('location:b_myBossInfo.php', true, 303);
    exit();
} else if ($me['officer_level'] < 5 && $permission != 4) {
    header('location:b_myBossInfo.php', true, 303);
    exit();
}

$nationID = $me['nation'];
$nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');

/*if ($btn == "국가방침 수정") {
    $msg = mb_substr($msg, 0, 16384);
    //$msg = StringUtil::
    $nationStor->notice = WebUtil::htmlPurify($msg);
} elseif ($btn == "임관 권유문 수정") {
    $scoutmsg = mb_substr($scoutmsg, 0, 1000);
    $nationStor->scout_msg = WebUtil::htmlPurify($scoutmsg);
} else*/if ($btn == "세율") {
    $rate = Util::valueFit($rate, 5, 30);
    $db->update('nation', [
        'rate' => $rate,
    ], 'nation=%i', $nationID);
} elseif ($btn == "지급률") {
    $bill = Util::valueFit($bill, 20, 200);
    $db->update('nation', [
        'bill' => $bill
    ], 'nation=%i', $nationID);
} elseif ($btn == "기밀권한") {
    $secretlimit = Util::valueFit($secretlimit, 1, 99);
    $db->update('nation', [
        'secretlimit' => $secretlimit
    ], 'nation=%i', $nationID);
} elseif ($btn == "임관 금지") {
    $db->update('nation', [
        'scout' => 1
    ], 'nation=%i', $nationID);
} elseif ($btn == "임관 허가") {
    $db->update('nation', [
        'scout' => 0
    ], 'nation=%i', $nationID);
} elseif ($btn == "전쟁 금지") {
    $avilableCnt = $nationStor->getValue('available_war_setting_cnt') ?? 0;
    if ($avilableCnt > 0) {
        $db->update('nation', [
            'war' => 1
        ], 'nation=%i', $nationID);
        $nationStor->setValue('available_war_setting_cnt', $avilableCnt - 1);
    }
} elseif ($btn == "전쟁 허가") {
    $avilableCnt = $nationStor->getValue('available_war_setting_cnt') ?? 0;
    if ($avilableCnt > 0) {
        $db->update('nation', [
            'war' => 0
        ], 'nation=%i', $nationID);
        $nationStor->setValue('available_war_setting_cnt', $avilableCnt - 1);
    }
}

header('location:b_dipcenter.php');
