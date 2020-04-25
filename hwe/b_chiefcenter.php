<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalObj = General::createGeneralObjFromDB($session->generalID);
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
<?=WebUtil::printJS('../e_lib/jquery.redirect.js')?>
<?=WebUtil::printJS('../e_lib/moment.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/chiefCenter.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/chiefCenter.css')?>
<script type="text/javascript">
var maxChiefTurn = <?=GameConst::$maxChiefTurn?>;
</script>

</head>

<body>
<div id='container' class='tb_layout bg0' style='width:1000px;margin:auto;border:solid 1px #888888;'>
<div class='tb_layout bg0'>사 령 부<button type='button' id='reloadTable'>갱신</button><br>
<?=backButton()?></div>
<div style='background-color:skyblue;text-align:center;'>수뇌부 일정</div>
<div class="chiefSubTable" style='height:<?=22*(GameConst::$maxChiefTurn+1)?>px;'
    ><div class='chiefTurnIdxPanel'
        ><div class='bg1 chiefTurnIdx'>.</div
<?php foreach(Util::range(GameConst::$maxChiefTurn) as $idx):?>
        ><div class='bg0 chiefTurnIdx'><?=$idx+1?></div
<?php endforeach; ?>
    ></div
<?php foreach([12, 10, 8, 6] as $chiefIdx): ?>
    ><div class='bg2 chiefPlate' style='flex-grow:1;' id='chief_<?=$chiefIdx?>'
        ><div class='bg1 chiefNamePlate'><span class='chiefLevelText'>-</span> : <span class='chiefName'>&nbsp;</span></div
<?php   foreach(Util::range(GameConst::$maxChiefTurn) as $turnIdx): ?>
        ><div class='chiefTurnBox turn<?=$turnIdx?>'
            ><div class='chiefTurnTime'>&nbsp;</div
            ><div class='chiefTurnPad'><span class='chiefTurnText'>&nbsp;</span></div
        ></div
<?php   endforeach; ?>
    ></div
<?php endforeach; ?>
    ><div class='chiefTurnIdxPanel tail'
        ><div class='bg1 chiefTurnIdx'>.</div
<?php foreach(Util::range(GameConst::$maxChiefTurn) as $idx):?>
        ><div class='bg0 chiefTurnIdx'><?=$idx+1?></div
<?php endforeach; ?>
    ></div
></div
><div id='controlPlate' style='display:flex;flex-flow:row wrap;justify-content:center;'
    ><div style='width:400px;text-align:right;'
        ><?=chiefTurnTable()
    ?></div
    ><div style='width:400px;display: flex;justify-content: center;flex-direction: column;'
        ><div
            ><input type='button' id='turnPush' style='visibility:hidden;background-color:<?=GameConst::$basecolor2?>;color:white;font-size:13px;' value='미루기▼'
            ><input type='button' id='turnPull' style='visibility:hidden;background-color:<?=GameConst::$basecolor2?>;color:white;font-size:13px;' value='▲당기기'
        ></div
        ><div
            ><?=chiefCommandTable($generalObj)
            ?><input type='button' id='setCommand' style='visibility:hidden;background-color:<?=GameConst::$basecolor2?>;color:white;font-size:13px;' value='실 행'
        ></div
    ></div
></div
><div class="chiefSubTable" style='height:<?=22*(GameConst::$maxChiefTurn+1)?>px;'
    ><div class='chiefTurnIdxPanel'
        ><div class='bg1 chiefTurnIdx'>.</div
<?php foreach(Util::range(GameConst::$maxChiefTurn) as $idx):?>
        ><div class='bg0 chiefTurnIdx'><?=$idx+1?></div
<?php endforeach; ?>
    ></div
<?php foreach([11, 9, 7, 5] as $chiefIdx): ?>
    ><div class='bg2 chiefPlate' style='flex-grow:1;' id='chief_<?=$chiefIdx?>'
        ><div class='bg1 chiefNamePlate'><span class='chiefLevelText'>-</span> : <span class='chiefName'>&nbsp;</span></div
<?php   foreach(Util::range(GameConst::$maxChiefTurn) as $turnIdx): ?>
        ><div class='chiefTurnBox turn<?=$turnIdx?>'
            ><div class='chiefTurnTime'>&nbsp;</div
            ><div class='chiefTurnPad'><span class='chiefTurnText'>&nbsp;</span></div
        ></div
<?php   endforeach; ?>
    ></div
<?php endforeach; ?>
    ><div class='chiefTurnIdxPanel tail'
        ><div class='bg1 chiefTurnIdx'>.</div
<?php foreach(Util::range(GameConst::$maxChiefTurn) as $idx):?>
        ><div class='bg0 chiefTurnIdx'><?=$idx+1?></div
<?php endforeach; ?>
    ></div
></div
><div
    ><?=backButton()
    ?><?=banner()
?></div>
</div>
</body>
</html>

