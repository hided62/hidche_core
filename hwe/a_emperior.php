<?php
namespace sammo;

include "lib.php";
include "func.php";

$db = DB::db();

increaseRefresh("왕조일람", 1);
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 왕조일람</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class="tb_layout bg0">
    <tr><td>역 대 왕 조<br>
        <button onclick=window.close()>창 닫기</button><br>
    </td></tr>
</table>

<?php

$showCurrentNation = true;

$emperiors = $db->query('SELECT * FROM emperior ORDER BY `no` DESC');

if($emperiors){
    $serverID = $emperior[0]['server_id']??($emperior[0]['serverID']??null);
    if($serverID == UniqueConst::$serverID){
        $showCurrentNation = false;
    }
}

if ($showCurrentNation) {
    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
?>

<table align=center width=1000 style="margin-top:10px;" class='tb_layout bg0'>
    <tr>
        <td style="background-color:#333333;" align=center colspan=8>
            <font size=5>현재 (<?=$year?>年 <?=$month?>月)</font>
            <a href="a_history.php"><button>역사 보기</button></a>
        </td>
    </tr>
</table>

<?php
}

foreach($emperiors as $emperior){
    $serverID = $emperior['server_id']??($emperior['serverID']??null);
?>

<table align=center width=1000 style="margin-top:10px;" class='tb_layout bg0'>
    <tr>
        <td bgcolor=skyblue align=center colspan=8>
            <font size=5><?=$emperior['phase']?></font>
            
            <a href="a_emperior_detail.php?select=<?=$emperior['no']?>"><button>자세히</button></a>
            
            <?php if($emperior['server_id']): ?>
            <a href="a_history.php?serverID=<?=$emperior['server_id']?>"><button>역사 보기</button></a>
            <?php endif ?>
        </td>
    </tr>
    <tr>
        <td align=center style=color:".newColor($emperior['color'])."; bgcolor=<?=$emperior['color']?> colspan=8>
            <font size=5><?=$emperior['name']?> (<?=$emperior['year']?>年 <?=$emperior['month']?>月)</font>
        </td>
    </tr>
    <tr>
        <td id=bg1 align=center width=80>국 력</td>
        <td align=center width=170><?=$emperior['power']?></td>
        <td id=bg1 align=center width=80>장 수</td>
        <td align=center width=170><?=$emperior['gennum']?></td>
        <td id=bg1 align=center width=80>속 령</td>
        <td align=center width=170><?=$emperior['citynum']?></td>
        <td id=bg1 align=center width=80>성 향</td>
        <td align=center width=170><?=$emperior['type']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>황 제</td>
        <td align=center><?=$emperior['l12name']?></td>
        <td id=bg1 align=center>승 상</td>
        <td align=center><?=$emperior['l11name']?></td>
        <td id=bg1 align=center>표 기 장 군</td>
        <td align=center><?=$emperior['l10name']?></td>
        <td id=bg1 align=center>사 공</td>
        <td align=center><?=$emperior['l9name']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>거 기 장 군</td>
        <td align=center><?=$emperior['l8name']?></td>
        <td id=bg1 align=center>태 위</td>
        <td align=center><?=$emperior['l7name']?></td>
        <td id=bg1 align=center>위 장 군</td>
        <td align=center><?=$emperior['l6name']?></td>
        <td id=bg1 align=center>사 도</td>
        <td align=center><?=$emperior['l5name']?></td>
    </tr>
</table>



<?php
}
?>

<table style="margin-top:10px;" align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>