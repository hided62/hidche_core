<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->loginGame()->setReadOnly();
$userID = Session::getUserID();

increaseRefresh("메인", 1);

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

if (!$userID) {
    header('Location:..');
    die();
}

//턴 실행.
TurnExecutionHelper::executeAllCommand();

if(!$session->isGameLoggedIn()){
    header('Location:..');
    die();
}

$me = $db->queryFirstRow(
    'SELECT no,con,turntime,newmsg,newvote,`officer_level` from general where owner = %i',
    $userID
);

//그새 사망이면
if ($me === null) {
    $session->logoutGame();
    header('Location: ../');
    die();
}

$gameStor->cacheAll(true);

if ($me['newmsg'] == 1 || $me['newvote'] == 1) {
    $db->update('general', [
        'newmsg'=>0,
        'newvote'=>0
    ], 'owner=%i', $userID);
}

$plock = $db->queryFirstField('SELECT plock FROM plock LIMIT 1');

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$generalObj = General::createGeneralObjFromDB($me['no']);
$generalObj->setRawCity($db->queryFirstRow('SELECT * FROM city WHERE city = %i', $generalObj->getCityID()));
$scenario = $gameStor->scenario_text;

$nationID = $generalObj->getNationID();
if($nationID){
    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    $nationStor->cacheAll();
}

$valid = 0;
if ($gameStor->extended_general == 0) {
    $extend = "표준";
} else {
    $extend = "확장";
    $valid = 1;
}
if ($gameStor->fiction == 0) {
    $fiction = "사실";
} else {
    $fiction = "가상";
    $valid = 1;
}
if ($gameStor->npcmode == 0) {
    $npcmode = "불가능";
} else if($gameStor->npcmode == 1){
    $npcmode = "가능";
    $valid = 1;
} else {
    $npcmode = "선택 생성";
}
$color = "cyan";
$mapTheme = $gameStor->map_theme;
$serverName = UniqueConst::$serverName;
$serverCnt = $gameStor->server_cnt;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=$serverName?>: 메인</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('dist_js/vendors.js')?>
<?=WebUtil::printJS('d_shared/base_map.js')?>
<?=WebUtil::printJS('dist_js/main.js')?>
<script>
window.serverNick = '<?=DB::prefix()?>';
window.serverID = '<?=UniqueConst::$serverID?>';
$(function(){
    reloadWorldMap({
        hrefTemplate:'b_currentCity.php?citylist={0}',
        useCachedMap:true
    });

    setInterval(function(){
        refreshMsg();
    }, 5000);
});
</script>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/main.css')?>
<?=WebUtil::printCSS('css/map.css')?>
<?=WebUtil::printCSS('css/msg.css')?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css?family=Nanum+Gothic|Nanum+Myeongjo|Nanum+Pen+Script" rel="stylesheet">

</head>
<body class="img_back">

<div id="container">
<div><?=allButton($gameStor->npcmode==1)?></div>
<table class="tb_layout bg0" style="width:1000px;">
    <tr height=50>
        <td colspan=5 id="server_title" align=center><font size=4>삼국지 모의전투 HiDCHe <?=$serverName.$serverCnt?>기 (<font color=cyan><?=$scenario?></font>)</font></td>
    </tr>
<?php if ($valid == 1): ?>
    <tr height=30>
        <td width=398 colspan=2 align=center><font color=<?=$color?>><?=$scenario?></font></td>
        <td width=198 align=center><font color=<?=$color?>>NPC수 : <?=$extend?></font></td>
        <td width=198 align=center><font color=<?=$color?>>NPC상성 : <?=$fiction?></font></td>
        <td width=198 align=center><font color=<?=$color?>>NPC선택 : <?=$npcmode?></font></td>
    </tr>
<?php endif; ?>

    <tr height=30>
        <td width=198><?=info(2)?></td>
        <td width=198>전체 접속자 수 : <?=$gameStor->online_user_cnt?> 명</td>
        <td width=198>턴당 갱신횟수 : <?=$gameStor->conlimit?>회</td>
        <td width=398 colspan=2><?=info(3)?></td>
    </tr>
    <tr height=30>
        <td style='text-align:center;'>
<?php
if (!$plock) {
    echo "<span style='color:cyan;'>동작 시각: ".substr($gameStor->turntime, 5, 14)."</span>";
} else {
    echo "<span style='color:magenta;'>동작 시각: ".substr($gameStor->turntime, 5, 14)."</span>";
}

echo "
        </td>
        <td align=center>
";

