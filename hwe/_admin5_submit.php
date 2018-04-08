<?php
namespace sammo;

include "lib.php";
include "func.php";

$nation = Util::getReq('nation', 'int');

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
            $db->update('general', [
                'nation'=>0,
                'level'=>0,
            ], 'owner=%i', $userID);
        } else {
            $db->update('general', [
                'nation'=>$nation,
                'level'=>1,
            ], 'owner=%i', $userID);
        }
        break;
}

//echo "<script>location.replace('_admin5.php');</script>";
echo '_admin5.php';//TODO:debug all and replace


