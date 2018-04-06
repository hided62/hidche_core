<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("인사부", 1);
//훼섭 추방을 위해 갱신
checkTurn();

$query = "select no,nation,level from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$meLevel = $me['level'];
if($meLevel == 0) {
    echo "재야입니다.";
    exit();
}

?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>인사부</title>
<link rel=stylesheet href=css/common.css type=text/css>
<script type="text/javascript">
function out() {
    return confirm('정말 추방하시겠습니까?');
}
</script>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>인 사 부<br><?=backButton()?></td></tr>
</table>
<br>

<?php

$query = "select nation,name,level,color,l12set,l11set,l10set,l9set,l8set,l7set,l6set,l5set from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);   //국가정보

$lv = getNationChiefLevel($nation['level']);
if($meLevel >= 5) { $btn = "submit"; }
else { $btn = "hidden"; }

$query = "select name,level,picture,imgsvr,belong from general where nation='{$nation['nation']}' and level>={$lv} order by level desc";
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
for($i=12; $i >= $lv; $i--) {
    $levels = MYDB_fetch_array($genresult);
    $level[$levels['level']] = $levels;
}

$query = "select name,picture,killnum from general where nation='{$nation['nation']}' order by killnum desc limit 5";   // 오호장군
$tigerresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "select name,picture,firenum from general where nation='{$nation['nation']}' order by firenum desc limit 7";   // 건안칠자
$eagleresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

echo "
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']} colspan=6>
            <font size=5>【 {$nation['name']} 】</font>
        </td>
    </tr>
";
for($i=12; $i >= $lv; $i-=2) {
    $i1 = $i;   $i2 = $i - 1;
    $imageTemp1 = GetImageURL($level[$i1]['imgsvr']);
    $imageTemp2 = GetImageURL($level[$i2]['imgsvr']);
    echo "
    <tr>
        <td width=98 align=center id=bg1><font size=4>".getLevel($i1, $nation['level'])."</font></td>
        <td width=64 height=64 background={$imageTemp1}/{$level[$i1]['picture']}>&nbsp;</td>
        <td width=332><font size=4>";echo $level[$i1]['name']==''?"-":$level[$i1]['name']; echo " ({$level[$i1]['belong']}년)</font></td>
        <td width=98 align=center id=bg1><font size=4>".getLevel($i2, $nation['level'])."</font></td>
        <td width=64 height=64 background={$imageTemp2}/{$level[$i2]['picture']}>&nbsp;</td>
        <td width=332><font size=4>";echo $level[$i2]['name']==''?"-":$level[$i2]['name']; echo " ({$level[$i2]['belong']}년)</font></td>
    </tr>
    ";
}

echo "
    <tr>
        <td width=98 align=center id=bg1>오호장군【승전】</td>
        <td colspan=5>
";

$tigernum = MYDB_num_rows($tigerresult);
for($i=0; $i < $tigernum; $i++) {
    $tiger = MYDB_fetch_array($tigerresult);

    if($tiger['killnum'] > 0) {
        echo "{$tiger['name']}【{$tiger['killnum']}】, ";
    }
}

echo "
        </td>
    </tr>
    <tr>
        <td width=98 align=center id=bg1>건안칠자【계략】</td>
        <td colspan=5>
";

$eaglenum = MYDB_num_rows($eagleresult);
for($i=0; $i < $eaglenum; $i++) {
    $eagle = MYDB_fetch_array($eagleresult);

    if($eagle['firenum'] > 0) {
        echo "{$eagle['name']}【{$eagle['firenum']}】, ";
    }
}

echo "
        </td>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
<form method=post action=c_myBossInfo.php>
    <tr><td colspan=6 height=5></td></tr>
    <tr><td colspan=2 align=center bgcolor=red>추 방</td></tr>
    <tr>
        <td width=498 align=right id=bg1>대상 장수</td>
        <td width=498>
";

if($meLevel >= 5 && $nation["l{$meLevel}set"] == 0) {
    echo "
            <select name=outlist size=1 style=color:white;background-color:black;>";

    $query = "select no,name,level from general where nation='{$me['nation']}' and level!='12' and no!='{$me['no']}' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        echo "
                <option value={$general['no']}>{$general['name']}</option>";
    }

    echo "
            </select>
            <input type=$btn name=btn value=추방 onclick='return out()'>";
}

$query = "select name,city from general where nation='{$me['nation']}' and level=12";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$general = MYDB_fetch_array($result);
echo "
        </td>
    </tr>
</form>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=4 height=5></td></tr>
    <tr><td colspan=4 align=center bgcolor=blue>수 뇌 부 임 명</td></tr>
    <tr>
        <td width=98  align=right id=bg1>".getLevel(12, $nation['level'])."</td>
        <td width=398>{$general['name']} 【".CityConst::byID($general['city'])->name."】</td>
        <td width=98  align=right id=bg1>".getLevel(11, $nation['level'])."</td>
<form method=post action=c_myBossInfo.php>
        <td width=398>
";

