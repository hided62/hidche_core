<?php
namespace sammo;

include "lib.php";
include "func.php";

WebUtil::requireAJAX();

$action = Util::getPost('action');
$officerLevel = Util::getPost('officerLevel', 'int');
$destGeneralID = Util::getPost('destGeneralID', 'int');
$destCityID = Util::getPost('destCityID', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gameStor->cacheValues(['startyear','year','month','scenario']);

$me = $db->queryFirstRow('SELECT no,nation,officer_level from general where owner=%i', $userID);
$myOfficerLevel = $me['officer_level'];
$nationID = $me['nation'];

//수뇌가 아니면 아무것도 할 수 없음
if($myOfficerLevel < 5){
    Json::die([
        'result'=>false,
        'reason'=>'수뇌가 아닙니다.'
    ]);
}

if($action === '추방' && $destGeneralID==0){
    Json::die([
        'result'=>false,
        'reason'=>'장수가 지정되지 않았습니다.'
    ]);
}

if($destGeneralID==0){
    $general = new DummyGeneral(true);
}
else{
    $general = General::createGeneralObjFromDB($destGeneralID, [
        'name', 'leadership', 'strength', 'intel', 'gold','rice',
        'troop','officer_level','npc','picture','imgsvr',
        'permission','penalty','belong', 'crewtype',
        'experience', 'dedication', 'betray', 'dedlevel', 'explevel', 'makelimit',
    ], 1);

    if($general instanceof DummyGeneral){
        Json::die([
            'result'=>false,
            'reason'=>'올바르지 않은 장수입니다.'
        ]);
    }

    if($nationID != $general->getNationID()){
        Json::die([
            'result'=>false,
            'reason'=>'아국 장수가 아닙니다'
        ]);
    }
}

if($officerLevel == 12){
    Json::die([
        'result'=>false,
        'reason'=>'군주를 대상으로 할 수 없습니다.'
    ]);
}

function do수뇌임명(General $general, int $targetOfficerLevel):?string{
    global $myOfficerLevel;
    $generalID = $general->getID();
    $officerLevel = $general->getVar('officer_level');
    $generalName = $general->getVar('name');
    $nationID = $general->getNationID();

    $db = DB::db();

    [$levelSet, $nationLevel] = $db->queryFirstList('SELECT %b,level FROM nation WHERE nation = %i', "l{$targetOfficerLevel}set", $nationID);

	// 임명가능 레벨
    $lv = getNationChiefLevel($nationLevel);

    if($targetOfficerLevel < $lv){
        return '임명불가능한 관직입니다.';
    }

    if($levelSet){
        return '지금은 임명할 수 없습니다.';
    }

    //기존 장수 일반으로
    $db->update('general', [
        'officer_level'=>1,
        'officer_city'=>0,
    ], 'nation=%i AND officer_level=%i', $nationID, $targetOfficerLevel);

    if($targetOfficerLevel === 11){

    }
    else if($targetOfficerLevel % 2 == 0){
        if($general->getVar('strength') < GameConst::$chiefStatMin){
            return '무력이 부족합니다.';
        }
        
    }
    else{
        if($general->getVar('intel') < GameConst::$chiefStatMin){
            return '지력이 부족합니다.';
        }
    }

    //신임 장수
    $general->setVar('officer_level', $targetOfficerLevel);
    $general->setVar('officer_city', 0);
    $db->update('nation', [
        "l{$targetOfficerLevel}set"=>1,
    ], 'nation=%i', $nationID);
    $general->applyDB($db);

    return null;
}

function do도시임명(General $general, int $cityID, int $targetOfficerLevel):?string{
    $nationID = $general->getNationID();

    $genlvset = 'officer'.$targetOfficerLevel.'set';

    $db = DB::db();

    if(CityConst::byID($cityID) === null){
        return '올바르지 않은 도시입니다';
    }

    //기존 장수 일반으로
    $db->update('general', [
        'officer_level'=>1,
        'officer_city'=>0,
    ], 'officer_level=%i AND officer_city=%i', $targetOfficerLevel, $cityID);

    if($general instanceof DummyGeneral){
        return null;
    }

    if($targetOfficerLevel === 4 && $general->getVar('strength') < GameConst::$chiefStatMin){
        return '무력이 부족합니다.';
    }

    if($targetOfficerLevel === 3 && $general->getVar('intel') < GameConst::$chiefStatMin){
        return '지력이 부족합니다.';
    }

    //신임 장수
    $db->update('city', [
        $genlvset=>1
    ], 'city=%i AND nation=%i', $cityID, $nationID);
    $general->setVar('officer_level', $targetOfficerLevel);
    $general->setVar('officer_city', $cityID);
    $general->applyDB($db);

    return null;
}

function do추방(General $general, int $myOfficerLevel):?string{
    $generalID = $general->getID();
    $officerLevel = $general->getVar('officer_level');
    $generalName = $general->getVar('name');
    $nationID = $general->getNationID();

    

    //추방할사람이 외교권자이면 불가
    $permission = checkSecretPermission($general->getRaw());
    if($permission == 4){
        return '외교권자는 추방할 수 없습니다.';
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $env = $gameStor->getValues(['startyear','year','month','scenario']);

    $nation = $db->queryFirstRow('SELECT name,%b,color FROM nation WHERE nation=%i',"l{$myOfficerLevel}set", $nationID);
    $nationName = $nation['name'];

    $logger = $general->getLogger();

    //이미 지정했다면 무시
    if($nation["l{$myOfficerLevel}set"] == 1 || $officerLevel == 0 && $officerLevel == 12) {
        header('location:b_myBossInfo.php', true, 303);
        die();
    }
    $dipcount1 = $db->queryFirstField('SELECT count(no) FROM diplomacy WHERE me=%i AND state>=3 AND state<=4', $nationID);
    $dipcount2 = $db->queryFirstField('SELECT count(no) FROM diplomacy WHERE me=%i AND state>=5 AND state<=6', $nationID);

    $gold = 0;
    $rice = 0;
    // 금쌀1000이상은 남김
    if($general->getVar('gold') > GameConst::$defaultGold) {
        $gold = $general->getVar('gold') - GameConst::$defaultGold;
        $general->setVar('gold', GameConst::$defaultGold);
    }
    if($general->getVar('rice') > GameConst::$defaultRice) {
        $rice = $general->getVar('rice') - GameConst::$defaultRice;
        $general->setVar('rice', GameConst::$defaultRice);
    }

    $general->setVar('nation', 0);
    $general->setVar('officer_level', 0);
    $general->setVar('officer_city', 0);
    $general->setVar('belong', 0);
    $oldMakeLimit = $general->getVar('makelimit');
    $general->setVar('makelimit', 12);
    $general->setVar('permission', 'normal');

    if($dipcount1 > 0) {
        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalActionLog("통합에 반대하던 <Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>에서 <R>숙청</>당했습니다.");
        $logger->pushGeneralActionLog("통합에 반대하다가 <D><b>{$nationName}</b></>에서 <R>숙청</>당했습니다.", ActionLogger::PLAIN);
    } elseif($dipcount2 > 0) {
        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalActionLog("합병에 반대하던 <Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>에서 <R>숙청</>당했습니다.");
        $logger->pushGeneralActionLog("합병에 반대하다가 <D><b>{$nationName}</b></>에서 <R>숙청</>당했습니다.", ActionLogger::PLAIN);
    } else {
        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>에서 <R>추방</>당했습니다.");
        $logger->pushGeneralActionLog("<D><b>{$nationName}</b></>에서 <R>추방</>당했습니다.", ActionLogger::PLAIN);

        // 명성/공헌 N*10%감소
        if($env['year'] <= $env['startyear'] && $general->getVar('npc') < 2){
            $general->setVar('makelimit', $oldMakeLimit);
        }
        else{
            $betrayCnt = $general->getVar('betray');
            $general->addExperience(-$general->getVar('experience')*0.1*$betrayCnt);
            $general->addDedication(-$general->getVar('dedication')*0.1*$betrayCnt);
            $general->increaseVarWithLimit('betray', 1, null, GameConst::$maxBetrayCnt);
        }
    }

    //부대장일 경우
    if($general->getVar('troop') == $generalID){
        // 모두 탈퇴
        $db->update('general', [
            'troop'=>0,
        ], 'troop = %i', $generalID);
        $db->delete('troop', 'troop_leader=%i', $generalID);
    }
    $general->setVar('troop', 0);

    if($general->getVar('npc') >= 2 && ($env['scenario'] < 100 || Util::randBool(0.01))) {

        $str = Util::choiceRandom([
            '날 버리다니... 곧 전장에서 복수해주겠다...',
            '추방이라... 내가 무얼 잘못했단 말인가...',
            '어디 추방해가면서 잘되나 보자... 꼭 복수하겠다...',
            '인덕이 제일이거늘... 추방이 웬말인가... 저주한다!',
            '날 추방했으니 그 복수로 적국에 정보를 팔아 넘겨야겠군요. 그럼 이만.'
        ]);

        $src = new MessageTarget(
            $generalID, 
            $generalName,
            $nationID,
            $nation['name'],
            $nation['color'],
            GetImageURL($general->getVar('imgsvr'), $general->getVar('picture'))
        );
        $msg = new Message(
            Message::MSGTYPE_PUBLIC, 
            $src,
            $src,
            $str,
            new \DateTime(),
            new \DateTime('9999-12-31'),
            []
        );
        $msg->send();
    }

    if($env['year'] < $env['startyear']+3) {
        //초반엔 군주 부상 증가(엔장 임관지양)
        $db->update('general', [
            'injury'=>$db->sqleval('least(injury + 1, %i)', 80),
        ], 'nation=%i AND officer_level=12', $nationID);

        $db->update('nation', [
            'gennum'=>$db->sqleval('gennum - %i', $general->getVar('npc')!=5?1:0),
            'gold'=>$db->sqleval('gold + %i', $gold),
            'rice'=>$db->sqleval('rice + %i', $rice),
        ], 'nation = %i', $nationID);
    } else {
        //이번분기는 추방불가(초반 제외)
        $db->update('nation', [
            "l{$myOfficerLevel}set"=>1,
            'gennum'=>$db->sqleval('gennum - %i', $general->getVar('npc')!=5?1:0),
            'gold'=>$db->sqleval('gold + %i', $gold),
            'rice'=>$db->sqleval('rice + %i', $rice),
        ], 'nation = %i', $nationID);
    }

    $logger->pushGeneralHistoryLog("<D>{$nation['name']}</>에서 추방됨");
    $general->applyDB($db);

    return null;
}



if($action == "임명") {
    if(2 <= $officerLevel && $officerLevel <= 4){
        if(!$destCityID){
            Json::die([
                'result'=>false,
                'reason'=>'도시가 지정되지 않았습니다.'
            ]);
        }
        $result = do도시임명($general, $destCityID, $officerLevel);
        if($result !== null){
            Json::die([
                'result'=>false,
                'reason'=>$result
            ]);
        }
        Json::die([
            'result'=>true,
            'reason'=>'success'
        ]);
    }

    if(5 <= $officerLevel && $officerLevel < 12){
        $result = do수뇌임명($general, $officerLevel);
        if($result !== null){
            Json::die([
                'result'=>false,
                'reason'=>$result
            ]);
        }
        Json::die([
            'result'=>true,
            'reason'=>'success'
        ]);
    }

    Json::die([
        'result'=>false,
        'reason'=>'올바르지 않은 지정입니다.'
    ]);
}

if($action == "추방") {
    $result = do추방($general, $myOfficerLevel);
    if($result !== null){
        Json::die([
            'result'=>false,
            'reason'=>$result
        ]);
    }
    Json::die([
        'result'=>true,
        'reason'=>'success'
    ]);
}

Json::die([
    'result'=>false,
    'reason'=>'올바르지 않은 명령입니다.'
]);