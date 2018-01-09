<?
include "lib.php";
include "func.php";

$connect=dbConn();

$query = "select userlevel from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me[userlevel] < 5) {
    echo "<script>location.replace('_119.php');</script>";
}

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
    processGoldIncome($connect);
    break;
case "쌀지급":
    processRiceIncome($connect);
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

echo "<script>location.replace('_119.php');</script>";
?>
