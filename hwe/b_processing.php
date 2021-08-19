<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사

$commandType = Util::getReq('command', 'string');
$turnList = array_map('intval', explode('_', Util::getReq('turnList', 'string', '0')));
$isChiefTurn = Util::getReq('is_chief', 'bool', false);

function die_redirect()
{
    global $isChiefTurn;
    if(!$isChiefTurn){
        header('location:index.php', true, 303);
    }
    else{
        header('location:b_chiefcenter.php', true, 303);
    }
    die();
}

if(!$turnList || !$commandType){
    die_redirect();
}
if(!is_array($turnList)){
    die_redirect();
}

$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();

if(!$isChiefTurn && !in_array($commandType, Util::array_flatten(GameConst::$availableGeneralCommand))){
    die_redirect();
}

if($isChiefTurn && !in_array($commandType, Util::array_flatten(GameConst::$availableChiefCommand))){
    die_redirect();
}

$gameStor = KVStorage::getStorage($db, 'game_env')->turnOnCache();
$env = $gameStor->getAll();
$general = General::createGeneralObjFromDB($session->generalID);

if(!$isChiefTurn){
    $commandObj = buildGeneralCommandClass($commandType, $general, $env);
}
else{
    if($general->getVar('officer_level') < 5){
        die_redirect();
    }
    $commandObj = buildNationCommandClass($commandType, $general, $env, new LastTurn());
}


if($commandObj->isArgValid()){
    //인자가 필요없는 타입의 경우 processing에서 '전혀' 처리하지 않음!
    die_redirect();
}

if(!$commandObj->hasPermissionToReserve()){
    die_redirect();
}

$jsList = $commandObj->getJSFiles();
$cssList = $commandObj->getCSSFiles();
?>

<!DOCTYPE html>
<html>
<head>
<title><?=$commandObj->getName()?></title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/vendors.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('../e_lib/select2/select2.full.min.js')?>
<?=WebUtil::printJS('d_shared/base_map.js')?>
<?=WebUtil::printJS('js/processing.js')?>
<script>
window.serverNick = '<?=DB::prefix()?>';
window.serverID = '<?=UniqueConst::$serverID?>';
window.command = '<?=$commandType?>';
window.turnList = [<?=join(', ',$turnList)?>];
window.isChiefTurn = <?=$isChiefTurn?'true':'false'?>;
</script>
<?php
foreach($jsList as $js){
    print(WebUtil::printJS($js));
}
?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2-bootstrap4.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/main.css')?>
<?=WebUtil::printCSS('css/map.css')?>
<?=WebUtil::printCSS('css/processing.css')?>
<?php
foreach($cssList as $css){
    print(WebUtil::printCSS($css));
}
?>
</head>
<body class="img_back">
<table class="tb_layout bg0" style="width:1000px;margin:auto;">
    <tr><td class="bg1" style='text-align:center;'><?=$commandObj->getName()?></td></tr>
    <tr><td>
    <input type=button value='돌아가기' onclick="history.back();"><br>
</td></tr></table>

<div class="tb_layout bg0" style="width:1000px;margin:auto;padding-bottom:2em;border:solid 1px gray;">
<?=$commandObj->getForm()?>
</div>

<table class="tb_layout bg0" style="width:1000px;margin:auto;">
    <tr><td>
    <input type=button value='돌아가기' onclick="history.back();"><br>
    <?=banner()?>
</td></tr></table>


</body>
</html>
