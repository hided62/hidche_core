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
$allowReset = $allowReset || $allowFullReset;

$reserve_open = Util::getPost('reserve_open');
if($reserve_open && $reserve_open < date('Y-m-d H:i')){
    Json::die([
        'result'=>false,
        'reason'=>'현재 시간보다 이전 시간대를 예약 시간으로 지정했습니다.'
    ]);
}

$pre_reserve_open = Util::getPost('pre_reserve_open');
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
    'block_general_create',
    'join_mode',
    'npcmode',
    'show_img_level',
    'autorun_user_minutes'
])->rule('integer', [
    'turnterm',
    'sync',
    'scenario',
    'fiction',
    'extend',
    'block_general_create',
    'npcmode',
    'show_img_level',
    'tournament_trig',
    'autorun_user_minutes'
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

    if(file_exists(__DIR__.'/.htaccess')){
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
$block_general_create = (int)$_POST['block_general_create'];
$show_img_level = (int)$_POST['show_img_level'];
$tournament_trig = !!(int)$_POST['tournament_trig'];
$join_mode = $_POST['join_mode'];
$autorun_user_minutes = (int)$_POST['autorun_user_minutes'];
$autorun_user_options = [];

foreach(Util::getPost('autorun_user', 'array_string', []) as $autorun_option){
    $autorun_user_options[$autorun_option] = 1;
}

if($autorun_user_minutes > 0 && !$autorun_user_options){
    Json::die([
        'result'=>false,
        'reason'=>'적어도 자동 행동 중 하나는 선택을 해야합니다.'
    ]);
}

if($autorun_user_minutes < 0){
    Json::die([
        'result'=>false,
        'reason'=>'자동 행동 기한이 0보다 작을 수 없습니다.'
    ]);
}

$autorun_user = $autorun_user_minutes?[
    'limit_minutes'=>$autorun_user_minutes,
    'options'=>$autorun_user_options
]:null;

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

    $rng = new RandUtil(new LiteHashDRBG(random_bytes(16)));

    $scenarioObj = new Scenario($rng, $scenario, true);
    $open_date = $reserve_open->format('Y-m-d H:i:s');

    $reserveInfo = [
        'turnterm'=>$turnterm,
        'sync'=>$sync,
        'scenario'=>$scenario,
        'scenarioName'=>$scenarioObj->getTitle(),
        'fiction'=>$fiction,
        'extend'=>$extend,
        'block_general_create'=>$block_general_create,
        'npcmode'=>$npcmode,
        'show_img_level'=>$show_img_level,
        'tournament_trig'=>$tournament_trig,
        'gameConf'=>$scenarioObj->getGameConf(),
        'join_mode'=>$join_mode,
        'starttime'=>$open_date,
        'autorun_user'=>$autorun_user
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
    ServConfig::getServerList()[DB::prefix()]->closeServer();
    Json::die([
        'result'=>true,
        'reason'=>'예약'
    ]);
}

try{
    Json::die(ResetHelper::buildScenario(
        $turnterm,
        $sync,
        $scenario,
        $fiction,
        $extend,
        $block_general_create,
        $npcmode,
        $show_img_level,
        !!$tournament_trig,
        $join_mode,
        TimeUtil::now(),
        $autorun_user
    ));
}
catch(\Exception $e){
    Json::die([
        'result'=>false,
        'reason'=>$e->getMessage()
    ]);
}