<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("국법", 1);
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 국법</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>국 법<br><?=backButton()?></td></tr>
    <tr><td>

<?php
$query = "select no,nation,level from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select rule from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

if($me['level'] >= 5) {
    echo "
<form name=form1 method=post action=c_nationrule.php>
<textarea name=msg style=color:white;background-color:black;width:998px;height:500px;>{$nation['rule']}</textarea><br>
<input type=submit value=저장하기>
</form>";
} else {
    echo "
<textarea name=msg style=color:white;background-color:black;width:998px;height:500px; readonly>{$nation['rule']}</textarea><br>";
}

?>
    </td></tr>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

