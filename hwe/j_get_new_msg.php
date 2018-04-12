<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;

$jsonPost = WebUtil::parseJsonPost();

$reqSequence = Util::toInt(Util::array_get($jsonPost['sequence'], 0));


$nationID = DB::db()->queryFirstField(
    'select `nation` from `general` where no = %i',
    $generalID
);


if($nationID === null){
    Json::die([
        'result'=>false,
        'reason'=>'소속 국가가 없습니다'
    ]);
}

Json::die([
    'result'=>true,
    'private'=>getMessage('private', $nationID, 10, $reqSequence),
    'public'=>getMessage('public', $nationID, 20, $reqSequence),
    'national'=>getMessage('national', $nationID, 30, $reqSequence),
    'diplomacy'=>getMessage('diplomacy', $nationID, 10, $reqSequence)
]);