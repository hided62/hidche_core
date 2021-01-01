<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$admin =  $gameStor->getValues(['npcmode', 'maxgeneral','show_img_level']);

if($admin['npcmode']!=2) {
    header('location:..');
    die();
}

$member = RootDB::db()->queryFirstRow("SELECT no,name,picture,imgsvr,grade from member where no= %i", $userID);

$generalID = $db->queryFirstField('SELECT no FROM general WHERE owner = %i', $userID);
$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');

$nationList = $db->query('SELECT nation,`name`,color,scout FROM nation');
shuffle($nationList);
$nationList = Util::convertArrayToDict($nationList, 'nation');
//NOTE: join 안할것임
$scoutMsgs = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'scout_msg');
foreach($scoutMsgs as $nationID=>$scoutMsg){
    $nationList[$nationID]['scoutmsg'] = $scoutMsg;
}

$characterAll = [];//선택용
$charInfoText = [];//구버전 생성용 ㅜ
foreach(getCharacterList(false) as $id=>[$name, $info]){
    $characterAll[$name] = ['name'=>$name,'info'=>$info];
    $charInfoText[$id] = $info;
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 장수 선택</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024'" />
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/select_general_from_pool.css')?>

<script>
var hasGeneralID = <?=$generalID===null?'false':'true'?>;
var defaultStatTotal = <?=GameConst::$defaultStatTotal?>;
var defaultStatMin = <?=GameConst::$defaultStatMin?>;
var defaultStatMax = <?=GameConst::$defaultStatMax?>;
var cards = {};
var currentGeneralInfo = null;

var characterInfo = <?=Json::encode($characterAll)?>;
var charInfoText = <?=Json::encode($charInfoText)?>;
var validCustomOption = <?=Json::encode(GameConst::$generalPoolAllowOption)?>;
</script>

<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/join.js')?>
<?=WebUtil::printJS('js/select_general_from_pool.js')?>

</head>


<?php 
if ($gencount>= $admin['maxgeneral']) {
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
<div class="bg1 with_border legacy_layout font1" style="text-align:center;font-weight:bold;">장수 선택</div>
<div class="with_border legacy_layout" style="text-align:center;">
<small id="valid_until">(<span id="valid_until_text"></span>까지 유효)</small><small id="outdate_token">- 만료 -</small><br>
<form class="card_holder">
</form>
</div>
</div>

<div class="bg0" id="create_plate">
<div class="bg1 with_border legacy_layout font1" style="text-align:center;font-weight:bold;margin-top:10px;">장수 생성</div>
<div class="with_border legacy_layout" style="display:flex">
<div style='flex:1;' id='left_pad'>
장수를<br>선택해주세요!
</div><div style='flex:4;'>
<form id='custom_form'>
    <table class='tb_layout' style='width:100%;text-align:left;'>
<?php
if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "") {
    $imageTemp = GetImageURL($member['imgsvr']);
    echo "
        <tr class='custom_picture'>
            <td align=right id=bg1>전콘 사용 여부</td>
            <td width=64 height=64>
                <img width='64' height='64' src='{$imageTemp}/{$member['picture']}' border='0'>
            </td>
            <td>
                <input type=checkbox id='use_own_picture' name=pic>사용
            </td>
        </tr>
    ";
}
?>
        <tr class='custom_personality'>
            <td align=right id=bg1>성격</td>
            <td colspan=2 style='text-align:left;'>
                <select id="selChar" name=character size=1 maxlength=15 style=color:white;background-color:black;>
                    <option selected value='Random'>????</option>
<?php foreach(GameConst::$availablePersonality as $personalityID): ?>
<?php $personalityName = buildPersonalityClass($personalityID)->getName(); ?>
                    <option value='<?=$personalityID?>'><?=$personalityName?></option>
<?php endforeach; ?>
                </select> <span id="charInfoText"></span>
            </td>
        </tr>
        <tr class='custom_stat'>
            <td align=right id=bg1>통솔</td>
            <td colspan=2><input type="number" name="leadership" id="leadership" value="50"></td>
        </tr>
        <tr class='custom_stat'>
            <td align=right id=bg1>무력</td>
            <td colspan=2><input type="number" name="strength" id="strength" value="50"></td>
        </tr>
        <tr class='custom_stat'>
            <td align=right id=bg1>지력</td>
            <td colspan=2><input type="number" name="intel" id="intel" value="50"></td>
        </tr>
        <tr class='custom_stat'>
            <td align=right id=bg1>능력치 조정</td>
            <td colspan=2>
                <input type=button value=랜덤형 onclick=abilityRand()>
                <input type=button value=통솔무력형 onclick=abilityLeadpow()>
                <input type=button value=통솔지력형 onclick=abilityLeadint()>
                <input type=button value=무력지력형 onclick=abilityPowint()>
            </td>
        </tr>
        <tr class='custom_stat'>
            <td align=center colspan=3>
                <font color=orange>모든 능력치는 ( <?=GameConst::$defaultStatMin?> <= 능력치 <= <?=GameConst::$defaultStatMax?> ) 사이로 잡으셔야 합니다.<br>
                그 외의 능력치는 가입되지 않습니다.</font>
            </td>
        </tr>
        <tr>
            <td align=center colspan=3>
                <span class='custom_stat'>능력치의 총합은 <?=GameConst::$defaultStatTotal?>입니다. 가입후 0~10의 능력치 보너스를 받게 됩니다.<br></span>
                임의의 도시에서 재야로 시작하며 건국과 임관은 게임 내에서 실행합니다.
            </td>
        </tr>
        <tr>
            <td align=right style='width:200px;'><input type=submit id='build_general' name=join value=장수생성></td>
            <td colspan=2><input type=reset name=reset value=다시입력></td>
        </tr>
    </table>
</form>
</div>
</table>
</div>
</div>
<div class="bg0">
<div class="with_border legacy_layout"><?=backButton()?></div>
<div class="with_border legacy_layout"><?=banner()?></div>
</div>
</div>
</body>
</html>
