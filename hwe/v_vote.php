<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireLogin()->loginGame()->setReadOnly();
$userID = Session::getUserID();
$isVoteAdmin = in_array('vote', $session->acl[DB::prefix()] ?? []);
$isVoteAdmin = $isVoteAdmin || $session->userGrade >= 5;
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$develcost = $gameStor->getValue('develcost');
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="color-scheme" content="dark">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=500" />
  <title><?= UniqueConst::$serverName ?>: 설문 조사</title>
  <?= WebUtil::printStaticValues(['staticValues' => [
    'serverNick' => DB::prefix(),
    'serverID' => UniqueConst::$serverID,
    'isGameLoggedIn' => $session->isGameLoggedIn(),
    'isVoteAdmin' => $isVoteAdmin,
    'voteReward' => $develcost * 5,
  ]]) ?>
  <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
  <?= WebUtil::printDist('vue', ['v_vote'], true) ?>
</head>

<body>
  <div id='app'>
  </div>
</body>

</html>