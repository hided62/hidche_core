<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

list($npcmode, $maxgeneral) = $gameStor->getValuesAsArray(['npcmode', 'maxgeneral']);

if(!$npcmode) {
    header('location:..');
    die();
}

$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');

$nationList = $db->query('SELECT nation,`name`,color,scout,scoutmsg FROM nation ORDER BY rand()');
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: NPC빙의</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="style='width:1024" />
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/select_npc.css')?>

<script>
var specialInfo = 
<?php
$specialAll = [];
foreach (SpecialityConst::DOMESTIC as $id=>$values) {
   $name = $values[0];
   $text = getSpecialInfo($id);
   $specialAll[$name] = $text;
}
foreach (SpecialityConst::WAR as $id=>$values) {
    $name = $values[0];
    $text = getSpecialInfo($id);
    $specialAll[$name] = $text;
}
$specialAll['-'] = '없음';
echo Json::encode($specialAll);
?>
;
var characterInfo = 
<?php
$characterAll = [];
foreach(getCharacterList() as $id=>[$name, $info]){
    $characterAll[$name] = $info;
}
echo Json::encode($characterAll);
?>
;
</script>

<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/select_npc.js')?>

</head>


<?php 
if ($gencount>= $maxgeneral) {
?>

<body>
<script>
alert('더 이상 등록할 수 없습니다.');
history.go(-1);
</script>
</body>
</html>
<?php
    die();
}
?>
<body>
<div class="container">
<div class="bg0 with_border legacy_layout">장 수 선 택<br><?=backButton()?></div>
<table style="width:100%;" class="bg0 with_border">
    <tr><td><?=info(0)?></td></tr>
</table>

<?=getInvitationList($nationList)?>

<div class="bg0">
<div class="bg1 with_border legacy_layout font1" style="text-align:center;font-weight:bold;">장수 빙의</div>
<div class="with_border legacy_layout" style="text-align:center;">
<small id="valid_until">(<span id="valid_until_text"></span>까지 유효)</small><small id="outdate_token">- 만료 -</small><br>
<form class="card_holder">
</form>
</div>
<div class="with_border legacy_layout" style="text-align:center">
    <button id="btn_pick_more" disabled="disabled" class="with_skin with_border">다른 장수 보기</button><button id="btn_load_general_list" class="with_skin with_border" style='margin-left:2ch;'>장수 목록 보기</button><br>
</div>

<table style='width:970px;table-layout: fixed;' class="tb_layout bg0" id='tb_general_list'>
<thead>
<tr class='bg1'>
        <th style='width:64px;'>얼 굴</td>
        <th style='width:140px;'>이 름</td>
        <th style='width:40px;'>연령</td>
        <th style='width:40px;'>성격</td>
        <th style='width:80px;'>특기</td>
        <th style='width:45px;'>레 벨</td>
        <th style='width:140px;'>국 가</td>
        <th style='width:50px;'>명 성</td>
        <th style='width:50px;'>계 급</td>
        <th style='width:75px;'>관 직</td>
        <th style='width:60px;'>종능</td>
        <th style='width:45px;'>통솔</td>
        <th style='width:45px;'>무력</td>
        <th style='width:45px;'>지력</td>
        <th style='width:45px;'>삭턴</td>
    </tr>
</thead>
<tbody id='general_list'>
</tbody>
</table>
<div class="with_border legacy_layout"><?=backButton()?></div>
<div class="with_border legacy_layout"><?=banner()?></div>
</div>
</div>
</body>
</html>
