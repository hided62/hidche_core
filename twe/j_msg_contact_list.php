<?php
include('lib.php');
include('func.php');



if(!getGeneralID()){
    Json::die([
        "nation"=>[]
    ]);
}

//NOTE: 모든 국가, 모든 장수에 대해서 같은 결과라면 캐싱 가능하지 않을까?

Json::die([
    "nation"=>getMailboxList()
]);