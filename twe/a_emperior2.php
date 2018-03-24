<?php
namespace sammo;

include "lib.php";
include "func.php";
$connect = dbConn();
increaseRefresh("왕조일람", 2);
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>왕조일람</title>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>역 대 왕 조<br>
        <input type=button value='창 닫기' onclick=window.close()><br>
    </td></tr>
</table>

<?php

if($select == 0) {
    $query = "select * from emperior_table order by no desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $empcount = MYDB_num_rows($result);

    for($i=0; $i < $empcount; $i++) {
        $emperior = MYDB_fetch_array($result);

        echo "
<form action=a_emperior2.php method=post>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td bgcolor=skyblue align=center colspan=8>
            <font size=5>{$emperior['phase']}</font>
            <input type=submit value='자세히'>
            <input type=hidden name=select value='{$emperior['no']}'>
        </td>
    </tr>
    <tr>
        <td align=center style=color:".newColor($emperior['color'])."; bgcolor={$emperior['color']} colspan=8>
            <font size=5>{$emperior['name']} ({$emperior['year']}年 {$emperior['month']}月)</font>
        </td>
    </tr>
    <tr>
        <td id=bg1 align=center width=123>국 력</td>
        <td align=center width=123>{$emperior['power']}</td>
        <td id=bg1 align=center width=123>장 수</td>
        <td align=center width=123>{$emperior['gennum']}</td>
        <td id=bg1 align=center width=123>속 령</td>
        <td align=center width=123>{$emperior['citynum']}</td>
        <td id=bg1 align=center width=123>성 향</td>
        <td align=center width=123>{$emperior['type']}</td>
    </tr>
    <tr>
        <td id=bg1 width=123 align=center>황 제</td>
        <td width=123 align=center>{$emperior['l12name']}</td>
        <td id=bg1 width=123 align=center>승 상</td>
        <td width=123 align=center>{$emperior['l11name']}</td>
        <td id=bg1 width=123 align=center>위 장 군</td>
        <td width=123 align=center>{$emperior['l10name']}</td>
        <td id=bg1 width=123 align=center>사 공</td>
        <td width=123 align=center>{$emperior['l9name']}</td>
    </tr>
</table>
</form>";
    }
} else {
    $query = "select * from emperior_table where no='$select'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $emperior = MYDB_fetch_array($result);

//background={$image}/{$emperior['l12pic']}

    echo "
<form action=a_emperior2.php method=post>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td bgcolor=skyblue align=center colspan=6>
            <font size=5>{$emperior['phase']}</font>
            <input type=submit value='전체보기'>
            <input type=hidden name=select value='0'>
        </td>
    </tr>
    <tr>
        <td id=bg1 width=98 align=center>국 가 수<br>(최종 / 최대)</td>
        <td align=center width=398 colspan=2>{$emperior['nation_count']}</td>
        <td id=bg1 width=98 align=center>장 수 수<br>(최종 / 최대)</td>
        <td align=center width=398 colspan=2>{$emperior['gen_count']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>등 장 국 가</td>
        <td colspan=5>{$emperior['nation_name']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>국가별 성향</td>
        <td colspan=5>{$emperior['nation_hist']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>장 수 성 격</td>
        <td colspan=5>{$emperior['personal_hist']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>장 수 특 기</td>
        <td colspan=5>{$emperior['special_hist']}</td>
    </tr>
    <tr>
        <td align=center style=color:".newColor($emperior['color'])."; bgcolor={$emperior['color']} colspan=6>
            <font size=5>{$emperior['name']} ({$emperior['year']}年 {$emperior['month']}月)</font>
        </td>
    </tr>
    <tr>
        <td id=bg1 width=98 align=center>국 력</td>
        <td align=center width=398 colspan=2>{$emperior['power']}</td>
        <td id=bg1 width=98 align=center>성 향</td>
        <td align=center width=398 colspan=2>{$emperior['type']}</td>
    </tr>
    <tr>
        <td id=bg1 width=98 align=center>장 수</td>
        <td align=center width=398 colspan=2>{$emperior['gennum']}</td>
        <td id=bg1 width=98 align=center>속 령</td>
        <td align=center width=398 colspan=2>{$emperior['citynum']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>총 인 구</td>
        <td align=center colspan=2>{$emperior['pop']}</td>
        <td id=bg1 align=center>인 구 율</td>
        <td align=center colspan=2>{$emperior['poprate']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>국 고</td>
        <td align=center colspan=2>{$emperior['gold']}</td>
        <td id=bg1 align=center>병 량</td>
        <td align=center colspan=2>{$emperior['rice']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>황 제</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l12name']}</td>
        <td id=bg1 align=center>승 상</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l11name']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>위 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l10name']}</td>
        <td id=bg1 align=center>사 공</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l9name']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>표 기 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l8name']}</td>
        <td id=bg1 align=center>태 위</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l7name']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>거 기 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l6name']}</td>
        <td id=bg1 align=center>사 도</td>
        <td width=64>&nbsp;</td>
        <td width=332>{$emperior['l5name']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>오 호 장 군</td>
        <td colspan=5>{$emperior['tiger']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>건 안 칠 자</td>
        <td colspan=5>{$emperior['eagle']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>장 수 들<br>(공헌도 순서)</td>
        <td colspan=5>{$emperior['gen']}</td>
    </tr>
    <tr>
        <td id=bg1 align=center>역 사 기 록</td>
        <td colspan=5>".ConvertLog($emperior['history'], 1)."</td>
    </tr>
</table>
</form>";
}
?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

