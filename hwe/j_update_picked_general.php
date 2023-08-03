<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$pick = Util::getPost('pick');

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
$oNow = new \DateTimeImmutable();
$now = $oNow->format('Y-m-d H:i:s');

$generalID = $db->queryFirstField('SELECT no FROM general WHERE owner = %i', $userID);
if(!$generalID){
    Json::die([
        'result'=>false,
        'reason'=>'장수가 생성하지 않았습니다. 이미 사망하지 않았는지 확인해보세요.'
    ]);
}

list(
    $year,
    $month,
    $maxgeneral,
    $npcmode,
    $turnterm
) = $gameStor->getValuesAsArray(['year', 'month', 'maxgeneral', 'npcmode', 'turnterm']);

if($npcmode!=2){
    Json::die([
        'result'=>false,
        'reason'=>'선택 가능한 서버가 아닙니다'
    ]);
}

$info = $db->queryFirstField('SELECT info FROM select_pool WHERE `owner` = %i AND `reserved_until`>=%s AND `unique_name`=%s', $userID, $now, $pick);
if(!$info){
    Json::die([
        'result'=>false,
        'reason'=>'유효한 장수 목록이 없습니다.'
    ]);
}

$ownerInfo = RootDB::db()->queryFirstRow('SELECT `name`,`picture`,`imgsvr` FROM member WHERE `NO`=%i',$userID);
if(!$ownerInfo){
    Json::die([
        'result'=>false,
        'reason'=>'멤버 정보를 가져오지 못했습니다.'
    ]);
}

$info = Json::decode($info);


$generalObj = General::createObjFromDB($generalID);
$oldGeneralName = $generalObj->getName();
$db->update('select_pool', [
    'general_id'=>-$generalID,
    'owner'=>null,
    'reserved_until'=>null
], 'unique_name=%s AND `reserved_until` IS NOT NULL AND owner = %i', $info['uniqueName'], $userID);

if($db->affectedRows()==0){
    Json::die([
        'result'=>false,
        'reason'=>'동시성 제어에 문제가 발생했습니다. 버그 제보를 부탁드립니다.'
    ]);
}

$db->update('select_pool', [
    'general_id'=>null,
    'owner'=>null,
    'reserved_until'=>null,
], 'unique_name != %s AND general_id = %i', $info['uniqueName'], $generalID);

$db->update('select_pool', [
    'general_id'=>$generalID,
], 'general_id=%i', -$generalID);
if($db->affectedRows()==0){
    Json::die([
        'result'=>false,
        'reason'=>'장수 선택 과정에 문제가 발생했습니다.. 버그 제보를 부탁드립니다.'
    ]);
}
$db->update('select_pool',[
    'owner'=>null,
    'reserved_until'=>null,
], '(owner=%i or reserved_until < %s) AND general_id is NULL', $userID, $now);

if(key_exists('leadership', $info)){
    $generalObj->updateVar('leadership', $info['leadership']);
    $generalObj->updateVar('strength', $info['strength']);
    $generalObj->updateVar('intel', $info['intel']);
}
if(key_exists('picture', $info)){
    $generalObj->updateVar('imgsvr', $info['imgsvr']);
    $generalObj->updateVar('picture', $info['picture']);
}
if(key_exists('generalName', $info)){
    $generalObj->updateVar('name', $info['generalName']);
}
if(key_exists('dex', $info)){
    $generalObj->updateVar('dex1', $info['dex'][0]);
    $generalObj->updateVar('dex2', $info['dex'][1]);
    $generalObj->updateVar('dex3', $info['dex'][2]);
    $generalObj->updateVar('dex4', $info['dex'][3]);
    $generalObj->updateVar('dex5', $info['dex'][4]);
}
if(key_exists('ego', $info)){
    $generalObj->updateVar('personal', $info['ego']);
}
if(key_exists('specialDomestic', $info)){
    $generalObj->updateVar('special', $info['specialDomestic']);
}
if(key_exists('specialWar', $info)){
    $generalObj->updateVar('special2', $info['specialWar']);
}
$generalObj->setAuxVar('next_change', TimeUtil::nowAddMinutes(12 * $turnterm));

$userNick = $ownerInfo['name'];
$generalObj->setVar('owner_name', $userNick);
$josaYi = JosaUtil::pick($userNick, '이');

$generalName = $info['generalName'];
$josaRo = JosaUtil::pick($generalName, '로');


$logger = $generalObj->getLogger();
$logger->pushGeneralHistoryLog("장수를 <Y>{$oldGeneralName}</>에서 <Y>{$generalName}</>{$josaRo} 변경");
$logger->pushGlobalActionLog("<Y>{$userNick}</>{$josaYi} 장수를 <Y>{$oldGeneralName}</>에서 <Y>{$generalName}</>{$josaRo} 변경합니다.");
$generalObj->applyDB($db);


Json::die([
    'result'=>true,
    'reason'=>'success'
]);