<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("내무부", 1);

$me = $db->queryFirstRow('SELECT no, nation, level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);


$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if ($permission < 1) {
    echo "권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.";
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 외교부</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<!--<?=WebUtil::printCSS('../e_lib/tui.editor/tui-editor.min.css')?>-->
<!--<?=WebUtil::printCSS('../e_lib/tui.editor/tui-editor-contents.min.css')?>-->
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/diplomacy.css')?>
<script>
var permissionLevel = <?=$permission?>; //
</script>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<!--<?=WebUtil::printJS('../e_lib/tui.editor/tui-editor-Editor-all.min.js')?>-->
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/board.js')?>

</head>
<body>

<template id='diplomacyTemplate'>
<div class='diplomacyFrame'>
<div><span class='articleTitle'></span></div>
<div class='authorPlate'><span class='authorIcon'></span><span class='authorName'><span><span class='date'></span></div>    
<div class='boardArticle'>

</div>
</div>
</div>
</template>

<div style='width=1000px;' class='tb_layout bg0'>
    <?=backButton()?><br>
    <?=banner()?>
</div>
</body>
</html>