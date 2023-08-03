<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if ($session->userGrade < 5) {
    die('권한 부족');
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if (!$gameStor->isunited) {
    die('아직 천통하지 않았습니다');
}

foreach ($db->queryFirstColumn(
    'SELECT no FROM general WHERE npc < 2 and age >= %i',
    40
) as $generalNo) {
    CheckHall($generalNo);
}

$inheritPointManager = InheritancePointManager::getInstance();
foreach(General::createObjListFromDB($db->queryFirstColumn('SELECT `no` FROM general WHERE npc = 0')) as $genObj){
    $inheritPointManager->mergeTotalInheritancePoint($genObj);
    $inheritPointManager->applyInheritanceUser($genObj->getVar('owner'));
}