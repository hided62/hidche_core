<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 9);

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("장수일람", 2);

$query = "select con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

if ($type <= 0 || $type > 15) {
    $type = 9;
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
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/common.js')?>

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

switch ($type) { //FIXME:  $query 처리 조심.
    case  1: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by nation"; break;
    case  2: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by leader desc"; break;
    case  3: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by power desc"; break;
    case  4: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by intel desc"; break;
    case  5: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by experience desc"; break;
    case  6: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by dedication desc"; break;
    case  7: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by level desc"; break;
    case  8: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by killturn"; break;
    default:
    case  9: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by connect desc"; break;
    case 10: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by experience desc"; break;
    case 11: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by personal"; break;
    case 12: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by special desc"; break;
    case 13: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by special2 desc"; break;
    case 14: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by age desc"; break;
    case 15: $query = "select no,picture,imgsvr,npc,age,nation,special,special2,personal,name,injury,leader,power,intel,experience,dedication,level,killturn,connect from general order by npc desc"; break;
}
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$gencount = MYDB_num_rows($genresult);

echo"
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td width=64  align=center id=bg1>얼 굴</td>
        <td width=140 align=center id=bg1>이 름</td>
        <td width=45 align=center id=bg1>연령</td>
        <td width=45 align=center id=bg1>성격</td>
        <td width=80 align=center id=bg1>특기</td>
        <td width=45 align=center id=bg1>레 벨</td>
        <td width=140 align=center id=bg1>국 가</td>
        <td width=55 align=center id=bg1>명 성</td>
        <td width=55 align=center id=bg1>계 급</td>
        <td width=75 align=center id=bg1>관 직</td>
        <td width=45 align=center id=bg1>통솔</td>
        <td width=45 align=center id=bg1>무력</td>
        <td width=45 align=center id=bg1>지력</td>
        <td width=45 align=center id=bg1>삭턴</td>
        <td width=70 align=center id=bg1>벌점</td>
    </tr>";
for ($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);
    $nation = $nationname[$general['nation']];

    if ($general['level'] == 12) {
        $lbonus = $nationlevel[$general['nation']] * 2;
    } elseif ($general['level'] >= 5) {
        $lbonus = $nationlevel[$general['nation']];
    } else {
        $lbonus = 0;
    }
    if ($lbonus > 0) {
        $lbonusText = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonusText = "";
    }

    if ($general['injury'] > 0) {
        $leader = intdiv($general['leader'] * (100 - $general['injury']), 100);
        $power = intdiv($general['power'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
        $leader = "<font color=red>{$leader}</font>{$lbonusText}";
        $power = "<font color=red>{$power}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leader = "{$general['leader']}{$lbonusText}";
        $power = "{$general['power']}";
        $intel = "{$general['intel']}";
    }

    if ($general['npc'] >= 2) {
        $name = "<font color=cyan>{$general['name']}</font>";
    } elseif ($general['npc'] == 1) {
        $name = "<font color=skyblue>{$general['name']}</font>";
    } else {
        $name =  "{$general['name']}";
    }

    $general['connect'] = Util::round($general['connect'] / 10) * 10;

    $imageTemp = GetImageURL($general['imgsvr']);
    echo "
    <tr data-general-id='{$general['no']}' 
        data-general-wounded='{$general['injury']}' 
        data-general-leadership='{$general['leader']}'
        data-general-leadership-bonus='{$lbonus}'
        data-general-power='{$general['power']}'
        data-general-intel='{$general['intel']}'
        data-is-npc='".($general['npc']>=2?'true':'false')."'
    >
        <td align=center><img width='64' height='64' src='{$imageTemp}/{$general['picture']}'></img></td>
        <td align=center>$name</td>
        <td align=center>{$general['age']}세</td>
        <td align=center>".displayCharInfo($general['personal'])."</td>
        <td align=center>".displaySpecialInfo($general['special'])." / ".displaySpecialInfo($general['special2'])."</td>
        <td align=center>Lv ".getExpLevel($general['experience'])."</td>
        <td align=center>{$nation}</td>
        <td align=center>".getHonor($general['experience'])."</td>
        <td align=center>".getDed($general['dedication'])."</td>
        <td align=center>";
    echo getLevel($general['level']);
    echo "</td>
        <td align=center>$leader</td>
        <td align=center>$power</td>
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
