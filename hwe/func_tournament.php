<?php
namespace sammo;


function processTournament() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $query = "select tournament,phase,tnmt_type,tnmt_auto,tnmt_time,now() as now,TIMESTAMPDIFF(SECOND,tnmt_time,now()) as offset from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //수동일땐 무시
    if($admin['tnmt_auto'] == 0) { return; }

    $type  = $admin['tnmt_type'];
    $tnmt  = $admin['tournament'];
    $phase = $admin['phase'];

    //현시간이 스탬프 지나친경우
    if($admin['offset'] >= 0)  {
        switch($admin['tnmt_auto']) {
        case 1: $unit = 720; break;
        case 2: $unit = 420; break;
        case 3: $unit = 180; break;
        case 4: $unit =  60; break;
        case 5: $unit =  30; break;
        case 6: $unit =  15; break;
        case 7: $unit =   5; break;
        }

        //업데이트 횟수
        $iter = intdiv($admin['offset'], $unit) + 1;

        for($i=0; $i < $iter; $i++) {
            switch($tnmt) {
            case 1: //신청 마감
                fillLowGenAll();
                $tnmt = 2;  $phase = 0;
                break;
            case 2: //예선중
                qualify($type, $tnmt, $phase);        $phase++;
                if($phase >= 56) { $tnmt = 3; $phase = 0; }
                break;
            case 3: //추첨중
                selectionAll($type, $tnmt, $phase);   $phase+=8;
                if($phase >= 32) { $tnmt = 4; $phase = 0; }
                break;
            case 4: //본선중
                finallySingle($type, $tnmt, $phase);        $phase++;
                if($phase >= 6) { $tnmt = 5; $phase = 0; }
                break;
            case 5: //배정중
                final16set();
                $tnmt = 6; $phase = 0;
                break;
            case 6: //베팅중
                $tnmt = 7; $phase = 0;
                break;
            case 7: //16강중
                finalFight($type, $tnmt, $phase, 16); $phase++;
                if($phase >= 8) { $tnmt = 8; $phase = 0; }
                break;
            case 8: //8강중
                finalFight($type, $tnmt, $phase, 8);  $phase++;
                if($phase >= 4) { $tnmt = 9; $phase = 0; }
                break;
            case 9: //4강중
                finalFight($type, $tnmt, $phase, 4);  $phase++;
                if($phase >= 2) { $tnmt = 10; $phase = 0; }
                break;
            case 10: //결승중
                finalFight($type, $tnmt, $phase, 2);
                $tnmt = 0; $phase = 0;
                setGift($type, $tnmt, $phase);
                $i = $iter;
                break;
            }

            //베팅은 무조건 60페이즈후 진행(최대 1시간)
            if($tnmt == 6) {
                $betTerm = $unit * 60;
                if($betTerm > 3600) { $betTerm = 3600; }
                //처리 초 더한 날짜
                $dt = date("Y-m-d H:i:s", strtotime($admin['tnmt_time']) + $unit * $i + $betTerm);
                $query = "update game set tournament='$tnmt',phase='$phase',tnmt_time='$dt'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                return;
            }

            if($admin['tnmt_auto'] == 1) {
                //처리 초 더한 날짜
                $dt = date("Y-m-d H:i:s", strtotime($admin['tnmt_time']) + $unit * $i);
                $hr = substr($dt, 11, 2);
                //지정시간대 넘어가면 중단 20~24시
                if($hr < 20) {
                    $dt = substr($dt, 0, 11)."20:00:00";
                    $query = "update game set tournament='$tnmt',phase='$phase',tnmt_time='$dt'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    return;
                }
            }
        }

        $second = $unit * $iter;
        $query = "update game set tournament='$tnmt',phase='$phase',tnmt_time=DATE_ADD(tnmt_time, INTERVAL {$second} SECOND)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function getTournamentTerm() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $query = "select tnmt_auto from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    switch($admin['tnmt_auto']) {
    case 0: $str = ''; break;
    case 1: $str = "경기당 12분"; break;
    case 2: $str = "경기당 7분"; break;
    case 3: $str = "경기당 3분"; break;
    case 4: $str = "경기당 1분"; break;
    case 5: $str = "경기당 30초"; break;
    case 6: $str = "경기당 15초"; break;
    case 7: $str = "경기당 5초"; break;
    }
    return $str;
}

function getTournamentTime() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $query = "select tournament,tnmt_time from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $tnmt = $admin['tournament'];
    $dt = substr($admin['tnmt_time'], 11, 5);
    switch($tnmt) {
    case 1: $tnmt = "개막시간 {$dt}"; break;
    case 2: $tnmt = "다음경기 {$dt}"; break;
    case 3: $tnmt = "다음추첨 {$dt}"; break;
    case 4: $tnmt = "다음경기 {$dt}"; break;
    case 5: $tnmt = "16강배정 {$dt}"; break;
    case 6: $tnmt = "베팅마감 {$dt}"; break;
    case 7: case 8: case 9: case 10:
            $tnmt = "다음경기 {$dt}"; break;
    default: $tnmt = ""; break;
    }
    return $tnmt;
}

function getTournament($tnmt) {
    switch($tnmt) {
    case  0: $tnmt = "<font color=magenta>경기 없음</font>"; break;
    case  1: $tnmt = "<font color=orange>참가 모집중</font>"; break;
    case  2: $tnmt = "<font color=orange>예선 진행중</font>"; break;
    case  3: $tnmt = "<font color=orange>본선 추첨중</font>"; break;
    case  4: $tnmt = "<font color=orange>본선 진행중</font>"; break;
    case  5: $tnmt = "<font color=orange>16강 배정중</font>"; break;
    case  6: $tnmt = "<font color=orange>베팅 진행중</font>"; break;
    case  7: $tnmt = "<font color=orange>16강 진행중</font>"; break;
    case  8: $tnmt = "<font color=orange>8강 진행중</font>"; break;
    case  9: $tnmt = "<font color=orange>4강 진행중</font>"; break;
    case 10: $tnmt = "<font color=orange>결승 진행중</font>"; break;
    }
    return $tnmt;
}

