<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("베팅장", 1);
checkTurn();

$query = "select no,tournament,con,turntime,bet0+bet1+bet2+bet3+bet4+bet5+bet6+bet7+bet8+bet9+bet10+bet11+bet12+bet13+bet14+bet15 as bet from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$query = "select conlimit,tournament,phase,tnmt_type,develcost,bet0,bet1,bet2,bet3,bet4,bet5,bet6,bet7,bet8,bet9,bet10,bet11,bet12,bet13,bet14,bet15,bet0+bet1+bet2+bet3+bet4+bet5+bet6+bet7+bet8+bet9+bet10+bet11+bet12+bet13+bet14+bet15 as bet from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$admin = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

switch ($admin['tnmt_type']) {
default: throw new \RuntimeException('Invalid tnmt_type');break;
case 0: $tnmt_type = "<font color=cyan>전력전</font>"; $tp = "tot"; $tp2 = "종합"; $tp3 = "total"; break;
case 1: $tnmt_type = "<font color=cyan>통솔전</font>"; $tp = "ldr"; $tp2 = "통솔"; $tp3 = "leader"; break;
case 2: $tnmt_type = "<font color=cyan>일기토</font>"; $tp = "pwr"; $tp2 = "무력"; $tp3 = "power"; break;
case 3: $tnmt_type = "<font color=cyan>설전</font>";   $tp = "itl"; $tp2 = "지력"; $tp3 = "intel"; break;
}

$str1 = getTournament($admin['tournament']);
$str2 = getTournamentTime();
if($str2){
    $str2 = ', '.$str2;
}
$str3 = getTournamentTerm();
if($str3){
    $str3 = ', '.$str3;
}

?>
<!DOCTYPE html>
<html>
<?php if ($con == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다!");
} ?>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title><?=UniqueConst::$serverName?>: 베팅장</title>
<link href="../d_shared/common.css" rel="stylesheet">
<link href="css/common.css?180512" rel="stylesheet">
</head>

<body>
<table align=center width=1120 class='tb_layout bg0'>
    <tr><td>베 팅 장<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1120 class='tb_layout bg0'>
    <tr><td colspan=16><input type=button value='갱신' onclick='location.reload()'></td></tr>
    <tr><td colspan=16 align=center><font color=white size=6><?=$tnmt_type?> (<?=$str1.$str2.$str3?>)</font></td></tr>
    <tr><td height=50 colspan=16 align=center id=bg2><font color=limegreen size=6>16강 상황</font><br><font color=orange size=3>(전체 금액 : <?=$admin['bet']?> / 내 투자 금액 : <?=$me['bet']?>)</font></td></tr>
</table>
<table align=center width=1120 class='mimic_flex bg0' style='border:solid 1px gray;font-size:10px;'>
    <tr align=center><td height=10 colspan=16></td></tr>
    <tr align=center>
<?php
$query = "select npc,name,win from tournament where grp>=60 order by grp, grp_no";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
for ($i=0; $i < 1; $i++) {
    $general = MYDB_fetch_array($result)??[
        'npc'=>0,
        'name'=>'',
        'win'=>0
    ];
    if ($general['name'] == "") {
        $general['name'] = "-";
    }
    if ($general['npc'] >= 2) {
        $general['name'] = "<font color=cyan>".$general['name']."</font>";
    } elseif ($general['npc'] == 1) {
        $general['name'] = "<font color=skyblue>".$general['name']."</font>";
    }
    echo "<td colspan=16>{$general['name']}</td>";
}

echo "
    </tr>
    <tr align=center>";

