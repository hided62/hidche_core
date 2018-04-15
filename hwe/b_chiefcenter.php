<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("사령부", 1);

$query = "select conlimit from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,nation,level,con,turntime,belong from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

if($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

if($me['level'] >= 5) { $btn = "submit"; $btn2 = "button"; }
else { $btn = "hidden"; $btn2 = "hidden"; }

$date = date('Y-m-d H:i:s');

// 명령 목록
$query = "select year,month,turnterm from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "
    select nation,level,
    l12turn0,l12turn1,l12turn2,l12turn3,l12turn4,l12turn5,l12turn6,l12turn7,l12turn8,l12turn9,l12turn10,l12turn11,
    l11turn0,l11turn1,l11turn2,l11turn3,l11turn4,l11turn5,l11turn6,l11turn7,l11turn8,l11turn9,l11turn10,l11turn11,
    l10turn0,l10turn1,l10turn2,l10turn3,l10turn4,l10turn5,l10turn6,l10turn7,l10turn8,l10turn9,l10turn10,l10turn11,
    l9turn0, l9turn1, l9turn2, l9turn3, l9turn4, l9turn5, l9turn6, l9turn7, l9turn8, l9turn9, l9turn10, l9turn11,
    l8turn0, l8turn1, l8turn2, l8turn3, l8turn4, l8turn5, l8turn6, l8turn7, l8turn8, l8turn9, l8turn10, l8turn11,
    l7turn0, l7turn1, l7turn2, l7turn3, l7turn4, l7turn5, l7turn6, l7turn7, l7turn8, l7turn9, l7turn10, l7turn11,
    l6turn0, l6turn1, l6turn2, l6turn3, l6turn4, l6turn5, l6turn6, l6turn7, l6turn8, l6turn9, l6turn10, l6turn11,
    l5turn0, l5turn1, l5turn2, l5turn3, l5turn4, l5turn5, l5turn6, l5turn7, l5turn8, l5turn9, l5turn10, l5turn11
    from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

$lv = getNationChiefLevel($nation['level']);
$turn = [];
$gen = [];
for($i=12; $i >= $lv; $i--) {
    $turn[$i] = getCoreTurn($nation, $i);

    $query = "select name,turntime,npc from general where level={$i} and nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen[$i] = MYDB_fetch_array($result);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>사령부</title>
<link rel='stylesheet' href='../d_shared/common.css' type='text/css'>
<link rel='stylesheet' href='css/common.css' type='text/css'>
<script type="text/javascript">
function turn(type) {
    if(type == 0) location.replace('turn_push_core.php');
    else if(type == 1) location.replace('turn_pop_core.php');
}
</script>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>사 령 부<input type=button value='갱신' onclick=location.replace('b_chiefcenter.php')><br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=10 align=center bgcolor=skyblue>수뇌부 일정</td></tr>
    <tr><td colspan=10 align=center>
<?php
$year = $admin['year'];
$month = $admin['month'];
$date = substr(date('Y-m-d H:i:s'), 14);

$totaldate = [];
$turntime = [];
$turndate = [];

for($i=12; $i >= $lv; $i--) {
    $totaldate[$i] = $gen[$i]['turntime'];
    $turntime[$i] = substr($gen[$i]['turntime'], 14);
}


//FIXME: 각 칸을 div로 놓으면 네개씩 출력하는 삽질이 필요없다.

for($k=0; $k < 2; $k++) {
    $l4 = 12 - $k;  $l3 = 10 - $k;  $l2 =  8 - $k;  $l1 =  6 - $k;

    if(!isset($gen[$l4])){
        $gen[$l4] = [
            'npc'=>0,
            'name'=>0
        ];
    }

    if    ($gen[$l4]['npc'] >= 2) { $gen[$l4]['name'] = "<font color=cyan>".$gen[$l4]['name']."</font>"; }
    elseif($gen[$l4]['npc'] == 1) { $gen[$l4]['name'] = "<font color=skyblue>".$gen[$l4]['name']."</font>"; }
    if    ($gen[$l3]['npc'] >= 2) { $gen[$l3]['name'] = "<font color=cyan>".$gen[$l3]['name']."</font>"; }
    elseif($gen[$l3]['npc'] == 1) { $gen[$l3]['name'] = "<font color=skyblue>".$gen[$l3]['name']."</font>"; }
    if    ($gen[$l2]['npc'] >= 2) { $gen[$l2]['name'] = "<font color=cyan>".$gen[$l2]['name']."</font>"; }
    elseif($gen[$l2]['npc'] == 1) { $gen[$l2]['name'] = "<font color=skyblue>".$gen[$l2]['name']."</font>"; }
    if    ($gen[$l1]['npc'] >= 2) { $gen[$l1]['name'] = "<font color=cyan>".$gen[$l1]['name']."</font>"; }
    elseif($gen[$l1]['npc'] == 1) { $gen[$l1]['name'] = "<font color=skyblue>".$gen[$l1]['name']."</font>"; }

    echo "
    <tr>
        <td align=center id=bg1>.</td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l4, $nation['level'])." : {$gen[$l4]['name']}</b></td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l3, $nation['level'])." : {$gen[$l3]['name']}</b></td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l2, $nation['level'])." : {$gen[$l2]['name']}</b></td>
        <td colspan=2 align=center id=bg1><b>".getLevel($l1, $nation['level'])." : {$gen[$l1]['name']}</b></td>
        <td align=center id=bg1>.</td>
    </tr>
    ";

    for($i=0; $i < 12; $i++) {
        $turndate[$l4] = substr($totaldate[$l4]??'', 11, 5);
        $turndate[$l3] = substr($totaldate[$l3]??'', 11, 5);
        $turndate[$l2] = substr($totaldate[$l2]??'', 11, 5);
        $turndate[$l1] = substr($totaldate[$l1]??'', 11, 5);
        $j = $i + 1;
        $td4 = $turndate[$l4] ?: "-";
        $td3 = $turndate[$l3] ?: "-";
        $td2 = $turndate[$l2] ?: "-";
        $td1 = $turndate[$l1] ?: "-";
        $tn4 = $turn[$l4][$i] ?? "-";
        $tn3 = $turn[$l3][$i] ?? "-";
        $tn2 = $turn[$l2][$i] ?? "-";
        $tn1 = $turn[$l1][$i] ?? "-";
        echo "
    <tr>
        <td width=28  align=center id=bg0><b>$j</b></td>
        <td width=58  align=center bgcolor=black><b>$td4</b></td>
        <td width=173 align=center height=24 style=table-layout:fixed; id=bg2>$tn4</td>
        <td width=58  align=center bgcolor=black><b>$td3</b></td>
        <td width=173 align=center height=24 style=table-layout:fixed; id=bg2>$tn3</td>
        <td width=58  align=center bgcolor=black><b>$td2</b></td>
        <td width=173 align=center height=24 style=table-layout:fixed; id=bg2>$tn2</td>
        <td width=58  align=center bgcolor=black><b>$td1</b></td>
        <td width=173 align=center height=24 style=table-layout:fixed; id=bg2>$tn1</td>
        <td width=28  align=center id=bg0><b>$j</b></td>
    </tr>
        ";
        if($totaldate[$l4] != "") { $totaldate[$l4] = addTurn($totaldate[$l4], $admin['turnterm']); }
        if($totaldate[$l3] != "") { $totaldate[$l3] = addTurn($totaldate[$l3], $admin['turnterm']); }
        if($totaldate[$l2] != "") { $totaldate[$l2] = addTurn($totaldate[$l2], $admin['turnterm']); }
        if($totaldate[$l1] != "") { $totaldate[$l1] = addTurn($totaldate[$l1], $admin['turnterm']); }
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
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

