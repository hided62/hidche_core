<?php
namespace sammo;

include('lib.php');

$result = [
    "generalID"=>null,
    "myNationID"=>null,
    "isChief"=>false
];
$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$generalInfo = DB::db()->queryFirstRow('SELECT `no`, `nation`, `level` from `general` where `owner`=%i', $userID);
if(!$generalInfo){
    Json::die($result);
}

$result['generalID'] = $generalInfo['no'];
$result['myNationID'] = $generalInfo['nation'];
$result['isChief'] = ($generalInfo['level'] == 12);

Json::die($result);