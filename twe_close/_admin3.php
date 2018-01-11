<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me[userlevel] < 5) {
    echo "
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
</head>
<body oncontextmenu='return false'>
관리자가 아닙니다.<br>
";
    banner();
    echo "
</body>
</html>";

    exit();
}
//$admin = getAdmin($connect);
?>
<html>
<head>
<title>특별회원</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
</head>
<body oncontextmenu='return false'>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>특 별 회 원<br><?php backButton(); ?></td></tr>
</table>
<form name=form1 method=post action=_admin3_submit.php>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=80 align=center rowspan=5>회원선택<br><br><font color=orange>무장</font><br><font color=skyblue>지장</font></td>
        <td width=105 rowspan=5>
<?php

echo "
            <select name=genlist[] size=20 multiple style=color:white;background-color:black;font-size:13>";

$query = "select no,name,power,intel from general where userlevel>2 order by npc,binary(name)";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

for($i=0; $i < $gencount; $i++) {
    $general = MYDB_fetch_array($result);
    if($general[power] >= $general[intel]) {
        echo "
                <option value=$general[no] style=color:orange;>$general[name]</option>";
    } else {
        echo "
                <option value=$general[no] style=color:skyblue;>$general[name]</option>";
    }
}

echo "
            </select>
        </td>
        <td width=100 align=center>아이템 지급</td>
        <td width=504>
            <select name=weap size=1 style=color:white;background-color:black;font-size:13>";
for($i=0; $i < 27; $i++) {
    echo "
                <option value={$i}>{$i}</option>";
}
?>
            </select>
            <input type=submit name=btn value='무기지급'>
            <input type=submit name=btn value='책지급'>
            <input type=submit name=btn value='말지급'>
            <input type=submit name=btn value='도구지급'>
        </td>
    </tr>
    <tr>
        <td align=center>특별회원</td>
        <td><input type=submit name=btn value='특별회원 해제'></td>
    </tr>
    <tr>
        <td align=center>메세지 전달</td>
        <td><input type=textarea size=60 maxlength=255 name=msg style=background-color:black;color:white;><input type=submit name=btn value='메세지 전달'></td>
    </tr>
</table>
</form>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php backButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
</body>
</html>
