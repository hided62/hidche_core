<?php

namespace sammo;

include "lib.php";
include "func.php";

?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>최근 지도</title>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <script>
        var serverNick = '<?= $runningServer['korName'] ?>';
    </script>
    <?= WebUtil::printJS('js/vendors.js') ?>
    <?= WebUtil::printJS('d_shared/base_map.js') ?>
    <?= WebUtil::printJS('js/recent_map.js') ?>

    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('../e_lib/bootstrap.min.css') ?>
    <?= WebUtil::printCSS('css/common.css') ?>
    <?= WebUtil::printCSS('css/map.css') ?>
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
            <?= getMapHtml($mapTheme) ?>
        </div>
        <div class="card-body">
        </div>
    </div>

</body>

</html>