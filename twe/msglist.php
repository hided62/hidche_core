<?php
require_once("lib.php");
require_once("func.php");
//로그인 검사
CheckLogin(1);
$connect = dbconn();

?>

<html>
<head>
<title>메시지리스트</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>
<body>

<?php

$query = "select no,nation,skin from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

?>
<table align=center width=1000 height=1375 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td valign=top width=500>
            전체 메세지(최고75자)<br>
            <?=MsgFile($me['skin'], 1)?>
            <br>
            개인 메세지(최고75자)<br>
            <?=MsgMe($connect, 2)?>
        </td>
        <td valign=top width=500>
            <?=MsgDip($connect, 4)?>
            국가 메세지(최고75자)<br>
            <?=MsgFile($me['skin'], 3, $me['nation'])?>
        </td>
    </tr>
</table>
</body>
</html>

