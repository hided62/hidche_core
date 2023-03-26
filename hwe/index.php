<?php

namespace sammo;

include "lib.php";
include "func.php";
Session::requireLogin()->loginGame()->setReadOnly();

$mapName = GameConst::$mapName;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 메인</title>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
          'serverName' => UniqueConst::$serverName,
          'serverNick' => DB::prefix(),
          'serverID' => UniqueConst::$serverID,
          'mapName' => GameConst::$mapName,
          'unitSet' => GameConst::$unitSet,

          'maxTurn' => GameConst::$maxTurn,
          'maxPushTurn' => 12,
          'serverNow' => TimeUtil::now(false),
        ]
    ], false) ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS("js/map/theme_{$mapName}.js") ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('vue', 'v_front', true) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>