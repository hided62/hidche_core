<?php
namespace sammo;

function getGeneralTurnBrief(General $generalObj, array $turnList) {
    $result = [];

    foreach($turnList as $turnIdx => [$action, $arg]){
        $commandObj = buildGeneralCommandClass($action, $generalObj, [], $arg);
        $turnText = $commandObj->getBrief();
        $result[$turnIdx] = $turnText;
    }
    return $result;

    //TODO: 정리가 끝나면 삭제
    $turn = [];
    $turn[0] = $general["turn0"];

    if($type >= 1) {
        $turn[1] = $general["turn1"];
        $turn[2] = $general["turn2"];
        $turn[3] = $general["turn3"];
        $turn[4] = $general["turn4"];
    }
    if($type >= 2) {
        $turn[5] = $general["turn5"];
        $turn[6] = $general["turn6"];
        $turn[7] = $general["turn7"];
        $turn[8] = $general["turn8"];
        $turn[9] = $general["turn9"];
        $turn[10] = $general["turn10"];
        $turn[11] = $general["turn11"];
        $turn[12] = $general["turn12"];
        $turn[13] = $general["turn13"];
        $turn[14] = $general["turn14"];
        $turn[15] = $general["turn15"];
        $turn[16] = $general["turn16"];
        $turn[17] = $general["turn17"];
        $turn[18] = $general["turn18"];
        $turn[19] = $general["turn19"];
        $turn[20] = $general["turn20"];
        $turn[21] = $general["turn21"];
        $turn[22] = $general["turn22"];
        $turn[23] = $general["turn23"];
    }

    if($type == 0) { $count = 1; }
    elseif($type == 1) { $count = 5; }
    elseif($type == 2) { $count = 24; }

    for($i=0; $i < $count; $i++) {
        $command = DecodeCommand($turn[$i]);

        switch($command[0]) {
            case 0:  $str[$i] = "휴식"; break; //휴식
            case 1:  $str[$i] = "농지 개간"; break; //농업
            case 2:  $str[$i] = "상업 투자"; break; //상업
            case 3:  $str[$i] = "기술 연구"; break; //기술
            case 4:  $str[$i] = "주민 선정"; break; //선정
            case 5:  $str[$i] = "수비 강화"; break; //수비
            case 6:  $str[$i] = "성벽 보수"; break; //성벽
            case 7:  $str[$i] = "정착 장려"; break; //정착 장려
            case 8:  $str[$i] = "치안 강화"; break; //치안
            case 9:  $str[$i] = "물자 조달"; break; //조달

            case 11: //징병
                $third = GameUnitConst::byID($command[2])->name;
                $double = $command[1];
                $str[$i] = "【{$third}】 {$double}00명 징병";
                break;
            case 12: //모병
                $third = GameUnitConst::byID($command[2])->name;
                $double = $command[1];
                $str[$i] = "【{$third}】 {$double}00명 모병";
                break;
            case 13: //훈련
                $str[$i] = "훈련";
                break;
            case 14: //사기진작
                $str[$i] = "사기진작";
                break;
            case 15: //전투태세
                $str[$i] = "전투태세";
                break;
            case 16: //전쟁
                $double = $command[1];
                $city = getCity($double, "name");
                $josaRo = JosaUtil::pick($city['name'], '로');
                $str[$i] = "【{$city['name']}】{$josaRo} 출병";
                break;
            case 17: //소집해제
                $str[$i] = "소집 해제";
                break;

            case 21: //이동
                $double = $command[1];
                $city = getCity($double, "name");
                $josaRo = JosaUtil::pick($city['name'], '로');
                $str[$i] = "【{$city['name']}】{$josaRo} 이동";
                break;
            case 22: //등용
                $double = $command[1];

                $query = "select name from general where no='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $josaUl = JosaUtil::pick($general['name'], '을');
                $str[$i] = "【{$general['name']}】{$josaUl} 등용";
                break;
            case 25: //임관
                $double = $command[1];

                $nation = getNationStaticInfo($double);

                if(!$nation['name']) { $nation['name'] = '????'; }

                $josaRo = JosaUtil::pick($nation['name'], '로');
                $str[$i] = "【{$nation['name']}】{$josaRo} 임관";
                break;
            case 26: //집합
                $str[$i] = "집합";
                break;
            case 28: //귀환
                $str[$i] = "담당 도시로 귀환";
                break;
            case 29: //인재탐색
                $str[$i] = "인재 탐색";
                break;
            case 30: //강행
                $double = $command[1];
                $city = getCity($double, "name");
                $josaRo = JosaUtil::pick($city['name'], '로');
                $str[$i] = "【{$city['name']}】{$josaRo} 강행";
                break;
                
            case 31: //첩보
                $double = $command[1];
                $city= getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 첩보 실행";
                break;
            case 32: //화계
                $double = $command[1];
                $city= getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 화계 실행";
                break;
            case 33: //탈취
                $double = $command[1];
                $city= getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 탈취 실행";
                break;
            case 34: //파괴
                $double = $command[1];
                $city= getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 파괴 실행";
                break;
            case 35: //선동
                $double = $command[1];
                $city= getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 선동 실행";
                break;

            case 41: //단련
                $str[$i] = "숙련도를 단련";
                break;
            case 42: //견문
                $str[$i] = "견문";
                break;
            case 43: //증여
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게 금 {$double}00을 증여"; }
                else { $str[$i] = "【{$general['name']}】에게 쌀 {$double}00을 증여"; }
                break;
            case 44: //헌납
                $third = $command[2];
                $double = $command[1];

                if($third == 1) { $str[$i] = "금 {$double}00을 헌납"; }
                else { $str[$i] = "쌀 {$double}00을 헌납"; }
                break;
            case 45: //하야
                $str[$i] = "하야";
                break;
            case 46: //건국
                $nationName = $db->queryFirstField('SELECT makenation FROM general WHERE `no`=%i', $general['no'])??'';

                $josaUl = JosaUtil::pick($nationName, '을');
                $str[$i] = "【{$nationName}】{$josaUl} 건국";
                break;
            case 47: //방랑
                $str[$i] = "방랑";
                break;
            case 48: //장비 구입
                $double = $command[1];
                if($double == 0) {
                    $str[$i] = "무기를 판매";
                } elseif($double == 100) {
                    $str[$i] = "서적을 판매";
                } elseif($double == 200) {
                    $str[$i] = "명마를 판매";
                } elseif($double == 300) {
                    $str[$i] = "도구를 판매";
                } elseif($double < 100) {
                    $josaUl = JosaUtil::pick(getWeapName($double), '을');
                    $str[$i] = "【".getWeapName($double)."】{$josaUl} 구입";
                } elseif($double < 200) {
                    $josaUl = JosaUtil::pick(getBookName($double-100), '을');
                    $str[$i] = "【".getBookName($double-100)."】{$josaUl} 구입";
                } elseif($double < 300) {
                    $josaUl = JosaUtil::pick(getHorseName($double-200), '을');
                    $str[$i] = "【".getHorseName($double-200)."】{$josaUl} 구입";
                } elseif($double < 400) {
                    $josaUl = JosaUtil::pick(getItemName($double-300), '을');
                    $str[$i] = "【".getItemName($double-300)."】{$josaUl} 구입";
                }
                break;
            case 49: //군량 매매
                $third = $command[2];
                $double = $command[1];

                if($third == 1) { $str[$i] = "군량 {$double}00을 판매"; }
                else { $str[$i] = "군량 {$double}00을 구입"; }
                break;
            case 50: //요양
                $str[$i] = "요양";
                break;

            case 54: //선양
                $double = $command[1];

                $query = "select name from general where no='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $str[$i] = "【{$general['name']}】에게 선양";
                break;
            case 55: //거병
                $str[$i] = "방랑군 결성";
                break;
            case 56: //해산
                $str[$i] = "방랑군 해산";
                break;
            case 57: //모반 시도
                $str[$i] = "모반 시도";
                break;

            default:
                $str[$i] = "????";
                break;
        }
    }

    if($font == 1) {
        for($i=0; $i < count($str); $i++) {
            $str[$i] = getFont($str[$i]);
        }
    }
    return $str;
}

