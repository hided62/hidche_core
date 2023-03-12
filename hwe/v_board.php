<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$isSecretBoard = Util::getReq('isSecret', 'bool', false);

//increaseRefresh("회의실", 1);

$me = $db->queryFirstRow('SELECT no, nation, officer_level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);


$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if ($permission < 0) {
    echo '국가에 소속되어있지 않습니다.';
    die();
} else if ($isSecretBoard && $permission < 2) {
    echo "권한이 부족합니다. 수뇌부가 아닙니다.";
    die();
}

$boardName = $isSecretBoard ? '기밀실' : '회의실';

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: <?= $boardName ?></title>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printStaticValues([
        'isSecretBoard' => $isSecretBoard,
    ]) ?>
    <?= WebUtil::printDist('vue', 'v_board', true) ?>
</head>
<body>
    <div id="app"></div>
</body>

</html>