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
<?=WebUtil::printCSS('../css/config.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/battle_simulator.css')?>
<?=WebUtil::printJS('../d_shared/common_path.js')?>
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/battle_simulator.js')?>
</head>
<body>
<div id="container">
<div class="card mb-3">
    <div class="card-header">
        전역 설정
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="input-group">
                    <input type="number" class="form-control" id="year" aria-describedby="text_year" value="183" min="<?=$startYear?>">
                    <div class="input-group-append">
                        <span class="input-group-text" id="text_year">년</span>
                    </div>
                    <input type="number" class="form-control" id="month" aria-describedby="text_month" value="1" min="1" max="12">
                    <div class="input-group-append">
                        <span class="input-group-text" id="text_month">월</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group mr-2" role="group">
                        <button type="button" class="btn btn-primary">저장</button>
                        <button type="button" class="btn btn-primary">불러오기</button>
                    </div>
                    <div class="btn-group mr-2" role="group">
                        <button type="button" class="btn btn-info">내보내기</button>
                        <button type="button" class="btn btn-info">가져오기</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <div class="card mb-2">
            <div class="card-header">
                출병국 설정
            </div>
            <div class="card-body nation_detail">
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">국가 성향</span>
                    </div>
                    <select class="custom-select form_nation_type" style="width:25ch;">
                        <?php foreach(getNationTypeList() as $typeID => [$name,$pros,$cons]): ?>
                            <option value="<?=$typeID?>"><?=$name?> (<?=$pros?>, <?=$cons?>)</option>
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
                            <input type="radio" name="is_attacker_capital" id="is_attacker_capital_y" autocomplete="off">Y
                        </label>
                        <label class="btn btn-secondary active">
                            <input type="radio" name="is_attacker_capital" id="is_attacker_capital_n" autocomplete="off" checked>N
                        </label>
                    </div>
                    
                </div>
                <div class="input-group mb-1">
                    
                </div>
            </div>
        </div>
        <div class="card mb-2 attacker_form general_form">
            <div class="card-header">
                <div class="float-sm-left" style="line-height:25px;">출병자 설정</div>
                <div class="float-sm-right btn-toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm mr-2" role="group">
                        <button type="button" class="btn btn-primary">저장</button>
                        <button type="button" class="btn btn-primary">불러오기</button>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info">내보내기</button>
                        <button type="button" class="btn btn-info">가져오기</button>
                    </div>
                </div>
            </div>
            <div class="card-body general_detail">
            </div>
        </div>
    </div><!-- <div class="col-sm"> -->
    <div class="col-sm">
        <div class="card mb-2">
            <div class="card-header">
                수비국 설정
            </div>
            <div class="card-body">
                <div class="input-group mb-1">
                <div class="input-group-prepend">
                        <span class="input-group-text">국가 성향</span>
                    </div>
                    <select class="custom-select form_nation_type" style="width:25ch;">
                        <?php foreach(getNationTypeList() as $typeID => [$name,$pros,$cons]): ?>
                            <option value="<?=$typeID?>"><?=$name?> (<?=$pros?>, <?=$cons?>)</option>
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
                            <input type="radio" name="is_attacker_capital" id="is_attacker_capital_y" autocomplete="off">Y
                        </label>
                        <label class="btn btn-secondary active">
                            <input type="radio" name="is_attacker_capital" id="is_attacker_capital_n" autocomplete="off" checked>N
                        </label>
                    </div>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">수비</span>
                    </div>
                    <input type="number" class="form-control form_def" id="month" value="1000" min="10" step="10">
                    <div class="input-group-prepend">
                        <span class="input-group-text">성벽</span>
                    </div>
                    <input type="number" class="form-control form_wall" id="month" value="1000" min="0" step="10">
                </div>
            </div>
        </div>
        <div class="card mb-2 defender_form general_form">
            <div class="card-header">
                <div class="float-sm-left" style="line-height:25px;">수비자 설정</div>
                <div class="float-sm-right btn-toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm mr-2" role="group">
                        <button type="button" class="btn btn-primary">저장</button>
                        <button type="button" class="btn btn-primary">불러오기</button>
                    </div>
                    <div class="btn-group btn-group-sm mr-2" role="group">
                        <button type="button" class="btn btn-info">내보내기</button>
                        <button type="button" class="btn btn-info">가져오기</button>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-warning">복제</button>
                        <button type="button" class="btn btn-danger">제거</button>
                    </div>
                </div>
            </div>
            <div class="card-body general_detail">
            </div>
        </div>

        <div class="card mb-2 defender_add_form">
            <div class="card-header">
                <div class="float-sm-left" style="line-height:25px;">수비자 설정</div>
                <div class="float-sm-right btn-toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm mr-2" role="group">
                        <button type="button" class="btn btn-primary">불러오기</button>
                    </div>
                    <div class="btn-group btn-group-sm mr-2" role="group">
                        <button type="button" class="btn btn-info">가져오기</button>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-success">추가</button>
                    </div>
                </div>
            </div>
            <div class="card-body general_detail">
                <div class="input-group mb-3">
                    
                    <div class="input-group-prepend">
                        <span class="input-group-text">직위</span>
                    </div>
                    <select class="custom-select form_general_level">
                        <option value="1">일반</option>
                        <option value="4">태수</option>
                        <option value="3">군사</option>
                        <option value="2">시중</option>
                        <option value="10">무장 수뇌</option>
                        <option value="9">지장 수뇌</option>
                        <option value="11">참모</option>
                        <option value="12">군주</option>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">Level</span>
                    </div>
                    <input type="number" class="form-control form_exp_level" value="20" min="0" max="300" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">부상</span>
                    </div>
                    <input type="number" class="form-control form_leadership" value="0" min="0" max="80" step="1">
                    <div class="input-group-append">
                        <span class="input-group-text">%(<span class="injury_helptext">건강</span>)</span>
                    </div>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">통솔</span>
                    </div>
                    <input type="number" class="form-control form_leadership" value="50" min="1" max="300" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">무력</span>
                    </div>
                    <input type="number" class="form-control form_power" value="50" min="1" max="300" step="1">
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
                        <?php foreach(range(0, 26) as $horseID): ?>
                            <option value="<?=$horseID?>"><?=getHorseName($horseID)?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">무기</span>
                    </div>
                    <select class="custom-select form_general_horse">
                        <?php foreach(range(0, 26) as $weapID): ?>
                            <option value="<?=$weapID?>"><?=getWeapName($weapID)?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">서적</span>
                    </div>
                    <select class="custom-select form_general_book">
                        <?php foreach(range(0, 26) as $bookID): ?>
                            <option value="<?=$bookID?>"><?=getBookName($bookID)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-3">
                <div class="input-group-prepend">
                        <span class="input-group-text">자금</span>
                    </div>
                    <input type="number" class="form-control form_power" value="0" min="0" max="20000" step="50">
                    <div class="input-group-prepend">
                        <span class="input-group-text">군량</span>
                    </div>
                    <input type="number" class="form-control form_power" value="5000" min="50" max="20000" step="50">
                    <div class="input-group-prepend">
                        <span class="input-group-text">도구</span>
                    </div>
                    <select class="custom-select form_general_item">
                        <?php foreach(range(0, 26) as $bookID): ?>
                            <option value="<?=$bookID?>"><?=getItemName($bookID)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">병종</span>
                    </div>
                    <select class="custom-select form_crewtype">
                        <?php foreach(GameUnitConst::all() as $crewTypeID => $crewType): ?>
                            <?php if($crewTypeID < 0){ continue; } ?>
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
                        <?php foreach(getCharacterList() as $characterID => [$name,$info]): ?>
                            <option value="<?=$characterID?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">훈련</span>
                    </div>
                    <input type="number" class="form-control form_crew" value="100" min="40" max="<?=GameConst::$maxTrainByWar?>" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">사기</span>
                    </div>
                    <input type="number" class="form-control form_crew" value="100" min="40" max="<?=GameConst::$maxAtmosByWar?>" step="1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">전특</span>
                    </div>
                    <select class="custom-select form_general_special_war">
                        <?php foreach(SpecialityConst::WAR as $specialWarID => [$name,$buff,$cond]): ?>
                            <option value="<?=$specialWarID?>"><?=$name?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">보병숙련</span>
                    </div>
                    <select class="custom-select form_dex0">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexLevel?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">궁병숙련</span>
                    </div>
                    <select class="custom-select form_dex10">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexLevel?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">기병숙련</span>
                    </div>
                    <select class="custom-select form_dex20">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexLevel?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">귀병숙련</span>
                    </div>
                    <select class="custom-select form_dex30">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexLevel?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">차병숙련</span>
                    </div>
                    <select class="custom-select form_dex40">
                        <?php foreach(getDexLevelList() as $dexLevel => [$dexAmount, $color, $name]): ?>
                            <option value="<?=$dexLevel?>"><?="{$name} (".number_format($dexAmount).")"?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-prepend">
                        <span class="input-group-text">수비여부</span>
                    </div>
                    <select class="custom-select form_defend_mode">
                        <option value="2">훈사 80</option>
                        <option value="3">훈사 60</option>
                        <option value="0">안함</option>
                    </select>
                </div>
            </div>
        </div>
    </div><!-- <div class="col-sm"> -->
</div>
</div>
</body>
</html>