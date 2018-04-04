<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if($session->userGrade() < 5) {
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

$db = DB::db();
$connect=$db->get();

if($btn == '정렬하기') {
    $gen = 0;
}

if($type == 0) {
    $type = 0;
}
$sel[$type] = "selected";
?>
<!DOCTYPE html>
<html>
<head>
<title>로그정보</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>로 그 정 보<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>
        정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0];?> value=0>최근턴</option>
            <option <?=$sel[1];?> value=1>최근전투</option>
            <option <?=$sel[2];?> value=2>장수명</option>
            <option <?=$sel[3];?> value=3>전투수</option>
        </select>
        <input type=submit name=btn value='정렬하기'>
        대상장수 :
        <select name=gen size=1>
<?php
switch($type) {
    case 0: $query = "select no,name from general order by turntime desc"; break;
    case 1: $query = "select no,name from general order by recwar desc"; break;
    case 2: $query = "select no,name from general order by npc,binary(name)"; break;
    case 3: $query = "select no,name from general order by warnum desc"; break;
}
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

for($i=0; $i < $gencount; $i++) {
    $general = MYDB_fetch_array($result);
    // 선택 없으면 맨 처음 장수
    if($gen == 0) {
        $gen = $general['no'];
    }
    if($gen == $general['no']) {
        echo "
            <option selected value={$general['no']}>{$general['name']}</option>";
    } else {
        echo "
            <option value={$general['no']}>{$general['name']}</option>";
    }
}
?>
        </select>
        <input type=submit name=btn value='조회하기'>
        </form>
    </td></tr>
</table>
<table width=1000 align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=50% align=center id=bg1><font color=skyblue size=3>장 수 정 보</font></td>
        <td width=50% align=center id=bg1><font color=orange size=3>-</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?php generalInfo($gen); generalInfo2($gen); ?>
        </td>
        <td valign=top>&nbsp;
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>개인 기록</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 기록</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=getGenLogRecent($gen, 24)?>
        </td>
        <td valign=top>
            <?=getBatLogRecent($gen, 24)?>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>장수 열전</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 결과</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=getGeneralHistoryAll($gen)?>
        </td>
        <td valign=top>
            <?=getBatResRecent($gen, 24)?>
        </td>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
