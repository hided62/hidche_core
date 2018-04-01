<?php
namespace sammo;

include('lib.php');

$result = [
    "generalID"=>null,
    "myNationID"=>null,
    "isChief"=>false
];
$session = Session::Instance()->loginGame()->setReadOnly();

$generalID = $session->generalID;

if(!$generalID){
    Json::die($result);
}

$generalInfo = DB::db()->queryFirstRow('SELECT `nation`, `level` from `general` where `no`=%i', $generalID);
if(!$generalInfo){
    Json::die($result);
}

$result['generalID'] = $generalID;
$result['myNationID'] = $generalInfo['nation'];
$result['isChief'] = ($generalInfo['level'] == 12);

Json::die($result);