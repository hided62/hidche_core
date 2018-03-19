<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("감찰부", 2);
//전투 추진을 위해 갱신
checkTurn($connect);

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select nation from general where no='$gen'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$general = MYDB_fetch_array($result);

$query = "select skin,no,nation,level,con,turntime,belong from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

//재야인 경우
$meLevel = $me['level'];
if($meLevel == 0 || ($meLevel == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

//잘못된 접근
if($general['nation'] != $me['nation']) {
    $gen = 0;
}

if($btn == '정렬하기') {
    $gen = 0;
}

if($type == 0) {
    $type = 0;
}
$sel[$type] = "selected";

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}
?>
<!DOCTYPE html>
<html>

<head>
<title>감찰부</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>감 찰 부<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0];?> value=0>최근턴</option>
            <option <?=$sel[1];?> value=1>최근전투</option>
            <option <?=$sel[2];?> value=2>장수명</option>
            <option <?=$sel[3];?> value=3>전투수</option>
        </select>
        <input type=submit name=btn value='정렬하기'>
        대상장수 :
        <select name=gen size=1>
<?php
switch($type) {
    case 0: $query = "select no,name from general where nation='{$me['nation']}' order by turntime desc"; break;
    case 1: $query = "select no,name from general where nation='{$me['nation']}' order by recwar desc"; break;
    case 2: $query = "select no,name from general where nation='{$me['nation']}' order by npc,binary(name)"; break;
    case 3: $query = "select no,name from general where nation='{$me['nation']}' order by warnum desc"; break;
}
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

for($i=0; $i < $gencount; $i++) {
    $general = MYDB_fetch_array($result);
    // 선택 없으면 맨 처음 장수
    if($gen == 0) {
        $gen = $general['no'];
    }
    if($gen == $general['no']) {
        echo "
            <option selected value={$general['no']}>{$general['name']}</option>";
    } else {
        echo "
            <option value={$general['no']}>{$general['name']}</option>";
    }
}
?>
        </select>
        <input type=submit name=btn value='조회하기'>
        </form>
    </td></tr>
</table>
<table width=1000 align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=50% align=center id=bg1><font color=skyblue size=3>장 수 정 보</font></td>
        <td width=50% align=center id=bg1><font color=orange size=3>장 수 열 전</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?php generalInfo($connect, $gen, $me['skin']); generalInfo2($connect, $gen, $me['skin']); ?>
        </td>
        <td valign=top>
            <?php MyHistory($connect, $gen, $me['skin']); ?>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=orange size=3>전투 기록</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 결과</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?php MyBatLog($gen, 24, $me['skin']); ?>
        </td>
        <td valign=top>
            <?php MyBatRes($gen, 24, $me['skin']); ?>
        </td>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
