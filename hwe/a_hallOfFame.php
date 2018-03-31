<?php
namespace sammo;

include "lib.php";
include "func.php";
$connect = dbConn();
increaseRefresh("명예의전당", 2);
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>명예의 전당</title>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=Font-size:13;word-break:break-all; id=bg0>
    <tr><td>명 예 의 전 당<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<?php
$type = array(
    "명 성",
    "계 급",
    "계 략 성 공",
    "전 투 횟 수",
    "승 리",
    "승 률",
    "사 살",
    "살 상 률",
    "보 병 숙 련 도",
    "궁 병 숙 련 도",
    "기 병 숙 련 도",
    "귀 병 숙 련 도",
    "차 병 숙 련 도",
    "전 력 전 승 률",
    "통 솔 전 승 률",
    "일 기 토 승 률",
    "설 전 승 률",
    "베 팅 투 자 액",
    "베 팅 당 첨",
    "베 팅 수 익 금",
    "베 팅 수 익 률"
);
for($i=0; $i < 21; $i++) {
    $query = "select * from hall where type={$i} order by rank";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    echo "
    <tr><td align=center colspan=10 id=bg1><font size=4>$type[$i]</font></td></tr>
    <tr align=center id=bg2><td>1위</td><td>2위</td><td>3위</td><td>4위</td><td>5위</td><td>6위</td><td>7위</td><td>8위</td><td>9위</td><td>10위</td></tr>
    <tr>";

    for($k=0; $k < 10; $k++) {
        $gen = MYDB_fetch_array($result);
        $name[$k]   = $gen['name'];
        $nation[$k] = $gen['nation'];
        $data[$k]   = $gen['data'];
        $color[$k]  = $gen['color'];
        $pic[$k]    = $gen['picture'];
        if($color[$k] == "") $color[$k] = $_basecolor4;
        if($nation[$k] == "") $nation[$k] = "&nbsp;";
/*
        if($pic[$k] == "") {
            echo "<td align=center>&nbsp;</td>";
        } else {
            $imageTemp = GetImageURL($gen['imgsvr']);
            echo "<td align=center><img src={$imageTemp}/{$pic[$k]}></img></td>";
        }
*/
    }

//    echo "</tr><tr>";

    for($k=0; $k < 10; $k++) {
        echo "<td align=center style=background-color:{$color[$k]};color:".newColor($color[$k]).">{$nation[$k]}</td>";
    }

    echo "</tr><tr>";

    for($k=0; $k < 10; $k++) {
        echo "<td align=center style=background-color:{$color[$k]};color:".newColor($color[$k]).">{$name[$k]}</td>";
    }

    echo "</tr><tr>";

    for($k=0; $k < 10; $k++) {
        if($i == 5 || $i == 7 || $i == 20) { $data[$k] = floor($data[$k]/100).".".($data[$k]%100)." %"; }
        if($i >= 13 && $i <= 16) { $data[$k] = floor($data[$k]/100).".".($data[$k]%100)." %"; }
        echo "<td align=center>{$data[$k]}</td>";
    }
    echo "</tr><tr><td colspan=10 height=5 id=bg1></td></tr>";
}
?>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=Font-size:13;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

