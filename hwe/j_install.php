<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin([])->setReadOnly();

if(!class_exists('\\sammo\\DB')){
    Json::die([
        'result'=>false,
        'reason'=>'DB리셋 필요'
    ]);
}

$serverName = DB::prefix();
$serverAcl = $session->acl[$serverName]??[];
$allowReset = in_array('reset', $serverAcl);
$allowFullReset = in_array('fullReset',$serverAcl);
$allowReset |= $allowFullReset;

$reserve_open = Util::getReq('reserve_open');
if($reserve_open && $reserve_open < date('Y-m-d H:i')){
    Json::die([
        'result'=>false,
        'reason'=>'현재 시간보다 이전 시간대를 예약 시간으로 지정했습니다.'
    ]);
}

$pre_reserve_open = Util::getReq('pre_reserve_open');
if($pre_reserve_open && !$reserve_open){
    Json::die([
        'result'=>false,
        'reason'=>'가오픈 예약을 위해선 오픈 예약을 지정해야합니다.'
    ]);
}
if($pre_reserve_open && $pre_reserve_open >= $reserve_open){
    Json::die([
        'result'=>false,
        'reason'=>'가오픈 시간이 오픈 예약 시점보다 이전이어야 합니다.'
    ]);
}

if($session->userGrade < 5 && !$allowReset){
    Json::die([
        'result'=>false,
        'reason'=>'관리자 아님'
    ]);
}

$v = new Validator($_POST);
$v->rule('required', [
    'turnterm',
    'sync',
    'scenario',
    'fiction',
    'extend',
    'join_mode',
    'npcmode',
    'show_img_level'
])->rule('integer', [
    'turnterm',
    'sync',
    'scenario',
    'fiction',
    'extend',
    'npcmode',
    'show_img_level',
    'tournament_trig',
])->rule('in', 'join_mode', ['onlyRandom', 'full']);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr()
    ]);
}

$allowReset = true;
if($session->userGrade < 5 && !$allowFullReset){
    //리셋 가능한 조건인지 테스트
    $allowReset = false;

    if(file_exists(__dir__.'/.htaccess')){
        $allowReset = true;
    }
    else{
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        if($gameStor->isunited){
            $allowReset = true;
        }
    }
}

if(!$allowReset){
    Json::die([
        'result'=>false,
        'reason'=>'부족한 권한: 서버가 닫혀있거나, 천통되어 있을 경우에만 리셋 가능합니다.'
    ]);
}

$turnterm  = (int)$_POST['turnterm'];
$sync = (int)$_POST['sync'];
$scenario = (int)$_POST['scenario'];
$fiction = (int)$_POST['fiction'];
$extend = (int)$_POST['extend'];
$npcmode = (int)$_POST['npcmode'];
$show_img_level = (int)$_POST['show_img_level'];
$tournament_trig = (int)$_POST['tournament_trig'];
$join_mode = $_POST['join_mode'];

if($reserve_open){
    $reserve_open = new \DateTime($reserve_open);
    $db = DB::db();
    
    if (!$db->queryFirstField("SHOW TABLES LIKE 'storage'")) {
        $clearResult = ResetHelper::clearDB();
        if(!$clearResult['result']){
            Json::die($clearResult);
        }
    }

    if (!$db->queryFirstField("SHOW TABLES LIKE 'reserved_open'")) {
        Json::die([
            'result'=>false,
            'reason'=>'예약 테이블이 없음!'
        ]);
    }

    $scenarioObj = new Scenario($scenario, true);
    $open_date = $reserve_open->format('Y-m-d H:i:s');

    $reserveInfo = [
        'turnterm'=>$turnterm,
        'sync'=>$sync,
        'scenario'=>$scenario,
        'scenarioName'=>$scenarioObj->getTitle(),
        'fiction'=>$fiction,
        'extend'=>$extend,
        'npcmode'=>$npcmode,
        'show_img_level'=>$show_img_level,
        'tournament_trig'=>$tournament_trig,
        'gameConf'=>$scenarioObj->getGameConf(),
        'join_mode'=>$join_mode,
        'starttime'=>$open_date,
    ];

    
    if($pre_reserve_open){
        $pre_reserve_open = new \DateTime($pre_reserve_open);
        $open_date = $pre_reserve_open->format('Y-m-d H:i:s');
    }


    
    $db->delete('reserved_open', true);
    $db->insert('reserved_open', [
        'options'=>Json::encode($reserveInfo),
        'date'=>$open_date
    ]);
    AppConf::getList()[DB::prefix()]->closeServer();
    Json::die([
        'result'=>true,
        'reason'=>'예약'
    ]);
}

Json::die(ResetHelper::buildScenario(
    $turnterm,
    $sync,
    $scenario,
    $fiction,
    $extend,
    $npcmode,
    $show_img_level,
    $tournament_trig,
    $join_mode,
    TimeUtil::DatetimeNow()
));