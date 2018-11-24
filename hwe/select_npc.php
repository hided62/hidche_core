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
<meta name="viewport" content="width=1024" />
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/select_npc.css')?>

<script>
var specialInfo = 
<?php
$specialAll = [];
foreach (GameConst::$availableSpecialDomestic as $id) {
    $domesticClass = getGeneralSpecialDomesticClass($id);
    $name = $domesticClass::$name;
    $info = $domesticClass::$info;
    $specialAll[$name] = $info;
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
</script>

<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/select_npc.js')?>

</head>


<?php 
if ($gencount >= $maxgeneral) {
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
    <button type="button" id="btn_pick_more" disabled="disabled" class="with_skin with_border">다른 장수 보기</button><br>
</div>
<div class="with_border legacy_layout"><?=backButton()?></div>
<div class="with_border legacy_layout"><?=banner()?></div>
</div>
</div>
</body>
</html>
