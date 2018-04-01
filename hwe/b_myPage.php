<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh("내정보", 1);

$query = "select myset from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['myset'] > 0) {
    $submit = 'submit';
} else {
    $submit = 'hidden';
}

if($btn == "설정저장" && $me['myset'] > 0) {
    if($me['myset'] > 1) {
        $submit = 'submit';
    } else {
        $submit = 'hidden';
    }

    $query = "update general set myset=myset-1,map='$map',mode='$mode',tnmt='$tnmt' where owner='{$_SESSION['userID']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

$query = "select no,map,mode,tnmt,myset from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

?>
<!DOCTYPE html>
<html>
<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title>내정보</title>
<link rel=stylesheet href="css/common.css" type=text/css>
<script type="text/javascript">
function go(type) {
    if(type == 0){ 
        //location.replace('c_vacation.php');
        console.log('c_vacation.php');//TODO:debug all and replace
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
            <?php myInfo($connect); ?>
            <?php myInfo2($connect); ?>
        </td>
        <td width=50% valign=top>
            <form name=form1 action=b_myPage.php method=post>
                &nbsp;&nbsp;&nbsp;&nbsp;지도수준 【
                <input type=radio name=map value=0 <?=$me['map']==0?"checked":""; ?>>상세
                <input type=radio name=map value=1 <?=$me['map']==1?"checked":""; ?>>간단
                <input type=radio name=map value=2 <?=$me['map']==2?"checked":""; ?>>생략
                】<br><br>
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
                &nbsp;&nbsp;&nbsp;&nbsp;<input type=<?=$submit;?> name=btn style=background-color:<?=$_basecolor2;?>;color:white;width:160;height:30;font-size:13px; value=설정저장><br>
                &nbsp;&nbsp;&nbsp;&nbsp;∞<font color=orange>설정저장은 이달중 <?=$me['myset'];?>회 남았습니다.</font><br><br>
            </form>
            &nbsp;&nbsp;&nbsp;&nbsp;휴 가 신 청<br>
            &nbsp;&nbsp;&nbsp;&nbsp;<input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:160;height:30;font-size:13px; value=휴가신청 onclick='go(0)'>
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

