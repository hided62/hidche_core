<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$mapName = GameConst::$mapName;
?>
<!DOCTYPE html>
<html>

<head>
  <title><?= UniqueConst::$serverName ?>:중원정보</title>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="dark">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=500" />
  <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
  <?= WebUtil::printJS("js/map/theme_{$mapName}.js") ?>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
  <?= WebUtil::printStaticValues([
    'staticValues' => [
      'serverNick' => DB::prefix(),
      'serverID' => UniqueConst::$serverID,
    ],
  ]) ?>
  <?= WebUtil::printDist('vue', 'v_globalDiplomacy', true) ?>
</head>

<body>
  <div id="app"></div>
</body>

</html>