<?php
namespace sammo;

include "lib.php";
include "func.php";

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("갱신정보", 2);

$admin = $gameStor->getValues(['year','month','refresh','maxrefresh','maxonline']);

$log = getRawFileLogRecent(__dir__.'/logs/'.UniqueConst::$serverID.'/_traffic.txt', 11, 100);

$date = [];
$year = [];
$month = [];
$refresh = [];
$online = [];

$curonline = getOnlineNum();
foreach($log as $i=>$value){
    $parse = Json::decode($value);
    $date[$i]    = $parse[0];
    $year[$i]    = $parse[1];
    $month[$i]   = $parse[2];
    $refresh[$i] = $parse[3];
    $online[$i]  = $parse[4];
}
$year[] = $admin['year'];
$month[] = $admin['month'];
$date[] = date('Y-m-d H:i:s');

if ($admin['maxrefresh'] == 0) {
    $admin['maxrefresh'] = 1;
}
if ($admin['maxrefresh'] < $admin['refresh']) {
    $admin['maxrefresh'] = $admin['refresh'];
}
if ($admin['maxonline'] == 0) {
    $admin['maxonline'] = 1;
}
if ($admin['maxonline'] < $curonline) {
    $admin['maxonline'] = $curonline;
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 트래픽정보</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="viewport" content="width=1024, initial-scale=1" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<style>
.big_bar{
    float:left;
    position:relative;
    height:30px;
}
.big_bar span{
    float:right;
    padding:0;
    margin:0;
    line-height:30px;
    padding-right:1ch;
}

.little_bar{
    float:left;
    position:relative;
    height:17px;
}

span.out_bar{
    line-height:30px;
    margin-left:1ch;
}
</style>
</head>
<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>트 래 픽 정 보<br><?=closeButton()?></td></tr>
</table>
<br>
<table align=center width=1016>
    <tr><td align=left>
        <table align=center class='tb_layout bg0'>
            <tr><td colspan=4 align=center id=bg2><font size=5>접 속 량</font></td></tr>
<?php
$refresh[] = $admin['refresh'];
foreach($refresh as $i=>$value){
    $w = round($value / $admin['maxrefresh'] * 100, 1);
    $color = getTrafficColor($w);
    $dt = substr($date[$i], 11, 5); ?>
            <tr height=30>
                <td width=100 align=center><?=$year[$i]?>년 <?=$month[$i]?>월</td>
                <td width=60 align=center id=bg2><?=$dt?></td>
                <td width=2 align=center id=bg1></td>
                <td width=320>
                    <?php if($w == 0): ?>
                        <span class="out_bar"><?=$value?></span>
                    <?php elseif($w < 10): ?>
                        <div class='big_bar' style='width:<?=$w?>%;background-color:<?=$color?>;'></div><span class="out_bar"><?=$value?></span>
                    <?php else:?>
                        <div class='big_bar' style='width:<?=$w?>%;background-color:<?=$color?>;'><span><?=$value?></span></div>
                    <?php endif;?>
                    
                </td>
            </tr>
<?php
}
?>
            <tr><td colspan=4 height=5 align=center id=bg1></td></tr>
            <tr>
                <td colspan=4 height=30 align=center id=bg0>최고기록: <?=$admin['maxrefresh']?></td>
            </tr>


        </table>
    </td>
    <td align=right>
        <table align=center class='tb_layout bg0'>
            <tr><td colspan=4 align=center id=bg2><font size=5>접 속 자</font></td></tr>
<?php
$online[] = $curonline;
foreach($online as $i=>$value){
    $w = round($value / $admin['maxonline'] * 100, 1);
    $color = getTrafficColor($w);
    $dt = substr($date[$i], 11, 5); ?>
            <tr height=30>
                <td width=100 align=center><?=$year[$i]?>년 <?=$month[$i]?>월</td>
                <td width=60 align=center id=bg2><?=$dt?></td>
                <td width=2 align=center id=bg1></td>
                <td width=320>
                    <?php if($w == 0): ?>
                        <span class="out_bar"><?=$value?></span>
                    <?php elseif($w < 10): ?>
                        <div class='big_bar' style='width:<?=$w?>%;background-color:<?=$color?>;'></div><span class="out_bar"><?=$value?></span>
                    <?php else:?>
                        <div class='big_bar' style='width:<?=$w?>%;background-color:<?=$color?>;'><span><?=$value?></span></div>
                    <?php endif;?>
                    
                </td>
            </tr>
<?php
}
?>
            <tr><td colspan=4 height=5 align=center id=bg1></td></tr>
            <tr>
                <td colspan=4 height=30 align=center id=bg0>최고기록: <?=$admin['maxonline']?></td>
            </tr>
        </table>
    </td></tr>
</table>
<br>
<table align=center class='tb_layout bg0'>
    <tr><td colspan=3 align=center id=bg2><font size=5>주 의 대 상 자 (순간과도갱신)</font></td></tr>
<?php
$max_refresh = $db->queryFirstRow('SELECT sum(refresh) as refresh, sum(`connect`) as `connect` from general');
$max_refresh['name'] = '접속자 총합';

$refresh_result = array_merge([$max_refresh], $db->query('SELECT `name`,refresh,`connect` FROM general ORDER BY refresh DESC LIMIT 5'));

foreach ($refresh_result as $i=>$user) {
    $w = round($user['refresh'] / $max_refresh['refresh'] * 100, 1);
    $w2 = round(100 - $w, 1);
    $color = getTrafficColor($w);
?>
    <tr>
        <td width=98  align=center><?=$user['name']?></td>
        <td width=98  align=center><?=$user['connect']?>(<?=$user['refresh']?>)</td>
        <td width=798>
            <?php if($w == 0): ?>
            <?php elseif($w < 10): ?>
                <div class='little_bar' style='width:<?=$w?>%;background-color:<?=$color?>;'></div>
            <?php else:?>
                <div class='little_bar' style='width:<?=$w?>%;background-color:<?=$color?>;'></div>
            <?php endif;?>
        </td>
    </tr>
<?php
}
?>
</table>
<br>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

<?php
function getTrafficColor($per)
{
    $r = getHex($per);
    $b = getHex(100 - $per);
    $color = $r . "00" . $b;
    return '#'.$color;
}

function getHex($dec)
{
    $hex = intdiv($dec * 255, 100);
    $code = getHexCode(intdiv($hex, 16));
    $code .= getHexCode($hex % 16);
    return $code;
}

function getHexCode($hex)
{
    switch ($hex) {
    case  0: return "0";    case  1: return "1";    case  2: return "2";    case  3: return "3";
    case  4: return "4";    case  5: return "5";    case  6: return "6";    case  7: return "7";
    case  8: return "8";    case  9: return "9";    case 10: return "A";    case 11: return "B";
    case 12: return "C";    case 13: return "D";    case 14: return "E";    case 15: return "F";
    }
}
