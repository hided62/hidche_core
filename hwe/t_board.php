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
<!--<?=WebUtil::printCSS('../e_lib/tui.editor/tui-editor.min.css')?>-->
<!--<?=WebUtil::printCSS('../e_lib/tui.editor/tui-editor-contents.min.css')?>-->
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/board.css')?>
<script>
var isSecretBoard = <?=($isSecretBoard?'true':'false')?>; //
</script>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<!--<?=WebUtil::printJS('../e_lib/tui.editor/tui-editor-Editor-all.min.js')?>-->
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/board.js')?>

</head>
<body>

<table id='newArticle' class='bg0'>
<thead>
        <tr><td colspan='2' class='newArticleHeader bg2'>새 게시물 작성</td>
    </thead>
<tbody>
<tr><th class='bg1' style='width:66px;'><span class='articleTitle'>제목</span></th><td><input class='titleInput' type='text' maxlength='250' placeholder='제목'></input></td></tr>
<tr><th class='bg1'>내용</th><td class='boardArticle'>
<textarea class="contentInput autosize" placeholder='내용'></textarea>
</td></tr>
</tbody>
<tfoot>
<tr><td colspan="2">
<button type='button' id='submitArticle'>등록</button>
</td></tr>
</tfoot>
</table>

<div id="board">
</div>

<!-- 설계미스. template와 shadowdom으로 변경 -->
<div id='articleTemplate' style='display:none;'>
<table class='articleFrame bg0'>
<thead>
<tr class='bg1'>
<th class='authorName'></th><th class='articleTitle'></th><th class='date'></th>
</tr>
</thead>
<tbody>
<tr>
<td><img class='authorIcon generalIcon' width='64' height='64'></td>
<td class='text' colspan='2'></td> 
</tr>
</tbody>
<tbody class='commentList'>
</tbody>
<tfoot>
<tr><td class='bg2'>댓글 달기</td><td><input class='commentText' type='text' maxlength='250'></td><td><button type='button' class='submitComment'>등록</button></td></tr>
</tfoot>
</table>
</div>

<template id='commentTemplate' style='display:none;'>
<tr class='comment'>
<th class='author'></th>
<td class='text'></td>
<td class='date'></td>
</tr>
</template>

<div style='width=1000px;' class='tb_layout bg0'>
    <?=backButton()?><br>
    <?=banner()?>
</div>
</body>
</html>