if ($gameStor->tournament == 0) {
    echo "<font color=magenta>현재 토너먼트 경기 없음</font>";
} else {
    switch ($gameStor->tnmt_type) {
        case 0:  $str = "전력전"; break;
        case 1:  $str = "통솔전"; break;
        case 2:  $str = "일기토"; break;
        case 3:  $str = "설전"; break;
        }
    $str2 = getTournament($gameStor->tournament);
    $str3 = getTournamentTime();
    echo "<marquee scrollamount=2>↑<font color=cyan>{$str}</font> {$str2} {$str3}↑</marquee>";
}

echo "
        </td>
        <td align=center>
";

$auctionCount = $db->queryFirstField('SELECT count(`no`) FROM auction');
if ($auctionCount > 0) {
    echo "<marquee scrollamount=2><font color=cyan>{$auctionCount}건</font> 거래 진행중</marquee>";
} else {
    echo "<font color=magenta>진행중 거래 없음</font>";
}

echo "
        </td>
        <td colspan=2 align=center>
";

$vote = $gameStor->vote?:[''];
$vote_title = Tag2Code($gameStor->vote_title??'-');
if ($vote[0] == "") {
    echo "<font color=magenta>진행중 설문 없음</font>";
} else {
    echo "<marquee scrollamount=3><font color=cyan>설문 진행중</font> : $vote_title</marquee>";
}


echo "
        </td>
    </tr>";
?>
    <tr><td colspan=5 style="text-align:left;">접속중인 국가: <?=$gameStor->online_nation?></td></tr>
    <tr><td colspan=5 style="text-align:left;">운영자 메세지 : <span style='color:yellow;'><?=$gameStor->msg?></span></td></tr>
    <tr><td colspan=5 style="text-align:left;"><div>【 국가방침 】</div><div><?=nationMsg($generalObj)?></div></td></tr>
    <tr><td colspan=5 style="text-align:left;">【 접속자 】<?=onlinegen($generalObj)?></td></tr>
<?php
if ($session->userGrade >= 5) {
?>
    <tr><td colspan=5>
        <a href='_admin1.php' target='_blank'><button type='button'>게임관리</button></a>
        <a href='_admin2.php' target='_blank'><button type='button'>회원관리</button></a>
        <a href='_admin4.php' target='_blank'><button type='button'>멀티관리</button></a>
        <a href='_admin5.php' target='_blank'><button type='button'>일제정보</button></a>
        <a href='_admin7.php' target='_blank'><button type='button'>로그정보</button></a>
        <a href='_admin8.php' target='_blank'><button type='button'>외교정보</button></a>
        <a href='_119.php' target='_blank'><button type='button'>119</button></a>
    </td></tr>
<?php
}
else if($session->userGrade == 4){
    ?>
    <tr><td colspan=5>
        <a href='_119.php' target='_blank'><button type='button'>119</button></a>
    </td></tr>
<?php
}

?>

</table>
<table class="tb_layout bg0" style="width:1000px;" id='map_position'>
    <tr>
        <td style='width:700px;height:520px;' colspan=2>
            <?=getMapHtml($mapTheme)?>
        </td>
        <td style='width:300px;vertical-align:top;' rowspan=4>
            <div id="reservedCommandList" style='overflow-y:scroll;height:700px;'>
                <table width="300" class="tb_layout b2">
                    <thead>
                        <tr height="24"><td colspan="4" class="center bg0"
                            ><strong>- 명령 목록 - </strong><input value="<?=TimeUtil::now(false)?>" type="text" id="clock" size="19" style="background-color:black;color:white;border-style:none;"
                        ></td></tr>
                    </thead>
                    <tbody class="center" style="font-weight:bold">
<?php for($turnIdx = 0; $turnIdx < GameConst::$maxTurn; $turnIdx++): ?>
                        <tr height='28' id="command_<?=$turnIdx?>"
                            ><td width="24" class='idx_pad center bg0'><?=$turnIdx+1?></td
                            ><td height='24' class='month_pad center bg1' style='min-width:70px;white-space:nowrap;overflow:hidden;'></td
                            ><td width='42' class='time_pad center' style='background-color:black;white-space:nowrap;overflow:hidden;'></td
                            ><td width='165' class='turn_pad center bg2'><span class='turn_text'></span></td
                        ></tr>
