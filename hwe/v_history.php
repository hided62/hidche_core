<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$serverID = Util::getReq('serverID', 'string', null);
if (!$serverID) {
  $serverID = UniqueConst::$serverID;
}
if ($serverID !== UniqueConst::$serverID) {
  $mapName = $db->queryFirstField('SELECT map FROM ng_games WHERE server_id=%s', $serverID) ?: 'che';
} else {
  $mapName = GameConst::$mapName;
}

[$f_year, $f_month] = $db->queryFirstList('SELECT year, month FROM ng_history WHERE server_id = %s ORDER BY year ASC, month ASC LIMIT 1', $serverID);
[$l_year, $l_month] = $db->queryFirstList('SELECT year, month FROM ng_history WHERE server_id = %s ORDER BY year DESC, month DESC LIMIT 1', $serverID);

if($serverID === UniqueConst::$serverID){
  [$currentYear, $currentMonth] = $gameStor->getValuesAsArray(['year', 'month']);
}
else{
  [$currentYear, $currentMonth] = [$l_year, $l_month];
}

?>
<!DOCTYPE html>
<html>

<head>
  <title><?= UniqueConst::$serverName ?>:연감</title>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="dark">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=500" />
  <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
  <?= WebUtil::printJS("js/map/theme_{$mapName}.js") ?>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
  <?= WebUtil::printStaticValues([
    'staticValues' => [
      'firstYearMonth' => Util::joinYearMonth($f_year, $f_month),
      'lastYearMonth' => Util::joinYearMonth($l_year, $l_month),
      'currentYearMonth' => Util::joinYearMonth($currentYear, $currentMonth),
      'serverNick' => DB::prefix(),
      'serverID' => UniqueConst::$serverID,
      'mapName' => $mapName,
    ],
    'query' => [
      'serverID' => $serverID,
      'yearMonth' => Util::getReq('yearMonth', 'int'),
    ],
  ]) ?>
  <?= WebUtil::printDist('vue', 'v_history', true) ?>
</head>

<body>
  <div id="app"></div>
</body>

</html>