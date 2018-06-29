<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("토너먼트", 1);
checkTurn();

$query = "select no,tournament,con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$admin = $gameStor->getValues(['tournament','phase','tnmt_msg','tnmt_type','develcost','tnmt_trig']);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

switch ($admin['tnmt_type']) {
default: throw new \RuntimeException('invalid tnmt_type');
case 0: $tnmt_type = "<font color=cyan>전력전</font>"; $tp = "tot"; $tp2 = "종합"; $tp3 = "total"; break;
case 1: $tnmt_type = "<font color=cyan>통솔전</font>"; $tp = "ldr"; $tp2 = "통솔"; $tp3 = "leader"; break;
case 2: $tnmt_type = "<font color=cyan>일기토</font>"; $tp = "pwr"; $tp2 = "무력"; $tp3 = "power"; break;
case 3: $tnmt_type = "<font color=cyan>설전</font>";   $tp = "itl"; $tp2 = "지력"; $tp3 = "intel"; break;
}

?>
<!DOCTYPE html>
<html>
<?php if ($con == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다! 제한량이 모자라다면 참여를 해보세요^^");
} ?>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?=UniqueConst::$serverName?>: 토너먼트</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>

<body>
<table align=center width=2000 class='tb_layout bg0'>
    <tr><td>삼모전 토너먼트<br><?=closeButton()?></td></tr>
