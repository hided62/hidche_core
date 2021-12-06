<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 9);

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("장수일람", 2);

$me = $db->queryFirstRow('SELECT con,turntime FROM general WHERE owner = %i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

if ($type <= 0 || $type > 15) {
    $type = 9;
}

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
<title><?=UniqueConst::$serverName?>: 장수일람</title>
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
    <tr><td>장 수 일 람<br><?=closeButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select id='viewType' name='type' size=1>
            <option value=1>국가</option>
            <option value=2>통솔</option>
            <option value=3>무력</option>
            <option value=4>지력</option>
            <option value=5>명성</option>
            <option value=6>계급</option>
            <option value=7>관직</option>
            <option value=8>삭턴</option>
            <option value=9>벌점</option>
            <option value=10>Lv</option>
            <option value=11>성격</option>
            <option value=12>내특</option>
            <option value=13>전특</option>
            <option value=14>연령</option>
            <option value=15>NPC</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php
$nationname = [];
$nationlevel = [];
$nationname[0] = "-";
foreach (getAllNationStaticInfo() as $nation) {
    $nationname[$nation['nation']] = $nation['name'];
    $nationlevel[$nation['nation']] = $nation['level'];
}


[$orderKey, $orderDesc] = [
    1=>['nation', false],
    2=>['leadership', true],
    3=>['strength', true],
    4=>['intel', true],
    5=>['experience', true],
    6=>['dedication', true],
    7=>['officer_level', true],
    8=>['killturn', false],
    9=>['connect', true],
    10=>['experience', true],
    11=>['personal', true],
    12=>['special', true],
    13=>['special2', true],
    14=>['age', true],
    15=>['npc', true],
][$type];

$generalList = $db->query('SELECT owner,no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leadership,strength,intel,experience,dedication,officer_level,killturn,connect from general order by %b %l', $orderKey, $orderDesc?'desc':'');

echo"
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td width=64  align=center class='bg1'>얼 굴</td>
        <td width=140 align=center class='bg1'>이 름</td>
        <td width=45 align=center class='bg1'>연령</td>
        <td width=45 align=center class='bg1'>성격</td>
        <td width=80 align=center class='bg1'>특기</td>
        <td width=45 align=center class='bg1'>레 벨</td>
        <td width=140 align=center class='bg1'>국 가</td>
        <td width=55 align=center class='bg1'>명 성</td>
        <td width=55 align=center class='bg1'>계 급</td>
        <td width=75 align=center class='bg1'>관 직</td>
        <td width=45 align=center class='bg1'>통솔</td>
        <td width=45 align=center class='bg1'>무력</td>
        <td width=45 align=center class='bg1'>지력</td>
        <td width=45 align=center class='bg1'>삭턴</td>
        <td width=70 align=center class='bg1'>벌점</td>
    </tr>";
foreach($generalList as $general){
    $nation = $nationname[$general['nation']];

    $lbonus = calcLeadershipBonus($general['officer_level'], $nationlevel[$general['nation']]??0);
    if ($lbonus > 0) {
        $lbonusText = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonusText = "";
    }

    if ($general['injury'] > 0) {
        $leadership = intdiv($general['leadership'] * (100 - $general['injury']), 100);
        $strength = intdiv($general['strength'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
        $leadership = "<font color=red>{$leadership}</font>{$lbonusText}";
        $strength = "<font color=red>{$strength}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leadership = "{$general['leadership']}{$lbonusText}";
        $strength = "{$general['strength']}";
        $intel = "{$general['intel']}";
    }



    if ($general['npc'] >= 2) {
        $name = "<font color=cyan>{$general['name']}</font>";
    } elseif ($general['npc'] == 1) {
        $name = "<font color=skyblue>{$general['name']}</font>";
    } else {
        $name =  "{$general['name']}";
    }

    if(key_exists($general['owner'], $ownerNameList)){
        $name = $name.'<br><small>('.$ownerNameList[$general['owner']].')</small>';
    }

    $general['connect'] = Util::round($general['connect'], -1);

    $imageTemp = GetImageURL($general['imgsvr']);
    echo "
    <tr data-general-id='{$general['no']}'
        data-general-wounded='{$general['injury']}'
        data-general-leadership='{$general['leadership']}'
        data-general-leadership-bonus='{$lbonus}'
        data-general-strength='{$general['strength']}'
        data-general-intel='{$general['intel']}'
        data-is-npc='".($general['npc']>=2?'true':'false')."'
    >
        <td align=center><img class='generalIcon' width='64' height='64' src='{$imageTemp}/{$general['picture']}'></img></td>
        <td align=center>$name</td>
        <td align=center>{$general['age']}세</td>
        <td align=center>".displayCharInfo($general['personal'])."</td>
        <td align=center>".displaySpecialDomesticInfo($general['special'])." / ".displaySpecialWarInfo($general['special2'])."</td>
        <td align=center>Lv ".getExpLevel($general['experience'])."</td>
        <td align=center>{$nation}</td>
        <td align=center>".getHonor($general['experience'])."</td>
        <td align=center>".getDed($general['dedication'])."</td>
        <td align=center>";
    echo getOfficerLevelText($general['officer_level']);
    echo "</td>
        <td align=center>$leadership</td>
        <td align=center>$strength</td>
        <td align=center>$intel</td>
        <td align=center>{$general['killturn']}</td>
        <td align=center>{$general['connect']}";
    echo "<br>【".getConnect($general['connect'])."】</td>
    </tr>";
}
echo "
</table>
";

?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<script type="text/javascript">
$(document).ready(function() {
    $("#viewType").val("<?=$type?>").attr("selected", "selected");
});
</script>
</body>

</html>
