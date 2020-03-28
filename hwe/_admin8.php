<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$gen = Util::getReq('gen', 'int', 0);
$type = 0;

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if ($session->userGrade < 5) {
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
$connect=$db->get();

if ($btn == '정렬하기') {
    $gen = 0;
}

$sel[$type] = "selected";
?>
<!DOCTYPE html>
<html>
<head>
<title>외교정보</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>
<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>외 교 정 보<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0]??''?> value=0>상태</option>
        </select>
        <input type=submit name=btn value='정렬하기'>
        </form>
    </td></tr>
</table>
<table width=1000 align=center class='tb_layout bg0'>
    <tr><td colspan=9 align=center bgcolor=blue>외 교 관 계</td></tr>
    <tr>
        <td width=130 align=center id=bg1>국 가 명</td>
        <td width=130 align=center id=bg1>국 가 명</td>
        <td width=80  align=center id=bg1>상 태</td>
        <td width=60  align=center id=bg1>기 간</td>
    </tr>
<?php

$nationName = [];
$nationColor = [];
foreach (getAllNationStaticInfo() as $nation) {
    $nationName[$nation['nation']] = $nation['name'];
    $nationColor[$nation['nation']] = $nation['color'];
}


$query = "select * from diplomacy where me < you order by state desc";

$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$dipcount = MYDB_num_rows($result);
for ($i=0; $i < $dipcount; $i++) {
    $dip = MYDB_fetch_array($result);

    $me = $dip['me'];
    $you = $dip['you'];

    if ($dip['state'] == 2) {
        continue;
    }

    switch ($dip['state']) {
        case 0: $state = "<font color=red>교 전</font>"; break;
        case 1: $state = "<font color=magenta>선포중</font>"; break;
        case 2: $state = "통 상"; break;
        case 3: $state = "<font color=cyan>통합수락중</font>"; break;
        case 4: $state = "<font color=cyan>통합제의중</font>"; break;
        case 5: $state = "<font color=cyan>합병수락중</font>"; break;
        case 6: $state = "<font color=cyan>합병제의중</font>"; break;
        case 7: $state = "<font color=green>불가침</font>"; break;
    }

    $date = TimeUtil::now();

    echo "
    <tr>
        <td align=center style=color:".newColor($nationColor[$me]).";background-color:{$nationColor[$me]};>$nationName[$me]</td>
        <td align=center style=color:".newColor($nationColor[$you]).";background-color:{$nationColor[$you]};>$nationName[$you]</td>
        <td align=center>$state</td>
        <td align=center>{$dip['term']} 개월</td>
    </tr>";
}
?>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
