<?php
include "lib.php";
include "func.php";

//NOTE:관리자의 경우 NPC로그인을 user_id는 유지하되 no값만 바꾸는 식으로 가능하지 않을까?

$connect=dbConn();

?>
<html>
<head>
<title>NPC로그인</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
<?php require('analytics.php'); ?>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td align=center id=bg1>삼국지 모의전투 PHP (유기체서버)</td></tr>
    <tr>
        <td align=center>
            <form name=form1 action=npc_login_process.php method=post>
                <table cellpadding=0 cellspacing=0 width=600 height=100>
                    <tr>
                        <td width=49% align=right>ID</td>
                        <td width=2%></td>
                        <td width=49%><input style=color:white;background-color:black; type=text name=id maxlength=12 size=12></td>
                    </tr>
                    <tr>
                        <td align=right>비밀번호</td>
                        <td></td>
                        <td><input style=color:white;background-color:black; type=password name=pw maxlength=12 size=12></td>
                    </tr>
                    <tr>
                        <td align=right><input type=submit value=로그인></td>
                        <td></td>
                        <td>-</td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>