if($meLevel >= 5 && $nation['l11set'] == 0) {
    echo "
            <select name=genlist size=1 maxlength=15 style=color:white;background-color:black;>
                <option value=0>____공석____</option>";
    $query = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['level'] == 11) {
            echo "<option style=color:red; selected value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['level'] > 1) {
            echo "<option style=color:orange; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input type=hidden name=level value=11>
            <input type=$btn name=btn value=임명>";
} else {
    $query = "select name,city from general where nation='{$me['nation']}' and level='11'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    echo "{$general['name']} 【".CityConst::byID($general['city'])->name."】";
}
echo "
        </td>
</form>
    </tr>
";

$querys[10] = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and power>='".GameConst::$goodgenpower."' order by npc,binary(name)";
$querys[9]  = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and intel>='".GameConst::$goodgenintel."' order by npc,binary(name)";
$querys[8]  = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and power>='".GameConst::$goodgenpower."' order by npc,binary(name)";
$querys[7]  = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and intel>='".GameConst::$goodgenintel."' order by npc,binary(name)";
$querys[6]  = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and power>='".GameConst::$goodgenpower."' order by npc,binary(name)";
$querys[5]  = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and intel>='".GameConst::$goodgenintel."' order by npc,binary(name)";

for($i=10; $i >= $lv; $i--) {
    if($i % 2 == 0) { echo "<tr>"; }
    echo "
        <td width=98 align=right id=bg1>".getLevel($i, $nation['level'])."</td>
<form method=post action=c_myBossInfo.php>
        <td width=398>
    ";

    if($meLevel >= 5 && $nation["l{$i}set"] == 0) {
        echo "
            <select name=genlist size=1 style=color:white;background-color:black;>
                <option value=0>____공석____</option>";

        $query = $querys[$i];
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($k=0; $k < $gencount; $k++) {
            $general = MYDB_fetch_array($result);
            if($general['level'] == $i) {
                echo "<option style=color:red; selected value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
            } elseif($general['level'] > 1) {
                echo "<option style=color:orange; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
            } else {
                echo "<option value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
            }
        }

        echo "
            </select>
            <input type=hidden name=level value={$i}>
            <input type=$btn name=btn value=임명>";
    } else {
        $query = "select name,city from general where nation='{$me['nation']}' and level={$i}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);
        echo "{$general['name']} 【".CityConst::byID($general['city'])->name."】";
    }
    echo "</td></form>";
    if($i % 2 == 1) { echo "</tr>"; }
}
echo "
    <tr><td colspan=4>※ <font color=red>빨간색</font>은 현재 임명중인 장수, <font color=orange>노란색</font>은 다른 관직에 임명된 장수, 하얀색은 일반 장수를 뜻합니다.</td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td colspan=5 height=5></td></tr>
";
if($meLevel >= 5) {
    echo "
    <tr><td colspan=5 align=center bgcolor=orange>도 시 관 직 임 명</td></tr>
<form method=post action=c_myBossInfo.php>
    <tr>
        <td colspan=3 align=right id=bg2>태 수 임 명</td>
        <td colspan=2>
            <select name=citylist size=1 style=color:white;background-color:black;>
    ";

    $query = "select city,name,region from city where nation='{$nation['nation']}' and gen1set=0 order by region,level desc,binary(name)"; // 도시 이름 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $region = 0;
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        if($region != $city['region']) {
            if($region != 0) {
                echo "</optgroup>";
            }
            echo "<optgroup label=' 【 ".CityConst::$regionMap[$city['region']]." 】 ' style=color:skyblue;>";
            $region = $city['region'];
        }

        echo "<option value='{$city['city']}' style=color:white;>{$city['name']}</option>";
    }
    echo "</optgroup>";

    echo "
            </select>
            <select name=genlist size=1 style=color:white;background-color:black;>
                <option value=0>____공석____</option>
    ";

    $query = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and power>='".GameConst::$goodgenpower."' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['level'] == 4) {
            echo "<option style=color:red; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['level'] > 1) {
            echo "<option style=color:orange; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input type=hidden name=level value=4>
            <input type=$btn name=btn value=임명>
        </td>
    </tr>
</form>
<form method=post action=c_myBossInfo.php>
    <tr>
        <td colspan=3 align=right id=bg2>군 사 임 명</td>
        <td colspan=2>
            <select name=citylist size=1 style=color:white;background-color:black;>
    ";

    $query = "select city,name,region from city where nation='{$nation['nation']}' and gen2set=0 order by region,level desc,binary(name)"; // 도시 이름 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $region = 0;
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        if($region != $city['region']) {
            if($region != 0) {
                echo "</optgroup>";
            }
            echo "<optgroup label=' 【 ".CityConst::$regionMap[$city['region']]." 】 ' style=color:skyblue;>";
            $region = $city['region'];
        }

        echo "<option value='{$city['city']}' style=color:white;>{$city['name']}</option>";
    }
    echo "</optgroup>";

    echo "
            </select>
            <select name=genlist size=1 style=color:white;background-color:black;>
                <option value=0>____공석____</option>
    ";

    $query = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' and intel>='".GameConst::$goodgenintel."' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['level'] == 3) {
            echo "<option style=color:red; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['level'] > 1) {
            echo "<option style=color:orange; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input type=hidden name=level value=3>
            <input type=$btn name=btn value=임명>
        </td>
    </tr>
