<?php
namespace sammo;


function processTournament() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['tournament', 'phase', 'tnmt_type', 'tnmt_auto', 'tnmt_time']);
    $now = new \DateTime();
    $admin['now'] = $now->format('Y-m-d H:i:s');
    $admin['offset'] = $now->getTimestamp() - (new \DateTime($admin['tnmt_time']))->getTimestamp();

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
        default: throw new MustNotBeReachedException();
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
                $gameStor->tournament = $tnmt;
                $gameStor->phase = $phase;
                $gameStor->tnmt_time = $dt;
                return;
            }

            if($admin['tnmt_auto'] == 1) {
                //처리 초 더한 날짜
                $dt = date("Y-m-d H:i:s", strtotime($admin['tnmt_time']) + $unit * $i);
                $hr = substr($dt, 11, 2);
                //지정시간대 넘어가면 중단 20~24시
                if($hr < 20) {
                    $dt = substr($dt, 0, 11)."20:00:00";
                    $gameStor->tournament = $tnmt;
                    $gameStor->phase = $phase;
                    $gameStor->tnmt_time = $dt;
                    return;
                }
            }
        }

        $second = $unit * $iter;
        $gameStor->tournament = $tnmt;
        $gameStor->phase = $phase;
        $gameStor->tnmt_time = (new \DateTimeImmutable($admin['tnmt_time']))->add(new \DateInterval("PT{$second}S"))->format('Y-m-d H:i:s');
    }
}

