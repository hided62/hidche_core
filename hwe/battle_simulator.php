<?php

namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$gameStor->cacheValues(['startyear', 'year']);
$startYear = $gameStor->startyear;
$year = $gameStor->year;

$me = $db->queryFirstRow('SELECT no, nation, city FROM general WHERE owner =%i', Session::getUserID());

if ($me) {
    $generalID = $me['no'];
    $nationID = $me['nation'];
    $city = $db->queryFirstRow('SELECT city, level, def, wall FROM city WHERE city = %i', $me['city']);
} else {
    $generalID = 0;
    $nationID = 0;
    $city = [
        'city' => 0,
        'level' => 5,
        'def' => 1000,
        'wall' => 1000,
    ];
}

if ($nationID) {
    $nation = $db->queryFirstRow('SELECT level, type, tech, capital FROM nation WHERE nation = %i', $nationID);
} else {
    $nation = null;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 전투 시뮬레이터</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1100" />
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('dist_css/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printCSS('css/battle_simulator.css') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('dist_js/vendors.js') ?>
    <?= WebUtil::printJS('dist_js/common_ts.js') ?>
    <script>
        var defaultSpecialDomestic = '<?= GameConst::$defaultSpecialDomestic ?>';
        var city = <?= Json::encode($city) ?>;
        var nation = <?= Json::encode($nation) ?>;
    </script>
    <?= WebUtil::printJS('dist_js/battle_simulator.js') ?>
</head>

<body>
    <div id="container">
        <div class="card mb-3">
            <div class="card-header">
                전역 설정
            </div>
            <div class="card-body dragpad_battle">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="input-group">
                            <input type="number" class="form-control" aria-describedby="text_year" value="<?= $startYear ?>" disabled>
                            <div class="input-group-text"> 년 시작 </div>
                            <input type="number" class="form-control" id="year" value="<?= $year ?>" min="<?= $startYear ?>">
                            <div class="input-group-text"> 년 </div>
                            <input type="number" class="form-control" id="month" value="1" min="1" max="12">
                            <div class="input-group-text"> 월 </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="btn-toolbar" role="toolbar">
                            <div class="input-group me-2" role="group">
                                <div class="input-group-text">
                                    반복 횟수
                                </div>
                                <select class="form-select" id="repeat_cnt">
                                    <option value="1">1회 (로그 표기)</option>
                                    <option value="1000">1000회 (요약 표기)</option>
                                </select>
                            </div>
                            <div class="btn-group me-2" role="group">
                                <button type="button" class="btn btn-danger btn-begin_battle">전투</button>
                            </div>
                            <div class="btn-group me-2" role="group">
                                <button type="button" class="btn btn-info btn-battle-save">모두 저장</button>
                                <input type="file" class="form_load_battle_file" accept=".json" style="display: none;" />
                                <button type="button" class="btn btn-primary btn-battle-load">모두 불러오기</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="card mb-2 attacker_nation">
                    <div class="card-header">
                        출병국 설정
                    </div>
                    <div class="card-body nation_detail dragpad_battle">
                        <div class="input-group mb-1">
                            <div class="input-group-text">국가 성향
                            </div>
                            <select class="form-select form_nation_type" style="width:25ch;">
                                <?php foreach (GameConst::$availableNationType as $typeID) : ?>
                                    <?php $nationTypeClass = buildNationTypeClass($typeID) ?>
                                    <option value="<?= $typeID ?>"><?= $nationTypeClass->getName() ?> (<?= $nationTypeClass::$pros ?>, <?= $nationTypeClass::$cons ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">기술 </div>
                            <input type="number" class="form-control form_tech" value="1" min="0" max="12">
                            <div class="input-group-text">등급 </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-text">국가 규모 </div>
                            <select class="form-select form_nation_level">
                                <?php foreach (getNationLevelList() as $nationLevel => [$name, $chiefCnt, $cityCnt]) : ?>
                                    <option value="<?= $nationLevel ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">도시 규모
                            </div>
                            <select class="form-select form_city_level">
                                <?php foreach (getCityLevelList() as $levelID => $name) : ?>
                                    <option value="<?= $levelID ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">수도
                            </div>
                            <div class="input-group-text btn-group btn-group-toggle" data-bs-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="is_attacker_capital" class="form_is_capital" value="1" autocomplete="off">Y
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="is_attacker_capital" class="form_is_capital" value="0" autocomplete="off">N
                                </label>
                            </div>

                        </div>
                        <div class="input-group mb-1">

                        </div>
                    </div>
                </div>
                <div class="card mb-2 attacker_form general_form" data-general_no='1'>
                    <div class="card-header">
                        <div class="float-sm-start" style="line-height:25px;">출병자 설정</div>
                        <div class="float-sm-end btn-toolbar" role="toolbar">
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-success btn-general-import-server">서버에서 가져오기</button>
                            </div>
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-info btn-general-save">저장</button>
                                <input type="file" class="form_load_general_file" accept=".json" style="display: none;" />
                                <button type="button" class="btn btn-primary btn-general-load">불러오기</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- <div class="col-sm"> -->
            <div class="col-sm defender-column">
                <div class="card mb-2 defender_nation">
                    <div class="card-header">
                        수비국 설정
                    </div>
                    <div class="card-body dragpad_battle">
                        <div class="input-group mb-1">
                            <div class="input-group-text">국가 성향
                            </div>
                            <select class="form-select form_nation_type" style="width:25ch;">
                                <?php foreach (GameConst::$availableNationType as $typeID) : ?>
                                    <?php $nationTypeObj = buildNationTypeClass($typeID) ?>
                                    <option value="<?= $typeID ?>"><?= $nationTypeObj->getName() ?> (<?= $nationTypeObj::$pros ?>, <?= $nationTypeObj::$cons ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">기술
                            </div>
                            <input type="number" class="form-control form_tech" value="1" min="0" max="12">
                            <div class="input-group-text">등급
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-text">국가 규모
                            </div>
                            <select class="form-select form_nation_level">
                                <?php foreach (getNationLevelList() as $nationLevel => [$name, $chiefCnt, $cityCnt]) : ?>
                                    <option value="<?= $nationLevel ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">도시 규모
                            </div>
                            <select class="form-select form_city_level">
                                <?php foreach (getCityLevelList() as $levelID => $name) : ?>
                                    <option value="<?= $levelID ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">수도
                            </div>
                            <div class="input-group-text btn-group btn-group-toggle" data-bs-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="is_defender_capital" class="form_is_capital" value="1" autocomplete="off">Y
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="is_defender_capital" class="form_is_capital" value="0" autocomplete="off">N
                                </label>
                            </div>
                        </div>
                        <div class="input-group mb-1">
                            <div class="input-group-text">수비
                            </div>
                            <input type="number" class="form-control form_def" id="city_def" value="1000" min="10" step="10">
                            <div class="input-group-text">성벽
                            </div>
                            <input type="number" class="form-control form_wall" id="city_wall" value="1000" min="0" step="10">
                        </div>
                    </div>
                </div>
                <div class="card mb-2 defender_add_form">
                    <div class="card-header">
                        <div class="float-sm-start" style="line-height:25px;">수비자 설정</div>
                        <div class="float-sm-end btn-toolbar" role="toolbar">
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-dark btn-reorder_defender">수비 순서대로 정렬</button>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-success add-defender">추가</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-2 form_sample">
                    <div class="card-header">
                        <div class="float-sm-start" style="line-height:25px;">수비자 설정</div>
                        <div class="float-sm-end btn-toolbar" role="toolbar">
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-success btn-general-import-server">서버에서 가져오기</button>
                            </div>
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-info btn-general-save">저장</button>
                                <input type="file" class="form_load_general_file" accept=".json" style="display: none;" />
                                <button type="button" class="btn btn-primary btn-general-load">불러오기</button>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-warning copy-defender">복제</button>
                                <button type="button" class="btn btn-danger delete-defender">제거</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body general_detail">
                        <div class="input-group mb-3">
                            <div class="input-group-text">이름
                            </div>
                            <input type="text" class="form-control form_general_name" value="무명" style="width:15ch;">
                            <div class="input-group-text">직위
                            </div>
                            <select class="form-select form_officer_level" style="width:8ch;">
                                <option value="1">일반</option>
                                <option value="4">태수</option>
                                <option value="3">군사</option>
                                <option value="2">종사</option>
                                <option value="10">무장 수뇌</option>
                                <option value="9">지장 수뇌</option>
                                <option value="11">참모</option>
                                <option value="12">군주</option>
                            </select>
                            <div class="input-group-text">Level
                            </div>
                            <input type="number" class="form-control form_exp_level" value="20" min="0" max="300" step="1">

                        </div>
                        <div class="input-group mb-1">
                            <div class="input-group-text">통솔
                            </div>
                            <input type="number" class="form-control form_leadership" value="50" min="1" max="300" step="1">
                            <div class="input-group-text">무력
                            </div>
                            <input type="number" class="form-control form_strength" value="50" min="1" max="300" step="1">
                            <div class="input-group-text">지력
                            </div>
                            <input type="number" class="form-control form_intel" value="50" min="1" max="300" step="1">
                        </div>
                        <div class="input-group mb-1">
                            <div class="input-group-text">명마
                            </div>
                            <select class="form-select form_general_horse">
                                <option value='None'>-</option>
                                <?php foreach (GameConst::$allItems['horse'] as $horseID => $cnt) : ?>
                                    <option value="<?= $horseID ?>"><?= getItemName($horseID) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">무기
                            </div>
                            <select class="form-select form_general_weap">
                                <option value='None'>-</option>
                                <?php foreach (GameConst::$allItems['weapon'] as $weaponID => $cnt) : ?>
                                    <option value="<?= $weaponID ?>"><?= getItemName($weaponID) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">서적
                            </div>
                            <select class="form-select form_general_book">
                                <option value='None'>-</option>
                                <?php foreach (GameConst::$allItems['book'] as $bookID => $cnt) : ?>
                                    <option value="<?= $bookID ?>"><?= getItemName($bookID) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-text">부상
                            </div>
                            <input type="number" class="form-control form_injury" value="0" min="0" max="80" step="1">
                            <div class="input-group-text">%(<span class="injury_helptext">건강</span>)
                            </div>
                            <div class="input-group-text">군량
                            </div>
                            <input type="number" class="form-control form_rice" value="5000" min="50" max="40000" step="50">
                            <div class="input-group-text">도구
                            </div>
                            <select class="form-select form_general_item">
                                <option value='None'>-</option>
                                <?php foreach (GameConst::$allItems['item'] as $itemID => $cnt) : ?>
                                    <option value="<?= $itemID ?>"><?= getItemName($itemID) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group mb-1">
                            <div class="input-group-text">병종
                            </div>
                            <select class="form-select form_crewtype">
                                <?php foreach (GameUnitConst::all() as $crewTypeID => $crewType) : ?>
                                    <?php if ($crewType->armType === GameUnitConst::T_CASTLE) {
                                        continue;
                                    } ?>
                                    <option value="<?= $crewTypeID ?>"><?= $crewType->name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">병사
                            </div>
                            <input type="number" class="form-control form_crew" value="7000" min="100" step="100">
                            <div class="input-group-text">성격
                            </div>
                            <select class="form-select form_general_character">
                                <?php foreach (getCharacterList(false) as $characterID => [$name, $info]) : ?>
                                    <option value="<?= $characterID ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-text">훈련
                            </div>
                            <input type="number" class="form-control form_train" value="100" min="40" max="<?= GameConst::$maxTrainByWar ?>" step="1">
                            <div class="input-group-text">사기
                            </div>
                            <input type="number" class="form-control form_atmos" value="100" min="40" max="<?= GameConst::$maxAtmosByWar ?>" step="1">
                            <div class="input-group-text">전특
                            </div>
                            <select class="form-select form_general_special_war">
                                <?php foreach (SpecialityHelper::getSpecialWarList(false) as $specialWarID => $specialObj) : ?>
                                    <option value="<?= $specialWarID ?>"><?= $specialObj->getName() ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group mb-1">
                            <div class="input-group-text">보병숙련
                            </div>
                            <select class="form-select form_dex1">
                                <?php foreach (getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]) : ?>
                                    <option value="<?= $dexAmount ?>"><?= "{$name} (" . number_format($dexAmount) . ")" ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">궁병숙련
                            </div>
                            <select class="form-select form_dex2">
                                <?php foreach (getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]) : ?>
                                    <option value="<?= $dexAmount ?>"><?= "{$name} (" . number_format($dexAmount) . ")" ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">기병숙련
                            </div>
                            <select class="form-select form_dex3">
                                <?php foreach (getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]) : ?>
                                    <option value="<?= $dexAmount ?>"><?= "{$name} (" . number_format($dexAmount) . ")" ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-text">귀병숙련
                            </div>
                            <select class="form-select form_dex4">
                                <?php foreach (getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]) : ?>
                                    <option value="<?= $dexAmount ?>"><?= "{$name} (" . number_format($dexAmount) . ")" ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text">차병숙련
                            </div>
                            <select class="form-select form_dex5">
                                <?php foreach (getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]) : ?>
                                    <option value="<?= $dexAmount ?>"><?= "{$name} (" . number_format($dexAmount) . ")" ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-text only_defender">수비여부
                            </div>
                            <select class="form-select form_defence_train only_defender">
                                <option value="90">훈사 90</option>
                                <option value="80">훈사 80</option>
                                <option value="60">훈사 60</option>
                                <option value="40">훈사 40</option>
                                <option value="999">안함</option>
                            </select>
                        </div>
                        <div class="input-group mb-1">
                            <div class="input-group-text">전투 수
                            </div>
                            <input type="number" class="form-control form_warnum" value="0" step="1">
                            <div class="input-group-text">승리 수
                            </div>
                            <input type="number" class="form-control form_killnum" value="0" step="1">
                            <div class="input-group-text">사살 수
                            </div>
                            <input type="number" class="form-control form_killcrew" value="0" step="1">
                        </div>
                    </div>
                </div>


            </div><!-- <div class="col-sm"> -->
        </div>
        <div class="card mb-3">
            <div class="card-header">
                전투 요약
            </div>
            <table class="table">
                <tbody id="battle_result_summary">
                    <tr>
                        <th style='width:18ch;'>전투 일시</th>
                        <td id='result_datetime'></td>
                    </tr>
                    <tr>
                        <th>전투 횟수</th>
                        <td id='result_warcnt'></td>
                    </tr>
                    <tr>
                        <th>전투 페이즈</th>
                        <td id='result_phase'></td>
                    </tr>
                    <tr>
                        <th>준 피해</th>
                        <td><span id='result_killed'>0</span><span id='result_varKilled'> (<span id='result_minKilled'>0</span> ~ <span id='result_maxKilled'>0</span>)</span></td>
                    </tr>
                    <tr>
                        <th>받은 피해</th>
                        <td><span id='result_dead'>0</span><span id='result_varDead'> (<span id='result_minDead'>0</span> ~ <span id='result_maxDead'>0</span>)</span></td>
                    </tr>
                    <tr>
                        <th>출병자 군량 소모</th>
                        <td id='result_attackerRice'></td>
                    </tr>
                    <tr>
                        <th>수비자 군량 소모</th>
                        <td id='result_defenderRice'></td>
                    </tr>
                    <tr>
                        <th>공격자 스킬</th>
                        <td id='result_attackerSkills'></td>
                    </tr>
                    <tr class='result_defenderSkills'>
                        <th>수비자1 스킬</th>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-sm">
                <div class="card mb-3">
                    <div class="card-header">
                        마지막 전투 로그
                    </div>
                    <div class="card-body" id="generalBattleResultLog">

                    </div>
                </div>
            </div>
            <div class="col-sm">
                <div class="card mb-3">
                    <div class="card-header">
                        마지막 전투 상세 로그
                    </div>
                    <div class="card-body" id="generalBattleDetailLog">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="importModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">장수 목록</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <select id="modalSelector"></select>
                    <p>타국 장수를 선택한 경우 숙련과 아이템은 0으로 초기화됩니다.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='importFromDB'>가져오기</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>