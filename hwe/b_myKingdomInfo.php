<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("세력정보", 1);

$nationID = $db->queryFirstField('SELECT nation FROM general WHERE owner=%i', $userID);

if($nationID == 0) {
    echo "재야입니다.";
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 세력정보</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>세 력 정 보<br><?=backButton()?></td></tr>
</table>
<br>
<?php

$nation = $db->queryFirstRow('SELECT nation,gennum,power,rate,bill,type,gold,rice,color,name,level,tech,history,capital FROM nation WHERE nation=%i', $nationID);   //국가정보
$cityList = $db->query('SELECT * FROM city WHERE nation=%i', $nationID);

$currPop = 0;
$maxPop = 0;
$cityNames = [];

foreach($cityList as $city){
    $currPop += $city['pop'];
    $maxPop += $city['pop2'];
    if($city['city'] == $nation['capital']){
        $cityNames[] = "<span style='color:cyan;'>{$city['name']}</span>";
    }
    else{
        $cityNames[] = $city['name'];
    }
}

[$currCrew, $maxCrew] = $db->queryFirstList('SELECT sum(crew), sum(leadership)*100 FROM general WHERE nation=%i AND npc != 5', $nation['nation']);
$dedicationList = $db->query('SELECT dedication FROM general WHERE nation=%i AND npc!=5', $nationID);

$goldIncome  = getGoldIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
$warIncome  = getWarGoldIncome($nation['type'], $cityList);
$totalGoldIncome = $goldIncome + $warIncome;

$riceIncome = getRiceIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
$wallIncome = getWallIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
$totalRiceIncome = $riceIncome + $wallIncome;

$outcome = getOutcome($nation['bill'], $dedicationList);

$budgetgold = $nation['gold'] + $totalGoldIncome - $outcome;
$budgetrice = $nation['rice'] + $totalRiceIncome - $outcome;
$budgetgolddiff = $totalGoldIncome - $outcome;
$budgetricediff = $totalRiceIncome - $outcome;

if ($budgetgolddiff > 0) {
    $budgetgolddiff = '+'.number_format($budgetgolddiff);
} else {
    $budgetgolddiff = number_format($budgetgolddiff);
}
if ($budgetricediff > 0) {
    $budgetricediff = '+'.number_format($budgetricediff);
} else {
    $budgetricediff = number_format($budgetricediff);
}

?>
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=8 align=center style='color:<?=newColor($nation['color'])?>;background-color:<?=$nation['color']?>'>【<?=$nation['name']?>】</td>
    </tr>
    <tr>
        <td width=98 align=center id=bg1>총주민</td>
        <td width=198 align=center><?=number_format($currPop)?>/<?=number_format($maxPop)?></td>
        <td width=98 align=center id=bg1>총병사</td>
        <td width=198 align=center><?=number_format($currCrew)?>/<?=number_format($maxCrew)?></td>
        <td width=98 align=center id=bg1>국 력</td>
        <td width=298 align=center colspan=3><?=$nation['power']?></td>
    </tr>
    <tr>
        <td align=center id=bg1>국 고</td>
        <td align=center><?=number_format($nation['gold'])?></td>
        <td align=center id=bg1>병 량</td>
        <td align=center><?=number_format($nation['rice'])?></td>
        <td align=center id=bg1>세 율</td>
        <td align=center colspan=3><?=$nation['rate']?> %</td>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1>세금/단기</td>
        <td align=center>+<?=number_format($goldIncome)?> / +<?=number_format($warIncome)?></td>
        <td align=center id=bg1>세곡/둔전</td>
        <td align=center>+<?=number_format($riceIncome)?> / +<?=number_format($wallIncome)?></td>
        <td align=center id=bg1>지급률</td>
        <td align=center colspan=3><?=$nation['bill']?> %</td>
    </tr>
    <tr>
        <td align=center id=bg1>수입/지출</td>
        <td align=center>+<?=number_format($totalGoldIncome)?> / -<?=number_format($outcome)?></td>
        <td align=center id=bg1>수입/지출</td>
        <td align=center>+<?=number_format($totalRiceIncome)?> / -<?=number_format($outcome)?></td>
        <td align=center id=bg1>속 령</td>
        <td width=98 align=center><?=count($cityList)?></td>
        <td width=98 align=center id=bg1>장 수</td>
        <td width=98 align=center><?=$nation['gennum']?></td>
    </tr>
    <tr>
        <td align=center id=bg1>국고 예산</td>
        <td align=center><?=number_format($budgetgold)?> (<?=$budgetgolddiff?>)</td>
        <td align=center id=bg1>병량 예산</td>
        <td align=center><?=number_format($budgetrice)?> (<?=$budgetricediff?>)</td>
        <td align=center id=bg1>기술력</td>
        <td align=center><?=number_format(floor($nation['tech']))?></td>
        <td align=center id=bg1>작 위</td>
        <td align=center><?=getNationLevel($nation['level'])?></td>
    </tr>
    <tr>
        <td align=center valign=top id=bg1> 속령일람 :</td>
        <td colspan=7><?=join(', ', $cityNames)?></td>
    </tr>
    <tr>
        <td align=center valign=top id=bg1>국가열전</td>
        <td colspan=7 id=bg0><?=ConvertLog($nation['history'])?></td>
    </tr>
</table>
<br>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>

</html>

