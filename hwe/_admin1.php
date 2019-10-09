<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if($session->userGrade < 5) {
?>
<!DOCTYPE html>
<html>
<head>
<title>관리메뉴</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
관리자가 아닙니다.<br>
    <?=banner()?>
</body>
</html>
<?php
    exit();
}

$db = DB::db();

$admin = getAdmin();
?>
<!DOCTYPE html>
<html>
<head>
<title>게임관리</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
<table align=center width=1000 class="tb_layout bg0">
    <tr><td>게 임 관 리<br><?=backButton()?></td></tr>
</table>
<form name=form1 method=post action=_admin1_submit.php>
<table align=center width=1000 class="tb_layout bg0">
    <tr><td width=110 align=right>운영자메세지</td>
        <td colspan=3><input type=textarea size=90 style=color:white;background-color:black; name=msg value='<?=$admin['msg']?>'><input type=submit name=btn value=변경></td></td>
    </tr>
    <tr><td width=110 align=right>중원정세추가</td>
        <td colspan=3><input type=textarea size=90 maxlength=80 style=color:white;background-color:black; name=log><input type=submit name=btn value=로그쓰기></td></td>
    </tr>
    <tr>
        <td width=110 align=right>시작시간변경</td>
        <td width=285><input type=text size=20 maxlength=20 style=color:white;background-color:black;text-align:right; name=starttime value='<?=$admin['starttime']?>'><input type=submit name=btn value=변경1></td>
        <td width=110 align=right>현재도시훈사</td>
        <td width=285><?=$admin['city_rate']?></td>
    </tr>
    <tr>
        <td width=110 align=right>최대 장수</td>
        <td width=285><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=maxgeneral value=<?=$admin['maxgeneral']?>><input type=submit name=btn value=변경2></td>
        <td width=110 align=right>최대 국가</td>
        <td width=285><input type=text size=3 maxlength=2 style=color:white;background-color:black;text-align:right; name=maxnation value=<?=$admin['maxnation']?>><input type=submit name=btn value=변경3></td>
    </tr>
    <tr>
        <td width=110 align=right>시작 년도</td>
        <td width=285><input type=text size=3 maxlength=3 style=color:white;background-color:black;text-align:right; name=startyear value='<?=$admin['startyear']?>'><input type=submit name=btn value=변경4></td>
        <td width=110 align=right>최근 갱신 시간</td>
        <td width=285>&nbsp;<?=$admin['turntime']?></td>
    </tr>
    <tr>
        <td width=110 align=right>턴시간</td>
        <td colspan=3><input type=submit name=btn value=1분턴><input type=submit name=btn value=2분턴><input type=submit name=btn value=5분턴><input type=submit name=btn value=10분턴><input type=submit name=btn value=20분턴><input type=submit name=btn value=30분턴><input type=submit name=btn value=60분턴><input type=submit name=btn value=120분턴></td>
    </tr>
</table>
</form>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
