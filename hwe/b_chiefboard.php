<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLoginWithGeneralID();
$connect = dbConn();
increaseRefresh("기밀실", 1);

$query = "select no,nation,level from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['level'] < 5) {
    echo "수뇌부가 아닙니다.";
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<title>기밀실</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>기 밀 실<br><?=backButton()?></td></tr>
<form name=form1 method=post action=c_chiefboard.php>
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
$index = $nation['coreindex'];
for($i=0; $i < 20; $i++) {
    $who = "coreboard{$index}_who";
    $query = "select name,picture,imgsvr from general where no='$nation[$who]'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    if($nation["coreboard{$index}"] != '') { msgprint($connect, $nation["coreboard{$index}"], $general['name'], $general['picture'], $general['imgsvr'], $nation["coreboard{$index}_when"], $index, 1); }
    $index--;
    if($index < 0) { $index = 19; }
}

?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