function getTournamentTerm() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $tnmt_auto = $gameStor->tnmt_auto;

    switch($tnmt_auto) {
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

    list($tnmt, $tnmt_time) = $gameStor->getValuesAsArray(['tournament', 'tnmt_time']);
    $dt = substr($tnmt_time, 11, 5);
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

function getTournament(int $tnmt) {
    return [
        "<font color=magenta>경기 없음</font>",
        "<font color=orange>참가 모집중</font>",
        "<font color=orange>예선 진행중</font>",
        "<font color=orange>본선 추첨중</font>",
        "<font color=orange>본선 진행중</font>",
        "<font color=orange>16강 배정중</font>",
        "<font color=orange>베팅 진행중</font>",
        "<font color=orange>16강 진행중</font>",
        "<font color=orange>8강 진행중</font>",
        "<font color=orange>4강 진행중</font>",
        "<font color=orange>결승 진행중</font>",
    ][$tnmt]??"TOURNAMENT_TYPE_ERR_{$tnmt}";
}

function printRow($k, $npc, $name, $abil, $tgame, $win, $draw, $lose, $gd, $gl, $prmt) {
    if($prmt > 0) { $name = "<font color=orange>".$name."</font>"; }
    elseif($npc >= 2) { $name = "<font color=cyan>".$name."</font>"; }
    elseif($npc == 1) { $name = "<font color=skyblue>".$name."</font>"; }
    echo "<tr align=center><td id=bg2>$k</td><td style='font-size:80%;'>$name</td><td>$abil</td><td>$tgame</td><td>$win</td><td>$draw</td><td>$lose</td><td>$gd</td><td>$gl</td></tr>";
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

    $admin = $gameStor->getValues(['year', 'month']);
    
    $gameStor->tnmt_auto = $auto;
    $gameStor->tnmt_time = (new \DateTimeImmutable())->add(new \DateInterval("PT{$unit}M"))->format('Y-m-d H:i:s');
    $gameStor->tournament = 1;
    $gameStor->tnmt_type = $type;
    $gameStor->phase = 0;

    $db->update('general', [
        'tournament'=>0,
    ], true);
    $db->update('betting', [
        'bet0'=>0,
        'bet1'=>0,
        'bet2'=>0,
        'bet3'=>0,
        'bet4'=>0,
        'bet5'=>0,
        'bet6'=>0,
        'bet7'=>0,
        'bet8'=>0,
        'bet9'=>0,
        'bet10'=>0,
        'bet11'=>0,
        'bet12'=>0,
        'bet13'=>0,
        'bet14'=>0,
        'bet15'=>0
    ], true);
    $db->query('TRUNCATE TABLE tournament');

    $opener = $db->queryFirstField('SELECT `general`.`name` FROM `general` JOIN `nation` ON `general`.`nation` = `nation`.`nation` WHERE `general`.`officer_level` = 12 AND `nation`.`level` = 7 ORDER BY rand() LIMIT 1');
    if(!$opener){
        $opener = $gameStor->prev_winner;
    }

    if($opener){
        $openerText = "황제 <Y>{$opener}</>의 명으로 ";
    }
    else{
        $openerText = '';
    }

    $history = [];
    [$typeText, $genTypeText] = [
        ['전력전','영웅'],
        ['통솔전','명사'],
        ['일기토','용사'],
        ['설전','책사'],
    ][$type];

    $history[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월:{$openerText}<C>{$typeText}</> 대회가 개최됩니다! 천하의 <span class='ev_highlight'>{$genTypeText}</span>들을 모집하고 있습니다!";
    
    pushGlobalHistoryLog($history, $admin['year'], $admin['month']);
}

function fillLowGenAll() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $grpCount = [];

    $dummyGeneral = [
        'no'=>0,
        'npc'=>2,
        'name'=>'무명장수',
        'leadership'=>10,
        'strength'=>10,
        'intel'=>10,
        'explevel'=>10
    ];

    $dummyGeneral = [
        'no'=>0,
        'npc'=>2,
        'name'=>'무명장수',
        'leadership'=>10,
        'strength'=>10,
        'intel'=>10,
        'lvl'=>0,
        'h'=>'None',
        'w'=>'None',
        'b'=>'None'
    ];

    for($i=0;$i<8;$i++){
        $grpCount[$i] = 0;
    }

    foreach($db->queryAllLists('SELECT grp, count(grp) FROM tournament GROUP BY grp') as [$grpIdx, $grpCnt]){
        $grpCount[$grpIdx] = $grpCnt;
    }

    $gameStor->tournament = 2;
    $gameStor->phase = 0;

    $currentJoinerCnt = array_sum($grpCount);
    if($currentJoinerCnt == 64){
        return;
    }

    $toBeFilledCnt = 8*8-$currentJoinerCnt;

    //자동신청하고, 돈 있고, 아직 참가 안한 장수
    $freeJoiners = $db->query(
        'SELECT no,npc,name,leadership,strength,intel,explevel,horse,weapon,book from general where tnmt=1 and tournament=0 order by rand() limit %d',
        $toBeFilledCnt
    );

    $joinersValues = [];
    $joinersIdx = [];

    foreach($freeJoiners as $general){
        $grpIdx = array_keys($grpCount, min($grpCount))[0];
        $grpCnt = $grpCount[$grpIdx];
        $joinersValues[] = [
            'no'=>$general['no'],
            'npc'=>$general['npc'],
            'name'=>$general['name'],
            'leadership'=>$general['leadership'],
            'strength'=>$general['strength'],
            'intel'=>$general['intel'],
            'lvl'=>$general['explevel'],
            'grp'=>$grpIdx,
            'grp_no'=>$grpCnt,
            'h'=>$general['horse'],
            'w'=>$general['weapon'],
            'b'=>$general['book']
        ];

        $joinersIdx[] = $general['no'];
        $grpCount[$grpIdx] += 1;
    }

    foreach($grpCount as $grpIdx=>$grpCnt){
        while($grpCnt < 8){
            $dummyCopy = $dummyGeneral;
            $dummyCopy['grp'] = $grpIdx;
            $dummyCopy['grp_no'] = $grpCnt;
            $grpCnt += 1;
            $joinersValues[] = $dummyCopy;
        }
    }

    $db->update('general', [
        'tournament'=>1
    ], 'no IN %li', $joinersIdx);

    $db->insert('tournament', $joinersValues);
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

    $cand = getTwo($tnmt, $phase);

    //각 그룹 페이즈 실행
    for($i=0; $i < 8; $i++) {
        fight($tnmt_type, $tnmt, $phase, $i, $cand[0], $cand[1], 0);
    }
    if($phase < 55) {
        $gameStor->phase+=1;
    } else {
        $gameStor->phase=0;
        $gameStor->tournament=3;

        foreach(Util::range(8) as $grpIdx){
            $promoters = $db->query(
                'SELECT grp,grp_no,win+draw+lose as game,win,draw,lose,gl,win*3+draw as gd from tournament where grp=%i order by gd desc, gl desc, seq limit 0,4',
                $grpIdx
            );
            foreach($promoters as $grpRank=>$grpGen){
                $db->update('tournament', [
                    'prmt'=>$grpRank+1,
                ], 'grp=%i AND grp_no=%i', $grpIdx, $grpGen['grp_no']);
            }
        }
    }
}

function qualifyAll($tnmt_type, $tnmt, $phase) {
    $db = DB::db();

    $start = $phase;
    $end = $phase - ($phase % 4) + 4;
    for($i=$start; $i < $end; $i++) {
        qualify($tnmt_type, $tnmt, $i);
    }
}

function selection($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    //시드1 배정
    if($phase < 8) {
        $grp = $phase + 10;  $grp_no = 0;
        $general = $db->queryFirstRow('SELECT * FROM tournament WHERE prmt=1 ORDER BY rand() LIMIT 1');
    //시드2 배정
    } elseif($phase < 16) {
        $grp = $phase - 8 + 10;  $grp_no = 1;
        $general = $db->queryFirstRow('SELECT * FROM tournament WHERE prmt=2 ORDER BY rand() LIMIT 1');
    } elseif($phase < 24) {
        $grp = $phase - 16 + 10;  $grp_no = 2;
        $general = $db->queryFirstRow('SELECT * FROM tournament WHERE prmt>2 ORDER BY rand() LIMIT 1');
    } else {
        $grp = $phase - 24 + 10;  $grp_no = 3;
        $general = $db->queryFirstRow('SELECT * FROM tournament WHERE prmt>2 ORDER BY rand() LIMIT 1');
    }
    //해당 시드에서 랜덤 선택
    //본선에 추가
    $db->insert('tournament', [
        'no'=>$general['no'],
        'npc'=>$general['npc'],
        'name'=>$general['name'],
        'leadership'=>$general['leadership'],
        'strength'=>$general['strength'],
        'intel'=>$general['intel'],
        'lvl'=>$general['lvl'],
        'grp'=>$grp,
        'grp_no'=>$grp_no,
        'h'=>$general['h'],
        'w'=>$general['w'],
        'b'=>$general['b']
    ]);

    //시드 삭제
    $db->update('tournament', [
        'prmt'=>0
    ], 'grp=%i AND grp_no=%i', $general['grp'], $general['grp_no']);

    if($phase < 31) {
        $gameStor->phase+=1;
    } else {
        $gameStor->tournamemt = 4;
        $gameStor->phase=0;
    }
}

function selectionAll($tnmt_type, $tnmt, $phase) {
    $start = $phase;
    $end = $phase - ($phase % 8) + 8;
    for($i=$start; $i < $end; $i++) {
        selection($tnmt_type, $tnmt, $i);
    }
}

function finallySingle($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $cand = getTwo($tnmt, $phase);

    //각 그룹 페이즈 실행
    for($i=10; $i < 18; $i++) {
        fight($tnmt_type, $tnmt, $phase, $i, $cand[0], $cand[1], 0);
    }
    if($phase < 5) {
        $gameStor->phase+=1;
    } else {
        $gameStor->tournament=5;
        $gameStor->phase=0;

        foreach(Util::range(10, 18) as $grpIdx){
            $promoters = $db->query(
                'SELECT grp,grp_no,win+draw+lose as game,win,draw,lose,gl,win*3+draw as gd from tournament where grp=%i order by gd desc, gl desc, seq limit 0,2',
                $grpIdx
            );
            foreach($promoters as $grpRank=>$grpGen){
                $db->update('tournament', [
                    'prmt'=>$grpRank+1,
                ], 'grp=%i AND grp_no=%i', $grpIdx, $grpGen['grp_no']);
            }
        }
    }
}

function finallyAll($tnmt_type, $tnmt, $phase) {
    $start = $phase;
    $end = $phase - ($phase % 2) + 2;
    for($i=$start; $i < $end; $i++) {
        finallySingle($tnmt_type, $tnmt, $i);
    }
}

function final16set() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    //1조1-5조2, 2조1-6조2, 3조1-7조2, 4조1-8조2, 5조1-1조2, 6조1-2조2, 7조1-3조2, 8조1-4조2
    $grp  = Array(10, 14, 11, 15, 12, 16, 13, 17, 14, 10, 15, 11, 16, 12, 17, 13);
    $prmt = Array( 1,  2,  1,  2,  1,  2,  1,  2,  1,  2,  1,  2,  1,  2,  1,  2);
    for($i=0; $i < 16; $i++) {
        $general = $db->queryFirstRow('SELECT * FROM tournament WHERE grp=%i AND prmt=%i LIMIT 1', $grp[$i], $prmt[$i]);
        //16강에 추가
        $newGrp    = 20 + intdiv($i, 2);
        $newGrp_no = $i % 2;

        $db->insert('tournament', [
            'no'=>$general['no'],
            'npc'=>$general['npc'],
            'name'=>$general['name'],
            'leadership'=>$general['leadership'],
            'strength'=>$general['strength'],
            'intel'=>$general['intel'],
            'lvl'=>$general['lvl'],
            'grp'=>$newGrp,
            'grp_no'=>$newGrp_no,
            'h'=>$general['h'],
            'w'=>$general['w'],
            'b'=>$general['b']
        ]);
    }
    $db->update('tournament', [
        'prmt'=>0
    ], true);

    $gameStor->tournament=6;
    $gameStor->phase=0;
}

