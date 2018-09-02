<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 1);
if($type <= 0 || $type > 15) {
    $type = 1;
}

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("세력장수", 1);

$query = "select no,nation,level from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['level'] == 0) {
    echo "재야입니다.";
    exit();
}

$sel = [$type => "selected"];

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 세력장수</title>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>세 력 장 수<br><?=backButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1]??''?> value=1>관직</option>
            <option <?=$sel[2]??''?> value=2>계급</option>
            <option <?=$sel[3]??''?> value=3>명성</option>
            <option <?=$sel[4]??''?> value=4>통솔</option>
            <option <?=$sel[5]??''?> value=5>무력</option>
            <option <?=$sel[6]??''?> value=6>지력</option>
            <option <?=$sel[7]??''?> value=7>자금</option>
            <option <?=$sel[8]??''?> value=8>군량</option>
            <option <?=$sel[9]??''?> value=9>병사</option>
            <option <?=$sel[10]??''?> value=10>벌점</option>
            <option <?=$sel[11]??''?> value=11>성격</option>
            <option <?=$sel[12]??''?> value=12>내특</option>
            <option <?=$sel[13]??''?> value=13>전특</option>
            <option <?=$sel[14]??''?> value=14>사관</option>
            <option <?=$sel[15]??''?> value=15>NPC</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php

$nationLevel = DB::db()->queryFirstField('select level from nation where nation = %i', $me['nation']);
$orderByText = '';//FIXME: 쿼리 재작성
switch($type) {
    case  1: $orderByText = " order by level desc"; break;
    case  2: $orderByText = " order by dedication desc"; break;
    case  3: $orderByText = " order by experience desc"; break;
    case  4: $orderByText = " order by leader desc"; break;
    case  5: $orderByText = " order by power desc"; break;
    case  6: $orderByText = " order by intel desc"; break;
    case  7: $orderByText = " order by gold desc"; break;
    case  8: $orderByText = " order by rice desc"; break;
    case  9: $orderByText = " order by crew desc"; break;
    case 10: $orderByText = " order by connect desc"; break;
    case 11: $orderByText = " order by personal"; break;
    case 12: $orderByText = " order by special desc"; break;
    case 13: $orderByText = " order by special2 desc"; break;
    case 14: $orderByText = " order by belong desc"; break;
    case 15: $orderByText = " order by npc desc"; break;
}
$query = 
    "select 
        npc,
        special,
        special2,
        personal,
        picture,
        imgsvr,
        name,
        level,
        dedication,
        experience,
        injury,
        leader,
        power,
        intel,
        gold,
        rice,
        belong,
        connect,
        killturn
    from general where nation='{$me['nation']}' ".$orderByText;
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

echo"
<table align=center class='tb_layout bg0'>
    <tr>
        <td width=64 align=center id=bg1>얼 굴</td>
        <td width=98 align=center id=bg1>이 름</td>
        <td width=78 align=center id=bg1>관 직</td>
        <td width=68 align=center id=bg1>계 급</td>
        <td width=68 align=center id=bg1>명 성</td>
        <td width=68 align=center id=bg1>봉 록</td>
        <td width=48 align=center id=bg1>통솔</td>
        <td width=48 align=center id=bg1>무력</td>
        <td width=48 align=center id=bg1>지력</td>
        <td width=68 align=center id=bg1>자 금</td>
        <td width=68 align=center id=bg1>군 량</td>
        <td width=48 align=center id=bg1>성 격</td>
        <td width=78 align=center id=bg1>특 기</td>
        <td width=48 align=center id=bg1>사 관</td>
        <td width=70 align=center id=bg1>벌점</td>
    </tr>";
for($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);

    if($general['level'] == 12) {
        $lbonus = $nationLevel * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nationLevel;
    } else {
        $lbonus = 0;
    }
    if($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    if($general['injury'] > 0) {
        $leader = intdiv($general['leader'] * (100 - $general['injury']), 100);
        $power = intdiv($general['power'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
        $leader = "<font color=red>{$leader}</font>{$lbonus}";
        $power = "<font color=red>{$power}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leader = "{$general['leader']}{$lbonus}";
        $power = "{$general['power']}";
        $intel = "{$general['intel']}";
    }

    if($general['npc'] >= 2) { $name = "<font color=cyan>{$general['name']}</font>"; }
    elseif($general['npc'] == 1) { $name = "<font color=skyblue>{$general['name']}</font>"; }
    else { $name =  "{$general['name']}"; }

    $imageTemp = GetImageURL($general['imgsvr']);
    echo "
    <tr>
        <td align=center class='generalIcon' style='background:no-repeat center url(\"{$imageTemp}/{$general['picture']}\");background-size:64px;' height=64></td>
        <td align=center>$name</td>
        <td align=center>"; echo getLevel($general['level'], $nationLevel); echo "</td>
        <td align=center>".getDed($general['dedication'])."</td>
        <td align=center>".getHonor($general['experience'])."</td>
        <td align=center>".getBill($general['dedication'])."</td>
        <td align=center>$leader</td>
        <td align=center>$power</td>
        <td align=center>$intel</td>
        <td align=center>{$general['gold']}</td>
        <td align=center>{$general['rice']}</td>
        <td align=center>".displayCharInfo($general['personal'])."</td>
        <td align=center>".displaySpecialDomesticInfo($general['special'])." / ".displaySpecialWarInfo($general['special2'])."</td>
        <td align=center>{$general['belong']}</td>
        <td align=center>{$general['connect']}"; echo "<br>(".getConnect($general['connect']).")</td>
    </tr>";
}
    echo "
</table>
";

?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>

</html>

