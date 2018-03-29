<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("현재도시", 1);

$query = "select no,nation,level,city from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select nation,level,spy from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$myNation = MYDB_fetch_array($result);

?>
<!DOCTYPE html>
<html>

<head>
<title>도시정보</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>도 시 정 보<br><?=backButton()?></td></tr>
</table>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=998>
            <form name=cityselect method=get>도시선택 :
                <select name=citylist size=1 style=color:white;background-color:black;width:798;>
<?php
if(!array_key_exists('citylist', $_REQUEST) || $_REQUEST['citylist'] == '') { $_REQUEST['citylist'] = $me['city']; }

// 재야일때는 현재 도시만
$valid = 0;
if($me['level'] == 0) {
    $query = "select city,name,nation from city where city='{$me['city']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($cityresult);
    echo "
                    <option value={$city['city']}";
    if($city['city'] == $_REQUEST['citylist']) { echo " selected"; $valid = 1; }
    echo ">==================================================【".StringUtil::Fill($city['name'], 4, '_')."】";
    if($city['nation'] == 0) echo "공백지";
    elseif($me['nation'] == $city['nation']) echo "본국==";
    else echo "타국==";
    echo "============================================</option>";
} else {
    // 아국 도시들 선택
    $query = "select city,name,nation from city where nation='{$me['nation']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        echo "
                        <option value={$city['city']}";
        if($city['city'] == $_REQUEST['citylist']) { echo " selected"; $valid = 1; }
        echo ">==================================================【".StringUtil::Fill($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국==";
        else echo "타국==";
        echo "============================================</option>";
    }

    // 아국 장수가 있는 타국 도시들 선택
    $query = "select distinct A.city,B.name,B.nation from general A,city B where A.city=B.city and A.nation='{$me['nation']}' and B.nation!='{$me['nation']}'";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        echo "
                        <option value={$city['city']}";
        if($city['city'] == $_REQUEST['citylist']) { echo " selected"; $valid = 1; }
        echo ">==================================================【".StringUtil::Fill($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국==";
        else echo "타국==";
        echo "============================================</option>";
    }
}

if($myNation['level'] > 0) {
    // 첩보도시도 목록에 추가
    $where = 'city=0';
    $citys = explode("|", $myNation['spy']);
    for($i=0; $i < count($citys); $i++) {
        $city = floor($citys[$i]/10);
        $where .= " or city='{$city}'";
    }

    $query = "select city,name,nation from city where {$where}";
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        echo "
                        <option value={$city['city']}";
        if($city['city'] == $_REQUEST['citylist']) { echo " selected"; $valid = 1; }
        echo ">==================================================【".StringUtil::Fill($city['name'], 4, '_')."】";
        if($city['nation'] == 0) echo "공백지";
        elseif($me['nation'] == $city['nation']) echo "본국==";
        else echo "타국==";
        echo "============================================</option>";
    }

}

echo "
                </select>
                <input type=submit value='도 시 선 택'>
                <p align=center>명령 화면에서 도시를 클릭하셔도 됩니다.</p>
            </form>
        </td>
    </tr>
</table>
<br>";

// 첩보된 도시까지만 허용
if($valid == 0 && Session::getUserGrade() < 5) {
    $_REQUEST['citylist'] = $me['city'];
}

$query = "select * from city where city='{$_REQUEST['citylist']}'"; // 도시 이름 목록
$cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$city = MYDB_fetch_array($cityresult);

$nation = getNationStaticInfo($city['nation']);

$query = "select name from general where no='{$city['gen1']}'"; // 태수 이름
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gen1 = MYDB_fetch_array($genresult);

$query = "select name from general where no='{$city['gen2']}'"; // 군사 이름
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gen2 = MYDB_fetch_array($genresult);

$query = "select name from general where no='{$city['gen3']}'"; // 시중 이름
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gen3 = MYDB_fetch_array($genresult);

if($city['trade'] == 0) {
    $city['trade'] = "- ";
}
?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
</table>

<?php
echo "
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg2>
    <tr>
        <td colspan=12 align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}>【 ".getRegion($city['region'])." | ".getCityLevel($city['level'])." 】 {$city['name']}</td>
    </tr>
    <tr>
        <td align=center width=48 id=bg1>주민</td>
        <td align=center width=112>{$city['pop']}/{$city['pop2']}</td>
        <td align=center width=48 id=bg1>농업</td>
        <td align=center width=108>{$city['agri']}/{$city['agri2']}</td>
        <td align=center width=48 id=bg1>상업</td>
        <td align=center width=108>{$city['comm']}/{$city['comm2']}</td>
        <td align=center width=48 id=bg1>치안</td>
        <td align=center width=108>{$city['secu']}/{$city['secu2']}</td>
        <td align=center width=48 id=bg1>수비</td>
        <td align=center width=108>{$city['def']}/{$city['def2']}</td>
        <td align=center width=48 id=bg1>성벽</td>
        <td align=center width=108>{$city['wall']}/{$city['wall2']}</td>
    </tr>
    <tr>
        <td align=center id=bg1>민심</td>
        <td align=center>{$city['rate']}</td>
        <td align=center id=bg1>시세</td>
        <td align=center>{$city['trade']}%</td>
        <td align=center id=bg1>인구</td>
        <td align=center>".round($city['pop']/$city['pop2']*100, 2)." %</td>
        <td align=center id=bg1>태수</td>
        <td align=center>";echo $gen1['name']==''?"-":"{$gen1['name']}";echo "</td>
        <td align=center id=bg1>군사</td>
        <td align=center>";echo $gen2['name']==''?"-":"{$gen2['name']}";echo "</td>
        <td align=center id=bg1>시중</td>
        <td align=center>";echo $gen3['name']==''?"-":"{$gen3['name']}";echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1>장수</td>
        <td colspan=11>";
    $query = "select name from general where city='{$city['city']}' and nation='{$city['nation']}'";    // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);
    if($gencount == 0) echo "-";
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($genresult);
        echo "{$general['name']}, ";
    }
    echo "
        </td>
    </tr>
