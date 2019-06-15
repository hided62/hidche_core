<?php
namespace sammo;

include "lib.php";
include "func.php";

//로그인 검사
$session = Session::requireLogin();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if($session->isGameLoggedIn()){
    //increaseRefresh("도시일람", 1);
}
else{
    $availableNextCall = $session->availableNextCallGetCityList??'2000-01-01 00:00:00';
    $now = new \DateTimeImmutable();

    if($now <= new \DateTimeImmutable($availableNextCall) && $session->userGrade < 5){
        Json::die([
            'result'=>false,
            'reason'=>"도시 목록은 10초에 한번 갱신 가능합니다.\n다음 시간 : ".$availableNextCall
        ]);
    }
    
    $availableNextCall = $now->add(new \DateInterval('PT10S'))->format('Y-m-d H:i:s');
    $session->availableNextCallGetCityList = $availableNextCall;
}

$nation = getAllNationStaticInfo();
$cityArgsList = ['city', 'nation', 'name', 'level'];
$cities = $db->queryAllLists('SELECT %l FROM city', join(',', $cityArgsList));

Json::die([
    'nations'=>$nation,
    'cityArgsList'=>$cityArgsList,
    'cities'=>$cities
]);