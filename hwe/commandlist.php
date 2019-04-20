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
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
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

    // 명령 목록
    $admin = $gameStor->getValues(['year','month','turnterm','turntime']);

    $me = $db->queryFirstRow("SELECT `no`,name,city,nation,level,turntime,last_turn FROM general WHERE `owner`=%s", $userID);
    if(!$me){
        echo "로그인 되어있지 않습니다.";
        return;
    }
    $generalObj = new General($me, null, null, null, false);
    $turnList = $db->query('SELECT turn_idx, action, arg FROM general_turn WHERE general_id = %i ORDER BY turn_idx ASC', $generalObj->getID());
    $turnBrief = getGeneralTurnBrief($generalObj, $turnList);

    echo "<table width=300 height=700 class='tb_layout bg2'>
<form name=clock>
    <tr>
        <td colspan=4 align=center id=bg0><b>- 명령 목록 - <input value='$date' type=text name=clock size=19 style=background-color:black;color:white;border-style:none;></b></td>
    </tr>";

    $year = $admin['year'];
    $month = $admin['month'];
    // 실행된 턴시간이면 +1
    $cutTurn = cutTurn($me['turntime'], $admin['turnterm']);
    if($admin['turntime'] <= $cutTurn) { $month++; }

    $totaldate = $me['turntime'];

    foreach($turnBrief as $rawTurnIdx => $turn) {
        if($month == 13) {
            $month = 1;
            $year++;
        }
        $turnIdx = $rawTurnIdx + 1;
        $turndate = substr($totaldate,11, 5);
        echo "
    <tr height=28>
        <td width=24 align=center height=24 class='bg0'><b>$turnIdx</b></td>
        <td width=71 align=center height=24 class='bg1' style='max-width:68px;white-space:nowrap;overflow:hidden;'><b>{$year}年 {$month}月</b></td>
        <td width=42 align=center bgcolor=black><b>$turndate</b></td>
        <td width=150 align=center height=24 style=table-layout:fixed;>{$turn}</td>
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
