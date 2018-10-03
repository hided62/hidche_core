<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("사령부", 1);

$me = $db->queryFirstRow('SELECT no,nation,level,con,turntime,belong FROM general WHERE owner=%i', $userID);

[$nationLevel, $secretLimit] = $db->queryFirstList('SELECT level, secretlimit FROM nation WHERE nation = %i', $me['nation']);

$con = checkLimit($me['con']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

if($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $secretLimit)) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

if($me['level'] >= 5) { $btn = "submit"; $btn2 = "button"; }
else { $btn = "hidden"; $btn2 = "hidden"; }

$date = TimeUtil::now();

// 명령 목록
$admin = $gameStor->getValues(['year','month','turnterm']);

$lv = getNationChiefLevel($nationLevel);
$turn = [];

$generals = [];
foreach($db->query('SELECT no,name,turntime,npc,city,nation,level FROM general WHERE nation = %i AND level >= 5') as $rawGeneral){
    $generals[$rawGeneral['level']] = new General($rawGeneral, null, $admin['year'], $admin['month'], false);
}

$nationTurnList = [];

foreach(
    $db->queryAllLists(
        'SELECT level, turn_idx, action, arg FROM nation_turn WHERE nation_id = %i ORDER BY level DESC, turn_idx ASC',
        $me['nation']
    ) as [$level, $turn_idx, $action, $arg]
){
    if(!key_exists($level, $nationTurnList)){
        $nationTurnList[$level] = [];
    }
    $nationTurnList[$level][$turn_idx] = [$action, Json::decode($arg)];
}

