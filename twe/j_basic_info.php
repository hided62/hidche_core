<?php
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
    returnJson($result);
}

$generalInfo = getDB()->queryFirstRow('SELECT `nation`, `level` from `general` where `id`=%i', $generalID);
if(!$generalInfo){
    returnJson($result);
}

$result['generalID'] = $generalID;
$result['myNationID'] = $generalInfo['nation'];
$result['isChief'] = ($generalInfo['level'] == 12);

returnJson($result);