function finalFight($tnmt_type, $tnmt, $phase, $type) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    switch($type) {
    case 16: $offset = 20; $turn = 7; $next = 7; break;
    case  8: $offset = 30; $turn = 3; $next = 8; break;
    case  4: $offset = 40; $turn = 1; $next = 9; break;
    case  2: $offset = 50; $turn = 0; $next = 0; break;
    default: throw new MustNotBeReachedException();
    }

    $grp = $phase + $offset;
    fight($tnmt_type, $tnmt, $phase, $grp, 0, 1, 1);

    $gameStor->phase+=1;

    $general = $db->queryFirstRow('SELECT * FROM tournament WHERE grp=%i AND win>0 AND (grp_no=0 OR grp_no=1) LIMIT 1', $grp);
    //x강에 추가
    $newGrp    = intdiv($phase, 2) + $offset + 10;
    $newGrp_no = $phase % 2;
    $db->insert('tournament', [
        'no'=>$general['no'],
        'npc'=>$general['npc'],
        'name'=>$general['name'],
        'leadership'=>$general['leadership'],
        'strength'=>$general['strength'],
        'intel'=>$general['intel'],
        'lvl'=>$general['lvl'],
        'grp'=>$newGrp,
        'grp_no'=>$newGrp_no,
        'h'=>$general['h'],
        'w'=>$general['w'],
        'b'=>$general['b']
    ]);

    if($phase >= $turn) {
        $gameStor->tournament = $next;
        $gameStor->phase = 0;
    }
}

