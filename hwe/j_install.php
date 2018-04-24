<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin([])->setReadOnly();
if($session->userGrade < 5){
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
    'tournament_trig'
]);
if(!$v->validate()){
    Json::die([
        'result'=>false,
        'reason'=>$v->errorStr()
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

if(120 % $turnterm != 0){
    Json::die([
        'result'=>false,
        'reason'=>'turnterm은 120의 약수여야 합니다.'
    ]);
}

if($tournament_trig < 0 || $tournament_trig > 7){
    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 토너먼트 주기입니다.'
    ]);
}

if(!file_exists(__dir__.'/logs') || !file_exists(__dir__.'/data')){
    if(!is_writable(__dir__)){
        Json::die([
            'result'=>false,
            'reason'=>'logs, data 디렉토리를 생성할 권한이 없습니다.'
        ]);
    }
    mkdir(__dir__.'/logs', 0644);
    mkdir(__dir__.'/data', 0644);
}

if(!is_writable(__dir__.'/logs')){
    Json::die([
        'result'=>false,
        'reason'=>'logs 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if(!is_writable(__dir__.'/data')){
    Json::die([
        'result'=>false,
        'reason'=>'data 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if(!is_writable(__dir__.'/d_setting')){
    Json::die([
        'result'=>false,
        'reason'=>'d_setting 디렉토리의 쓰기 권한이 없습니다'
    ]);
}

if(!file_exists(__dir__.'/logs/.htaccess')){
    @file_put_contents(__dir__.'/logs/.htaccess', 'Deny from  all');
}

if(!file_exists(__dir__.'/data/.htaccess')){
    @file_put_contents(__dir__.'/data/.htaccess', 'Deny from  all');
}


$db = DB::db();
$mysqli_obj = $db->get();


$scenarioObj = new Scenario($scenario, false);
$startyear = $scenarioObj->getYear()??GameConst::$defaultStartYear;

FileUtil::delInDir(__dir__."/logs");
FileUtil::delInDir(__dir__."/data");

$result = Util::generateFileUsingSimpleTemplate(
    __DIR__.'/d_setting/UniqueConst.orig.php',
    __DIR__.'/d_setting/UniqueConst.php',[
        'serverID'=>DB::prefix().'_'.Util::randomStr(8)
    ], true
);

if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/reset.sql'))){
    while(true){
        if (!$mysqli_obj->more_results()) {
            break;
        }
        if(!$mysqli_obj->next_result()){
            break;
        }
    }
    
}

if($mysqli_obj->multi_query(file_get_contents(__dir__.'/sql/schema.sql'))){
    while(true){
        if (!$mysqli_obj->more_results()) {
            break;
        }
        if(!$mysqli_obj->next_result()){
            break;
        }
    }
}


$db->insert('plock', [
    'plock'=>1
]);

CityConst::build();




$turntime = date('Y-m-d H:i:s');
$time = substr($turntime, 11, 2);
if($sync == 0) {
    // 현재 시간을 1월로 맞춤
    $starttime = cutTurn($turntime, $turnterm);
    $month = 1;
    $year = $startyear;
} else {
    // 현재 시간과 동기화
    list($starttime, $yearPulled, $month) = cutDay($turntime, $turnterm);
    if($yearPulled){
        $year = $startyear-1;
    }
    else{
        $year = $startyear;
    }
}

$killturn = 4800 / $turnterm;
if($npcmode == 1) { $killturn = intdiv($killturn, 3); }

$env = [
    'scenario'=>$scenario,
    'scenario_text'=>$scenarioObj->getTitle(),
    'startyear'=>$startyear,
    'year'=> $year,
    'month'=> $month,
    'msg'=>'공지사항',//TODO:공지사항
    'maxgeneral'=>GameConst::$defaultMaxGeneral,
    'maxnation'=>GameConst::$defaultMaxNation,
    'conlimit'=>300,
    'gold_rate'=>100,
    'rice_rate'=>100,
    'turntime'=>$turntime,
    'starttime'=>$starttime,
    'turnterm'=>$turnterm,
    'killturn'=>$killturn,
    'genius'=>GameConst::$defaultMaxGenius,
    'show_img_level'=>$show_img_level,
    'npcmode'=>$npcmode,
    'extended_general'=>$extend,
    'fiction'=>$fiction,
    'tnmt_trig'=>$tournament_trig
];

foreach(RootDB::db()->query('SELECT `no`, `name`, `picture`, `imgsvr` FROM member WHERE grade >= 5') as $admin){
    $db->insert('general', [
        'owner'=>$admin['no'],
        'name'=>$admin['name'],
        'picture'=>$admin['picture'],
        'imgsvr'=>$admin['imgsvr'],
        'turntime'=>$turntime,
        'killturn'=>9999
    ]);
}

$db->insert('game', $env);

$scenarioObj->build($env);

$db->update('plock', [
    'plock'=>0
], true);

LogHistory(1);

$prefix = DB::prefix();
AppConf::getList()[$prefix]->closeServer();

Json::die([
    'result'=>true
]);