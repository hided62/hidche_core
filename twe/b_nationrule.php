<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("국법", 1);
?>
<!DOCTYPE html>
<html>
<head>
<title>국법</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>국 법<br><?=backButton()?></td></tr>
    <tr><td>

<?php
$query = "select no,nation,level from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select rule from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

if($me['level'] >= 5) {
    echo "
<form name=form1 method=post action=c_nationrule.php>
<textarea name=msg style=color:white;background-color:black;width:998;height:500;>{$nation['rule']}</textarea><br>
<input type=submit value=저장하기>
</form>";
} else {
    echo "
<textarea name=msg style=color:white;background-color:black;width:998;height:500; readonly>{$nation['rule']}</textarea><br>";
}

?>
    </td></tr>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

