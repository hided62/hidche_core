<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getPost('btn');
$defence_train = Util::getPost('defence_train', 'int', 2);
$tnmt = Util::getPost('tnmt', 'int', 1);
//$detachNPC = Util::getPost('detachNPC', 'bool');
$detachNPC = false;

$showDieImmediatelyBtn = false;
$availableDieImmediately = false;

if ($defence_train <= 60) {
    $defence_train = 60;
}
else if($defence_train <= 80){
    $defence_train = 80;
}
else{
    $defence_train = 999;
}

if($tnmt < 0 || $tnmt > 1){
    $tnmt = 1;
}

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
    $submit = 'submit';
} else {
    $submit = 'hidden';
}

if (($btn == "설정저장" || $detachNPC) && $myset > 0) {
    if ($myset > 1) {
        $submit = 'submit';
    } else {
        $submit = 'hidden';
    }

    if($defence_train != $me->getVar('defence_train')){
        if($defence_train == 999){
            $me->increaseVar('myset', -1);
            $me->setVar('defence_train', $defence_train);
            $me->increaseVar('train', -3);
            $me->increaseVar('atmos', -3);
        }
        else{
            $me->increaseVar('myset', -1);
            $me->setVar('defence_train', $defence_train);
        }
        $myset -= 1;
    }
    
    if($me->getVar('tnmt') != $tnmt){
        $me->setVar('tnmt', $tnmt);
    }

    if($me->getNPCType() == 1 && $detachNPC){
        $turnterm = $gameStor->turnterm;

        if($turnterm < 10){
            $targetKillTurn = 30 / $turnterm;
        }
        else{
            $targetKillTurn = 60 / $turnterm;
        }
        $me->setVar('killturn', $targetKillTurn);
    }
}
$me->applyDB($db);

if($gameStor->turntime <= $gameStor->opentime){
    //서버 가오픈시 할 수 있는 행동

    if($me->getNPCType() == 0){
        $showDieImmediatelyBtn = true;
        if(addTurn($me->getVar('lastrefresh'), $gameStor->turnterm, 2) <= TimeUtil::now()){
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
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/myPage.js')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<script>
var availableDieImmediately = <?=$availableDieImmediately?'true':'false'?>;
jQuery(function($){

$('#die_immediately').click(function(){
    if(!availableDieImmediately){
        alert('삭제를 위해서는 생성 후 2턴 가량의 시간이 필요합니다.');
        location.reload();
        return false;
    }
    return confirm('정말로 삭제하시겠습니까?');
});

});
</script>
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
            <form name=form1 action=b_myPage.php method=post>
                토너먼트 【
                <input type=radio name=tnmt value=0 <?=$me->getVar('tnmt')==0?"checked":""; ?>>수동참여
                <input type=radio name=tnmt value=1 <?=$me->getVar('tnmt')==1?"checked":""; ?>>자동참여
                】<br>
               ∞<font color=orange>개막직전 남는자리가 있을경우 랜덤하게 참여합니다.</font><br><br>
                수비 【
                <input type=radio name=defence_train  value=80 <?=$me->getVar('defence_train')==80?"checked":""; ?>>◎(훈사80)
                <input type=radio name=defence_train  value=60 <?=$me->getVar('defence_train')==60?"checked":""; ?>>○(훈사60)
                <input type=radio name=defence_train  value=999 <?=$me->getVar('defence_train')==999?"checked":""; ?>>×
                】<br><br>
                <input type=<?=$submit?> name=btn style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px; value=설정저장><br>
                ∞<font color=orange>설정저장은 이달중 <?=$myset?>회 남았습니다.</font><br><br>
            </form>
            <?php if(!($gameStor->autorun_user['limit_minutes']??false)): ?>
            휴 가 신 청<br>
            <a href="c_vacation.php"><button type="button" style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>휴가 신청</button></a><br><br>
            <?php endif; ?>
            <!--빙의 해제용 삭턴 조절<br>
            <a href="b_myPage.php?detachNPC=1"><button type="button" style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>빙의 해체 요청</button></a>-->

<?php if($showDieImmediatelyBtn): ?>
            가오픈 기간 내 장수 삭제<br>
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

