<?php
namespace sammo;

include "lib.php";
include "func.php";
$btn = Util::getReq('btn');
$yearmonth = Util::getReq('yearmonth', 'int');

extractMissingPostToGlobals();

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("연감", 2);

$query = "select startyear,year,month,conlimit from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$admin = MYDB_fetch_array($result);

$query = "select con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$query = "select year,month from history order by no limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$history = MYDB_fetch_array($result);
$s = ($history['year']*12) + $history['month'];

$query = "select year,month from history order by no desc limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$history = MYDB_fetch_array($result);
$e = ($history['year']*12) + $history['month'];

//FIXME: $yearmonth가 올바르지 않을 경우에 처리가 필요.
if (!$yearmonth) {
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
$query = "select year,month from history";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$histCount = MYDB_num_rows($result);
for ($i=0; $i < $histCount; $i++) {
    $history = MYDB_fetch_array($result);
    $value = "".$history['year'].StringUtil::padStringAlignRight($history['month'], 2, "0");
    if ($history['year'] == $year && $history['month'] == $month) {
        echo "
            <option selected value={$value}>{$history['year']}년 {$history['month']}월</option>";
    } else {
        echo "
            <option value={$value}>{$history['year']}년 {$history['month']}월</option>";
    }
}

$query = "select log,genlog,nation,power,gen,city from history where year='$year' and month='$month'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$history = MYDB_fetch_array($result);
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
            
        <td width=98 valign=top><?=$history['nation']?></td>
        <td width=78 valign=top><?=$history['power']?></td>
        <td width=58 valign=top><?=$history['gen']?></td>
        <td width=58 valign=top><?=$history['city']?></td>
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
    targetJson:'j_map_history.php?year=<?=$year?>&month=<?=$month?>',
    showMe:false,
    neutralView:true
});
</script>
</body>
</html>
