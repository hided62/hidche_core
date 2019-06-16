<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사

$commandType = Util::getReq('commandtype', 'string');
$turnList = Util::getReq('turn', 'array_int');

if(!$turn || $commandtype === null){
    header('location:index.php', true, 303);
    die();
}

$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();

$gameStor = KVStorage::getStorage($db, 'game_env')->turnOnCache();
$env = $gameStor->getValues(['init_year','init_month','startyear','year','month','show_img_level','join_mode','maxnation']);
$general = General::createGeneralObjFromDB($session->generalID);
$commandObj = buildGeneralCommandClass($commandType, $general, $env);

if($general->getVar('level') < 5 && ($commandObj instanceof Command\NationCommand)){
    header('location:index.php', true, 303);
    die();
}

if($commandObj->isArgValid()){
    //인자가 필요없는 타입의 경우 processing에서 '전혀' 처리하지 않음!
    header('location:index.php', true, 303);
    die();
}

[$jsList, $cssList] = $commandObj->getResourceFiles();
?>

<!DOCTYPE html>
<html>
<head>
<title><?=$name?></title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('d_shared/base_map.js')?>
<?=WebUtil::printJS('js/map.js')?>
<script>
window.serverNick = '<?=DB::prefix()?>';
window.serverID = '<?=UniqueConst::$serverID?>';
</script>
<?php
foreach($jsList as $js){
    print(WebUtil::printJS($js));
}
?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/main.css')?>
<?=WebUtil::printCSS('css/map.css')?>
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

<div class="tb_layout bg0" style="width:100px;margin:auto;">
<form method='post' id='submitForm'>
<input type='hidden' name='command' value='<?=$commandType?>'>
<?php foreach($turnList as $turnIdx): ?>
    <input type=hidden name='turn[]' value=<?=$turnIdx?>>
<?php endforeach; ?>
<?=$commandObj->getForm()?>
</form>
</div>

<table class="tb_layout bg0" style="width:1000px;margin:auto;">
    <tr><td>
    <input type=button value='돌아가기' onclick="history.back();"><br>
</td></tr></table>


</body>
</html>
