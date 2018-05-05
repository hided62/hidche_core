<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();
$db = DB::db();

list($npcmode, $maxgeneral) = $db->queryFirstList('SELECT npcmode,maxgeneral FROM game LIMIT 1');

if(!$npcmode) {
    header('location:..');
    die();
}

$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');

$nations = $db->queryAllLists('SELECT `name`, scoutmsg, color FROM nation');
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: NPC빙의</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel='stylesheet' href='css/normalize.css?180505_2' type='text/css'>
<link rel='stylesheet' href='../d_shared/common.css?180505_2' type='text/css'>
<link rel='stylesheet' href='../css/config.css?180505_2' type='text/css'>
<link rel='stylesheet' href='css/common.css?180505_2' type='text/css'>
<link rel='stylesheet' href='css/select_npc.css?180505_2' type='text/css'>
<script type="text/javascript" src="../d_shared/common_path.js"></script>
<script type="text/javascript" src="../e_lib/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="../js/common.js?180505_2"></script>
<script src="js/select_npc.js?180505_2"></script>

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


<table style="width:100%;" class="bg0 with_border">
<thead>
<tr><th colspan=2 class="bg1">임관 권유 메세지</th></tr>
</thead>
<tbody>
<?php foreach($nations as list($name, $scoutmsg, $color)): ?>

<tr>
    <td style='width:98px;color:<?=newColor($color)?>;background-color:<?=$color?>'><?=$name?></td>
    <td style='color:<?=newColor($color)?>;background-color:<?=$color?>'><?=$scoutmsg?:'-'?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="bg0">
<div class="bg1 with_border legacy_layout font1" style="text-align:center;font-weight:bold;">장수 빙의</div>
<div class="with_border legacy_layout" style="text-align:center;">
<small id="valid_until">(<span id="valid_until_text"></span> 까지 유효)</small><small id="outdate_token">- 만료 -</small><br>
<form class="card_holder">
</form>
</div>
<div class="with_border legacy_layout" style="text-align:center">
    <button id="btn_pick_more" disabled="disabled" class="with_skin with_border">다른 장수 보기</button><br>
</div>
<div class="with_border legacy_layout"><?=backButton()?></div>
<div class="with_border legacy_layout"><?=banner()?></div>
</div>
</div>
</body>
</html>
