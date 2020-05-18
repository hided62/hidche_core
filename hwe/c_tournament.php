<?php
namespace sammo;

include "lib.php";
include "func.php";
// $btn, $msg
$btn = Util::getPost('btn');
$msg = Util::getPost('msg');

//관리자용
$auto = Util::getPost('auto', 'int');
$type = Util::getPost('type', 'int');
$gen = Util::getPost('gen', 'int');
$sel = Util::getPost('sel', 'int');
$trig = Util::getPost('trig', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$admin = $gameStor->getValues(['tournament','phase','tnmt_type','develcost']);

$me = $db->queryFirstRow('SELECT no,name,tournament from general where owner=%i',$userID);

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

    $freeSlot = [
        0=>8,
        1=>8,
        2=>8,
        3=>8,
        4=>8,
        5=>8,
        6=>8,
        7=>8,
    ];
    foreach($db->query('SELECT count(*) as cnt, grp FROM tournament WHERE grp < 10 GROUP BY grp') as $grpInfo){
        if($grpInfo['cnt'] == 8){
            unset($freeSlot[$grpInfo['grp']]);
        }
        $freeSlot[$grpInfo['grp']] = 8 - $grpInfo['cnt'];
    }

    $fullGrpCnt = 8 - count($freeSlot);
    
    if($freeSlot) {
        $grp = Util::choiceRandom(array_keys($freeSlot));
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
} elseif($btn == "랜덤투입") {
    $general = $db->queryFirstRow('SELECT no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 order by rand() limit 1');

    $occupied = [];
    foreach($db->queryFirstColumn('SELECT grp FROM tournament WHERE grp < 10 GROUP BY grp HAVING count(*)=8') as $grp){
        $occupied[$grp] = true;
    }
    $grpCount = count($occupied);

    if($grpCount < 8) {
        $notFullGrp = [];
        foreach(Util::range(8) as $grpIdx){
            if(!($occupied[$grpIdx]??false)){
                $notFullGrp[] = $grpIdx;
            }
        }
        $grp = Util::choiceRandom($notFullGrp);
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
        ], 'no=%i', $general['no']);
    }

    $grpCount = $db->queryFirstField('SELECT count(*) FROM tournament where grp<10 GROUP BY grp HAVING count(*)=8');
    if($grpCount >= 8) {
        $gameStor->tournament = 2;
        $gameStor->phase = 0;
    }

} elseif($btn == "랜덤전부투입") {
    $grpIn = [];
    
    foreach($db->queryAllLists('SELECT grp, count(*) FROM tournament WHERE 0 <= grp AND grp < 8 GROUP BY grp') as [$grpIdx, $cnt]){
        $grpIn[$grpIdx] = $cnt;
        
    }
    $code = [];
    foreach(Util::range(8) as $grpIdx){
        $cnt = $grpIn[$grpIdx]??0;
        foreach(Util::range($cnt, 8) as $grpNo){
            $code[] = $grpIdx * 10 + $grpNo;
        }
    }
    $z = count($code);


    $generals = $db->query('SELECT no,name,npc,leadership,strength,intel,explevel,leadership+strength+intel as total,horse,weapon,book from general where tournament=0 order by rand() limit %i', $z);

    foreach(Util::range($z) as $i){
        $sel = rand() % 32;
        $general = $generals[$i];

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
    }
    $db->update('general', [
        'tournament'=>1,
    ], 'no IN %li', Util::squeezeFromArray($generals, 'no'));

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