function getNationTurnBrief(General $generalObj, array $turnList) {
    $result = [];

    $tmpTurn = new LastTurn();
    foreach($turnList as $turnIdx => [$action, $arg]){
        $commandObj = buildNationCommandClass($action, $generalObj, [], $tmpTurn, $arg);
        $turnText = $commandObj->getBrief();
        $result[$turnIdx] = $turnText;
    }
    return $result;

    $turn[0] = $nation["l{$level}turn0"];
    $turn[1] = $nation["l{$level}turn1"];
    $turn[2] = $nation["l{$level}turn2"];
    $turn[3] = $nation["l{$level}turn3"];
    $turn[4] = $nation["l{$level}turn4"];
    $turn[5] = $nation["l{$level}turn5"];
    $turn[6] = $nation["l{$level}turn6"];
    $turn[7] = $nation["l{$level}turn7"];
    $turn[8] = $nation["l{$level}turn8"];
    $turn[9] = $nation["l{$level}turn9"];
    $turn[10] = $nation["l{$level}turn10"];
    $turn[11] = $nation["l{$level}turn11"];

    $count = 12;
    for($i=0; $i < $count; $i++) {
        $command = DecodeCommand($turn[$i]);

        switch($command[0]) {
            case 99: //휴식
                $str[$i] = "휴식";
                break;
            case 23: //포상
                $fourth = $command[3];
                $third = $command[2];
                $double = (int)$command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게 금 {$double}00 포상"; }
                else { $str[$i] = "【{$general['name']}】에게 쌀 {$double}00 포상"; }
                break;
            case 24: //몰수
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】의 금 {$double}00 몰수"; }
                else { $str[$i] = "【{$general['name']}】의 쌀 {$double}00 몰수"; }
                break;
            case 27: //발령
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);
                $city = getCity($double, "name");
                $josaRo = JosaUtil::pick($city['name'], '로');
                $str[$i] = "【{$general['name']}】【{$city['name']}】{$josaRo} 발령";
                break;
            case 51: //항복권고
                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에게 항복 권고";
                break;
            case 52: //원조
                $fourth = $command[3];
                $third = $command[2];
                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);

                $fourth *= 1000;
                $third *= 1000;
                $str[$i] = "【{$nation['name']}】에게 국고 {$third} 병량 {$fourth} 원조";
                break;
            case 53: //통합제의
                $nationName = $db->queryFirstField('SELECT makenation FROM general WHERE `level`=%i AND `nation`=%i', $level, $nation['nation'])??'';

                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);
                $josaRo = JosaUtil::pick($nationName, '로');
                $str[$i] = "【{$nation['name']}】에 【{$nationName}】{$josaRo} 통합 제의";
                break;
            case 61: //불가침제의
                $third = $command[2];
                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 {$third}년 불가침 제의";
                break;
            case 62: //선전 포고
                $double = $command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 선전 포고";
                break;
            case 63: //종전 제의
                $double = $command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 종전 제의";
                break;
            case 64: //파기 제의
                $double = $command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 파기 제의";
                break;
            case 65: //초토
                $double = $command[1];
                $city = getCity($double, "name");
                $josaUl = JosaUtil::pick($city['name'], '을');
                $str[$i] = "【{$city['name']}】{$josaUl} 초토화";
                break;
            case 66: //천도
                $double = $command[1];
                $city = getCity($double, "name");
                $josaRo = JosaUtil::pick($city['name'], '로');
                $str[$i] = "【{$city['name']}】{$josaRo} 천도";
                break;
            case 67: //증축
                $double = $command[1];
                $city = getCity($double, "name");
                $josaUl = JosaUtil::pick($city['name'], '을');
                $str[$i] = "【{$city['name']}】{$josaUl} 증축";
                break;
            case 68: //감축
                $double = $command[1];
                $city = getCity($double, "name");
                $josaUl = JosaUtil::pick($city['name'], '을');
                $str[$i] = "【{$city['name']}】{$josaUl} 감축";
                break;
            case 71: //필사즉생
                $str[$i] = "필사즉생";
                break;
            case 72: //백성동원
                $double = $command[1];
                $city = getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 백성동원";
                break;
            case 73: //수몰
                $double = $command[1];
                $city = getCity($double, "name");
                $josaUl = JosaUtil::pick($city['name'], '을');
                $str[$i] = "【{$city['name']}】{$josaUl} 수몰";
                break;
            case 74: //허보
                $double = $command[1];
                $city = getCity($double, "name");
                $str[$i] = "【{$city['name']}】에 허보";
                break;
            case 75: //피장파장
                $double = $command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 피장파장";
                break;
            case 76: //의병모집
                $str[$i] = "의병모집";
                break;
            case 77: //이호경식
                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 이호경식";
                break;
            case 78: //급습
                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 급습";
                break;
            case 81: //국기변경
                $double = $command[1];
                $colors = GetNationColors();
                $color = $colors[$double];

                $str[$i] = "【<font color={$color}>국기</font>】를 변경";
                break;
            default:
                $str[$i] = "????";
                break;
        }
    }

    for($i=0; $i < count($str); $i++) {
        $str[$i] = getFont($str[$i]);
    }

    return $str;
}

