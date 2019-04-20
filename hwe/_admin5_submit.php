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

switch($btn) {
    case "국가변경":
        $oldNation = $db->queryFirstField('SELECT nation FROM general WHERE owner=%i', $userID);
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
            $db->update('nation', [
                'gennum'=>$db->sqleval('gennum + 1')
            ], 'nation=%i', $oldNation);
        }
        if($oldNation != 0){
            $db->update('nation', [
                'gennum'=>$db->sqleval('gennum - 1')
            ], 'nation=%i', $oldNation);
        }
        break;
}

header('location:_admin5.php');


