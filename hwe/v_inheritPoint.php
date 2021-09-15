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

$currentInheritBuff = [];
foreach ($me->getAuxVar('inheritBuff') as $buff => $buffLevel) {
    if (!key_exists($buff, TriggerInheritBuff::BUFF_KEY_TEXT)) {
        continue;
    }
    $currentInheritBuff[$buff] = $buffLevel;
}

function calcResetAttrPoint($level)
{
    while (count(GameConst::$inheritResetAttrPointBase) <= $level) {
        $baseLen = count(GameConst::$inheritResetAttrPointBase);
        GameConst::$inheritResetAttrPointBase[] = GameConst::$inheritResetAttrPointBase[$baseLen - 1] + GameConst::$inheritResetAttrPointBase[$baseLen - 2];
    }
    return GameConst::$inheritResetAttrPointBase[$level];
}


$resetTurnTimeLevel = $me->getAuxVar('inheritResetTurnTime')??0;
$resetSpecialWarLevel = $me->getAuxVar('inheritResetSpecialWar')??0;
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 유산 관리</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printCSS('dist_css/common_vue.css') ?>
    <?= WebUtil::printCSS('dist_css/v_inheritPoint.css') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printJS('dist_js/vendors_vue.js', true) ?>
    <?= WebUtil::printJS('dist_js/common_vue.js', true) ?>
    <?= WebUtil::printJS('dist_js/v_inheritPoint.js', true) ?>
    <?= WebUtil::printStaticValues([
        'items' => $items,
        'currentInheritBuff' => $currentInheritBuff,
        'maxInheritBuff' => TriggerInheritBuff::MAX_STEP,
        'resetTurnTimeLevel' => $resetTurnTimeLevel,
        'resetSpecialWarLevel' => $resetSpecialWarLevel,
        'inheritActionCost' => [
            'buff' => GameConst::$inheritBuffPoints,
            'resetTurnTime' => calcResetAttrPoint($resetTurnTimeLevel),
            'resetSpecialWar' => calcResetAttrPoint($resetSpecialWarLevel),
            'randomUnique' => GameConst::$inheritItemRandomPoint,
        ],
    ]) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>