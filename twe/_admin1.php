<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

if(getUserGrade() < 5) {
    echo "<!DOCTYPE html>
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
</head>
<body>
관리자가 아닙니다.<br>
";
    echo banner();
    echo "
</body>
</html>";

    exit();
}

$admin = getAdmin($connect);
?>
<!DOCTYPE html>
<html>
<head>
<title>게임관리</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>게 임 관 리<br><?=backButton()?></td></tr>
</table>
<form name=form1 method=post action=_admin1_submit.php>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td width=110 align=right>운영자메세지</td>
        <td colspan=3><input type=textarea size=90 style=color:white;background-color:black; name=msg value='<?=$admin['msg'];?>'><input type=submit name=btn value=변경></td></td>
    </tr>
    <tr><td width=110 align=right>중원정세추가</td>
        <td colspan=3><input type=textarea size=90 maxlength=80 style=color:white;background-color:black; name=log><input type=submit name=btn value=로그쓰기></td></td>
    </tr>
    <tr><td width=110 align=right>쿼리 요청</td>
        <td colspan=3><input type=textarea size=90 maxlength=150 style=color:white;background-color:black; name=q><input type=submit name=btn value=요청></td></td>
    </tr>
    <tr>
        <td width=110 align=right>시작시간변경</td>
        <td width=285><input type=text size=20 maxlength=20 style=color:white;background-color:black;text-align:right; name=starttime value='<?=$admin['starttime'];?>'><input type=submit name=btn value=변경1></td>
        <td width=110 align=right>현재도시훈사</td>
        <td width=285><?=$admin['city_rate'];?></td>
    </tr>
    <tr>
        <td width=110 align=right>최대 장수</td>
        <td width=285><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=maxgeneral value=<?=$admin['maxgeneral'];?>><input type=submit name=btn value=변경2></td>
        <td width=110 align=right>최대 국가</td>
        <td width=285><input type=text size=3 maxlength=2 style=color:white;background-color:black;text-align:right; name=maxnation value=<?=$admin['maxnation'];?>><input type=submit name=btn value=변경3></td>
    </tr>
    <tr>
        <td width=110 align=right>기준 장수수</td>
        <td width=285><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=gen_rate value=<?=$admin['normgeneral'];?>><input type=submit name=btn value=변경5></td>
        <td width=110 align=right>현재 수입률</td>
        <td width=285><?=$admin['gold_rate'];?>%</td>
    </tr>
    <tr>
        <td width=110 align=right>시작 년도</td>
        <td width=285><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=startyear value='<?=$admin['startyear'];?>'><input type=submit name=btn value=변경4></td>
        <td width=110 align=right>최근 갱신 시간</td>
        <td width=285>&nbsp;<?=$admin['turntime']?></td>
    </tr>
    <tr>
        <td width=110 align=right>턴시간</td>
        <td colspan=3><input type=submit name=btn value=1분턴><input type=submit name=btn value=2분턴><input type=submit name=btn value=5분턴><input type=submit name=btn value=10분턴><input type=submit name=btn value=20분턴><input type=submit name=btn value=30분턴><input type=submit name=btn value=60분턴><input type=submit name=btn value=120분턴></td>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td align=right><input type=submit name=btn value=변경6></td>
        <td align=center>공격</td>
        <td align=center>방어</td>
        <td align=center>기동</td>
        <td align=center>회피</td>
        <td align=center>가격</td>
        <td align=center>군량</td>
    </tr>

