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
$zeroPolicy = new AutorunNationPolicy($general, ($gameStor->autorun_user)['options'], null, null, $nation, $gameStor->getAll(true));


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 임시 NPC 정책</title>
<?=WebUtil::printStaticValues([
    'nationID'=>$nationID,
    'defaultNationPolicy'=>$defaultNationPolicy,
    'currentNationPolicy'=>$currentNationPolicy,

    'defaultNationPriority'=>$defaultNationPriority,
    'currentNationPriority'=>$currentNationPriority,
    'availableNationPriorityItems'=>AutorunNationPolicy::$defaultPriority,

    'defaultGeneralActionPriority'=>$defaultGeneralActionPriority,
    'currentGeneralActionPriority'=>$currentGeneralActionPriority,
    'availableGeneralActionPriorityItems'=>AutorunGeneralPolicy::$default_priority,

    'defaultStatNPCMax'=>GameConst::$defaultStatNPCMax,
])?>
    <?=WebUtil::printJS('../d_shared/common_path.js')?>
    <?=WebUtil::printJS('dist_js/vendors.js')?>
    <?=WebUtil::printJS('dist_js/common.js')?>
    <?= WebUtil::printJS('../e_lib/jquery_toast/toast.js') ?>
    <?= WebUtil::printJS('../e_lib/Sortable.min.js') ?>
    <?= WebUtil::printJS('../e_lib/jquery-sortable.js') ?>
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
                            0이면 보병 6회 징병(<?= number_format(GameConst::$defaultStatMax * 100) ?> * 6) 가능한 금을 기준으로 하며, 그 수치는 현재 <?= number_format($zeroPolicy->reqHumanWarUrgentGold) ?>입니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanWarUrgentRice" class="col-sm-6 col-form-label">유저전투장 긴급포상 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanWarUrgentRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저장긴급포상시 이보다 쌀이 적은 장수에게 포상합니다.<br>0이면 기본 병종으로 <?= number_format(GameConst::$defaultStatMax * 100 * 6) ?>명 사살 가능한 쌀을 기준으로 하며, 그 수치는 현재 <?= number_format($zeroPolicy->reqHumanWarUrgentRice) ?>입니다.</small></div>
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
                            0이면 유저전투장 긴급포상 금의 2배를 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqHumanWarRecommandGold) ?>입니다.</small></div>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label for="reqHumanWarRecommandRice" class="col-sm-6 col-form-label">유저전투장 권장 쌀</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" data-type="integer" id="reqHumanWarRecommandRice" min="0" value="0">
                        </div>
                    </div>
                    <div style='text-align:right;'><small class="form-text text-muted">유저전투장에게 주는 쌀입니다. 이보다 적으면 포상합니다. <br>
                            0이면 유저전투장 긴급포상 쌀의 2배를 기준으로 하며, 그 수치는 현재 <?= number_format($autoPolicy->reqHumanWarRecommandRice) ?>입니다.</small></div>
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