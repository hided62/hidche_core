<?php

namespace sammo;

include "lib.php";
include "func.php";

$mapName = GameConst::$mapName;

?>
<!DOCTYPE html>
<html>

<head>
<title>최근 지도</title>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="dark">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=500" />
  <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
  <?= WebUtil::printJS("js/map/theme_{$mapName}.js") ?>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
  <?= WebUtil::printStaticValues([
    'staticValues' => [
      'serverName' => UniqueConst::$serverName,
      'serverNick' => DB::prefix(),
      'serverID' => UniqueConst::$serverID,
    ],
  ]) ?>
  <?= WebUtil::printDist('vue', 'v_cachedMap', true) ?>
</head>

<body>
  <div id="app"></div>
</body>

</html>