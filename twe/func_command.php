<?php


function processCommand($connect, $no) {
    $query = "select npc,no,name,userlevel,picture,imgsvr,nation,nations,city,troop,injury,leader,leader2,power,power2,intel,intel2,experience,dedication,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,makenation,makelimit,killturn,block,dedlevel,explevel,age,belong,personal,special,special2,term,turn0,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select month,killturn from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    $log = array();

    // 블럭자는 미실행. 삭턴 감소
    if($general['block'] == 2) {
        $date = substr($general['turntime'],11,5);
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 멀티, 또는 비매너로 인한<R>블럭</> 대상자입니다. <1>$date</>";
        pushGenLog($general, $log);

        $query = "update general set recturn='',resturn='BLOCK_2',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($general['block'] == 3) {
        $date = substr($general['turntime'],11,5);
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 악성유저로 분류되어 <R>블럭, 발언권 무효</> 대상자입니다. <1>$date</>";
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
                case 23: process_23($connect, $general); break; //포상
                case 24: process_24($connect, $general); break; //몰수
                case 27: process_27($connect, $general); break; //발령
                case 51: process_51($connect, $general); break; //항복권고
                case 52: process_52($connect, $general); break; //원조
                case 53: process_53($connect, $general); break; //통합제의
                case 61: process_61($connect, $general); break; //불가침제의
                case 62: process_62($connect, $general); break; //선전 포고
                case 63: process_63($connect, $general); break; //종전 제의
                case 64: process_64($connect, $general); break; //파기 제의
                case 65: process_65($connect, $general); break; //초토화
                case 66: process_66($connect, $general); break; //천도
                case 67: process_67($connect, $general); break; //증축
                case 68: process_68($connect, $general); break; //감축
                case 71: process_71($connect, $general); break; //필사즉생
                case 72: process_72($connect, $general); break; //백성동원
                case 73: process_73($connect, $general); break; //수몰
                case 74: process_74($connect, $general); break; //허보
                case 75: process_75($connect, $general); break; //피장파장
                case 76: process_76($connect, $general); break; //의병모집
                case 77: process_77($connect, $general); break; //이호경식
                case 78: process_78($connect, $general); break; //급습
                case 81: process_81($connect, $general); break; //국기변경
                case 99: break; //수뇌부휴식
            }

            //장수정보 재로드
            $query = "select npc,no,name,userlevel,picture,imgsvr,nation,nations,city,troop,injury,leader,leader2,power,power2,intel,intel2,experience,dedication,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,makenation,makelimit,killturn,block,dedlevel,explevel,age,belong,personal,special,special2,term,turn0,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $general = MYDB_fetch_array($result);
        }

        $command = DecodeCommand($general['turn0']);
        //삭턴 처리
        if($general['npc'] >= 2 || $general['killturn'] > $admin['killturn']) {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif(floor($command[0]) == 0 && $general['userlevel'] < 5) {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn='{$admin['killturn']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //연속턴 아닌경우 텀 리셋
        if($general['term']%100 != $command[0]) {
            $query = "update general set term=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //턴 처리
        switch($command[0]) {
            case 0: //휴식
                $date = substr($general['turntime'],11,5);
                $log[count($log)] = "<C>●</>{$admin['month']}월:아무것도 실행하지 않았습니다. <1>$date</>";
                pushGenLog($general, $log);
                break;
            case  1: process_1($connect, $general, 1); break; //농업
            case  2: process_1($connect, $general, 2); break; //상업
            case  3: process_3($connect, $general); break; //기술
            case  4: process_4($connect, $general); break; //선정
            case  5: process_5($connect, $general, 1); break; //수비
            case  6: process_5($connect, $general, 2); break; //성벽
            case  7: process_7($connect, $general); break; //정착 장려
            case  8: process_8($connect, $general); break; //치안
            case  9: process_9($connect, $general); break; //조달

            case 11: process_11($connect, $general, 1); break; //징병
            case 12: process_11($connect, $general, 2); break; //모병
            case 13: process_13($connect, $general); break; //훈련
            case 14: process_14($connect, $general); break; //사기진작
            case 15: process_15($connect, $general); break; //전투태세
            case 16: process_16($connect, $general); break; //전쟁
            case 17: process_17($connect, $general); break; //소집해제

            case 21: process_21($connect, $general); break; //이동
            //case 22: process_22($connect, $general); break; //등용 //TODO:등용장 재 디자인
            case 25: process_25($connect, $general); break; //임관
            case 26: process_26($connect, $general); break; //집합
            case 28: process_28($connect, $general); break; //귀환
            case 29: process_29($connect, $general); break; //인재탐색
            case 30: process_30($connect, $general); break; //강행
            
            case 31: process_31($connect, $general); break; //첩보
            case 32: process_32($connect, $general); break; //화계
            case 33: process_33($connect, $general); break; //탈취
            case 34: process_34($connect, $general); break; //파괴
            case 35: process_35($connect, $general); break; //선동
            case 36: process_36($connect, $general); break; //기습

            case 41: process_41($connect, $general); break; //단련
            case 42: process_42($connect, $general); break; //견문
            case 43: process_43($connect, $general); break; //증여
            case 44: process_44($connect, $general); break; //헌납
            case 45: process_45($connect, $general); break; //하야
            case 46: process_46($connect, $general); break; //건국
            case 47: process_47($connect, $general); break; //방랑
            case 48: process_48($connect, $general); break; //장비매매
            case 49: process_49($connect, $general); break; //군량매매
            case 50: process_50($connect, $general); break; //요양

            case 54: process_54($connect, $general); break; //선양
            case 55: process_55($connect, $general); break; //거병
            case 56: process_56($connect, $general); break; //해산
            case 57: process_57($connect, $general); break; //모반 시도
        }
    }
}

function updateCommand($connect, $no, $type=0) {
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


function backupdateCommand($connect, $no, $type=0) {
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


function command_Single($connect, $turn, $command) {
    $command = EncodeCommand(0, 0, 0, $command);

    $count = sizeof($turn);
    $str = "con=con";
    for($i=0; $i < $count; $i++) {
        $str .= ",turn{$turn[$i]}='{$command}'";
    }
    $query = "update general set {$str} where owner='{$_SESSION['noMember']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //echo "<script>location.replace('commandlist.php');</script>";
    echo 'commandlist.php';//TODO:debug all and replace

}

function command_Chief($connect, $turn, $command) {
    $command = EncodeCommand(0, 0, 0, $command);

    $query = "select nation,level from general where owner='{$_SESSION['noMember']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $count = sizeof($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$command}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    //echo "<script>location.replace('b_chiefcenter.php');</script>";
    echo 'b_chiefcenter.php';//TODO:debug all and replace
}

function command_Other($connect, $turn, $commandtype) {
    echo "<form name=form1 action=processing.php method=post target=_parent>";
    $count = sizeof($turn);
    for($i=0; $i < $count; $i++) {
        echo "<input type=hidden name=turn[] value=$turn[$i]>";
    }
    echo "<input type=hidden name=commandtype value={$commandtype}>";
    echo "</form>";
    echo "a";   // 없으면 파폭에서 아래 스크립트 실행 안됨
    echo "<script>form1.submit();</script>";
}


function EncodeCommand($fourth, $third, $double, $command) {
    $str  = _String::Fill2($fourth, 4, "0");
    $str .= _String::Fill2($third,  4, "0");
    $str .= _String::Fill2($double, 4, "0");
    $str .= _String::Fill2($command, 2, "0");
    return $str;
}

function DecodeCommand($str) {
    $command[3] = floor(substr($str, 0, 4));
    $command[2] = floor(substr($str, 4, 4));
    $command[1] = floor(substr($str, 8, 4));
    $command[0] = floor(substr($str, 12, 2));
    return $command;
}

