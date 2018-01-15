<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where user_id='$_SESSION['p_id']'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    echo "<script>location.replace('_admin5.php');</script>";
}

switch($btn) {
    case "국가변경":
        if($nation == 0) {
            $query = "update general set nation=0,level=0 where user_id='$_SESSION['p_id']'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set nation='{$nation}',level=1 where user_id='$_SESSION['p_id']'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
}

//echo "<script>location.replace('_admin5.php');</script>";
echo '_admin5.php';//TODO:replace


