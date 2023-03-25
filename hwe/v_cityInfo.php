<?php

namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin()->setReadOnly();

$cityID = Util::getReq('cityID', 'int');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 도시 정보</title>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
            'serverName' => UniqueConst::$serverName,
            'serverNick' => DB::prefix(),
            'serverID' => UniqueConst::$serverID,
            'mapName' => GameConst::$mapName,
            'unitSet' => GameConst::$unitSet,
        ],
        'query' => [
            'cityID' => $cityID,
        ]
    ], false) ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('vue', 'v_cityInfo', true) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>