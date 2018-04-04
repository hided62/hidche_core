<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $msg, $scoutmsg, $rate, $bill, $secretlimit

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$connect = dbConn();

$query = "select no,nation,level from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$btn = $_POST['btn'];

//내가 수뇌부이어야함
if($me['level'] < 5) {
    //echo "<script>location.replace('b_myBossInfo.php');</script>";
    echo 'b_myBossInfo.php';//TODO:debug all and replace
    exit();
}

if($btn == "국가방침") {
    $msg = BadTag2Code(addslashes(SQ2DQ($msg)));
    $query = "update nation set msg='$msg' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "임관권유") {
    $scoutmsg = BadTag2Code(addslashes(SQ2DQ($scoutmsg)));
    $query = "update nation set scoutmsg='$scoutmsg' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "세율") {
    if($rate < 5)  { $rate = 5; }
    if($rate > 30) { $rate = 30; }
    $query = "update nation set rate='$rate' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "지급율") {
    if($bill < 20)  { $bill = 20; }
    if($bill > 200) { $bill = 200; }
    $query = "update nation set bill='$bill' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "기밀권한") {
    if($secretlimit < 1)   { $secretlimit = 1; }
    if($secretlimit > 99) { $secretlimit = 99; }
    $query = "update nation set secretlimit='$secretlimit' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "임관 금지") {
    $query = "update nation set scout='1' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "임관 허가") {
    $query = "update nation set scout='0' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "전쟁 금지") {
    $query = "update nation set war='1' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "전쟁 허가") {
    $query = "update nation set war='0' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

//echo "<script>location.replace('b_dipcenter.php');</script>";
echo 'b_dipcenter.php';//TODO:debug all and replace


