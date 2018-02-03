<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "내정보", 1);

$query = "select myset from general where owner='{$_SESSION['noMember']}'";
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

    $query = "update general set myset=myset-1,map='$map',mode='$mode',skin='$skin',tnmt='$tnmt' where owner='{$_SESSION['noMember']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

$query = "select no,skin,map,mode,tnmt,myset from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}
?>
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
<?php require('analytics.php'); ?>
</head>

<body>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td>내 정 보<br><?php backButton(); ?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=50%>
            <?php myInfo($connect); ?>
            <?php myInfo2($connect); ?>
        </td>
        <td width=50% valign=top>
            <form name=form1 action=b_myPage.php method=post>
                &nbsp;&nbsp;&nbsp;&nbsp;스킨 【
                <select name=skin>
                    <option value=0 <?=$me['skin']==0?"selected ":" ";?>style=color:ffffff;background-color:000000;font-size:13px;>=== 간 단 ===</option>
                    <option value=1 <?=$me['skin']==1?"selected ":" ";?>style=color:225500;background-color:330000;font-size:13px;>=== 표 준 ===</option>
                    <option value=2 <?=$me['skin']==2?"selected ":" ";?>style=color:ffffff;background-color:000000;font-size:13px;>=== 계 절 ===</option>
                    <option value=3 <?=$me['skin']==3?"selected ":" ";?>style=color:ff69b4;background-color:330033;font-size:13px;>=== 봄&nbsp;&nbsp;&nbsp;&nbsp; ===</option>
                    <option value=4 <?=$me['skin']==4?"selected ":" ";?>style=color:225500;background-color:001717;font-size:13px;>=== 여 름 ===</option>
                    <option value=5 <?=$me['skin']==5?"selected ":" ";?>style=color:b8860b;background-color:220000;font-size:13px;>=== 가 을 ===</option>
                    <option value=6 <?=$me['skin']==6?"selected ":" ";?>style=color:666666;background-color:222222;font-size:13px;>=== 겨 울 ===</option>
                    <option value=7 <?=$me['skin']==7?"selected ":" ";?>style=color:660000;background-color:220000;font-size:13px;>=== 주 작 ===</option>
                    <option value=8 <?=$me['skin']==8?"selected ":" ";?>style=color:006600;background-color:002200;font-size:13px;>=== 초 태 ===</option>
                    <option value=9 <?=$me['skin']==9?"selected ":" ";?>style=color:000066;background-color:000022;font-size:13px;>=== 청 룡 ===</option>
                    <option value=10 <?=$me['skin']==10?"selected ":" ";?>style=color:006666;background-color:002222;font-size:13px;>=== 녹 기 ===</option>
                    <option value=11 <?=$me['skin']==11?"selected ":" ";?>style=color:660066;background-color:220022;font-size:13px;>=== 남 황 ===</option>
                    <option value=12 <?=$me['skin']==12?"selected ":" ";?>style=color:666600;background-color:222200;font-size:13px;>=== 황 봉 ===</option>
                    <option value=13 <?=$me['skin']==13?"selected ":" ";?>style=color:666666;background-color:222222;font-size:13px;>=== 현 무 ===</option>
                    <option value=14 <?=$me['skin']==14?"selected ":" ";?>style=color:ffffff;background-color:000000;font-size:13px;>=== 랜 덤 ===</option>
                    <option value=15 <?=$me['skin']==15?"selected ":" ";?>style=color:pink;background-color:000000;font-size:13px;>==소녀시대===</option>
                    <option value=16 <?=$me['skin']==16?"selected ":" ";?>style=color:pink;background-color:000000;font-size:13px;>=== 태 연 ===</option>
                    <option value=17 <?=$me['skin']==17?"selected ":" ";?>style=color:pink;background-color:000000;font-size:13px;>=== 소 원 ===</option>
                </select> 】<br><br>
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
            <?php MyLog($me['no'], 24, $me['skin']); ?>
        </td>
        <td valign=top>
            <?php MyBatLog($me['no'], 24, $me['skin']); ?>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><font color=skyblue size=3>장수 열전</font></td>
        <td align=center id=bg1><font color=orange size=3>전투 결과</font></td>
    </tr>
    <tr>
        <td valign=top>
            <?php MyHistory($connect, $me['no'], $me['skin']); ?>
        </td>
        <td valign=top>
            <?php MyBatRes($me['no'], 24, $me['skin']); ?>
        </td>
    </tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13px;word-break:break-all; id=bg0>
    <tr><td><?php backButton(); ?></td></tr>
    <tr><td><?php banner(); ?></td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>

