<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();
$connect=$db->get();

$query = "select killturn from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$admin['killturn'] *= 3;

$query = "update general set killturn='{$admin['killturn']}' where owner='{$session->userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//echo "<script>location.replace('b_myPage.php');</script>";
echo 'b_myPage.php'; //TODO:debug all and replace

