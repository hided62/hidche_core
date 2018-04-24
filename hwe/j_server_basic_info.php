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
            'fictionMode'=>($options['fiction']?'사실':'가상'),
            'npcMode'=>($options['npcmode']?'가능':'불가'),
            'openDatetime'=>$reserved['date']
        ],
        'game'=>null,
        'me'=>null
    ]);
}

//TODO: 천통시에도 예약 오픈 알림이 필요..?

$game = $db->queryFirstRow('SELECT isUnited, npcMode, year, month, scenario, scenario_text, maxgeneral as maxUserCnt, turnTerm from game where `no`=1');

$nationCnt = $db->queryFirstField('SELECT count(`nation`) from nation where `level` > 0');
$genCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` < 2');
$npcCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` >= 2');

$game['scenario'] = $game['scenario_text'];
$game['userCnt'] = $genCnt;
$game['npcCnt'] = $npcCnt;
$game['nationCnt'] = $nationCnt;

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
    'game'=>$game,
    'me'=>$me?:null
]);