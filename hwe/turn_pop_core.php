<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLoginWithGeneralID();
$connect = dbConn();

$query = "select no from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

updateCommand($connect, $me['no'], 2);

//echo "<script>location.replace('b_chiefcenter.php');</script>";
echo 'b_chiefcenter.php';//TODO:debug all and replace
