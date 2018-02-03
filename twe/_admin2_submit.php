<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
    //echo "<script>location.replace('_admin2.php');</script>";
    echo '_admin2.php';//TODO:debug all and replace
}

switch($btn) {
    case "전체 접속허용":
        $query = "update general set con=0";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "전체 접속제한":
        $query = "update general set con=1000";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "접속가중치":
        $query = "update game set conweight='$conweight' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case "블럭 해제":
        getDB()->query('update general set block=0 where no IN %li', $genlist);
        break;
    case "1단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = getDB();
        $db->query('update general set block=1,killturn=24 where no IN %li',$genlist);
        //FIXME: subquery로 하는게 더 빠를 듯.
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        getRootDB()->query('update MEMBER set block_num=block_num+1,block_date=%s where no IN %ls', $date, $uid);
        break;
    case "2단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = getDB();
        $db->query('update general set gold=0,rice=0,block=2,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        getRootDB()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "3단계 블럭":
        $date = date('Y-m-d H:i:s');
        $db = getDB();
        $db->query('update general set gold=0,rice=0,block=3,killturn=24 where no IN %li',$genlist);
        $uid = $db->queryFirstColumn('select owner from general where no IN %li', $genlist);
        getRootDB()->query('update MEMBER set block_num=block_num+1,block_date=%s where id IN %ls', $date, $uid);
        break;
    case "무한삭턴":
        getDB()->query('update general set killturn=8000 where no IN %li',$genlist);
        break;
    case "강제 사망":
        $date = date('Y-m-d H:i:s');
        getDB()->query('update general set turn0=0,killturn=0,turntime=%s where no IN %li',$date, $genlist);
        break;
    case "특기 부여":
        $query = "select year,month from game where no=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $admin = MYDB_fetch_array($result);

        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = $btn." 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select no,leader,power,intel,history from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $general = MYDB_fetch_array($result);

            $special2 = getSpecial2($connect, $general['leader'], $general['power'], $general['intel']);

            $query = "update general set specage2=age,special2='$special2' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>특기 【<b><L>".getGenSpecial($special2)."</></b>】(을)를 익혔습니다!";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special2)."</></b>】(을)를 습득");
            pushGenLog($general, $log);
        }
        break;
    case "경험치1000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = $btn." 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set experience=experience+1000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "공헌치1000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = $btn." 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set dedication=dedication+1000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "보숙10000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = "보병숙련도+10000 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set dex0=dex0+10000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "궁숙10000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = "궁병숙련도+10000 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set dex10=dex10+10000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "기숙10000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = "기병숙련도+10000 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set dex20=dex20+10000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "귀숙10000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = "귀병숙련도+10000 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set dex30=dex30+10000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "차숙10000":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            $msg = "차병숙련도+10000 지급!";
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set dex40=dex40+10000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "특별회원 임명":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set userlevel=3 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "특별회원 해제":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set userlevel=1 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "접속 허용":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set con=0 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "접속 제한":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set con=1000 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "메세지 전달":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date',newmsg=1 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "무기지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            if($weap == 0) { $msg = "무기 회수!"; }
            else { $msg = getWeapName($weap)." 지급!"; }
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set weap='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set weap='$weap' where no='$genlist[$i]' and weap<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "책지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            if($weap == 0) { $msg = "서적 회수!"; }
            else { $msg = getBookName($weap)." 지급!"; }
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set book='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set book='$weap' where no='$genlist[$i]' and book<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "말지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            if($weap == 0) { $msg = "말 회수!"; }
            else { $msg = getHorseName($weap)." 지급!"; }
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set horse='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set horse='$weap' where no='$genlist[$i]' and horse<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "도구지급":
        $date = date('Y-m-d H:i:s');
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "select msgindex from general where no='$genlist[$i]'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $you = MYDB_fetch_array($result);
            if($weap == 0) { $msg = "특수도구 회수!"; }
            else { $msg = getItemName($weap)." 지급!"; }
            // 상대에게 발송
            $you['msgindex']++;
            if($you['msgindex'] >= 10) { $you['msgindex'] = 0; }
            $query = "update general set msgindex='{$you['msgindex']}',msg{$you['msgindex']}_type=10,msg{$you['msgindex']}='$msg',msg{$you['msgindex']}_who='$genlist[$i]'+10000,msg{$you['msgindex']}_when='$date' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        for($i=0; $i < sizeof($genlist); $i++) {
            if($weap == 0) {
                $query = "update general set item='0' where no='$genlist[$i]'";
            } else {
                $query = "update general set item='$weap' where no='$genlist[$i]' and item<'$weap'";
            }
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "NPC해제":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set npc=1 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "하야입력":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set turn0='00000000000045' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "방랑해산":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set turn0='00000000000047',turn1='00000000000056' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "NPC설정":
        for($i=0; $i < sizeof($genlist); $i++) {
            $query = "update general set npc=2 where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "00턴":
        $query = "select turnterm from game where no=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $admin = MYDB_fetch_array($result);

        for($i=0; $i < sizeof($genlist); $i++) {
            $turntime = getRandTurn($admin['turnterm']);
            $cutTurn = cutTurn($turntime, $admin['turnterm']);
            $query = "update general set turntime='$cutTurn' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
    case "랜덤턴":
        $query = "select turnterm from game where no=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $admin = MYDB_fetch_array($result);

        for($i=0; $i < sizeof($genlist); $i++) {
            $turntime = getRandTurn($admin['turnterm']);
            $query = "update general set turntime='$turntime' where no='$genlist[$i]'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        break;
}

//echo "<script>location.replace('_admin2.php');</script>";
echo '_admin2.php';//TODO:debug all and replace

