<?php
namespace sammo;

include('lib.php');
include('func.php');

$session = Session::requireGameLogin([])->setReadOnly();

if(!Session::getInstance()->generalID){
    Json::die([
        "nation"=>[]
    ]);
}

//NOTE: 모든 국가, 모든 장수에 대해서 같은 결과라면 캐싱 가능하지 않을까?

Json::die([
    "nation"=>getMailboxList()
]);