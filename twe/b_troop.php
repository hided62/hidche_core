<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "부대편성", 1);

$query = "select skin,no,nation,troop from general where user_id='$_SESSION['p_id']'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select * from troop where nation='$me['nation']'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$troopcount = MYDB_num_rows($result);

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}
?>
<html>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>부대편성</title>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>

<body oncontextmenu='return false'>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>부 대 편 성<br><?php backButton(); ?></td></tr>
</table>
<form name=form1 method=post action=c_troop.php>
<table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td align=center width=64  id=bg1>선 택</td>
        <td align=center width=98  id=bg1>부 대 정 보</td>
        <td align=center width=64  id=bg1>부 대 장</td>
        <td align=center width=662 id=bg1 style=table-layout:fixed;word-break:break-all;>장 수</td>
        <td align=center width=98  id=bg1 style=table-layout:fixed;word-break:break-all;>부대장행동</td>
    </tr>
<?php
for($i=0; $i < $troopcount; $i++) {
    $troop = MYDB_fetch_array($result);

    $genlist = "";
    $query = "select no,name,picture,imgsvr,turntime,city,turn0,turn1,turn2,turn3,turn4,turn5 from general where troop='$troop['troop']'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        $genlist .= $general['name'].", ";
        if($troop['no'] == $general['no']) {
            $picture = $general['picture'];
            $imageTemp = GetImageURL($general['imgsvr']);
            $name = $general['name'];
            $turntime = $general['turntime'];
            $query = "select name from city where city='$general['city']'";
            $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($cityresult);
            $cityname = $city['name'];
            $turn = "";
            for($k=0; $k < 5; $k++) {
                $m = $k+1;
                if($general["turn{$k}"] == 26) {
                    $turn .= "&nbsp;$m : 집합<br>";
                } else {
                    $turn .= "&nbsp;$m : ∼<br>";
                }
            }
        }
    }
    $genlist .= "({$gencount}명)";

    if($me['troop'] == 0) {
        echo "
    <tr>
        <td align=center rowspan=2><input "; echo $i==0?"checked ":""; echo "type=radio name=troop value='{$troop['troop']}'></td>
        <td align=center >$troop['name']<br>【 $cityname 】</td>
        <td height=64 background={$imageTemp}/{$picture}>&nbsp;</td>
        <td rowspan=2 width=662>$genlist</td>
        <td rowspan=2>$turn</td>
    </tr>
    <tr><td align=center><font size=2>【턴】".substr($turntime, 14)."</font></td><td align=center><font size=1>$name</font></td></tr>
    <tr><td colspan=5>";
    } else {
        echo "
    <tr>
        <td align=center rowspan=2>&nbsp;</td>
        <td align=center >$troop['name']<br>【 $cityname 】</td>
        <td height=64 background={$imageTemp}/{$picture}>&nbsp;</td>
        <td rowspan=2 width=662>$genlist</td>
        <td rowspan=2>";

        if($troop['no'] == $me['no']) {
            $query = "select no,name from general where troop='$troop['troop']' and no!='$me['no']' order by binary(name)";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $genCount = MYDB_num_rows($genresult);
                echo "
            <select name=gen size=3 style=color:white;background-color:black;font-size:13;width:98;>";
            for($k=0; $k < $genCount; $k++) {
                $general = MYDB_fetch_array($genresult);
                echo "
                <option value=$general['no']>$general['name']</option>";
            }
            echo "
            </select><br>
            <input type=submit name=btn value='부 대 추 방' style=width:100;height:25;>";
        } else {
            echo $turn;
        }

        echo "
        </td>
    </tr>
    <tr><td align=center><font size=2>【턴】".substr($turntime, 14)."</font></td><td align=center><font size=1>$name</font></td></tr>
    <tr><td colspan=5>";
    }
}

if($me['troop'] == 0) {
    echo"
<input type=submit name=btn value='부 대 가 입'>";
} else {
    echo"
<input type=submit name=btn value='부 대 탈 퇴' onclick='return confirm(\"정말 부대를 탈퇴하시겠습니까?\")'>";
}

echo "
</td></tr>
</table>
<br>";

echo "
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=80 id=bg1>부 대 명</td>
        <td width=100><input type=text style=color:white;background-color:black; size=12 maxlength=6 name=name></td>";
if($me['troop'] == 0) {
    echo "
        <td><input type=submit name=btn value='부 대 창 설'></td>";
} else {
    echo "
        <td><input type=submit name=btn value='부 대 변 경'></td>";
}
echo "
    </tr>
</table>";

MYDB_close($connect);
?>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php backButton(); ?></td></tr>
    <tr><td><?php banner(); ?> </td></tr>
</table>
</form>
<?php PrintElapsedTime(); ?>
</body>
</html>

