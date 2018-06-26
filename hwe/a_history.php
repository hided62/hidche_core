<?php
namespace sammo;

include "lib.php";
include "func.php";
$btn = Util::getReq('btn');
$yearmonth = Util::getReq('yearmonth', 'int');
$serverID = Util::getReq('server_id', 'string', null);

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

if(!$serverID){
    $serverID = UniqueConst::$serverID;
}

if($serverID === UniqueConst::$serverID){
    increaseRefresh("연감", 1);
}

$admin = $gameStor->getValues(['startyear','year','month']);

$query = "select con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}



[$s_year, $s_month] = $db->queryFirstList('SELECT year, month FROM history WHERE server_id = %s ORDER BY year ASC, month ASC LIMIT 1', $serverID);
$s = $s_year * 12 + $s_month;

[$e_year, $e_month] = $db->queryFirstList('SELECT year, month FROM history WHERE server_id = %s ORDER BY year DESC, month DESC LIMIT 1', $serverID);
$e = $e_year * 12 + $e_month;

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

?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title><?=UniqueConst::$serverName?>: 연감</title>
<?=WebUtil::printJS('../e_lib/jquery-3.2.1.min.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/base_map.js')?>
<?=WebUtil::printJS('js/map.js')?>

<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/map.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>연 감<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        년월 선택 :
        <input type=submit name=btn value="◀◀ 이전달">
        <select name=yearmonth size=1>
<?php
$dates = $db->queryAllLists('SELECT year, month FROM history WHERE server_id = %s ORDER BY year ASC, month ASC', $serverID);
foreach($dates as [$hYear, $hMonth]){
    $value = "".$hYear.StringUtil::padStringAlignRight($hMonth, 2, "0");
    if ($hYear == $year && $hMonth == $month) {
        echo "
            <option selected value={$value}>{$hYear}년 {$hMonth}월</option>";
    } else {
        echo "
            <option value={$value}>{$hYear}년 {$hMonth}월</option>";
    }
}

$history = $db->queryFirstRow('SELECT log,genlog,nation,power,gen,city FROM history WHERE server_id = %s AND year = %i AND month = %i', $serverID, $year, $month);
?>
        </select>
        <input type=submit name=btn value='조회하기'>
        <input type=submit name=btn value="다음달 ▶▶">
        </form>
    </td></tr>
</table>
<table align=center width=1000 height=520 class='tb_layout bg0'>
    <tr><td colspan=5 align=center id=bg1>중 원 지 도</td></tr>
    <tr height=520>
        <td width=698>
            <?=getMapHtml();?>
            
        <td width=139 valign=top><div style='background-color:#cccccc;color:black;text-align:center'>국명</div><?=$history['nation']?></td>
        <td width=70 valign=top style='text-align:center'><div style='background-color:#cccccc;color:black;'>국력</div><?=$history['power']?></td>
        <td width=43 valign=top style='text-align:center'><div style='background-color:#cccccc;color:black;'>장수</div><?=$history['gen']?></td>
        <td width=40 valign=top style='text-align:center'><div style='background-color:#cccccc;color:black;'>속령</div><?=$history['city']?></td>
    </tr>
    <tr><td colspan=5 align=center id=bg1>중 원 정 세</td></tr>
    <tr>
        <td colspan=5 valign=top>
            <?=$history['log']?>
        </td>
    </tr>
    <tr><td colspan=5 align=center id=bg1>장 수 동 향</td></tr>
    <tr>
        <td colspan=5 valign=top>
            <?=$history['genlog']?>
        </td>
    </tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<script>
reloadWorldMap({
    targetJson:'j_map_history.php?year=<?=$year?>&month=<?=$month?>&server_id=<?=$serverID?>',
    showMe:false,
    neutralView:true
});
</script>
</body>
</html>
