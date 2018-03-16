<?php
include "lib.php";
include "func.php";

if(!getUserID()){
    returnJson([
        'game'=>'x',
        'me'=>'no'
    ]);
}

//FIXME:Name이 없는 동명의 함수가 있음
function getScenarioName($scenario) {
    switch($scenario) {
    case  0: $str = "공백지모드"; break;
    case  1: $str = "역사모드1 : 184년 황건적의 난"; break;
    case  2: $str = "역사모드2 : 190년 반동탁연합"; break;
    case  3: $str = "역사모드3 : 194년 군웅할거"; break;
    case  4: $str = "역사모드4 : 196년 황제는 허도로"; break;
    case  5: $str = "역사모드5 : 200년 관도대전"; break;
    case  6: $str = "역사모드6 : 202년 원가의 분열"; break;
    case  7: $str = "역사모드7 : 207년 적벽대전"; break;
    case  8: $str = "역사모드8 : 213년 익주 공방전"; break;
    case  9: $str = "역사모드9 : 219년 삼국정립"; break;
    case 10: $str = "역사모드10 : 225년 칠종칠금"; break;
    case 11: $str = "역사모드11 : 228년 출사표"; break;

    case 12: $str = "IF모드1 : 191년 백마장군의 위세"; break;

    case 20: $str = "가상모드1 : 180년 영웅 난무"; break;
    case 21: $str = "가상모드1 : 180년 영웅 집결"; break;
    case 22: $str = "가상모드2 : 179년 훼신 집결"; break;
    case 23: $str = "가상모드3 : 180년 영웅 시대"; break;
    case 24: $str = "가상모드4 : 180년 결사항전"; break;
    case 25: $str = "가상모드5 : 180년 영웅독존"; break;
    case 26: $str = "가상모드6 : 180년 무풍지대"; break;
    case 27: $str = "가상모드7 : 180년 가요대잔치"; break;
    case 28: $str = "가상모드8 : 180년 확산성 밀리언 아서"; break;
    default: $str = "시나리오?"; break;
    }
    return $str;
}

function getTurnTerm($term) {
    switch($term) {
    case 0: $str = "120분 턴 서버"; break;
    case 1: $str = "60분 턴 서버"; break;
    case 2: $str = "30분 턴 서버"; break;
    case 3: $str = "20분 턴 서버"; break;
    case 4: $str = "10분 턴 서버"; break;
    case 5: $str = "5분 턴 서버"; break;
    case 6: $str = "2분 턴 서버"; break;
    case 7: $str = "1분 턴 서버"; break;
    }
    return $str;
}

$db = getDB();

$game = $db->queryFirstRow('SELECT isUnited, npcMode, year, month, scenario, maxgeneral as maxUserCnt, turnTerm from game where `no`=1');

$nationCnt = $db->queryFirstField('SELECT count(`nation`) from nation where `level` > 0');
$genCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` < 2');
$npcCnt = $db->queryFirstField('SELECT count(`no`) from general where `npc` >= 2');

$game['turnTerm'] = getTurnTerm($game['turnTerm']);
$game['scenario'] = getScenarioName($game['scenario']);
$game['userCnt'] = $genCnt;
$game['npcCnt'] = $npcCnt;

$generalID = getGeneralID(false, false);
$userGrade = getUserGrade();
$me = [
];

if($generalID){
    $general = queryFirstRow('SELECT name, picture, imgsvr from general where no=%i', $generalID);
    if($general){
        $me['name'] = $general['name'];

        if($me['imgsvr'] == 0) {
            $me['picture'] = IMAGE.W.$me['PICTURE'];
        } else {
            $me['picture'] = '../d_pic/'.$me['PICTURE'];
        }
    }
}

//TODO: 이를 표현하는 방법은 '이전 버전'의 serverListPost.php를 참고할 것.
returnJson([
    'game'=>$game,
    'me'=>$me
]);