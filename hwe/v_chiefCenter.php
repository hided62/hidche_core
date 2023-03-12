<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalObj = General::createGeneralObjFromDB($session->generalID);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 사령부</title>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printStaticValues([
        'maxChiefTurn'=>GameConst::$maxChiefTurn,
        'generalID'=>$generalObj->getID(),
        'staticValues'=>[
            'serverNick' => DB::prefix(),
            'mapName' => GameConst::$mapName,
            'unitSet' => GameConst::$unitSet,
        ]
    ])?>
    <?= WebUtil::printDist('vue', 'v_chiefCenter', true) ?>
</head>

<body>
    <div id='app'></div>
</body>

</html>