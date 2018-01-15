<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select userlevel from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['userlevel'] < 5) {
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

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);
?>
<html>
<head>
<title>멀티관리</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
</head>
<body oncontextmenu='return false'>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>멀 티 관 리<br><?php backButton(); ?></td></tr>
</table>
<form name=form1 method=post action=_admin4_submit.php>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=80 align=center rowspan=3>회원선택<br><br><font color=cyan>NPC</font><br><font color=skyblue>NPC유저</font><br><font color=blue>특별회원</font><br><font color=red>접속제한</font><br><b style=background-color:red;>블럭회원</b></td>
        <td width=105 rowspan=3>
<?php

echo "
            <select name=genlist[] size=20 multiple style=color:white;background-color:black;font-size:13>";

$query = "select no,name,npc,userlevel,block from general where ip!='' order by npc,ip";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

for($i=0; $i < $gencount; $i++) {
    $general = MYDB_fetch_array($result);
    $style = "style=;";
    if($general['block']         > 0) { $style .= "background-color:red;"; }
    if($general['npc']          >= 2) { $style .= "color:cyan;"; }
    elseif($general['npc']      == 1) { $style .= "color:skyblue;"; }
    if($general['con'] > $admin['conlimit']) { $style .= "color:red;"; }
    if($general['userlevel'] > 2) { $style .= "color:blue;"; }

    echo "
                <option value={$general['no']} $style>{$general['name']}</option>";
}

echo "
            </select>";
?>
        </td>
        <td width=100 align=center>블럭</td>
        <td width=504>
            <input type=submit name=btn value='블럭 해제'><input type=submit name=btn value='1단계 블럭'><input type=submit name=btn value='2단계 블럭'><input type=submit name=btn value='3단계 블럭'><input type=submit name=btn value='무한삭턴'><br>
            1단계:발언권, 2단계:턴블럭
        </td>
    </tr>
    <tr>
        <td align=center>강제 사망</td>
        <td><input type=submit name=btn value='강제 사망'></td>
    </tr>
    <tr>
        <td align=center>메세지 전달</td>
        <td><input type=textarea size=60 maxlength=255 name=msg style=background-color:black;color:white;><input type=submit name=btn value='메세지 전달'></td>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td align=center width=100>장수명</td>
        <td align=center width=180>최근로그인</td>
        <td align=center width=129>IP</td>
        <td align=center width=100>ID</td>
        <td align=center width=278>-</td>
    </tr>
    <tr>
<?php
$query = "select substring_index(ip,'.',3) as ip2 from general where ip!='' and npc<2 group by ip2 having count(*)>1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$ipCount = MYDB_num_rows($result);
$genName = "";
$genDate = "";
$genIP   = "";
$genID   = "";
$conMsg  = "";
for($i=0; $i < $ipCount; $i++) {
    $ip = MYDB_fetch_array($result);

    $query = "select name,ip,lastconnect,user_id,block,conmsg from general where ip like '$ip[ip2]%' and npc<2 order by ip";
    $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($genResult);
    for($k=0; $k < $genCount; $k++) {
        $gen = MYDB_fetch_array($genResult);
        if($gen['block'] > 0) $genName .= "<font color=magenta>{$gen['name']}</font><br>";
        else $genName .= $gen['name']."<br>";
        $genDate .= $gen['lastconnect']."<br>";
        $genIP   .= $gen['ip']."<br>";
        $genID   .= $gen['user_id']."<br>";
        $conMsg  .= $gen['conmsg']."<br>";
    }
    $genName .= "<br>";
    $genDate .= "<br>";
    $genIP   .= "<br>";
    $genID   .= "<br>";
    $conMsg  .= "<br>";
}
echo "
        <td align=right>$genName</td>
        <td>$genDate</td>
        <td>$genIP</td>
        <td>$genID</td>
        <td>$conMsg</td>";
?>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td align=center width=100>장수명</td>
        <td align=center width=180>최근로그인</td>
        <td align=center width=129>Password</td>
        <td align=center width=100>ID</td>
        <td align=center width=278>-</td>
    </tr>
    <tr>
<?php
$query = "select password from general where npc<2 group by password having count(*)>1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$ipCount = MYDB_num_rows($result);
$genName = "";
$genDate = "";
$genIP   = "";
$genID   = "";
$conMsg  = "";
for($i=0; $i < $ipCount; $i++) {
    $ip = MYDB_fetch_array($result);

    $query = "select name,password,lastconnect,user_id,block,conmsg from general where password='{$ip['password']}' and npc<2";
    $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($genResult);
    for($k=0; $k < $genCount; $k++) {
        $gen = MYDB_fetch_array($genResult);
        if($gen['block'] > 0) $genName .= "<font color=magenta>{$gen['name']}</font><br>";
        else $genName .= $gen['name']."<br>";
        $genDate .= $gen['lastconnect']."<br>";
        $genIP   .= substr($gen['password'],0,10)." ...<br>";
        $genID   .= $gen['user_id']."<br>";
        $conMsg  .= $gen['conmsg']."<br>";
    }
    $genName .= "<br>";
    $genDate .= "<br>";
    $genIP   .= "<br>";
    $genID   .= "<br>";
    $conMsg  .= "<br>";
}
echo "
        <td align=right>$genName</td>
        <td>$genDate</td>
        <td>$genIP</td>
        <td>$genID</td>
        <td>$conMsg</td>";
?>
    </tr>
</table>
</form>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php backButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
</body>
</html>
