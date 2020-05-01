<?php
namespace sammo;

use Exception;

include('lib.php');
include('func.php');

$session = Session::requireLogin([])->setReadOnly();

if(Session::getInstance()->userGrade < 5){
    Json::die([
        'reason'=>'권한이 부족합니다.'
    ]);
}

$eventName = Util::getReq('event', 'string');
$eventArgsJson = Util::getReq('arg', 'string');

if($eventName === null){
    Json::die([
        'result'=>false,
        'reason'=>'event가 지정되지 않았습니다.'
    ]);
}

$eventArgs = [$eventName];
if($eventArgsJson !== null){
    try{
        $eventNextArgs = Json::decode($eventArgsJson);
        if(is_array($eventNextArgs)){
            $eventArgs = array_merge($eventArgs, $eventNextArgs);
        }
        else{
            $eventArgs[] = $eventNextArgs;
        }
    }
    catch(\Exception $e){
        Json::die([
            'result'=>false,
            'reason'=>'arg가 올바른 json이 아닙니다'
        ]);
    }
}

try{
    $action = Event\Action::build($eventArgs);
}
catch(Exception $e){
    Json::die([
        'result'=>false,
        'reason'=>$e->getMessage()
    ]);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$env = $gameStor->getAll();

$result = $action->run($env);

Json::die([
    'result'=>true,
    'reason'=>'success',
    'info'=>$result,
]);