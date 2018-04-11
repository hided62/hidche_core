<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$gen = Util::getReq('gen', 'int', 0);
$type = Util::getReq('type', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if ($session->userGrade < 5) {
    echo "<!DOCTYPE html>
<html>
<head>
<title>관리메뉴</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel='stylesheet' href='../d_shared/common.css' type='text/css'>
<link rel='stylesheet' href='css/common.css' type='text/css'>
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

$db = DB::db();
$connect=$db->get();

if ($btn == '정렬하기') {
    $gen = 0;
}

if ($type == 0) {
    $type = 0;
}
$sel[$type] = "selected";
?>
<!DOCTYPE html>
<html>
<head>
<title>외교정보</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel='stylesheet' href='../d_shared/common.css' type='text/css'>
<link rel='stylesheet' href='css/common.css' type='text/css'>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>외 교 정 보<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0]?> value=0>상태</option>
        </select>
        <input type=submit name=btn value='정렬하기'>
        </form>
    </td></tr>
</table>
<table width=1000 align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=9 align=center bgcolor=blue>외 교 관 계</td></tr>
    <tr>
        <td width=100 align=center id=bg1>국 가 명</td>
        <td width=100 align=center id=bg1>국 가 명</td>
        <td width=80  align=center id=bg1>상 태</td>
        <td width=60  align=center id=bg1>기 간</td>
        <td align=center id=bg1>비 고</td>
    </tr>
<?php

$nationName = [];
$nationColor = [];
foreach (getAllNationStaticInfo() as $nation) {
    $nationName[$nation['nation']] = $nation['name'];
    $nationColor[$nation['nation']] = $nation['color'];
}

switch ($type) {
case 0: $query = "select * from diplomacy where me < you order by state desc"; break;
}
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$dipcount = MYDB_num_rows($result);
for ($i=0; $i < $dipcount; $i++) {
    $dip = MYDB_fetch_array($result);

    $me = $dip['me'];
    $you = $dip['you'];

    $query = "select reserved,showing from diplomacy where you='$me' and me='$you'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $dip2 = MYDB_fetch_array($result2);

    if ($dip['state'] == 2 && $dip['fixed'] == "" && $dip['reserved'] == "" && $dip2['reserved'] == "") {
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

    $date = date('Y-m-d H:i:s');
    $note = "";
    if ($dip['fixed'] != "") {
        if ($dip['state'] == 7) {
            $note .= $dip['fixed'];
        } else {
            $note .= "<font color=gray>{$dip['fixed']}</font>";
        }
        if ($dip['reserved'] != "" || $dip2['reserved'] != "") {
            $note .= "<br>";
        }
    }
    if ($dip['reserved'] != "") {
        if ($dip['showing'] >= $date) {
            $note .= "<font color=skyblue>아국측 제의</font>: {$dip['reserved']}";
        } else {
            $note .= "<font color=gray>아국측 제의: {$dip['reserved']}</font>";
        }
        if ($dip2['reserved'] != "") {
            $note .= "<br>";
        }
    }
    if ($dip2['reserved'] != "") {
        if ($dip2['showing'] >= $date) {
            $note .= "<font color=limegreen>상대측 제의</font>: {$dip2['reserved']}";
        } else {
            $note .= "<font color=gray>상대측 제의: {$dip2['reserved']}</font>";
        }
    }
    if ($note == "") {
        $note = "&nbsp;";
    }

    echo "
    <tr>
        <td align=center style=color:".newColor($nationColor[$me]).";background-color:{$nationColor[$me]};>$nationName[$me]</td>
        <td align=center style=color:".newColor($nationColor[$you]).";background-color:{$nationColor[$you]};>$nationName[$you]</td>
        <td align=center>$state</td>
        <td align=center>{$dip['term']} 개월</td>
        <td align=left style=font-size:7px;>{$note}</td>
    </tr>";
}
?>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
