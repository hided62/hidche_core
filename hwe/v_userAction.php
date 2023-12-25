<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gameStor->cacheValues(['autorun_user', 'develcost']);

//TODO: 개인 전략 옵션이 활성화된 경우여야만 함!

increaseRefresh("개인 전략", 1);

$me = $db->queryFirstRow(
    'SELECT no, npc, nation, city, officer_level, refresh_score, turntime, belong, permission, penalty FROM `general`
    LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner=%i', $userID
);

$nationID = $me['nation'];
$nation = $db->queryFirstRow('SELECT nation,level,name,color,type,gold,rice,bill,tech,rate,scout,war,secretlimit,capital FROM nation WHERE nation = %i', $nationID);

$limitState = checkLimit($me['refresh_score']);
if ($limitState >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 개인 전략</title>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
          'serverName' => UniqueConst::$serverName,
          'serverNick' => DB::prefix(),
          'serverID' => UniqueConst::$serverID,
          'mapName' => GameConst::$mapName,
          'unitSet' => GameConst::$unitSet,

          'maxTurn' => GameConst::$maxTurn,
          'serverNow' => TimeUtil::now(false),
        ]
    ], false) ?>
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printDist('vue', ['v_userAction'], true) ?>
</head>

<body>
    <div id='app'>
    </div>
</body>

</html>