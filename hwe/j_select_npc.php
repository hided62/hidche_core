<?php
namespace sammo;

use sammo\Enums\GeneralAccessLogColumn;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

/** @var int|null */
$pick = Util::getPost('pick', 'int');

if(!$pick){
    Json::die([
        'result'=>false,
        'reason'=>'장수를 선택하지 않았습니다'
    ]);
}

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$rootDB = RootDB::db();

$oNow = new \DateTimeImmutable();
$now = $oNow->format('Y-m-d H:i:s');

$userNick = RootDB::db()->queryFirstField('SELECT `NAME` FROM member WHERE `NO`=%i',$userID);
if(!$userNick){
    Json::die([
        'result'=>false,
        'reason'=>'멤버 정보를 가져오지 못했습니다.'
    ]);
}

$pickResult = $db->queryFirstField('SELECT pick_result FROM select_npc_token WHERE `owner`=%i AND `valid_until`>=%s', $userID, $now);
if(!$pickResult){
    Json::die([
        'result'=>false,
        'reason'=>'유효한 장수 목록이 없습니다.'
    ]);
}


$pickResult = Json::decode($pickResult);
if(!key_exists($pick, $pickResult)){
    Json::die([
        'result'=>false,
        'reason'=>'선택한 장수가 목록에 없습니다.'
    ]);
}
$pickedNPC = $pickResult[$pick];


$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');
list(
    $year,
    $month,
    $maxgeneral,
    $npcmode
) = $gameStor->getValuesAsArray(['year', 'month', 'maxgeneral', 'npcmode']);

if($npcmode!=1){
    Json::die([
        'result'=>false,
        'reason'=>'빙의 가능한 서버가 아닙니다'
    ]);
}

if ($gencount >= $maxgeneral) {
    Json::die([
        'result'=>false,
        'reason'=>'더 이상 등록 할 수 없습니다.'
    ]);
}

$genAux = Json::decode($db->queryFirstField('SELECT aux FROM general WHERE no = %i', $pick)??'{}');
$genAux['pickYearMonth'] = Util::joinYearMonth($year, $month);
//등록 시작
$db->update('general', [
    'owner_name'=>$userNick,
    'npc'=>1,
    'killturn'=>6,
    'defence_train'=>80,
    'permission'=>'normal',
    'owner'=>$userID,
    'aux'=>Json::encode($genAux)
], 'owner <= 0 AND npc = 2 AND no = %i', $pick);
$db->insertIgnore('general_access_log', [
    GeneralAccessLogColumn::generalID->value => $pick,
    GeneralAccessLogColumn::userID->value => $userID,
    GeneralAccessLogColumn::lastRefresh->value => $now,
]);

if(!$db->affectedRows()){
    Json::die([
        'result'=>false,
        'reason'=>'장수 등록에 실패했습니다.'
    ]);
}

$db->delete('select_npc_token', 'owner=%i or valid_until < %s', $userID, $now);

$josaYi = JosaUtil::pick($userNick, '이');

$logger = new ActionLogger($pickedNPC['no'], 0, $year, $month);
$logger->pushGeneralHistoryLog("<Y>{$pickedNPC['name']}</>의 육체에 <Y>{$userNick}</>{$josaYi} 빙의되다.");
$logger->pushGlobalActionLog("<Y>{$pickedNPC['name']}</>의 육체에 <Y>{$userNick}</>{$josaYi} <S>빙의</>됩니다!");
$logger->flush();

pushAdminLog(["가입 : {$userID} // {$session->userName} // {$pick} // ".getenv("REMOTE_ADDR")]);

$rootDB->insert('member_log', [
    'member_no' => $userID,
    'date'=>TimeUtil::now(),
    'action_type'=>'make_general',
    'action'=>Json::encode([
        'server'=>DB::prefix(),
        'type'=>'npc',
        'generalID'=>$pickedNPC['no'],
        'generalName'=>$pickedNPC['name']
    ])
]);

Json::die([
    'result'=>true,
    'reason'=>'success'
]);