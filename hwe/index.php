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

if ($gameStor->extended_general == 0) {
    $extend = "표준";
} else {
    $extend = "확장";
}
if ($gameStor->fiction == 0) {
    $fiction = "사실";
} else {
    $fiction = "가상";
}
if ($gameStor->npcmode == 0) {
    $npcmode = "불가능";
} else if ($gameStor->npcmode == 1) {
    $npcmode = "가능";
} else {
    $npcmode = "선택 생성";
}
$color = "cyan";
$mapTheme = $gameStor->map_theme;
$serverName = UniqueConst::$serverName;
$serverCnt = $gameStor->server_cnt;

$auctionCount = $db->queryFirstField('SELECT count(`no`) FROM auction');

$myNationStatic = getNationStaticInfo($generalObj->getNationID());
$nationColorType = substr($myNationStatic['color'] ?? '#000000', 1);

$autorunUser = ($gameStor->autorun_user) ?? [];
$otherTextInfo = [];

if ($gameStor->join_mode == 'onlyRandom') {
    $otherTextInfo[] = '랜덤 임관 전용';
}
if ($autorunUser['limit_minutes'] ?? false) {
    $otherTextInfo[] = getAutorunInfo($autorunUser);
}

if (!$otherTextInfo) {
    $otherTextInfo = '표준';
} else {
    $otherTextInfo = join(', ', $otherTextInfo);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $serverName ?>: 메인</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('d_shared/base_map.js') ?>

    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('dist_css/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printCSS('dist_css/common_vue.css') ?>
    <?= WebUtil::printCSS('dist_css/main.css') ?>
    <?= WebUtil::printCSS('css/map.css') ?>
    <?= WebUtil::printStaticValues([
        'serverNick' => DB::prefix(),
        'serverID' => UniqueConst::$serverID,
        'maxTurn' => GameConst::$maxTurn,
        'maxPushTurn' => 12,
        'commandList' => getCommandTable($generalObj),
        'serverNow' => TimeUtil::now(false),
        'baseColor2' => GameConst::$basecolor2,
    ]) ?>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
</head>

<body class="img_back sam-color-<?= $nationColorType ?>">
    <div id="container" class="bg0">
        <div class="row toolbars gx-0">
            <div class="buttonPlate"><?= allButton($gameStor->npcmode == 1) ?></button></div>
        </div>
        <div class="tb_layout row gx-0">
            <div id="server_title" class="row py-2 center">
                <h3>삼국지 모의전투 HiDCHe <?= $serverName . $serverCnt ?>기 <span class="avoid-wrap">(<font color=cyan><?= $scenario ?></font>)</span></h3>
            </div>

            <div class="row center gx-0">
                <div class="s-border-t col py-2 col-8 col-md-4" style="color:<?= $color ?>;">
                    <?= $scenario ?>
                </div>
                <div class="s-border-t col py-2 col-4 col-md-2" style="color:<?= $color ?>;">
                    NPC수 : <?= $extend ?>
                </div>
                <div class="s-border-t col py-2 col-4 col-md-2" style="color:<?= $color ?>;">
                    NPC상성 : <?= $fiction ?>
                </div>
                <div class="s-border-t col py-2 col-4 col-md-2" style="color:<?= $color ?>;">
                    NPC선택 : <?= $npcmode ?>
                </div>
                <div class="s-border-t col py-2 col-4 col-md-2" style="color:<?= $color ?>;">
                    기타 설정: <?= $otherTextInfo ?>
                </div>

                <div class="s-border-t col py-2 col-8 col-md-4"><?= info(2) ?></div>
                <div class="s-border-t col py-2 col-4 col-md-2">전체 접속자 수 : <?= $gameStor->online_user_cnt ?> 명</div>
                <div class="s-border-t col py-2 col-4 col-md-2">턴당 갱신횟수 : <?= $gameStor->conlimit ?>회</div>
                <div class="s-border-t col py-2 col-8 col-md-4"><?= info(3) ?></div>
                <div class="s-border-t py-2 col col-6 col-md-4"><?php if ($gameStor->tournament == 0) : ?>
                        <span style='color:magenta'>현재 토너먼트 경기 없음</span>
                    <?php else : ?>
                        ↑<span style='color:cyan'><?=
                                                                    ([
                                                                        '전력전', '통솔전', '일기토', '설전',
                                                                    ])[$gameStor->tnmt_type] ?? '' ?> <?= getTournament($gameStor->tournament) ?> <?= getTournamentTime() ?></span>↑


                    <?php endif; ?>
                </div>
                <div class="s-border-t py-2 col col-6 col-md-2">
                    <div style="display:inline-block;"><?= !$plock ? ("<span style='color:cyan;'>동작 시각: " . substr($gameStor->turntime, 5, 14) . "</span>") : ("<span style='color:magenta;'>동작 시각: " . substr($gameStor->turntime, 5, 14) . "</span>") ?></div>
                </div>
                <div class="s-border-t py-2 col col-6 col-md-2">
                    <?php if ($auctionCount > 0) : ?>
                        <span style='color:cyan'><?= $auctionCount ?>건</span> 거래 진행중
                    <?php else : ?>
                        <span style='color:magenta'>진행중 거래 없음</span>
                    <?php endif; ?>
                </div>
                <div class="s-border-t py-2 col col-6 col-md-4 vote-cell">
                    <?php
                    $vote = $gameStor->vote ?: [''];
                    $vote_title = Tag2Code($gameStor->vote_title ?? '-');
                    ?>
                    <?php if ($vote[0] == "") : ?>
                        <span style='color:magenta'>진행중 설문 없음</span>
                    <?php else : ?>
                        <span style='color:cyan'>설문 진행중</span> : <span><?= $vote_title ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col s-border-t px-2 py-2">접속중인 국가: <?= $gameStor->online_nation ?></div>
            </div>
            <div class="row gx-0">
                <div class="col s-border-t px-2 py-2">운영자 메세지 : <span style='color:yellow;'><?= $gameStor->msg ?></span></div>
            </div>
            <div class="row gx-0">
                <div class="col s-border-t py-2" id="nation-msg-position">
                    <div class="px-2">【 국가방침 】</div>
                    <div id='nation-msg-box'>
                        <div id='nation-msg'><?= nationMsg($generalObj) ?></div>
                    </div>
                </div>
            </div>
            <div class="row gx-0">
                <div class="col s-border-t px-2 py-2">
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
            <div id="map_view" class="gx-0">
                <div id="mapZone" class="view-item"><?= getMapHtml($mapTheme) ?></div>
                <div class="view-item" id="reservedCommandZone">
                    <div id="reservedCommandList"></div>
                    <div id="actionMiniPlate" class="gx-0 row">
                        <div class="col">
                            <div class="gx-1 row">
                                <div class="col-8 d-grid"><button type='button' class='btn btn-sammo-base2 refreshPage'>갱 신</button></div>
                                <div class="col-4 d-grid"><button type='button' class='btn btn-sammo-base2' onclick="location.replace('../')">로비로</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="cityInfo" class="view-item" style="border:none;text-align:center;"><?= cityInfo($generalObj) ?></div>
                <div id="nation-position"><?php myNationInfo($generalObj); ?></div>
                <div id="general-position"><?php generalInfo($generalObj); ?></div>
                <div id="generalCommandButton" class="row gx-0">
                    <div class="buttonPlate bg2"><?= commandButton(['btnClass' => 'btn btn-sammo-nation']) ?></div>
                </div>
            </div>
        </div>
        <div id="actionMiniPlateSub" class="gx-0 row">
            <div class="col">
                <div class="gx-1 row">
                    <div class="col-3 d-grid"><button type='button' class="btn btn-dark" onclick="scrollHardTo('reservedCommandList')">명령으로</button></div>
                    <div class="col-5 d-grid"><button type='button' class='btn btn-sammo-base2 refreshPage'>갱 신</button></div>
                    <div class="col-4 d-grid"><button type='button' class='btn btn-sammo-base2' onclick="location.replace('../')">로비로</button></div>
                </div>
            </div>
        </div>
        <div class="row gx-0">
            <div class="col-md-6" id="general_public_record-position">
                <div class="bg1 center s-border-tb"><b>장수 동향</b></div>
                <div id="general_public_record" style="text-align:left;"><?= formatHistoryToHTML(getGlobalActionLogRecent(15)) ?></div>
            </div>
            <div class="col-md-6" id="general_log-position">
                <div class="bg1 center s-border-tb"><b>개인 기록</b></div>
                <div id="general_log" style="text-align:left;"><?= formatHistoryToHTML(getGeneralActionLogRecent($me['no'], 15)) ?></div>
            </div>
            <div class="col-12" id="world_history-position">
                <div class="bg1 center s-border-tb"><b>중원 정세</b></div>
                <div id="world_history" colspan=2 style="text-align:left;"><?= formatHistoryToHTML(getGlobalHistoryLogRecent(15)) ?></div>
            </div>
        </div>
        <div class="row toolbars gx-0">
            <div class="buttonPlate"><?= allButton($gameStor->npcmode == 1) ?></div>

            <div id="message_board" class="row gx-0">
                <div class="message_input_form bg0 gx-0 row">
                    <div id="mailbox_list-col" class="col-6 col-md-2 d-grid">
                        <select id="mailbox_list" size="1" class="form-control bg-dark text-white">
                        </select>
                    </div>
                    <div id="msg_input-col" class="col-12 col-md-8 d-grid">
                        <input type="text" id="msg_input" maxlength="99" class="form-control">
                    </div>
                    <div id="msg_submit-col" class="col-6 col-md-2 d-grid"><button id="msg_submit" class="btn btn-primary">서신전달&amp;갱신</button></div>
                </div>
                <div class="col-md-6 board_side bg0"><a id="public_talk_position"></a>
                    <div class="board_header bg0">전체 메시지(최고99자)</div>
                    <section class="public_message">
                        <div class="d-grid"><button type="button" class="load_old_message btn btn-secondary" data-msg_type="public">이전 메시지 불러오기</button></div>
                    </section>
                </div>
                <div class="col-md-6 board_side bg0"><a id="national_talk_position"></a>
                    <div class="board_header bg0">국가 메시지(최고99자)</div>
                    <section class="national_message">
                        <div class="d-grid"><button type="button" class="load_old_message btn btn-secondary" data-msg_type="national">이전 메시지 불러오기</button></div>
                    </section>
                </div>
                <div class="col-md-6 board_side bg0"><a id="private_talk_position"></a>
                    <div class="board_header bg0">개인 메시지(최고99자)</div>
                    <section class="private_message">
                        <div class="d-grid"><button type="button" class="load_old_message btn btn-secondary" data-msg_type="private">이전 메시지 불러오기</button></div>
                    </section>
                </div>
                <div class="col-md-6 board_side bg0"><a id="diplomacy_talk_position"></a>
                    <div class="board_header bg0">외교 메시지(최고99자)</div>
                    <section class="diplomacy_message">
                        <div class="d-grid"><button type="button" class="load_old_message btn btn-secondary" data-msg_type="diplomacy">이전 메시지 불러오기</button></div>
                    </section>
                </div>
            </div>
            <div class="row toolbars gx-0">
                <div class="buttonPlate"><?= allButton($gameStor->npcmode == 1) ?></div><?= banner() ?>
            </div>
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
    </div>
    <nav class="navbar navbar-expand fixed-bottom navbar-dark bg-dark d-sm-block d-md-none p-0" id="navbar500">
        <div class="container-fluid">
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mx-auto">
                    <li class="nav-item dropup">
                        <div class="dropdown-toggle text-white btn btn-sammo-base2" id="navbarGlobal" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            외부 메뉴
                        </div>
                        <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="navbarGlobal" id="navbarGlobalItems">
                            <?= allButton($gameStor->npcmode == 1, ['btnBegin' => '<li>', 'btnEnd' => '</li>', 'btnClass' => 'dropdown-item']) ?>
                        </ul>
                    </li>
                    <li class="nav-item dropup">
                        <div class="dropdown-toggle btn btn-sammo-nation" id="navbarNation" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            국가 메뉴
                        </div>
                        <ul class="dropdown-menu" aria-labelledby="navbarNation" id="navbarNationItems">
                            <?= commandButton(['btnBegin' => '<li>', 'btnEnd' => '</li>', 'btnClass' => 'dropdown-item']) ?>
                        </ul>
                    </li>
                    <li class="nav-item dropup">
                        <div class="dropdown-toggle text-white btn btn-dark" id="navbarQuick" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            빠른 이동
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" id="navbarQuickItems">
                            <li><a class="dropdown-item disabled">국가 정보</a></button></li>
                            <hr class="dropdown-divider">
                            </hr>
                            <li><button type="button" onclick="scrollHardTo('nation-msg-position')" class="dropdown-item">방침</button></li>
                            <li><button type="button" onclick="scrollHardTo('reservedCommandList')" class="dropdown-item">명령</button></li>
                            <li><button type="button" onclick="scrollHardTo('nation-position')" class="dropdown-item">국가</button></li>
                            <li><button type="button" onclick="scrollHardTo('general-position')" class="dropdown-item">장수</button></li>
                            <li><button type="button" onclick="scrollHardTo('cityInfo')" class="dropdown-item">도시</button></li>
                            <li><a class="dropdown-item disabled">동향 정보</a></button></li>
                            <hr class="dropdown-divider">
                            </hr>
                            <li><button type="button" onclick="scrollHardTo('mapZone')" class="dropdown-item">지도</button></li>
                            <li><button type="button" onclick="scrollHardTo('general_public_record-position')" class="dropdown-item">동향</button></li>
                            <li><button type="button" onclick="scrollHardTo('general_log-position')" class="dropdown-item">개인</button></li>
                            <li><button type="button" onclick="scrollHardTo('world_history-position')" class="dropdown-item">정세</button></li>
                            <li><span>&nbsp;</span></li>
                            <li><a class="dropdown-item disabled">메시지</a></button></li>
                            <hr class="dropdown-divider">
                            </hr>
                            <li><button type="button" onclick="scrollHardTo('public_talk_position')" class="dropdown-item">전체</button></li>
                            <li><button type="button" onclick="scrollHardTo('national_talk_position')" class="dropdown-item">국가</button></li>
                            <li><button type="button" onclick="scrollHardTo('private_talk_position')" class="dropdown-item">개인</button></li>
                            <li><button type="button" onclick="scrollHardTo('diplomacy_talk_position')" class="dropdown-item">외교</button></li>
                            <li><button type='button' class='btn btn-sammo-base2' onclick="location.replace('../')">로비로</button></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="refreshPage btn btn-sammo-base2 text-white" role="button">갱신</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?= WebUtil::printJS('dist_js/vendors_vue.js') ?>
    <?= WebUtil::printJS('dist_js/common_vue.js') ?>
    <?= WebUtil::printJS('dist_js/v_main.js') ?>
    <script>
        (function() {
            reloadWorldMap({
                hrefTemplate: 'b_currentCity.php?citylist={0}',
                useCachedMap: true
            });

            setInterval(function() {
                refreshMsg();
            }, 5000);
        })();
    </script>
</body>

</html>