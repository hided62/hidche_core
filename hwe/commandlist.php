<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::getInstance()->setReadOnly();

$db = DB::db();

?>
<!DOCTYPE html>
<html>
<head>
<title>커맨드리스트</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<script type="text/javascript">
<?php
if(!$session->isGameLoggedIn()){
    echo 'window.parent.location.href = "../";';
}
?>
function myclock() {
    lastday = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

    date = document.clock.clock.value;

    year = parseInt(date.substr(0, 4), 10);
    month = parseInt(date.substr(5,2), 10);
    day = parseInt(date.substr(8, 2), 10);
    hour = parseInt(date.substr(11, 2), 10);
    min = parseInt(date.substr(14, 2), 10);
    sec = parseInt(date.substr(17, 2), 10);

    //윤년계산
    if(((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) { lastday[1] = 29; }
    else { lastday[1] = 28; }

//    if(term > 0) term--;

    sec++;
    if(sec >= 60) { sec = 0; min++; }
    if(min >= 60) { min = 0; hour++; }
    if(hour >= 24) { hour = 0; day++; }
    if(day > lastday[month-1]) { month++; day = 1; }
    if(month >= 13) { year++; month = 1; }
    if(month < 10) { month = '0' + month; }
    if(day < 10) { day = '0' + day; }
    if(hour < 10) { hour = '0' + hour; }
    if(min < 10) { min = '0' + min; }
    if(sec < 10) { sec = '0' + sec; }
    date = '' + year + '-' + month + '-' + day + ' ' + hour + ':' + min + ':' + sec;

    document.clock.clock.value = date;

    window.setTimeout("myclock();", 1000);
}
</script>

</head>
<body OnLoad='myclock()'>
<?php
myCommandList();

function myCommandList() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $date = date('Y-m-d H:i:s');

    // 명령 목록
    $admin = $gameStor->getValues(['year','month','turnterm']);

    $me = $db->queryFirstRow("SELECT `no`,turntime,term,turn0,turn1,turn2,turn3,turn4,turn5,turn6,turn7,turn8,turn9,turn10,turn11,turn12,turn13,turn14,turn15,turn16,turn17,turn18,turn19,turn20,turn21,turn22,turn23 FROM general WHERE `owner`=%s", $userID);
    if(!$me){
        echo "로그인 되어있지 않습니다.";
        return;
    }
    $turn = getTurn($me, 2);

    echo "<table width=300 height=700 class='tb_layout bg2'>
<form name=clock>
    <tr>
        <td colspan=4 align=center id=bg0><b>- 명령 목록 - <input value='$date' type=text name=clock size=19 style=background-color:black;color:white;border-style:none;></b></td>
    </tr>";

    $year = $admin['year'];
    $month = $admin['month'];
    // 실행된 턴시간이면 +1
    $cutTurn = cutTurn($me['turntime'], $admin['turnterm']);
    if($date <= $cutTurn) { $month++; }

    $totaldate = $me['turntime'];

    for($i=0; $i < 24; $i++) {
        if($month == 13) {
            $month = 1;
            $year++;
        }
        $j = $i + 1;
        $turndate = substr($totaldate,11, 5);
        echo "
    <tr height=28>
        <td width=25 align=center height=24 id=bg0><b>$j</b></td>
        <td width=70 align=center height=24 id=bg1><b>{$year}年 {$month}月</b></td>
        <td width=42 align=center bgcolor=black><b>$turndate</b></td>
        <td width=150 align=center height=24 style=table-layout:fixed;>$turn[$i]</td>
    </tr>";
        $month++;
        $totaldate = addTurn($totaldate, $admin['turnterm']);
    }

    echo "
</form>
</table>
";
}
?>

</body>
</html>
