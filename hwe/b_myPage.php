<?php
namespace sammo;

include "lib.php";
include "func.php";

$showDieImmediatelyBtn = false;
$availableDieImmediately = false;

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("내정보", 1);

$me = General::createGeneralObjFromDB($generalID);

$myset = $me->getVar('myset');
if ($myset > 0) {
    $submit = 'button';
} else {
    $submit = 'hidden';
}

$targetTime = addTurn($me->getVar('lastrefresh'), $gameStor->turnterm, 2);
if($gameStor->turntime <= $gameStor->opentime){
    //서버 가오픈시 할 수 있는 행동
    if($me->getNPCType() == 0){
        $showDieImmediatelyBtn = true;
        if($targetTime <= TimeUtil::now()){
            $availableDieImmediately = true;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 내정보</title>
<script>
var availableDieImmediately = <?=$availableDieImmediately?'true':'false'?>;
</script>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/myPage.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
</head>

<body>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td>내 정 보<br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td width=50%>
            <?php generalInfo($me); ?>
            <?php generalInfo2($me); ?>
        </td>
        <td width=50% valign=top style="padding-left:4ch;">
                토너먼트 【
                <input type=radio class='tnmt' name=tnmt value=0 <?=$me->getVar('tnmt')==0?"checked":""; ?>>수동참여
                <input type=radio class='tnmt' name=tnmt value=1 <?=$me->getVar('tnmt')==1?"checked":""; ?>>자동참여
                】<br>
               ∞<font color=orange>개막직전 남는자리가 있을경우 랜덤하게 참여합니다.</font><br><br>
                수비 【<select id='defence_train' name='defence_train'>
                <option value=90 <?=$me->getVar('defence_train')==90?"selected":""; ?>>☆(훈사90)</option>
                <option value=80 <?=$me->getVar('defence_train')==80?"selected":""; ?>>◎(훈사80)</option>
                <option value=60 <?=$me->getVar('defence_train')==60?"selected":""; ?>>○(훈사60)</option>
                <option value=40 <?=$me->getVar('defence_train')==40?"selected":""; ?>>△(훈사40)</option>
                <option value=999 <?=$me->getVar('defence_train')==999?"selected":""; ?>>×[훈련, 사기 -3]</option>
                </select>
                】<br><br>
                <input type=<?=$submit?> id='set_my_setting' name=btn style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px; value=설정저장><br>
                ∞<font color=orange>설정저장은 이달중 <?=$myset?>회 남았습니다.</font><br><br>
            <?php if(!($gameStor->autorun_user['limit_minutes']??false)): ?>
            휴 가 신 청<br>
            <button type="button" id='vacation' style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>휴가 신청</button><br><br>
            <?php endif; ?>
            <!--빙의 해제용 삭턴 조절<br>
            <a href="b_myPage.php?detachNPC=1"><button type="button" style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>빙의 해체 요청</button></a>-->

<?php if($showDieImmediatelyBtn): ?>
            가오픈 기간 내 장수 삭제 (<?=substr($targetTime, 0, 19)?> 부터)<br>
            <a href="c_die_immediately.php" id='die_immediately'><button type="button" style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>장수 삭제</button></a><br><br>
<?php endif; ?>

            개인용 CSS<br>
            <textarea id='custom_css' style='color:white;background-color:black;width:420px;height:150px;'></textarea>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>개인 기록</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 기록</font></td>
    </tr>
    <tr>
        <td valign=top>
            <div id='generalActionPlate'>
            <?=formatHistoryToHTML(getGeneralActionLogRecent($generalID, 24), 'generalAction')?>
            </div>
            <button type="button" class="load_old_log btn btn-secondary btn-block" data-log_type="generalAction">이전 로그 불러오기</button>
        </td>
        <td valign=top>
            <div id='battleDetailPlate'>
            <?=formatHistoryToHTML(getBattleDetailLogRecent($generalID, 24), 'battleDetail')?>
            </div>
            <button type="button" class="load_old_log btn btn-secondary btn-block" data-log_type="battleDetail">이전 로그 불러오기</button>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>장수 열전</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 결과</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=formatHistoryToHTML(getGeneralHistoryLogAll($generalID))?>
        </td>
        <td valign=top>
            <div id='battleResultPlate'>
            <?=formatHistoryToHTML(getBattleResultRecent($generalID, 24), 'battleResult')?>
            </div>
            <button type="button" class="load_old_log btn btn-secondary btn-block" data-log_type="battleResult">이전 로그 불러오기</button>
        </td>
    </tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>

