<?php
include('lib.php');
include('func.php');

use utilphp\util as util;

$generalID = getGeneralID();

session_write_close(); // 이제 세션 안 쓴다

$jsonPost = parseJsonPost();

$reqSequence = toInt(util::array_get($jsonPost['sequence'], 0));


$nationID = getDB()->queryFirstField(
    'select `nation` from `general` where no = %i',
    $generalID
);


if($nationID === null){
    returnJson([
        'result'=>false,
        'reason'=>'소속 국가가 없습니다'
    ]);
}

returnJson([
    'result'=>true,
    'private'=>getMessage('private', $nationID, 10, $reqSequence),
    'public'=>getMessage('public', $nationID, 20, $reqSequence),
    'national'=>getMessage('national', $nationID, 30, $reqSequence),
    'diplomacy'=>getMessage('diplomacy', $nationID, 10, $reqSequence)
]);