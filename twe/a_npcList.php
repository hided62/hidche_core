<?php
include "lib.php";
include "func.php";
$connect = dbConn();
increaseRefresh($connect, "빙의일람", 2);

if($type == 0) {
    $type = 1;
}
$sel[$type] = "selected";

?>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>빙의일람</title>
<link rel=stylesheet href=stylesheet.php type=text/css>
<?php require('analytics.php'); ?>
</head>

<body oncontextmenu='return false'>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td>빙 의 일 람<br><?php closeButton(); ?></td></tr>
    <tr><td><form name=form1 method=post>정렬순서 :
        <select name=type size=1>
            <option <?=$sel[1];?> value=1>이름</option>
            <option <?=$sel[2];?> value=2>국가</option>
            <option <?=$sel[3];?> value=3>종능</option>
            <option <?=$sel[4];?> value=4>통솔</option>
            <option <?=$sel[5];?> value=5>무력</option>
            <option <?=$sel[6];?> value=6>지력</option>
            <option <?=$sel[7];?> value=7>명성</option>
            <option <?=$sel[8];?> value=8>계급</option>
        </select>
        <input type=submit value='정렬하기'></form>
    </td></tr>
</table>
<?php
$query = "select nation,name from nation";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$count = MYDB_num_rows($result);

$nationname[0] = "-";
for($i=0; $i < $count; $i++) {
    $nation = MYDB_fetch_array($result);
    $nationname[$nation['nation']] = $nation['name'];
}

switch($type) {
    case  1: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by binary(name)"; break;
    case  2: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by nation"; break;
    case  3: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by sum desc"; break;
    case  4: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by leader"; break;
    case  5: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by power"; break;
    case  6: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by intel"; break;
    case  7: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by experience"; break;
    case  8: $query = "select npc,nation,name,name2,special,special2,personal,leader,power,intel,leader+power+intel as sum,explevel,experience,dedication from general where npc=1 order by dedication"; break;
}
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

echo"
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=102  align=center id=bg1>희생된 장수</td>
        <td width=102  align=center id=bg1>악령 이름</td>
        <td width=68  align=center id=bg1>레벨</td>
        <td width=118 align=center id=bg1>국가</td>
        <td width=68  align=center id=bg1>성격</td>
        <td width=88  align=center id=bg1>특기</td>
        <td width=68  align=center id=bg1>종능</td>
        <td width=68  align=center id=bg1>통솔</td>
        <td width=68  align=center id=bg1>무력</td>
        <td width=68  align=center id=bg1>지력</td>
        <td width=78  align=center id=bg1>명성</td>
        <td width=78  align=center id=bg1>계급</td>
    </tr>";
for($j=0; $j < $gencount; $j++) {
    $general = MYDB_fetch_array($genresult);
    $nation = $nationname[$general['nation']];

    if($general['npc'] >= 2) { $name = "<font color=cyan>{$general['name']}</font>"; }
    elseif($general['npc'] == 1) { $name = "<font color=skyblue>{$general['name']}</font>"; }
    else { $name =  "$general['name']"; }

    echo "
    <tr>
        <td align=center>{$name}</td>
        <td align=center>{$general[name2]}</td>
        <td align=center>Lv {$general['explevel']}</td>
        <td align=center>{$nation}</td>
        <td align=center>".getGenChar($general['personal'])."</td>
        <td align=center>".getGenSpecial($general['special'])." / ".getGenSpecial($general[special2])."</td>
        <td align=center>{$general['sum']}</td>
        <td align=center>{$general['leader']}</td>
        <td align=center>{$general['power']}</td>
        <td align=center>{$general['intel']}</td>
        <td align=center>{$general['experience']}</td>
        <td align=center>{$general['dedication']}</td>
    </tr>";
}
echo "
</table>
";

MYDB_close($connect);
?>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr><td><?php closeButton(); ?></td></tr>
    <tr><td><?php banner(); ?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>

</html>
