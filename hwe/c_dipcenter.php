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
$connect=$db->get();

$query = "select no,nation,level from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

//내가 수뇌부이어야함
if($me['level'] < 5) {
    header('location:b_myBossInfo.php');
    exit();
}

if($btn == "국가방침") {
    $msg == mb_substr($msg, 0, 1000);
    //$msg = StringUtil::
    $db->update('nation', [
        'msg'=>BadTag2Code($msg)
    ], 'nation=%i',$me['nation']);
} elseif($btn == "임관권유") {
    $scoutmsg == mb_substr($msg, 0, 500);
    $db->update('nation', [
        'scoutmsg'=>BadTag2Code($scoutmsg)
    ], 'nation=%i',$me['nation']);
} elseif($btn == "세율") {
    if($rate < 5)  { $rate = 5; }
    if($rate > 30) { $rate = 30; }
    $query = "update nation set rate='$rate' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
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
    $query = "update nation set war='0' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

header('location:b_dipcenter.php');

