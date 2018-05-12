<?php
namespace sammo;

include "lib.php";

$session = Session::requireLogin([
    'game'=>null,
    'me'=>null
])->setReadOnly();
$userID = Session::getUserID();

if(!class_exists('\\sammo\\DB')){
    Json::die([
        'game'=>null,
        'me'=>null
    ]);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if(file_exists(__dir__.'/.htaccess')){
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

    Json::die([
        'reserved'=>[
            'scenarioName'=>$options['scenarioName'],
            'turnterm'=>$options['turnterm'],
            'fictionMode'=>($options['fiction']?'가상':'사실'),
            'npcMode'=>($options['npcmode']?'가능':'불가'),
            'openDatetime'=>$reserved['date']
        ],
        'game'=>null,
        'me'=>null
    ]);
}

//TODO: 천통시에도 예약 오픈 알림이 필요..?

$game_env = $gameStor->getValues(['isUnited', 'npcMode', 'year', 'month', 'scenario', 'scenario_text', 'maxgeneral', 'turnTerm']);
$game_env['maxUserCnt'] = $game_env['maxgeneral'];
unset($game_env['maxgeneral']);

$nationCnt = $db->queryFirstField('SELECT count(`nation`) from nation where `level` > 0');
$genCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` < 2');
$npcCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` >= 2');

$game_env['scenario'] = $game_env['scenario_text'];
$game_env['userCnt'] = $genCnt;
$game_env['npcCnt'] = $npcCnt;
$game_env['nationCnt'] = $nationCnt;

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
    'game'=>$game_env,
    'me'=>$me?:null
]);