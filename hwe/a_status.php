<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("세력도", 2);
checkTurn();

$query = "select con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?=UniqueConst::$serverName?>: 세력도</title>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/base_map.js')?>
<?=WebUtil::printJS('js/map.js')?>
<script>
$(function(){

    reloadWorldMap({
        neutralView:true,
        showMe:true
    });

});
</script>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/normalize.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/map.css')?>

</head>

<body>
<table align=center width=1200 class='tb_layout bg0'>
    <tr><td>세 력 도<br><?=closeButton()?></td></tr>
</table>
<table align=center width=1200 height=520 class='tb_layout bg0'>
    <tr height=520>
        <td width=498 valign=top>
            <?=getGeneralPublicRecordRecent(34)?>
        </td>
        <td width=698>
            <?=getMapHtml()?>
        </td>
    </tr>
    <tr>
        <td colspan=2 valign=top>
            <?=getWorldHistoryRecent(34)?>
        </td>
    </tr>
</table>
<table align=center width=1200 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>

</html>

