<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $msg
$btn = Util::getReq('btn');
$msg = Util::getReq('msg');

//관리자용
$auto = Util::getReq('auto', 'int');
$type = Util::getReq('type', 'int');
$gen = Util::getReq('gen', 'int');
$sel = Util::getReq('sel', 'int');
$trig = Util::getReq('trig', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

$admin = $gameStor->getValues(['tournament','phase','tnmt_type','develcost']);

$query = "select no,name,tournament from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

switch($admin['tnmt_type']) {
case 0: $tp = "total";  $tp2 = "전력전"; $tp3 = "leadership+strength+intel"; break;
case 1: $tp = "leadership"; $tp2 = "통솔전"; $tp3 = "leadership"; break;
case 2: $tp = "strength";  $tp2 = "일기토"; $tp3 = "strength"; break;
case 3: $tp = "intel";  $tp2 = "설전";   $tp3 = "intel"; break;
}

if($btn == '참가') {

    if($db->queryFirstField('SELECT `no` FROM tournament WHERE `no`=%i', $me['no'])){
        header('location:b_tournament.php');
        exit(1); 
    }

    $general = $db->queryFirstRow('SELECT no,name,npc,leadership,strength,intel,explevel,gold,horse,weapon,book FROM general WHERE `no`=%i', $me['no']);

    //{$admin['develcost']}원 참가비
    if($general['gold'] < $admin['develcost']) { 
        header('location:b_tournament.php');
        exit(1); 
    }
    $general['gold'] -= $admin['develcost'];

    $occupied = [];
    foreach($db->queryFirstColumn('SELECT grp FROM tournament WHERE grp < 10 GROUP BY grp HAVING count(*)=8') as $grp){
        $occupied[$grp] = true;
    }
    $grpCount = count($occupied);
    
    if($grpCount < 8) {
        $grp = Util::choiceRandom(array_keys($occupied));
        $grpMemberCount = $db->queryFirstField('SELECT count(*) FROM tournament WHERE grp=%i', $grp);
        $db->insert('tournament', [
            'no'=>$general['no'],
            'npc'=>$general['npc'],
            'name'=>$general['name'],
            'leadership'=>$general['leadership'],
            'strength'=>$general['strength'],
            'intel'=>$general['intel'],
            'lvl'=>$general['explevel'],
            'grp'=>$grp,
            'grp_no'=>$grpMemberCount,
            'h'=>$general['horse'],
            'w'=>$general['weapon'],
            'b'=>$general['book']
        ]);
        $db->update('general', [
            'tournament'=>1,
            'gold'=>$general['gold']
        ], 'no=%i', $general['no']);
    }

    $grpCount = $db->queryFirstField('SELECT count(*) FROM tournament where grp<10 GROUP BY grp HAVING count(*)=8');
    if($grpCount >= 8) {
        $gameStor->tournament = 2;
        $gameStor->phase = 0;
    }
    header('location:b_tournament.php');
    die(); 
}

if($session->userGrade < 5) { 
    header('location:b_tournament.php');
    exit(); 
}

if($btn == "자동개최설정") {
    $gameStor->tnmt_trig = $trig;
} elseif($btn == "개최") {
    startTournament($auto, $type);
} elseif($btn == "중단") {
    $gameStor->tnmt_auto = 0;
    $gameStor->tournament = 0;
    $gameStor->phase = 0;
} elseif($btn == "투입" || $btn == "무명투입" || $btn == "쪼렙투입" || $btn == "일반투입" || $btn == "굇수투입" || $btn == "랜덤투입") {
    if($btn == "투입") {
        $query = "select no,name,npc,leadership,strength,intel,explevel,gold,horse,weapon,book from general where no='$gen'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general['gold'] -= $admin['develcost'];
    } elseif($btn == "무명투입") {
        $general['no'] = 0;
        $general['name'] = "무명장수";
        $general['npc'] = 2;
        $general['leadership'] = 10;
        $general['strength'] = 10;
        $general['intel'] = 10;
        $general['explevel'] = 10;
        $general['gold'] = 0;
    } elseif($btn == "쪼렙투입") {
        $sel = rand() % 32;
        $query = "select no,name,npc,leadership,strength,intel,explevel,gold,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by {$tp} limit {$sel},1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general['gold'] -= $admin['develcost'];
    } elseif($btn == "일반투입") {
        //참가한 사람 평균치
        $query = "select AVG({$tp3}) as av from general where tournament=1";
        $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $avgGen = MYDB_fetch_array($genResult);

        //그 장수보다 높은장수 수
        $query = "select no from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 and {$tp3}>{$avgGen['av']}";
        $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $genCount = MYDB_num_rows($genResult);

        $sel = rand() % 32 + $genCount - 8;

        $query = "select no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by {$tp} desc limit {$sel},1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general['gold'] -= $admin['develcost'];
    } elseif($btn == "굇수투입") {
        $sel = rand() % 32;
        $query = "select no,name,npc,leadership,strength,intel,explevel,gold,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by {$tp} desc limit {$sel},1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general['gold'] -= $admin['develcost'];
    } elseif($btn == "랜덤투입") {
        $query = "select no,name,npc,leadership,strength,intel,explevel,gold,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        $general['gold'] -= $admin['develcost'];
    //참가
    }

    $occupied = [];
    foreach($db->queryFirstColumn('SELECT grp FROM tournament WHERE grp < 10 GROUP BY grp HAVING count(*)=8') as $grp){
        $occupied[$grp] = true;
    }
    $grpCount = count($occupied);

    if($grpCount < 8) {
        $grp = Util::choiceRandom(array_keys($occupied));
        $grpMemberCount = $db->queryFirstField('SELECT count(*) FROM tournament WHERE grp=%i', $grp);
        $db->insert('tournament', [
            'no'=>$general['no'],
            'npc'=>$general['npc'],
            'name'=>$general['name'],
            'leadership'=>$general['leadership'],
            'strength'=>$general['strength'],
            'intel'=>$general['intel'],
            'lvl'=>$general['explevel'],
            'grp'=>$grp,
            'grp_no'=>$grpMemberCount,
            'h'=>$general['horse'],
            'w'=>$general['weapon'],
            'b'=>$general['book']
        ]);
        $db->update('general', [
            'tournament'=>1,
            'gold'=>$general['gold']
        ], 'no=%i', $general['no']);
    }

    $grpCount = $db->queryFirstField('SELECT count(*) FROM tournament where grp<10 GROUP BY grp HAVING count(*)=8');
    if($grpCount >= 8) {
        $gameStor->tournament = 2;
        $gameStor->phase = 0;
    }

} elseif($btn == "쪼렙전부투입" || $btn == "일반전부투입" || $btn == "굇수전부투입" || $btn == "랜덤전부투입") {
    $z = 0;
    $code = [];
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
            $query = "select no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by {$tp} limit {$sel},1";
        } elseif($btn == "일반전부투입") {
            //참가한 사람 평균치
            $query = "select AVG({$tp3}) as av from general where tournament=1";
            $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $avgGen = MYDB_fetch_array($genResult);

            //그 장수보다 높은장수 수
            $query = "select no from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 and {$tp3}>{$avgGen['av']}";
            $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $genCount = MYDB_num_rows($genResult);

            $sel += $genCount - 8;

            $query = "select no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by {$tp} desc limit {$sel},1";
        } elseif($btn == "굇수전부투입") {
            $query = "select no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by {$tp} desc limit {$sel},1";
        } else {
            $query = "select no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 and gold>='{$admin['develcost']}' and npc>=2 order by rand() limit 0,1";
        }
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);

        $grp    = intdiv($code[$i], 10);
        $grp_no = $code[$i] % 10;
        $db->insert('tournament', [
            'no'=>$general['no'],
            'npc'=>$general['npc'],
            'name'=>$general['name'],
            'leadership'=>$general['leadership'],
            'strength'=>$general['strength'],
            'intel'=>$general['intel'],
            'lvl'=>$general['explevel'],
            'grp'=>$grp,
            'grp_no'=>$grp_no,
            'h'=>$general['horse'],
            'w'=>$general['weapon'],
            'b'=>$general['book']
        ]);
        $db->update('general', [
            'tournament'=>1,
            'gold'=>$db->sqleval('gold - %i', $admin['develcost'])
        ], 'no=%i', $general['no']);
    }

    $gameStor->tournament = 2;
    $gameStor->phase = 0;
} elseif($btn == "무명전부투입") { 
    fillLowGenAll();
} elseif($btn == "예선") { 
    qualify($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "예선전부") { 
    qualifyAll($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "추첨") { 
    selection($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "추첨전부") { 
    selectionAll($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "본선") { 
    finallySingle($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "본선전부") { 
    finallyAll($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "배정") { 
    final16set();
} elseif($btn == "베팅마감") {
    $dt = date("Y-m-d H:i:s", time() + 60);
    $gameStor->tournament=7;
    $gameStor->phase=0;
    $gameStor->tnmt_time = $dt;
} elseif($btn == "16강") { 
    finalFight($admin['tnmt_type'], $admin['tournament'], $admin['phase'], 16);
} elseif($btn == "8강") { 
    finalFight($admin['tnmt_type'], $admin['tournament'], $admin['phase'], 8);
} elseif($btn == "4강") { 
    finalFight($admin['tnmt_type'], $admin['tournament'], $admin['phase'], 4);
} elseif($btn == "결승") { 
    finalFight($admin['tnmt_type'], $admin['tournament'], $admin['phase'], 2);
} elseif($btn == "포상") { 
    setGift($admin['tnmt_type'], $admin['tournament'], $admin['phase']);
} elseif($btn == "회수") { 
    setRefund();
} elseif($btn == "메시지") {
    $gameStor->tnmt_msg = $msg;
}

header('location:b_tournament.php');