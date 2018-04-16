<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$nation = Util::getReq('nation', 'int');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

if($session->userGrade < 5) {
    header('location:_admin5.php');
    die();
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

header('location:_admin5.php');


