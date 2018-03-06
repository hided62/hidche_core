<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select skin from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if(getUserGrade() < 5) {
    echo "
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

if($type == 0) {
    $type = 0;
}
if($type2 == 0) {
    $type2 = 0;
}
$sel[$type] = "selected";
$sel2[$type2] = "selected";

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);
?>
<html>
<head>
<title>일제정보</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=css/common.css type=text/css>
</head>
<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>일 제 정 보<br><?=closeButton()?></td></tr>
    <tr><td>
        <form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[0];?>   value=0>국력</option>
            <option <?=$sel[1];?>   value=1>장수</option>
            <option <?=$sel[2];?>   value=2>기술</option>
            <option <?=$sel[3];?>   value=3>국고</option>
            <option <?=$sel[4];?>   value=4>병량</option>
            <option <?=$sel[5];?>   value=5>평금</option>
            <option <?=$sel[6];?>   value=6>평쌀</option>
            <option <?=$sel[7];?>   value=7>평통</option>
            <option <?=$sel[8];?>   value=8>평무</option>
            <option <?=$sel[9];?>   value=9>평지</option>
            <option <?=$sel[10];?> value=10>평Lv</option>
            <option <?=$sel[11];?> value=11>접속률</option>
            <option <?=$sel[12];?> value=12>단기접</option>
            <option <?=$sel[13];?> value=13>보숙</option>
            <option <?=$sel[14];?> value=14>궁숙</option>
            <option <?=$sel[15];?> value=15>기숙</option>
            <option <?=$sel[16];?> value=16>귀숙</option>
            <option <?=$sel[17];?> value=17>차숙</option>
        </select>
        <select name=type2 size=1>
            <option <?=$sel2[0];?> value=0>국력</option>
            <option <?=$sel2[1];?> value=1>국가별성향</option>
            <option <?=$sel2[2];?> value=2>국가성향</option>
            <option <?=$sel2[3];?> value=3>장수성격</option>
            <option <?=$sel2[4];?> value=4>장수특기</option>
            <option <?=$sel2[5];?> value=5>병종수</option>
            <option <?=$sel2[6];?> value=6>기타</option>
        </select>
        <input type=submit value='정렬하기'>
        </form>
        <form name=form2 method=post action=_admin5_submit.php>
        <select name=nation size=1 style=color:white;background-color:black>";
            <option value=0>재야</option>";
<?php
$query = "select nation,name,color,scout,scoutmsg,gennum from nation order by power";
$result = MYDB_query($query, $connect) or Error("aaa_processing.php ".MYDB_error($connect),"");
$count = MYDB_num_rows($result);
for($i=1; $i <= $count; $i++) {
    $nation = MYDB_fetch_array($result);

    echo "
            <option value={$nation['nation']}>{$nation['name']}</option>";
}
?>
        </select>
        <input type=submit name=btn value='국가변경'>
        </form>
    </td></tr>
</table>

<table align=center width=1600 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px; id=bg0>
    <tr id=bg1>
        <td align=center>국명</td>
        <td align=center>접률</td>
        <td align=center>단접</td>
        <td align=center>국력</td>
        <td align=center>단합</td>
        <td align=center>장수</td>
        <td align=center>속령</td>
        <td align=center>기술</td>
        <td align=center>전략</td>
        <td align=center>국고</td>
        <td align=center>병량</td>
        <td align=center>평금</td>
        <td align=center>평쌀</td>
        <td align=center>평통</td>
        <td align=center>평무</td>
        <td align=center>평지</td>
        <td align=center>평Lv</td>
        <td align=center>보숙</td>
        <td align=center>궁숙</td>
        <td align=center>기숙</td>
        <td align=center>귀숙</td>
        <td align=center>차숙</td>
        <td align=center>총병</td>
        <td align=center>인구</td>
        <td align=center>인구율</td>
        <td align=center>농업</td>
        <td align=center>상업</td>
        <td align=center>치안</td>
        <td align=center>성벽</td>
        <td align=center>수비</td>
        <td align=center>국명</td>
    </tr>
<?php
$query = "
SELECT
    A.nation,
    A.name,
    A.power,
    A.chemi,
    A.color,
    A.tech,
    A.tricklimit,
    A.gold,
    A.rice,
    COUNT(B.nation) AS gennum,
    ROUND(AVG(B.connect), 1) AS connect,
    ROUND(AVG(B.con), 1) AS con,
    ROUND(AVG(B.dex0)) AS dex0,
    ROUND(AVG(B.dex10)) AS dex10,
    ROUND(AVG(B.dex20)) AS dex20,
    ROUND(AVG(B.dex30)) AS dex30,
    ROUND(AVG(B.dex40)) AS dex40
FROM nation A, general B
WHERE A.nation=B.nation
GROUP BY B.nation
";

