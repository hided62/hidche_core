<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("회의실", 1);

$query = "select skin,no,nation from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}
?>
<html>
<head>
<title>회의실</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>회 의 실<br><?=backButton()?></td></tr>
<form name=form1 method=post action=c_nationboard.php>
    <tr><td align=center>
        제목 <input type=textarea maxlength=50 name=title style=color:white;background-color:black;width:830;>
        </td>
    </tr>
    <tr><td>
        <textarea name=msg style=color:white;background-color:black;width:998;height:200;></textarea><br>
        <input type=submit value=저장하기>
        <input type=hidden name=num value=-1>
    </td></tr>
</form>
</table>
<br>
<?php
$nation = getNation($connect, $me['nation']);

//20개 메세지
$index = $nation['boardindex'];
for($i=0; $i < 20; $i++) {
    $who = "board{$index}_who";
    $query = "select name,picture,imgsvr from general where no='$nation[$who]'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    if($nation["board{$index}"] != '') { msgprint($connect, $nation["board{$index}"], $general['name'], $general['picture'], $general['imgsvr'], $nation["board{$index}_when"], $index, 0); }
    $index--;
    if($index < 0) { $index = 19; }
}

?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