$nationTurnBrief = [];
foreach($nationTurnList as $level=>$turnList){
    if(!key_exists($level, $generals)){
        $general = Util::array_first($generals);
    }
    else{
        $general = $generals[$level];
    }
    $nationTurnBrief[$level] = getNationTurnBrief($general, $turnList);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 사령부</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<script type="text/javascript">
function turn(type) {
    if(type == 0) location.replace('turn_push_core.php');
    else if(type == 1) location.replace('turn_pop_core.php');
}
</script>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>사 령 부<input type=button value='갱신' onclick=location.replace('b_chiefcenter.php')><br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=10 align=center bgcolor=skyblue>수뇌부 일정</td></tr>
    <tr><td colspan=10 align=center>
<?php
$year = $admin['year'];
$month = $admin['month'];
$date = substr(TimeUtil::now(), 14);

$totaldate = [];
$turntime = [];
$turndate = [];

for($i=12; $i >= $lv; $i--) {
    $totaldate[$i] = $gen[$i]['turntime'];
    $turntime[$i] = substr($gen[$i]['turntime'], 14);
}

//FIXME: 각 칸을 div로 놓으면 네개씩 출력하는 삽질이 필요없다.
//TODO: 새롭게 제작한 $nationTurnBrief와 $generals를 이용하여 출력. div로 변경(사실상 새로 짜라!)
for($k=0; $k < 2; $k++) {
    $l4 = 12 - $k;  $l3 = 10 - $k;  $l2 =  8 - $k;  $l1 =  6 - $k;

    if    ($gen[$l4]['npc'] >= 2) { $gen[$l4]['name'] = "<font color=cyan>".($gen[$l4]['name']??'')."</font>"; }
    elseif($gen[$l4]['npc'] == 1) { $gen[$l4]['name'] = "<font color=skyblue>".($gen[$l4]['name']??'')."</font>"; }
    if    ($gen[$l3]['npc'] >= 2) { $gen[$l3]['name'] = "<font color=cyan>".($gen[$l3]['name']??'')."</font>"; }
    elseif($gen[$l3]['npc'] == 1) { $gen[$l3]['name'] = "<font color=skyblue>".($gen[$l3]['name']??'')."</font>"; }
    if    ($gen[$l2]['npc'] >= 2) { $gen[$l2]['name'] = "<font color=cyan>".($gen[$l2]['name']??'')."</font>"; }
    elseif($gen[$l2]['npc'] == 1) { $gen[$l2]['name'] = "<font color=skyblue>".($gen[$l2]['name']??'')."</font>"; }
    if    ($gen[$l1]['npc'] >= 2) { $gen[$l1]['name'] = "<font color=cyan>".($gen[$l1]['name']??'')."</font>"; }
    elseif($gen[$l1]['npc'] == 1) { $gen[$l1]['name'] = "<font color=skyblue>".($gen[$l1]['name']??'')."</font>"; }

    echo "
    <tr>
        <td align=center id=bg1>.</td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l4, $nationLevel)." : ".($gen[$l4]['name']??'')."</b></td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l3, $nationLevel)." : ".($gen[$l3]['name']??'')."</b></td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l2, $nationLevel)." : ".($gen[$l2]['name']??'')."</b></td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l1, $nationLevel)." : ".($gen[$l1]['name']??'')."</b></td>
        <td align=center id=bg1>.</td>
    </tr>
    ";

    for($i=0; $i < 12; $i++) {
        $turndate[$l4] = substr($totaldate[$l4]??'', 11, 5);
        $turndate[$l3] = substr($totaldate[$l3]??'', 11, 5);
        $turndate[$l2] = substr($totaldate[$l2]??'', 11, 5);
        $turndate[$l1] = substr($totaldate[$l1]??'', 11, 5);
        $j = $i + 1;
        $td4 = $turndate[$l4]??"-";
        $td3 = $turndate[$l3]??"-";
        $td2 = $turndate[$l2]??"-";
        $td1 = $turndate[$l1]??"-";
        $tn4 = $turn[$l4][$i]??"-";
        $tn3 = $turn[$l3][$i]??"-";
        $tn2 = $turn[$l2][$i]??"-";
        $tn1 = $turn[$l1][$i]??"-";
        echo "
    <tr>
        <td width=20  align=center id=bg0><b>$j</b></td>
        <td width=43  align=center bgcolor=black><b>$td4</b></td>
        <td width=192 align=center height=24 style=table-layout:fixed; id=bg2>$tn4</td>
        <td width=43  align=center bgcolor=black><b>$td3</b></td>
        <td width=192 align=center height=24 style=table-layout:fixed; id=bg2>$tn3</td>
        <td width=43  align=center bgcolor=black><b>$td2</b></td>
        <td width=192 align=center height=24 style=table-layout:fixed; id=bg2>$tn2</td>
        <td width=43  align=center bgcolor=black><b>$td1</b></td>
        <td width=192 align=center height=24 style=table-layout:fixed; id=bg2>$tn1</td>
        <td width=20  align=center id=bg0><b>$j</b></td>
    </tr>
        ";
        if($totaldate[$l4]??'') { $totaldate[$l4] = addTurn($totaldate[$l4], $admin['turnterm']); }
        if($totaldate[$l3]??'') { $totaldate[$l3] = addTurn($totaldate[$l3], $admin['turnterm']); }
        if($totaldate[$l2]??'') { $totaldate[$l2] = addTurn($totaldate[$l2], $admin['turnterm']); }
        if($totaldate[$l1]??'') { $totaldate[$l1] = addTurn($totaldate[$l1], $admin['turnterm']); }
    }
    if($k == 0) {
        echo "<form action=processing.php method=post><tr><td colspan=5 align=right>";
        echo CoreTurnTable();
        echo "</td><td colspan=5>
        <input type={$btn2} style=background-color:".GameConst::$basecolor2.";color:white;width:58px;font-size:13px; value='미루기▼' onclick='turn(0)'>
        <input type={$btn2} style=background-color:".GameConst::$basecolor2.";color:white;width:58px;font-size:13px; value='▲당기기' onclick='turn(1)'>
        <br>";
        CoreCommandTable();
        echo "<input type={$btn} style=background-color:".GameConst::$basecolor2.";color:white;width:55px;font-size:13px; value='실 행'></td></tr></form>";
    }
}

?>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>