$query = "select npc,name,win from tournament where grp>=50 order by grp, grp_no";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$cent = [];
$line = [];
$gen = [];
for ($i=0; $i < 1; $i++) {
    $cent[$i] = "<font color=white>";
}
for ($i=0; $i < 2; $i++) {
    $general = MYDB_fetch_array($result)??[
        'npc'=>0,
        'name'=>'',
        'win'=>0
    ];
    if ($general['name'] == "") {
        $general['name'] = "-";
    }
    if ($general['npc'] >= 2) {
        $general['name'] = "<font color=cyan>".$general['name']."</font>";
    } elseif ($general['npc'] == 1) {
        $general['name'] = "<font color=skyblue>".$general['name']."</font>";
    }
    if ($general['win'] > 0) {
        $line[$i] = "<font color=red>";
        $cent[intdiv($i, 2)] = "<font color=red>";
    } else {
        $line[$i] = "<font color=white>";
    }
    $gen[$i] = $general['name'];
}
for ($i=0; $i < 1; $i++) {
    $cent[$i] = $cent[$i]."┻"."</font>";
    $line[$i*2] =     $line[$i*2]."┏━━━━━━━━━━━━━━━━━━━━━━━━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━━━━━━━━━━━━━━━━━━━━━━━━┓"."</font>";
    echo "<td colspan=16>{$line[$i*2]}{$cent[$i]}{$line[$i*2+1]}</td>";
}
echo "
    </tr>
    <tr align=center>";

for ($i=0; $i < 2; $i++) {
    echo "<td colspan=8>{$gen[$i]}</td>";
}

echo "
    </tr>
    <tr align=center>";

$query = "select npc,name,win from tournament where grp>=40 order by grp, grp_no";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
for ($i=0; $i < 2; $i++) {
    $cent[$i] = "<font color=white>";
}
for ($i=0; $i < 4; $i++) {
    $general = MYDB_fetch_array($result)??[
        'npc'=>0,
        'name'=>'',
        'win'=>0
    ];
    if ($general['name'] == "") {
        $general['name'] = "-";
    }
    if ($general['npc'] >= 2) {
        $general['name'] = "<font color=cyan>".$general['name']."</font>";
    } elseif ($general['npc'] == 1) {
        $general['name'] = "<font color=skyblue>".$general['name']."</font>";
    }
    if ($general['win'] > 0) {
        $line[$i] = "<font color=red>";
        $cent[intdiv($i, 2)] = "<font color=red>";
    } else {
        $line[$i] = "<font color=white>";
    }
    $gen[$i] = $general['name'];
}
for ($i=0; $i < 2; $i++) {
    $cent[$i] = $cent[$i]."┻"."</font>";
    $line[$i*2] =     $line[$i*2]."┏━━━━━━━━━━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━━━━━━━━━━┓"."</font>";
    echo "<td colspan=8>{$line[$i*2]}{$cent[$i]}{$line[$i*2+1]}</td>";
}
echo "
    </tr>
    <tr align=center>";

for ($i=0; $i < 4; $i++) {
    echo "<td colspan=4>{$gen[$i]}</td>";
}

echo "
    </tr>
    <tr align=center>";

$query = "select npc,name,win from tournament where grp>=30 order by grp, grp_no";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
for ($i=0; $i < 4; $i++) {
    $cent[$i] = "<font color=white>";
}
for ($i=0; $i < 8; $i++) {
    $general = MYDB_fetch_array($result)??[
        'npc'=>0,
        'name'=>'',
        'win'=>0
    ];
    if ($general['name'] == "") {
        $general['name'] = "-";
    }
    if ($general['npc'] >= 2) {
        $general['name'] = "<font color=cyan>".$general['name']."</font>";
    } elseif ($general['npc'] == 1) {
        $general['name'] = "<font color=skyblue>".$general['name']."</font>";
    }
    if ($general['win'] > 0) {
        $line[$i] = "<font color=red>";
        $cent[intdiv($i, 2)] = "<font color=red>";
    } else {
        $line[$i] = "<font color=white>";
    }
    $gen[$i] = $general['name'];
}
for ($i=0; $i < 4; $i++) {
    $cent[$i] = $cent[$i]."┻"."</font>";
    $line[$i*2] =     $line[$i*2]."┏━━━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━━━┓"."</font>";
    echo "<td colspan=4>{$line[$i*2]}{$cent[$i]}{$line[$i*2+1]}</td>";
}
echo "
    </tr>
    <tr align=center>";