</table>
<table align=center class='tb_layout bg0'>
<?php
if ($session->userGrade >= 5) {
    $sel = [];
    echo "
<form method=post action=c_tournament.php>
    <tr><td colspan=8><input type=textarea size=150 style=color:white;background-color:black; name=msg><input type=submit name=btn value='메시지'></td></tr>
    <tr><td colspan=8>
        <input type=button value='갱신' onclick='location.reload()'>";

    switch ($admin['tnmt_trig']) {
    case 0: $sel[0] = "selected"; break;
    case 1: $sel[1] = "selected"; break;
    case 2: $sel[2] = "selected"; break;
    case 3: $sel[3] = "selected"; break;
    case 4: $sel[4] = "selected"; break;
    case 5: $sel[5] = "selected"; break;
    case 6: $sel[6] = "selected"; break;
    case 7: $sel[7] = "selected"; break;
    }

    if ($admin['tournament'] == 0) {
        ?>
            <select name=auto size=1 style=color:white;background-color:black;>
                <option style=color:white; value=0>수동진행</option>
                <option style=color:white; value=1>12분 05일</option>
                <option style=color:white; value=2>07분 10시</option>
                <option style=color:white; value=3>03분 04시</option>
                <option style=color:white; value=4>01분 82분</option>
                <option style=color:white; value=5>30초 41분</option>
                <option style=color:white; value=6>15초 21분</option>
                <option style=color:white; value=7>05초 07분</option>
            </select>
            <select name=type size=1 style=color:white;background-color:black;>
                <option style=color:white; value=0>전력전</option>
                <option style=color:white; value=1>통솔전</option>
                <option style=color:white; value=2>일기토</option>
                <option style=color:white; value=3>설전</option>
            </select>
            <input type=submit name=btn value='개최'>
            <select name=trig size=1 style=color:white;background-color:black;>
                <option style=color:white; value=0 <?=$sel[0]??''?>>수동진행</option>
                <option style=color:white; value=1 <?=$sel[1]??''?>>12분 05일</option>
                <option style=color:white; value=2 <?=$sel[2]??''?>>07분 10시</option>
                <option style=color:white; value=3 <?=$sel[3]??''?>>03분 04시</option>
                <option style=color:white; value=4 <?=$sel[4]??''?>>01분 82분</option>
                <option style=color:white; value=5 <?=$sel[5]??''?>>30초 41분</option>
                <option style=color:white; value=6 <?=$sel[6]??''?>>15초 21분</option>
                <option style=color:white; value=7 <?=$sel[7]??''?>>05초 07분</option>
            </select>
            <input type=submit name=btn value='자동개최설정'>
            <input type=submit name=btn value='포상'>
            <input type=submit name=btn value='회수'>
        <?php
    } else {
        echo "<input type=submit name=btn value='중단' onclick='return confirm(\"진짜 중단하시겠습니까?\")'>";
    }

    switch ($admin['tournament']) {
    case 1:
        echo "<select name=gen size=1 style=color:white;background-color:black;>";

        $query = "select no,name,npc,tnmt,leader,power,intel,leader+power+intel as total from general where tournament=0 and gold>='{$admin['develcost']}' order by {$tp3} desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
        $genCount = MYDB_num_rows($result);

        for ($i=0; $i < $genCount; $i++) {
            $general = MYDB_fetch_array($result);
            if ($general['npc'] >= 2) {
                $npc = "cyan";
            } elseif ($general['npc'] == 1) {
                $npc = "skyblue";
            } elseif ($general['tnmt'] > 0) {
                $npc = "blue";
            } else {
                $npc = "white";
            }
            echo "<option style=color:{$npc}; value={$general['no']}>[{$general[$tp3]}]{$general['name']}</option>";
        }
        echo "
        </select>
        <input type=submit name=btn value='투입'>
        <input type=submit name=btn value='무명투입'>
        <input type=submit name=btn value='쪼렙투입'>
        <input type=submit name=btn value='일반투입'>
        <input type=submit name=btn value='굇수투입'>
        <input type=submit name=btn value='랜덤투입'>
        <input type=submit name=btn value='쪼렙전부투입'>
        <input type=submit name=btn value='일반전부투입'>
        <input type=submit name=btn value='굇수전부투입'>
        <input type=submit name=btn value='랜덤전부투입'>
        <input type=submit name=btn value='무명전부투입'>";
        break;
    case 2: echo "<input type=submit name=btn value='예선'><input type=submit name=btn value='예선전부'>"; break;
    case 3: echo "<input type=submit name=btn value='추첨'><input type=submit name=btn value='추첨전부'>"; break;
    case 4: echo "<input type=submit name=btn value='본선'><input type=submit name=btn value='본선전부'>"; break;
    case 5: echo "<input type=submit name=btn value='배정'>"; break;
    case 6: echo "<input type=submit name=btn value='베팅마감'>"; break;
    case 7: echo "<input type=submit name=btn value='16강'>"; break;
    case 8: echo "<input type=submit name=btn value='8강'>"; break;
    case 9: echo "<input type=submit name=btn value='4강'>"; break;
    case 10: echo "<input type=submit name=btn value='결승'>"; break;
    }

    echo "
    </td></tr>
</form>";
} elseif ($me['no'] > 0 && $me['tournament'] == 0 && $admin['tournament'] == 1) {
    echo "<form method=post action=c_tournament.php><tr><td colspan=8><input type=button value='갱신' onclick='location.reload()'><input type=submit name=btn value='참가' onclick='return confirm(\"참가비 금{$admin['develcost']}이 필요합니다. 참가하시겠습니까?\")'></td></tr></form>";
} else {
    echo "<tr><td colspan=8><input type=button value='갱신' onclick='location.reload()'></td></tr>";
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
    <tr><td colspan=8>운영자 메세지 : <font color=orange size=5><?=$admin['tnmt_msg']?></font></td></tr>
    <tr><td colspan=8 align=center><font color=white size=6><?=$tnmt_type?> (<?=$str1.$str2.$str3?>)</font></td></tr>
    <tr><td colspan=8 align=center id=bg2><font color=magenta size=5>16강 승자전</font></td></tr>
    <tr><td height=10 colspan=8 align=center></td></tr>
<?php

echo "
    <tr>
        <td colspan=8>
            <table align=center width=2000 class='bg0 mimic_flex'>
               <tr align=center>";

$query = "select npc,name,win from tournament where grp>=60 order by grp, grp_no";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
for ($i=0; $i < 1; $i++) {
    $general = MYDB_fetch_array($result) ?? [
        'name'=>'',
        'npc'=>0,
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
for ($i=0; $i < 1; $i++) {
    $cent[$i] = "<font color=white>";
}
$line = [];
$gen = [];
for ($i=0; $i < 2; $i++) {
    //FIXME: 다시 작성. null인 경우엔 어쩌려고?
    $general = MYDB_fetch_array($result) ?? [
        'name'=>'',
        'npc'=>0,
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
    $line[$i*2] =     $line[$i*2]."┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓"."</font>";
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
    $general = MYDB_fetch_array($result) ?? [
        'name'=>'',
        'npc'=>0,
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
    $line[$i*2] =     $line[$i*2]."┏━━━━━━━━━━━━━━━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━━━━━━━━━━━━━━━┓"."</font>";
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
    $general = MYDB_fetch_array($result) ?? [
        'name'=>'',
        'npc'=>0,
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
    $line[$i*2] =     $line[$i*2]."┏━━━━━━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━━━━━━┓"."</font>";
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
    $general = MYDB_fetch_array($result) ?? [
        'name'=>'',
        'npc'=>0,
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
    $line[$i*2] =     $line[$i*2]."┏━━━━"."</font>";
    $line[$i*2+1] = $line[$i*2+1]."━━━━┓"."</font>";
    echo "<td colspan=2>{$line[$i*2]}{$cent[$i]}{$line[$i*2+1]}</td>";
}
echo "
                </tr>
                <tr align=center>";

for ($i=0; $i < 16; $i++) {
    echo "<td width=125>{$gen[$i]}</td>";
}

echo"
                </tr>";

$betting = $gameStor->getValues(['tournament','bet0','bet1','bet2','bet3','bet4','bet5','bet6','bet7','bet8','bet9','bet10','bet11','bet12','bet13','bet14','bet15']);
$betting['bet'] = 0;
for($i=0;$i<16;$i+=1){
    $betting['bet'] += $betting['bet'.$i];
}
$bet = [];
for ($i=0; $i < 16; $i++) {
    if($betting["bet{$i}"] == 0){
        $bet[$i] = '∞';
        continue;
    }
    $bet[$i]  = round($betting['bet'] /  $betting["bet{$i}"], 2);
}

echo "
                <tr align=center>";

for ($i=0; $i < 16; $i++) {
    echo "<td><font color=skyblue>{$bet[$i]}</font></td>";
}

echo "
                </tr>
                <tr align=center><td height=10 colspan=16></td></tr>
                <tr align=center><td colspan=16><font color=skyblue size=4>배당률이 낮을수록 베팅된 금액이 많고 유저들이 우승후보로 많이 선택한 장수입니다.</font></td></tr>
            </table>
        </td>
    </tr>";

if ($admin['tournament'] >= 7 || $admin['tournament'] == 0) {
    printFighting($admin['tournament'], $admin['phase']);
}
echo "
    <tr><td height=10 colspan=8 align=center></td></tr>
    <tr><td colspan=8 align=center id=bg2><font color=orange size=5>조별 본선 순위</font></td></tr>
    <tr>";

$num = array("一", "二", "三", "四", "五", "六", "七", "八");

for ($i=0; $i < 8; $i++) {
    $grp = $i + 10;
    echo "
        <td>
            <table align=center width=250 class='tb_layout bg0'>
                <tr><td colspan=9 style=background-color:black;>{$num[$i]}조</td></tr>
                <tr id=bg1><td align=center>순</td><td align=center>장수</td><td align=center>{$tp2}</td><td align=center>경</td><td align=center>승</td><td align=center>무</td><td align=center>패</td><td align=center>점</td><td align=center>득</td></tr>";

    $query = "select npc,name,ldr,pwr,itl,ldr+pwr+itl as tot,prmt,win+draw+lose as game,win,draw,lose,gl,win*3+draw as gd from tournament where grp='$grp' order by gd desc, gl desc, seq";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    for ($k=1; $k <= 4; $k++) {
        $general = MYDB_fetch_array($result);
        printRow($k, $general['npc'], $general['name'], $general[$tp], $general['game'], $general['win'], $general['draw'], $general['lose'], $general['gd'], $general['gl'], $general['prmt']);
    }
    echo "
            </table>
        </td>";
}
echo "</tr>";
if ($admin['tournament'] == 4 || $admin['tournament'] == 5) {
    printFighting($admin['tournament'], $admin['phase']);
}
echo "
    <tr><td colspan=8 align=center id=bg2><font color=yellow size=5>조별 예선 순위</font></td></tr>
    <tr>";

for ($i=0; $i < 8; $i++) {
    $grp = $i;
    echo "
        <td>
            <table align=center width=250 class='tb_layout bg0'>
                <tr><td colspan=9 style=background-color:black;>{$num[$i]}조</td></tr>
                <tr id=bg1><td align=center>순</td><td align=center>장수</td><td align=center>{$tp2}</td><td align=center>경</td><td align=center>승</td><td align=center>무</td><td align=center>패</td><td align=center>점</td><td align=center>득</td></tr>";

    $query = "select npc,name,ldr,pwr,itl,ldr+pwr+itl as tot,prmt,win+draw+lose as game,win,draw,lose,gl,win*3+draw as gd from tournament where grp='$grp' order by gd desc, gl desc, seq";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    for ($k=1; $k <= 8; $k++) {
        $general = MYDB_fetch_array($result);
        printRow($k, $general['npc'], $general['name'], $general[$tp], $general['game'], $general['win'], $general['draw'], $general['lose'], $general['gd'], $general['gl'], $general['prmt']);
    }
    echo "
            </table>
        </td>";
}

if ($admin['tournament'] == 2 || $admin['tournament'] == 3) {
    printFighting($admin['tournament'], $admin['phase']);
}

?>
    </tr>
    <tr><td colspan=8>
        <font color=white size=2>
ㆍ예선은 홈&amp;어웨이 풀리그로 진행됩니다. (총 14경기)<br>
ㆍ상위 4명이 본선에 진출하게 되며 조추첨을 통해 조가 배정됩니다.<br>
ㆍ각 조1위가 시드1로 랜덤하게 조에 배정되며, 역시 각 조2위가 시드2로 랜덤하게 조에 배정됩니다.<br>
ㆍ그후 남은 3, 4위는 완전 랜덤하게 모든 조에 랜덤하게 배정됩니다.<br>
ㆍ본선은 개인당 3경기를 치르게 되며 승점(승3, 무1, 패0), 득실, 참가순서(시드)에 따라 순위를 매깁니다.<br>
ㆍ각 조 1, 2위는 16강에 지정된 위치에 배정됩니다.<br>
ㆍ16강부터는 1경기 토너먼트로 진행됩니다.<br>
ㆍ참가비는 금20~140이며, 성적에 따라 금과 약간의 명성이 포상으로 주어집니다.<br>
ㆍ16강자 100, 8강자 300, 4강자 600, 준우승자 1200, 우승자 2000 (220년 기준)<br>
ㆍ즐거운 삼토!
        </font>
    </td></tr>
</table>
<table align=center width=2000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
