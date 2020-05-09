<?php
namespace sammo;

include "lib.php";
include "func.php";


//로그인 검사
$session = Session::requireLogin();
$userID = Session::getUserID();

$db = DB::db();
$withToken = Util::getPost('with_token', 'bool', false);
$gameStor = KVStorage::getStorage($db, 'game_env');

if($session->isGameLoggedIn()){
    increaseRefresh("장수일람", 2);

    $me = $db->queryFirstRow('SELECT con, turntime FROM general WHERE owner=%i', $userID);
    $con = checkLimit($me['con']);
    if ($con >= 2) {
        Json::die([
            'result'=>false,
            'reason'=>'접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다.'
        ]);
    }
}
else{
    $availableNextCall = $session->availableNextCallGetGeneralList??'2000-01-01 00:00:00';
    $now = new \DateTimeImmutable();

    if($now <= new \DateTimeImmutable($availableNextCall) && $session->userGrade < 5){
        Json::die([
            'result'=>false,
            'reason'=>"장수 리스트는 10초에 한번 갱신 가능합니다.\n다음 시간 : ".$availableNextCall
        ]);
    }
    
    $availableNextCall = $now->add(new \DateInterval('PT10S'))->format('Y-m-d H:i:s');
    $session->availableNextCallGetGeneralList = $availableNextCall;
}

$session->setReadOnly();

$rawGeneralList = $db->queryAllLists('SELECT owner,no,picture,imgsvr,npc,age,nation,special,special2,personal,name,owner_name as ownerName,injury,leadership,strength,intel,experience,dedication,officer_level,killturn,connect from general');

$ownerNameList = [];
if($gameStor->isunited){
    foreach(RootDB::db()->queryAllLists('SELECT no, name FROM member') as [$ownerID, $ownerName]){
        $ownerNameList[$ownerID] = $ownerName;
    }
}

$generalList = [];
foreach($rawGeneralList as $rawGeneral){
    [$owner,$no,$picture,$imgsvr,$npc,$age,$nation,$special,$special2,$personal,$name,$ownerName,$injury,$leadership,$strength,$intel,$experience,$dedication,$officerLevel,$killturn,$connectCnt] = $rawGeneral;

    if(key_exists($owner, $ownerNameList)){
        $ownerName = $ownerNameList[$owner];
    }

    $nationArr = getNationStaticInfo($nation);
    $lbonus = calcLeadershipBonus($officerLevel, $nationArr['level']);

    $generalList[] = [
        $no,
        $picture,
        $imgsvr,
        $npc,
        $age,
        $nationArr['name'],
        getGeneralSpecialDomesticName($special),
        getGeneralSpecialWarName($special2),
        getGenChar($personal),
        $name,
        $npc==1?$ownerName:null,
        $injury,
        $leadership,
        $lbonus,
        $strength,
        $intel,
        getExpLevel($experience),
        getHonor($experience),
        getDed($dedication),
        getOfficerLevelText($officerLevel, $nationArr['level']),
        $killturn,
        $connectCnt
    ];
}

$result = [
    'result'=>'true',
    'list'=>$generalList,
];

if($withToken){
    $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
    $tokens = [];
    foreach($db->query('SELECT * FROM select_npc_token WHERE `valid_until`>=%s', $now) as $token){
        $validUntil = $token['valid_until'];

        foreach(Json::decode($token['pick_result']) as $pickResult){
            $tokens[$pickResult['no']]=$pickResult['keepCnt'];
        }
    }
    $result['token'] = $tokens;
}

Json::die($result);
