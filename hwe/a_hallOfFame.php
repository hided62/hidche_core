<?php

namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();
$seasonIdx = Util::getReq('seasonIdx', 'int', UniqueConst::$seasonIdx);
$scenarioIdx = Util::getReq('scenarioIdx', 'int', null);

$db = DB::db();

increaseRefresh("명예의전당", 1);

$scenarioList = (function () {
    $db = DB::db();
    $scenarioList = [];
    foreach ($db->query('SELECT season, scenario_name as name, count(scenario) as cnt, scenario from ng_games group by season, scenario order by season desc, scenario asc') as $scenarioInfo) {
        $seasonIdx = $scenarioInfo['season'];
        $scenarioIdx = $scenarioInfo['scenario'];
        if (!key_exists($seasonIdx, $scenarioList)) {
            $scenarioList[$seasonIdx] = [];
        }
        $scenarioList[$seasonIdx][$scenarioIdx] = $scenarioInfo;
    }
    return $scenarioList;
})();



if ($scenarioIdx && key_exists($scenarioIdx, $scenarioList[$seasonIdx] ?? [])) {
    $searchScenarioName = $scenarioList[$seasonIdx][$scenarioIdx]['name'];
    $searchFilter = $db->sqleval('season = %i AND scenario = %i', $seasonIdx, $scenarioIdx);
} else {
    $searchScenarioName = '* 모두 *';
    $searchFilter = $db->sqleval('season = %i', $seasonIdx);
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 명예의 전당</title>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printDist('ts', ['common', 'hallOfFame']) ?>
</head>

<body>
    <div id="container" class="bg0">
        <div class="row gx-0">
            <div class="col">명 예 의 전 당<br><?= closeButton() ?></div>
        </div>
        <div class="row gx-0">
            <div class="col center">
                시나리오 검색 : <select id="by_scenario" name="by_scenario">
                    <?php foreach ($scenarioList as $iterSeasonIdx => $subScenarioList) : ?>
                        <option data-season="<?= $iterSeasonIdx ?>" value="" <?= ($iterSeasonIdx == $seasonIdx && $scenarioIdx === null) ? "selected='selected'" : '' ?>>* 시즌 : <?= $iterSeasonIdx ?> 종합 *</option>
                        <?php foreach ($subScenarioList as $info) : ?>
                            <option data-season="<?= $iterSeasonIdx ?>" value="<?= $info['scenario'] ?>" <?= ($iterSeasonIdx == $seasonIdx && $info['scenario'] === $scenarioIdx) ? "selected='selected'" : '' ?>><?= $info['name'] ?>(<?= $info['cnt'] ?>회)</option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    ?>
                </select>
            </div>
        </div>
        <?php
        $types = [
            'experience' => ['명 성', 'int'],
            'dedication' => ['계 급', 'int'],
            'firenum' => ['계 략 성 공', 'int'],
            'warnum' => ['전 투 횟 수', 'int'],
            'killnum' => ['승 리', 'int'],
            'winrate' => ['승 률', 'percent'],
            'occupied' => ['점 령', 'int'],
            'killcrew' => ['사 살', 'int'],
            'killrate' => ['살 상 률', 'percent'],
            'killcrew_person' => ['대 인 사 살', 'int'],
            'killrate_person' => ['대 인 살 상 률', 'percent'],
            'dex1' => ['보 병 숙 련 도', 'int'],
            'dex2' => ['궁 병 숙 련 도', 'int'],
            'dex3' => ['기 병 숙 련 도', 'int'],
            'dex4' => ['귀 병 숙 련 도', 'int'],
            'dex5' => ['차 병 숙 련 도', 'int'],
            'ttrate' => ['전 력 전 승 률', 'percent'],
            'tlrate' => ['통 솔 전 승 률', 'percent'],
            'tsrate' => ['일 기 토 승 률', 'percent'],
            'tirate' => ['설 전 승 률', 'percent'],
            'betgold' => ['베 팅 투 자 액', 'int'],
            'betwin' => ['베 팅 당 첨', 'int'],
            'betwingold' => ['베 팅 수 익 금', 'int'],
            'betrate' => ['베 팅 수 익 률', 'percent'],
        ];

        $templates = new \League\Plates\Engine('templates');

        $ownerNameList = [];
        foreach (RootDB::db()->queryAllLists('SELECT no, name FROM member') as [$ownerID, $ownerName]) {
            $ownerNameList[$ownerID] = $ownerName;
        }

        foreach ($types as $typeName => [$typeDescribe, $typeValue]) {
            $hallResult = $db->query('SELECT * FROM hall WHERE `type`=%s AND %? ORDER BY `value` DESC LIMIT 10', $typeName, $searchFilter);

            $hallResult = array_map(function ($general) use ($typeValue, $ownerNameList) {
                $aux = Json::decode($general['aux']);
                $general += $aux;

                if (key_exists($general['owner'], $ownerNameList)) {
                    $general['ownerName'] = $ownerNameList[$general['owner']];
                }

                if (!key_exists('bgColor', $general)) {
                    if (!key_exists('color', $general)) {
                        $general['bgColor'] = GameConst::$basecolor4;
                    } else {
                        $general['bgColor'] = $general['color'];
                    }
                }

                if (!key_exists('fgColor', $general)) {
                    $general['fgColor'] = newColor($general['bgColor']);
                }

                if (key_exists('picture', $general)) {
                    $imageTemp = GetImageURL($general['imgsvr']);
                    $general['pictureFullPath'] = "$imageTemp/{$general['picture']}";
                } else {
                    $general['pictureFullPath'] = GetImageURL(0) . "/default.jpg";
                }

                if (!key_exists('printValue', $general)) {
                    $value = $general['value'];
                    if ($typeValue == 'percent') {
                        $general['printValue'] = number_format($value * 100, 2) . '%';
                    } else {
                        $general['printValue'] = number_format($value);
                    }
                }

                return $general;
            }, $hallResult);

            echo $templates->render('hallOfFrame', [
                'typeName' => $typeDescribe,
                'generals' => $hallResult
            ]);
        }
        ?>
        <div class="row gx-0">
            <div class="col"><?= closeButton() ?></div>
        </div>
        <div class="row bg0 gx-0">
            <div class="col bg0"><?= banner() ?></div>
        </div>
    </div>
</body>

</html>