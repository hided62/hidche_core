<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select no from general where no_member='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

updateCommand($connect, $me['no'], 2);

//echo "<script>location.replace('b_chiefcenter.php');</script>";
echo 'b_chiefcenter.php';//TODO:debug all and replace
