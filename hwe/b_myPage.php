<?php

namespace sammo;

include "lib.php";
include "func.php";

$showDieOnPrestartBtn = false;
$availableDieOnPrestart = false;

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gameStor->cacheValues(['turntime', 'opentime', 'autorun_user', 'npcmode']);

increaseRefresh("내정보", 1);

$me = General::createGeneralObjFromDB($generalID);

$myset = $me->getVar('myset');
if ($myset > 0) {
    $submit = 'button';
} else {
    $submit = 'hidden';
}

$targetTime = addTurn($me->getVar('lastrefresh'), $gameStor->turnterm, 2);
if ($gameStor->turntime <= $gameStor->opentime) {
    //서버 가오픈시 할 수 있는 행동
    if ($me->getNPCType() == 0) {
        $showDieOnPrestartBtn = true;
        if ($targetTime <= TimeUtil::now()) {
            $availableDieOnPrestart = true;
        }
    }
}

$use_treatment = $me->getAuxVar('use_treatment') ?? 10;
$use_auto_nation_turn = $me->getAuxVar('use_auto_nation_turn') ?? 1;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 내정보</title>
    <?= WebUtil::printStaticValues([
        'availableDieOnPrestart' => $availableDieOnPrestart,
        'staticValues' => [
            'items' => Util::mapWithKey(fn (string $key, BaseItem $item) => [
                'name' => $item->getName(),
                'rawName' => $item->getRawName(),
                'className' => $item->getRawClassName(),
                'cost' => $item->getCost(),
                'isBuyable' => $item->isBuyable(),
            ], $me->getItems())
        ]
    ]) ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printDist('ts', ['myPage']) ?>
</head>

