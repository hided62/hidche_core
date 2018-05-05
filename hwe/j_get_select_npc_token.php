<?php
namespace sammo;

include "lib.php";
include "func.php";

const VALID_MINUTE = 5;
const PICK_MORE_MINUTE = 2;
const KEEP_CNT = 3;


$refresh = Util::getReq('refresh', 'bool', false);
$keepResult = Util::getReq('keep', 'array_int', []);

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();


$oNow = new \DateTimeImmutable();

$now = $oNow->format('Y-m-d H:i:s');

$db = DB::db();

$oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);
if($oldGeneral !== null){
    Json::die([
        'result'=>false,
        'reason'=>'이미 장수가 생성되었습니다'
    ]);
}

list(
    $year,
    $month,
    $maxgeneral,
    $turnterm,
    $genius,
    $npcmode
) = $db->queryFirstList('SELECT year,month,maxgeneral,turnterm,genius,npcmode from game limit 1');

if(!$npcmode){
    Json::die([
        'result'=>false,
        'reason'=>'빙의 가능한 서버가 아닙니다'
    ]);
}


$token = $db->queryFirstRow('SELECT * FROM select_npc_token WHERE `owner`=%i AND `valid_until`>=%s', $userID, $now);
$pickResult = [];


if($token && $refresh){
    $pickMoreFrom = (new \DateTime($token['pick_more_from']))->getTimestamp();
    $nowT = $oNow->getTimestamp();

    if($nowT >= $pickMoreFrom){
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
    Json::die([
        'result'=>true,
        'pick'=>Json::decode($token['pick_result']),
        'pickMoreFrom'=>$token['pick_more_from'],
        'validUntil'=>$token['valid_until']
    ]);
}

$candidates = [];

$weight = [];
foreach($db->query('SELECT `no`, `name`, leader, power, intel, imgsvr, picture, special, special2 FROM general WHERE npc=2') as $general){
    $general['special'] = \sammo\SpecialityConst::DOMESTIC[$general['special']][0]??'-';
    $general['special2'] = \sammo\SpecialityConst::WAR[$general['special2']][0]??'-';
    $candidates[$general['no']] = $general + ['keepCnt'=>KEEP_CNT];
    $allStat = $general['leader'] + $general['power'] + $general['intel'];
    $weight[$general['no']] = pow($allStat, 1.7);
}

foreach($db->queryFirstColumn('SELECT pick_result FROM select_npc_token WHERE `owner`!=%i AND valid_until >=%s', $userID, $now) as $reserved){
    $reserved = Json::decode($reserved);
    foreach(array_keys($reserved) as $reservedNPC){
        if(key_exists($reservedNPC, $weight)){
            unset($candidates[$reservedNPC]);
            unset($weight[$reservedNPC]);
        }
    }
}

$pickLimit = min(count($candidates), 5);

while(count($pickResult) < $pickLimit){
    $generalID = Util::choiceRandomUsingWeight($weight);
    if(!key_exists($generalID, $pickResult)){
        $pickResult[$generalID] = $candidates[$generalID];
    }
}

$newNonce = mt_rand(0, 0xfffffff);

$validMinute = max(VALID_MINUTE, intdiv($turnterm, 2));
$pickMoreMinute = max(PICK_MORE_MINUTE, intdiv($turnterm, 5));

$validUntil = $oNow->add(new \DateInterval(sprintf('PT%dM', $validMinute)));
$pickMoreFrom = $oNow->add(new \DateInterval(sprintf('PT%dM', $pickMoreMinute)));

$db->query('LOCK TABLES select_npc_token WRITE');

$db->delete('select_npc_token', 'valid_until < %s', $now);

$inserted = 0;

if($token){
    $db->update('select_npc_token', [
        'valid_until'=>$validUntil->format('Y-m-d H:i:s'),
        'pick_more_from'=>$pickMoreFrom->format('Y-m-d H:i:s'),
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
        'valid_until'=>$validUntil->format('Y-m-d H:i:s'),
        'pick_more_from'=>'2000-01-01 01:00:00',
        'pick_result'=>Json::encode($pickResult),
        'nonce'=>$newNonce
    ]);

    if($db->affectedRows()){
        $inserted = 1;
    }
}

$db->query('UNLOCK TABLES');

if($inserted === 0){
    Json::die([
        'result'=>false,
        'reason'=>'중복 요청, 다시 랜덤 토큰을 확인해주세요'
    ]);
}

Json::die([
    'result'=>true,
    'pick'=>$pickResult,
    'pickMoreFrom'=>($inserted===-1)?$pickMoreFrom->format('Y-m-d H:i:s'):'2000-01-01 01:00:00',
    'validUntil'=>$validUntil->format('Y-m-d H:i:s')
]);