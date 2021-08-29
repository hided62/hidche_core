<?php
namespace sammo;

include "lib.php";
include "func.php";
$btn = Util::getReq('btn');
$yearmonth = Util::getReq('yearmonth', 'int');
$serverID = Util::getReq('serverID', 'string', null);

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if(!$serverID){
    $serverID = UniqueConst::$serverID;
}

if($serverID === UniqueConst::$serverID){
    increaseRefresh("연감", 1);
}

$admin = $gameStor->getValues(['startyear','year','month','map_theme']);

$me = $db->queryFirstRow('SELECT con, turntime FROM general WHERE owner = %i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}



[$s_year, $s_month] = $db->queryFirstList('SELECT year, month FROM ng_history WHERE server_id = %s ORDER BY year ASC, month ASC LIMIT 1', $serverID);
$s = $s_year * 12 + $s_month;

if($s_year === null){
    echo '인자 에러';
    exit();
}

[$e_year, $e_month] = $db->queryFirstList('SELECT year, month FROM ng_history WHERE server_id = %s ORDER BY year DESC, month DESC LIMIT 1', $serverID);
$e = $e_year * 12 + $e_month;

if($serverID !== UniqueConst::$serverID){
    $mapTheme = $db->queryFirstField('SELECT map FROM ng_games WHERE server_id=%s', $serverID)?:'che';
}
else{
    $mapTheme = $admin['map_theme']??'che';
}

//FIXME: $yearmonth가 올바르지 않을 경우에 처리가 필요.
if($serverID !== UniqueConst::$serverID && !$yearmonth){
    $year = $s_year;
    $month = $s_month;
}
else if (!$yearmonth) {
    $year = $admin['year'];
    $month = $admin['month'] - 1;
} else {
    $year = intdiv($yearmonth, 100);
    $month = $yearmonth % 100;

    if ($btn == "◀◀ 이전달") {
        $month -= 1;
    } elseif ($btn == "다음달 ▶▶") {
        $month += 1;
    }
}
$now = ($year*12) + $month;

if ($now < $s) {
    $now = $s;
}
if ($now > $e) {
    $now = $e;
}

$year = intdiv($now, 12);
$month = $now % 12;
if ($month <= 0) {
    $year -= 1;
    $month += 12;
}

$history = $db->queryFirstRow('SELECT * FROM ng_history WHERE server_id = %s AND year = %i AND month = %i', $serverID, $year, $month);


$nations = Json::decode($history['nations']);
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 연감</title>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS("js/map/theme_{$mapTheme}.js")?>
<?=WebUtil::printJS('dist_js/history.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/map.css')?>
<?=WebUtil::printCSS('css/history.css')?>
<script>
var startYear = <?=$s_year?>;
var startMonth = <?=$s_month?>;
var lastYear = <?=$e_year?>;
var lastMonth = <?=$e_month?>;
var selectYear = <?=$year?>;
var selectMonth = <?=$month?>;
var nations = <?=$nations?$history['nations']:'{}'?>;
</script>
</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>연 감<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        연월 선택 :
        <input type=submit name=btn value="◀◀ 이전달">
        <select id='yearmonth' name=yearmonth size=1>
            <option selected='selected'><?=$year?>년 <?=$month?>월</option>
            <option><?=$e_year?>년 12월</option>
        </select>
        <input type=submit name=btn value='조회하기'>
        <input type=submit name=btn value="다음달 ▶▶">
        </form>
    </td></tr>
</table>
<table align=center width=1000 height=520 class='tb_layout bg0'>
    <thead><tr><th colspan=5 align=center class='bg1'>중 원 지 도</th></tr></thead>
    <tbody>
    <tr height=520>
        <td width=698>
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
                        <td style='text-align:right'><?=number_format(count($nation['cities']??[]))?></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
                <tfoot></tfoot>
            </table>
        </td>
    </tr>
    <tr><th colspan=5 align=center class='bg1'>중 원 정 세</th></tr>
    <tr>
        <td colspan=5 valign=top>
            <?=formatHistoryToHTML(Json::decode($history['global_history']))?>
        </td>
    </tr>
    <tr><th colspan=5 align=center class='bg1'>장 수 동 향</th></tr>
    <tr>
        <td colspan=5 valign=top>
            <?=formatHistoryToHTML(Json::decode($history['global_action']))?>
        </td>
    </tr>
    </tbody>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<script>
window.serverNick = '<?=DB::prefix()?>';
window.serverID = '<?=UniqueConst::$serverID?>';
reloadWorldMap({
    targetJson:'j_map_history.php?year=<?=$year?>&month=<?=$month?>&serverID=<?=$serverID?>',
    showMe:false,
    neutralView:true,
    useCachedMap:false,
    year:<?=$year?>,
    month:<?=$month?>,
});
</script>
</body>
</html>
