<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 사령부</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/chiefCenter.css')?>
<script type="text/javascript">
var maxChiefTurn = <?=GameConst::$maxChiefTurn?>;
</script>

</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>사 령 부<input id='refreshChiefTurn' type=button value='갱신'><br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
<thead><tr><td colspan=10 align=center bgcolor=skyblue>수뇌부 일정</td></tr></thead>
<tbody>
<tr>
    <td class='bg1 center'>.</td>
    <td colspan=2 class='bg1 chiefNamePlate level12'>- :</td>
    <td colspan=2 class='bg1 chiefNamePlate level10'>- :</td>
    <td colspan=2 class='bg1 chiefNamePlate level8'>- :</td>
    <td colspan=2 class='bg1 chiefNamePlate level6'>- :</td>
    <td class='bg1 center'>.</td>
</tr>
<?php foreach([12, 10, 8, 6] as $turnIdx): ?>
    <tr class='turnIdx<?=$turnIdx?>'>
        <td class='bg0 center turnIdxHeader'><?=($turnIdx+1)?></td>
        <td class='chiefTurnTime level12'></td>
        <td class='chiefTurnText level12 bg2'>-</td>
        <td class='chiefTurnTime level10'></td>
        <td class='chiefTurnText level10 bg2'>-</td>
        <td class='chiefTurnTime level8'></td>
        <td class='chiefTurnText level8 bg2'>-</td>
        <td class='chiefTurnTime level6'></td>
        <td class='chiefTurnText level6 bg2'>-</td>
        <td class='bg0 center turnIdxHeader'><?=($turnIdx+1)?></td>
    </tr>
<?php endforeach; ?>
<tr>
    <td colspan="5" style='text-align:right;'>
        <?=chiefTurnTable()?>
    </td>
    <td colspan="5" style='text-align:left;'>
        <input id='turnPush' type='hidden' style=background-color:<?=GameConst::$basecolor2?>;color:white;width:58px;font-size:13px; value='미루기▼' onclick='turn(0)'>
        <input id='turnPull' type='hidden' style=background-color:<?=GameConst::$basecolor2?>;color:white;width:58px;font-size:13px; value='▲당기기' onclick='turn(1)'>
        <br>
        <?=chiefCommandTable()?>
        <input id='setCommand' type='hidden' style='background-color:<?=GameConst::$basecolor2?>;color:white;width:55px;font-size:13px;' value='실 행'>
    </td>
</tr>
<tr>
    <td class='bg1 center'>.</td>
    <td colspan=2 class='bg1 chiefNamePlate level12'>- :</td>
    <td colspan=2 class='bg1 chiefNamePlate level10'>- :</td>
    <td colspan=2 class='bg1 chiefNamePlate level8'>- :</td>
    <td colspan=2 class='bg1 chiefNamePlate level6'>- :</td>
    <td class='bg1 center'>.</td>
</tr>
<?php foreach([11, 9, 7, 5] as $turnIdx): ?>
    <tr class='turnIdx<?=$turnIdx?>'>
        <td class='bg0 center turnIdxHeader'><?=($turnIdx+1)?></td>
        <td class='chiefTurnTime level11'></td>
        <td class='chiefTurnText level11 bg2'>-</td>
        <td class='chiefTurnTime level9'></td>
        <td class='chiefTurnText level9 bg2'>-</td>
        <td class='chiefTurnTime level7'></td>
        <td class='chiefTurnText level7 bg2'>-</td>
        <td class='chiefTurnTime level5'></td>
        <td class='chiefTurnText level5 bg2'>-</td>
        <td class='bg0 center turnIdxHeader'><?=($turnIdx+1)?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>

