<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();
$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$startYear = $gameStor->getValue('startyear');

?>
<!DOCTYPE html>
<html>
<head>
<title><?=UniqueConst::$serverName?>: 전투 시뮬레이터</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1100" />
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/battle_simulator.css')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/moment.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../e_lib/download2.js')?>
<?=WebUtil::printJS('js/common.js')?>
<script>
var defaultSpecialDomestic = '<?=GameConst::$defaultSpecialDomestic?>';
</script>
<?=WebUtil::printJS('js/battle_simulator.js')?>
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
                    <input type="number" class="form-control" aria-describedby="text_year" value="<?=$startYear?>" disabled>
                    <div class="input-group-append">
                        <span class="input-group-text">년 시작</span>
                    </div>
                    <input type="number" class="form-control" id="year" value="<?=$startYear+3?>" min="<?=$startYear?>">
                    <div class="input-group-append">
                        <span class="input-group-text">년</span>
                    </div>
                    <input type="number" class="form-control" id="month" value="1" min="1" max="12">
                    <div class="input-group-append">
                        <span class="input-group-text">월</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="btn-toolbar" role="toolbar">
                    <div class="input-group mr-2" role="group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">반복 횟수</span>
                        </div>
                        <select class="custom-select" id="repeat_cnt">
                            <option value="1">1회 (로그 표기)</option>
                            <option value="1000">1000회 (요약 표기)</option>
                        </select>
                    </div>
                    <div class="btn-group mr-2" role="group">
                        <button type="button" class="btn btn-danger btn-begin_battle">전투</button>
                    </div>
                    <div class="btn-group mr-2" role="group">
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
                    <div class="input-group-prepend">
                        <span class="input-group-text">국가 성향</span>
                    </div>
                    <select class="custom-select form_nation_type" style="width:25ch;">
                        <?php foreach(GameConst::$availableNationType as $typeID): ?>
                            <?php $nationTypeClass = buildNationTypeClass($typeID) ?> 
                            <option value="<?=$typeID?>"><?=$nationTypeClass->getName()?> (<?=$nationTypeClass::$pros?>, <?=$nationTypeClass::$cons?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">기술</span>
                    </div>
                    <input type="number" class="form-control form_tech" value="1" min="0" max="12">
                    <div class="input-group-append">
                        <span class="input-group-text">등급</span>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">국가 규모</span>
                    </div>
                    <select class="custom-select form_nation_level">
                        <?php foreach(getNationLevelList() as $nationLevel => [$name,$chiefCnt,$cityCnt]): ?>
                            <option value="<?=$nationLevel?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">도시 규모</span>
                    </div>
                    <select class="custom-select form_city_level">
                        <?php foreach(getCityLevelList() as $levelID => $name): ?>
                            <option value="<?=$levelID?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">수도</span>
                    </div>
                    <div class="input-group-append btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-secondary">
                            <input type="radio" name="is_attacker_capital" class="form_is_capital" value="1" autocomplete="off">Y
                        </label>
                        <label class="btn btn-secondary active">
                            <input type="radio" name="is_attacker_capital" class="form_is_capital" value="2" autocomplete="off" checked>N
                        </label>
                    </div>
                    
                </div>
                <div class="input-group mb-1">
                    
                </div>
            </div>
        </div>
        <div class="card mb-2 attacker_form general_form" data-general_no='1'>
            <div class="card-header">
                <div class="float-sm-left" style="line-height:25px;">출병자 설정</div>
                <div class="float-sm-right btn-toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm mr-2" role="group">
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
                <div class="input-group-prepend">
                        <span class="input-group-text">국가 성향</span>
                    </div>
                    <select class="custom-select form_nation_type" style="width:25ch;">
                        <?php foreach(GameConst::$availableNationType as $typeID): ?>
                            <?php $nationTypeObj = buildNationTypeClass($typeID) ?> 
                            <option value="<?=$typeID?>"><?=$nationTypeObj->getName()?> (<?=$nationTypeObj::$pros?>, <?=$nationTypeObj::$cons?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">기술</span>
                    </div>
                    <input type="number" class="form-control form_tech" value="1" min="0" max="12">
                    <div class="input-group-append">
                        <span class="input-group-text">등급</span>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">국가 규모</span>
                    </div>
                    <select class="custom-select form_nation_level">
                        <?php foreach(getNationLevelList() as $nationLevel => [$name,$chiefCnt,$cityCnt]): ?>
                            <option value="<?=$nationLevel?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">도시 규모</span>
                    </div>
                    <select class="custom-select form_city_level">
                        <?php foreach(getCityLevelList() as $levelID => $name): ?>
                            <option value="<?=$levelID?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">수도</span>
                    </div>
                    <div class="input-group-append btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-secondary">
                            <input type="radio" name="is_defender_capital" class="form_is_capital" value="1" autocomplete="off">Y
                        </label>
                        <label class="btn btn-secondary active">
                            <input type="radio" name="is_defender_capital" class="form_is_capital" value="0" autocomplete="off" checked>N
                        </label>
                    </div>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">수비</span>
                    </div>
                    <input type="number" class="form-control form_def" id="city_def" value="1000" min="10" step="10">
                    <div class="input-group-prepend">
                        <span class="input-group-text">성벽</span>
                    </div>
                    <input type="number" class="form-control form_wall" id="city_wall" value="1000" min="0" step="10">
                </div>
            </div>
        </div>
        <div class="card mb-2 defender_add_form">
            <div class="card-header">
                <div class="float-sm-left" style="line-height:25px;">수비자 설정</div>
                <div class="float-sm-right btn-toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm mr-2" role="group">
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
                <div class="float-sm-left" style="line-height:25px;">수비자 설정</div>
                <div class="float-sm-right btn-toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm mr-2" role="group">
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
                    <div class="input-group-prepend">
                        <span class="input-group-text">이름</span>
                    </div>
                    <input type="text" class="form-control form_general_name" value="무명" style="width:15ch;">
                    <div class="input-group-prepend">
                        <span class="input-group-text">직위</span>
                    </div>
                    <select class="custom-select form_officer_level" style="width:8ch;">
                        <option value="1">일반</option>
                        <option value="4">태수</option>
                        <option value="3">군사</option>
                        <option value="2">종사</option>
                        <option value="10">무장 수뇌</option>
                        <option value="9">지장 수뇌</option>
                        <option value="11">참모</option>
                        <option value="12">군주</option>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">Level</span>
                    </div>
                    <input type="number" class="form-control form_exp_level" value="20" min="0" max="300" step="1">
                    
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">통솔</span>
                    </div>
                    <input type="number" class="form-control form_leadership" value="50" min="1" max="300" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">무력</span>
                    </div>
                    <input type="number" class="form-control form_strength" value="50" min="1" max="300" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">지력</span>
                    </div>
                    <input type="number" class="form-control form_intel" value="50" min="1" max="300" step="1">
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">명마</span>
                    </div>
                    <select class="custom-select form_general_horse">
                        <?php foreach(GameConst::$allItems['horse'] as $horseID=>$cnt): ?>
                            <option value="<?=$horseID?>"><?=getItemName($horseID)?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">무기</span>
                    </div>
                    <select class="custom-select form_general_weap">
                    <?php foreach(GameConst::$allItems['weapon'] as $weaponID=>$cnt): ?>
                            <option value="<?=$weaponID?>"><?=getItemName($weaponID)?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">서적</span>
                    </div>
                    <select class="custom-select form_general_book">
                        <?php foreach(GameConst::$allItems['book'] as $bookID=>$cnt): ?>
                            <option value="<?=$bookID?>"><?=getItemName($bookID)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">부상</span>
                    </div>
                    <input type="number" class="form-control form_injury" value="0" min="0" max="80" step="1">
                    <div class="input-group-append">
                        <span class="input-group-text">%(<span class="injury_helptext">건강</span>)</span>
                    </div>
                    <div class="input-group-prepend">
                        <span class="input-group-text">군량</span>
                    </div>
                    <input type="number" class="form-control form_rice" value="5000" min="50" max="40000" step="50">
                    <div class="input-group-prepend">
                        <span class="input-group-text">도구</span>
                    </div>
                    <select class="custom-select form_general_item">
                        <?php foreach(GameConst::$allItems['item'] as $itemID=>$cnt): ?>
                            <option value="<?=$itemID?>"><?=getItemName($itemID)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">병종</span>
                    </div>
                    <select class="custom-select form_crewtype">
                        <?php foreach(GameUnitConst::all() as $crewTypeID => $crewType): ?>
                            <?php if($crewType->armType === GameUnitConst::T_CASTLE){ continue; } ?>
                            <option value="<?=$crewTypeID?>"><?=$crewType->name?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">병사</span>
                    </div>
                    <input type="number" class="form-control form_crew" value="7000" min="100" step="100">
                    <div class="input-group-prepend">
                        <span class="input-group-text">성격</span>
                    </div>
                    <select class="custom-select form_general_character">
                        <?php foreach(getCharacterList(false) as $characterID => [$name,$info]): ?>
                            <option value="<?=$characterID?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">훈련</span>
                    </div>
                    <input type="number" class="form-control form_train" value="100" min="40" max="<?=GameConst::$maxTrainByWar?>" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">사기</span>
                    </div>
                    <input type="number" class="form-control form_atmos" value="100" min="40" max="<?=GameConst::$maxAtmosByWar?>" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">전특</span>
                    </div>
                    <select class="custom-select form_general_special_war">
                        <?php foreach(SpecialityHelper::getSpecialWarList(false) as $specialWarID =>$specialObj): ?>
                            <option value="<?=$specialWarID?>"><?=$specialObj->getName()?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">보병숙련</span>
                    </div>
                    <select class="custom-select form_dex1">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexAmount?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">궁병숙련</span>
                    </div>
                    <select class="custom-select form_dex2">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexAmount?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">기병숙련</span>
                    </div>
                    <select class="custom-select form_dex3">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexAmount?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">귀병숙련</span>
                    </div>
                    <select class="custom-select form_dex4">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexAmount?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">차병숙련</span>
                    </div>
                    <select class="custom-select form_dex5">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexAmount?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend only_defender">
                        <span class="input-group-text">수비여부</span>
                    </div>
                    <select class="custom-select form_defence_train only_defender">
                        <option value="80">훈사 80</option>
                        <option value="60">훈사 60</option>
                        <option value="999">안함</option>
                    </select>
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
            <tr><th style='width:18ch;'>전투 일시</th><td id='result_datetime'></td></tr>
            <tr><th>전투 횟수</th><td id='result_warcnt'></td></tr>
            <tr><th>전투 페이즈</th><td id='result_phase'></td></tr>
            <tr><th>준 피해</th><td><span id='result_killed'>0</span><span id='result_varKilled'> (<span id='result_minKilled'>0</span> ~ <span id='result_maxKilled'>0</span>)</span></td></tr>
            <tr><th>받은 피해</th><td><span id='result_dead'>0</span><span id='result_varDead'> (<span id='result_minDead'>0</span> ~ <span id='result_maxDead'>0</span>)</span></td></tr>
            <tr><th>출병자 군량 소모</th><td id='result_attackerRice'></td></tr>
            <tr><th>수비자 군량 소모</th><td id='result_defenderRice'></td></tr>
            <tr><th>공격자 스킬</th><td id='result_attackerSkills'></td></tr>
            <tr class='result_defenderSkills'><th>수비자1 스킬</th><td></td></tr>
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
</body>
</html>