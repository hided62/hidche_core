<?php
namespace sammo;

include('lib.php');
include 'func.php';

$result = [
    "generalID"=>null,
    "myNationID"=>null,
    "isChief"=>false
];
$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$generalInfo = DB::db()->queryFirstRow('SELECT `no`, `nation`, `level`, belong, penalty, permission from `general` where `owner`=%i', $userID);
if(!$generalInfo){
    Json::die($result);
}

$permission = checkSecretPermission($generalInfo);


$result['generalID'] = $generalInfo['no'];
$result['myNationID'] = $generalInfo['nation'];
$result['isChief'] = ($generalInfo['level'] == 12);
$result['generalLevel'] = $generalInfo['level'];
$result['permission'] = $permission;

Json::die($result);