<body>
    <div id="container" class="bg0">
        <div class="row gx-0">
            <div class="col">내 정 보<br><?= backButton() ?></div>
        </div>
        <div class="row gx-0">
            <div class="col col-12 col-md-6">
                <div class="row">
                    <div class="col"><?php generalInfo($me); ?><?php generalInfo2($me); ?></div>
                </div>
            </div>

            <div class="col col-12 col-md-6">
                <div class="row mx-0 gx-0">
                    <div class="col" style='padding-left:2ch;'>
                        토너먼트 【
                        <input type=radio class='tnmt' name=tnmt value=0 <?= $me->getVar('tnmt') == 0 ? "checked" : ""; ?>>수동참여
                        <input type=radio class='tnmt' name=tnmt value=1 <?= $me->getVar('tnmt') == 1 ? "checked" : ""; ?>>자동참여
                        】<br>
                        ∞<span style='color:orange'>개막직전 남는자리가 있을경우 랜덤하게 참여합니다.</span><br><br>

                        환약 사용 【<select id='use_treatment' name='use_treatment'>
                            <option value=10 <?= ($use_treatment == 10) ? "selected" : ""; ?>>경상</option>
                            <option value=21 <?= ($use_treatment == 21) ? "selected" : ""; ?>>중상</option>
                            <option value=41 <?= ($use_treatment == 41) ? "selected" : ""; ?>>심각</option>
                            <option value=61 <?= ($use_treatment == 61) ? "selected" : ""; ?>>위독</option>
                            <option value=100 <?= ($use_treatment == 100) ? "selected" : ""; ?>>사용안함</option>
                        </select>】<br>
                        ∞<span style='color:orange'>부상을 입었을 때 환약을 사용하는 기준입니다.</span><br><br>
                        <?php if (($gameStor->autorun_user['options']['chief']) ?? false) : ?>
                            자동 사령턴 허용 【<select id='use_auto_nation_turn' name='use_auto_nation_turn'>
                                <option value=1 <?= $use_auto_nation_turn ? "selected" : ""; ?>>허용</option>
                                <option value=0 <?= (!$use_auto_nation_turn) ? "selected" : ""; ?>>허용 안함</option>
                            </select>】<br>
                            ∞<span style='color:orange'>수뇌가 되었을 때 휴식 턴이어도 적당한 턴을 알아서 넣는 것을 허용합니다.</span><br><br>
                        <?php else : ?>
                            <input type="hidden" id='use_auto_nation_turn' name='use_auto_nation_turn' value="1" />
                        <?php endif; ?>
                        수비 【<select id='defence_train' name='defence_train'>
                            <?php foreach ([90, 80, 60, 40] as $targetDefenceTrain) : ?>
                                <option value='<?= $targetDefenceTrain ?>' <?= $me->getVar('defence_train') == $targetDefenceTrain ? "selected" : ""; ?>><?= formatDefenceTrain($targetDefenceTrain) ?>(훈사<?= $targetDefenceTrain ?>)</option>
                            <?php endforeach; ?>
                            <option value=999 <?= $me->getVar('defence_train') == 999 ? "selected" : ""; ?>><?= formatDefenceTrain(999) ?>[훈련, 사기 -3]</option>
                        </select>
                        】<br><br>
                        <input type=<?= $submit ?> id='set_my_setting' name=btn style=background-color:<?= GameConst::$basecolor2 ?>;color:white;width:160px;height:30px;font-size:14px; value=설정저장><br>
                        ∞<span style='color:orange'>설정저장은 이달중 <?= $myset ?>회 남았습니다.</span><br><br>
                        <?php if (!($gameStor->autorun_user['limit_minutes'] ?? false)) : ?>
                            휴 가 신 청<br>
                            <button type="button" id='vacation' style=background-color:<?= GameConst::$basecolor2 ?>;color:white;width:160px;height:30px;font-size:14px;>휴가 신청</button><br><br>
                        <?php endif; ?>
                        <!--빙의 해제용 삭턴 조절<br>
            <a href="b_myPage.php?detachNPC=1"><button type="button" style=background-color:<?= GameConst::$basecolor2 ?>;color:white;width:160px;height:30px;font-size:14px;>빙의 해체 요청</button></a>-->

                        <?php if ($showDieOnPrestartBtn) : ?>
                            가오픈 기간 내 장수 삭제 (<?= substr($targetTime, 0, 19) ?> 부터)<br>
                            <button type="button" id='dieOnPrestart' style=background-color:<?= GameConst::$basecolor2 ?>;color:white;width:160px;height:30px;font-size:14px;>장수 삭제</button><br><br>
                        <?php endif; ?>

                        <?php if ($gameStor->npcmode == 2 && $me->getNPCType() == 0) : ?>
                            다른 장수 선택 (<?= substr($me->getAuxVar('next_change') ?? TimeUtil::now(), 0, 19) ?> 부터)<br>
                            <a href="select_general_from_pool.php" id='select_general_from_pool'><button type="button" style=background-color:<?= GameConst::$basecolor2 ?>;color:white;width:160px;height:30px;font-size:14px;>다른 장수 선택</button></a><br><br>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-4 text-end">500px/1000px 모드<br>(모바일 전용, 즉시 설정)</div>
                            <div class="col-8">
                                <div class="btn-group" role="group" aria-label="500px/1000px 모드 설정">
                                    <input type="radio" class="btn-check" name="screenMode" value="auto" id="screenMode_auto" autocomplete="off">
                                    <label class="btn btn-primary" for="screenMode_auto">자동</label>

                                    <input type="radio" class="btn-check" name="screenMode" value="500px" id="screenMode_500px" autocomplete="off">
                                    <label class="btn btn-primary" for="screenMode_500px">500px</label>

                                    <input type="radio" class="btn-check" name="screenMode" value="1000px" id="screenMode_1000px" autocomplete="off">
                                    <label class="btn btn-primary" for="screenMode_1000px">1000px</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">아이템 파기</div>

                        </div>
                        <div class="row mx-1">
                            <div class="btn-group" role="group">
                                <?php foreach ($me->getItems() as $itemKey => $item) : ?>
                                    <button type="button" data-item-type='<?=$itemKey?>' class="drop-item-btn btn btn-primary <?= $item->getName() == '-' ? 'disabled' : '' ?>"><?= $item->getName() ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <br>

                        개인용 CSS<br>
                        <textarea id='custom_css' style='color:white;background-color:black;width:420px;height:150px;'></textarea>
                    </div>
                </div>
            </div>
            <div class="col col-12 col-md-6">
                <div class="row gx-0">
                    <div class="col bg1 text-center">
                        <h4 style='color:skyblue'>개인 기록</h4>
                    </div>
                </div>
                <div class="row gx-0">
                    <div id='generalActionPlate'>
                        <?= formatHistoryToHTML(getGeneralActionLogRecent($generalID, 24), 'generalAction') ?>
                    </div>
                    <button type="button" class="load_old_log btn btn-secondary" data-log_type="generalAction">이전 로그 불러오기</button>
                </div>
            </div>
            <div class="col col-12 col-md-6">
                <div class="row gx-0">
                    <div class="col bg1 text-center">
                        <h4 style='color:orange'>전투 기록</h4>
                    </div>
                </div>
                <div class="row gx-0">
                    <div id='battleDetailPlate'>
                        <?= formatHistoryToHTML(getBattleDetailLogRecent($generalID, 24), 'battleDetail') ?>
                    </div>
                    <button type="button" class="load_old_log btn btn-secondary" data-log_type="battleDetail">이전 로그 불러오기</button>
                </div>
            </div>

            <div class="col col-12 col-md-6">
                <div class="row gx-0">
                    <div class="col bg1 text-center">
                        <h4 style='color:skyblue'>장수 열전</h4>
                    </div>
                </div>
                <div class="row gx-0">
                    <?= formatHistoryToHTML(getGeneralHistoryLogAll($generalID)) ?>
                </div>
            </div>
            <div class="col col-12 col-md-6">
                <div class="row gx-0">
                    <div class="col bg1 text-center">
                        <h4 style='color:orange'>전투 결과</h4>
                    </div>
                </div>
                <div class="row gx-0">
                    <div id='battleResultPlate'>
                        <?= formatHistoryToHTML(getBattleResultRecent($generalID, 24), 'battleResult') ?>
                    </div>
                    <button type="button" class="load_old_log btn btn-secondary" data-log_type="battleResult">이전 로그 불러오기</button>
                </div>
            </div>
        </div>
        <div class="row  gx-0">
            <div class="col "><?= backButton() ?></div>
        </div>
        <div class="row bg0 gx-0">
            <div class="col bg0"><?= banner() ?></div>
        </div>
    </div>
</body>

</html>