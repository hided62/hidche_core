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



$currentInheritBuff = [];
foreach ($me->getAuxVar('inheritBuff') ?? [] as $buff => $buffLevel) {
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

$avilableSpecialWar = [];
foreach (GameConst::$availableSpecialWar as $specialWarKey) {
    $specialWarObj = buildGeneralSpecialWarClass($specialWarKey);
    $avilableSpecialWar[$specialWarKey] = [
        'title' => $specialWarObj->getName(),
        'info' => $specialWarObj->getInfo(),
    ];
}

$availableUnique = [];
foreach (GameConst::$allItems as $subItems) {
    foreach ($subItems as $itemKey => $amount) {
        if ($amount == 0) {
            continue;
        }
        $itemObj = buildItemClass($itemKey);
        $availableUnique[$itemKey] = [
            'title' => $itemObj->getName(),
            'info' => $itemObj->getInfo(),
        ];
    }
}


$items = [];
foreach (array_keys(General::INHERITANCE_KEY) as $key) {
    $items[$key] = $me->getInheritancePoint($key) ?? 0;
}

$resetTurnTimeLevel = ($me->getAuxVar('inheritResetTurnTime') ?? -1) + 1;
$resetSpecialWarLevel = ($me->getAuxVar('inheritResetSpecialWar') ?? -1) + 1;

$lastInheritPointLogs = $db->query('SELECT server_id, year, month, date, text FROM user_record WHERE log_type = %s AND user_id = %i ORDER BY id desc LIMIT 30', "inheritPoint", $userID);
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 유산 관리</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printDist('vue', 'v_inheritPoint', true) ?>
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
            'nextSpecial' => GameConst::$inheritSpecificSpecialPoint,
            'minSpecificUnique' => GameConst::$inheritItemUniqueMinPoint,
        ],
        'availableSpecialWar' => $avilableSpecialWar,
        'availableUnique' => $availableUnique,
        'lastInheritPointLogs' => $lastInheritPointLogs,
    ]) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>