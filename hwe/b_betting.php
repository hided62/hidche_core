<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("베팅장", 1);
TurnExecutionHelper::executeAllCommand();

$generalID = $session->generalID;

//NOTE: general_id만 빼기 귀찮음.
$myBet = $db->queryFirstList('SELECT * FROM betting WHERE general_id = %i', $generalID);
$myBet = array_splice($myBet, -16);
$globalBet = $db->queryFirstList('SELECT * FROM betting WHERE general_id = 0');
$globalBet = array_splice($globalBet, -16);

$me = $db->queryFirstRow('SELECT no,tournament,con,turntime from general where owner=%i', $userID);

$myBetTotal = array_sum($myBet);
$globalBetTotal = array_sum($globalBet);

$admin = $gameStor->getValues(['tournament','phase','tnmt_type','develcost']);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

switch ($admin['tnmt_type']) {
default: throw new \RuntimeException('Invalid tnmt_type');
case 0: $tnmt_type = "<font color=cyan>전력전</font>"; $tp = "total"; $tp2 = "종합"; $tp3 = "total"; break;
case 1: $tnmt_type = "<font color=cyan>통솔전</font>"; $tp = "leadership"; $tp2 = "통솔"; $tp3 = "leadership"; break;
case 2: $tnmt_type = "<font color=cyan>일기토</font>"; $tp = "strength"; $tp2 = "무력"; $tp3 = "strength"; break;
case 3: $tnmt_type = "<font color=cyan>설전</font>";   $tp = "intel"; $tp2 = "지력"; $tp3 = "intel"; break;
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
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 베팅장</title>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/betting.js')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('dist_css/common.css')?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
</head>

<body>
<table align=center width=1120 class='tb_layout bg0'>
    <tr><td>베 팅 장<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1120 class='tb_layout bg0'>
    <tr><td colspan=16><input type=button value='갱신' onclick='location.reload()'></td></tr>
    <tr><td colspan=16 align=center><font color=white size=6><?=$tnmt_type?> (<?=$str1.$str2.$str3?>)</font></td></tr>
    <tr><td height=50 colspan=16 align=center class='bg2'><font color=limegreen size=6>16강 상황</font><br><font color=orange size=3>(전체 금액 : <?=$globalBetTotal?> / 내 투자 금액 : <?=$myBetTotal?>)</font></td></tr>
</table>
<table align=center width=1120 class='mimic_flex bg0' style='border:solid 1px gray;font-size:10px;'>
    <tr align=center><td height=10 colspan=16></td></tr>
    <tr align=center>
<?php
$generalList = $db->query('SELECT npc,name,win from tournament where grp>=60 order by grp, grp_no LIMIT 1');
while(count($generalList) < 1){
    $generalList[] = [
        'name'=>'-',
        'npc'=>0,
        'win'=>0
    ];
}
foreach($generalList as $i=>$general){
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

$cent = [];
$line = [];
$gen = [];
for ($i=0; $i < 1; $i++) {
    $cent[$i] = "<font color=white>";
}
$generalList = $db->query('SELECT npc,name,win from tournament where grp>=50 order by grp, grp_no LIMIT 2');
while(count($generalList) < 2){
    $generalList[] = [
        'name'=>'-',
        'npc'=>0,
        'win'=>0
    ];
}
foreach($generalList as $i=>$general){
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

for ($i=0; $i < 2; $i++) {
    $cent[$i] = "<font color=white>";
}
$generalList = $db->query('SELECT npc,name,win from tournament where grp>=40 order by grp, grp_no LIMIT 4');
while(count($generalList) < 4){
    $generalList[] = [
        'name'=>'-',
        'npc'=>0,
        'win'=>0
    ];
}
foreach($generalList as $i=>$general){
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

for ($i=0; $i < 4; $i++) {
    $cent[$i] = "<font color=white>";
}
$generalList = $db->query('SELECT npc,name,win from tournament where grp>=30 order by grp, grp_no LIMIT 8');
while(count($generalList) < 8){
    $generalList[] = [
        'name'=>'-',
        'npc'=>0,
        'win'=>0
    ];
}
foreach($generalList as $i=>$general){
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

for ($i=0; $i < 8; $i++) {
    $cent[$i] = "<font color=white>";
}
$generalList = $db->query('SELECT npc,name,win from tournament where grp>=20 order by grp, grp_no LIMIT 16');
while(count($generalList) < 16){
    $generalList[] = [
        'name'=>'-',
        'npc'=>0,
        'win'=>0
    ];
}
foreach($generalList as $i=>$general){
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

$bet = [];
$gold = [];

for ($i=0; $i < 16; $i++) {
    if($globalBet[$i] == 0){
        $bet[$i] = "∞";
    }
    else{
        $bet[$i]  = round($globalBetTotal /  $globalBet[$i], 2);
    }
}

for ($i=0; $i < 16; $i++) {
    if(!is_numeric($bet[$i])){
        $gold[$i] = 0;
    }
    else{
        $gold[$i] = Util::round($myBet[$i] * $bet[$i]);
    }

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
    <tr align=center>";

    for ($i=0; $i < 16; $i++) {
        echo "
        <td>
            <select size=1 id='target_{$i}' style=color:white;background-color:black;>
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
        <td><input type=button class='submitBtn' data-target='{$i}' value=베팅! style=width:100%;color:white;background-color:black;></td>";
    }

    echo "</tr>";
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
    <tr align=center><td height=50 colspan=4 class='bg2'><font color=yellow size=6>토너먼트 랭킹</font></td></tr>
    <tr align=center><td colspan=4 class='bg2'><font color=skyblue size=3>순위 / 장수명 / 능력치 / 경기수 / 승리 / 무승부 / 패배 / 집계점수 / 우승횟수</font></td></tr>
    <tr align=center>
<?php

$tournamentType = [
    '전 력 전'=>[
        '종합',
        function(General $general){return $general->getVar('leadership')+$general->getVar('strength')+$general->getVar('intel');},
        'tt',
    ],
    '통 솔 전'=>[
        '통솔',
        function(General $general){return $general->getVar('leadership');},
        'tl',
    ],
    '일 기 토'=>[
        '무력',
        function(General $general){return $general->getVar('strength');},
        'ts',
    ],
    '설 전'=>[
        '지력',
        function(General $general){return $general->getVar('intel');},
        'ti',
    ],
];

$type1 = array("전 력 전", "통 솔 전", "일 기 토", "설 전");
$type2 = array("종합", "통솔", "무력", "지력");
$type3 = array("tt", "tl", "ts", "ti");
$type4 = array("total", "leadership", "strength", "intel");

foreach($tournamentType as $tournamentTypeText=>[$statTypeText,$statFunc,$rankColumn]): ?>
        <td>
            <table align=center width=280 class='tb_layout bg0'>
                <tr><td colspan=9 align=center style=color:white;background-color:black;><font size=4><?=$tournamentTypeText?></font></td></tr>
                <tr class='bg1'><td align=center>순</td><td align=center>장수</td><td align=center><?=$statTypeText?></td><td align=center>경</td><td align=center>승</td><td align=center>무</td><td align=center>패</td><td align=center>점</td><td align=center>勝</td></tr>
<?php
    $prizeColumn = "{$rankColumn}p";
    $gameColumn = "{$rankColumn}g";
    $winColumn = "{$rankColumn}w";
    $drawColumn = "{$rankColumn}d";
    $loseColumn = "{$rankColumn}l";
    $tournamentRankerList = General::createGeneralObjListFromDB(
        $db->queryFirstColumn('SELECT general_id FROM rank_data WHERE `type`= %s ORDER BY value DESC LIMIT 40', $gameColumn),
        [$prizeColumn, $gameColumn, $winColumn, $drawColumn, $loseColumn,'leadership', 'strength', 'intel', 'no', 'npc', 'name'],
        0
    );
    usort($tournamentRankerList, function(General $lhs, General $rhs) use($gameColumn, $winColumn, $drawColumn, $loseColumn){
        $result = -($lhs->getRankVar($gameColumn) <=> $rhs->getRankVar($gameColumn));
        if($result !== 0) return $result;
        $result = -(
            ($lhs->getRankVar($winColumn)+$lhs->getRankVar($drawColumn)+$lhs->getRankVar($loseColumn))
            <=>
            ($rhs->getRankVar($winColumn)+$rhs->getRankVar($drawColumn)+$rhs->getRankVar($loseColumn))
        );
        if($result !== 0) return $result;
        $result = -($lhs->getRankVar($winColumn) <=> $rhs->getRankVar($winColumn));
        if($result !== 0) return $result;
        $result = -($lhs->getRankVar($drawColumn) <=> $rhs->getRankVar($drawColumn));
        if($result !== 0) return $result;
        return $lhs->getRankVar($loseColumn) <=> $rhs->getRankVar($loseColumn);
    });
    $tournamentRankerList = array_splice($tournamentRankerList, 0, 30);
    foreach($tournamentRankerList as $rank=>$ranker){
        printRow(
            $rank,
            $ranker->getNPCType(),
            $ranker->getName(),
            ($statFunc)($ranker),
            $ranker->getRankVar($winColumn)+$ranker->getRankVar($drawColumn)+$ranker->getRankVar($loseColumn),
            $ranker->getRankVar($winColumn),
            $ranker->getRankVar($drawColumn),
            $ranker->getRankVar($loseColumn),
            $ranker->getRankVar($gameColumn),
            $ranker->getRankVar($prizeColumn),
            0
        );
    }
?>
    </table></td>
<?php endforeach; ?>
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
