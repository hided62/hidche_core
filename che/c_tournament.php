<?php
// $btn, $msg

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select tournament,phase,tnmt_type,develcost from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,userlevel,tournament from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

switch($admin[tnmt_type]) {
case 0: $tp = "total";  $tp2 = "전력전"; $tp3 = "leader+power+intel"; break;
case 1: $tp = "leader"; $tp2 = "통솔전"; $tp3 = "leader"; break;
case 2: $tp = "power";  $tp2 = "일기토"; $tp3 = "power"; break;
case 3: $tp = "intel";  $tp2 = "설전";   $tp3 = "intel"; break;
}

if($me[tournament] == 1 && $me[userlevel] < 5) { echo "<script>location.replace('b_tournament.php');</script>"; exit(); }

if($btn == "자동개최설정" && $me[userlevel] >= 5) {
    $query = "update game set tnmt_trig={$trig}";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "개최" && $me[userlevel] >= 5) {
    startTournament($connect, $auto, $type);
} elseif($btn == "중단" && $me[userlevel] >= 5) {
    $query = "update game set tnmt_auto=0, tournament=0, phase=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif((($btn == "투입" || $btn == "무명투입" || $btn == "쪼렙투입" || $btn == "일반투입" || $btn == "굇수투입" || $btn == "랜덤투입") && $me[userlevel] >= 5) || $btn == "참가") {
    if($btn == "투입") {
        $query = "select no,name,npc,leader,power,intel,explevel,gold,horse,weap,book from general where no='$gen'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general[gold] -= $admin[develcost];
    } elseif($btn == "무명투입") {
        $general[no] = 0;
        $general[name] = "무명장수";
        $general[npc] = 2;
        $general[leader] = 10;
        $general[power] = 10;
        $general[intel] = 10;
        $general[explevel] = 10;
        $general[gold] = 0;
    } elseif($btn == "쪼렙투입") {
        $sel = rand() % 32;
        $query = "select no,name,npc,leader,power,intel,explevel,gold,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by {$tp} limit {$sel},1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general[gold] -= $admin[develcost];
    } elseif($btn == "일반투입") {
        //참가한 사람 평균치
        $query = "select AVG({$tp3}) as av from general where tournament=1";
        $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $avgGen = MYDB_fetch_array($genResult);

        //그 장수보다 높은장수 수
        $query = "select no from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 and {$tp3}>{$avgGen[av]}";
        $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $genCount = MYDB_num_rows($genResult);

        $sel = rand() % 32 + $genCount - 8;

        $query = "select no,name,npc,leader,power,intel,explevel,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by {$tp} desc limit {$sel},1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general[gold] -= $admin[develcost];
    } elseif($btn == "굇수투입") {
        $sel = rand() % 32;
        $query = "select no,name,npc,leader,power,intel,explevel,gold,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by {$tp} desc limit {$sel},1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general[gold] -= $admin[develcost];
    } elseif($btn == "랜덤투입") {
        $query = "select no,name,npc,leader,power,intel,explevel,gold,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general[gold] -= $admin[develcost];
    //참가
    } else {
        $query = "select no,name,npc,leader,power,intel,explevel,gold,horse,weap,book from general where no='$me[no]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);

        //$admin[develcost]원 참가비
        if($general[gold] < $admin[develcost]) { echo "<script>location.replace('b_tournament.php');</script>"; exit(1); }
        $general[gold] -= $admin[develcost];
    }

    $query = "select grp from tournament where grp<10 group by grp having count(*)=8";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $grpCount = MYDB_num_rows($result);
    for($i=0; $i < $grpCount; $i++) {
        $grp = MYDB_fetch_array($result);
        $occupied[$grp[grp]] = 1;
    }
    for($i=0; $i < 8; $i++) {
        if($occupied[$i] == 0) {
            $map[count($map)] = $i;
        }
    }

    if($grpCount < 8) {
        $grp = $map[rand() % count($map)];
        $query = "select grp from tournament where grp='$grp'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $grpCount = MYDB_num_rows($result);
        $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no, h, w, b) values ('$general[no]', '$general[npc]', '$general[name]', '$general[leader]', '$general[power]', '$general[intel]', '$general[explevel]', '$grp', '$grpCount', '$general[horse]', '$general[weap]', '$general[book]')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set tournament=1,gold='$general[gold]' where no='$general[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    $query = "select grp from tournament where grp<10 group by grp having count(*)=8";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $grpCount = MYDB_num_rows($result);
    if($grpCount >= 8) {
        $query = "update game set tournament=2, phase=0";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
} elseif(($btn == "쪼렙전부투입" || $btn == "일반전부투입" || $btn == "굇수전부투입" || $btn == "랜덤전부투입") && $me[userlevel] >= 5) {
    $z = 0;
    for($i=0; $i < 8; $i++) {
        $query = "select grp from tournament where grp='$i'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $grpCount = MYDB_num_rows($result);
        for($k=$grpCount; $k < 8; $k++) {
            $code[$z++] = $i * 10 + $k;
        }
    }
    //섞기
    for($i=0; $i < $z; $i++) {
        $index = rand() % $z;
        $temp = $code[$i];
        $code[$i] = $code[$index];
        $code[$index] = $temp;
    }

    for($i=0; $i < $z; $i++) {
        $sel = rand() % 32;
        if($btn == "쪼렙전부투입") {
            $query = "select no,name,npc,leader,power,intel,explevel,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by {$tp} limit {$sel},1";
        } elseif($btn == "일반전부투입") {
            //참가한 사람 평균치
            $query = "select AVG({$tp3}) as av from general where tournament=1";
            $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $avgGen = MYDB_fetch_array($genResult);

            //그 장수보다 높은장수 수
            $query = "select no from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 and {$tp3}>{$avgGen[av]}";
            $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $genCount = MYDB_num_rows($genResult);

            $sel += $genCount - 8;

            $query = "select no,name,npc,leader,power,intel,explevel,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by {$tp} desc limit {$sel},1";
        } elseif($btn == "굇수전부투입") {
            $query = "select no,name,npc,leader,power,intel,explevel,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by {$tp} desc limit {$sel},1";
        } else {
            $query = "select no,name,npc,leader,power,intel,explevel,leader+power+intel as total,horse,weap,book from general where tournament=0 and gold>='$admin[develcost]' and npc>=2 order by rand() limit 0,1";
        }
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);

        $grp    = floor($code[$i] / 10);
        $grp_no = $code[$i] % 10;
        $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no, h, w, b) values ('$general[no]', '$general[npc]', '$general[name]', '$general[leader]', '$general[power]', '$general[intel]', '$general[explevel]', '$grp', '$grp_no', '$general[horse]', '$general[weap]', '$general[book]')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set tournament=1,gold=gold-'$admin[develcost]' where no='$general[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    $query = "update game set tournament=2, phase=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "무명전부투입" && $me[userlevel] >= 5) { fillLowGenAll($connect);
} elseif($btn == "예선"         && $me[userlevel] >= 5) { qualify($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "예선전부"     && $me[userlevel] >= 5) { qualifyAll($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "추첨"         && $me[userlevel] >= 5) { selection($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "추첨전부"     && $me[userlevel] >= 5) { selectionAll($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "본선"         && $me[userlevel] >= 5) { finallySingle($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "본선전부"     && $me[userlevel] >= 5) { finallyAll($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "배정"         && $me[userlevel] >= 5) { final16set($connect);
} elseif($btn == "베팅마감"     && $me[userlevel] >= 5) {
    $dt = date("Y-m-d H:i:s", time() + 60);
    $query = "update game set tournament='7',phase='0',tnmt_time='$dt' where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "16강" && $me[userlevel] >= 5) { finalFight($connect, $admin[tnmt_type], $admin[tournament], $admin[phase], 16);
} elseif($btn == "8강"  && $me[userlevel] >= 5) { finalFight($connect, $admin[tnmt_type], $admin[tournament], $admin[phase], 8);
} elseif($btn == "4강"  && $me[userlevel] >= 5) { finalFight($connect, $admin[tnmt_type], $admin[tournament], $admin[phase], 4);
} elseif($btn == "결승" && $me[userlevel] >= 5) { finalFight($connect, $admin[tnmt_type], $admin[tournament], $admin[phase], 2);
} elseif($btn == "포상" && $me[userlevel] >= 5) { setGift($connect, $admin[tnmt_type], $admin[tournament], $admin[phase]);
} elseif($btn == "회수" && $me[userlevel] >= 5) { setRefund($connect);
} elseif($btn == "메시지" && $me[userlevel] >= 5) {
    $msg = addslashes(SQ2DQ($msg));
    $query = "update game set tnmt_msg='$msg'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}
?>

<!--<script>location.replace('b_tournament.php');</script> //TODO:replace -->
b_tournament.php
