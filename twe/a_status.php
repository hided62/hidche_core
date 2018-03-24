<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("세력도", 2);
checkTurn($connect);

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select skin,map,con,turntime from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>세력도</title>
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="js/common.js"></script>
<script src="js/base_map.js"></script>
<script src="js/map.js"></script>
<script>
$(function(){

    reloadWorldMap({
        neutralView:true,
        showMe:true
    });

});
</script>
<link href="css/normalize.css" rel="stylesheet">
<link href="css/common.css" rel="stylesheet">
<link href="css/map.css" rel="stylesheet">

</head>

<body>
<table align=center width=1200 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>세 력 도<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1200 height=520 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr height=520>
        <td width=498 valign=top>
            <?php AllLog(34, $me['skin']); ?>
        </td>
        <td width=698>
            <?=getMapHtml()?>
        </td>
    </tr>
    <tr>
        <td colspan=2 valign=top>
            <?php History(34, $me['skin']); ?>
        </td>
    </tr>
</table>
<table align=center width=1200 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>

</html>

