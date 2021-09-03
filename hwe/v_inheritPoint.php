<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$me = General::createGeneralObjFromDB($generalID);


$items = [];
foreach (array_keys(General::INHERITANCE_KEY) as $key) {
    $items[$key] = $me->getInheritancePoint($key) ?? 0;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 유산 관리</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::preloadCSS('dist_css/v_inheritPoint.css') ?>
    <?= WebUtil::preloadJS('dist_js/v_inheritPoint.js') ?>
    <?= WebUtil::printCSS('dist_css/v_inheritPoint.css') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('dist_js/vendors_vue.js') ?>
    <?= WebUtil::printStaticValues([
        'items' => $items
    ]) ?>
</head>

<body>
    <div id="app"></div>

    <?= WebUtil::printJS('dist_js/v_inheritPoint.js') ?>
</body>

</html>