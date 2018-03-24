<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("세력장수", 1);

$query = "select skin,no,nation,level from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['level'] == 0) {
    echo "재야입니다.";
    exit();
}


if($type == 0) {
    $type = 1;
}
$sel[$type] = "selected";

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>세력장수</title>
<link rel=stylesheet href=css/common.css type=text/css>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>세 력 장 수<br><?=backButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1];?> value=1>관직</option>
            <option <?=$sel[2];?> value=2>계급</option>
            <option <?=$sel[3];?> value=3>명성</option>
            <option <?=$sel[4];?> value=4>통솔</option>
            <option <?=$sel[5];?> value=5>무력</option>
            <option <?=$sel[6];?> value=6>지력</option>
            <option <?=$sel[7];?> value=7>자금</option>
            <option <?=$sel[8];?> value=8>군량</option>
            <option <?=$sel[9];?> value=9>병사</option>
            <option <?=$sel[10];?> value=10>벌점</option>
            <option <?=$sel[11];?> value=11>성격</option>
            <option <?=$sel[12];?> value=12>내특</option>
            <option <?=$sel[13];?> value=13>전특</option>
            <option <?=$sel[14];?> value=14>사관</option>
            <option <?=$sel[15];?> value=15>NPC</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php

$nationLevel = getDB()->queryFirstField('select level from nation where nation = %i', $me['nation']);
switch($type) {
    case  1: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by level desc"; break;
    case  2: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by dedication desc"; break;
    case  3: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by experience desc"; break;
    case  4: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by leader desc"; break;
    case  5: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by power desc"; break;
    case  6: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by intel desc"; break;
    case  7: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by gold desc"; break;
    case  8: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by rice desc"; break;
    case  9: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by crew desc"; break;
    case 10: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by connect desc"; break;
    case 11: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by personal"; break;
    case 12: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by special desc"; break;
    case 13: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by special2 desc"; break;
    case 14: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by belong desc"; break;
    case 15: $query = "select npc,special,special2,personal,picture,imgsvr,name,level,dedication,experience,injury,leader,power,intel,gold,rice,belong,connect,killturn from general where nation='{$me['nation']}' order by npc desc"; break;
}
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

echo"
<table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=64 align=center id=bg1>얼 굴</td>
        <td width=98 align=center id=bg1>이 름</td>
        <td width=78 align=center id=bg1>관 직</td>
        <td width=68 align=center id=bg1>계 급</td>
        <td width=68 align=center id=bg1>명 성</td>
        <td width=68 align=center id=bg1>봉 록</td>
        <td width=48 align=center id=bg1>통솔</td>
        <td width=48 align=center id=bg1>무력</td>
        <td width=48 align=center id=bg1>지력</td>
        <td width=68 align=center id=bg1>자 금</td>
        <td width=68 align=center id=bg1>군 량</td>
        <td width=48 align=center id=bg1>성 격</td>
        <td width=78 align=center id=bg1>특 기</td>
        <td width=48 align=center id=bg1>사 관</td>
        <td width=70 align=center id=bg1>벌점</td>
    </tr>";
for($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);

    if($general['level'] == 12) {
        $lbonus = $nationLevel * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nationLevel;
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

    if($general['npc'] >= 2) { $name = "<font color=cyan>{$general['name']}</font>"; }
    elseif($general['npc'] == 1) { $name = "<font color=skyblue>{$general['name']}</font>"; }
    else { $name =  "{$general['name']}"; }

    $imageTemp = GetImageURL($general['imgsvr']);
    echo "
    <tr>
        <td align=center"; echo $me['skin']>0?" background={$imageTemp}/{$general['picture']}":""; echo " height=64></td>
        <td align=center>$name</td>
        <td align=center>"; echo getLevel($general['level'], $nation['level']); echo "</td>
        <td align=center>".getDed($general['dedication'])."</td>
        <td align=center>".getHonor($general['experience'])."</td>
        <td align=center>".getBill($general['dedication'])."</td>
        <td align=center>$leader</td>
        <td align=center>$power</td>
        <td align=center>$intel</td>
        <td align=center>{$general['gold']}</td>
        <td align=center>{$general['rice']}</td>
        <td align=center>".getGenChar($general['personal'])."</td>
        <td align=center>".getGenSpecial($general['special'])." / ".getGenSpecial($general['special2'])."</td>
        <td align=center>{$general['belong']}</td>
        <td align=center>{$general['connect']}"; echo "<br>(".getConnect($general['connect']).")</td>
    </tr>";
}
    echo "
</table>
";

?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>

</html>

