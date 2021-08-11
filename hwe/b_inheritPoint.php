<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$generalID = $session->generalID;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$me = General::createGeneralObjFromDB($generalID);


$pointHelpText = [
    'sum' => '다음 플레이에서 사용할 수 있는 총 포인트입니다.',
    'new' => '이번 플레이에서 얻은 총 포인트입니다.',
    'previous' => '이전에 물려받은 포인트입니다.',
    'lived_month' => '살아남은 기간입니다. (1개월 단위)',
    'max_belong' => '가장 오래 임관했던 국가의 연도입니다.',
    'max_domestic_critical' => '성공한 내정 중 최대 연속값입니다.',
    'snipe_combat' => '유리한 상성을 가지고 전투했습니다.',
    'combat' => '전투 횟수입니다.',
    'sabotage' => '계략 성공 횟수입니다.',
    'unifier' => '천통에 기여한 포인트입니다. <br>각 국의 군주, 천통 수뇌, 천통 군주가 받습니다.',
    'dex' => '총 숙련도합입니다.',
    'tournament' => '토너먼트 입상 포인트입니다.',
    'betting' => '성공적인 베팅을 했습니다. <br>수익율과 베팅 성공 횟수를 따릅니다.',
];
$items = [];
foreach(array_keys(General::INHERITANCE_KEY) as $key){
    $items[$key] = $me->getInheritancePoint($key)??0;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 유산 관리</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printCSS('../e_lib/bootstrap.min.css') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('../css/config.css') ?>
    <?= WebUtil::printCSS('css/common.css') ?>
    <?= WebUtil::printCSS('css/inheritPoint.css') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('../e_lib/jquery-3.3.1.min.js') ?>
    <?= WebUtil::printJS('../e_lib/bootstrap.bundle.min.js') ?>
    <?= WebUtil::printJS('js/common.js') ?>
    <?= WebUtil::printJS('js/inheritPoint.js') ?>
<script>
    var items = <?=Json::encode($items)?>;
    var helpText = <?=Json::encode($pointHelpText)?>;
</script>
</head>

<body onload="formStart()">

    <table style='width:1000px;margin:auto;' class='tb_layout bg0'>
        <tr>
            <td style='text-align:left;'>유산 관리<br><?= backButton() ?></td>
        </tr>
    </table>
    <div id="container" class="tb_layout bg0" style="width:1000px;margin:auto;border:solid 1px #888888;">
        <div id='inheritance_list'>
            <div id="inherit_sum" class='inherit_item'>
                <div class="row">
                    <label id="inherit_sum_head" class='inherit_head col-sm-6 col-form-label'>총 포인트</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control inherit_value" readonly id="inherit_sum_value" value="">
                    </div>
                </div>

                <div style="text-align:right">
                    <small class="form-text text-muted"></small>
                </div>
            </div>

            <div id="inherit_previous" class='inherit_item'>
                <div class="row">
                    <label id="inherit_sum_head" class='inherit_head col-sm-6 col-form-label'>기존 포인트</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control inherit_value" readonly id="inherit_previous_value" value="">
                    </div>
                </div>

                <div style="text-align:right">
                    <small class="form-text text-muted"></small>
                </div>
            </div>

            <div id="inherit_new" class='inherit_item'>
                <div class="row">
                    <label id="inherit_sum_head" class='inherit_head col-sm-6 col-form-label'>신규 포인트</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control inherit_value" readonly id="inherit_new_value" value="">
                    </div>
                </div>

                <div style="text-align:right">
                    <small class="form-text text-muted"></small>
                </div>
            </div>
            <div style="width:100%; padding:0 10px"><hr style="border-top:1px solid #888888;"></div>
            <?php foreach (General::INHERITANCE_KEY as $key => [$type, $multiplier, $name]) : ?>
                <?php if ($key == 'previous') {
                    continue;
                } ?>
                <div id="inherit_<?= $key ?>" class='inherit_item inherit_template_item'>
                    <div class="row">
                        <label id="inherit_<?= $key ?>_head" class='inherit_head col-sm-6 col-form-label'><?= $name ?></label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control inherit_value" readonly id="inherit_<?= $key ?>_value" value="">
                        </div>
                    </div>

                    <div style="text-align:right">
                        <small class="form-text text-muted"></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>