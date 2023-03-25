<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gen = Util::getReq('gen', 'int');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 감찰부</title>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
            'serverNick' => DB::prefix(),
            'mapName' => GameConst::$mapName,
            'unitSet' => GameConst::$unitSet,
        ],
        'query' => [
            'generalID' => $gen,
        ]
    ], false) ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('vue', 'v_battleCenter', true) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>