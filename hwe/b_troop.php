<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("부대편성", 1);

$query = "select no,nation,troop from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select * from troop where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$troopcount = MYDB_num_rows($result);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 부대편성</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('js/ext.plugin_troop.js')?>
</head>

<body>
<div style="width:1000px;margin:auto;">
<table width=1000 class='tb_layout bg0'>
    <tr><td>부 대 편 성<br><?=backButton()?></td></tr>
</table>
<form name=form1 method=post action=c_troop.php>
<table id="troop_list" class='tb_layout bg0'>
    <thead>
    <tr>
        <td width=64  class='bg1 center'>선 택</td>
        <td width=130  class='bg1 center'>부 대 정 보</td>
        <td width=100  class='bg1 center'>부 대 장</td>
        <td width=576 class='bg1 center' style=table-layout:fixed;word-break:break-all;>장 수</td>
        <td width=130  class='bg1 center' style=table-layout:fixed;word-break:break-all;>부대장행동</td>
    </tr>
    </thead>
    <tbody>
<?php
for($i=0; $i < $troopcount; $i++) {
    $troop = MYDB_fetch_array($result);

    $genlist = "";
    $query = "select no,name,picture,imgsvr,turntime,city,turn0,turn1,turn2,turn3,turn4,turn5 from general where troop='{$troop['troop']}'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        $genlist .= $general['name'].", ";
        if($troop['no'] == $general['no']) {
            $picture = $general['picture'];
            $imageTemp = GetImageURL($general['imgsvr']);
            $name = $general['name'];
            $turntime = $general['turntime'];
            $query = "select name from city where city='{$general['city']}'";
            $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($cityresult);
            $cityname = $city['name'];
            $turn = "";
            for($k=0; $k < 5; $k++) {
                $m = $k+1;
                $turnK = DecodeCommand($general["turn{$k}"]);
                if($turnK[0] == 26) {
                    $turn .= "&nbsp;$m : 집합<br>";
                } else {
                    $turn .= "&nbsp;$m : ∼<br>";
                }
            }
        }
    }
    $genlist .= "({$gencount}명)";

    if($me['troop'] == 0) {
        echo "
    <tr>
        <td align=center rowspan=2><input "; echo $i==0?"checked ":""; echo "type=radio name=troop value='{$troop['troop']}'></td>
        <td align=center >{$troop['name']}<br>【 $cityname 】</td>
        <td height=64 style='background:no-repeat center url(\"{$imageTemp}/{$picture}\");background-size:64px;'>&nbsp;</td>
        <td rowspan=2 width=62>$genlist</td>
        <td rowspan=2>$turn</td>
    </tr>
    <tr><td align=center><font size=2>【턴】".substr($turntime, 14)."</font></td><td align=center><font size=1>$name</font></td></tr>
    <tr><td colspan=5>";
    } else {
        echo "
    <tr>
        <td align=center rowspan=2>&nbsp;</td>
        <td align=center >{$troop['name']}<br>【 $cityname 】</td>
        <td height=64 style='background:no-repeat center url(\"{$imageTemp}/{$picture}\");background-size:64px;'>&nbsp;</td>
        <td rowspan=2 width=576>$genlist</td>
        <td rowspan=2>";

        if($troop['no'] == $me['no']) {
            $query = "select no,name from general where troop='{$troop['troop']}' and no!='{$me['no']}' order by binary(name)";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $genCount = MYDB_num_rows($genresult);
                echo "
            <select name=gen size=3 style=color:white;background-color:black;font-size:13px;width:128px;>";
            for($k=0; $k < $genCount; $k++) {
                $general = MYDB_fetch_array($genresult);
                echo "
                <option value={$general['no']}>{$general['name']}</option>";
            }
            echo "
            </select><br>
            <input type=submit name=btn value='부 대 추 방' style=width:130px;height:25px;>";
        } else {
            echo $turn;
        }

        echo "
        </td>
    </tr>
    <tr><td align=center><font size=2>【턴】".substr($turntime, 14)."</font></td><td align=center><font size=1>$name</font></td></tr>
    <tr><td colspan=5></td></tr>";
    }
}
echo "</tbody>
<tfoot><tr><td>";
if ($troopcount == 0) {
}
else if($me['troop'] == 0) {
    echo"
<input type=submit name=btn value='부 대 가 입'>";
} else {
    echo"
<input type=submit name=btn value='부 대 탈 퇴' onclick='return confirm(\"정말 부대를 탈퇴하시겠습니까?\")'>";
}

echo "
</td></tr>
</tfoot>
</table>
<br>";

echo "
<table width=1000 class='tb_layout bg0'>
    <tr>
        <td width=80 id=bg1>부 대 명</td>
        <td width=130><input type=text style=color:white;background-color:black; size=18 maxlength=9 name=name></td>";
if($me['troop'] == 0) {
    echo "
        <td><input type=submit name=btn value='부 대 창 설'></td>";
} else {
    echo "
        <td><input type=submit name=btn value='부 대 변 경'></td>";
}
echo "
    </tr>
</table>";

?>
<table width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</form>
</div>
</body>
</html>