function setGift($tnmt_type, $tnmt, $phase) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['year', 'month', 'develcost']);

    $resultHelper = [];

    switch($tnmt_type) {
    case 0: $tp = "전력전"; $tp2 = "tt"; break;
    case 1: $tp = "통솔전"; $tp2 = "tl"; break;
    case 2: $tp = "일기토"; $tp2 = "ts"; break;
    case 3: $tp = "설전";   $tp2 = "ti"; break;
    default: throw new MustNotBeReachedException();
    }

    //16강자 명성 돈
    $cost = $admin['develcost'];
    //experience를 General로
    foreach($db->query('SELECT no, name, grp, grp_no FROM tournament WHERE grp>=20 AND grp<30 AND no > 0') as $general){
        $generalID = $general['no'];
        $db->update('general', [
            'experience'=>$db->sqleval('experience + 25'),
            'gold'=>$db->sqleval('gold + %i', $cost)
        ], 'no=%i', $general['no']);
        $db->update('rank_data', [
            'value' => $db->sqleval('value + 1')
        ], 'general_id = %i AND type = %s', $general['no'], "{$tp2}g");
        //포상 장수 이름, 금액

        $logger = new ActionLogger($generalID, 0, $admin['year'], $admin['month']);
        $resultHelper[$generalID] = [
            'id'=>$generalID,
            'grp'=>$general['grp'],
            'grp_no'=>$general['grp_no'],
            'reward'=>$cost,
            'msg'=>"<span class='ev_highlight'>16강 진출</span>",
            'logger'=>$logger
        ];
    }
    //8강자 명성 돈
    $cost = $admin['develcost'] * 2;
    foreach($db->query('SELECT no, name FROM tournament WHERE grp>=30 AND grp<40 AND no > 0') as $general){
        $generalID = $general['no'];
        $db->update('general', [
            'experience'=>$db->sqleval('experience + 50'),
            'gold'=>$db->sqleval('gold + %i', $cost)
        ], 'no=%i', $general['no']);
        $db->update('rank_data', [
            'value' => $db->sqleval('value + 1')
        ], 'general_id = %i AND type = %s', $general['no'], "{$tp2}g");

        //포상 장수 이름, 금액
        $resultHelper[$generalID]['reward'] += $cost;
        $resultHelper[$generalID]['msg'] = "<span class='ev_highlight'>8강 진출</span>";
    }
    //4강자 명성 돈
    $cost = $admin['develcost'] * 3;
    foreach($db->query('SELECT no, name FROM tournament WHERE grp>=40 AND grp<50 AND no > 0') as $general){
        $generalID = $general['no'];
        $db->update('general', [
            'experience'=>$db->sqleval('experience + 50'),
            'gold'=>$db->sqleval('gold + %i', $cost)
        ], 'no=%i', $general['no']);
        $db->update('rank_data', [
            'value' => $db->sqleval('value + 2')
        ], 'general_id = %i AND type = %s', $general['no'], "{$tp2}g");

        //포상 장수 이름, 금액
        $resultHelper[$generalID]['reward'] += $cost;
        $resultHelper[$generalID]['msg'] = "<span class='ev_highlight'>4강 진출</span>";
    }
    //결승자 명성 돈
    $cost = $admin['develcost'] * 6;
    foreach($db->query('SELECT no, name, lose FROM tournament WHERE grp>=50 AND grp<60 AND no > 0') as $general){
        $generalID = $general['no'];
        $db->update('general', [
            'experience'=>$db->sqleval('experience + 100'),
            'gold'=>$db->sqleval('gold + %i', $cost)
        ], 'no=%i', $general['no']);
        $db->update('rank_data', [
            'value' => $db->sqleval('value + 2')
        ], 'general_id = %i AND type = %s', $general['no'], "{$tp2}g");

        //포상 장수 이름, 금액
        $resultHelper[$generalID]['reward'] += $cost;
        $resultHelper[$generalID]['msg'] = "<span class='ev_highlight'>준우승</span>으";
        if($general['lose'] > 0){
            $runnerUp = $general;
        }
    }
    //우승자 명성 돈
    $cost = $admin['develcost'] * 8;
    foreach($db->query('SELECT no, name FROM tournament WHERE grp>=60 AND no > 0') as $general){
        $generalID = $general['no'];
        $db->update('general', [
            'experience'=>$db->sqleval('experience + 200'),
            'gold'=>$db->sqleval('gold + %i', $cost)
        ], 'no=%i', $general['no']);
        $db->update('rank_data', [
            'value' => $db->sqleval('value + 2')
        ], 'general_id = %i AND type = %s', $general['no'], "{$tp2}g");

        //포상 장수 이름, 금액
        $resultHelper[$generalID]['reward'] += $cost;
        $resultHelper[$generalID]['msg'] = "<span class='ev_highlight'>우승</span>으";
        $winner = $general;
    }

    //자동진행 끝
    $gameStor->tnmt_auto = 0;

    //장수열전 기록
    /** @var ActionLogger */
    $winnerLogger = $resultHelper[$winner['no']]['logger'];
    $winnerLogger->pushGeneralHistoryLog("<C>{$tp}</> 대회에서 우승");
    /** @var ActionLogger */
    $runnerUpLogger = $resultHelper[$runnerUp['no']]['logger'];
    $runnerUpLogger->pushGeneralHistoryLog("<C>{$tp}</> 대회에서 준우승");


    

    $winnerRewardText = number_format($resultHelper[$winner['no']]['reward']);
    $runnerUpRewardText = number_format($resultHelper[$runnerUp['no']]['reward']);

    $josaYiWinner = JosaUtil::pick($winner['name'], '이');
    $josaYiRunnerUp = JosaUtil::pick($runnerUp['name'], '이');

    $winnerLogger->pushGlobalHistoryLog("<C>{$tp}</> 대회에서 <Y>{$winner['name']}</>{$josaYiWinner} <C>우승</>, <Y>{$runnerUp['name']}</>{$josaYiRunnerUp} <C>준우승</>을 차지하여 천하에 이름을 떨칩니다!", ActionLogger::EVENT_YEAR_MONTH);
    $winnerLogger->pushGlobalHistoryLog("<C>{$tp}</> 대회의 <S>우승자</>에게는 <C>{$winnerRewardText}</>, <S>준우승자</>에겐 <C>{$runnerUpRewardText}</>의 <S>상금</>과 약간의 <S>명성</>이 주어집니다!", ActionLogger::EVENT_YEAR_MONTH);
    
    foreach($resultHelper as $general){
        $rewardText = number_format($general['reward']);
        /** @var ActionLogger */
        $logger = $general['logger'];
        $logger->pushGeneralActionLog("<C>{$tp}</> 대회의 {$general['msg']}로 <C>{$rewardText}</>의 <S>상금</>, 약간의 <S>명성</> 획득!", ActionLogger::EVENT_PLAIN);
    }

    //우승자 번호
    $winnerGrp = $resultHelper[$winner['no']]['grp'];
    $winnerGrpNo = $resultHelper[$winner['no']]['grp_no'];
    $winnerSlot = ($winnerGrp - 20) * 2 + $winnerGrpNo;

    //당첨칸에 베팅한 사람들만
    $globalBet = $db->queryFirstList('SELECT * FROM betting WHERE general_id = 0');
    $globalBet = array_splice($globalBet, -16);
    $globalBetTotal = array_sum($globalBet);
    $rewardRate = round($globalBetTotal / max($globalBet[$winnerSlot], 1), 2);

    $betKey = "bet{$winnerSlot}";
    $gambleResult = Util::convertArrayToDict($db->query('SELECT general_id, %b as bet FROM betting WHERE %b > 0', $betKey, $betKey), 'general_id');

    foreach(General::createGeneralObjListFromDB(Util::squeezeFromArray($gambleResult, 'general_id'), ['gold'], 1) as $gambler){
        $reward = Util::round($gambleResult[$gambler->getID()]['bet'] * $rewardRate);
        $gambler->increaseVar('gold', $reward);
        $gambler->increaseRankVar('betwingold', $reward);
        $gambler->increaseRankVar('betwin', 1);
        $rewardText = number_format($reward);
        $gambler->getLogger()->pushGeneralActionLog("<C>{$tp}</> 대회의 베팅 당첨으로 <C>{$rewardText}</>의 <S>금</> 획득!", ActionLogger::EVENT_PLAIN);
        $gambler->applyDB($db);
    }
}

