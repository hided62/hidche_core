<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $level, $genlist, $outlist

//TODO: 변경이 완료되면 항상 공지되어야함

$isAmbassador = Util::getReq('isAmbassador', 'bool');
$genlist = Util::getReq('genlist', 'array_int');

//로그인 검사
$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$me = $db->queryFirstRow('SELECT no, level, nation FROM general WHERE owner = %i', $userID);

if(!$me || $me['level'] != 12){
    Json::die([
        'result'=>false,
        'reason'=>'군주가 아닙니다',
        'tmp'=>$me
    ]);
}

$nationID = $me['nation'];

if($isAmbassador){
    $targetType = 'ambassador';
    $targetLevel = 4;
    if($genlist && count($genlist) > 2){
        Json::die([
            'result'=>false,
            'reason'=>'외교권자는 최대 둘까지만 설정 가능합니다.'
        ]);
    }
}
else{
    $targetType = 'auditor';
    $targetLevel = 3;
}

$db->update('general', [
    'permission'=>'normal'
], 'nation = %i AND permission = %s', $nationID, $targetType);

if(!$genlist){
    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}
$realCandidates = [];
foreach($db->query('SELECT no, nation, level, penalty, permission FROM general WHERE nation = %i AND level != 12 AND permission = \'normal\' AND no IN %li', $nationID, $genlist) as $candidate){
    $maxPermission = checkSecretMaxPermission($candidate);
    if($maxPermission < $targetLevel){
        continue;
    }
    $realCandidates[] = $candidate['no'];
}

if(!$realCandidates){
    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

$db->update('general', [
    'permission'=>$targetType,
], 'no IN %li', $realCandidates);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);