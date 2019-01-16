<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$isSecretBoard = Util::getReq('isSecret', 'bool', false);

//increaseRefresh("회의실", 1);

$me = $db->queryFirstRow('SELECT no, nation, level, permission, con, turntime, belong, penalty FROM general WHERE owner=%i', $userID);


$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if($permission < 0){
    echo '국가에 소속되어있지 않습니다.';
    die();
}
else if ($isSecretBoard && $permission < 2) {
    echo "권한이 부족합니다. 수뇌부가 아닙니다.";
    die();
}

$boardName = $isSecretBoard?'회의실':'기밀실';

?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: <?=$boardName?></title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../e_lib/tui.editor/tui-editor.min.css')?>
<?=WebUtil::printCSS('../e_lib/tui.editor/tui-editor-contents.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/select_npc.css')?>
<script>
var isSecretBoard = <?=($isSecretBoard?'true':'false')?>; //
</script>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../e_lib/tui.editor/tui-editor-Editor-all.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/board.js')?>

</head>
<body>

<div id='newArticle'>
<div><span class='articleTitle'>제목</span><input class='commentText' type='text' maxlength='250'></input></div>
<div class='boardArticle'>

</div>
<button type='button' class='submitArticle'>등록</button>
</div>

<template id='articleTemplate'>
<div class='articleFrame'>
<div><span class='articleTitle'></span></div>
<div class='authorPlate'><span class='authorIcon'></span><span class='authorName'><span><span class='date'></span></div>    
<div class='boardArticle'>

</div>
</div>
<div class='commentFrame'>
    <ul class='commentList'>

    </ul>
    <div>
        <input class='commentText' type='textarea' maxlength='250'></input>
        <button type='button' class='submitComment'>등록</button>
    </div>
</div>
</div>
</template>

<template id='commentTemplate'>
<li class='comment'>
<span class='author'></span>
<span class='text'></span>
<span class='date'></span>
</li>
</template>

<div style='width=1000px;' class='tb_layout bg0'>
    <?=backButton()?><br>
    <?=banner()?>
</div>
</body>
</html>