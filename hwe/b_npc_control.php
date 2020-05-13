<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gameStor->cacheValues(['autorun_user', 'develcost']);

increaseRefresh("NPC 정책", 1);


$me = $db->queryFirstRow('SELECT no, npc, nation, city, officer_level, con, turntime, belong, permission, penalty FROM general WHERE owner=%i', $userID);

$nationID = $me['nation'];
$nation = $db->queryFirstRow('SELECT nation,level,name,color,type,gold,rice,bill,tech,rate,scout,war,secretlimit,capital FROM nation WHERE nation = %i', $nationID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if ($permission < 0) {
    echo '국가에 소속되어있지 않습니다.';
    die();
} else if ($permission < 1) {
    echo "권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.";
    die();
}


$nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
$nationStor->cacheValues(['npc_nation_policy', 'npc_general_policy']);
$gameStor->cacheAll();

$general = new General($me, null, null, $nation, $gameStor->year, $gameStor->month, false);

$rawServerPolicy = $gameStor->getValue('npc_nation_policy') ?? [];
$rawNationPolicy = $nationStor->getValue('npc_nation_policy') ?? [];
$rawServerGeneralPolicy = $gameStor->getValue('npc_general_policy') ?? [];
$rawNationGeneralPolicy = $nationStor->getValue('npc_general_policy') ?? [];

$defaultNationPolicy = ($rawServerPolicy['values'] ?? []) + AutorunNationPolicy::$defaultPolicy;
$currentNationPolicy = ($rawNationPolicy['values'] ?? []) + $defaultNationPolicy;

$defaultNationPriority = $rawServerPolicy['priority'] ?? (AutorunNationPolicy::$defaultPriority);
$currentNationPriority = $rawNationPolicy['priority'] ?? $defaultNationPriority;

$defaultGeneralActionPriority = $rawServerGeneralPolicy['priority'] ?? (AutorunGeneralPolicy::$default_priority);
$currentGeneralActionPriority = $rawNationGeneralPolicy['priority'] ?? $defaultGeneralActionPriority;

$autoPolicyVariable = [];
if ($currentNationPolicy['reqHumanWarUrgentRice'] ?? 0) {
    $autoPolicyVariable['reqHumanWarUrgentRice'] = $currentNationPolicy['reqHumanWarUrgentRice'];
}
if ($currentNationPolicy['reqHumanWarUrgentGold'] ?? 0) {
    $autoPolicyVariable['reqHumanWarUrgentGold'] = $currentNationPolicy['reqHumanWarUrgentGold'];
}
$autoPolicy = new AutorunNationPolicy($general, ($gameStor->autorun_user)['options'], ['values' => $autoPolicyVariable], null, $nation, $gameStor->getAll(true));


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 임시 NPC 정책</title>
    <script>
        var nationID = <?= $nationID ?>;

        var defaultNationPolicy = <?= Json::encode($defaultNationPolicy) ?>;
        var currentNationPolicy = <?= Json::encode($currentNationPolicy) ?>;

        var defaultNationPriority = <?= Json::encode($defaultNationPriority) ?>;
        var currentNationPriority = <?= Json::encode($currentNationPriority) ?>;
        var availableNationPriorityItems = <?= Json::encode(AutorunNationPolicy::$defaultPriority) ?>;

        var defaultGeneralActionPriority = <?= Json::encode($defaultGeneralActionPriority) ?>;
        var currentGeneralActionPriority = <?= Json::encode($currentGeneralActionPriority) ?>;
        var availableGeneralActionPriorityItems = <?= Json::encode(AutorunGeneralPolicy::$default_priority) ?>;

        var btnHelpMessage = {
            '선전포고': '군주가 NPC이고, 전쟁중이 아닐 때,<br>주변국중 하나를 골라 선포합니다.<br><br>선포 시점은 다음을 참고합니다.<br>- 인구율<br>- 도시내정률<br>- NPC전투장권장 금 충족률<br>- NPC전투장권장 쌀 충족률<br><br>국력이 낮은 국가를 조금 더 선호합니다.',
            '천도': '인구가 많은 곳을 찾아 천도를 시도합니다.<br>영토의 가운데를 선호합니다.<br><br>도시 인구가 충분하다면, 굳이 천도하지는 않습니다.',
            '유저장긴급포상': '금/쌀이 부족한 유저전투장에게 긴급하게 포상합니다.<br>국고가 권장량보다 적어지더라도 시도합니다.',
            '부대전방발령': '(작동하지 않음)<br>전투 부대를 접경으로 발령합니다.<br>수도->시작점->도착점 경로를 따릅니다.',
            '유저장구출발령': '아군 영토에 있지 않은 유저장을 아군 영토로 발령합니다.<br>곧 집합하는 부대에 탑승한 경우는 제외합니다.',
            '유저장후방발령': '유저전투장 중에<br>- 병력이 충분하지 않고,<br>- 도시의 인구가 제자리 징병할 수 있을 정도로 충분하지 않고,<br>- 부대에 탑승하지 않았다면,<br>인구가 충분한 후방도시로 발령합니다.',
            '부대유저장후방발령': '접경에 위치한 부대에 탑승한 유저전투장 중에,<br>- 병력이 충분하지 않고,<br>- 첫 턴이 징병턴이며,<br>- 부대장 집합 턴 사이라면,<br>인구가 충분한 후방도시로 발령합니다.<br><br>부대장의 위치와 유저장의 위치가 다르다면 발령하지 않습니다.',
            '유저장전방발령': '후방에 있는 유저장이<br>- 병력을 가지고 있으며,<br>- 곧 훈련/사기진작이 완료될 것 같으면,<br>전방으로 발령합니다.<br><br>도시 관직이 많이 임명된 도시를 선호합니다.',
            '유저장포상': '금/쌀이 부족한 유저장에게 포상합니다.<br>유저전투장과 유저내정장은 각각 기준을 따릅니다.<br>국고 권장량을 가급적 지킵니다.',
            '부대후방발령': '(작동하지 않음)<br>후방 부대가 위치한 도시의 인구가 충분하지 않을 경우,<br>인구가 충분한 도시로 발령합니다.',
            '부대구출발령': '전투 부대, 후방 부대가 아닌 부대가 아군 영토에 있지 않을 때,<br>전방 도시 중 하나를 골라 발령합니다.',
            'NPC긴급포상': '금/쌀이 부족한 NPC전투장에게 긴급하게 포상합니다.<br>국고가 권장량보다 \'약간\' 적어지더라도 시도합니다.',
            'NPC구출발령': '아군 영토에 있지 않은 NPC장을 아군 영토로 발령합니다.',
            'NPC후방발령': 'NPC전투장 중에<br>- 병력이 충분하지 않고,<br>- 도시의 인구가 제자리 징병할 수 있을 정도로 충분하지 않고,<br>- 부대에 탑승하지 않았다면,<br>인구가 충분한 후방도시로 발령합니다.',
            'NPC포상': '금/쌀이 부족한 유저장에게 포상합니다.<br>NPC전투장과 NPC내정장은 각각 기준을 따릅니다.<br>국고 권장량을 가급적 지킵니다.',
            'NPC전방발령': '후방에 있는 유저장이<br>- 병력을 가지고 있으며,<br>- 곧 훈련/사기진작이 완료될 것 같으면,<br>전방으로 발령합니다.<br><br>도시 관직이 많이 임명된 도시를 선호합니다.',
            '유저장내정발령': '내정중인 유저장이 위치한 도시의 내정률이 95% 이상이면<br>개발되지 않은 도시로 발령합니다.',
            'NPC내정발령': '내정중인 NPC장이 위치한 도시의 내정률이 95% 이상이면<br>개발되지 않은 도시로 발령합니다.',
            'NPC몰수': '국고가 부족하다면 NPC에게서 몰수합니다. 내정NPC장은 국고가 부족하지 않아도 몰수합니다.',

            'NPC사망대비': 'NPC의 사망까지 5턴 이내인 경우, 헌납합니다.<br>헌납할 금쌀이 없다면 물자조달을 수행합니다.',
            '귀환': '아국 도시에 있지 않다면 귀환합니다.',
            '금쌀구매': '전쟁 중에 금쌀의 비율이 크게 차이난다면 금쌀을 거래하여 비슷하게 맞춥니다.<br>금쌀 비율이 적절하는지 판단하는데 살상률을 포함합니다.<br>NPC는 상인이 없어도 금쌀을 구매할 수 있습니다.<br><br>또는 금쌀 한쪽이 지나치게 적은 경우에는 내정 중에도 금쌀을 거래합니다.',
            '출병': '충분한 병력과 충분한 훈련/사기를 가지고 있는 경우 출병합니다.<br>접경이 여럿인 경우 무작위로 선택합니다.<br><br>타국과 전쟁중인 경우 공백지로는 출병하지 않습니다.',
            '긴급내정': '전쟁중에 민심이 70 미만이거나,<br>인구가 제자리 징병이 가능하지 않을 정도로 적을 경우,<br>일정확률로 주민선정과 정착장려를 수행합니다.<br><br>통솔이 높을 수록 수행할 확률이 높습니다.',
            '전투준비': '충분한 병력을 가지고 있지만 훈련과 사기가 부족한 경우 훈련과 사기진작을 수행합니다.',
            '전방워프': '전투장이 충분한 병력을 가지고 있다면 전방으로 이동합니다.',
            'NPC헌납': '국고가 부족한데 NPC장수가 충분한 금쌀을 가지고 있다면 일부를 헌납합니다. <br>NPC내정장은 국고가 넉넉하더라도 충분한 금쌀을 가지고 있다면 일부를 헌납합니다.',
            '징병': '전쟁 중 병력을 소진하였다면 재 징병합니다.<br><br>기존에 사용한 병종군 중에서 사용가능한 병종을 랜덤하게 선택합니다.<br>고급 병종을 선택할 확률이 조금 더 높습니다.<br><br>NPC의 경우 도시의 인구가 충분하지 않다면 징병을 할 확률이 감소합니다.<br><br>유저장은 최대한 고급병종을 유지하며,<br>유저장 모병이 허용되는 경우 모병을 3회할 수 있다면 모병합니다.',
            '후방워프': '전쟁 중 병력을 소진하였는데 도시의 인구가 충분하지 않다면,<br>인구가 많은 도시로 이동합니다.',
            '전쟁내정': '전쟁 중 수행하는 내정입니다.<br>정착장려, 기술연구의 확률이 좀 더 높고,<br>치안강화, 농지개간, 상업투자의 확률이 낮습니다.<br><br>내정이 가능하다 하더라도 전시임을 고려해,<br>30% 확률로 다른 턴을 수행합니다.',
            '소집해제': '전쟁 중이 아닌 데 병력이 남아있는 경우,<br>3/4 확률로 소집해제합니다.',
            '일반내정': '도시에서 내정을 수행합니다. 낮은 내정일 수록 수행할 확률이 높습니다.<br>기술 연구는 1등급 이상 뒤쳐지지 않도록 노력합니다.',
            '내정워프': '도시에서 더이상 내정을 수행할 수 없는 경우,<br>일정확률로 내정이 부족한 다른 도시로 이동합니다.',
        };
    </script>
    <?= WebUtil::printJS('../e_lib/jquery-3.3.1.min.js') ?>
    <?= WebUtil::printJS('../e_lib/Sortable.min.js') ?>
    <?= WebUtil::printJS('../e_lib/jquery-sortable.js') ?>
    <?= WebUtil::printJS('../e_lib/bootstrap.bundle.min.js') ?>
    <?= WebUtil::printJS('../e_lib/jquery_toast/toast.js') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('js/common.js') ?>
    <?= WebUtil::printJS('js/npc_control.js') ?>

    <?= WebUtil::printCSS('../e_lib/bootstrap.min.css') ?>
    <?= WebUtil::printCSS('../e_lib/font_awesome/css/all.min.css') ?>
    <?= WebUtil::printCSS('../e_lib/jquery_toast/toast.css') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('css/common.css') ?>
    <?= WebUtil::printCSS('css/npc_control.css') ?>
</head>

<body>
    <div id='container' class='tb_layout bg0' style='width:1000px;margin:auto;border:solid 1px #888888;'>
        <div class='tb_layout bg0'>임시 NPC 정책<br>
            <?= backButton() ?></div>

        <div class='bg1 section_bar'>국가 정책</div>
        <div class="text-right px-3"><small class="form-text text-muted">
                최근 설정: <?= $rawNationPolicy['valueSetter'] ?? '-없음-' ?> (<?= $rawNationPolicy['valueSetTime'] ?? '설정 기록 없음' ?>)
            </small></div>
        <div class="form_list">
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqNationGold" class="col-sm-6 col-form-label">국가 권장 금</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqNationGold" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상 제외)</small></div>
                </div>

                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqNationRice" class="col-sm-6 col-form-label">국가 권장 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqNationRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">이보다 많으면 포상, 적으면 몰수/헌납합니다.(긴급포상 제외)</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanWarUrgentGold" class="col-sm-6 col-form-label">유저전투장 긴급포상 금</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanWarUrgentGold" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">
                            유저장긴급포상시 이보다 금이 적은 장수에게 포상합니다.<br>
                            0이면 보병 6회 징병(<?= number_format(GameConst::$defaultStatMax * 100) ?> * 6) 가능한 금을 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqHumanWarUrgentGold) ?>입니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanWarUrgentRice" class="col-sm-6 col-form-label">유저전투장 긴급포상 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanWarUrgentRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저장긴급포상시 이보다 쌀이 적은 장수에게 포상합니다.<br>0이면 기본 병종으로 <?= number_format(GameConst::$defaultStatMax * 100 * 6) ?>명 사살 가능한 쌀을 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqHumanWarUrgentRice) ?>입니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanWarRecommandGold" class="col-sm-6 col-form-label">유저전투장 권장 금</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanWarRecommandGold" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저전투장에게 주는 금입니다. 이보다 적으면 포상합니다. <br>
                            0이면 유저전투장 긴급포상 금의 3배를 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqHumanWarRecommandGold) ?>입니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanWarRecommandRice" class="col-sm-6 col-form-label">유저전투장 권장 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanWarRecommandRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다. <br>
                            0이면 유저전투장 긴급포상 쌀의 3배를 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqHumanWarRecommandRice) ?>입니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanDevelGold" class="col-sm-6 col-form-label">유저내정장 권장 금</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanDevelGold" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저내정장에게 주는 금입니다. 이보다 적으면 포상합니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanDevelRice" class="col-sm-6 col-form-label">유저내정장 권장 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanDevelRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저내정장에게 주는 쌀입니다. 이보다 적으면 포상합니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqNPCWarGold" class="col-sm-6 col-form-label">NPC전투장 권장 금</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqNPCWarGold" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">NPC전투장에게 주는 금입니다. 이보다 적으면 포상합니다. <br>
                            0이면 기본 병종 4회(<?= number_format(GameConst::$defaultStatNPCMax * 100) ?> * 4) 징병비를 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqNPCWarGold) ?>입니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqNPCWarRice" class="col-sm-6 col-form-label">NPC전투장 권장 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqNPCWarRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">NPC전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다. <br>
                            0이면 기본 병종으로 <?= number_format(GameConst::$defaultStatNPCMax * 100 * 4) ?>명 사살 가능한 쌀을 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqNPCWarRice) ?>입니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqNPCDevelGold" class="col-sm-6 col-form-label">NPC내정장 권장 금</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqNPCDevelGold" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">NPC내정장에게 주는 금입니다. 이보다 5배 더 많다면 헌납합니다.<br>0이면 30턴 내정 가능한 금을 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqNPCDevelGold) ?>입니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqNPCDevelRice" class="col-sm-6 col-form-label">NPC내정장 권장 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqNPCDevelRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">NPC내정장에게 주는 쌀입니다. 이보다 5배 더 많다면 헌납합니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="minimumResourceActionAmount" class="col-sm-6 col-form-label">포상/몰수/헌납/삼/팜 최소 단위</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="minimumResourceActionAmount" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">연산결과가 이 단위보다 적다면 수행하지 않습니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="minWarCrew" class="col-sm-6 col-form-label">최소 전투 가능 병력 수</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="minWarCrew" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">이보다 적을 때에는 징병을 시도합니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="minNPCRecruitCityPopulation" class="col-sm-6 col-form-label">NPC 최소 징병 가능 인구 수</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="minNPCRecruitCityPopulation" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">이보다 낮으면 NPC는 도시에서 징병하지 않고 후방 워프합니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="safeRecruitCityPopulationRatio" class="col-sm-6 col-form-label">제자리 징병 허용 인구율(%)</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="percent" id="safeRecruitCityPopulationRatio" min="0" max="100" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">전쟁 시 후방 발령, 후방 워프의 기준 인구입니다. 이보다 많다면 '충분하다'고 판단합니다.</small></div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6">
                    <div class="row">
                        <label for="minNPCWarLeadership" class="col-sm-6 col-form-label">NPC 전투 참여 통솔 기준</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="minNPCWarLeadership" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">이 수치보다 같거나 높으면 NPC전투장으로 분류됩니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="properWarTrainAtmos" class="col-sm-6 col-form-label">훈련/사기진작 목표치</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="properWarTrainAtmos" min="0" max="100" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">훈련/사기진작 기준치입니다. 이보다 같거나 높으면 출병합니다.</small></div>
                </div>
            </div>
            <div class="alert alert-secondary">
                전투 부대는 작업중입니다(json양식: {부대번호:[시작도시번호(아국),도착도시번호(적군)],...})<br>
                후방 징병 부대는 작업중입니다(json양식: [부대번호,...])<br>
                내정 부대는 작업중입니다(json양식: [부대번호,...])
                <input type="hidden" value="{}" data-type="json" id="CombatForce">
                <input type="hidden" value="[]" data-type="json" id="SupportForce">
                <input type="hidden" value="[]" data-type="json" id="DevelopForce">
                <input type="hidden" value="true" data-type="json" id="allowNpcAttackCity">
                <!--allowNpcAttackCity는 현재 게임 내 비활성-->
            </div>
            <div class='control_bar' data-type="nationPolicy">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-dark reset_btn">초기값으로</button>
                    <button type="button" class="btn btn-secondary revert_btn">이전값으로</button>
                </div><button type="button" class="btn btn-primary submit_btn">설정</button>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 half_section_left">
                <div class='bg1 section_bar'>NPC 사령턴 우선순위</div>
                <div class="float-right px-3"><small class="form-text text-muted">
                        최근 설정: <?= $rawNationPolicy['prioritySetter'] ?? '-없음-' ?> (<?= $rawNationPolicy['prioritySetTime'] ?? '설정 기록 없음' ?>)
                    </small></div>
                <div class='text-left px-2'><small class="text-muted">예턴이 없거나, 지정되어 있더라도 실패할경우<br>아래 순위에 따라 사령턴을 시도합니다.</small></div>
                <div class="form_list">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="bg2 sub_bar">비활성</div>
                            <div id="nationPriorityDisabled" class="list-group col" data-type="list">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg2 sub_bar">활성</div>
                            <div id="nationPriority" class="list-group col" data-type="list">
                            </div>
                        </div>
                    </div>
                    <div class='control_bar' data-type="nationPriority">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-dark reset_btn">초기값으로</button>
                            <button type="button" class="btn btn-secondary revert_btn">이전값으로</button>
                        </div><button type="button" class="btn btn-primary submit_btn">설정</button>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 half_section_right">
                <div class='bg1 section_bar'>NPC 일반턴 우선순위</div>
                <div class="float-right px-3"><small class="form-text text-muted">
                        최근 설정: <?= $rawNationGeneralPolicy['prioritySetter'] ?? '-없음-' ?> (<?= $rawNationGeneralPolicy['prioritySetTime'] ?? '설정 기록 없음' ?>)
                    </small></div>
                <div class='text-left px-2'><small class="text-muted">순위가 높은 것부터 시도합니다. <br>아무것도 실행할 수 없으면 물자조달이나 인재탐색을 합니다.</small></div>
                <div class="form_list">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="bg2 sub_bar">비활성</div>
                            <div id="generalPriorityDisabled" class="list-group col" data-type="list">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg2 sub_bar">활성</div>
                            <div id="generalPriority" class="list-group col" data-type="list">
                            </div>
                        </div>
                    </div>
                    <div class='control_bar' data-type="generalPriority">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-dark reset_btn">초기값으로</button>
                            <button type="button" class="btn btn-secondary revert_btn">이전값으로</button>
                        </div><button type="button" class="btn btn-primary submit_btn">설정</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>