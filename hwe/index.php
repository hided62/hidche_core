<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->loginGame()->setReadOnly();
$userID = Session::getUserID();

increaseRefresh("메인", 1);

$db = DB::db();
$connect=$db->get();

if (!$userID) {
    header('Location:..');
    die();
}

$me = $db->queryFirstRow(
    'SELECT no,con,turntime,newmsg,newvote,`level` from general where owner = %i',
    $userID
);

//턴 실행.
checkTurn();

//그새 사망이면
if ($me === null) {
    $session->logoutGame();
    header('Location: ../');
    die();
}

if ($me['newmsg'] == 1 || $me['newvote'] == 1) {
    $db->update('general', [
        'newmsg'=>0,
        'newvote'=>0
    ], 'owner=%i', $userID);
}

$query = "select develcost,online,conlimit,tournament,tnmt_type,turnterm,scenario,scenario_text,extended_general,fiction,npcmode,vote from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$admin = MYDB_fetch_array($result);

$query = "select plock from plock limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$plock = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$scenario = $admin['scenario_text'];
?>
<!DOCTYPE html>
<html>
<head>
<title>메인</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="../e_lib/jquery.redirect.js"></script>
<script src="../d_shared/common_path.js"></script>
<script src="js/common.js"></script>
<script src="js/main.js"></script>
<script src="js/base_map.js"></script>
<script src="js/map.js"></script>
<script src="js/msg.js"></script>
<script>
$(function(){
    reloadWorldMap({
        hrefTemplate:'b_currentCity.php?citylist={0}'
    });

    setInterval(function(){
        refreshMsg();
    }, 30000);
});
</script>
<link href="css/normalize.css" rel="stylesheet">
<link href="../d_shared/common.css" rel="stylesheet">
<link href="css/common.css" rel="stylesheet">
<link href="css/main.css" rel="stylesheet">
<link href="css/map.css" rel="stylesheet">
<link href="css/msg.css" rel="stylesheet">


</head>
<body class="img_back">

<div id="container">
<div><?=allButton()?></div>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13px;word-break:break-all; id=bg0>
    <tr height=50>
        <td colspan=5 align=center><font size=4>삼국지 모의전투 HiDCHe (<font color=cyan><?=$scenario?></font>)</font></td>
    </tr>