function setRefund() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    //16강자 명성 돈
    $cost = $gameStor->develcost;
    $generalIDList = $db->queryFirstColumn('SELECT no FROM tournament WHERE grp<10 AND no >0');
    $db->update('general', [
        'gold'=>$db->sqleval('gold + %i', $cost)
    ], 'no IN %li', $generalIDList);
    
    //베팅금 환수
    $db->update(['general', [
        'gold'=>$db->sqleval('gold + (SELECT bet0+bet1+bet2+bet3+bet4+bet5+bet6+bet7+bet8+bet9+bet10+bet11+bet12+bet13+bet14+bet15 FROM betting WHERE general_id = general.no)')
    ]], true);

    //자동진행 끝
    $gameStor->tnmt_auto = 0;
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

    eraseTnmtFightLog($group);

    $gen1 = $db->queryFirstRow('SELECT *,(leadership+strength+intel)*7/15 as total,h,w,b from tournament where grp=%i AND grp_no=%i', $group, $g1);
    $gen2 = $db->queryFirstRow('SELECT *,(leadership+strength+intel)*7/15 as total,h,w,b from tournament where grp=%i AND grp_no=%i', $group, $g2);

    if($type == 0) { $turn = 10; }
    else           { $turn = 100; }

    
    if($tnmt_type == 1) { $tp = "leadership"; $tp2 = "tl"; }
    elseif($tnmt_type == 2) { $tp = "strength"; $tp2 = "ts"; }
    elseif($tnmt_type == 3) { $tp = "intel"; $tp2 = "ti"; }
    else /*$tnmt_type == 0*/{ $tp = "total"; $tp2 = "tt"; } 

    $e1 = $energy1 = Util::round($gen1[$tp] * getLog($gen1['lvl'], $gen2['lvl']) * 10);
    $e2 = $energy2 = Util::round($gen2[$tp] * getLog($gen1['lvl'], $gen2['lvl']) * 10);

    

    foreach([$gen1, $gen2] as $gen){
        $horse = buildItemClass($gen['h']);
        $weapon = buildItemClass($gen['w']);
        $book = buildItemClass($gen['b']);

        //아이템 로그
        if(!$horse->isBuyable() && ($tnmt_type == 0 || $tnmt_type == 1)) {
            $itemName = $horse->getName();
            $itemRawName = $horse->getRawName();
            switch(rand()%4) {
            case 0: 
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 <S>{$itemName}</>{$josaYi} 포효합니다!"; break;
            case 1:
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 <S>{$itemName}</>{$josaYi} 그 위용을 뽐냅니다!"; break;
            case 2:
            $josaYi = JosaUtil::pick($gen['name'], '이');
            $josaUl = JosaUtil::pick($itemRawName, '을');
            $log[] = "<S>●</> <Y>{$gen['name']}</>{$josaYi} <S>{$itemName}</>{$josaUl} 타고 있습니다!"; break;
            case 3:
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 <S>{$itemName}</>{$josaYi} 갈기를 휘날립니다!"; break;
            }
        }
        if(!$weapon->isBuyable() && ($tnmt_type == 0 || $tnmt_type == 2)) {
            $itemName = $weapon->getName();
            $itemRawName = $weapon->getRawName();
            switch(rand()%4) {
            case 0:
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 <S>{$itemName}</>{$josaYi} 번뜩입니다!"; break;
            case 1:
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 <S>{$itemName}</>{$josaYi} 푸르게 빛납니다!"; break;
            case 2:
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 <S>{$itemName}</>에서 살기가 느껴집니다!"; break;
            case 3:
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 손에는 <S>{$itemName}</>{$josaYi} 쥐어져 있습니다!"; break;
            }
        }
        if(!$book->isBuyable() && ($tnmt_type == 0 || $tnmt_type == 3)) {
            $itemName = $book->getName();
            $itemRawName = $book->getRawName();
            switch(rand()%4) {
            case 0:
            $josaYi = JosaUtil::pick($gen['name'], '이');
            $josaUl = JosaUtil::pick($itemRawName, '을');
            $log[] = "<S>●</> <Y>{$gen['name']}</>{$josaYi} <S>{$itemName}</>{$josaUl} 펼쳐듭니다!"; break;
            case 1:
            $josaYi = JosaUtil::pick($gen['name'], '이');
            $josaUl = JosaUtil::pick($itemRawName, '을');
            $log[] = "<S>●</> <Y>{$gen['name']}</>{$josaYi} <S>{$itemName}</>{$josaUl} 품에서 꺼냅니다!"; break;
            case 2:
            $josaYi = JosaUtil::pick($gen['name'], '이');
            $josaUl = JosaUtil::pick($itemRawName, '을');
            $log[] = "<S>●</> <Y>{$gen['name']}</>{$josaYi} <S>{$itemName}</>{$josaUl} 들고 있습니다!"; break;
            case 3:
            $josaYi = JosaUtil::pick($itemRawName, '이');
            $log[] = "<S>●</> <Y>{$gen['name']}</>의 손에는 <S>{$itemName}</>{$josaYi} 쥐어져 있습니다!"; break;
            }
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
                $damage2 *= Util::randRangeInt(150, 300) / 100; // 150 ~ 300%
                $critical1 = 1;
                if    ($tnmt_type == 0) { switch(rand()%6) { case 0: $str = "참격"; break; case 1: $str = "집중"; break; case 2: $str = "역공"; break; case 3: $str = "반격"; break; case 4: $str = "선제"; break; case 5: $str = "도발"; break; } }
                elseif($tnmt_type == 1) { switch(rand()%6) { case 0: $str = "추행진"; break; case 1: $str = "학익진"; break; case 2: $str = "장사진"; break; case 3: $str = "형액진"; break; case 4: $str = "기형진"; break; case 5: $str = "구행진"; break; } }
                elseif($tnmt_type == 2) { switch(rand()%6) { case 0: $str = "기합"; break; case 1: $str = "기염"; break; case 2: $str = "반격"; break; case 3: $str = "역공"; break; case 4: $str = "삼단"; break; case 5: $str = "나선"; break; } }
                elseif($tnmt_type == 3) { switch(rand()%6) { case 0: $str = "논파"; break; case 1: $str = "항변"; break; case 2: $str = "반론"; break; case 3: $str = "반박"; break; case 4: $str = "도발"; break; case 5: $str = "면박"; break; } }
                $log[] = "<S>●</> <Y>{$gen1['name']}</>의 <M>{$str}</>!";
            }
            $ratio = rand() % 1000;
            if($critical2 == 0 && $gen2[$tp] >= $ratio) {
                $damage1 *= Util::randRangeInt(150, 300) / 100; // 150 ~ 300%
                $critical2 = 1;
                if    ($tnmt_type == 0) { switch(rand()%6) { case 0: $str = "참격"; break; case 1: $str = "집중"; break; case 2: $str = "역공"; break; case 3: $str = "반격"; break; case 4: $str = "선제"; break; case 5: $str = "도발"; break; } }
                elseif($tnmt_type == 1) { switch(rand()%6) { case 0: $str = "추행진"; break; case 1: $str = "학익진"; break; case 2: $str = "장사진"; break; case 3: $str = "형액진"; break; case 4: $str = "기형진"; break; case 5: $str = "구행진"; break; } }
                elseif($tnmt_type == 2) { switch(rand()%6) { case 0: $str = "기합"; break; case 1: $str = "기염"; break; case 2: $str = "반격"; break; case 3: $str = "역공"; break; case 4: $str = "삼단"; break; case 5: $str = "나선"; break; } }
                elseif($tnmt_type == 3) { switch(rand()%6) { case 0: $str = "논파"; break; case 1: $str = "항변"; break; case 2: $str = "반론"; break; case 3: $str = "반박"; break; case 4: $str = "도발"; break; case 5: $str = "면박"; break; } }
                $log[] = "<S>●</> <Y>{$gen2['name']}</>의 <M>{$str}</>!";
            }
        }

        Util::setRound($damage1);
        Util::setRound($damage2);

        $energy1 -= $damage1;
        $energy2 -= $damage2;
        $tDamage1 = $damage1;   $tDamage2 = $damage2;
        $tEnergy1 = $energy1;   $tEnergy2 = $energy2;
        if($energy1 <= 0 && $energy2 <= 0) {
            $r1 = $tEnergy1 / Util::valueFit($tDamage1, 1);
            $r2 = $tEnergy2 / Util::valueFit($tDamage2, 1);

            if($r1 > $r2) {
                $offset = Util::round($tEnergy2*$tDamage1/Util::valueFit($tDamage2,1));
                $damage1 += $offset;    $energy1 -= $offset;
                $damage2 += $tEnergy2;  $energy2 = 0;
            } else {
                $offset = Util::round($tEnergy1*$tDamage2/Util::valueFit($tDamage1,1));
                $damage2 += $offset;    $energy2 -= $offset;
                $damage1 += $tEnergy1;  $energy1 = 0;
            }
        } elseif($energy1 * $energy2 <= 0) {
            if($energy2 < 0) {
                $offset = Util::round($tEnergy2*$tDamage1/Util::valueFit($tDamage2,1));
                $damage1 += $offset;    $energy1 -= $offset;
                $damage2 += $tEnergy2;  $energy2 = 0;
            }
            if($energy1 < 0) {
                $offset = Util::round($tEnergy1*$tDamage2/Util::valueFit($tDamage1,1));
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

    $general1 = $db->queryFirstRow('SELECT value as gl FROM rank_data WHERE general_id = %i AND type = %s', $gen1['no'], $tp2.'g');
    $general2 = $db->queryFirstRow('SELECT value as gl FROM rank_data WHERE general_id = %i AND type = %s', $gen2['no'], $tp2.'g');

    switch($sel) {
    case 0:
        $log[] = "<S>●</> <Y>{$gen1['name']}</> <S>승리</>!";

        $gl = Util::round(($gd2 - $gd1) / 50);
        $db->update('tournament', [
            'win'=>$db->sqleval('win+1'),
            'gl'=>$db->sqleval('gl+%i', $gl)
        ], 'grp=%i AND grp_no=%i', $group, $g1);
        $db->update('tournament', [
            'lose'=>$db->sqleval('lose+1'),
            'gl'=>$db->sqleval('gl-%i', $gl)
        ], 'grp=%i AND grp_no=%i', $group, $g2);
        
        if($general1['gl'] > $general2['gl'])      { $gl1 = 1; $gl2 = 0; }
        elseif($general1['gl'] == $general2['gl']) { $gl1 = 2; $gl2 = -1; }
        else                                   { $gl1 = 3; $gl2 = -2; }

        $gen1resKey = 'w';
        $gen2resKey = 'l';
        break;
    case 1:
        $log[] = "<S>●</> <Y>{$gen2['name']}</> <S>승리</>!";

        $gl = Util::round(($gd1 - $gd2) / 50);
        $db->update('tournament', [
            'lose'=>$db->sqleval('lose+1'),
            'gl'=>$db->sqleval('gl-%i', $gl)
        ], 'grp=%i AND grp_no=%i', $group, $g1);
        $db->update('tournament', [
            'win'=>$db->sqleval('win+1'),
            'gl'=>$db->sqleval('gl+%i', $gl)
        ], 'grp=%i AND grp_no=%i', $group, $g2);

        if($general2['gl'] > $general1['gl'])      { $gl2 = 1; $gl1 = 0; }
        elseif($general2['gl'] == $general1['gl']) { $gl2 = 2; $gl1 = -1; }
        else                                   { $gl2 = 3; $gl1 = -2; }

        $gen1resKey = 'l';
        $gen2resKey = 'w';
        break;
    case 2:
        $log[] = "<S>●</> 무승부!";

        $db->update('tournament', [
            'draw'=>$db->sqleval('draw+1')
        ], 'grp=%i AND (grp_no=%i OR grp_no=%i)', $group, $g1, $g2);

        if($general1['gl'] > $general2['gl'])      { $gl2 = -1; $gl1 = 1; }
        elseif($general1['gl'] == $general2['gl']) { $gl2 = 0; $gl1 = 0; }
        else                                   { $gl2 = +1; $gl1 = -1; }

        $gen1resKey = 'd';
        $gen2resKey = 'd';
        break;
    default: new MustNotBeReachedException();
    }

    $db->update('rank_data', [
        'value' => $db->sqleval('value + 1')
    ], 'general_id=%i AND type = %s',$gen1['no'], "{$tp2}{$gen1resKey}");
    $db->update('rank_data', [
        'value' => $db->sqleval('value + %i', $gl1),
    ], 'general_id=%i AND type = %s',$gen1['no'], "{$tp2}g");

    $db->update('rank_data', [
        'value' => $db->sqleval('value + 1'),
    ], 'general_id=%i AND type = %s',$gen2['no'], "{$tp2}{$gen2resKey}");
    $db->update('rank_data', [
        'value' => $db->sqleval('value + %i', $gl2)
    ], 'general_id=%i AND type = %s',$gen2['no'], "{$tp2}g");



    if(($tnmt == 2 && $phs < 55) || ($tnmt == 4 && $phs < 5)) {
        $cand = getTwo($tnmt, $phs+1);

        $gen1Name = $db->queryFirstField('SELECT name FROM tournament where grp=%i and grp_no=%i', $group, $cand[0]);
        $gen2Name = $db->queryFirstField('SELECT name FROM tournament where grp=%i and grp_no=%i', $group, $cand[1]);

        $log[] = "--------------- 다음경기 ---------------<br><S>☞</> <Y>{$gen1Name}</> vs <Y>{$gen2Name}</>";
    }

    pushTnmtFightLog($group, $log);
}
