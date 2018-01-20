<?php
include "lib.php";
include "func.php";

$connect=dbConn();

$query = "select no,user_id,password,name from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$_SESSION['p_id']     = "";
$_SESSION['p_name']   = "";
$_SESSION['p_nation'] = 0;

$id = $me['user_id'];
$pw = $me['password'];
$conmsg = $me['conmsg'];

//���Ǻ��� ����
session_destroy();

//echo "<script>location.replace('start.php');</script>";
//echo 'start.php';//TODO:debug all and replace
header('Location:start.php');

/*
<html>
a
<form name=form1 action=../login.php method=post>
    <input type=hidden name=id value='<?=$id;?>'>
    <input type=hidden name=pw value='<?=$pw;?>'>
    <input type=hidden name=conmsg value='<?=$conmsg;?>'>
</form>
<script>form1.submit();</script>
</html>
*/