</table>";

$query = "select npc,mode,no,picture,imgsvr,name,injury,leader,power,intel,level,nation,crewtype,crew,train,atmos,term,turn0,turn1,turn2,turn3,turn4,turn5 from general where city='{$city['city']}' order by dedication desc";    // 장수 목록
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

foreach(getAllNationStaticInfo() as $nation){
    $nationname[$nation['nation']] = $nation['name'];
    $nationlevel[$nation['nation']] = $nation['level'];
}

if($gencount != 0) {
    echo "
<br>
<table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=64 align=center id=bg1>얼 굴</td>
        <td width=98 align=center id=bg1>이 름</td>
        <td width=48 align=center id=bg1>통솔</td>
        <td width=48 align=center id=bg1>무력</td>
        <td width=48 align=center id=bg1>지력</td>
        <td width=78 align=center id=bg1>관 직</td>
        <td width=28 align=center id=bg1>守</td>
        <td width=78 align=center id=bg1>병 종</td>
        <td width=78 align=center id=bg1>병 사</td>
        <td width=48 align=center id=bg1>훈련</td>
        <td width=48 align=center id=bg1>사기</td>
        <td width=310 align=center id=bg1>명 령</td>
    </tr>";
}

for($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);

    if($general['level'] == 12) {
        $lbonus = $nationlevel[$general['nation']] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nationlevel[$general['nation']];
    } else {
        $lbonus = 0;
    }
    if($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    if($general['injury'] > 0) {
        $leader = floor($general['leader'] * (100 - $general['injury'])/100);
        $power = floor($general['power'] * (100 - $general['injury'])/100);
        $intel = floor($general['intel'] * (100 - $general['injury'])/100);
        $leader = "<font color=red>{$leader}</font>{$lbonus}";
        $power = "<font color=red>{$power}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leader = "{$general['leader']}{$lbonus}";
        $power = "{$general['power']}";
        $intel = "{$general['intel']}";
    }

    if($general['npc'] >= 2) { $general['name'] = "<font color=cyan>{$general['name']}</font>"; }
    elseif($general['npc'] == 1) { $general['name'] = "<font color=skyblue>{$general['name']}</font>"; }
    $imageTemp = GetImageURL($general['imgsvr']);
    echo "
    <tr>
        <td align=center height=64></td>
        <td align=center>{$general['name']}</td>
        <td align=center>$leader</td>
        <td align=center>$power</td>
        <td align=center>$intel</td>
        <td align=center>".getLevel($general['level'])."</td>";
    //아국장수이거나 보는 사람이 운영자일때 보여줌
    if(($general['nation'] != 0 && $general['nation'] == $myNation['nation']) || Session::getUserGrade() >= 5) {
        switch($general['mode']) {
        case 0: $mode = "×"; break;
        case 1: $mode = "○"; break;
        case 2: $mode = "◎"; break;
        }

        echo "
        <td align=center>$mode</td>
        <td align=center>".getTypename($general['crewtype'])."</td>
        <td align=center>{$general['crew']}</td>
        <td align=center>{$general['train']}</td>
        <td align=center>{$general['atmos']}</td>";
        if($general['npc'] >= 2) {
            echo "
        <td>NPC 장수";
        } else {
            echo "
        <td>
            <font size=1>";
            $turn = getTurn($connect, $general, 1);

            for($i=0; $i < 4; $i++) {
                $k = $i+1;
                echo "
                    &nbsp;&nbsp;$k : $turn[$i]<br>";
            }
            echo "
            </font>";
        }
    } elseif($general['nation'] != 0) {
        echo "
        <td align=center>?</td>
        <td align=center>?</td>
        <td align=center>{$general['crew']}</td>
        <td align=center>?</td>
        <td align=center>?</td>
        <td>【{$nationname[$general['nation']]}】 장수";
    } else {
        echo "
        <td align=center>?</td>
        <td align=center>?</td>
        <td align=center>{$general['crew']}</td>
        <td align=center>?</td>
        <td align=center>?</td>
        <td>&nbsp; 재 야";
    }

    echo "
        </td>
    </tr>";
}
?>
</table>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