switch($type) {
    case  0: $query .= " order by power desc"; break;
    case  1: $query .= " order by gennum desc"; break;
    case  2: $query .= " order by A.tech desc"; break;
    case  3: $query .= " order by A.gold desc"; break;
    case  4: $query .= " order by A.rice desc"; break;
    case  5: $query .= " order by avg(B.gold) desc"; break;
    case  6: $query .= " order by avg(B.rice) desc"; break;
    case  7: $query .= " order by avg(B.leader) desc"; break;
    case  8: $query .= " order by avg(B.power) desc"; break;
    case  9: $query .= " order by avg(B.intel) desc"; break;
    case 10: $query .= " order by avg(B.explevel) desc"; break;
    case 11: $query .= " order by avg(B.connect) desc"; break;
    case 12: $query .= " order by avg(B.con) desc"; break;
    case 13: $query .= " order by avg(B.dex0) desc"; break;
    case 14: $query .= " order by avg(B.dex10) desc"; break;
    case 15: $query .= " order by avg(B.dex20) desc"; break;
    case 16: $query .= " order by avg(B.dex30) desc"; break;
    case 17: $query .= " order by avg(B.dex40) desc"; break;
}
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nationCount = MYDB_num_rows($result);
for($i=0; $i < $nationCount; $i++) {
    $nation = MYDB_fetch_array($result);

    $query = "select COUNT(*) as cnt,
                    ROUND(AVG(gold)) as avgg,
                    ROUND(AVG(rice)) as avgr,
                    SUM(leader) as leader,  ROUND(AVG(leader), 1) as avgl,
                                            ROUND(AVG(power), 1) as avgp,
                                            ROUND(AVG(intel), 1) as avgi,
                                            ROUND(AVG(explevel), 1) as avge,
                    SUM(crew) as crew
        from general where nation='{$nation['nation']}'";
    $genResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($genResult);

    $query = "select COUNT(*) as cnt,
                    SUM(pop) as pop,    SUM(pop2) as pop2,
                    ROUND(SUM(pop)/SUM(pop2)*100, 2) as rate,
                    ROUND(SUM(agri)/SUM(agri2)*100, 2) as agri,
                    ROUND(SUM(comm)/SUM(comm2)*100, 2) as comm,
                    ROUND(SUM(secu)/SUM(secu2)*100, 2) as secu,
                    ROUND(SUM(wall)/SUM(wall2)*100, 2) as wall,
                    ROUND(SUM(def)/SUM(def2)*100, 2) as def
        from city where nation='{$nation['nation']}'";
    $cityResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($cityResult);

    echo "
    <tr>
        <td align=center style=background-color:{$nation['color']};color:".newColor($nation['color']).";>{$nation['name']}</td>
        <td align=center>&nbsp;{$nation['connect']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['con']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['power']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['chemi']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['cnt']}&nbsp;</td>
        <td align=center>&nbsp;{$city['cnt']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['tech']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['tricklimit']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['gold']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['rice']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['avgg']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['avgr']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['avgl']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['avgp']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['avgi']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['avge']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['dex0']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['dex10']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['dex20']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['dex30']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['dex40']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['crew']}/{$gen['leader']}00&nbsp;</td>
        <td align=center>&nbsp;{$city['pop']}/{$city['pop2']}&nbsp;</td>
        <td align=center>&nbsp;{$city['rate']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['agri']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['comm']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['secu']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['wall']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['def']}%&nbsp;</td>
        <td align=center style=background-color:{$nation['color']};color:".newColor($nation['color']).";>{$nation['name']}</td>
    </tr>
";
}

?>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?php TrickLog(20, $me['skin']); ?></td></tr>
</table>

<table align=center width=1760 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px; id=bg0>
    <tr id=bg1>
        <td width=30 align=center>년</td>
        <td width=30 align=center>월</td>
        <td width=50 align=center>국가수</td>
        <td width=50 align=center>장수수</td>
<?php
switch($type2) {
default:
case 0: echo "<td width=1600>국력(국력,장수수,도시수,인구/100,최대인구/100,국가자원/100,장수자원/100,능력치,숙련/1000,경험공헌/100)</td>"; break;
case 1: echo "<td width=1600>국가별성향</td>"; break;
case 2: echo "<td width=1600>국가성향</td>"; break;
case 3: echo "<td width=1600>장수성격</td>"; break;
case 4: echo "<td width=1600>장수특기</td>"; break;
case 5: echo "<td width=1600>병종수</td>"; break;
case 6: echo "<td width=1600>기타</td>"; break;
}
?>
    </tr>
<?php
$query = "select * from statistic where month=1 or no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$count = MYDB_num_rows($result);
for($i=0; $i < $count; $i++) {
    $stat = MYDB_fetch_array($result);

    echo "
    <tr>
        <td align=center>{$stat['year']}</td>
        <td align=center>{$stat['month']}</td>
        <td align=center>{$stat['nation_count']}</td>
        <td align=center>{$stat['gen_count']}</td>
";
    switch($type2) {
        default:
        case 0: echo "<td>{$stat['power_hist']}</td>"; break;
        case 1: echo "<td>{$stat['nation_name']}</td>"; break;
        case 2: echo "<td>{$stat['nation_hist']}</td>"; break;
        case 3: echo "<td>{$stat['personal_hist']}</td>"; break;
        case 4: echo "<td>{$stat['special_hist']}</td>"; break;
        case 5: echo "<td>{$stat['crewtype']}</td>"; break;
        case 6: echo "<td>{$stat['etc']}</td>"; break;
    }
    
    echo "
    </tr>
";
}
?>
</table>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
