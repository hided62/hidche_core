<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();

if (!$userID) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

//회원 테이블에서 정보확인
$member = RootDB::db()->queryFirstRow("select no,name,picture,imgsvr,grade from member where no= %i", $userID);

if (!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$admin = $gameStor->getValues(['block_general_create','show_img_level','maxgeneral']);
if($admin['block_general_create']){
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 장수생성</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<script>
var defaultStatTotal = <?=GameConst::$defaultStatTotal?>;
var defaultStatMin = <?=GameConst::$defaultStatMin?>;
var defaultStatMax = <?=GameConst::$defaultStatMax?>;

var charInfoText = <?php

$charInfoText = [];
foreach(GameConst::$availablePersonality as $personalityID){
    $personalityInfo = buildPersonalityClass($personalityID)->getInfo();
    $charInfoText[$personalityID] = $personalityInfo;
}
echo Json::encode((object)$charInfoText);
?>;
</script>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('dist_js/common.js')?>
<?=WebUtil::printJS('js/join.js')?>

</head>
<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr><td>장 수 생 성<br><?=backButton()?></td></tr>
    </table>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr><td align=center><?=info(0)?></td></tr>
    </table>
<?php

$gencount = $db->queryFirstField('SELECT count(no) FROM general WHERE npc<2');

if ($gencount >= $admin['maxgeneral']) {
    echo "<script>alert('더 이상 등록할 수 없습니다.');</script>";
    echo "<script>history.go(-1);</script>";
    exit();
}

$nationList = $db->query('SELECT nation,`name`,color,scout FROM nation');
shuffle($nationList);
$nationList = Util::convertArrayToDict($nationList, 'nation');
//NOTE: join 안할것임
$scoutMsgs = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'scout_msg');
foreach($scoutMsgs as $nationID=>$scoutMsg){
    $nationList[$nationID]['scoutmsg'] = $scoutMsg;
}

echo getInvitationList($nationList);
?>

<form id='join_form' name=form1 method=post action=join_post.php>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td colspan=3 align=center id=bg1>장수 생성</td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>장수명</td>
            <td colspan=2>
                <input id="generalName" type=text name=name maxlength=18 size=18 style="color:white;background-color:black;" value="<?=$member['name']?>">(전각 9글자, 반각 18글자 이내)
            </td>
        </tr>
<?php
if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "") {
    $imageTemp = GetImageURL($member['imgsvr']);
    echo "
        <tr>
            <td align=right id=bg1>전콘 사용 여부</td>
            <td width=64 height=64>
                <img width='64' height='64' src='{$imageTemp}/{$member['picture']}' border='0'>
            </td>
            <td>
                <input type=checkbox name=pic value=1 checked>사용
            </td>
        </tr>
    ";
}
?>
        <tr>
            <td align=center colspan=3>
                계정관리에서 자신만을 표현할 수 있는 아이콘을 업로드 해보세요!
            </td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>성격</td>
            <td colspan=2>
                <select id="selChar" name=character size=1 maxlength=15 style=color:white;background-color:black;>
                    <option selected value='Random'>????</option>
<?php foreach(GameConst::$availablePersonality as $personalityID): ?>
<?php $personalityName = buildPersonalityClass($personalityID)->getName(); ?>
                    <option value='<?=$personalityID?>'><?=$personalityName?></option>
<?php endforeach; ?>
                </select> <span id="charInfoText"></span>
            </td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>통솔</td>
            <td colspan=2><input type="number" name="leadership" id="leadership" value="50"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>무력</td>
            <td colspan=2><input type="number" name="strength" id="strength" value="50"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>지력</td>
            <td colspan=2><input type="number" name="intel" id="intel" value="50"></td>
        </tr>
        <tr>
            <td width=498 align=right id=bg1>능력치 조정</td>
            <td colspan=2>
                <input type=button value=랜덤형 onclick=abilityRand()>
                <input type=button value=통솔무력형 onclick=abilityLeadpow()>
                <input type=button value=통솔지력형 onclick=abilityLeadint()>
                <input type=button value=무력지력형 onclick=abilityPowint()>
            </td>
        </tr>
        <tr>
            <td align=center colspan=3>
                <font color=orange>모든 능력치는 ( <?=GameConst::$defaultStatMin?> <= 능력치 <= <?=GameConst::$defaultStatMax?> ) 사이로 잡으셔야 합니다.<br>
                그 외의 능력치는 가입되지 않습니다.</font>
            </td>
        </tr>
        <tr>
            <td align=center colspan=3>
                능력치의 총합은 <?=GameConst::$defaultStatTotal?>입니다. 가입후 0~10의 능력치 보너스를 받게 됩니다.<br>
                임의의 도시에서 재야로 시작하며 건국과 임관은 게임 내에서 실행합니다.
            </td>
        </tr>
        <tr>
            <td width=498 align=right><input type=submit name=join value=장수생성></td>
            <td colspan=2><input type=reset name=reset value=다시입력></td>
        </tr>
    </table>
</form>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>
