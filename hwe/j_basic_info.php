<?php
namespace sammo;

include('lib.php');
include 'func.php';

$result = [
    "generalID"=>0,
    "myNationID"=>0,
    "isChief"=>false,
    "officerLevel"=>0,
    "permission"=>0,
];
$session = Session::requireGameLogin([])->setReadOnly();
$userID = Session::getUserID();

$generalInfo = DB::db()->queryFirstRow('SELECT `no`, `nation`, `officer_level`, belong, penalty, permission from `general` where `owner`=%i', $userID);
if(!$generalInfo){
    Json::die($result);
}

$permission = checkSecretPermission($generalInfo);


$result['generalID'] = $generalInfo['no'];
$result['myNationID'] = $generalInfo['nation'];
$result['isChief'] = ($generalInfo['officer_level'] == 12);
$result['officerLevel'] = $generalInfo['officer_level'];
$result['permission'] = $permission;

Json::die($result);