<?php
namespace sammo;

include "lib.php";
include "func.php";

function sortTokens(&$tokens){
    usort($tokens, function($lhs, $rhs){
        return array_sum($lhs['dex'])<=>array_sum($rhs['dex']);
    });
}

function putInfoText(&$info){
    if(key_exists('specialDomestic', $info)){
        $class = buildGeneralSpecialDomesticClass($info['specialDomestic']);
        $info['specialDomesticName'] = $class->getName();
        $info['specialDomesticInfo'] = $class->getInfo();
    }

    if(key_exists('specialWar', $info)){
        $class = buildGeneralSpecialDomesticClass($info['specialWar']);
        $info['specialWarName'] = $class->getName();
        $info['specialWarInfo'] = $class->getInfo();
    }
}

$session = Session::requireLogin([])->setReadOnly();
$userID = Session::getUserID();

$oNow = new \DateTimeImmutable();

$now = $oNow->format('Y-m-d H:i:s');

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$npcmode = $gameStor->getValue('npcmode');
if($npcmode!=2){
    Json::die([
        'result'=>false,
        'reason'=>'선택 가능한 서버가 아닙니다'
    ]);
}

$rawGeneral = $db->queryFirstRow('SELECT no, aux FROM general WHERE `owner` = %i', $userID);
if($rawGeneral){
    $generalAux = Json::decode($rawGeneral['aux']);
    if(key_exists('next_change', $generalAux)&& $generalAux['next_change'] > $now){
        Json::die([
            'result'=>false,
            'reason'=>'아직 다시 고를 수 없습니다'
        ]);
    }
}

$tokens = $db->query('SELECT unique_name, reserved_until, info FROM `select_pool` WHERE `owner`=%i AND `reserved_until`>=%s', $userID, $now);

if($tokens){
    $pick = [];
    $valid_until = null;
    $specialInfo = [];
    foreach($tokens as $token){
        $valid_until = $token['reserved_until'];
        $info = Json::decode($token['info']);
        putInfoText($info);
        $info['uniqueName'] = $token['unique_name'];
        $pick[] = $info;
    }
    sortTokens($pick);//좀 무식하지만..
    Json::die([
        'result'=>true,
        'pick'=>$pick,
        'validUntil'=>$valid_until
    ]);
}

$rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
    UniqueConst::$hiddenSeed, 'selectPool', $userID, $now
)));

$pick = [];
$valid_until = null;
foreach(pickGeneralFromPool($db, $rng, $userID, 14) as $pickObj){
    $valid_until = $pickObj->getValidUntil();
    $info = $pickObj->getInfo();
    putInfoText($info);
    $pick[] = $info;
}
sortTokens($pick);//좀 무식하지만..
Json::die([
    'result'=>true,
    'pick'=>$pick,
    'validUntil'=>$valid_until
]);