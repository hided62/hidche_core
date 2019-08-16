<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::getInstance()->setReadOnly();

$db = DB::db();

if (!$session->isGameLoggedIn()) {
    die('<script>window.location.href = "../"</script>');
}

myCommandList();

function myCommandList() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $date = date('Y-m-d H:i:s');

    // 명령 목록
    $admin = $gameStor->getValues(['year','month','turnterm','turntime','opentime']);

    $me = $db->queryFirstRow("SELECT `no`,turntime,term,turn0,turn1,turn2,turn3,turn4,turn5,turn6,turn7,turn8,turn9,turn10,turn11,turn12,turn13,turn14,turn15,turn16,turn17,turn18,turn19,turn20,turn21,turn22,turn23 FROM general WHERE `owner`=%s", $userID);
    if(!$me){
        echo "로그인 되어있지 않습니다.";
        return;
    }
    $turn = getTurn($me, 2);

    echo "<table width=300 height=700 class='tb_layout bg2'>
    <tr>
        <td colspan=4 align=center id=bg0><b>- 명령 목록 - <input value='$date' type=text id=clock size=19 style=background-color:black;color:white;border-style:none;></b></td>
    </tr>";

    $year = $admin['year'];
    $month = $admin['month'];
    // 실행된 턴시간이면 +1
    $cutTurn = cutTurn($me['turntime'], $admin['turnterm']);
    if($date <= $cutTurn && $date >= $admin['opentime']) { $month++; }

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
        <td width=24 align=center height=24 id=bg0><b>$j</b></td>
        <td width=71 align=center height=24 id=bg1 style='max-width:68px;white-space:nowrap;overflow:hidden;'><b>{$year}年 {$month}月</b></td>
        <td width=42 align=center bgcolor=black><b>$turndate</b></td>
        <td width=150 align=center height=24 style=table-layout:fixed;>$turn[$i]</td>
    </tr>";
        $month++;
        $totaldate = addTurn($totaldate, $admin['turnterm']);
    }

    echo "
</table>
";
}