<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 10);
if ($type <= 0 || $type > 12) {
    $type = 10;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
increaseRefresh("세력도시", 1);

$me = $db->queryFirstRow('SELECT no,nation,officer_level FROM general WHERE owner=%i', $userID);
$nationID = $me['nation'];

if ($me['officer_level'] == 0) {
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
<?=WebUtil::printCSS('dist_css/common.css')?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/extExpandCity.js')?>

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

$nation = getNationStaticInfo($me['nation']);  //국가정보

$officerList = [];
foreach($db->query('SELECT no,name,npc,city,officer_level,officer_city,belong FROM general WHERE nation = %i AND 2 <= officer_level AND officer_level <= 4', $nationID) as $officer){
    $officerCityID = $officer['officer_city'];
    if(!key_exists($officerCityID, $officerList)){
        $officerList[$officerCityID] = [];
    }
    $officerList[$officerCityID][$officer['officer_level']] = $officer;
}


$cityList = $db->query('SELECT *,pop/pop_max as poprate from city where nation=%i', $nationID);


switch ($type) {
    case  1: break;
    case  2: usort($cityList, function($lhs, $rhs){return $rhs['pop'] <=> $lhs['pop'];}); break;
    case  3: usort($cityList, function($lhs, $rhs){return $rhs['poprate'] <=> $lhs['poprate'];}); break;
    case  4: usort($cityList, function($lhs, $rhs){return $rhs['trust'] <=> $lhs['trust'];}); break;
    case  5: usort($cityList, function($lhs, $rhs){return $rhs['agri'] <=> $lhs['agri'];}); break;
    case  6: usort($cityList, function($lhs, $rhs){return $rhs['comm'] <=> $lhs['comm'];}); break;
    case  7: usort($cityList, function($lhs, $rhs){return $rhs['secu'] <=> $lhs['secu'];}); break;
    case  8: usort($cityList, function($lhs, $rhs){return $rhs['def'] <=> $lhs['def'];}); break;
    case  9: usort($cityList, function($lhs, $rhs){return $rhs['wall'] <=> $lhs['wall'];}); break;
    case 10: usort($cityList, function($lhs, $rhs){return $rhs['trade'] <=> $lhs['trade'];}); break;
    case 11: usort($cityList, function($lhs, $rhs){
        $cmpTrust = $lhs['region'] <=> $rhs['region'];
        if($cmpTrust != 0){
            return $cmpTrust;
        }
        return $rhs['level']<=>$lhs['level'];
    }); break;
    case 12: usort($cityList, function($lhs, $rhs){
        $cmpTrust = $rhs['level'] <=> $lhs['level'];
        if($cmpTrust != 0){
            return $cmpTrust;
        }
        return $lhs['region']<=>$rhs['region'];
    }); break;
}

$region = 0;
$level = 0;

foreach($cityList as $city){
    $cityID = $city['city'];
    if ($city['city'] == $nation['capital']) {
        $city['name'] = "<font color=cyan>[{$city['name']}]</font>";
    }

    $officerQuery = [];
    $officerName = [
        2=>'-',
        3=>'-',
        4=>'-'
    ];

    $cityOfficerList = $officerList[$cityID]??[];
    foreach($cityOfficerList as $cityOfficer){
        $officerName[$cityOfficer['officer_level']] = getColoredName($cityOfficer['name'], $cityOfficer['npc']);
    }

    if ($type == 10 && $city['region'] != $region) {
        echo "<br>";
        $region = $city['region'];
    } elseif ($type == 11 && $city['level'] != $level) {
        echo "<br>";
        $level = $city['level'];
    }

    if ($city['trade'] === null) {
        $city['trade'] = "- ";
    }

    echo "
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=12 style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}><font size=2>【 ".CityConst::$regionMap[$city['region']]." | ".CityConst::$levelMap[$city['level']]." 】 {$city['name']}</font></td>
    </tr>
    <tr>
        <td align=center width=46 class='bg1'>주민</td>
        <td align=center width=140>{$city['pop']}/{$city['pop_max']}</td>
        <td align=center width=46 class='bg1'>농업</td>
        <td align=center width=140>{$city['agri']}/{$city['agri_max']}</td>
        <td align=center width=46 class='bg1'>상업</td>
        <td align=center width=140>{$city['comm']}/{$city['comm_max']}</td>
        <td align=center width=46 class='bg1'>치안</td>
        <td align=center width=140>{$city['secu']}/{$city['secu_max']}</td>
        <td align=center width=46 class='bg1'>수비</td>
        <td align=center width=140>{$city['def']}/{$city['def_max']}</td>
        <td align=center width=46 class='bg1'>성벽</td>
        <td align=center width=140>{$city['wall']}/{$city['wall_max']}</td>
    </tr>
    <tr>
        <td align=center class='bg1'>민심</td>
        <td align=center>".round($city['trust'], 1)."</td>
        <td align=center class='bg1'>시세</td>
        <td align=center>{$city['trade']}%</td>
        <td align=center class='bg1'>인구</td>
        <td align=center>".round($city['pop']/$city['pop_max']*100, 2)." %</td>
        <td align=center class='bg1'>태수</td>
        <td align=center>";
    echo $officerName[4];
    echo "</td>
        <td align=center class='bg1'>군사</td>
        <td align=center>";
    echo $officerName[3];
    echo "</td>
        <td align=center class='bg1'>종사</td>
        <td align=center>";
    echo $officerName[2];
    echo "</td>
    </tr>
    <tr>
        <td align=center class='bg1'>장수</td>
        <td colspan=11>";
    $generalList = $db->query('SELECT npc, name FROM general WHERE city = %i AND nation = %i', $city['city'], $me['nation']);
    if (!$generalList) {
        echo "-";
    }
    foreach($generalList as $general) {
        echo getColoredName($general['name'], $general['npc']).', ';
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
