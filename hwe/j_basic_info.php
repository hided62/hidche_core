<?php
namespace sammo;

include('lib.php');
include('func.php');

$result = [
    "generalID"=>null,
    "myNationID"=>null,
    "isChief"=>false
];
$generalID = getGeneralID();

session_write_close();

if(!$generalID){
    Json::die($result);
}

$generalInfo = DB::db()->queryFirstRow('SELECT `nation`, `level` from `general` where `id`=%i', $generalID);
if(!$generalInfo){
    Json::die($result);
}

$result['generalID'] = $generalID;
$result['myNationID'] = $generalInfo['nation'];
$result['isChief'] = ($generalInfo['level'] == 12);

Json::die($result);