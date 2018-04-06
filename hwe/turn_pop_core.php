<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

$query = "select no from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

updateCommand($me['no'], 2);

//echo "<script>location.replace('b_chiefcenter.php');</script>";
echo 'b_chiefcenter.php';//TODO:debug all and replace
