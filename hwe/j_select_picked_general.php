<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$pick = Util::getPost('pick');
$leadership = Util::getPost('leadership', 'int', GameConst::$defaultStatMin);
$isCentennialAllStar = CentennialAllStarGrowthService::isActive();
$strength = Util::getPost(
    $isCentennialAllStar ? 'strength' : 'leadership',
    'int',
    GameConst::$defaultStatMin
);
$intel = Util::getPost(
    $isCentennialAllStar ? 'intel' : 'leadership',
    'int',
    GameConst::$defaultStatMin
);
$personal = Util::getPost('personal', 'string', null);
$use_own_picture = Util::getPost('use_own_picture', 'bool', false);
$pictureSource = Util::getPost('picture_source', 'string', 'selected');


if(!$pick){
    Json::die([
        'result'=>false,
        'reason'=>'장수를 선택하지 않았습니다'
    ]);
}

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$rootDB = RootDB::db();
$clock = GameClock::fromStorage($gameStor);
$now = $clock->nowTick();

$hasGeneralID = $db->queryFirstField('SELECT no FROM general WHERE owner = %i', $userID);
if($hasGeneralID){
    Json::die([
        'result'=>false,
        'reason'=>'이미 장수를 생성했습니다.'
    ]);
}

$env = $gameStor->getValues(['startyear', 'year', 'month', 'maxgeneral', 'npcmode', 'show_img_level', 'icon_path', 'turnterm', 'turntime']);
$env['fiction'] = false;

$npcmode = $env['npcmode'];
$maxgeneral = $env['maxgeneral'];

if($npcmode!=2){
    Json::die([
        'result'=>false,
        'reason'=>'선택 가능한 서버가 아닙니다'
    ]);
}

$selectInfo = $db->queryFirstField('SELECT info FROM select_pool WHERE `owner` = %i AND `reserved_until`>=%s AND `unique_name`=%s', $userID, $now, $pick);
if(!$selectInfo){
    Json::die([
        'result'=>false,
        'reason'=>'유효한 장수 목록이 없습니다.'
    ]);
}
$selectInfo = Json::decode($selectInfo);

$ownerInfo = RootDB::db()->queryFirstRow(
    'SELECT `name`,`picture`,`imgsvr`,`grade` FROM member WHERE `NO`=%i',
    $userID
);
if(!$ownerInfo){
    Json::die([
        'result'=>false,
        'reason'=>'멤버 정보를 가져오지 못했습니다.'
    ]);
}
if ($isCentennialAllStar) {
    if (!in_array($pictureSource, ['selected', 'own'], true)) {
        Json::die([
            'result' => false,
            'reason' => '올바르지 않은 전콘 선택입니다.',
        ]);
    }
    if ($pictureSource === 'own') {
        $canUseOwnPicture = in_array('picture', GameConst::$generalPoolAllowOption, true)
            && $env['show_img_level'] >= 1
            && $ownerInfo['grade'] >= 1
            && $ownerInfo['picture'] !== '';
        if (!$canUseOwnPicture) {
            Json::die([
                'result' => false,
                'reason' => '사용할 수 있는 내 전콘이 없습니다.',
            ]);
        }
    }
}


$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');

if ($gencount >= $maxgeneral) {
    Json::die([
        'result'=>false,
        'reason'=>'더 이상 등록 할 수 없습니다.'
    ]);
}

$poolClass = getGeneralPoolClass(GameConst::$targetGeneralPool);
/** @var AbsGeneralPool */
if ($isCentennialAllStar) {
    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
        UniqueConst::$hiddenSeed,
        'selectPickedGeneral',
        $userID,
        $pick
    )));
    $pickedGeneral = new $poolClass($db, $rng, $selectInfo, $now);
} else {
    $pickedGeneral = new $poolClass($db, $selectInfo, $now);
}

$builder = $pickedGeneral->getGeneralBuilder();
if ($isCentennialAllStar) {
    CentennialAllStarGrowthService::prepareInitialUser($builder, $selectInfo);
}

