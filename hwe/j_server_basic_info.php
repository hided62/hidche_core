<?php
namespace sammo;

include "lib.php";
include "func_template.php";

$session = Session::requireLogin([
    'game'=>null,
    'me'=>null
])->setReadOnly();
$userID = $session->userID;

if(!class_exists('\\sammo\\DB')){
    Json::die([
        'game'=>null,
        'me'=>null
    ]);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if(file_exists(__DIR__.'/.htaccess')){
    $reserved = $db->queryFirstRow(
        'SELECT * FROM reserved_open ORDER BY `date` ASC LIMIT 1'
    );
    if(!$reserved){
        Json::die([
            'game'=>null,
            'me'=>null
        ]);
    }

    $options = Json::decode($reserved['options']);

    $otherTextInfo = [];

    if($options['join_mode'] == 'onlyRandom'){
        $otherTextInfo[] = '랜덤 임관 전용';
    }

    if($options['autorun_user']['limit_minutes']??false){
        $otherTextInfo[] = getAutorunInfo($options['autorun_user']);
    }

    if(!$otherTextInfo){
        $otherTextInfo = '표준';
    }
    else{
        $otherTextInfo = join(', ', $otherTextInfo);
    }


    Json::die([
        'reserved'=>[
            'scenarioName'=>$options['scenarioName'],
            'turnterm'=>$options['turnterm'],
            'fictionMode'=>($options['fiction']?'가상':'사실'),
            'block_general_create'=>(!!$options['block_general_create']),
            'npcMode'=>([0=>'불가',1=>'가능',2=>'선택 생성'][$options['npcmode']]),
            'openDatetime'=>$reserved['date'],
            'starttime'=>$options['starttime'],
            'gameConf'=>$options['gameConf'],
            'otherTextInfo'=>$otherTextInfo
        ],
        'game'=>null,
        'me'=>null
    ]);
}

//TODO: 천통시에도 예약 오픈 알림이 필요..?

$admin = $gameStor->getValues(['isunited', 'npcmode', 'year', 'month', 'scenario', 'scenario_text', 'maxgeneral', 'turnterm', 'opentime', 'turntime', 'join_mode', 'fiction', 'block_general_create', 'autorun_user']);
$admin['maxUserCnt'] = $admin['maxgeneral'];
$admin['npcMode'] = $admin['npcmode'];
$admin['turnTerm'] = $admin['turnterm'];
$admin['isUnited'] = $admin['isunited'];
$admin['starttime'] = substr($admin['opentime'], 5, 11);
$admin['turntime'] = substr($admin['turntime'], 5, 11);
unset($admin['npcmode']);
unset($admin['maxgeneral']);
unset($admin['turnterm']);
unset($admin['isunited']);

$nationCnt = $db->queryFirstField('SELECT count(`nation`) from nation where `level` > 0');
$genCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` < 2');
$npcCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` >= 2');

$admin['scenario'] = $admin['scenario_text'];
$admin['userCnt'] = $genCnt;
$admin['npcCnt'] = $npcCnt;
$admin['nationCnt'] = $nationCnt;
$admin['block_general_create'] = !!$admin['block_general_create'];
$admin['npcMode'] = [0=>'불가',1=>'가능',2=>'선택 생성'][$admin['npcMode']];
$admin['fictionMode'] = $admin['fiction']?'가상':'사실';

$otherTextInfo = [];

if($admin['join_mode'] == 'onlyRandom'){
    $otherTextInfo[] = '랜덤 임관 전용';
}

if($admin['autorun_user']['limit_minutes']??false){
    $otherTextInfo[] = getAutorunInfo($admin['autorun_user']);
}

if(!$otherTextInfo){
    $otherTextInfo = '표준';
}
else{
    $otherTextInfo = join(', ', $otherTextInfo);
}


$admin['otherTextInfo'] = $otherTextInfo;
$admin['defaultStatTotal'] = GameConst::$defaultStatTotal;
$me = [];

$general = $db->queryFirstRow('SELECT name, picture, imgsvr from general where owner=%i', $userID);
if($general){
    $me['name'] = $general['name'];

    if($general['imgsvr'] == 0) {
        $me['picture'] = ServConfig::getSharedIconPath().'/'.$general['picture'];
    } else {
        $me['picture'] = AppConf::getUserIconPathWeb().'/'.$general['picture'];
    }
}

//TODO: 이를 표현하는 방법은 '이전 버전'의 serverListPost.php를 참고할 것.
Json::die([
    'game'=>$admin,
    'me'=>$me?:null
]);