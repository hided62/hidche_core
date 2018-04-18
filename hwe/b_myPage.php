<?php
namespace sammo;

include "lib.php";
include "func.php";

$btn = Util::getReq('btn');
$mode = Util::getReq('mode', 'int', 2);
$tnmt = Util::getReq('tnmt', 'int', 1);

extractMissingPostToGlobals();

if($mode < 0 || $mode > 1){
    $mode = 1;
}

if($tnmt < 0 || $tnmt > 1){
    $tnmt = 1;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$connect=$db->get();

increaseRefresh("내정보", 1);

$query = "select myset from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

if ($me['myset'] > 0) {
    $submit = 'submit';
} else {
    $submit = 'hidden';
}

if ($btn == "설정저장" && $me['myset'] > 0) {
    if ($me['myset'] > 1) {
        $submit = 'submit';
    } else {
        $submit = 'hidden';
    }

    $db->update('general', [
        'myset'=>$db->sqleval('myset-1'),
        'mode'=>$mode,
        'tnmt'=>$tnmt
    ], 'owner=%i', $userID);
}

$query = "select no,mode,tnmt,myset from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$me = MYDB_fetch_array($result);

?>
<!DOCTYPE html>
<html>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>내정보</title>
<link href="../d_shared/common.css" rel="stylesheet">
<link rel=stylesheet href="css/common.css">
<script type="text/javascript">
function go(type) {
    if(type == 0){ 
        location.replace('c_vacation.php');
    }
}
</script>

</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>내 정 보<br><?=backButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=50%>
            <?php myInfo(); ?>
            <?php myInfo2(); ?>
        </td>
        <td width=50% valign=top>
            <form name=form1 action=b_myPage.php method=post>
                &nbsp;&nbsp;&nbsp;&nbsp;토너먼트 【
                <input type=radio name=tnmt value=0 <?=$me['tnmt']==0?"checked":""; ?>>수동참여
                <input type=radio name=tnmt value=1 <?=$me['tnmt']==1?"checked":""; ?>>자동참여
                】<br>
                &nbsp;&nbsp;&nbsp;&nbsp;∞<font color=orange>개막직전 남는자리가 있을경우 랜덤하게 참여합니다.</font><br><br>
                &nbsp;&nbsp;&nbsp;&nbsp;수비 【
                <input type=radio name=mode  value=2 <?=$me['mode']==2?"checked":""; ?>>◎(훈사80)
                <input type=radio name=mode  value=1 <?=$me['mode']==1?"checked":""; ?>>○(훈사60)
                <input type=radio name=mode  value=0 <?=$me['mode']==0?"checked":""; ?>>×
                】<br><br>
                &nbsp;&nbsp;&nbsp;&nbsp;<input type=<?=$submit?> name=btn style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px; value=설정저장><br>
                &nbsp;&nbsp;&nbsp;&nbsp;∞<font color=orange>설정저장은 이달중 <?=$me['myset']?>회 남았습니다.</font><br><br>
            </form>
            &nbsp;&nbsp;&nbsp;&nbsp;휴 가 신 청<br>
            &nbsp;&nbsp;&nbsp;&nbsp;<input type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;height:30px;font-size:13px; value=휴가신청 onclick='go(0)'>
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
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?=backButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

