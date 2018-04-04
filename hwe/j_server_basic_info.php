<?php
namespace sammo;

include "lib.php";

$session = Session::requireLogin([
    'game'=>'x',
    'me'=>null
])->setReadOnly();

$db = DB::db();

$game = $db->queryFirstRow('SELECT isUnited, npcMode, year, month, scenario, scenario_text, maxgeneral as maxUserCnt, turnTerm from game where `no`=1');

$nationCnt = $db->queryFirstField('SELECT count(`nation`) from nation where `level` > 0');
$genCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` < 2');
$npcCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` >= 2');

$game['scenario'] = $game['scenario_text'];
$game['userCnt'] = $genCnt;
$game['npcCnt'] = $npcCnt;
$game['nationCnt'] = $nationCnt;

$me = [];

$general = $db->queryFirstRow('SELECT name, picture, imgsvr from general where owner=%i', $session->userID);
if($general){
    $me['name'] = $general['name'];

    if($general['imgsvr'] == 0) {
        $me['picture'] = '../../image/'.$general['picture'];
    } else {
        $me['picture'] = '../d_pic/'.$general['picture'];
    }
}

//TODO: 이를 표현하는 방법은 '이전 버전'의 serverListPost.php를 참고할 것.
Json::die([
    'game'=>$game,
    'me'=>$me?:null
]);