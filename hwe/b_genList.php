<?php
namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("암행부", 2);

$query = "select conlimit from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$admin = MYDB_fetch_array($result);

$query = "select no,nation,level,con,turntime,belong from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

$query = "select level,secretlimit from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$nation = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

if ($me['level'] == 0 || ($me['level'] == 1 && $me['belong'] < $nation['secretlimit'])) {
    echo "수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

if ($type == 0) {
    $type = 7;
}
$sel = [];
$sel[$type] = "selected";

?>
<!DOCTYPE html>
<html>
<?php if ($con == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다!");
} ?>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>암행부</title>
<link rel='stylesheet' href='../d_shared/common.css' type='text/css'>
<link rel='stylesheet' href='css/common.css' type='text/css'>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>암 행 부<br><?=closeButton()?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1]??''?> value=1>자금</option>
            <option <?=$sel[2]??''?> value=2>군량</option>
            <option <?=$sel[3]??''?> value=3>도시</option>
            <option <?=$sel[4]??''?> value=4>병종</option>
            <option <?=$sel[5]??''?> value=5>병사</option>
            <option <?=$sel[6]??''?> value=6>삭제턴</option>
            <option <?=$sel[7]??''?> value=7>턴</option>
            <option <?=$sel[8]??''?> value=8>부대</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php
$query = "select troop,name from troop where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$troopCount = MYDB_num_rows($result);
$troopName = [];
for ($i=0; $i < $troopCount; $i++) {
    $troop = MYDB_fetch_array($result);
    $troopName[$troop['troop']] = $troop['name'];
}

switch ($type) {
    case 1: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by gold desc"; break;
    case 2: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by rice desc"; break;
    case 3: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by city"; break;
    case 4: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by crewtype desc"; break;
    case 5: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by crew desc"; break;
    case 6: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by killturn"; break;
    case 7: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by turntime"; break;
    case 8: $query = "select npc,mode,no,level,troop,city,injury,leader,power,intel,experience,name,gold,rice,crewtype,crew,train,atmos,killturn,turntime,term,turn0,turn1,turn2,turn3,turn4 from general where nation='{$me['nation']}' order by troop desc"; break;
}
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$gencount = MYDB_num_rows($genresult);

echo"
<table align=center border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=98 align=center id=bg1>이 름</td>
        <td width=98 align=center id=bg1>통무지</td>
        <td width=98 align=center id=bg1>부 대</td>
        <td width=58 align=center id=bg1>자 금</td>
        <td width=58 align=center id=bg1>군 량</td>
        <td width=48 align=center id=bg1>도시</td>
        <td width=28 align=center id=bg1>守</td>
        <td width=58 align=center id=bg1>병 종</td>
        <td width=68 align=center id=bg1>병 사</td>
        <td width=48 align=center id=bg1>훈련</td>
        <td width=48 align=center id=bg1>사기</td>
        <td width=148 align=center id=bg1>명 령</td>
        <td width=58 align=center id=bg1>삭턴</td>
        <td width=58 align=center id=bg1>턴</td>
    </tr>";
for ($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);
    $city = CityConst::byID($general['city'])->name;
    $troop = $troopName[$general['troop']]??'-';

    if ($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif ($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }
    if ($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    if ($general['injury'] > 0) {
        $leader = intdiv($general['leader'] * (100 - $general['injury']), 100);
        $power = intdiv($general['power'] * (100 - $general['injury']), 100);
        $intel = intdiv($general['intel'] * (100 - $general['injury']), 100);
        $leader = "<font color=red>{$leader}</font>{$lbonus}";
        $power = "<font color=red>{$power}</font>";
        $intel = "<font color=red>{$intel}</font>";
    } else {
        $leader = "{$general['leader']}{$lbonus}";
        $power = "{$general['power']}";
        $intel = "{$general['intel']}";
    }

    if ($general['npc'] >= 2) {
        $name = "<font color=cyan>{$general['name']}</font>";
    } elseif ($general['npc'] == 1) {
        $name = "<font color=skyblue>{$general['name']}</font>";
    } else {
        $name =  "{$general['name']}";
    }

    switch ($general['mode']) {
    case 0: $mode = "×"; break;
    case 1: $mode = "○"; break;
    case 2: $mode = "◎"; break;
    }

    echo "
    <tr>
        <td align=center>$name<br>Lv ".getExpLevel($general['experience'])."</td>
        <td align=center>{$leader}∥{$power}∥{$intel}</td>
        <td align=center>$troop</td>
        <td align=center>{$general['gold']}</td>
        <td align=center>{$general['rice']}</td>
        <td align=center>$city</td>
        <td align=center>$mode</td>
        <td align=center>".GameUnitConst::byId($general['crewtype'])->name."</td>
        <td align=center>{$general['crew']}</td>
        <td align=center>{$general['train']}</td>
        <td align=center>{$general['atmos']}</td>";
    if ($general['npc'] >= 2) {
        echo "
        <td>
            <font size=3>NPC 장수";
    } else {
        echo "
        <td>
            <font size=1>";
        $turn = getTurn($general, 1, 0);

        for ($i=0; $i < 5; $i++) {
            $turn[$i] = StringUtil::subStringForWidth($turn[$i], 0, 20);
            $k = $i+1;
            echo "
                &nbsp;$k : $turn[$i]  <br>";
        }
    }
    echo "
            </font>
        </td>
        <td align=center>{$general['killturn']}</td>
        <td align=center>".substr($general['turntime'], 14, 5)."</td>
    </tr>";
}
echo "
</table>
";

?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
