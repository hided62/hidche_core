<?php

namespace sammo;

include "lib.php";
include "func.php";

?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>최근 지도</title>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
            'serverNick' => DB::prefix(),
            'serverID' => UniqueConst::$serverID
        ],
    ]) ?>
    <?= WebUtil::printJS('d_shared/base_map.js') ?>

    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printCSS('css/map.css') ?>
    <?= WebUtil::printDist('ts', ['common', 'recent_map'], true) ?>
    <?= WebUtil::printDist('vue', [], true) ?>
    <style>
        html {
            width: 700px;
        }

        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            width: 700px;
        }
    </style>
</head>

<body>
    <div class="card" style="width:700px;">
        <h3 class="card-header">
            <?= UniqueConst::$serverName ?> 현황
        </h3>
        <div class='map-container' style='position:relative;'>
            <?= getMapHtml() ?>
        </div>
        <div class="card-body">
        </div>
    </div>
</body>

</html>