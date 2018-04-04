<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

$connect = dbConn();
increaseRefresh("명장일람", 2);

$query = "select conlimit from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select con,turntime from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>명장일람</title>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>명 장 일 람<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<form name=form1 action=a_bestGeneral.php method=post>
    <tr><td align=center>
        <input type=submit name=btn value='유저 보기'>
        <input type=submit name=btn value='NPC 보기'>
    </td></tr>
</form>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<?php
if(isset($btn) && $btn == "NPC 보기") {
    $sel = "npc>=2";
} else {
    $sel = "npc<2";
}

foreach(getAllNationStaticInfo() as $nation){
    $nationName[$nation['nation']] = $nation['name'];
    $nationColor[$nation['nation']] = $nation['color'];
}

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
    switch($i) {
    case  0: $query = "select nation,no,name,picture,imgsvr,experience as data from general where $sel order by data desc limit 0,10"; break;
    case  1: $query = "select nation,no,name,picture,imgsvr,dedication as data from general where $sel order by data desc limit 0,10"; break;
    case  2: $query = "select nation,no,name,picture,imgsvr,firenum as data from general where $sel order by data desc limit 0,10"; break;
    case  3: $query = "select nation,no,name,picture,imgsvr,warnum as data from general where $sel order by data desc limit 0,10"; break;
    case  4: $query = "select nation,no,name,picture,imgsvr,killnum as data from general where $sel order by data desc limit 0,10"; break;
    case  5: $query = "select nation,no,name,picture,imgsvr,killnum/warnum*10000 as data from general where warnum>=10 and $sel order by data desc limit 0,10"; break;
    case  6: $query = "select nation,no,name,picture,imgsvr,killcrew as data from general where $sel order by data desc limit 0,10"; break;
    case  7: $query = "select nation,no,name,picture,imgsvr,killcrew/deathcrew*10000 as data from general where warnum>=10 and $sel order by data desc limit 0,10"; break;
    case  8: $query = "select nation,no,name,picture,imgsvr,dex0 as data from general where $sel order by data desc limit 0,10"; break;
    case  9: $query = "select nation,no,name,picture,imgsvr,dex10 as data from general where $sel order by data desc limit 0,10"; break;
    case 10: $query = "select nation,no,name,picture,imgsvr,dex20 as data from general where $sel order by data desc limit 0,10"; break;
    case 11: $query = "select nation,no,name,picture,imgsvr,dex30 as data from general where $sel order by data desc limit 0,10"; break;
    case 12: $query = "select nation,no,name,picture,imgsvr,dex40 as data from general where $sel order by data desc limit 0,10"; break;
    case 13: $query = "select nation,no,name,picture,imgsvr,ttw/(ttw+ttd+ttl)*10000 as data from general where $sel and (ttw+ttd+ttl)>=50 order by data desc limit 0,10"; break;
    case 14: $query = "select nation,no,name,picture,imgsvr,tlw/(tlw+tld+tll)*10000 as data from general where $sel and (tlw+tld+tll)>=50 order by data desc limit 0,10"; break;
    case 15: $query = "select nation,no,name,picture,imgsvr,tpw/(tpw+tpd+tpl)*10000 as data from general where $sel and (tpw+tpd+tpl)>=50 order by data desc limit 0,10"; break;
    case 16: $query = "select nation,no,name,picture,imgsvr,tiw/(tiw+tid+til)*10000 as data from general where $sel and (tiw+tid+til)>=50 order by data desc limit 0,10"; break;
    case 17: $query = "select nation,no,name,picture,imgsvr,betgold as data from general where $sel order by data desc limit 0,10"; break;
    case 18: $query = "select nation,no,name,picture,imgsvr,betwin as data from general where $sel order by data desc limit 0,10"; break;
    case 19: $query = "select nation,no,name,picture,imgsvr,betwingold as data from general where $sel order by data desc limit 0,10"; break;
    case 20: $query = "select nation,no,name,picture,imgsvr,betwingold/betgold*10000 as data from general where $sel and betgold >= 1000 order by data desc limit 0,10"; break;
    }
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    echo "
    <tr><td align=center colspan=10 id=bg1><font size=4>$type[$i]</font></td></tr>
    <tr align=center id=bg2><td>1위</td><td>2위</td><td>3위</td><td>4위</td><td>5위</td><td>6위</td><td>7위</td><td>8위</td><td>9위</td><td>10위</td></tr>
    <tr>";

    for($k=0; $k < 10; $k++) {
        $gen = MYDB_fetch_array($result);

        if($i != 2) {
            if(isset($gen)) {
                $name[$k] = $gen['name'];
                $nation[$k] = $gen['nation'] == 0 ? "재야" : $nationName[$gen['nation']];
                $data[$k] = $gen['data'];
                $color[$k] = $gen['nation'] == 0 ? "#FFFFFF" : $nationColor[$gen['nation']];
                $pic[$k] = $gen['picture'];
            }else{
                $name[$k] = "-";
                $nation[$k] = "-";
                $data[$k] = "-";
                $color[$k] = $_basecolor4;
                $pic[$k] = "";
            }
        } else {
            $name[$k]   = "???";
            $nation[$k] = "???";
            $data[$k]   = $gen['data'];
            $color[$k]  = $_basecolor4;
            $gen['imgsvr'] = 0;
            $pic[$k]    = "9999.jpg";
        }
        if($color[$k] == "") $color[$k] = $_basecolor4;
        if($nation[$k] == "") $nation[$k] = "&nbsp;";
        if($pic[$k] == "") {
            echo "<td align=center>&nbsp;</td>";
        } else {
            $imageTemp = GetImageURL($gen['imgsvr']);
            echo "<td align=center><img src={$imageTemp}/{$pic[$k]}></img></td>";
        }
    }

    echo "</tr><tr>";

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

