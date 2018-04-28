<?php
namespace sammo;

include "lib.php";
include "func.php";


$session = Session::requireGameLogin()->setReadOnly();
if($session->userGrade < 4){
    header('location:_119.php');
    die();
}

$v = new Validator($_POST);
$v->rule('integer', [
    'minute',
    'minutes2'
]);
if(!$v->validate()){
    Error($v->errorStr());
}

$btn = Util::getReq('btn');
$minute = Util::getReq('minute', 'int');
$minute2 = Util::getReq('minute2', 'int');

extractMissingPostToGlobals();

$db = DB::db();
$connect=$db->get();

switch($btn) {
case "분당김":
    $query = "update game set turntime=DATE_SUB(turntime, INTERVAL $minute MINUTE),starttime=DATE_SUB(starttime, INTERVAL $minute MINUTE),tnmt_time=DATE_SUB(tnmt_time, INTERVAL $minute MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set turntime=DATE_SUB(turntime, INTERVAL $minute MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update auction set expire=DATE_SUB(expire, INTERVAL $minute MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case "분지연":
    $query = "update game set turntime=DATE_ADD(turntime, INTERVAL $minute MINUTE),starttime=DATE_ADD(starttime, INTERVAL $minute MINUTE),tnmt_time=DATE_ADD(tnmt_time, INTERVAL $minute MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set turntime=DATE_ADD(turntime, INTERVAL $minute MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update auction set expire=DATE_ADD(expire, INTERVAL $minute MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case "토너분당김":
    $query = "update game set tnmt_time=DATE_SUB(tnmt_time, INTERVAL $minute2 MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case "토너분지연":
    $query = "update game set tnmt_time=DATE_ADD(tnmt_time, INTERVAL $minute2 MINUTE)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case "금지급":
    processGoldIncome();
    break;
case "쌀지급":
    processRiceIncome();
    break;
case "락걸기":
    $query = "update plock set plock=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
case "락풀기":
    $query = "update plock set plock=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    break;
}

header('Location:_119.php');