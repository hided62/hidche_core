<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("중원정보", 1);

$mapTheme = $gameStor->map_theme??'che';

$me = $db->queryFirstRow('SELECT no,nation FROM general WHERE owner=%i', $userID);
$myNationID = $me['nation'];

$nations = array_filter(getAllNationStaticInfo(), function(array $nation){
    return $nation['level'];
});
uasort($nations, function(array $lhs, array $rhs){
    return -($lhs['power']<=>$rhs['power']);
});
$nationCnt = count($nations);

foreach($db->queryAllLists('SELECT nation, count(city) FROM city WHERE nation != 0 GROUP BY nation') as [$nationID, $cityCnt]){
    $nations[$nationID]['city_cnt'] = $cityCnt;
}

$realConflict = [];
foreach ($db->queryAllLists('SELECT city, `name`, conflict FROM city WHERE conflict!=%s', '{}') as [
    $cityID, 
    $cityName, 
    $rawConflict
])
{
    $rawConflict = Json::decode($rawConflict);
    if (count($rawConflict)<2) {
        continue;
    }

    $sum = array_sum($rawConflict);


    $conflict = [];
    foreach ($rawConflict as $nationID=>$killnum) {
        $conflict[$nationID] = [
            'killnum'=>$killnum,
            'percent'=>round(100*$killnum / $sum, 1),
            'name'=>$nations[$nationID]['name'],
            'color'=>$nations[$nationID]['color']
        ];
    }

    $realConflict[] = [$cityID, $cityName, $conflict];
};

$diplomacyList = [];
foreach($db->queryAllLists('SELECT me, you, state FROM diplomacy') as [$me, $you, $state]){
    if(!key_exists($me, $diplomacyList)){
        $diplomacyList[$me] = [];
    }
    
    $diplomacyList[$me][$you] = $state;
}

$cellWidth = intdiv(888, max(1, $nationCnt));


$defaultStateChar = '?';
$sameNationStateChar = '＼';
$infomativeStateCharMap = [
    0=>'<span style="color:red;">★</span>',
    1=>'<span style="color:magenta;">▲</span>',
    2=>'ㆍ',
    7=>'<span style="color:green;">@</span>'
];
$neutralStateCharMap = [
    0=>'<span style="color:red;">★</span>',
    1=>'<span style="color:magenta;">▲</span>'
];

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 중원 정보</title>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/vendors.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('d_shared/base_map.js')?>
<?=WebUtil::printJS('js/map.js')?>
<script>
window.serverNick = '<?=DB::prefix()?>';
window.serverID = '<?=UniqueConst::$serverID?>';
$(function(){

    reloadWorldMap({
        neutralView:true,
        showMe:true,
        useCachedMap:true
    });

});
</script>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/map.css')?>
<?=WebUtil::printCSS('css/history.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>중 원 정 보<br><?=backButton()?></td></tr>
</table>
<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td colspan=<?=$nationCnt+1?> align=center bgcolor=blue>외 교 현 황</td></tr>
    <tr>
        <td align=center width=130 style=background-color:<?=GameConst::$basecolor2?>;>&nbsp;</td>
<?php foreach($nations as $nation): ?>
    <td align=center width=<?=$cellWidth?> style='background-color:<?=$nation['color']?>;color:<?=newColor($nation['color'])?>'><?=$nation['name']?></td>
<?php endforeach; ?>
</tr>
<?php foreach($nations as $me=>$meNation): ?>
<tr style='text-align:center;'>
<td align=center style='background-color:<?=$meNation['color']?>;color:<?=newColor($meNation['color'])?>'><?=$meNation['name']?></td>
<?php    foreach(array_keys($nations) as $you): ?>
<td <?=($me==$myNationID||$you==$myNationID)?'style="background-color:'.GameConst::$basecolor3.'"':''?>><?php
if($me==$you){
    echo $sameNationStateChar;
}
else if($me==$myNationID || $you==$myNationID || $session->userGrade >= 5){
    echo $infomativeStateCharMap[$diplomacyList[$me][$you]]??$defaultStateChar;
}
else{
    echo $neutralStateCharMap[$diplomacyList[$me][$you]]??$defaultStateChar;
}
?></td>
<?php    endforeach; ?>
</tr>
<?php endforeach; ?>
    <tr><td colspan=<?=$nationCnt+1?> align=center>불가침 : <font color=limegreen>@</font>, 통상 : ㆍ, 선포 : <font color=magenta>▲</font>, 교전 : <font color=red>★</font></td></tr>
</table>

<?php if ($realConflict) : ?>

<br>
<table align='center' width=1000 class='tb_layout bg0'>
    <tr><td colspan=2 align=center bgcolor=magenta>분 쟁 현 황</td></tr>
    <?php foreach($realConflict as list($cityID, $cityName, $conflict)): ?>
    <tr>
        <td align=center width=48><?=$cityName?></td>
        <td style='width:948px;position:relative;'>
            <table class='tb_layout bg0' style='width:100%;'>
            <?php foreach($conflict as $item): ?>
                <tr>
                    <td 
                        width=130
                        align=right 
                        style='color:<?=newColor($item['color'])?>;background-color:<?=$item['color']?>;'
                    ><?=$item['name']?>&nbsp;</td>
                    <td width=48 align=right><?=(float)$item['percent']?> %&nbsp;</td>
                    <td width=*><div
                        style='display:inline-block;width:<?=$item['percent']?>%;background-color:<?=$item['color']?>;'
                    >&nbsp;</div></td>
                </tr>
            <?php endforeach; ?>
            </table>
    
        </td>
    </tr>
    <?php endforeach; ?>
    <tr><td colspan=2 height=5 id=bg1></td></tr>
</table>

<?php endif; ?> 

<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td colspan=5 align=center bgcolor=green><font size=3>중 원 지 도</font></td>
    </tr>
    <tr>
        <td width=698 height=420>
            <?=getMapHtml($mapTheme)?>
        </td>
        <td id='nation_list_frame'>
            <table id='nation_list'>
                <thead>
                    <tr>
                        <th width=130>국명</th>
                        <th width=70>국력</th>
                        <th width=45>장수</th>
                        <th width=45>속령</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach($nations as $nation): ?>
                    <tr>
                        <td><span style='color:<?=newColor($nation['color'])?>;background-color:<?=$nation['color']?>'><?=$nation['name']?></td>
                        <td style='text-align:right'><?=number_format($nation['power'])?></td>
                        <td style='text-align:right'><?=number_format($nation['gennum'])?></td>
                        <td style='text-align:right'><?=number_format($nation['city_cnt'])?></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
                <tfoot></tfoot>
            </table>
        </td>
    </tr>
</table>
<br>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
