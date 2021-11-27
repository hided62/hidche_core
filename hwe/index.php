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

if (!$session->isGameLoggedIn()) {
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
        'newmsg' => 0,
        'newvote' => 0
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
if ($nationID) {
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
} else if ($gameStor->npcmode == 1) {
    $npcmode = "가능";
    $valid = 1;
} else {
    $npcmode = "선택 생성";
}
$color = "cyan";
$mapTheme = $gameStor->map_theme;
$serverName = UniqueConst::$serverName;
$serverCnt = $gameStor->server_cnt;

$auctionCount = $db->queryFirstField('SELECT count(`no`) FROM auction');
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $serverName ?>: 메인</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('dist_js/vendors.js') ?>
    <?= WebUtil::printJS('d_shared/base_map.js') ?>
    <?= WebUtil::printJS('dist_js/vendors_vue.js', true) ?>
    <?= WebUtil::printJS('dist_js/v_main.js', true) ?>
    <?= WebUtil::printJS('dist_js/main.js') ?>
    <script>
        window.serverNick = '<?= DB::prefix() ?>';
        window.serverID = '<?= UniqueConst::$serverID ?>';
        $(function() {
            reloadWorldMap({
                hrefTemplate: 'b_currentCity.php?citylist={0}',
                useCachedMap: true
            });

            setInterval(function() {
                refreshMsg();
            }, 5000);
        });
    </script>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('css/common.css') ?>
    <?= WebUtil::printCSS('dist_css/common_vue.css') ?>
    <?= WebUtil::printCSS('dist_css/main.css') ?>
    <?= WebUtil::printCSS('css/map.css') ?>
    <?= WebUtil::printStaticValues([
        'maxTurn'=>GameConst::$maxTurn,
        'maxPushTurn'=>12,
        'commandList'=>getCommandTable($generalObj),
        'serverNow'=>TimeUtil::now(false),
        'baseColor2'=>GameConst::$basecolor2,
    ])?>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Nanum+Gothic|Nanum+Myeongjo|Nanum+Pen+Script" rel="stylesheet">

</head>

<body class="img_back">

    <div id="container">
        <div class="row toolbars"><?= allButton($gameStor->npcmode == 1) ?></div>
        <div class="tb_layout bg0 row">
            <div style="height:50px" id="server_title">
                <font size=4>삼국지 모의전투 HiDCHe <?= $serverName . $serverCnt ?>기 (<font color=cyan><?= $scenario ?></font>)</font>
            </div>

            <?php if ($valid == 1) : ?>
                <div class="row">
                    <div class="col">
                        <font color=<?= $color ?>><?= $scenario ?></font>
                    </div>
                    <div class="col">
                        <font color=<?= $color ?>>NPC수 : <?= $extend ?></font>
                    </div>
                    <div class="col">
                        <font color=<?= $color ?>>NPC상성 : <?= $fiction ?></font>
                    </div>
                    <div class="col">
                        <font color=<?= $color ?>>NPC선택 : <?= $npcmode ?></font>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row" style='height:30px;'>
                <div class="col"><?= info(2) ?></div>
                <div class="col">전체 접속자 수 : <?= $gameStor->online_user_cnt ?> 명</div>
                <div class="col">턴당 갱신횟수 : <?= $gameStor->conlimit ?>회</div>
                <div class="col2"></div>
            </div>
            <div class="row" style='height:30px;'>
                <div class="col"><?= !$plock ? ("<span style='color:cyan;'>동작 시각: " . substr($gameStor->turntime, 5, 14) . "</span>") : ("<span style='color:magenta;'>동작 시각: " . substr($gameStor->turntime, 5, 14) . "</span>") ?></div>
                <div class="col"><?php if ($gameStor->tournament == 0) : ?>
                        <font color=magenta>현재 토너먼트 경기 없음</font>
                    <?php else : ?>
                        <marquee scrollamount=2>↑<font color=cyan><?=
                                                                    ([
                                                                        '전력전', '통솔전', '일기토', '설전',
                                                                    ])[$gameStor->tnmt_type] ?? '' ?> <?= getTournament($gameStor->tournament) ?> <?= getTournamentTime() ?>↑</font>

                        </marquee>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <?php if ($auctionCount > 0) : ?>
                        <marquee scrollamount=2>
                            <font color=cyan><?= $auctionCount ?>건</font> 거래 진행중
                        </marquee>
                    <?php else : ?>
                        <font color=magenta>진행중 거래 없음</font>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <?php
                    $vote = $gameStor->vote ?: [''];
                    $vote_title = Tag2Code($gameStor->vote_title ?? '-');
                    ?>
                    <?php if ($vote[0] == "") : ?>
                        <font color=magenta>진행중 설문 없음</font>
                    <?php else : ?>
                        <marquee scrollamount=3>
                            <font color=cyan>설문 진행중</font> : $vote_title
                        </marquee>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col">접속중인 국가: <?= $gameStor->online_nation ?></div>
            </div>
            <div class="row">
                <div class="col">운영자 메세지 : <span style='color:yellow;'><?= $gameStor->msg ?></span></div>
            </div>
            <div class="row">
                <div class="col">
                    <div>【 국가방침 】</div>
                    <div><?= nationMsg($generalObj) ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    【 접속자 】<?= onlinegen($generalObj) ?>
                </div>
            </div>
            <?php if ($session->userGrade >= 5) : ?>
                <div class="row">
                    <div class="col">
                        <a href='_admin1.php' target='_blank'><button type='button'>게임관리</button></a>
                        <a href='_admin2.php' target='_blank'><button type='button'>회원관리</button></a>
                        <a href='_admin4.php' target='_blank'><button type='button'>멀티관리</button></a>
                        <a href='_admin5.php' target='_blank'><button type='button'>일제정보</button></a>
                        <a href='_admin7.php' target='_blank'><button type='button'>로그정보</button></a>
                        <a href='_admin8.php' target='_blank'><button type='button'>외교정보</button></a>
                        <a href='_119.php' target='_blank'><button type='button'>119</button></a>
                    </div>
                </div>
            <?php elseif ($session->userGrade == 4) : ?>
                <div class="row">
                    <div class="col">
                        <a href='_119.php' target='_blank'><button type='button'>119</button></a>
                    </div>
                </div>
            <?php endif; ?>
            <div id="map_view">
                <div id="mapZone" class="view-item"><?= getMapHtml($mapTheme) ?></div>
                <div class="view-item" id="reservedCommandList"></div>
                <div id="cityInfo" class="view-item" style="border:none;text-align:center;"><?= cityInfo($generalObj) ?></div>
                <div id="routeButtons" class="view-item"><input type=button value='갱 신' id='refreshPage' style='background-color:<?= GameConst::$basecolor2 ?>;color:white;width:110px;font-size:13px;'><input type=button value='로비로' onclick="location.replace('../')" style="background-color:<?= GameConst::$basecolor2 ?>;color:white;width:160px;font-size:13px;"></div>
            </div>
            <div class="row">
                <div class="col-lg-6"><?php myNationInfo($generalObj); ?></div>
                <div class="col-lg-6"><?php generalInfo($generalObj); ?></div>
            </div>

            <div class="row"><?= commandButton() ?></div>
            <div class="row">
                <div class="col-lg-6">
                    <div><b>장수 동향</b></div>
                    <div id="general_public_record" style="text-align:left;"><?= formatHistoryToHTML(getGlobalActionLogRecent(15)) ?></div>
                </div>
                <div class="col-lg-6">
                    <div><b>개인 기록</b></div>
                    <div id="general_log" style="text-align:left;"><?= formatHistoryToHTML(getGeneralActionLogRecent($me['no'], 15)) ?></div>
                </div>
                <div class="col-12">
                    <div><b>중원 정세</b></div>
                    <div id="world_history" colspan=2 style="text-align:left;"><?= formatHistoryToHTML(getGlobalHistoryLogRecent(15)) ?></div>
                </div>
            </div>
            <div class="message_input_form bg0">
                <select id="mailbox_list" size="1">

                    <select name="genlist" size="1" style="color:white;background-color:black;font-size:13px">



                    </select>
                    <input type="textarea" id="msg_input" maxlength="99">
                    <button id="msg_submit">서신전달&amp;갱신</button><br>
                    내용 없이 '서신전달&amp;갱신'을 누르면 메세지창이 갱신됩니다.
            </div>
            <div class="row toolbars"><?= allButton($gameStor->npcmode == 1) ?></div>
            <div id="message_board" class="row">
                <div class="col-lg-6 board_side bg0">
                    <div class="board_header bg0" id='public_talk_position'>전체 메시지(최고99자)</div>
                    <section class="public_message">
                        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="public">이전 메시지 불러오기</button>
                    </section>
                </div>
                <div class="col-lg-6 board_side bg0">
                    <div class="board_header bg0">국가 메시지(최고99자)</div>
                    <section class="national_message">
                        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="national">이전 메시지 불러오기</button>
                    </section>
                </div>




                <div class="col-lg-6 board_side bg0">
                    <div class="board_header bg0" id='secret_talk_position'>개인 메시지(최고99자)</div>
                    <section class="private_message">
                        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="private">이전 메시지 불러오기</button>
                    </section>
                </div>

                <div class="col-lg-6 board_side bg0">
                    <div class="board_header bg0">외교 메시지(최고99자)</div>
                    <section class="diplomacy_message">
                        <button type="button" class="load_old_message btn btn-secondary btn-block" data-msg_type="diplomacy">이전 메시지 불러오기</button>
                    </section>
                </div>
            </div>
        </div>
        <div class="row toolbars"><?= allButton($gameStor->npcmode == 1) ?><?= banner() ?></div>
    </div>
    <?php
    if ($con == 1) {
        MessageBox("접속제한이 얼마 남지 않았습니다!");
    }
    if ($me['newmsg'] == 1) {
        MessageBox("새로운 서신이 도착했습니다!");
    }
    if ($me['newvote'] == 1) {
        $develcost = $gameStor->develcost * 5;
        MessageBox("설문조사에 참여하시면 금{$develcost}과 유니크템을 드립니다! (우측 상단 설문조사 메뉴)");
    }
    ?>
</body>

</html>