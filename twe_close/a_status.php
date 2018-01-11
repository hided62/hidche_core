<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "세력도", 2);
checkTurn($connect);

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select skin,map,con,userlevel,turntime from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me[userlevel], $me[con], $admin[conlimit]);
if($con >= 2) { printLimitMsg($me[turntime]); exit(); }
?>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>세력도</title>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>

<body oncontextmenu='return false'>
<table align=center width=1200 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>세 력 도<br><?php closeButton(); ?></td></tr>
</table>
<table align=center width=1200 height=520 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr height=520>
        <td width=498 valign=top>
            <?php AllLog(34, $me[skin]); ?>
        </td>
        <td width=698>
            <iframe src='map.php?type=2&graphic=<?=$me[map];?>' width=698 height=520 frameborder=0 marginwidth=0 marginheight=0 topmargin=0 scrolling=no>
            </iframe>
        </td>
    </tr>
    <tr>
        <td colspan=2 valign=top>
            <?php History(34, $me[skin]); ?>
        </td>
    </tr>
</table>
<table align=center width=1200 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php closeButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>

</html>

