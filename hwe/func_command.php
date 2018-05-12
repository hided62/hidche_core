<?php
namespace sammo;

function getTurn(array $general, $type, $font=1) {
    //TODO: 왜 'Type' 인가. 그냥 count로 하자.
    $str = [];
    $db = DB::db();
    $connect=$db->get();

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
                $str[$i] = "【{$city['name']}】(으)로 출병";
                break;
            case 17: //소집해제
                $str[$i] = "소집 해제";
                break;

            case 21: //이동
                $double = $command[1];
                $city = getCity($double, "name");
                $str[$i] = "【{$city['name']}】(으)로 이동";
                break;
            case 22: //등용
                $double = $command[1];

                $query = "select name from general where no='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $str[$i] = "【{$general['name']}】(을)를 등용";
                break;
            case 25: //임관
                $double = $command[1];

                $nation = getNationStaticInfo($double);

                if(!$nation['name']) { $nation['name'] = '????'; }

                $str[$i] = "【{$nation['name']}】(으)로 임관";
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
                $str[$i] = "【{$city['name']}】(으)로 강행";
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
                $query = "select makenation from general where no='{$general['no']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $str[$i] = "【{$general['makenation']}】(을)를 건국";
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
                    $str[$i] = "【".getWeapName($double)."】(을)를 구입";
                } elseif($double < 200) {
                    $str[$i] = "【".getBookName($double-100)."】(을)를 구입";
                } elseif($double < 300) {
                    $str[$i] = "【".getHorseName($double-200)."】(을)를 구입";
                } elseif($double < 400) {
                    $str[$i] = "【".getItemName($double-300)."】(을)를 구입";
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

function getCoreTurn($nation, $level) {
    $db = DB::db();
    $connect=$db->get();
    $str = [];
    $turn = [];

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

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게 금 {$double}00을 포상"; }
                else { $str[$i] = "【{$general['name']}】에게 쌀 {$double}00을 포상"; }
                break;
            case 24: //몰수
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게서 금 {$double}00을 몰수"; }
                else { $str[$i] = "【{$general['name']}】에게서 쌀 {$double}00을 몰수"; }
                break;
            case 27: //발령
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);
                $city = getCity($double, "name");

                $str[$i] = "【{$general['name']}】【{$city['name']}】(으)로 발령";
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
                $query = "select makenation from general where level='$level' and nation='{$nation['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $double = (int)$command[1];

                $nation = getNationStaticInfo($double);

                $str[$i] = "【{$nation['name']}】에 【{$general['makenation']}】(으)로 통합 제의";
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
                $str[$i] = "【{$city['name']}】(을)를 초토화";
                break;
            case 66: //천도
                $double = $command[1];
                $city = getCity($double, "name");
                $str[$i] = "【{$city['name']}】(으)로 천도";
                break;
            case 67: //증축
                $double = $command[1];
                $city = getCity($double, "name");
                $str[$i] = "【{$city['name']}】(을)를 증축";
                break;
            case 68: //감축
                $double = $command[1];
                $city = getCity($double, "name");
                $str[$i] = "【{$city['name']}】(을)를 감축";
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
                $str[$i] = "【{$city['name']}】(을)를 수몰";
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


function processCommand($no) {
    $session = Session::getInstance();
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $query = "select npc,no,name,picture,imgsvr,nation,nations,city,troop,injury,leader,leader2,power,power2,intel,intel2,experience,dedication,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,makenation,makelimit,killturn,block,dedlevel,explevel,age,belong,personal,special,special2,term,turn0,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select month,killturn from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    $log = [];

    // 블럭자는 미실행. 삭턴 감소
    if($general['block'] == 2) {
        $date = substr($general['turntime'],11,5);
        $log[] = "<C>●</>{$admin['month']}월:현재 멀티, 또는 비매너로 인한<R>블럭</> 대상자입니다. <1>$date</>";
        pushGenLog($general, $log);

        $query = "update general set recturn='',resturn='BLOCK_2',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($general['block'] == 3) {
        $date = substr($general['turntime'],11,5);
        $log[] = "<C>●</>{$admin['month']}월:현재 악성유저로 분류되어 <R>블럭, 발언권 무효</> 대상자입니다. <1>$date</>";
        pushGenLog($general, $log);

        $query = "update general set recturn='',resturn='BLOCK_3',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        if($general['level'] >= 5 && $general['level'] <= 12) {
            $query = "select l{$general['level']}turn0,l{$general['level']}term from nation where nation='{$general['nation']}'";
            $coreresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $core = MYDB_fetch_array($coreresult);
            $corecommand = DecodeCommand($core["l{$general['level']}turn0"]);
            //연속턴 아닌경우 텀 리셋
            if($core["l{$general['level']}term"]%100 != $corecommand[0]) {
                $query = "update nation set l{$general['level']}term=0 where nation='{$general['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }

            switch($corecommand[0]) {
                case 23: process_23($general); break; //포상
                case 24: process_24($general); break; //몰수
                case 27: process_27($general); break; //발령
                case 51: process_51($general); break; //항복권고
                case 52: process_52($general); break; //원조
                case 53: process_53($general); break; //통합제의
                case 61: process_61($general); break; //불가침제의
                case 62: process_62($general); break; //선전 포고
                case 63: process_63($general); break; //종전 제의
                case 64: process_64($general); break; //파기 제의
                case 65: process_65($general); break; //초토화
                case 66: process_66($general); break; //천도
                case 67: process_67($general); break; //증축
                case 68: process_68($general); break; //감축
                case 71: process_71($general); break; //필사즉생
                case 72: process_72($general); break; //백성동원
                case 73: process_73($general); break; //수몰
                case 74: process_74($general); break; //허보
                case 75: process_75($general); break; //피장파장
                case 76: process_76($general); break; //의병모집
                case 77: process_77($general); break; //이호경식
                case 78: process_78($general); break; //급습
                case 81: process_81($general); break; //국기변경
                case 99: break; //수뇌부휴식
            }

            //장수정보 재로드
            $query = "select npc,no,name,picture,imgsvr,nation,nations,city,troop,injury,leader,leader2,power,power2,intel,intel2,experience,dedication,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,makenation,makelimit,killturn,block,dedlevel,explevel,age,belong,personal,special,special2,term,turn0,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $general = MYDB_fetch_array($result);
        }

        $command = DecodeCommand($general['turn0']);
        //삭턴 처리
        if($general['npc'] >= 2 || $general['killturn'] > $admin['killturn']) {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($command[0] == 0) {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn='{$admin['killturn']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //FIXME: 운영자 같이 사망하면 안되는 인물에 대한 처리가 필요

        //연속턴 아닌경우 텀 리셋
        if($general['term']%100 != $command[0]) {
            $query = "update general set term=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //턴 처리
        switch($command[0]) {
            case 0: //휴식
                $date = substr($general['turntime'],11,5);
                $log[] = "<C>●</>{$admin['month']}월:아무것도 실행하지 않았습니다. <1>$date</>";
                pushGenLog($general, $log);
                break;
            case  1: process_1($general, 1); break; //농업
            case  2: process_1($general, 2); break; //상업
            case  3: process_3($general); break; //기술
            case  4: process_4($general); break; //선정
            case  5: process_5($general, 1); break; //수비
            case  6: process_5($general, 2); break; //성벽
            case  7: process_7($general); break; //정착 장려
            case  8: process_8($general); break; //치안
            case  9: process_9($general); break; //조달

            case 11: process_11($general, 1); break; //징병
            case 12: process_11($general, 2); break; //모병
            case 13: process_13($general); break; //훈련
            case 14: process_14($general); break; //사기진작
            case 15: process_15($general); break; //전투태세
            case 16: process_16($general); break; //전쟁
            case 17: process_17($general); break; //소집해제

            case 21: process_21($general); break; //이동
            case 22: process_22($general); break; //등용 //TODO:등용장 재 디자인
            case 25: process_25($general); break; //임관
            case 26: process_26($general); break; //집합
            case 28: process_28($general); break; //귀환
            case 29: process_29($general); break; //인재탐색
            case 30: process_30($general); break; //강행
            
            case 31: process_31($general); break; //첩보
            case 32: process_32($general); break; //화계
            case 33: process_33($general); break; //탈취
            case 34: process_34($general); break; //파괴
            case 35: process_35($general); break; //선동

            case 41: process_41($general); break; //단련
            case 42: process_42($general); break; //견문
            case 43: process_43($general); break; //증여
            case 44: process_44($general); break; //헌납
            case 45: process_45($general); break; //하야
            case 46: process_46($general); break; //건국
            case 47: process_47($general); break; //방랑
            case 48: process_48($general); break; //장비매매
            case 49: process_49($general); break; //군량매매
            case 50: process_50($general); break; //요양

            case 54: process_54($general); break; //선양
            case 55: process_55($general); break; //거병
            case 56: process_56($general); break; //해산
            case 57: process_57($general); break; //모반 시도
        }
    }
}

function updateCommand($no, $type=0) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select no,nation,level from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($type == 0 || $type == 1) {  // 턴처리후, 당기기
        $query = "
update general set
turn0=turn1,turn1=turn2,turn2=turn3,turn3=turn4,turn4=turn5,turn5=turn6,turn6=turn7,turn7=turn8,turn8=turn9,
turn9=turn10,turn10=turn11,turn11=turn12,turn12=turn13,turn13=turn14,turn14=turn15,turn15=turn16,turn16=turn17,
turn17=turn18,turn18=turn19,turn19=turn20,turn20=turn21,turn21=turn22,turn22=turn23,turn23='00000000000000'
where no='{$general['no']}'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    if($type == 2 || ($general['level'] >= 5 && $general['level'] <= 12 && $type == 0)) {   // 턴 처리후 수뇌부, 수뇌부 당기기
        $turn = "l{$general['level']}turn";
        $query = "
update nation set
{$turn}0={$turn}1,{$turn}1={$turn}2,
{$turn}2={$turn}3,{$turn}3={$turn}4,
{$turn}4={$turn}5,{$turn}5={$turn}6,
{$turn}6={$turn}7,{$turn}7={$turn}8,
{$turn}8={$turn}9,{$turn}9={$turn}10,
{$turn}10={$turn}11,{$turn}11='00000000000099'
where nation='{$general['nation']}'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}


function backupdateCommand($no, $type=0) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select no,nation,level from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($type == 1) {  // 미루기
        $query = "
update general set
turn23=turn22,turn22=turn21,
turn21=turn20,turn20=turn19,
turn19=turn18,turn18=turn17,
turn17=turn16,turn16=turn15,
turn15=turn14,turn14=turn13,
turn13=turn12,turn12=turn11,
turn11=turn10,turn10=turn9,
turn9=turn8,turn8=turn7,
turn7=turn6,turn6=turn5,
turn5=turn4,turn4=turn3,
turn3=turn2,turn2=turn1,
turn1=turn0,turn0='00000000000000'
where no='$no'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($type == 2) {  // 수뇌부 미루기
        $turn = "l{$general['level']}turn";
        $query = "
update nation set
{$turn}11={$turn}10,{$turn}10={$turn}9,
{$turn}9={$turn}8,{$turn}8={$turn}7,
{$turn}7={$turn}6,{$turn}6={$turn}5,
{$turn}5={$turn}4,{$turn}4={$turn}3,
{$turn}3={$turn}2,{$turn}2={$turn}1,
{$turn}1={$turn}0,{$turn}0='00000000000099'
where nation='{$general['nation']}'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}


function command_Single($turn, $command) {
    if(!$turn){
        header('location:commandlist.php');
        return;
    }

    $db = DB::db();
    $userID = Session::getUserID();

    $command = EncodeCommand(0, 0, 0, $command);

    $setValues = [];
    foreach($turn as $turnIdx){
        $setValues["turn{$turnIdx}"] = $command;
    }
    $db->update('general', $setValues, 'owner=%i',$userID);
    
    header('location:commandlist.php');
}

function command_Chief($turn, $command) {
    $db = DB::db();
    $connect=$db->get();
    $userID = Session::getUserID();

    $command = EncodeCommand(0, 0, 0, $command);

    $query = "select nation,level from general where owner='{$userID}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $count = count($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$command}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    header('location:b_chiefcenter.php');
}

function command_Other($turn, $commandtype) {

    $target = "processing.php?commandtype={$commandtype}";
    foreach($turn as $turnItem){
        $target.="&turn[]={$turnItem}";
    }
    $target.="&".mt_rand();
    ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script>
parent.moveProcessing(<?=$commandtype?>, <?=Json::encode($turn)?>);
</script>
</head>
<body style="background-color:black;">

</body>
</html>
<?php

/*
<form name='form1' action='processing.php' method='post' target=_parent>
<?php foreach($turn as $turnItem): ?>
    <input type='hidden' name='turn[]' value='<?=$turnItem?>'>
<?php endforeach; ?>
<input type=hidden name=commandtype value=<?=$commandtype?>>
</form>&nbsp;
<script>*/
}


function EncodeCommand($fourth, $third, $double, $command) {
    $str  = StringUtil::padStringAlignRight((string)$fourth, 4, "0");
    $str .= StringUtil::padStringAlignRight((string)$third,  4, "0");
    $str .= StringUtil::padStringAlignRight((string)$double, 4, "0");
    $str .= StringUtil::padStringAlignRight((string)$command, 2, "0");
    return $str;
}

function DecodeCommand($str) {
    $command = [];
    $command[3] = (int)(substr($str, 0, 4));
    $command[2] = (int)(substr($str, 4, 4));
    $command[1] = (int)(substr($str, 8, 4));
    $command[0] = (int)(substr($str, 12, 2));
    return $command;
}

