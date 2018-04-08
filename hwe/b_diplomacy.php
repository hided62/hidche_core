<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("중원정보", 1);

$query = "select turnterm from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,nation,map from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select nation,color,name,power,gennum from nation where level>0 order by power desc";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nationcount = MYDB_num_rows($result);
$nationnum = array();

$nationStr = "";
$powerStr = "";
$genStr = "";
$cityStr = "";
for($i=0; $i < $nationcount; $i++) {
    $nation = MYDB_fetch_array($result);

    $query = "select city from city where nation='{$nation['nation']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);


    $nationnum[] = $nation['nation'];
    $nationname[$nation['nation']] = $nation['name'];
    $nationcolor[$nation['nation']] = $nation['color'];

    $nationStr .= "<font color=cyan>◆</font> <font style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</font><br>";
    $powerStr .= "국력 {$nation['power']}<br>";
    $genStr .= "장수 {$nation['gennum']}<br>";
    $cityStr .= "속령 $citycount<br>";
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>중원정보</title>
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="js/common.js"></script>
<script src="js/base_map.js"></script>
<script src="js/map.js"></script>
<script>
$(function(){

    reloadWorldMap({
        neutralView:true,
        showMe:true
    });

});
</script>
<link href="css/normalize.css" rel="stylesheet">
<link href="css/common.css" rel="stylesheet">
<link href="css/map.css" rel="stylesheet">

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>중 원 정 보<br><?=backButton()?></td></tr>
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=<?=$nationcount+1;?> align=center bgcolor=blue>외 교 현 황</td></tr>
<?php
echo "
    <tr>
        <td align=center width=108 style=background-color:".GameConst::$basecolor2.";>&nbsp;</td>";

if($nationcount != 0) {
    $width = floor(888 / $nationcount);
}

for($i=0; $i < $nationcount; $i++) {
    echo "
        <td align=center width={$width} style=background-color:{$nationcolor[$nationnum[$i]]};color:".newColor($nationcolor[$nationnum[$i]]).";>{$nationname[$nationnum[$i]]}</td>";
}
echo "
    </tr>";

for($i=0; $i < $nationcount; $i++) {
    $query = "select you,state from diplomacy where me='$nationnum[$i]'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount2 = MYDB_num_rows($dipresult);
    for($k=0; $k < $nationcount2; $k++) {
        $dip = MYDB_fetch_array($dipresult);
        $state[$dip['you']] = $dip['state'];
    }
    echo "
    <tr>
        <td align=center style=background-color:{$nationcolor[$nationnum[$i]]};color:".newColor($nationcolor[$nationnum[$i]]).";>{$nationname[$nationnum[$i]]}</td>";

    for($k=0; $k < $nationcount; $k++) {
        if($i == $k) {
            $str = "＼";
        } else {
            switch($state[$nationnum[$k]]) {
                case 0: $str = "<font color=red>★</font>"; break;
                case 1: $str = "<font color=magenta>▲</font>"; break;
                case 2:
                    if($nationnum[$i] == $me['nation'] || $nationnum[$k] == $me['nation'] || $session->userGrade >= 5) { $str = "ㆍ"; }
                    else { $str = "?"; }
//                    $str = "ㆍ";
                    break;
                case 3: $str = "<font color=cyan>○</font>"; break;
                case 4: $str = "<font color=cyan>○</font>"; break;
                case 5: $str = "<font color=cyan>◎</font>"; break;
                case 6: $str = "<font color=cyan>◎</font>"; break;
                case 7:
                    if($nationnum[$i] == $me['nation'] || $nationnum[$k] == $me['nation'] || $session->userGrade >= 5) { $str = "<font color=green>@</font>"; }
                    else { $str = "?"; }
//                    $str = "<font color=limegreen>@</font>";
                    break;
            }
        }

        if($nationnum[$i] == $me['nation'] || $nationnum[$k] == $me['nation']) { $backcolor = "style=background-color:".GameConst::$basecolor3.";"; }
        else { $backcolor = ""; }

        echo "
        <td align=center $backcolor>$str</td>";
    }
    echo "
    </tr>
";
}
?>
    <tr><td colspan=<?=$nationcount+1;?> align=center>불가침 : <font color=limegreen>@</font>, 통합 : <font color=cyan>○</font>, 합병 : <font color=skyblue>◎</font>, 통상 : ㆍ, 선포 : <font color=magenta>▲</font>, 교전 : <font color=red>★</font></td></tr>
</table>
<?php
$query = "select city,name,conflict,conflict2 from city where conflict like '%|%'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$citycount = MYDB_num_rows($result);

if($citycount != 0) {
    echo "
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=2 align=center bgcolor=magenta>분 쟁 현 황</td></tr>";
}

for($i=0; $i < $citycount; $i++) {
    $city = MYDB_fetch_array($result);

    if($city['conflict'] != "") {
        $nation = explode("|", $city['conflict']);
        $killnum = explode("|", $city['conflict2']);

        $seq = mySort($killnum);    // 큰 순서대로 순서를 구한다.

        $sum = 0;
        for($k=0; $k < count($killnum); $k++) {
            $sum += $killnum[$k];
        }
        echo "
        <tr>
            <td align=center width=48>{$city['name']}</td>
            <td width=948>";
        for($k=0; $k < count($nation); $k++) {
            $per = 100*$killnum[$seq[$k]] / $sum;
            $graph1 = $per / 100 * 798;
            $per = round($per, 1);
            echo "
                <table border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
                    <tr>
                        <td width=98 align=right style=color:".newColor($nationcolor[$nation[$seq[$k]]]).";background-color:{$nationcolor[$nation[$seq[$k]]]};>{$nationname[$nation[$seq[$k]]]}&nbsp;</td>
                        <td width=48 align=right>{$per}%&nbsp;</td>
                        <td width=$graph1 style=background-color:{$nationcolor[$nation[$seq[$k]]]};></td>
                        <td width=*></td>
                    </tr>
                </table>";
        }
        echo "
            </td>
        </tr>
        <tr><td colspan=2 height=5 id=bg1></td></tr>";
    }
}

function mySort($killnum) {
    for($i=0; $i < count($killnum); $i++) {
        $seq[$i] = $i;
    }
    for($i=0; $i < count($killnum); $i++) {
        $max = 0;
        for($k=0; $k < count($killnum); $k++) {
            if($max < $killnum[$k]) {
                $max = $killnum[$k];
                $index = $k;
            }
        }
        $seq[$i] = $index;
        $killnum[$index] = 0;
    }
    return $seq;
}

?>

</table>
<br>
<table class="bg0" align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style="font-size:13px;word-break:break-all;">
    <tr>
        <td colspan=5 align=center bgcolor=green><font size=3>중 원 지 도</font></td>
    </tr>
    <tr>
        <td width=698 height=420>
            <?=getMapHtml()?>
        </td>
        <td width=98 valign=top><?=$nationStr?></td>
        <td width=78 valign=top><?=$powerStr?></td>
        <td width=58 valign=top><?=$genStr?></td>
        <td width=58 valign=top><?=$cityStr?></td>
    </tr>
</table>
<br>

<table class="bg0" align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style="font-size:13px;word-break:break-all;">
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
