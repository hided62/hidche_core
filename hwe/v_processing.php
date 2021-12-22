<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사

$commandType = Util::getReq('command', 'string');
$turnList = array_map('intval', explode('_', Util::getReq('turnList', 'string', '0')));
$isChiefTurn = Util::getReq('is_chief', 'bool', false);

function die_redirect()
{
    global $isChiefTurn;
    if (!$isChiefTurn) {
        header('location:index.php', true, 303);
    } else {
        header('location:b_chiefcenter.php', true, 303);
    }
    die();
}

if (!$turnList || !$commandType) {
    die_redirect();
}
if (!is_array($turnList)) {
    die_redirect();
}

$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();

if (!$isChiefTurn && !in_array($commandType, Util::array_flatten(GameConst::$availableGeneralCommand))) {
    die_redirect();
}

if ($isChiefTurn && !in_array($commandType, Util::array_flatten(GameConst::$availableChiefCommand))) {
    die_redirect();
}

$gameStor = KVStorage::getStorage($db, 'game_env')->turnOnCache();
$env = $gameStor->getAll();
$general = General::createGeneralObjFromDB($session->generalID);

if (!$isChiefTurn) {
    $commandObj = buildGeneralCommandClass($commandType, $general, $env);
} else {
    if ($general->getVar('officer_level') < 5) {
        die_redirect();
    }
    $commandObj = buildNationCommandClass($commandType, $general, $env, new LastTurn());
}


if ($commandObj->isArgValid()) {
    //인자가 필요없는 타입의 경우 processing에서 '전혀' 처리하지 않음!
    die_redirect();
}

if (!$commandObj->hasPermissionToReserve()) {
    die_redirect();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title><?= $commandObj->getName() ?></title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('d_shared/base_map.js') ?>
    <?= WebUtil::printStaticValues([
        'serverNick' => DB::prefix(),
        'serverID' => UniqueConst::$serverID,
        'commandName' => $commandObj->getName(),
        'turnList' => $turnList,
        'currentCity' => $general->getCityID(),
        'currentNation' => $general->getNationID(),
        'entryInfo' => [$isChiefTurn?'Nation':'General', $commandType]
    ])?>
    <?= WebUtil::printStaticValues($commandObj->exportJSVars(), false) ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('vue', ['v_processing'], true) ?>
</head>

<body class="img_back">
    <div id="container"></div>
</body>

</html>