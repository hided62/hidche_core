<?php
include "lib.php";
include "func.php";
$yearmonth = $_POST['yearmonth'];
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "연감", 5);

$query = "select startyear,year,month,conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select skin,map,con,userlevel,turntime from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['userlevel'], $me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

$query = "select year,month from history order by no limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$history = MYDB_fetch_array($result);
$s = ($history['year']*12) + $history['month'];

$query = "select year,month from history order by no desc limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$history = MYDB_fetch_array($result);
$e = ($history['year']*12) + $history['month'];

if(!$yearmonth) {
    $year = $admin['year'];
    $month = $admin['month'] - 1;
} else {
    $year = substr($yearmonth, 0, 3) - 0;
    $month = substr($yearmonth, 3, 2) - 0;

    if($btn == "◀◀ 이전달") {
        $month -= 1;
    } elseif($btn == "다음달 ▶▶") {
        $month += 1;
    }
}
$now = ($year*12) + $month;

if($now < $s) { $now = $s; }
if($now > $e) { $now = $e; }

$year = floor($now / 12);
$month = $now % 12;
if($month <= 0) {
    $year -= 1;
    $month += 12;
}

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}
?>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>연감</title>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>연 감<br><?php closeButton(); ?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        년월 선택 :
        <input type=submit name=btn value="◀◀ 이전달">
        <select name=yearmonth size=1>
<?php
$query = "select year,month from history";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$histCount = MYDB_num_rows($result);
for($i=0; $i < $histCount; $i++) {
    $history = MYDB_fetch_array($result);
    $value = "".$history['year']._String::Fill2($history['month'], 2, "0");
    if($history['year'] == $year && $history['month'] == $month) {
        echo "
            <option selected value={$value}>{$history['year']}년 {$history['month']}월</option>";
    } else {
        echo "
            <option value={$value}>{$history['year']}년 {$history['month']}월</option>";
    }
}

$query = "select log,genlog,nation,power,gen,city from history where year='$year' and month='$month'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$history = MYDB_fetch_array($result);
?>
        </select>
        <input type=submit name=btn value='조회하기'>
        <input type=submit name=btn value="다음달 ▶▶">
        </form>
    </td></tr>
</table>
<table align=center width=1000 height=520 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=5 align=center id=bg1>중 원 지 도</td></tr>
    <tr height=520>
        <td width=698>
            <iframe src='map_history.php?year=<?=$year;?>&month=<?=$month;?>' width=698 height=520 frameborder=0 marginwidth=0 marginheight=0 topmargin=0 scrolling=no>
            </iframe>
        </td>
        <td width=98 valign=top><?=$history['nation'];?></td>
        <td width=78 valign=top><?=$history['power'];?></td>
        <td width=58 valign=top><?=$history['gen'];?></td>
        <td width=58 valign=top><?=$history['city'];?></td>
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
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?php closeButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>

</html>