function pushGeneralCommand(int $generalID, int $turnCnt=1){
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pullGeneralCommand($generalID, -$turnCnt);   
    }
    if($turnCnt >= GameConst::$maxTurn){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'general_id=%i', $generalID);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'general_id=%i AND turn_idx >= %i', $generalID, GameConst::$maxTurn);
}

function pullGeneralCommand(int $generalID, int $turnCnt=1){
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pushGeneralCommand($generalID, -$turnCnt);
    }
    if($turnCnt >= GameConst::$maxTurn){
        return;
    }
    
    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', GameConst::$maxTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'general_id=%i AND turn_idx < %i', $generalID, $turnCnt);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'general_id=%i', $generalID);
}

function pushNationCommand(int $nationID, int $level, int $turnCnt=1){
    if($nationID == 0){
        return;
    }
    if($level < 5){
        return;
    }
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pullNationCommand($nationID, $level, -$turnCnt);   
    }
    if($turnCnt >= GameConst::$maxNationTurn){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', $turnCnt)
    ], 'nation_id=%i AND level=%i', $nationID, $level);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', GameConst::$maxNationTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'nation_id=%i AND level=%i AND turn_idx >= %i', $nationID, $level, GameConst::$maxNationTurn);
}

function pullNationCommand(int $nationID, int $level, int $turnCnt=1){
    if($nationID == 0){
        return;
    }
    if($level < 5){
        return;
    }
    if($turnCnt == 0){
        return;
    }
    if($turnCnt < 0){
        pushNationCommand($nationID, $level, -$turnCnt);
    }
    if($turnCnt >= GameConst::$maxNationTurn){
        return;
    }
    
    $db = DB::db();

    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx + %i', GameConst::$maxNationTurn),
        'action'=>'휴식',
        'arg'=>'{}'
    ], 'nation_id=%i AND level=%i AND turn_idx < %i', $nationID, $level, $turnCnt);
    $db->update('general_turn', [
        'turn_idx'=>$db->sqleval('turn_idx - %i', $turnCnt)
    ], 'nation_id=%i AND level=%i', $nationID, $level);
}

function setGeneralCommand(int $generalID, array $turnList, string $command, ?array $arg = null) {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $db->update('general_turn', [
        'action'=>$command,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT)
    ], 'general_id = %i AND turn_idx IN %li', $generalID, $turnList);
}

function setNationCommand(int $nationID, int $level, array $turnList, string $command, ?array $arg = null) {
    if(!$turnList){
        return;
    }

    $db = DB::db();

    $db->update('nation_turn', [
        'action'=>$command,
        'arg'=>Json::encode($arg, JSON::EMPTY_ARRAY_IS_DICT)
    ], 'nation_id = %i AND level = %i AND turn_idx IN %li', $generalID, $level, $turnList);
}
