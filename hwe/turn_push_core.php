<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$me = $db->queryFirstRow('SELECT no,nation,level FROM general WHERE owner=%i', $userID);

if($me['level'] >= 5 && $me['nation'] > 0){
    pushNationCommand($me['nation'], $me['level']);
}

header('location:b_chiefcenter.php');