function printRow($k, $npc, $name, $abil, $game, $win, $draw, $lose, $gd, $gl, $prmt) {
    if($prmt > 0) { $name = "<font color=orange>".$name."</font>"; }
    elseif($npc >= 2) { $name = "<font color=cyan>".$name."</font>"; }
    elseif($npc == 1) { $name = "<font color=skyblue>".$name."</font>"; }
    echo "<tr align=center><td id=bg2>$k</td><td>$name</td><td>$abil</td><td>$game</td><td>$win</td><td>$draw</td><td>$lose</td><td>$gd</td><td>$gl</td></tr>";
}

function printFighting($tournament, $phase) {
    $code = $tournament * 100 + $phase;
    if($code == 0) {
        echo "<tr valign=top>";
        echo "<td>&nbsp;</td>";
        echo "<td>&nbsp;</td>";
        echo "<td>&nbsp;</td>";
        echo "<td>".getTnmtFightLogAll(50)."</td>";
        echo "<td>&nbsp;</td>";
        echo "<td>&nbsp;</td>";
        echo "<td>&nbsp;</td>";
        echo "<td>&nbsp;</td>";
        echo "</tr>";
    } elseif($code <= 300) {
        echo "<tr valign=top>";
        for($i=0; $i < 8; $i++) {
            echo "<td>".getTnmtFightLogAll($i)."</td>";
        }
        echo "</tr>";
    } elseif($code < 400) {
    } elseif($code <= 500) {
        echo "<tr valign=top>";
        for($i=10; $i < 18; $i++) {
            echo "<td>".getTnmtFightLogAll($i)."</td>";
        }
        echo "</tr>";
    } elseif($code < 700) {
    } elseif($code <= 800) {
        echo "<tr valign=top>";
        for($i=20; $i < 28; $i++) {
            echo "<td>".getTnmtFightLogAll($i)."</td>";
        }
        echo "</tr>";
    } elseif($code <= 900) {
        echo "<tr valign=top>";
        for($i=30; $i < 34; $i++) {
            echo "<td>&nbsp;</td>";
            echo "<td>".getTnmtFightLogAll($i)."</td>";
        }
        echo "</tr>";
    } elseif($code <= 1000) {
        echo "<tr valign=top>";
        for($i=40; $i < 42; $i++) {
            echo "<td>&nbsp;</td>";
            echo "<td>&nbsp;</td>";
            echo "<td>".getTnmtFightLogAll($i)."</td>";
            echo "<td>&nbsp;</td>";
        }
        echo "</tr>";
    }
}