for ($i=0; $i < 8; $i++) {
    echo "<td colspan=2>{$gen[$i]}</td>";
}

echo "
    </tr>
    <tr align=center>";

$query = "select npc,name,win from tournament where grp>=20 order by grp, grp_no";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
for ($i=0; $i < 8; $i++) {
    $cent[$i] = "<font color=white>";
}
for ($i=0; $i < 16; $i++) {
    $general = MYDB_fetch_array($result)??[
        'npc'=>0,
        'name'=>'',
        'win'=>0
    ];
    if ($general['name'] == "") {
        $general['name'] = "-";
    }
    if ($general['npc'] >= 2) {
        $general['name'] = "<font color=cyan>".$general['name']."</font>";
    } elseif ($general['npc'] == 1) {
        $general['name'] = "<font color=skyblue>".$general['name']."</font>";
    }
    if ($general['win'] > 0) {
        $line[$i] = "<font color=red>";
        $cent[intdiv($i, 2)] = "<font color=red>";
    } else {
        $line[$i] = "<font color=white>";
    }
    $gen[$i] = $general['name'];
}
for ($i=0; $i < 8; $i++) {
    $cent[$i] = $cent[$i]."┻"."</font>";
    $line[$i*2] =     $line[$i*2]."┏━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━┓"."</font>";
    echo "<td colspan=2>{$line[$i*2]}{$cent[$i]}{$line[$i*2+1]}</td>";
}
echo "
    </tr>
    <tr align=center>";

for ($i=0; $i < 16; $i++) {
    echo "<td width=70>{$gen[$i]}</td>";
}

$query = "select bet0,bet1,bet2,bet3,bet4,bet5,bet6,bet7,bet8,bet9,bet10,bet11,bet12,bet13,bet14,bet15 from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);
$myBet = [];
$bet = [];
$gold = [];

for ($i=0; $i < 16; $i++) {
    $myBet[$i]  = $me["bet{$i}"];
}

for ($i=0; $i < 16; $i++) {
    if($admin["bet{$i}"] == 0){
        $bet[$i] = "∞";
    }
    else{
        $bet[$i]  = round($admin['bet'] /  $admin["bet{$i}"], 2);
    }
}

for ($i=0; $i < 16; $i++) {
    $gold[$i] = Util::round($myBet[$i] * $bet[$i]);
}
?>
    </tr>
</table>
<table align=center width=1120 class='tb_layout bg0'>
    <tr align=center><td height=10 colspan=16></td></tr>
<?php
echo "
    <tr align=center>";

for ($i=0; $i < 16; $i++) {
    echo "<td width=70><font color=skyblue>{$bet[$i]}</font></td>";
}
?>
    </tr>
    <tr align=center>
        <td>×</td><td>×</td><td>×</td><td>×</td><td>×</td><td>×</td><td>×</td><td>×</td>
        <td>×</td><td>×</td><td>×</td><td>×</td><td>×</td><td>×</td><td>×</td><td>×</td>
    </tr>
    <tr align=center>
<?php
for ($i=0; $i < 16; $i++) {
    echo "<td><font color=orange>{$myBet[$i]}</font></td>";
}
?>
    </tr>
    <tr align=center>
        <td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td>
        <td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td><td>∥</td>
    </tr>
    <tr align=center>
<?php
for ($i=0; $i < 16; $i++) {
    echo "<td><font color=cyan>{$gold[$i]}</font></td>";
}

echo "
    </tr>
    <tr align=center><td height=10 colspan=16></td></tr>";

