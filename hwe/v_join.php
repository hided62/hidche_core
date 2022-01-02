<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();

if (!$userID) {
    die(WebUtil::errorBackMsg("잘못된 접근입니다!!!"));
}


//회원 테이블에서 정보확인
$member = RootDB::db()->queryFirstRow("SELECT no,name,picture,imgsvr,grade from member where no= %i", $userID);
if (!$member) {
    die(WebUtil::errorBackMsg("잘못된 접근입니다!!!"));
}

$db = DB::db();

$gameStor = KVStorage::getStorage($db, 'game_env');
$admin = $gameStor->getValues(['block_general_create', 'show_img_level', 'maxgeneral', 'turnterm']);
if ($admin['block_general_create']) {
    die(WebUtil::errorBackMsg("잘못된 접근입니다!!!"));
}

$alreadyJoined = $db->queryFirstField('SELECT name FROM general WHERE owner = %i', $userID);
if ($alreadyJoined) {
    die(WebUtil::errorBackMsg("이미 장수를 생성했습니다: {$alreadyJoined}", './'));
}

$gencount = $db->queryFirstField('SELECT count(no) FROM general WHERE npc<2');
if ($gencount >= $admin['maxgeneral']) {
    die(WebUtil::errorBackMsg("더 이상 등록할 수 없습니다."));
}

$inheritTotalPoint = applyInheritanceUser($userID);

$nationList = $db->query('SELECT nation,`name`,color,scout FROM nation');
$nationList = Util::convertArrayToDict($nationList, 'nation');
//NOTE: join 안할것임
$scoutMsgs = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'scout_msg');
foreach ($scoutMsgs as $destNationID => $scoutMsg) {
    $nationList[$destNationID]['scoutmsg'] = $scoutMsg;
}


$availablePersonality = [];
foreach (GameConst::$availablePersonality as $personalityID) {
    $personalityObj = buildPersonalityClass($personalityID);
    $availablePersonality[$personalityID] = [
        'name' => $personalityObj->getName(),
        'info' => $personalityObj->getInfo(),
    ];
}

$availableInheritSpecial = [];
foreach (GameConst::$availableSpecialWar as $specialID){
    $specialObj = buildGeneralSpecialWarClass($specialID);
    $availableInheritSpecial[$specialID] = [
        'name' => $specialObj->getName(),
        'info' => $specialObj->getInfo(),
    ];
}

$availableInheritCity = [];
foreach(CityConst::all() as $city){
    $availableInheritCity[] = [$city->id, CityConst::$regionMap[$city->region], $city->name];
}

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 장수 생성</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?= WebUtil::printJS('../d_shared/common_path.js', true) ?>
    <?= WebUtil::printDist('vue', 'v_join', true) ?>

    <?= WebUtil::printStaticValues([
        'serverID' => UniqueConst::$serverID,
        'nationList' => array_values($nationList),
        'config' => [
            'show_img_level' => $admin['show_img_level']
        ],
        'member' => [
            'name' => $member['name'],
            'grade' => $member['grade'],
            'picture' => $member['picture'],
            'imgsvr' => $member['imgsvr'],
        ],
        'availablePersonality' => array_merge([
            'Random' => ['name' => '???', 'info' => '무작위 성격을 선택합니다.']
        ], $availablePersonality),
        'stats' => [
            'min' => GameConst::$defaultStatMin,
            'max' => GameConst::$defaultStatMax,
            'total' => GameConst::$defaultStatTotal,
            'bonusMin' => GameConst::$bornMinStatBonus,
            'bonusMax' => GameConst::$bornMaxStatBonus,
        ],
        'inheritTotalPoint'=>$inheritTotalPoint,
        'inheritPoints'=>[
            'special'=>GameConst::$inheritBornSpecialPoint,
            'turnTime'=>GameConst::$inheritBornTurntimePoint,
            'city'=>GameConst::$inheritBornCityPoint,
            'stat'=>GameConst::$inheritBornStatPoint
        ],
        'availableInheritSpecial' => $availableInheritSpecial,
        'availableInheritCity'=> $availableInheritCity,
        'turnterm'=>$gameStor->turnterm,
    ]) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>