<?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    <tr>
        <td rowspan=3 width=50 valign=top><?=turnTable()?></td>
        <td style="width:650px;border:none;text-align:center;"><?=cityInfo($generalObj)?></td>
    </tr>
    <tr>
        <td style='width:650px;' align=right>
            <font color=cyan><b>←</b> Ctrl, Shift, 드래그로 복수선택 가능　　반복&amp;수정<b>→</b></font>
            <select id='repeatAmount' name=sel size=1 style=color:white;background-color:black;font-size:13px;>
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
            </select
            ><input type=button value='반복' id='repeatTurn'
                style='background-color:<?=GameConst::$basecolor2?>;color:white;width:70px;font-size:13px;margin-left:1ch;margin-right:2ch;'
            ><input type=button value='▼미루기' id='pushTurn'
                style='background-color:<?=GameConst::$basecolor2?>;color:white;width:80px;font-size:13px;'
            ><input type=button value='▲당기기' id='pullTurn'
                style='background-color:<?=GameConst::$basecolor2?>;color:white;width:80px;font-size:13px;'
        ></td>
    </tr>
    <tr>
        <td align=right style="width:650px;border:none;"><br>
            <?php printCommandTable($generalObj)?>
            <input type=button value='실 행' id="reserveTurn"
                style='background-color:<?=GameConst::$basecolor2?>;color:white;width:110px;font-size:13px;'
            ><input type=button value='갱 신' id='refreshPage'
                style='background-color:<?=GameConst::$basecolor2?>;color:white;width:110px;font-size:13px;'
            ><input type=button value='로비로' onclick="location.replace('../')"
                style=background-color:<?=GameConst::$basecolor2?>;color:white;width:160px;font-size:13px;
            >
        </td>
    </tr>
</table>
<table class="tb_layout bg0" style="width:1000px;">
    <tr>
        <td width=498 style="border:none;"><?php myNationInfo($generalObj); ?></td>
        <td width=498 style="border:none;"><?php generalInfo($generalObj); ?></td>
    </tr>
    <tr><td colspan=2><?=commandButton()?></td></tr>
</table>
<table class="tb_layout bg0" id='history_position'>
    <tr>
        <td width=498 class='bg1 center'><b>장수 동향</b></td>
        <td width=498 class='bg1 center'><b>개인 기록</b></td>
    </tr>
    <tr>
        <td width=498 id="general_public_record" style="text-align:left;"><?=formatHistoryToHTML(getGlobalActionLogRecent(15))?></td>
        <td width=498 id="general_log" style="text-align:left;"><?=formatHistoryToHTML(getGeneralActionLogRecent($me['no'], 15))?></td>
    </tr>
    <tr><td width=998 colspan=2 class='bg1 center'><b>중원 정세</b></td></tr>
    <tr><td width=998 id="world_history" colspan=2 style="text-align:left;"><?=formatHistoryToHTML(getGlobalHistoryLogRecent(15))?></td></tr>
</table>
<div class="message_input_form bg0">
    <select id="mailbox_list" size="1">

            <select name="genlist" size="1" style="color:white;background-color:black;font-size:13px">



    </select>
    <input type="textarea" id="msg_input" maxlength="99">
    <button id="msg_submit">서신전달&amp;갱신</button><br>
    내용 없이 '서신전달&amp;갱신'을 누르면 메세지창이 갱신됩니다.
</div>
<div><?=allButton($gameStor->npcmode==1)?></div>
<div id="message_board"><div style="left:0;" class="board_side bg0">
        <div class="board_header bg0" id='public_talk_position'>전체 메시지(최고99자)</div>
        <section class="public_message">
        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="public">이전 메시지 불러오기</button>
        </section>
        <div class="board_header bg0" id='secret_talk_position'>개인 메시지(최고99자)</div>
        <section class="private_message">
        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="private">이전 메시지 불러오기</button>
        </section>
    </div><div style="right:0;" class="board_side bg0">
        <div class="board_header bg0">국가 메시지(최고99자)</div>
        <section class="national_message">
        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="national">이전 메시지 불러오기</button>
        </section>
        <div class="board_header bg0">외교 메시지(최고99자)</div>
        <section class="diplomacy_message">
        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="diplomacy">이전 메시지 불러오기</button>
        </section>
 </div></div>
<div style="clear:left;"><?=allButton($gameStor->npcmode==1)?><?=banner()?></div>
</div>
<?php
if ($con == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다!");
}
if ($me['newmsg'] == 1) {
    MessageBox("새로운 서신이 도착했습니다!");
}
if ($me['newvote'] == 1) {
    $develcost = $gameStor->develcost*5;
    MessageBox("설문조사에 참여하시면 금{$develcost}과 유니크템을 드립니다! (우측 상단 설문조사 메뉴)");
}
?>
</body>
</html>
