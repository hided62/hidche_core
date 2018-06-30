<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 10);
if ($type <= 0 || $type > 12) {
    $type = 10;
}

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("세력도시", 1);

$query = "select no,nation,level from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

if ($me['level'] == 0) {
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
<title><?=UniqueConst::$serverName?>: 세력도시</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../e_lib/jquery-ui.min.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/jquery-ui.min.js')?>
<?=WebUtil::printJS('js/ext.expand_city.js')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>세 력 도 시<br><?=backButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1]??''?> value=1>기본</option>
            <option <?=$sel[2]??''?> value=2>인구</option>
            <option <?=$sel[3]??''?> value=3>인구율</option>
            <option <?=$sel[4]??''?> value=4>민심</option>
            <option <?=$sel[5]??''?> value=5>농업</option>
            <option <?=$sel[6]??''?> value=6>상업</option>
            <option <?=$sel[7]??''?> value=7>치안</option>
            <option <?=$sel[8]??''?> value=8>수비</option>
            <option <?=$sel[9]??''?> value=9>성벽</option>
            <option <?=$sel[10]??''?> value=10>시세</option>
            <option <?=$sel[11]??''?> value=11>지역</option>
            <option <?=$sel[12]??''?> value=12>규모</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php
$query = "select nation from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$nation = getNationStaticInfo($me['nation']);  //국가정보

$query = "select *,pop/pop2 as poprate from city where nation='{$me['nation']}'";

switch ($type) {
    case  1: break;
    case  2: $query .= " order by pop desc"; break;
    case  3: $query .= " order by poprate desc"; break;
    case  4: $query .= " order by rate desc"; break;
    case  5: $query .= " order by agri desc"; break;
    case  6: $query .= " order by comm desc"; break;
    case  7: $query .= " order by secu desc"; break;
    case  8: $query .= " order by def desc"; break;
    case  9: $query .= " order by wall desc"; break;
    case 10: $query .= " order by trade desc"; break;
    case 11: $query .= " order by region,level desc"; break;
    case 12: $query .= " order by level desc, region"; break;
}
$cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$citycount = MYDB_num_rows($cityresult);

$region = 0;
$level = 0;

for ($j=0; $j < $citycount; $j++) {
    $city = MYDB_fetch_array($cityresult);
    if ($city['city'] == $nation['capital']) {
        $city['name'] = "<font color=cyan>[{$city['name']}]</font>";
    }
    $query = "select name from general where no='{$city['gen1']}'";    // 태수
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $gen1 = MYDB_fetch_array($genresult);

    $query = "select name from general where no='{$city['gen2']}'";    // 군사
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $gen2 = MYDB_fetch_array($genresult);

    $query = "select name from general where no='{$city['gen3']}'";    // 시중
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $gen3 = MYDB_fetch_array($genresult);

    if ($type == 10 && $city['region'] != $region) {
        echo "<br>";
        $region = $city['region'];
    } elseif ($type == 11 && $city['level'] != $level) {
        echo "<br>";
        $level = $city['level'];
    }

    if ($city['trade'] == 0) {
        $city['trade'] = "- ";
    }

    echo "
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=12 style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}><font size=2>【 ".CityConst::$regionMap[$city['region']]." | ".CityConst::$levelMap[$city['level']]." 】 {$city['name']}</font></td>
    </tr>
    <tr>
        <td align=center width=46 id=bg1>주민</td>
        <td align=center width=140>{$city['pop']}/{$city['pop2']}</td>
        <td align=center width=46 id=bg1>농업</td>
        <td align=center width=140>{$city['agri']}/{$city['agri2']}</td>
        <td align=center width=46 id=bg1>상업</td>
        <td align=center width=140>{$city['comm']}/{$city['comm2']}</td>
        <td align=center width=46 id=bg1>치안</td>
        <td align=center width=140>{$city['secu']}/{$city['secu2']}</td>
        <td align=center width=46 id=bg1>수비</td>
        <td align=center width=140>{$city['def']}/{$city['def2']}</td>
        <td align=center width=46 id=bg1>성벽</td>
        <td align=center width=140>{$city['wall']}/{$city['wall2']}</td>
    </tr>
    <tr>
        <td align=center id=bg1>민심</td>
        <td align=center>{$city['rate']}</td>
        <td align=center id=bg1>시세</td>
        <td align=center>{$city['trade']}%</td>
        <td align=center id=bg1>인구</td>
        <td align=center>".round($city['pop']/$city['pop2']*100, 2)." %</td>
        <td align=center id=bg1>태수</td>
        <td align=center>";
    echo $gen1['name']==''?"-":"{$gen1['name']}";
    echo "</td>
        <td align=center id=bg1>군사</td>
        <td align=center>";
    echo $gen2['name']==''?"-":"{$gen2['name']}";
    echo "</td>
        <td align=center id=bg1>시중</td>
        <td align=center>";
    echo $gen3['name']==''?"-":"{$gen3['name']}";
    echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1>장수</td>
        <td colspan=11>";
    $query = "select npc,name from general where city='{$city['city']}' and nation='{$me['nation']}'";    // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $gencount = MYDB_num_rows($genresult);
    if ($gencount == 0) {
        echo "-";
    }
    for ($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($genresult);
        if ($general['npc'] >= 2) {
            echo "<font color=cyan>{$general['name']}, </font>";
        } elseif ($general['npc'] == 1) {
            echo "<font color=skyblue>{$general['name']}, </font>";
        } else {
            echo "{$general['name']}, ";
        }
    }
    echo "
        </td>
    </tr>
</table>
";
}
?>

<table align=center width=1000 class='tb_layout bg0 anchor'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
<div id="helper_genlist" style="display:none;"></div>
</html>

