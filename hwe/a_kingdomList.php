<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("세력일람", 2);

$query = "select con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 세력일람</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/ext.kingdoms.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>세 력 일 람<br><?=closeButton()?></td></tr>
</table>
<?php
$query = "select nation,name,color,level,type,power,gennum,capital from nation order by power desc";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$count = MYDB_num_rows($result);

for($i=1; $i <= $count; $i++) {
    $nation = MYDB_fetch_array($result);   //국가정보

    $query = "select city,name from city where nation='{$nation['nation']}'"; // 도시 이름 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    $query = "select npc,name,city from general where nation='{$nation['nation']}' and level='12'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level12 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='11'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level11 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='10'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level10 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='9'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level9 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='8'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level8 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='7'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level7 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='6'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level6 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and level='5'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level5 = MYDB_fetch_array($genresult);

    $query = "select npc,name from general where nation='{$nation['nation']}' and npc != 5 order by dedication desc";    // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);

    if($level12['name'] == "") { $l12 = "-"; }
    elseif($level12['npc'] >= 2) { $l12 = "<font color=cyan>{$level12['name']}</font>"; }
    elseif($level12['npc'] == 1) { $l12 = "<font color=skyblue>{$level12['name']}</font>"; }
    else { $l12 = $level12['name']; }

    if($level11['name'] == "") { $l11 = "-"; }
    elseif($level11['npc'] >= 2) { $l11 = "<font color=cyan>{$level11['name']}</font>"; }
    elseif($level11['npc'] == 1) { $l11 = "<font color=skyblue>{$level11['name']}</font>"; }
    else { $l11 = $level11['name']; }

    if($level10['name'] == "") { $l10 = "-"; }
    elseif($level10['npc'] >= 2) { $l10 = "<font color=cyan>{$level10['name']}</font>"; }
    elseif($level10['npc'] == 1) { $l10 = "<font color=skyblue>{$level10['name']}</font>"; }
    else { $l10 = $level10['name']; }

    if($level9['name'] == "") { $l9 = "-"; }
    elseif($level9['npc'] >= 2) { $l9 = "<font color=cyan>{$level9['name']}</font>"; }
    elseif($level9['npc'] == 1) { $l9 = "<font color=skyblue>{$level9['name']}</font>"; }
    else { $l9 = $level9['name']; }

    if($level8['name'] == "") { $l8 = "-"; }
    elseif($level8['npc'] >= 2) { $l8 = "<font color=cyan>{$level8['name']}</font>"; }
    elseif($level8['npc'] == 1) { $l8 = "<font color=skyblue>{$level8['name']}</font>"; }
    else { $l8 = $level8['name']; }

    if($level7['name'] == "") { $l7 = "-"; }
    elseif($level7['npc'] >= 2) { $l7 = "<font color=cyan>{$level7['name']}</font>"; }
    elseif($level7['npc'] == 1) { $l7 = "<font color=skyblue>{$level7['name']}</font>"; }
    else { $l7 = $level7['name']; }

    if($level6['name'] == "") { $l6 = "-"; }
    elseif($level6['npc'] >= 2) { $l6 = "<font color=cyan>{$level6['name']}</font>"; }
    elseif($level6['npc'] == 1) { $l6 = "<font color=skyblue>{$level6['name']}</font>"; }
    else { $l6 = $level6['name']; }

    if($level5['name'] == "") { $l5 = "-"; }
    elseif($level5['npc'] >= 2) { $l5 = "<font color=cyan>{$level5['name']}</font>"; }
    elseif($level5['npc'] == 1) { $l5 = "<font color=skyblue>{$level5['name']}</font>"; }
    else { $l5 = $level5['name']; }

    $generals = $db->query('SELECT no,nation,npc,name,level,penalty,permission FROM general WHERE nation=%i ORDER BY no ASC', $nation['nation']);
    $ambassadors = [];
    foreach($generals as $general){
        if(checkSecretPermission($general, false) == 4){
            $ambassadors[] = $general['name'];
        }
    }

    echo "
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=8 align=center style=color:".newColor($nation['color'])."; bgcolor={$nation['color']}>【 {$nation['name']} 】</td>
    </tr>
    <tr>
        <td width=80 align=center id=bg1>성 향</td>
        <td width=170 align=center><font color=yellow>".getNationType($nation['type'])."</font></td>
        <td width=80 align=center id=bg1>작 위</td>
        <td width=170 align=center>".getNationLevel($nation['level'])."</td>
        <td width=80 align=center id=bg1>국 력</td>
        <td width=170 align=center>{$nation['power']}</td>
        <td width=80 align=center id=bg1>장수 / 속령</td>
        <td width=170 align=center>".count($generals)." / {$citycount}</td>
    </tr>
    <tr>
        <td align=center id=bg1>".getLevel(12, $nation['level'])."</td>
        <td align=center>$l12</td>
        <td align=center id=bg1>".getLevel(11, $nation['level'])."</td>
        <td align=center>$l11</td>
        <td align=center id=bg1>".getLevel(10, $nation['level'])."</td>
        <td align=center>$l10</td>
        <td align=center id=bg1>".getLevel( 9, $nation['level'])."</td>
        <td align=center>$l9</td>
    </tr>
    <tr>
        <td align=center id=bg1>".getLevel( 8, $nation['level'])."</td>
        <td align=center>$l8</td>
        <td align=center id=bg1>".getLevel( 7, $nation['level'])."</td>
        <td align=center>$l7</td>
        <td align=center id=bg1>".getLevel( 6, $nation['level'])."</td>
        <td align=center>$l6</td>
        <td align=center id=bg1>".getLevel( 5, $nation['level'])."</td>
        <td align=center>$l5</td>
    </tr>
    <tr>
        <td align=center id=bg1>외교권자</td><td colspan=7>";
    echo join(', ', $ambassadors);
    echo "</td>
    </tr>
    <tr>
        <td colspan=8>";
    if($nation['level'] > 0) {
        echo "속령 일람 : ";

        for($j=0; $j < $citycount; $j++) {
            $city = MYDB_fetch_array($cityresult);
            if($city['city'] == $nation['capital']) {
                echo "<font color=cyan>[{$city['name']}]</font>, ";
            } else {
                echo "{$city['name']}, ";
            }
        }
    } else {
        $query = "select name from city where city='{$level12['city']}'";   // 군주 위치 도시 이름
        $cityResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($cityResult);

        echo "현재 위치 : <font color=yellow>{$city['name']}</font>";
    }
    echo"
        </td>
    </tr>
    <tr>
        <td colspan=8> 장수 일람 : ";
    foreach($generals as $general){
        if($general['npc'] >= 2) { echo "<font color=cyan>{$general['name']}</font>, "; }
        elseif($general['npc'] == 1) { echo "<font color=skyblue>{$general['name']}</font>, "; }
        else { echo "{$general['name']}, "; }
    }
    echo"
        </td>
    </tr>
</table>
<br>";
}

//재야
$query = "select npc,name from general where nation='0'";    // 장수 목록
$genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($genresult);

$query = "select name from city where nation='0'"; // 도시 이름 목록
$cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$citycount = MYDB_num_rows($cityresult);

echo "
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=5 align=center>【 재 야 】</td>
    </tr>
    <tr>
        <td width=498 align=center>&nbsp;</td>
        <td width=123 align=center id=bg1>장 수</td>
        <td width=123 align=center>{$gencount}</td>
        <td width=123 align=center id=bg1>속 령</td>
        <td width=123 align=center>{$citycount}</td>
    </tr>
    <tr>
        <td colspan=5> 속령 일람 : ";
for($j=0; $j < $citycount; $j++) {
    $city = MYDB_fetch_array($cityresult);
    echo "{$city['name']}, ";
}
echo"
        </td>
    </tr>
    <tr>
        <td colspan=5> 장수 일람 : ";
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        if($general['npc'] >= 2) { echo "<font color=cyan>{$general['name']}</font>, "; }
        elseif($general['npc'] == 1) { echo "<font color=skyblue>{$general['name']}</font>, "; }
        else { echo "{$general['name']}, "; }
    }
    echo"
        </td>
    </tr>
</table>";

?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>

</html>