<?php
$valid = 0;
if ($admin['extended_general'] == 0) {
    $extend = "표준";
} else {
    $extend = "확장";
    $valid = 1;
}
if ($admin['fiction'] == 0) {
    $fiction = "사실";
} else {
    $fiction = "가상";
    $valid = 1;
}
if ($admin['npcmode'] == 0) {
    $npcmode = "불가능";
} else {
    $npcmode = "가능";
    $valid = 1;
}
$color = "cyan";
if ($valid == 1) {
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
        <td width=198 align=center><?=info(2)?></td>
        <td width=198 align=center>전체 접속자 수 : <?=$admin['online']?> 명</td>
        <td width=198 align=center>턴당 갱신횟수 : <?=$admin['conlimit']?>회</td>
        <td width=398 colspan=2 align=center><?=info(3)?></td>
    </tr>
    <tr height=30>
        <td align=center>
<?php
if ($plock['plock'] == 0) {
    echo "<marquee scrollamount=2><font color=cyan>서버 가동중</font></marquee>";
} else {
    echo "<font color=magenta>서버 동결중</font>";
}

echo "
        </td>
        <td align=center>
";

switch ($admin['tnmt_type']) {
case 0:  $str = "전력전"; break;
case 1:  $str = "통솔전"; break;
case 2:  $str = "일기토"; break;
case 3:  $str = "설전"; break;
}
$str2 = getTournament($admin['tournament']);
$str3 = getTournamentTime();
if ($admin['tournament'] == 0) {
    echo "<font color=magenta>현재 토너먼트 경기 없음</font>";
} else {
    echo "<marquee scrollamount=2>↑<font color=cyan>{$str}</font> {$str2} {$str3}↑</marquee>";
}

echo "
        </td>
        <td align=center>
";

$query = "select no from auction";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
$auctionCount = MYDB_num_rows($result);
if ($auctionCount > 0) {
    echo "<marquee scrollamount=2><font color=cyan>{$auctionCount}건</font> 거래 진행중</marquee>";
} else {
    echo "<font color=magenta>진행중 거래 없음</font>";
}

echo "
        </td>
        <td colspan=2 align=center>
";

$vote = explode("|", $admin['vote']);
$vote[0] = Tag2Code($vote[0]);
if ($vote[0] == "") {
    echo "<font color=magenta>진행중 설문 없음</font>";
} else {
    echo "<marquee scrollamount=3><font color=cyan>설문 진행중</font> : $vote[0]</marquee>";
}


echo "
        </td>
    </tr>";
?>
    <tr><td colspan=5>접속중인 국가: <?=onlinenation()?></td></tr>
    <tr><td colspan=5><?php adminMsg(); ?></td></tr>
    <tr><td colspan=5>【 국가방침 】<?php nationMsg(); ?></td></tr>
    <tr><td colspan=5>【 접속자 】<?=onlinegen()?></td></tr>
<?php
if ($session->userGrade >= 5) {
    echo "
    <tr><td colspan=5>
        <input type=button value=게임관리 onclick=location.replace('_admin1.php')>
        <input type=button value=회원관리 onclick=location.replace('_admin2.php')>
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
else if($session->userGrade == 4){
    echo "
    <tr><td colspan=5>
        <input type=button value=시뮬 onclick=window.open('_simul.php')>
        <input type=button value=119 onclick=window.open('_119.php')>
    </td></tr>
";
}

?>

</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=698 height=520 colspan=2>
            <?=getMapHtml()?>
        </td>
        <td width=298 rowspan=4><iframe seamless="seamless" name=commandlist src='commandlist.php' width=298 height=700 frameborder=0 marginwidth=0 marginheight=0 topmargin=0 scrolling=no></iframe></td>
    </tr>
<form name=form2 action=preprocessing.php method=post target=commandlist>
    <tr>
        <td rowspan=3 width=50 valign=top><?=turnTable()?></td>
        <td width=646><?php cityInfo(); ?></td>
    </tr>
    <tr>
        <td width=646 align=right>
            <font color=cyan><b>←</b> Ctrl, Shift, 드래그로 복수선택 가능　　　　　반복&amp;수정<b>→</b></font>
            <select name=sel size=1 style=color:white;background-color:black;font-size:13px;>
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
            </select><input type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:50px;font-size:13px; value='반복' onclick='refreshing(this, 2,0)'><input type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:80px;font-size:13px; value='▼미루기' onclick='refreshing(this, 2,1)'><input type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:80px;font-size:13px; value='▲당기기' onclick='refreshing(this, 2,2)'>
        </td>
    </tr>
    <tr>
        <td width=646 align=right>
            <?php commandTable(); ?>
            <input id="mainBtnSubmit" type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:110px;font-size:13px; value='실 행' onclick='refreshing(this, 3,form2)'><input type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:110px;font-size:13px; value='갱 신' onclick='refreshing(this, 0,0)'><input type=button style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;font-size:13px; value='로비로' onclick=location.replace('../')><br>
        </td>
    </tr>
</form>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=498><?php myNationInfo(); ?></td>
        <td width=498><?php myInfo(); ?></td>
    </tr>
    <tr><td colspan=2><?=commandButton()?></td></tr>
</table>
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 style=font-size:13px;word-break:break-all; id=bg0>
    <tr>
        <td width=498 align=center id=bg1><b>장수 동향</b></td>
        <td width=498 align=center id=bg1><b>개인 기록</b></td>
    </tr>
    <tr>
        <td width=498 ><?=getGeneralPublicRecordRecent(15)?></td>
        <td width=498 ><?=getGenLogRecent($me['no'], 15)?></td>
    </tr>
    <tr><td width=998 colspan=2 align=center id=bg1><b>중원 정세</b></td></tr>
    <tr><td width=998 colspan=2><?=getWorldHistoryRecent(15)?></td></tr>
</table>
<div class="message_input_form bg0">
    <select id="mailbox_list" size="1">

            <select name="genlist" size="1" style="color:white;background-color:black;font-size:13">



    </select>
    <input type="textarea" id="msg_input" maxlength="99">
    <button id="msg_submit">서신전달&amp;갱신</button><br>
    내용 없이 '서신전달&amp;갱신'을 누르면 메세지창이 갱신됩니다.
</div>
<div><?=allButton()?></div>
<div id="message_board"><div style="left:0;" class="board_side bg0">
        <div class="board_header bg0">전체 메시지(최고99자)</div>
        <section class="public_message"></section>
        <div class="board_header bg0">개인 메시지(최고99자)</div>
        <section class="private_message"></section>
    </div><div style="right:0;" class="board_side bg0">
        <section class="diplomacy_message">

        </section>
        <div class="board_header bg0">국가 메시지(최고99자)</div>
        <section class="national_message"></section>
 </div></div>
<div style="clear:left;"><?=allButton()?><?=banner()?></div>
<?php PrintElapsedTime(); ?>
</div>
<?php
if ($con == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다!");
}
if ($me['newmsg'] == 1) {
    MessageBox("개인 서신이 도착했습니다!");
}
if ($me['newvote'] == 1) {
    $develcost = $admin['develcost']*5;
    MessageBox("설문조사에 참여하시면 금{$develcost}과 유니크템을 드립니다! (우측 상단 설문조사 메뉴)");
}
?>
</body>
</html>