foreach(GameConst::$generalPoolAllowOption as $allowOption){
    if($allowOption == 'stat' && !$isCentennialAllStar){
        $leadership = Util::valueFit($leadership, GameConst::$defaultStatMin, GameConst::$defaultStatMax);
        $strength = Util::valueFit($strength, GameConst::$defaultStatMin, GameConst::$defaultStatMax);
        $intel = Util::valueFit($intel, GameConst::$defaultStatMin, GameConst::$defaultStatMax);

        if($leadership + $strength + $intel > GameConst::$defaultStatTotal){
            Json::die([
                'result'=>false,
                'reason'=>'스탯의 총 합이 올바르지 않습니다.'
            ]);
        }
        $builder->setStat($leadership, $strength, $intel);
    }
    else if(
        $allowOption == 'picture'
        && (
            (!$isCentennialAllStar && $use_own_picture)
            || ($isCentennialAllStar && $pictureSource === 'own')
        )
    ){
        $builder->setPicture($ownerInfo['imgsvr'], $ownerInfo['picture']);
    }
    else if($allowOption == 'ego'){
        if(!$personal || $personal == 'Random'){
            $personal =  Util::choiceRandom(GameConst::$availablePersonality);
        }
        $invalidPersonal = $isCentennialAllStar
            ? !in_array($personal, GameConst::$availablePersonality, true)
            : !array_search($personal, GameConst::$availablePersonality);
        if($invalidPersonal){
            Json::die([
                'result'=>false,
                'reason'=>'올바르지 않은 성격입니다.'
            ]);
        }
        $builder->setEgo($personal);
    }
}

$userNick = $ownerInfo['name'];

$builder->setOwner($userID);
$builder->setOwnerName($userNick);
$builder->setKillturn(5);
$builder->setNPCType(0);
$builder->setAuxVar('next_change', $now + GameClock::TICKS_PER_TURN * 12);
$builder->setAuxVar(
    'prestart_delete_after',
    addTurn($now, $env['turnterm'], GameConst::$minTurnDieOnPrestart)
);
$builder->fillRemainSpecAsZero($env);
if ($isCentennialAllStar) {
    $candidateCities = $db->queryFirstColumn(
        'SELECT city FROM city WHERE level >= 5 AND level <= 6 AND nation = 0'
    );
    if (!$candidateCities) {
        $candidateCities = $db->queryFirstColumn(
            'SELECT city FROM city WHERE level >= 5 AND level <= 6'
        );
    }
    if (!$candidateCities) {
        Json::die([
            'result' => false,
            'reason' => '장수를 생성할 소·중성이 없습니다.',
        ]);
    }
    $builder->setCityID($rng->choice($candidateCities));
}
$builder->build($env);
$generalID = $builder->getGeneralID();
if(!$generalID){
    Json::die([
        'result'=>false,
        'reason'=>'장수 등록에 실패했습니다.'
    ]);
}
$pickedGeneral->occupyGeneralName();

$db->update('select_pool',[
    'owner'=>null,
    'reserved_until'=>null,
], '(owner=%i or reserved_until < %s) AND general_id is NULL', $userID, $now);


$josaYi = JosaUtil::pick($userNick, '이');
$generalName = $builder->getGeneralName();
$josaRo = JosaUtil::pick($generalName, '로');

$cityName = CityConst::byID($builder->getCityID())->name;

$logger = new ActionLogger($generalID, 0, $env['year'], $env['month']);
$logger->pushGeneralHistoryLog("<Y>{$generalName}</>, <G>{$cityName}</>에서 등장");
$logger->pushGlobalActionLog("<G><b>{$cityName}</b></>에서 <Y>{$userNick}</>{$josaYi} <Y>{$generalName}</>{$josaRo} 등장합니다.");
$logger->flush();

pushAdminLog(["가입 : {$userID} // {$session->userName} // {$pick} // ".getenv("REMOTE_ADDR")]);

$rootDB->insert('member_log', [
    'member_no' => $userID,
    'date'=>TimeUtil::now(),
    'action_type'=>'make_general',
    'action'=>Json::encode([
        'server'=>DB::prefix(),
        'type'=>'select',
        'generalID'=>$generalID,
        'generalName'=>$generalName
    ])
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);
