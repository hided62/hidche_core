<?php
namespace sammo;

include "lib.php";
include "func.php";
// $msg

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

$query = "select no,nation from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$msg = addslashes(SQ2DQ($msg));

$query = "update nation set rule='$msg' where nation='{$me['nation']}'";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//echo "<script>location.replace('b_nationrule.php');</script>";
echo 'b_nationrule.php';//TODO:debug all and replace

