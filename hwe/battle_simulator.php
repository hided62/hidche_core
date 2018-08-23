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
                        <button type="button" class="btn btn-secondary">저장</button>
                        <button type="button" class="btn btn-secondary">불러오기</button>
                    </div>
                    <div class="btn-group mr-2" role="group">
                        <button type="button" class="btn btn-secondary">내보내기</button>
                        <button type="button" class="btn btn-secondary">가져오기</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm">
        <div class="card">
            <div class="card-header">
                출병국 설정
            </div>
            <div class="card-body">
                <div class="input-group mb-1">
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
                        <span class="input-group-text">국가 성향</span>
                    </div>
                    <select class="custom-select form_nation_type">
                        <?php foreach(getNationTypeList() as $typeID => [$name,$pros,$cons]): ?>
                            <option value="<?=$typeID?>"><?=$name?> (<?=$pros?>, <?=$cons?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
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
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-header">
                수비국 설정
            </div>
            <div class="card-body">
                <div class="input-group mb-1">
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
                        <span class="input-group-text">국가 성향</span>
                    </div>
                    <select class="custom-select form_nation_type">
                        <?php foreach(getNationTypeList() as $typeID => [$name,$pros,$cons]): ?>
                            <option value="<?=$typeID?>"><?=$name?> (<?=$pros?>, <?=$cons?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group mb-1">
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
                            <input type="radio" name="is_defender_capital" id="is_defender_capital_y" autocomplete="off">Y
                        </label>
                        <label class="btn btn-secondary active">
                            <input type="radio" name="is_defender_capital" id="is_defender_capital_n" autocomplete="off" checked>N
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
    </div>
</div>
</div>
</body>
</html>