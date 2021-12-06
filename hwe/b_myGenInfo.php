<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 1);
if($type <= 0 || $type > 15) {
    $type = 1;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("세력장수", 1);

$me = $db->queryFirstRow('SELECT no,nation,officer_level from general where owner=%i', $userID);

if($me['officer_level'] == 0) {
    echo "재야입니다.";
    exit();
}

$sel = [$type => "selected"];

$ownerNameList = [];
if($gameStor->isunited){
    foreach(RootDB::db()->queryAllLists('SELECT no, name FROM member') as [$ownerID, $ownerName]){
        $ownerNameList[$ownerID] = $ownerName;
    }
}


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
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/common.js')?>

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

[$orderKey, $orderDesc] = [
    1=>['officer_level', true],
    2=>['dedication', true],
    3=>['experience', true],
    4=>['leadership', true],
    5=>['strength', true],
    6=>['intel', true],
    7=>['gold', true],
    8=>['rice', true],
    9=>['crew', true],
    10=>['connect', true],
    11=>['personal', true],
    12=>['special', true],
    13=>['special2', true],
    14=>['belong', true],
    15=>['npc', true],
][$type];

$generalList = $db->query('SELECT owner,no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leadership,strength,intel,experience,dedication,officer_level,killturn,connect,gold,rice,crew,belong from general where nation = %i order by %b %l', $me['nation'], $orderKey, $orderDesc?'desc':'');

echo"
<table align=center class='tb_layout bg0'>
    <tr>
        <td width=64 align=center class='bg1'>얼 굴</td>
        <td width=98 align=center class='bg1'>이 름</td>
        <td width=78 align=center class='bg1'>관 직</td>
        <td width=68 align=center class='bg1'>계 급</td>
        <td width=68 align=center class='bg1'>명 성</td>
        <td width=68 align=center class='bg1'>봉 록</td>
        <td width=48 align=center class='bg1'>통솔</td>
        <td width=48 align=center class='bg1'>무력</td>
        <td width=48 align=center class='bg1'>지력</td>
        <td width=68 align=center class='bg1'>자 금</td>
        <td width=68 align=center class='bg1'>군 량</td>
        <td width=48 align=center class='bg1'>성 격</td>
        <td width=78 align=center class='bg1'>특 기</td>
        <td width=48 align=center class='bg1'>사 관</td>
        <td width=70 align=center class='bg1'>벌점</td>
    </tr>";
foreach($generalList as $general){

    $lbonus = calcLeadershipBonus($general['officer_level'], $nationLevel);
    if($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    if($general['injury'] > 0) {
        $leadership = intdiv($general['leadership'] * (100 - $general['injury']), 100);
        $strength = intdiv($general['strength'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
        $leadership = "<font color=red>{$leadership}</font>{$lbonus}";
        $strength = "<font color=red>{$strength}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leadership = "{$general['leadership']}{$lbonus}";
        $strength = "{$general['strength']}";
        $intel = "{$general['intel']}";
    }

    if($general['npc'] >= 2) { $name = "<font color=cyan>{$general['name']}</font>"; }
    elseif($general['npc'] == 1) { $name = "<font color=skyblue>{$general['name']}</font>"; }
    else { $name =  "{$general['name']}"; }

    if(key_exists($general['owner'], $ownerNameList)){
        $name = $name.'<br><small>('.$ownerNameList[$general['owner']].')</small>';
    }

    $imageTemp = GetImageURL($general['imgsvr']);
    echo "
    <tr>
        <td align=center class='generalIcon' style='background:no-repeat center url(\"{$imageTemp}/{$general['picture']}\");background-size:64px;' height=64></td>
        <td align=center>$name</td>
        <td align=center>"; echo getOfficerLevelText($general['officer_level'], $nationLevel); echo "</td>
        <td align=center>".getDed($general['dedication'])."</td>
        <td align=center>".getHonor($general['experience'])."</td>
        <td align=center>".getBill($general['dedication'])."</td>
        <td align=center>$leadership</td>
        <td align=center>$strength</td>
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
