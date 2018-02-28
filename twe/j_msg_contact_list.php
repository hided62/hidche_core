<?php
include('lib.php');
include('func.php');

use utilphp\util as util;

if(!getGeneralID()){
    returnJson([
        "nation"=>[]
    ]);
}

//NOTE: 모든 국가, 모든 장수에 대해서 같은 결과라면 캐싱 가능하지 않을까?

returnJson([
    "nation"=>getMailboxList()
]);