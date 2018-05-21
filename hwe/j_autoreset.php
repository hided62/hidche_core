<?php
namespace sammo;

include 'lib.php';
include "func.php";

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
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
else if(file_exists(__dir__.'/.htaccess')){
    //일단 서버는 닫혀 있음
}
else if(
    $isUnited == 2 &&
    $now->getTimestamp() - $lastTurn->getTimestamp() > $reservedDate->getTimestamp() - $now->getTimestamp()
){
    //정지 상태 & 중간 넘음
    AppConf::getList()[DB::prefix()]->closeServer();
    $status = 'closed';
}
else if(
    $isUnited > 0 && 
    $now->getTimestamp() - $lastTurn->getTimestamp() > ($reservedDate->getTimestamp() - $now->getTimestamp()) * 2
){
    //천통 & 비정지 상태 & 2/3 넘음
    AppConf::getList()[DB::prefix()]->closeServer();
    $status = 'closed';
}
else if($reservedDate->getTimestamp() - $now->getTimestamp() <= 60*10){
    //어쨌든 간에 10분 남았다면.
    AppConf::getList()[DB::prefix()]->closeServer();
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
    $options['npcmode'],
    $options['show_img_level'],
    $options['tournament_trig']
);

$result['affected']=1;

$prefix = DB::prefix();
AppConf::getList()[$prefix]->openServer();

Json::die($result);