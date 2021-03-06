<?php
namespace sammo;

include 'lib.php';
include "func.php";

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if (!$db->queryFirstField("SHOW TABLES LIKE 'reserved_open'")) {
    Json::die([
        'result'=>true,
        'affected'=>0,
        'status'=>'no_reserved_table'
    ]);
}

$reserved = $db->queryFirstRow('SELECT `date`, options FROM reserved_open ORDER BY `date` ASC LIMIT 1');

if(!$reserved){
    Json::die([
        'result'=>true,
        'affected'=>0,
        'status'=>'no_reserved'
    ]);
}

$reservedDate = new \DateTime($reserved['date']);
$now = new \DateTime();


$status = 'not_yet';

list($isUnited, $lastTurn) = $gameStor->getValuesAsArray(['isunited', 'turntime']);
if($isUnited === null || $lastTurn === null){
    $isUnited = 2;
    $lastTurn = '2000-01-01';
}

if($lastTurn !== null){
    $lastTurn = new \DateTime($lastTurn);
}

if($lastTurn === null){
    //이미 리셋된 상태임
}
else if(file_exists(__DIR__.'/.htaccess')){
    //일단 서버는 닫혀 있음
}
else if(
    $isUnited == 2 &&
    $now->getTimestamp() - $lastTurn->getTimestamp() > $reservedDate->getTimestamp() - $now->getTimestamp()
){
    //정지 상태 & 중간 넘음
    ServConfig::getServerList()[DB::prefix()]->closeServer();
    $status = 'closed';
}
else if(
    $isUnited > 0 &&
    $now->getTimestamp() - $lastTurn->getTimestamp() > ($reservedDate->getTimestamp() - $now->getTimestamp()) * 2
){
    //천통 & 비정지 상태 & 2/3 넘음
    ServConfig::getServerList()[DB::prefix()]->closeServer();
    $status = 'closed';
}
else if($reservedDate->getTimestamp() - $now->getTimestamp() <= 60*10){
    //어쨌든 간에 10분 남았다면.
    ServConfig::getServerList()[DB::prefix()]->closeServer();
    $status = 'closed';
}

if($now < $reservedDate){
    Json::die([
        'result'=>true,
        'affected'=>0,
        'status'=>$status
    ]);
}

$options = Json::decode($reserved['options']);

$result = ResetHelper::buildScenario(
    $options['turnterm'],
    $options['sync'],
    $options['scenario'],
    $options['fiction'],
    $options['extend'],
    $options['block_general_create'],
    $options['npcmode'],
    $options['show_img_level'],
    !!$options['tournament_trig'],
    $options['join_mode'],
    $options['starttime'],
    $options['autorun_user']?:null
);

$result['affected']=1;

$prefix = DB::prefix();
ServConfig::getServerList()[$prefix]->openServer();

Json::die($result);