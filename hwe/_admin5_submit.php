<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

if($session->userGrade < 5) {
    //echo "<script>location.replace('_admin5.php');</script>";
    echo '_admin5.php';//TODO:debug all and replace
}

$db = DB::db();
$connect=$db->get();

switch($btn) {
    case "국가변경":
        if($nation == 0) {
            $query = "update general set nation=0,level=0 where owner='{$userID}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set nation='{$nation}',level=1 where owner='{$userID}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
}

//echo "<script>location.replace('_admin5.php');</script>";
echo '_admin5.php';//TODO:debug all and replace


