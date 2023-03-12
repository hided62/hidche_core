<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$isResAuction = Util::getReq('type') !== 'unique';

$me = $db->queryFirstRow('SELECT no, nation, officer_level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 경매장</title>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
          'serverID' => UniqueConst::$serverID,
          'serverNick' => DB::prefix(),
          'turnterm' => $gameStor->turnterm,
          'isResAuction' => $isResAuction,
      ]
    ]) ?>
    <?= WebUtil::printDist('vue', 'v_auction', true) ?>
</head>
<body>
    <div id="app"></div>
</body>

</html>