if ($admin['tournament'] == 6) {
    echo "
<form method=post action=c_betting.php>
    <tr align=center>";

    for ($i=0; $i < 16; $i++) {
        echo "
        <td>
            <select size=1 name=gold{$i} style=color:white;background-color:black;>
                <option style=color:white; value=10>금10</option>
                <option style=color:white; value=20>금20</option>
                <option style=color:white; value=50>금50</option>
                <option style=color:white; value=100>금100</option>
                <option style=color:white; value=200>금200</option>
                <option style=color:white; value=500>금500</option>
                <option style=color:white; value=1000>최대</option>
            </select>
        </td>";
    }

    echo "
    </tr>
    <tr align=center>";

    for ($i=0; $i < 16; $i++) {
        echo "
        <td><input type=submit name=btn{$i} value=베팅! style=width:100%;color:white;background-color:black;></td>";
    }

    echo "
    </tr>
</form>";
}

?>
    <tr align=center>
        <td height=30 colspan=16>
            <font color=skyblue size=4>배당률</font> × <font color=orange size=4>베팅금</font> = <font color=cyan size=4>적중시 환수금</font><br>
            <font color=skyblue size=4>( 베팅후 500원 이하일땐 베팅이 불가능합니다. )</font>
        </td>
    </tr>
    <tr align=center><td height=10 colspan=16></td></tr>
</table>
<table align=center width=1120 class='tb_layout bg0'>
    <tr align=center><td height=50 colspan=4 id=bg2><font color=yellow size=6>토너먼트 랭킹</font></td></tr>
    <tr align=center><td colspan=4 id=bg2><font color=skyblue size=3>순위 / 장수명 / 능력치 / 경기수 / 승리 / 무승부 / 패배 / 집계점수 / 우승횟수</font></td></tr>
    <tr align=center>
<?php

$type1 = array("전 력 전", "통 솔 전", "일 기 토", "설 전");
$type2 = array("종합", "통솔", "무력", "지력");
$type3 = array("tt", "tl", "tp", "ti");
$type4 = array("total", "leader", "power", "intel");

for ($i=0; $i < 4; $i++) {
    $grp = $i;
    echo "
        <td>
            <table align=center width=280 class='tb_layout bg0'>
                <tr><td colspan=9 align=center style=color:white;background-color:black;><font size=4>{$type1[$i]}</font></td></tr>
                <tr id=bg1><td align=center>순</td><td align=center>장수</td><td align=center>{$type2[$i]}</td><td align=center>경</td><td align=center>승</td><td align=center>무</td><td align=center>패</td><td align=center>점</td><td align=center>勝</td></tr>";

    $query = "select npc,name,leader,power,intel,leader+power+intel as total,{$type3[$i]}p as prize,{$type3[$i]}w+{$type3[$i]}d+{$type3[$i]}l as game,{$type3[$i]}w as win,{$type3[$i]}d as draw,{$type3[$i]}l as lose,{$type3[$i]}g as gl from general order by gl desc, game desc, win desc, draw desc, lose, no limit 0,30";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    for ($k=1; $k <= 30; $k++) {
        $general = MYDB_fetch_array($result);
        printRow($k, $general['npc'], $general['name'], $general[$type4[$i]], $general['game'], $general['win'], $general['draw'], $general['lose'], $general['gl'], $general['prize'], 0);
    }
    echo "
            </table>
        </td>";
}

?>
    </tr>
    <tr>
        <td colspan=16>
ㆍ토너먼트의 16강 대진표가 완성되면, 베팅 기간이 주어집니다.<br>
ㆍ유저들의 베팅 상황에 따라 배당률이 실시간 결정되며, 자신의 베팅금에 따른 예상 환급금을 알 수 있습니다.<br>
ㆍ베팅은 16슬롯에 각각 베팅 가능하며, 도합 최대 금 1000씩 베팅 가능합니다.<br>
ㆍ소지금 500원 이하일땐 베팅이 불가능합니다.
ㆍ삼모와 더불어 토너먼트, 베팅기능으로 즐거운 삼모 되세요!<br>
        </td>
    </tr>
</table>
<table align=center width=1120 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