</form>
<form method=post action=c_myBossInfo.php>
    <tr>
        <td colspan=3 align=right id=bg2>시 중 임 명</td>
        <td colspan=2>
            <select name=citylist size=1 style=color:white;background-color:black;>
    ";

    $query = "select city,name,region from city where nation='{$nation['nation']}' and gen3set=0 order by region, level desc,binary(name)"; // 도시 이름 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $region = 0;
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        if($region != $city['region']) {
            if($region != 0) {
                echo "</optgroup>";
            }
            echo "<optgroup label=' 【 ".CityConst::$regionMap[$city['region']]." 】 ' style=color:skyblue;>";
            $region = $city['region'];
        }

        echo "<option value='{$city['city']}' style=color:white;>{$city['name']}</option>";
    }
    echo "</optgroup>";

    echo "
            </select>
            <select name=genlist size=1 style=color:white;background-color:black;>
                <option value=0>____공석____</option>
    ";

    $query = "select no,name,level,city from general where nation='{$me['nation']}' and level!='12' order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['level'] == 2) {
            echo "<option style=color:red; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } elseif($general['level'] > 1) {
            echo "<option style=color:orange; value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        } else {
            echo "<option value={$general['no']}>{$general['name']} 【".CityConst::byID($general['city'])->name."】</option>";
        }
    }

    echo "
            </select>
            <input type=hidden name=level value=2>
            <input type=$btn name=btn value=임명>
        </td>
    </tr>
    <tr><td colspan=5>※ <font color=red>빨간색</font>은 현재 임명중인 장수, <font color=orange>노란색</font>은 다른 관직에 임명된 장수, 하얀색은 일반 장수를 뜻합니다.</td></tr>
</form>
    ";
}
echo "
    <tr>
        <td width=158 align=center id=bg1 colspan=2><font size=4>도 시</font></td>
        <td width=278 align=center id=bg1><font size=4>태 수 (사관) 【현재도시】</font></td>
        <td width=278 align=center id=bg1><font size=4>군 사 (사관) 【현재도시】</font></td>
        <td width=278 align=center id=bg1><font size=4>시 중 (사관) 【현재도시】</font></td>
    </tr>
";

$citylevel[1] = "수";
$citylevel[2] = "진";
$citylevel[3] = "관";
$citylevel[4] = "이";
$citylevel[5] = "소";
$citylevel[6] = "중";
$citylevel[7] = "대";
$citylevel[8] = "특";

$query = "select city,name,gen1,gen2,gen3,level,region,gen1set,gen2set,gen3set from city where nation='{$nation['nation']}' order by region,level desc,binary(name)"; // 도시 이름 목록
$cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$citycount = MYDB_num_rows($cityresult);

$region = 0;
for($j=0; $j < $citycount; $j++) {
    $city = MYDB_fetch_array($cityresult);

    $query = "select name,belong,city from general where no='{$city['gen1']}'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen1 = MYDB_fetch_array($genresult);
    $query = "select name,belong,city from general where no='{$city['gen2']}'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen2 = MYDB_fetch_array($genresult);
    $query = "select name,belong,city from general where no='{$city['gen3']}'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen3 = MYDB_fetch_array($genresult);

    if($region != $city['region']) {
        echo "
    <tr><td colspan=5 height=3 id=bg1></td></tr>
    <tr><td colspan=5 id=bg1><font size=4 color=skyblue> 【 ".CityConst::$regionMap[$city['region']]." 】 </font></td></tr>";
        $region = $city['region'];
    }

    $gen1['name'] = $gen1['name']==""?"-":$gen1['name']." ({$gen1['belong']}년) 【".CityConst::byID($gen1['city'])->name."】";
    $gen2['name'] = $gen2['name']==""?"-":$gen2['name']." ({$gen2['belong']}년) 【".CityConst::byID($gen2['city'])->name."】";
    $gen3['name'] = $gen3['name']==""?"-":$gen3['name']." ({$gen3['belong']}년) 【".CityConst::byID($gen3['city'])->name."】";
    if($city['gen1set'] == 1) { $gen1['name'] = "<font color=orange>".$gen1['name']."</font>"; }
    if($city['gen2set'] == 1) { $gen2['name'] = "<font color=orange>".$gen2['name']."</font>"; }
    if($city['gen3set'] == 1) { $gen3['name'] = "<font color=orange>".$gen3['name']."</font>"; }
    echo "
    <tr>
        <td width=78 align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}><font size=3>【{$citylevel[$city['level']]}】</font></td>
        <td width=78 align=right  style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}><font size=3>{$city['name']}&nbsp;&nbsp;</font></td>
        <td align=center>{$gen1['name']}</td>
        <td align=center>{$gen2['name']}</td>
        <td align=center>{$gen3['name']}</td>
    </tr>
    ";
}
?>
    <tr><td colspan=5>※ <font color=orange>노란색</font>은 변경 불가능, 하얀색은 변경 가능 관직입니다.</td></tr>
</table>
<br>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
