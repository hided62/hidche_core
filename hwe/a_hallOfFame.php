<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();
$scenarioIdx = Util::getReq('scenarioIdx', 'int', null);

$db = DB::db();

increaseRefresh("명예의전당", 1);

$scenarioList= [];
foreach($db->query('SELECT scenario_name as name, count(scenario) as cnt, scenario from ng_games group by scenario order by scenario asc') as $scenarioInfo){
    $scenarioList[$scenarioInfo['scenario']] = $scenarioInfo;
}



if($scenarioIdx !== null || key_exists($scenarioIdx, $scenarioList)){
    $searchScenarioName = $scenarioList[$scenarioIdx]['name'];
    $searchFilter = $db->sqleval('scenario = %i', $scenarioIdx);
}
else{
    $searchScenarioName = '* 모두 *';
    $searchFilter = $db->sqleval(true);
}

?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1136" />
<title><?=UniqueConst::$serverName?>: 명예의 전당</title>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/hallOfFame.css')?>

<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/jquery.redirect.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJs('js/hallOfFame.js')?>
</head>

<body>
<table align=center width=1100 class='tb_layout bg0'>
    <tr><td>명 예 의 전 당<br><?=closeButton()?></td></tr>
    <tr><td>
시나리오 검색 : <select id="by_scenario" name="by_scenario">
    <option value="" <?=$scenarioIdx?"selected='selected'":''?>>* 종합 *</option>
<?php foreach($scenarioList as $info): ?>
    <option
        value="<?=$info['scenario']?>"
        <?=($info['scenario']===$scenarioIdx)?"selected='selected'":''?>
    ><?=$info['name']?>(<?=$info['cnt']?>회)</option>
<?php endforeach; ?>
?>
</select>
</td></tr>
</table>
<div style="margin:auto;width=1100px;">
<?php
$types = [
    ["명 성", 'int'],
    ["계 급", 'int'],
    ["계 략 성 공", 'int'],
    ["전 투 횟 수", 'int'],
    ["승 리", 'int'],
    ["승 률", 'percent'],
    ["사 살", 'int'],
    ["살 상 률", 'percent'],
    ["보 병 숙 련 도", 'int'],
    ["궁 병 숙 련 도", 'int'],
    ["기 병 숙 련 도", 'int'],
    ["귀 병 숙 련 도", 'int'],
    ["차 병 숙 련 도", 'int'],
    ["전 력 전 승 률", 'percent'],
    ["통 솔 전 승 률", 'percent'],
    ["일 기 토 승 률", 'percent'],
    ["설 전 승 률", 'percent'],
    ["베 팅 투 자 액", 'int'],
    ["베 팅 당 첨", 'int'],
    ["베 팅 수 익 금", 'int'],
    ["베 팅 수 익 률", 'percent'],
];

$templates = new \League\Plates\Engine('templates');

foreach($types as $idx=>[$typeName, $typeValue]) {
    $hallResult = $db->query('SELECT * FROM ng_hall WHERE `type`=%i AND %? ORDER BY `value` DESC LIMIT 10', $idx, $searchFilter);

    $hallResult = array_map(function($general)use($typeValue){
        $aux = Json::decode($general['aux']);
        $general += $aux;
        if(!key_exists('bgColor', $general)){
            if(!key_exists('color', $general)){
                $general['bgColor'] = GameConst::$basecolor4;
            }
            else{
                $general['bgColor'] = $general['color'];
            }
        }
        
        if(!key_exists('fgColor', $general)){
            $general['fgColor'] = newColor($general['bgColor']);
        }

        if(key_exists('picture', $general)){
            $imageTemp = GetImageURL($general['imgsvr']);
            $general['pictureFullPath'] = "$imageTemp/{$general['picture']}";
        }
        else{
            $general['pictureFullPath'] = GetImageURL(0)."/default.jpg";
        }

        if(!key_exists('printValue', $general)){
            $value = $general['value'];
            if($typeValue == 'percent'){
                $general['printValue'] = number_format($value*100, 2).'%';
            }
            else {
                $general['printValue'] = number_format($value);
            }
        }

        return $general;
    }, $hallResult);

    echo $templates->render('hallOfFrame', [
        'typeName'=>$typeName,
        'generals'=>$hallResult
    ]);
}
?>
</div>
<table align=center width=1100 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

