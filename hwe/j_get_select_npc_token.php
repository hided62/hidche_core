<?php
namespace sammo;

include "lib.php";
include "func.php";

const VALID_SECOND = 90;
const PICK_MORE_SECOND = 10;
const KEEP_CNT = 3;


$refresh = Util::getPost('refresh', 'bool', false);
$keepResult = Util::getPost('keep', 'array_int', []);

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();


$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$clock = GameClock::fromStorage($gameStor);
$now = $clock->nowTick();

$oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);
if($oldGeneral !== null){
    Json::die([
        'result'=>false,
        'reason'=>'이미 장수가 생성되었습니다'
    ]);
}

list(
    $maxgeneral,
    $turnterm,
    $npcmode
) = $gameStor->getValuesAsArray(['maxgeneral', 'turnterm', 'npcmode']);

if($npcmode!=1){
    Json::die([
        'result'=>false,
        'reason'=>'빙의 가능한 서버가 아닙니다'
    ]);
}

$token = $db->queryFirstRow('SELECT * FROM select_npc_token WHERE `owner`=%i AND `valid_until`>=%i', $userID, $now);
$pickResult = [];


if($token && $refresh){
    $pickMoreFrom = Util::toInt($token['pick_more_from']);

    if($now >= $pickMoreFrom){
        $oldPickResult = Json::decode($token['pick_result']);

        foreach($keepResult as $keepId){
            if(\key_exists($keepId, $oldPickResult) && $oldPickResult[$keepId]['keepCnt'] > 0){
                $pickResult[$keepId] = $oldPickResult[$keepId];
                $pickResult[$keepId]['keepCnt']-=1;
            }
        }

        if(count($pickResult) == count($oldPickResult)){
            $refresh = false;
        }
    }
    else{
        Json::die([
            'result'=>false,
            'reason'=>'아직 다시 뽑을 수 없습니다',
        ]);
    }
}

if($token && !$refresh){
    $pickMoreFrom = Util::toInt($token['pick_more_from']);

    Json::die([
        'result'=>true,
        'pick'=>Json::decode($token['pick_result']),
        'pickMoreFrom'=>$clock->formatTick($pickMoreFrom),
        'pickMoreSeconds'=>intdiv($pickMoreFrom - $now, $clock->ticksPerSecond()),
        'validUntil'=>$clock->formatTick(Util::toInt($token['valid_until'])),
        'validForSeconds'=>max(0, intdiv(Util::toInt($token['valid_until']) - $now, $clock->ticksPerSecond())),
        'clockMode'=>$clock->getMode(),
    ]);
}

$candidates = [];

$weight = [];
foreach($db->query('SELECT `no`, `name`, leadership, strength, intel, nation, imgsvr, picture, personal, special, special2 FROM general WHERE npc=2') as $general){
    $general['special'] = buildGeneralSpecialDomesticClass($general['special'])->getName();
    $general['special2'] = buildGeneralSpecialWarClass($general['special2'])->getName();
    $general['personal'] = buildPersonalityClass($general['personal'])->getName();
    $general['nation'] = getNationStaticInfo($general['nation'])['name'];
    $candidates[$general['no']] = $general + ['keepCnt'=>KEEP_CNT];
    $allStat = $general['leadership'] + $general['strength'] + $general['intel'];
    $weight[$general['no']] = pow($allStat, 1.5);
}

foreach($db->queryFirstColumn('SELECT pick_result FROM select_npc_token WHERE `owner`!=%i AND valid_until >=%i', $userID, $now) as $reserved){
    $reserved = Json::decode($reserved);
    foreach(array_keys($reserved) as $reservedNPC){
        if(key_exists($reservedNPC, $weight)){
            unset($candidates[$reservedNPC]);
            unset($weight[$reservedNPC]);
        }
    }
}

$rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
    UniqueConst::$hiddenSeed,
    'SelectNPCToken',
    $userID,
    $now,
)));

$pickLimit = min(count($candidates), 5);

while(count($pickResult) < $pickLimit){
    $generalID = $rng->choiceUsingWeight($weight);
    if(!key_exists($generalID, $pickResult)){
        $pickResult[$generalID] = $candidates[$generalID];
    }
}

$newNonce = random_int(0, 0xfffffff);

$validSecond = max(VALID_SECOND, $turnterm*40);
$pickMoreSecond = max(PICK_MORE_SECOND, Util::round(pow($turnterm, 0.672)*8));

$validUntil = $now + $clock->ticksFromSeconds($validSecond);
$pickMoreFrom = $now + $clock->ticksFromSeconds($pickMoreSecond);

$db->delete('select_npc_token', 'valid_until < %s', $now);

$inserted = 0;

if($token){
    $db->update('select_npc_token', [
        'valid_until'=>$validUntil,
        'pick_more_from'=>$pickMoreFrom,
        'pick_result'=>Json::encode($pickResult),
        'nonce'=>$newNonce
    ], 'owner = %i AND nonce = %i', $userID, $token['nonce']);
    if($db->affectedRows()){
        $inserted = -1;
    }
}
else{
    $db->insertIgnore('select_npc_token', [
        'owner'=>$userID,
        'valid_until'=>$validUntil,
        'pick_more_from'=>$now,
        'pick_result'=>Json::encode($pickResult),
        'nonce'=>$newNonce
    ]);

    if($db->affectedRows()){
        $inserted = 1;
    }
}

if($inserted === 0){
    Json::die([
        'result'=>false,
        'reason'=>'중복 요청, 다시 랜덤 토큰을 확인해주세요'
    ]);
}

Json::die([
    'result'=>true,
    'pick'=>$pickResult,
    'pickMoreFrom'=>$clock->formatTick(($inserted===-1)?$pickMoreFrom:$now),
    'pickMoreSeconds'=>($inserted===-1)?$pickMoreSecond:0,
    'validUntil'=>$clock->formatTick($validUntil),
    'validForSeconds'=>max(0, intdiv($validUntil - $now, $clock->ticksPerSecond())),
    'clockMode'=>$clock->getMode(),
]);
