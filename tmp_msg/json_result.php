<?php
require('../twe/lib.php');

/*
원래대로라면 json을 돌려주는게 맞지만
refresh를 반영해야하므로 php로 중계해서 반환함! 
*/

function relayJson($filepath, $noCache = true, $die = true){
    //NOTE: $filepath 경로 주의

    header('Content-Type: application/json');

    if($noCache){
        header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
    }

    $json = json_decode(file_get_contents($filepath));
    echo json_encode($json,JSON_UNESCAPED_UNICODE);

    if($die){
        die();
    }
}

$jsonPost = parseJsonPost();

$reqSequence = toInt(util::array_get($jsonPost['sequence'], 0), true);

if($reqSequence === null){
    $reqSequence = 0;
}

//서버가 돌아가는게 아니니까 흉내만 내자.
switch($reqSequence){
    case 0:
        relayJson('fresh_result.json');
        break;
    case 1122:
        relayJson('update_private_result.json');
        break;
    case 1366:
        relayJson('update_national_result.json');
        break;
    case 1811:
        relayJson('update_public_result.json');
        break;
}

//???
relayJson('fresh_result.json');