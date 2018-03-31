<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

if(Session::getUserGrade() < 5) {
    //echo "<script>location.replace('_admin5.php');</script>";
    echo '_admin5.php';//TODO:debug all and replace
}

switch($btn) {
    case "국가변경":
        if($nation == 0) {
            $query = "update general set nation=0,level=0 where owner='{$_SESSION['userID']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set nation='{$nation}',level=1 where owner='{$_SESSION['userID']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
}

//echo "<script>location.replace('_admin5.php');</script>";
echo '_admin5.php';//TODO:debug all and replace


