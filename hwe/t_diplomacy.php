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
<?=WebUtil::printCSS('../e_lib/select2/select2.min.css')?>
<?=WebUtil::printCSS('../e_lib/select2/select2-bootstrap4.css')?>
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
<?=WebUtil::printJS('../e_lib/select2/select2.full.min.js')?>
<!--<?=WebUtil::printJS('../e_lib/tui.editor/tui-editor-Editor-all.min.js')?>-->
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/diplomacy.js')?>

</head>
<body>

<table id='newLetter' class='bg0' style='display:none;'>
    <thead>
        <tr><td colspan='2' class='newLetterHeader'>새 외교문서 작성</td>
    </thead>
    <tbody>
        <tr><th class='bg1'>이전 문서</th><td><select id='inputPrevNo'></select></td></tr>
        <tr><th class='bg1'>대상 국가</th><td><select id='inputDestNation'></select></td></tr>
        <tr><th class='bg1'>내용(공개)</th><td><textarea id='inputBrief' class='autosize'></textarea></td></tr>
        <tr><th class='bg1'>내용(비밀)</th><td><textarea id='inputDetail' class='autosize'></textarea></td></tr>
    </tbody>
    <tfoot>
        <tr class='letterActionPlate'><th class='bg1'>동작</th><td>
            <button type='button' id='btnSend'>전송</button>
        </td></tr>
    </tfoot>
</table>

<div id='letters'></div>

<!-- 설계미스. template와 shadowdom으로 변경 -->
<div id='letterTemplate' style='display:none;'>
    <table class='letterFrame bg0'>
        <thead>
            <tr><td colspan='2' class='letterHeader'>홍길동<span class='letterNationName'></span>국과의 외교 문서<span class='letterDate'>2099-12-31 23:59:59</span></td>
        </thead>
        <tbody>
            <tr><th class='bg1'>문서 번호</th><td><span class='letterNo'></span></td></tr>
            <tr><th class='bg1'>이전 문서</th><td><span class='letterPrevNo'></span></td></tr>
            <tr><th class='bg1'>상태</th><td><span class='letterStatus'></span></td></tr>
            <tr><th class='bg1'>내용(공개)</th><td><div class='letterBrief'></div></td></tr>
            <tr><th class='bg1'>내용(비밀)</th><td><div class='letterDetail'></div></td></tr>
        </tbody>
        <tfoot>
            <tr><th class='bg1'>서명인</th><td class='letterSignerPlate'>
                <div class='letterSrc'>
                    <div class="signerImg"><img class='generalIcon' width='64px' height='64px'></div>
                    <div class="signerNation">&nbsp;</div>
                    <div class="signerName">&nbsp;</div>
                </div><div class='letterDest'>
                    <div class="signerImg"><img class='generalIcon' width='64px' height='64px'></div>
                    <div class="signerNation">&nbsp;</div>
                    <div class="signerName">&nbsp;</div>
                </div>
            </td></tr>
            <tr class='letterActionPlate'><th class='bg1'>동작</th><td>
                <button type='button' class='btnAgree' style='display:none;'>승인</button>
                <button type='button' class='btnDisagree' style='display:none;'>거부</button>
                <button type='button' class='btnRollback' style='display:none;'>회수</button>
                <button type='button' class='btnRenew'>추가 문서 작성</button>
            </td></tr>
        </tfoot>
    </table>
</div>

<div style='width=1000px;' class='tb_layout bg0'>
    <?=backButton()?><br>
    <?=banner()?>
</div>
</body>
</html>