<?php
    for($i=0; $i <= 5; $i++) {
        $att = $admin["att{$i}"];
        $def = $admin["def{$i}"];
        $spd = $admin["spd{$i}"];
        $avd = $admin["avd{$i}"];
        $cst = $admin["cst{$i}"];
        $ric = $admin["ric{$i}"];
        echo "
    <tr>
        <td align=right>".getTypename($i)."</td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=att{$i} value=$att></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=def{$i} value=$def></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=spd{$i} value=$spd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=avd{$i} value=$avd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=cst{$i} value=$cst></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=ric{$i} value=$ric></td>
    </tr>";
    }
    echo "
    <tr><td colspan=7 height=5></td></tr>";

    for($i=10; $i <= 14; $i++) {
        $att = $admin["att{$i}"];
        $def = $admin["def{$i}"];
        $spd = $admin["spd{$i}"];
        $avd = $admin["avd{$i}"];
        $cst = $admin["cst{$i}"];
        $ric = $admin["ric{$i}"];
        echo "
    <tr>
        <td align=right>".getTypename($i)."</td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=att{$i} value=$att></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=def{$i} value=$def></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=spd{$i} value=$spd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=avd{$i} value=$avd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=cst{$i} value=$cst></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=ric{$i} value=$ric></td>
    </tr>";
    }
    echo "
    <tr><td colspan=7 height=5></td></tr>";

    for($i=20; $i <= 27; $i++) {
        $att = $admin["att{$i}"];
        $def = $admin["def{$i}"];
        $spd = $admin["spd{$i}"];
        $avd = $admin["avd{$i}"];
        $cst = $admin["cst{$i}"];
        $ric = $admin["ric{$i}"];
        echo "
    <tr>
        <td align=right>".getTypename($i)."</td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=att{$i} value=$att></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=def{$i} value=$def></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=spd{$i} value=$spd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=avd{$i} value=$avd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=cst{$i} value=$cst></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=ric{$i} value=$ric></td>
    </tr>";
    }
    echo "
    <tr><td colspan=7 height=5></td></tr>";

    for($i=30; $i <= 38; $i++) {
        $att = $admin["att{$i}"];
        $def = $admin["def{$i}"];
        $spd = $admin["spd{$i}"];
        $avd = $admin["avd{$i}"];
        $cst = $admin["cst{$i}"];
        $ric = $admin["ric{$i}"];
        echo "
    <tr>
        <td align=right>".getTypename($i)."</td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=att{$i} value=$att></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=def{$i} value=$def></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=spd{$i} value=$spd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=avd{$i} value=$avd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=cst{$i} value=$cst></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=ric{$i} value=$ric></td>
    </tr>";
    }
    echo "
    <tr><td colspan=7 height=5></td></tr>";

    for($i=40; $i <= 43; $i++) {
        $att = $admin["att{$i}"];
        $def = $admin["def{$i}"];
        $spd = $admin["spd{$i}"];
        $avd = $admin["avd{$i}"];
        $cst = $admin["cst{$i}"];
        $ric = $admin["ric{$i}"];
        echo "
    <tr>
        <td align=right>".getTypename($i)."</td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=att{$i} value=$att></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=def{$i} value=$def></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=spd{$i} value=$spd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=avd{$i} value=$avd></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=cst{$i} value=$cst></td>
        <td align=center><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=ric{$i} value=$ric></td>
    </tr>";
    }
    echo "
    <tr><td colspan=7 height=5></td></tr>";
?>
</table>
</form>
<form name=form2 method=post action=reset.php>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=9>리 셋 요 청</td></tr>
    <tr>
        <td align=right>HOSTNAME</td>
        <td align=center><input type=text size=12 maxlength=50 style=color:white;background-color:black; name=hostname value=localhost></td>
        <td align=center>DB_ID</td>
        <td align=center><input type=text size=12 maxlength=12 style=color:white;background-color:black; name=user_id></td>
        <td align=center>PASSWORD</td>
        <td align=center><input type=password size=12 maxlength=12 style=color:white;background-color:black; name=password></td>
        <td align=center>DB_NAME</td>
        <td align=center><input type=text size=12 maxlength=12 style=color:white;background-color:black; name=dbname></td>
        <td align=center><input type=submit value=요청></td>
    </tr>
</table>
</form>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