function startTournament($auto, $type) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    eraseTnmtFightLogAll();

    switch($auto) {
    case 1: $unit = 60; break;
    case 2: $unit = 60; break;
    case 3: $unit = 60; break;
    case 4: $unit = 60; break;
    case 5: $unit = 30; break;
    case 6: $unit = 15; break;
    case 7: $unit =  5; break;
    default:$unit = 60; break;
    }

    $query = "select year,month from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    
    $query = "update game set tnmt_auto='$auto',tnmt_time=DATE_ADD(now(), INTERVAL {$unit} MINUTE),tournament=1,tnmt_type='$type',phase=0,bet0=0,bet1=0,bet2=0,bet3=0,bet4=0,bet5=0,bet6=0,bet7=0,bet8=0,bet9=0,bet10=0,bet11=0,bet12=0,bet13=0,bet14=0,bet15=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set tournament=0,bet0=0,bet1=0,bet2=0,bet3=0,bet4=0,bet5=0,bet6=0,bet7=0,bet8=0,bet9=0,bet10=0,bet11=0,bet12=0,bet13=0,bet14=0,bet15=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "truncate tournament";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $history = [];
    switch($type) {
    case 0: $history[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월: <C>전력전</> 대회가 개최됩니다! 천하의 <span class='ev_highlight'>영웅</span>들을 모집하고 있습니다!"; break;
    case 1: $history[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월: <C>통솔전</> 대회가 개최됩니다! 천하의 <span class='ev_highlight'>명사</span>들을 모집하고 있습니다!"; break;
    case 2: $history[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월: <C>일기토</> 대회가 개최됩니다! 천하의 <span class='ev_highlight'>용사</span>들을 모집하고 있습니다!"; break;
    case 3: $history[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월: <C>설전</> 대회가 개최됩니다! 천하의 <span class='ev_highlight'>책사</span>들을 모집하고 있습니다!"; break;
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
}

function fillLowGenAll() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $general = [];
    $grpCount = [];

    $query = "select develcost from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $general['no'] = 0;
    $general['name'] = "무명장수";
    $general['npc'] = 2;
    $general['leader'] = 10;
    $general['power'] = 10;
    $general['intel'] = 10;
    $general['explevel'] = 10;

    for($i=0; $i < 8; $i++) {
        $query = "select grp from tournament where grp='$i'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $grpCount[$i] = MYDB_num_rows($result);
    }

    //자동신청하고, 돈 있고, 아직 참가 안한 장수
    $query = "select no,npc,name,leader,power,intel,explevel from general where tnmt='1' and tournament='0' and gold>='{$admin['develcost']}' order by rand() limit 0,64";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);

    $valid = 1;
    while($valid == 1) {
        $valid = 0;
        //조마다 돌아가며
        for($i=0; $i < 8; $i++) {
            //비었으면 채우기
            if($grpCount[$i] < 8) {
                //자동신청 장수 있으면 채우기
                if($genCount > 0) {
                    $genCount--;
                    $gen = MYDB_fetch_array($result);
                    $query = "update general set gold=gold-'{$admin['develcost']}',tournament='1' where no='{$gen['no']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                    $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no) values ('{$gen['no']}', '{$gen['npc']}', '{$gen['name']}', '{$gen['leader']}', '{$gen['power']}', '{$gen['intel']}', '{$gen['explevel']}', '$i', '$grpCount[$i]')";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                //자동신청 장수 없으면 무명장수
                } else {
                    $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no) values ('{$general['no']}', '{$general['npc']}', '{$general['name']}', '{$general['leader']}', '{$general['power']}', '{$general['intel']}', '{$general['explevel']}', '$i', '$grpCount[$i]')";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                }
                $grpCount[$i]++;
            }
            //덜 찼으면 루프 다시
            if($grpCount[$i] < 8) {
                $valid = 1;
            }
        }
    }

    $query = "update game set tournament=2, phase=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

//0 경기없음
//1 모집중
//2 예선중 28페이즈
//3 본선추첨중
//4 본선중 6페이즈
//5 16강 배정중
//6 베팅
//6 16강
//7 8강
//8 4강
//9 결승
function getTwo($tournament, $phase) {
    $cand = [];
    switch($tournament) {
    case 2:
        //예선
        switch($phase%28) {
        case  0: $cand[0] = 0; $cand[1] = 1; break; case  1: $cand[0] = 2; $cand[1] = 3; break; case  2: $cand[0] = 4; $cand[1] = 5; break; case  3: $cand[0] = 6; $cand[1] = 7; break;
        case  4: $cand[0] = 0; $cand[1] = 2; break; case  5: $cand[0] = 1; $cand[1] = 3; break; case  6: $cand[0] = 4; $cand[1] = 6; break; case  7: $cand[0] = 5; $cand[1] = 7; break;
        case  8: $cand[0] = 0; $cand[1] = 3; break; case  9: $cand[0] = 1; $cand[1] = 6; break; case 10: $cand[0] = 2; $cand[1] = 5; break; case 11: $cand[0] = 4; $cand[1] = 7; break;
        case 12: $cand[0] = 0; $cand[1] = 4; break; case 13: $cand[0] = 1; $cand[1] = 5; break; case 14: $cand[0] = 2; $cand[1] = 6; break; case 15: $cand[0] = 3; $cand[1] = 7; break;
        case 16: $cand[0] = 0; $cand[1] = 5; break; case 17: $cand[0] = 1; $cand[1] = 4; break; case 18: $cand[0] = 2; $cand[1] = 7; break; case 19: $cand[0] = 3; $cand[1] = 6; break;
        case 20: $cand[0] = 0; $cand[1] = 6; break; case 21: $cand[0] = 1; $cand[1] = 7; break; case 22: $cand[0] = 2; $cand[1] = 4; break; case 23: $cand[0] = 3; $cand[1] = 5; break;
        case 24: $cand[0] = 0; $cand[1] = 7; break; case 25: $cand[0] = 1; $cand[1] = 2; break; case 26: $cand[0] = 3; $cand[1] = 4; break; case 27: $cand[0] = 5; $cand[1] = 6; break;
        }
        if($phase >= 28) {
            $temp = $cand[0];
            $cand[0] = $cand[1];
            $cand[1] = $temp;
        }
        break;
    case 4:
        //본선
        switch($phase%6) {
        case  0: $cand[0] = 0; $cand[1] = 1; break; case  1: $cand[0] = 2; $cand[1] = 3; break;
        case  2: $cand[0] = 0; $cand[1] = 2; break; case  3: $cand[0] = 1; $cand[1] = 3; break;
        case  4: $cand[0] = 0; $cand[1] = 3; break; case  5: $cand[0] = 1; $cand[1] = 2; break;
        }
        break;
    }
    return $cand;
}

function qualify($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $cand = getTwo($tnmt, $phase);

    //각 그룹 페이즈 실행
    for($i=0; $i < 8; $i++) {
        fight($tnmt_type, $tnmt, $phase, $i, $cand[0], $cand[1], 0);
    }
    if($phase < 55) {
        $query = "update game set phase=phase+1";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $query = "update game set tournament=3, phase=0";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        for($i=0; $i < 8; $i++) {
            $query = "select grp,grp_no,win+draw+lose as game,win,draw,lose,gl,win*3+draw as gd from tournament where grp='$i' order by gd desc, gl desc, seq limit 0,4";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            for($k=1; $k <= 4; $k++) {
                $gen = MYDB_fetch_array($result);
                $query = "update tournament set prmt='$k' where grp='{$gen['grp']}' and grp_no='{$gen['grp_no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }
}

function qualifyAll($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $connect=$db->get();

    $start = $phase;
    $end = $phase - ($phase % 4) + 4;
    for($i=$start; $i < $end; $i++) {
        qualify($tnmt_type, $tnmt, $i);
    }
}

function selection($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    //시드1 배정
    if($phase < 8) {
        $grp = $phase + 10;  $grp_no = 0;
        $query = "select * from tournament where prmt=1 order by rand() limit 0,1";
    //시드2 배정
    } elseif($phase < 16) {
        $grp = $phase - 8 + 10;  $grp_no = 1;
        $query = "select * from tournament where prmt=2 order by rand() limit 0,1";
    } elseif($phase < 24) {
        $grp = $phase - 16 + 10;  $grp_no = 2;
        $query = "select * from tournament where prmt>2 order by rand() limit 0,1";
    } else {
        $grp = $phase - 24 + 10;  $grp_no = 3;
        $query = "select * from tournament where prmt>2 order by rand() limit 0,1";
    }
    //해당 시드에서 랜덤 선택
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    //본선에 추가
    $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no, h, w, b) values ('{$general['no']}', '{$general['npc']}', '{$general['name']}', '{$general['ldr']}', '{$general['pwr']}', '{$general['itl']}', '{$general['lvl']}', '$grp', '$grp_no', '{$general['h']}', '{$general['w']}', '{$general['b']}')";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //시드 삭제
    $query = "update tournament set prmt=0 where grp='{$general['grp']}' and grp_no='{$general['grp_no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    if($phase < 31) {
        $query = "update game set phase=phase+1";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $query = "update game set tournament=4, phase=0";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function selectionAll($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $connect=$db->get();

    $start = $phase;
    $end = $phase - ($phase % 8) + 8;
    for($i=$start; $i < $end; $i++) {
        selection($tnmt_type, $tnmt, $i);
    }
}

function finallySingle($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $cand = getTwo($tnmt, $phase);

    //각 그룹 페이즈 실행
    for($i=10; $i < 18; $i++) {
        fight($tnmt_type, $tnmt, $phase, $i, $cand[0], $cand[1], 0);
    }
    if($phase < 5) {
        $query = "update game set phase=phase+1";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $query = "update game set tournament=5, phase=0";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        for($i=10; $i < 18; $i++) {
            $query = "select grp,grp_no,win+draw+lose as game,win,draw,lose,gl,win*3+draw as gd from tournament where grp='$i' order by gd desc, gl desc, seq limit 0,2";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            for($k=1; $k <= 2; $k++) {
                $gen = MYDB_fetch_array($result);
                $query = "update tournament set prmt='$k' where grp='{$gen['grp']}' and grp_no='{$gen['grp_no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }
}

function finallyAll($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $connect=$db->get();

    $start = $phase;
    $end = $phase - ($phase % 2) + 2;
    for($i=$start; $i < $end; $i++) {
        finallySingle($tnmt_type, $tnmt, $i);
    }
}

function final16set() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    //1조1-5조2, 2조1-6조2, 3조1-7조2, 4조1-8조2, 5조1-1조2, 6조1-2조2, 7조1-3조2, 8조1-4조2
    $grp  = Array(10, 14, 11, 15, 12, 16, 13, 17, 14, 10, 15, 11, 16, 12, 17, 13);
    $prmt = Array( 1,  2,  1,  2,  1,  2,  1,  2,  1,  2,  1,  2,  1,  2,  1,  2);
    for($i=0; $i < 16; $i++) {
        $query = "select * from tournament where grp='$grp[$i]' and prmt='$prmt[$i]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        //16강에 추가
        $newGrp    = 20 + intdiv($i, 2);
        $newGrp_no = $i % 2;
        $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no, h, w, b) values ('{$general['no']}', '{$general['npc']}', '{$general['name']}', '{$general['ldr']}', '{$general['pwr']}', '{$general['itl']}', '{$general['lvl']}', '$newGrp', '$newGrp_no', '{$general['h']}', '{$general['w']}', '{$general['b']}')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    $query = "update tournament set prmt=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "update game set tournament=6, phase=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function finalFight($tnmt_type, $tnmt, $phase, $type) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    switch($type) {
    case 16: $offset = 20; $turn = 7; $next = 7; break;
    case  8: $offset = 30; $turn = 3; $next = 8; break;
    case  4: $offset = 40; $turn = 1; $next = 9; break;
    case  2: $offset = 50; $turn = 0; $next = 0; break;
    }

    $grp = $phase + $offset;
    fight($tnmt_type, $tnmt, $phase, $grp, 0, 1, 1);

    $query = "update game set phase=phase+1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "select * from tournament where grp='$grp' and win>0 and (grp_no=0 or grp_no=1)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    //x강에 추가
    $newGrp    = intdiv($phase, 2) + $offset + 10;
    $newGrp_no = $phase % 2;
    $query = "insert into tournament (no, npc, name, ldr, pwr, itl, lvl, grp, grp_no, h, w, b) values ('{$general['no']}', '{$general['npc']}', '{$general['name']}', '{$general['ldr']}', '{$general['pwr']}', '{$general['itl']}', '{$general['lvl']}', '$newGrp', '$newGrp_no', '{$general['h']}', '{$general['w']}', '{$general['b']}')";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    if($phase >= $turn) {
        $query = "update game set tournament='$next', phase=0";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function setGift($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $query = "select year,month,develcost from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $genNo = [];
    $genName = [];
    $genGold = [];
    $genCall = [];

    switch($tnmt_type) {
    case 0: $tp = "전력전"; $tp2 = "tt"; break;
    case 1: $tp = "통솔전"; $tp2 = "tl"; break;
    case 2: $tp = "일기토"; $tp2 = "tp"; break;
    case 3: $tp = "설전";   $tp2 = "ti"; break;
    }

    //16강자 명성 돈
    $cost = $admin['develcost'];
    $query = "select no,name from tournament where grp>=20 and grp<30";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        $query = "update general set experience=experience+25,gold=gold+'$cost',{$tp2}g={$tp2}g+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //포상 장수 이름, 금액
        $genNo[$i] = $general['no'];
        $genName[$i] = $general['name'];
        $genGold[$general['no']] = $cost;
        $genCall[$general['no']] = "<span class='ev_highlight'>16강 진출</span>";
    }
    //8강자 명성 돈
    $cost = $admin['develcost'] * 2;
    $query = "select no from tournament where grp>=30 and grp<40";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        $query = "update general set experience=experience+50,gold=gold+'$cost',{$tp2}g={$tp2}g+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //포상 장수 이름, 금액
        $genGold[$general['no']] += $cost;
        $genCall[$general['no']] = "<span class='ev_highlight'>8강 진출</span>";
    }
    //4강자 명성 돈
    $cost = $admin['develcost'] * 3;
    $query = "select no from tournament where grp>=40 and grp<50";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        $query = "update general set experience=experience+75,gold=gold+'$cost',{$tp2}g={$tp2}g+2 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //포상 장수 이름, 금액
        $genGold[$general['no']] += $cost;
        $genCall[$general['no']] = "<span class='ev_highlight'>4강 진출</span>";
    }
    //결승자 명성 돈
    $cost = $admin['develcost'] * 6;
    $query = "select no from tournament where grp>=50 and grp<60";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        $query = "update general set experience=experience+150,gold=gold+'$cost',{$tp2}g={$tp2}g+2 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //포상 장수 이름, 금액
        $genGold[$general['no']] += $cost;
        $genCall[$general['no']] = "<span class='ev_highlight'>준우승</span>으";
    }
    //우승자 명성 돈
    $cost = $admin['develcost'] * 8;
    $query = "select no from tournament where grp>=60";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        $query = "update general set experience=experience+200,gold=gold+'$cost',{$tp2}g={$tp2}g+3,{$tp2}p={$tp2}p+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //포상 장수 이름, 금액
        $genGold[$general['no']] += $cost;
        $genCall[$general['no']] = "<span class='ev_highlight'>우승</span>으";
    }
    //우승자 이름
    $query = "select no,name from tournament where grp=60";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    //준우승자 이름
    $query = "select no,name from tournament where grp=50 and lose=1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general2 = MYDB_fetch_array($result);

    //자동진행 끝
    $query = "update game set tnmt_auto=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //장수열전 기록
    $query = "select no from general where no={$general['no']}";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen1 = MYDB_fetch_array($result);
    $query = "select no from general where no={$general2['no']}";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen2 = MYDB_fetch_array($result);
    pushGeneralHistory($gen1, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>{$tp}</> 대회에서 우승");
    pushGeneralHistory($gen2, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>{$tp}</> 대회에서 준우승");

    $cost = $admin['develcost'] * 20;
    $cost2 = $admin['develcost'] * 12;
    $history = [
        "<S>◆</>{$admin['year']}년 {$admin['month']}월: <C>{$tp}</> 대회에서 <Y>{$general['name']}</>(이)가 <C>우승</>, <Y>{$general2['name']}</>(이)가 <C>준우승</>을 차지하여 천하에 이름을 떨칩니다!",
        "<S>◆</>{$admin['year']}년 {$admin['month']}월: <C>{$tp}</> 대회의 <S>우승자</>에게는 <C>{$cost}</>, <S>준우승자</>에겐 <C>{$cost2}</>의 <S>상금</>과 약간의 <S>명성</>이 주어집니다!"
    ];
    pushWorldHistory($history, $admin['year'], $admin['month']);

    for($i=0; $i < count($genNo); $i++) {
        $general['no']   = $genNo[$i];
        $general['name'] = $genName[$i];
        pushGenLog($general, ["<S>◆</><C>{$tp}</> 대회의 {$genCall[$genNo[$i]]}로 <C>{$genGold[$genNo[$i]]}</>의 <S>상금</>, 약간의 <S>명성</> 획득!"]);
    }

    //우승자 번호
    $query = "select no from tournament where grp=60";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    //16강 목록에서 검색
    $query = "select grp,grp_no from tournament where grp>=20 and grp<30 and no='{$general['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    $no = ($general['grp'] - 20) * 2 + $general['grp_no'];

    $query = "select bet{$no},bet0+bet1+bet2+bet3+bet4+bet5+bet6+bet7+bet8+bet9+bet10+bet11+bet12+bet13+bet14+bet15 as bet from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    $bet = @round($admin['bet'] /  $admin["bet{$no}"], 2);

    //당첨칸에 베팅한 사람들만
    $query = "select no,name,gold,bet{$no} as bet from general where bet{$no}>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $gen = MYDB_fetch_array($result);
        $gold = Util::round($gen['bet'] * $bet);
        //금 지급
        $query = "update general set gold=gold+'$gold',betwingold=betwingold+'$gold',betwin=betwin+1 where no='{$gen['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //로그
        pushGenLog($gen, ["<S>◆</><C>{$tp}</> 대회의 베팅 당첨으로 <C>{$gold}</>의 <S>금</> 획득!"]);
    }
}

function setRefund() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $query = "select develcost from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //16강자 명성 돈
    $cost = $admin['develcost'];
    $query = "select no from tournament where grp<10";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        $query = "update general set gold=gold+'$cost' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    //자동진행 끝
    $query = "update game set tnmt_auto=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //베팅금 환수
    $query = "update general set gold=gold+bet0+bet1+bet2+bet3+bet4+bet5+bet6+bet7+bet8+bet9+bet10+bet11+bet12+bet13+bet14+bet15";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

//10차이 1.1, 50 차이 1.17, 100차이 1.2
function getLog($lvl1, $lvl2) {
    if($lvl1 >= $lvl2) {
        $ratio = 1 + log(1+$lvl1-$lvl2, 10) / 10;
    } else {
        $ratio = 1 - log(1+$lvl2-$lvl1, 10) / 10;
    }
    return $ratio;
}

//0 : 승무패, 1 : 승패
function fight($tnmt_type, $tnmt, $phs, $group, $g1, $g2, $type) {
    $log = [];
    $db = DB::db();
    $connect=$db->get();

    eraseTnmtFightLog($group);

    $query = "select *,(ldr+pwr+itl)*7/15 as tot,h,w,b from tournament where grp='$group' and grp_no='$g1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen1 = MYDB_fetch_array($result);

    $query = "select *,(ldr+pwr+itl)*7/15 as tot,h,w,b from tournament where grp='$group' and grp_no='$g2'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen2 = MYDB_fetch_array($result);

    if($type == 0) { $turn = 10; }
    else           { $turn = 100; }

    
    if($tnmt_type == 1) { $tp = "ldr"; $tp2 = "tl"; }
    elseif($tnmt_type == 2) { $tp = "pwr"; $tp2 = "tp"; }
    elseif($tnmt_type == 3) { $tp = "itl"; $tp2 = "ti"; }
    else /*$tnmt_type == 0*/{ $tp = "tot"; $tp2 = "tt"; } 

    $e1 = $energy1 = Util::round($gen1[$tp] * getLog($gen1['lvl'], $gen2['lvl']) * 10);
    $e2 = $energy2 = Util::round($gen2[$tp] * getLog($gen1['lvl'], $gen2['lvl']) * 10);

    //아이템 로그
    if($gen1['h'] > 6 && ($tnmt_type == 0 || $tnmt_type == 1)) {
        switch(rand()%4) {
        case 0: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <S>".getHorseName($gen1['h'])."</>(이)가 포효합니다!"; break;
        case 1: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <S>".getHorseName($gen1['h'])."</>(이)가 그 위용을 뽐냅니다!"; break;
        case 2: $log[] = "<S>●</> <Y>{$gen1['name']}</>(이)가 <S>".getHorseName($gen1['h'])."</>(을)를 타고 있습니다!"; break;
        case 3: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <S>".getHorseName($gen1['h'])."</>(이)가 갈기를 휘날립니다!"; break;
        }
    }
    if($gen1['w'] > 6 && ($tnmt_type == 0 || $tnmt_type == 2)) {
        switch(rand()%4) {
        case 0: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <S>".getWeapName($gen1['w'])."</>(이)가 번뜩입니다!"; break;
        case 1: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <S>".getWeapName($gen1['w'])."</>(이)가 푸르게 빛납니다!"; break;
        case 2: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <S>".getWeapName($gen1['w'])."</>에서 살기가 느껴집니다!"; break;
        case 3: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 손에는 <S>".getWeapName($gen1['w'])."</>(이)가 쥐어져 있습니다!"; break;
        }
    }
    if($gen1['b'] > 6 && ($tnmt_type == 0 || $tnmt_type == 3)) {
        switch(rand()%4) {
        case 0: $log[] = "<S>●</> <Y>{$gen1['name']}</>(이)가 <S>".getBookName($gen1['b'])."</>(을)를 펼쳐듭니다!"; break;
        case 1: $log[] = "<S>●</> <Y>{$gen1['name']}</>(이)가 <S>".getBookName($gen1['b'])."</>(을)를 품에서 꺼냅니다!"; break;
        case 2: $log[] = "<S>●</> <Y>{$gen1['name']}</>(이)가 <S>".getBookName($gen1['b'])."</>(을)를 들고 있습니다!"; break;
        case 3: $log[] = "<S>●</> <Y>{$gen1['name']}</>의 손에는 <S>".getBookName($gen1['b'])."</>(이)가 쥐어져 있습니다!"; break;
        }
    }
    if($gen2['h'] > 6 && ($tnmt_type == 0 || $tnmt_type == 1)) {
        switch(rand()%4) {
        case 0: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <S>".getHorseName($gen2['h'])."</>(이)가 포효합니다!"; break;
        case 1: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <S>".getHorseName($gen2['h'])."</>(이)가 그 위용을 뽐냅니다!"; break;
        case 2: $log[] = "<S>●</> <Y>{$gen2['name']}</>(이)가 <S>".getHorseName($gen2['h'])."</>(을)를 타고 있습니다!"; break;
        case 3: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <S>".getHorseName($gen2['h'])."</>(이)가 갈기를 휘날립니다!"; break;
        }
    }
    if($gen2['w'] > 6 && ($tnmt_type == 0 || $tnmt_type == 2)) {
        switch(rand()%4) {
        case 0: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <S>".getWeapName($gen2['w'])."</>(이)가 번뜩입니다!"; break;
        case 1: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <S>".getWeapName($gen2['w'])."</>(이)가 푸르게 빛납니다!"; break;
        case 2: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <S>".getWeapName($gen2['w'])."</>에서 살기가 느껴집니다!"; break;
        case 3: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 손에는 <S>".getWeapName($gen2['w'])."</>(이)가 쥐어져 있습니다!"; break;
        }
    }
    if($gen2['b'] > 6 && ($tnmt_type == 0 || $tnmt_type == 3)) {
        switch(rand()%4) {
        case 0: $log[] = "<S>●</> <Y>{$gen2['name']}</>(이)가 <S>".getBookName($gen2['b'])."</>(을)를 펼쳐듭니다!"; break;
        case 1: $log[] = "<S>●</> <Y>{$gen2['name']}</>(이)가 <S>".getBookName($gen2['b'])."</>(을)를 품에서 꺼냅니다!"; break;
        case 2: $log[] = "<S>●</> <Y>{$gen2['name']}</>(이)가 <S>".getBookName($gen2['b'])."</>(을)를 들고 있습니다!"; break;
        case 3: $log[] = "<S>●</> <Y>{$gen2['name']}</>의 손에는 <S>".getBookName($gen2['b'])."</>(이)가 쥐어져 있습니다!"; break;
        }
    }

    $log[] = "<S>●</> <Y>{$gen1['name']}</> <C>({$energy1})</> vs <C>({$energy2})</> <Y>{$gen2['name']}</>";

    $gd1 = 0;       $gd2 = 0;
    $phase = 0;     $sel = 2;
    while($phase < $turn) {
        $phase++;
        //평타
        $damage1 = Util::round($gen2[$tp] * (rand() % 21 + 90) / 130);   // 90~110%
        $damage2 = Util::round($gen1[$tp] * (rand() % 21 + 90) / 130);   // 90~110%
        //보너스타
        $ratio = rand() % 100;
        if($gen1[$tp] >= $ratio) { $damage2 += Util::round($gen1[$tp] * (rand() % 41 + 10) / 130); }   // 10~50
        $ratio = rand() % 100;
        if($gen2[$tp] >= $ratio) { $damage1 += Util::round($gen2[$tp] * (rand() % 41 + 10) / 130); }   // 10~50
        $critical1 = 0; $critical2 = 0;
        //막판 분노
        $ratio = rand() % 300;
        if($e1 / 5 > $energy1 && $damage1 > $damage2 && $gen1[$tp] >= $ratio) {
            $damage2 *= Util::round((rand() % 301 + 200) / 100); // 200 ~ 500%
            $critical1 = 1;
            if    ($tnmt_type == 0) { switch(rand()%2) { case 0: $str = "전력"; break; case 1: $str = "집중"; break; } }
            elseif($tnmt_type == 1) { switch(rand()%2) { case 0: $str = "봉시진"; break; case 1: $str = "어린진"; break; } }
            elseif($tnmt_type == 2) { switch(rand()%2) { case 0: $str = "삼단"; break; case 1: $str = "나선"; break; } }
            elseif($tnmt_type == 3) { switch(rand()%2) { case 0: $str = "독설"; break; case 1: $str = "논파"; break; } }
            $log[] = "<S>●</> <Y>{$gen1['name']}</>의 분노의 <M>{$str}</> 공격!";
        }
        $ratio = rand() % 300;
        if($e2 / 5 > $energy2 && $damage2 > $damage1 && $gen2[$tp] >= $ratio) {
            $damage1 *= Util::round((rand() % 301 + 200) / 100); // 200 ~ 500%
            $critical2 = 1;
                if($tnmt_type == 0) { switch(rand()%2) { case 0: $str = "전력"; break; case 1: $str = "집중"; break; } }
            elseif($tnmt_type == 1) { switch(rand()%2) { case 0: $str = "봉시진"; break; case 1: $str = "어린진"; break; } }
            elseif($tnmt_type == 2) { switch(rand()%2) { case 0: $str = "삼단"; break; case 1: $str = "나선"; break; } }
            elseif($tnmt_type == 3) { switch(rand()%2) { case 0: $str = "독설"; break; case 1: $str = "논파"; break; } }
            $log[] = "<S>●</> <Y>{$gen2['name']}</>의 분노의 <M>{$str}</> 공격!";
        }
        //1합 승부
        if($phase == 1) {
            $ratio = rand() % 400;
            if($gen1[$tp]*0.9 > $gen2[$tp] && $gen1[$tp] >= $ratio) {
                $damage1 = 0;   $damage2 = $e2;
                if    ($tnmt_type == 0) { $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <M>압도</>!"; }
                elseif($tnmt_type == 1) { $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <M>팔문금쇄진</>!"; }
                elseif($tnmt_type == 2) { $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <M>일격 필살</>!"; }
                elseif($tnmt_type == 3) { $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <M>모독 욕설</>!"; }
            }
            if($gen2[$tp]*0.9 > $gen1[$tp] && $gen2[$tp] >= $ratio) {
                $damage2 = 0;   $damage1 = $e1;
                if    ($tnmt_type == 0) { $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <M>압도</>!"; }
                elseif($tnmt_type == 1) { $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <M>팔문금쇄진</>!"; }
                elseif($tnmt_type == 2) { $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <M>일격 필살</>!"; }
                elseif($tnmt_type == 3) { $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <M>모독 욕설</>!"; }
            }
        } else {
            $ratio = rand() % 1000;
            if($critical1 == 0 && $gen1[$tp] >= $ratio) {
                $damage2 *= Util::round((rand() % 151 + 150) / 100); // 150 ~ 300%
                $critical1 = 1;
                if    ($tnmt_type == 0) { switch(rand()%6) { case 0: $str = "참격"; break; case 1: $str = "집중"; break; case 2: $str = "역공"; break; case 3: $str = "반격"; break; case 4: $str = "선제"; break; case 5: $str = "도발"; break; } }
                elseif($tnmt_type == 1) { switch(rand()%6) { case 0: $str = "추행진"; break; case 1: $str = "학익진"; break; case 2: $str = "장사진"; break; case 3: $str = "형액진"; break; case 4: $str = "기형진"; break; case 5: $str = "구행진"; break; } }
                elseif($tnmt_type == 2) { switch(rand()%6) { case 0: $str = "기합"; break; case 1: $str = "기염"; break; case 2: $str = "반격"; break; case 3: $str = "역공"; break; case 4: $str = "삼단"; break; case 5: $str = "나선"; break; } }
                elseif($tnmt_type == 3) { switch(rand()%6) { case 0: $str = "논파"; break; case 1: $str = "항변"; break; case 2: $str = "반론"; break; case 3: $str = "반박"; break; case 4: $str = "도발"; break; case 5: $str = "면박"; break; } }
                $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <M>{$str}</>!";
            }
            $ratio = rand() % 1000;
            if($critical2 == 0 && $gen2[$tp] >= $ratio) {
                $damage1 *= Util::round((rand() % 151 + 150) / 100); // 150 ~ 300%
                $critical2 = 1;
                if    ($tnmt_type == 0) { switch(rand()%6) { case 0: $str = "참격"; break; case 1: $str = "집중"; break; case 2: $str = "역공"; break; case 3: $str = "반격"; break; case 4: $str = "선제"; break; case 5: $str = "도발"; break; } }
                elseif($tnmt_type == 1) { switch(rand()%6) { case 0: $str = "추행진"; break; case 1: $str = "학익진"; break; case 2: $str = "장사진"; break; case 3: $str = "형액진"; break; case 4: $str = "기형진"; break; case 5: $str = "구행진"; break; } }
                elseif($tnmt_type == 2) { switch(rand()%6) { case 0: $str = "기합"; break; case 1: $str = "기염"; break; case 2: $str = "반격"; break; case 3: $str = "역공"; break; case 4: $str = "삼단"; break; case 5: $str = "나선"; break; } }
                elseif($tnmt_type == 3) { switch(rand()%6) { case 0: $str = "논파"; break; case 1: $str = "항변"; break; case 2: $str = "반론"; break; case 3: $str = "반박"; break; case 4: $str = "도발"; break; case 5: $str = "면박"; break; } }
                $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <M>{$str}</>!";
            }
        }

        $energy1 -= $damage1;
        $energy2 -= $damage2;
        $tDamage1 = $damage1;   $tDamage2 = $damage2;
        $tEnergy1 = $energy1;   $tEnergy2 = $energy2;
        if($energy1 <= 0 && $energy2 <= 0) {
            $r1 = $tEnergy1 / $tDamage1;
            $r2 = $tEnergy2 / $tDamage2;

            if($r1 > $r2) {
                $offset = Util::round($tEnergy2*$tDamage1/$tDamage2);
                $damage1 += $offset;    $energy1 -= $offset;
                $damage2 += $tEnergy2;  $energy2 = 0;
            } else {
                $offset = Util::round($tEnergy1*$tDamage2/$tDamage1);
                $damage2 += $offset;    $energy2 -= $offset;
                $damage1 += $tEnergy1;  $energy1 = 0;
            }
        } elseif($energy1 * $energy2 <= 0) {
            if($energy2 < 0) {
                $offset = Util::round($tEnergy2*$tDamage1/$tDamage2);
                $damage1 += $offset;    $energy1 -= $offset;
                $damage2 += $tEnergy2;  $energy2 = 0;
            }
            if($energy1 < 0) {
                $offset = Util::round($tEnergy1*$tDamage2/$tDamage1);
                $damage2 += $offset;    $energy2 -= $offset;
                $damage1 += $tEnergy1;  $energy1 = 0;
            }
        }
        $gd1 += $damage1;           $gd2 += $damage2;
        $energy1 = Util::round($energy1); $energy2 = Util::round($energy2);
        $damage1 = Util::round($damage1); $damage2 = Util::round($damage2);

        $log[] = '<S>●</> '
            .StringUtil::padStringAlignRight((string)$phase, 2, "0").'合 : '
            .'<C>'.StringUtil::padStringAlignRight((string)$energy1, 3, "0").'</>'
            .'<span class="ev_highlight">(-'.StringUtil::padStringAlignRight((string)$damage1, 3, "0").')</span>'
            .' vs '
            .'<span class="ev_highlight">(-'.StringUtil::padStringAlignRight((string)$damage2, 3, "0").')</span>'
            .'<C>'.StringUtil::padStringAlignRight((string)$energy2, 3, "0").'</>';

        if($energy1 <= 0 && $energy2 <= 0) {
            if($type == 0) { $sel = 2; break; }
            else {
                $energy1 = Util::round($e1 / 2); $energy2 = Util::round($e2 / 2);
                $log[] = "<S>●</> <span class='ev_highlight'>재대결</span>!";
            }
        }
        if($energy1 <= 0) { $sel = 1; break; }
        if($energy2 <= 0) { $sel = 0; break; }
    }

    $query = "select {$tp2}g as gl from general where no='{$gen1['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general1 = MYDB_fetch_array($result);

    $query = "select {$tp2}g as gl from general where no='{$gen2['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general2 = MYDB_fetch_array($result);

    switch($sel) {
    case 0:
        $log[] = "<S>●</> <Y>{$gen1['name']}</> <S>승리</>!";

        $gl = Util::round(($gd2 - $gd1) / 50);
        $query = "update tournament set win=win+1,gl=gl+'$gl' where grp='$group' and grp_no='$g1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update tournament set lose=lose+1,gl=gl-'$gl' where grp='$group' and grp_no='$g2'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general1['gl'] > $general2['gl'])      { $gl1 = "+1"; $gl2 = "+0"; }
        elseif($general1['gl'] == $general2['gl']) { $gl1 = "+2"; $gl2 = "-1"; }
        else                                   { $gl1 = "+3"; $gl2 = "-2"; }

        $query = "update general set {$tp2}w={$tp2}w+1,{$tp2}g={$tp2}g{$gl1} where no='{$gen1['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set {$tp2}l={$tp2}l+1,{$tp2}g={$tp2}g{$gl2} where no='{$gen2['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 1:
        $log[] = "<S>●</> <Y>{$gen2['name']}</> <S>승리</>!";

        $gl = Util::round(($gd1 - $gd2) / 50);
        $query = "update tournament set win=win+1,gl=gl+'$gl' where grp='$group' and grp_no='$g2'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update tournament set lose=lose+1,gl=gl-'$gl' where grp='$group' and grp_no='$g1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general2['gl'] > $general1['gl'])      { $gl2 = "+1"; $gl1 = "+0"; }
        elseif($general2['gl'] == $general1['gl']) { $gl2 = "+2"; $gl1 = "-1"; }
        else                                   { $gl2 = "+3"; $gl1 = "-2"; }

        $query = "update general set {$tp2}l={$tp2}l+1,{$tp2}g={$tp2}g{$gl1} where no='{$gen1['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set {$tp2}w={$tp2}w+1,{$tp2}g={$tp2}g{$gl2} where no='{$gen2['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 2:
        $log[] = "<S>●</> 무승부!";

        $query = "update tournament set draw=draw+1 where grp='$group' and (grp_no='$g1' or grp_no='$g2')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general1['gl'] > $general2['gl'])      { $gl2 = "-1"; $gl1 = "+1"; }
        elseif($general1['gl'] == $general2['gl']) { $gl2 = "+0"; $gl1 = "+0"; }
        else                                   { $gl2 = "+1"; $gl1 = "-1"; }

        $query = "update general set {$tp2}d={$tp2}d+1,{$tp2}g={$tp2}g{$gl1} where no='{$gen1['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set {$tp2}d={$tp2}d+1,{$tp2}g={$tp2}g{$gl2} where no='{$gen2['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    }

    if(($tnmt == 2 && $phs < 55) || ($tnmt == 4 && $phs < 5)) {
        $cand = getTwo($tnmt, $phs+1);

        $query = "select name from tournament where grp='$group' and grp_no='$cand[0]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen1 = MYDB_fetch_array($result);

        $query = "select name from tournament where grp='$group' and grp_no='$cand[1]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen2 = MYDB_fetch_array($result);

        $log[] = "--------------- 다음경기 ---------------<br><S>☞</> <Y>{$gen1['name']}</> vs <Y>{$gen2['name']}</>";
    }

    pushTnmtFightLog($group, $log);
}
