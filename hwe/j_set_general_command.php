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
        'nationName', 'optionText', 'itemType'
    ],
    'integer'=>[
        'crewType', 'destGeneralID', 'destCityID', 'destNationID',
        'amount', 'colorType', 'nationType', 'itemCode'
    ],
    'boolean'=>[
        'isGold', 'buyRice',
    ],
    'between'=>[
        ['month', [1, 12]]
    ],
    'min'=>[
        ['year', 0]
    ]
];

$v = new Validator($query);