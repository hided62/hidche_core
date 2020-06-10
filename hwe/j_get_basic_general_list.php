<?php
namespace sammo;

include "lib.php";
include "func.php";

//로그인 검사
$session = Session::requireGameLogin([]);
$generalID = Session::getGeneralID();

$db = DB::db();

$rawList = Util::convertArrayToDict($db->query('SELECT no, name, npc, nation FROM general'), 'no');
$nationID = $rawList[$generalID]['nation'];


$result = [
    'result'=>true,
    'reason'=>'success',
    'nationID'=>$nationID,
    'generalID'=>$generalID,
    'column'=>['no', 'name', 'npc']
];

$resultItem = [];
foreach(Util::arrayGroupBy($rawList, 'nation') as $subNationID=>$generals){
    $subNation = [];
    foreach($generals as $general){
        $subNation[] = [$general['no'], $general['name'], $general['npc']];
    }
    $resultItem[$subNationID] = $subNation;
}

$result['list'] = $resultItem;
$result['nation'] = Util::convertArrayToDict($db->query('SELECT nation, name, color FROM nation'), 'nation');
Json::die($result);