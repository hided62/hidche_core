<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "메인", 2);
checkTurn($connect);

if(!isset($_SESSION['p_id'])){
    echo "<script>location.replace('start.php');</script>";
    exit(0);
}

$query = "select no,skin,userlevel,con,turntime,newmsg,newvote,map from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

//그새 사망이면
if($me['no'] == 0) {
    //echo "a";
    header('Location: start.php');
    //echo "<script>location.replace('start.php');</script>";
    exit(0);
}

if($me['newmsg'] == 1 && $me['newvote'] == 1) {
    $query = "update general set newmsg=0,newvote=0 where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($me['newmsg'] == 1) {
    $query = "update general set newmsg=0 where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($me['newvote'] == 1) {
    $query = "update general set newvote=0 where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

$query = "select develcost,online,conlimit,tournament,tnmt_type,turnterm,scenario,extend,fiction,npcmode,vote from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select plock from plock where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$plock = MYDB_fetch_array($result);

$con = checkLimit($me['userlevel'], $me['con'], $admin['conlimit']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

if($me['skin'] < 1) {
    $tempColor = $_basecolor;   $tempColor2 = $_basecolor2; $tempColor3 = $_basecolor3; $tempColor4 = $_basecolor4;
    $_basecolor = "000000";     $_basecolor2 = "000000";    $_basecolor3 = "000000";    $_basecolor4 = "000000";
}

$scenario = getScenario();
?>
<!DOCTYPE html>
<html>
<head>
<title>메인</title>
<meta charset="UTF-8">
<link rel=stylesheet href=stylesheet.php?<?=$me['skin'];?> type=text/css>
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="js/main.js"></script>
<link href="css/common.css" rel="stylesheet">
<link href="css/main.css" rel="stylesheet">

<?php require('analytics.php'); ?>
</head>
<body oncontextmenu='return false'>

<div style="position:absolute; top:15px; left:50%; margin-left: -567px; width:  52px; height:  52px; border: 1px solid white;">심의</div>
<?php $banner_id = $_SESSION['p_id']; ?>
<div style="position:absolute; top:77px; left:50%; margin-left: -675px; width: 160px; height: 600px; border: 1px solid white;">
<?php include('../i_banner/banner.php'); ?>
</div>
<div style="position:absolute; top:77px; left:50%; margin-left: 515px; width: 160px; height: 600px; border: 1px solid white;">
<?php include('../i_banner/banner.php'); ?>
</div>
<div style="position:absolute; top:1720px; left:50%; margin-left: -675px; width: 160px; height: 600px; border: 1px solid white;">
<?php include('../i_banner/banner.php'); ?>
</div>
<div style="position:absolute; top:1720px; left:50%; margin-left: 515px; width: 160px; height: 600px; border: 1px solid white;">
<?php include('../i_banner/banner.php'); ?>
</div>

<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg0>
    <tr><td colspan=5><?=allButton()?></td></tr>
    <tr height=50>
        <td colspan=5 align=center><font size=4>삼국지 모의전투 PHP 유기체서버 (<font color=<?=$me['skin']>0?"cyan":"white";?>><?=$scenario;?></font>)</font></td>
    </tr>
<?php
$valid = 0;
if($admin['extend'] == 0) { $extend = "표준"; }
else { $extend = "확장"; $valid = 1; }
if($admin['fiction'] == 0) { $fiction = "사실"; }
else { $fiction = "가상"; $valid = 1; }
if($admin['npcmode'] == 0) { $npcmode = "불가능"; }
else { $npcmode = "가능"; $valid = 1; }
if($me['skin'] > 0) { $color = "cyan"; }
else { $color = "white"; }
if($valid == 1) {
    echo "
    <tr height=30>
        <td width=398 colspan=2 align=center><font color={$color}>{$scenario}</font></td>
        <td width=198 align=center><font color={$color}>NPC수 : {$extend}</font></td>
        <td width=198 align=center><font color={$color}>NPC상성 : {$fiction}</font></td>
        <td width=198 align=center><font color={$color}>NPC선택 : {$npcmode}</font></td>
    </tr>";
}
?>

    <tr height=30>
        <td width=198 align=center><?php info($connect, 2, $me['skin']); ?></td>
        <td width=198 align=center>전체 접속자 수 : <?=$admin['online'];?> 명</td>
        <td width=198 align=center>턴당 갱신횟수 : <?=$admin['conlimit'];?>회</td>
        <td width=398 colspan=2 align=center><?php info($connect, 3, $me['skin']); ?></td>
    </tr>
    <tr height=30>
        <td align=center>
<?php
if($plock['plock'] == 0) { echo "<marquee scrollamount=2><font color=cyan>서버 가동중</font></marquee>"; }
else { echo "<font color=magenta>서버 동결중</font>"; }

echo "
        </td>
        <td align=center>
";

switch($admin['tnmt_type']) {
case 0:  $str = "전력전"; break;
case 1:  $str = "통솔전"; break;
case 2:  $str = "일기토"; break;
case 3:  $str = "설전"; break;
}
$str2 = getTournament($admin['tournament']);
$str3 = getTournamentTime($connect);
if($admin['tournament'] == 0) { echo "<font color=magenta>현재 토너먼트 경기 없음</font>"; }
else { echo "<marquee scrollamount=2>↑<font color=cyan>{$str}</font> {$str2} {$str3}↑</marquee>"; }

echo "
        </td>
        <td align=center>
";

$query = "select no from auction";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$auctionCount = MYDB_num_rows($result);
if($auctionCount > 0) { echo "<marquee scrollamount=2><font color=cyan>{$auctionCount}건</font> 거래 진행중</marquee>"; }
else { echo "<font color=magenta>진행중 거래 없음</font>"; }

echo "
        </td>
        <td colspan=2 align=center>
";

$vote = explode("|", $admin['vote']);
$vote[0] = Tag2Code($vote[0]);
if($vote[0] == "") { echo "<font color=magenta>진행중 설문 없음</font>"; }
else { echo "<marquee scrollamount=3><font color=cyan>설문 진행중</font> : $vote[0]</marquee>"; }


echo "
        </td>
    </tr>";
?>
    <tr><td colspan=5>접속중인 국가: <?=onlinenation($connect);?></td></tr>
    <tr><td colspan=5><?php adminMsg($connect, $me['skin']); ?></td></tr>
    <tr><td colspan=5>【 국가방침 】<?php nationMsg($connect, $me['skin']); ?></td></tr>
    <tr><td colspan=5>【 접속자 】<?=onlinegen($connect);?></td></tr>
<?php
if($me['userlevel'] >= 5) {
    echo "
    <tr><td colspan=5>
        <input type=button value=게임관리 onclick=location.replace('_admin1.php')>
        <input type=button value=회원관리 onclick=location.replace('_admin2.php')>
        <input type=button value=특별회원 onclick=location.replace('_admin3.php')>
        <input type=button value=멀티관리 onclick=location.replace('_admin4.php')>
        <input type=button value=일제정보 onclick=window.open('_admin5.php')>
        <input type=button value=접속정보 onclick=window.open('_admin6.php')>
        <input type=button value=로그정보 onclick=window.open('_admin7.php')>
        <input type=button value=외교정보 onclick=window.open('_admin8.php')>
        <input type=button value=시뮬 onclick=window.open('_simul.php')>
        <input type=button value=119 onclick=window.open('_119.php')>
    </td></tr>
";
}

?>

</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=698 height=520 colspan=2><iframe src='map.php?type=0&graphic=<?=$me['map'];?>' width=698 height=520 frameborder=0 marginwidth=0 marginheight=0 topmargin=0 scrolling=no></iframe></td>
        <td width=298 rowspan=4><iframe name=commandlist src='commandlist.php' width=298 height=700 frameborder=0 marginwidth=0 marginheight=0 topmargin=0 scrolling=no></iframe></td>
    </tr>
<form name=form2 action=preprocessing.php method=post target=commandlist>
    <tr>
        <td rowspan=3 width=50 valign=top><?=turnTable()?></td>
        <td width=646><?php cityInfo($connect); ?></td>
    </tr>
    <tr>
        <td width=646 align=right>
            <font color=<?=$me['skin']>0?"cyan":"white";?>><b>←</b> Ctrl, Shift, 드래그로 복수선택 가능　　　　　반복&수정<b>→</b></font>
            <select name=sel size=1 style=color:white;background-color:black;font-size:13;>
                <option value=1>1턴</option>
                <option value=2>2턴</option>
                <option value=3>3턴</option>
                <option value=4>4턴</option>
                <option value=5>5턴</option>
                <option value=6>6턴</option>
                <option value=7>7턴</option>
                <option value=8>8턴</option>
                <option value=9>9턴</option>
                <option value=10>10턴</option>
                <option value=11>11턴</option>
                <option value=12>12턴</option>
            </select><input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:50;font-size:13; value='반복' onclick='refreshing(2,0)'><input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:80;font-size:13; value='▼미루기' onclick='refreshing(2,1)'><input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:80;font-size:13; value='▲당기기' onclick='refreshing(2,2)'>
        </td>
    </tr>
    <tr>
        <td width=646 align=right>
            <?php commandTable($connect); ?>
            <input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:110;font-size:13; value='실 행' onclick='refreshing(3,form2)'><input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:110;font-size:13; value='갱 신' onclick='refreshing(0,0)'><input type=button style=background-color:<?=$_basecolor2;?>;color:white;width:160;font-size:13; value='로그아웃' onclick=location.replace('logout_process.php')><br>
        </td>
    </tr>
</form>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=498><?php myNationInfo($connect); ?></td>
        <td width=498><?php myInfo($connect); ?></td>
    </tr>
    <tr><td colspan=2><?=commandButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=498 align=center id=bg1><b>장수 동향</b></td>
        <td width=498 align=center id=bg1><b>개인 기록</b></td>
    </tr>
    <tr>
        <td width=498 ><?php AllLog(15, $me['skin']); ?></td>
        <td width=498 ><?php MyLog($me['no'], 15, $me['skin']); ?></td>
    </tr>
    <tr><td width=998 colspan=2 align=center id=bg1><b>중원 정세</b></td></tr>
    <tr><td width=998 colspan=2><?php History(15, $me['skin']); ?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td colspan=2>
            <form id="message" name="message" method="post" action="c_msgsubmit.php" target="msglist">
                <?php genList($connect); ?>
                <input type=textarea id=msg name=msg maxlength=99 style=color:white;background-color:black;font-size:13;width:720px;>
                <input type=button style=background-color:<?=$_basecolor2;?>;color:white;font-size:13;width:100px; value='서신전달&갱신' onclick='refreshing(4,message)'>
                <br>내용 없이 '서신전달&amp;갱신'을 누르면 메세지창이 갱신됩니다.
            </form>
        </td>
    </tr>
    <tr><td colspan=2><?=allButton()?></td></tr>
    <tr><td colspan=2>
        <iframe id="msglist" name="msglist" src='msglist.php' width=1000 height=1375 frameborder=0 marginwidth=0 marginheight=0 topmargin=0 scrolling=no>
        </iframe>
    </td></tr>
    <tr><td colspan=2>

<?php
echo allButton();
banner();
?>

    </td></tr>
</table>
<?php PrintElapsedTime(); ?>
</body>
</html>
<?php
if($con == 1) { MessageBox("접속제한이 얼마 남지 않았습니다!"); }
if($me['newmsg'] == 1) { MessageBox("개인 서신이 도착했습니다!"); }
if($me['newvote'] == 1) { $develcost = $admin['develcost']*5; MessageBox("설문조사에 참여하시면 금{$develcost}과 유니크템을 드립니다! (우측 상단 설문조사 메뉴)"); }