echo "
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
";

$type = array(
    "명 마",
    "명 검",
    "명 서",
    "도 구"
);

$call = array(
    "horse",
    "weap",
    "book",
    "item"
);

$func = array(
    "getHorseName",
    "getWeapName",
    "getBookName",
    "getItemName"
);

for($i=0; $i < 4; $i++) {
    echo "
    <tr><td align=center colspan=10 id=bg1><font size=4>$type[$i]</font></td></tr>
    <tr align=center id=bg2>";
    for($k=26; $k > 16; $k--) {
        $str = $func[$i]($k);
        echo "<td>".$str."</td>";
    }

    echo "</tr><tr>";

    for($k=26; $k > 16; $k--) {
        $query = "select nation,no,name,picture,imgsvr from general where {$call[$i]}={$k}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen = MYDB_fetch_array($result);
        if(isset($gen)) {
            $name[$k] = $gen['name'];
            $nation[$k] = $gen['nation'] == 0 ? "재야" : $nationName[$gen['nation']];
            $color[$k] = $gen['nation'] == 0 ? "#FFFFFF" : $nationColor[$gen['nation']];
            $pic[$k] = $gen['picture'];
        }else{
            $name[$k] = "미발견";
            $nation[$k] = "-";
            $color[$k] = "";
            $pic[$k] = "";
        }
        if($color[$k] == "") $color[$k] = $_basecolor4;
        if($nation[$k] == "") $nation[$k] = "&nbsp;";
        if($pic[$k] == "") {
            echo "<td align=center>&nbsp;</td>";
        } else {
            $imageTemp = GetImageURL($gen['imgsvr']);
            echo "<td align=center><img src={$imageTemp}/{$pic[$k]}></img></td>";
        }
    }

    echo "</tr><tr>";

    for($k=26; $k > 16; $k--) {
        echo "<td align=center style=background-color:{$color[$k]};color:".newColor($color[$k]).">{$nation[$k]}</td>";
    }

    echo "</tr><tr>";

    for($k=26; $k > 16; $k--) {
        echo "<td align=center style=background-color:{$color[$k]};color:".newColor($color[$k]).">{$name[$k]}</td>";
    }

    echo "</tr><tr><td colspan=10 height=5 id=bg1></td></tr>";

    echo "
    <tr align=center id=bg2>";
    for($k=16; $k > 6; $k--) {
        $str = $func[$i]($k);
        echo "<td>".$str."</td>";
    }

    echo "</tr><tr>";

    for($k=16; $k > 6; $k--) {
        $query = "select nation,no,name,picture,imgsvr from general where {$call[$i]}={$k}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen = MYDB_fetch_array($result);
        if(isset($gen)) {
            $name[$k] = $gen['name'];
            $nation[$k] = $gen['nation'] == 0 ? "재야" : $nationName[$gen['nation']];
            $color[$k] = $gen['nation'] == 0 ? "#FFFFFF" : $nationColor[$gen['nation']];
            $pic[$k] = $gen['picture'];
        }else{
            $name[$k] = "미발견";
            $nation[$k] = "-";
            $color[$k] = "";
            $pic[$k] = "";
        }
        if($color[$k] == "") $color[$k] = $_basecolor4;
        if($nation[$k] == "") $nation[$k] = "&nbsp;";
        if($pic[$k] == "") {
            echo "<td align=center>&nbsp;</td>";
        } else {
            $imageTemp = GetImageURL($gen['imgsvr']);
            echo "<td align=center><img src={$imageTemp}/{$pic[$k]}></img></td>";
        }
    }

    echo "</tr><tr>";

    for($k=16; $k > 6; $k--) {
        echo "<td align=center style=background-color:{$color[$k]};color:".newColor($color[$k]).">{$nation[$k]}</td>";
    }

    echo "</tr><tr>";

    for($k=16; $k > 6; $k--) {
        echo "<td align=center style=background-color:{$color[$k]};color:".newColor($color[$k]).">{$name[$k]}</td>";
    }

    echo "</tr><tr><td colspan=10 height=5 id=bg1></td></tr>";
}

?>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

