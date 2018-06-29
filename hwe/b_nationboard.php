<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("회의실", 1);

$query = "select no,nation from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 회의실</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="viewport" content="width=1024, initial-scale=1" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<div style='width:1000px;margin:auto'>
<table width=1000 class='tb_layout bg0'>
    <tr><td>회 의 실<br><?=backButton()?></td></tr>
<form name=form1 method=post action=c_nationboard.php>
    <tr><td align=center>
        제목 <input type=textarea maxlength=50 name=title style=color:white;background-color:black;width:830px;>
        </td>
    </tr>
    <tr><td>
        <textarea name=msg style=color:white;background-color:black;width:998px;height:200px;></textarea><br>
        <input type=submit value=저장하기>
        <input type=hidden name=num value=-1>
    </td></tr>
</form>
</table>
<br>
<?php
$nation = getNation($me['nation']);

//20개 메세지
$index = $nation['boardindex'];
for($i=0; $i < 20; $i++) {
    $who = "board{$index}_who";
    $query = "select name,picture,imgsvr from general where no='$nation[$who]'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    if($nation["board{$index}"] != '') { msgprint($nation["board{$index}"], $general['name'], $general['picture'], $general['imgsvr'], $nation["board{$index}_when"], $index, 0); }
    $index--;
    if($index < 0) { $index = 19; }
}

?>

<table width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</div>
</body>
</html>

