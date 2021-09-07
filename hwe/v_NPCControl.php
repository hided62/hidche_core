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

increaseRefresh("NPC 정책", 1);


$me = $db->queryFirstRow('SELECT no, npc, nation, city, officer_level, con, turntime, belong, permission, penalty FROM general WHERE owner=%i', $userID);

$nationID = $me['nation'];
$nation = $db->queryFirstRow('SELECT nation,level,name,color,type,gold,rice,bill,tech,rate,scout,war,secretlimit,capital FROM nation WHERE nation = %i', $nationID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if ($permission < 0) {
    echo '국가에 소속되어있지 않습니다.';
    die();
} else if ($permission < 1) {
    echo "권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.";
    die();
}


$nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
$nationStor->cacheValues(['npc_nation_policy', 'npc_general_policy']);
$gameStor->cacheAll();

$general = new General($me, null, null, $nation, $gameStor->year, $gameStor->month, false);

$rawServerPolicy = $gameStor->getValue('npc_nation_policy') ?? [];
$rawNationPolicy = $nationStor->getValue('npc_nation_policy') ?? [];
$rawServerGeneralPolicy = $gameStor->getValue('npc_general_policy') ?? [];
$rawNationGeneralPolicy = $nationStor->getValue('npc_general_policy') ?? [];

$defaultNationPolicy = ($rawServerPolicy['values'] ?? []) + AutorunNationPolicy::$defaultPolicy;
$currentNationPolicy = ($rawNationPolicy['values'] ?? []) + $defaultNationPolicy;

$defaultNationPriority = $rawServerPolicy['priority'] ?? (AutorunNationPolicy::$defaultPriority);
$currentNationPriority = $rawNationPolicy['priority'] ?? $defaultNationPriority;

$defaultGeneralActionPriority = $rawServerGeneralPolicy['priority'] ?? (AutorunGeneralPolicy::$default_priority);
$currentGeneralActionPriority = $rawNationGeneralPolicy['priority'] ?? $defaultGeneralActionPriority;

$autoPolicyVariable = [];
if ($currentNationPolicy['reqHumanWarUrgentRice'] ?? 0) {
    $autoPolicyVariable['reqHumanWarUrgentRice'] = $currentNationPolicy['reqHumanWarUrgentRice'];
}
if ($currentNationPolicy['reqHumanWarUrgentGold'] ?? 0) {
    $autoPolicyVariable['reqHumanWarUrgentGold'] = $currentNationPolicy['reqHumanWarUrgentGold'];
}
$autoPolicy = new AutorunNationPolicy($general, ($gameStor->autorun_user)['options'], ['values' => $autoPolicyVariable], null, $nation, $gameStor->getAll(true));
$zeroPolicy = new AutorunNationPolicy($general, ($gameStor->autorun_user)['options'], null, null, $nation, $gameStor->getAll(true));

$lastSetters = [
    'policy' => [
        'setter' => $rawNationPolicy['valueSetter'] ?? null,
        'date' => $rawNationPolicy['valueSetTime'] ?? null,
    ],
    'nation' => [
        'setter' => $rawNationPolicy['prioritySetter'] ?? null,
        'date' => $rawNationPolicy['prioritySetTime'] ?? null,
    ],
    'general' => [
        'setter' => $rawNationGeneralPolicy['prioritySetter'] ?? null,
        'date' => $rawNationGeneralPolicy['prioritySetTime'] ?? null,
    ]
]


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 임시 NPC 정책</title>
    <?= WebUtil::printStaticValues([
        'nationID' => $nationID,
        'defaultNationPolicy' => $defaultNationPolicy,
        'currentNationPolicy' => $currentNationPolicy,
        'zeroPolicy' => $zeroPolicy,

        'defaultNationPriority' => $defaultNationPriority,
        'currentNationPriority' => $currentNationPriority,
        'availableNationPriorityItems' => AutorunNationPolicy::$defaultPriority,

        'defaultGeneralActionPriority' => $defaultGeneralActionPriority,
        'currentGeneralActionPriority' => $currentGeneralActionPriority,
        'availableGeneralActionPriorityItems' => AutorunGeneralPolicy::$default_priority,

        'lastSetters' => $lastSetters,

        'defaultStatNPCMax' => GameConst::$defaultStatNPCMax,
        'defaultStatMax' => GameConst::$defaultStatMax,
    ]) ?>
    <?= WebUtil::printCSS('dist_css/vendors_vue.css') ?>
    <?= WebUtil::printCSS('dist_css/common_vue.css') ?>
    <?= WebUtil::printCSS('dist_css/v_NPCControl.css') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printJS('dist_js/vendors_vue.js', true) ?>
    <?= WebUtil::printJS('dist_js/common_vue.js', true) ?>
    <?= WebUtil::printJS('dist_js/v_NPCControl.js', true) ?>
</head>

<body>
    <div id='app'>
    </div>
</body>

</html>