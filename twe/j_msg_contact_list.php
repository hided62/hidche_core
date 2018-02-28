<?php
include('lib.php');
include('func.php');

use utilphp\util as util;

if(!getGeneralID()){
    returnJson([
        "nation"=>[]
    ]);
}

session_write_close(); // 세션 안쓴다

//NOTE: 모든 국가, 모든 장수에 대해서 같은 결과라면 캐싱 가능하지 않을까?

$generalNations = [];

foreach(getDB()->query('select `no`, `name`, `nation`, `level`, `npc` from `general` where `npc` < 2') as $general)
{
    list($generalID, $generalName, $nationID, $level, $npc) = $general;
    if(!isset($generalNations[$nationID])){
        $generalNations[$nationID] = [];
    }

    $isChief = ($level == 12);

    $obj = [$generalID, $generalName];
    if($isChief){
        $obj[] = 1;
    }

    //TODO: 빙의장 정보 추가
    $generalNations[$nationID][] = $obj;
}

$neutral = [
    "nation"=>0,
    "name"=>"재야",
    "color"=>"#ffffff"
];

$result = array_map(function($nation){
    $nationID = $nation['nation'];
    $mailbox = $nationID + 9000;
    $nation = $nation['name'];
    $color = ('#'.$nation['color']).replace('##','#');//xxx: #기호 없는 이전 코드 대비용
    $generals = util::array_get($generalNations[$nationID], []);

    return [
        "nationID"=>$nationID,
        "mailbox"=>$mailbox,
        "nation"=>$nationID,
        "color"=>$color,
        "general"=>$generals
    ];
}, array_merge([$neutral], getAllNationStaticInfo()));

returnJson([
    "nation"=>$result
]);