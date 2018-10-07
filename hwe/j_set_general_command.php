<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin([])->setReadOnly();

$generalID = $session->generalID;

$action = Util::getReq('action', 'string');
$arg = Json::decode(Util::getReq('arg', 'string'));

if(!$action){
    Json::die([
        'result'=>false,
        'reason'=>'action이 입력되지 않았습니다.'
    ]);
}

$defaultCheck = [
    'string'=>[
        'crewType', 'nationName', 'optionText', 'itemType'
    ],
    'integer'=>[
        'destGeneralID', 'destCityID', 'destNationID',
        'amountMoney', 'amountCrew', 'colorType', 'nationType'
    ],
    'boolean'=>[
        'isGold'
    ],
    'between'=>[
        ['month', [1, 12]]
    ],
    'min'=>[
        ['year', 0]
    ]
];

$v = new Validator($query);