<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$mode = Util::getReq('mode', 'int', 2);
$tnmt = Util::getReq('tnmt', 'int', 1);
//$detachNPC = Util::getReq('detachNPC', 'bool');
$detachNPC = false;

extractMissingPostToGlobals();

if($mode < 0 || $mode > 2){
    $mode = 2;
}

if($tnmt < 0 || $tnmt > 1){
    $tnmt = 1;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();

increaseRefresh("내정보", 1);

$query = "select no,npc,mode,tnmt,myset from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);


if ($me['myset'] > 0) {
    $submit = 'submit';
} else {
    $submit = 'hidden';
}

if (($btn == "설정저장" || $detachNPC) && $me['myset'] > 0) {
    if ($me['myset'] > 1) {
        $submit = 'submit';
    } else {
        $submit = 'hidden';
    }

    $me['myset'] -= 1;

    $db->update('general', [
        'myset'=>$db->sqleval('myset-1'),
        'mode'=>$mode,
        'tnmt'=>$tnmt
    ], 'owner=%i', $userID);

    if($me['npc'] == 1 && $detachNPC){
        $turnterm = $gameStor->turnterm;

        if($turnterm < 10){
            $targetKillTurn = 30 / $turnterm;
        }
        else{
            $targetKillTurn = 60 / $turnterm;
        }
        $db->update('general', [
            'killturn'=>$targetKillTurn
        ], 'owner=%i AND npc=1', $userID);

        $me['killturn']=$targetKillTurn;
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
            <?php myInfo(); ?>
            <?php myInfo2(); ?>
        </td>
        <td width=50% valign=top style="padding-left:4ch;">
            <form name=form1 action=b_myPage.php method=post>
                토너먼트 【
                <input type=radio name=tnmt value=0 <?=$me['tnmt']==0?"checked":""; ?>>수동참여
                <input type=radio name=tnmt value=1 <?=$me['tnmt']==1?"checked":""; ?>>자동참여
                】<br>
               ∞<font color=orange>개막직전 남는자리가 있을경우 랜덤하게 참여합니다.</font><br><br>
                수비 【
                <input type=radio name=mode  value=2 <?=$me['mode']==2?"checked":""; ?>>◎(훈사80)
                <input type=radio name=mode  value=1 <?=$me['mode']==1?"checked":""; ?>>○(훈사60)
                <input type=radio name=mode  value=0 <?=$me['mode']==0?"checked":""; ?>>×
                】<br><br>
                <input type=<?=$submit?> name=btn style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px; value=설정저장><br>
                ∞<font color=orange>설정저장은 이달중 <?=$me['myset']?>회 남았습니다.</font><br><br>
            </form>
            휴 가 신 청<br>
            <a href="c_vacation.php"><button type="button" style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>휴가 신청</button></a><br><br>
            <!--빙의 해제용 삭턴 조절<br>
            <a href="b_myPage.php?detachNPC=1"><button type="button" style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px;>빙의 해체 요청</button></a>-->
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>개인 기록</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 기록</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=getGenLogRecent($me['no'], 24)?>
        </td>
        <td valign=top>
            <?=getBatLogRecent($me['no'], 24)?>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>장수 열전</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 결과</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?=getGeneralHistoryAll($me['no'])?>
        </td>
        <td valign=top>
            <?=getBatResRecent($me['no'], 24)?>
        </td>
    </tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>

