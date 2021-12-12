<?php

namespace sammo;

include "lib.php";
include "func.php";

$citylist = Util::getReq('citylist', 'int');

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();
$userGrade = Session::getUserGrade();

$db = DB::db();

increaseRefresh("현재도시", 1);

$me = $db->queryFirstRow('SELECT no,nation,officer_level,city from general where owner=%i', $userID);
$myNation = $db->queryFirstRow('SELECT nation,level,spy FROM nation WHERE nation=%i', $me['nation']) ?? [
    'nation' => 0,
    'level' => 0,
    'spy' => ''
];

$templates = new \League\Plates\Engine('templates');

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 도시정보</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../e_lib/select2/select2.min.css') ?>
    <?= WebUtil::printCSS('../e_lib/select2/select2-bootstrap4.css') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <style>
        #general_list tr td {
            text-align: center;
        }

        #general_list tr td:last-child {
            text-align: left;
            padding-left: 1em;
        }

        .general_turn_text {
            font-size: x-small;
        }
    </style>
    <?= WebUtil::printDist('ts', ['common', 'currentCity']) ?>
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>도 시 정 보<br><?= backButton() ?></td>
        </tr>
    </table>

    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td width=998>
                <form name=cityselect method=get>
                    <div style='text-align:center;'>도시선택 :
                        <select id="citySelector" name=citylist size=1 style='display:inline-block;min-width:400px;'>
                            <?php
                            if (!$citylist) {
                                $citylist = $me['city'];
                            }

                            // 재야일때는 현재 도시만
                            $valid = 0;
                            if ($me['officer_level'] == 0) {
                                $city = $db->queryFirstRow('SELECT city,name,nation from city where city=%i', $me['city']);
                                echo "
                    <option value={$city['city']}";
                                if ($city['city'] == $citylist) {
                                    echo " selected";
                                    $valid = 1;
                                }
                                echo ">【" . StringUtil::padString($city['name'], 4, '_') . "】";
                                if ($city['nation'] == 0) echo "공백지";
                                elseif ($me['nation'] == $city['nation']) echo "본국";
                                else echo "타국";
                                echo "</option>";
                            } else {
                                // 아국 도시들 선택
                                foreach ($db->query('SELECT city,name,nation from city where nation=%i', $me['nation']) as $city) {
                                    echo "
                        <option value={$city['city']}";
                                    if ($city['city'] == $citylist) {
                                        echo " selected";
                                        $valid = 1;
                                    }
                                    echo ">【" . StringUtil::padString($city['name'], 4, '_') . "】";
                                    if ($city['nation'] == 0) echo "공백지";
                                    elseif ($me['nation'] == $city['nation']) echo "본국";
                                    else echo "타국";
                                    echo "</option>";
                                }

                                // 아국 장수가 있는 타국 도시들 선택
                                foreach ($db->query('SELECT distinct A.city,B.name,B.nation from general A,city B where A.city=B.city and A.nation=%i and B.nation!=%i', $me['nation'], $me['nation']) as $city) {
                                    echo "
                        <option value={$city['city']}";
                                    if ($city['city'] == $citylist) {
                                        echo " selected";
                                        $valid = 1;
                                    }
                                    echo ">【" . StringUtil::padString($city['name'], 4, '_') . "】";
                                    if ($city['nation'] == 0) echo "공백지";
                                    elseif ($me['nation'] == $city['nation']) echo "본국";
                                    else echo "타국";
                                    echo "</option>";
                                }
                            }

                            if ($myNation['level'] > 0) {
                                // 첩보도시도 목록에 추가

                                $rawSpy = $myNation['spy'];

                                if ($rawSpy == '') {
                                    $spyCities = [];
                                } else {
                                    $spyCities = array_keys(Json::decode($rawSpy));
                                }


                                if ($spyCities) {
                                    foreach ($db->query('SELECT city,name,nation FROM city WHERE city in %li', $spyCities) as $city) {
                                        echo "<option value={$city['city']}";
                                        if ($city['city'] == $citylist) {
                                            echo " selected";
                                            $valid = 1;
                                        }
                                        echo ">【" . StringUtil::padString($city['name'], 4, '_') . "】";
                                        if ($city['nation'] == 0) echo "공백지";
                                        elseif ($me['nation'] == $city['nation']) echo "본국";
                                        else echo "타국";
                                        echo "</option>";
                                    }
                                }
                            }

                            echo "
                </select></div>
                <p align=center>명령 화면에서 도시를 클릭하셔도 됩니다.</p>
            </form>
        </td>
    </tr>
</table>
<br>";

                            unset($city);

                            // 첩보된 도시까지만 허용

                            $showDetailedInfo = false;
                            if ($valid) {
                                $showDetailedInfo = true;
                            }

                            if (!key_exists($citylist, CityConst::all())) {
                                $citylist = $me['city'];
                                $showDetailedInfo = true;
                                $valid = 1;
                            }

                            if ($userGrade >= 5) {
                                $valid = true;
                                $showDetailedInfo = true;
                            }

                            if (!$valid) {
                                $ownCities = Util::convertArrayToSetLike($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i AND nation != 0', $me['nation']));
                                foreach (array_keys(CityConst::byID($citylist)->path) as $pathID) {
                                    if (key_exists($pathID, $ownCities)) {
                                        $showDetailedInfo = true;
                                        break;
                                    }
                                }
                            }


                            $city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $citylist);
                            $cityNation = getNationStaticInfo($city['nation']);

                            //태수, 군사, 종사
                            $officer = [
                                4 => ['name' => '-', 'npc' => 0],
                                3 => ['name' => '-', 'npc' => 0],
                                2 => ['name' => '-', 'npc' => 0]
                            ];

                            foreach ($db->query('SELECT `name`, npc, `officer_level` FROM general WHERE `officer_city` = %i', $city['city']) as $officerInfo) {
                                $officer[$officerInfo['officer_level']] = $officerInfo;
                            }

                            if ($city['trade'] === null) {
                                $city['trade'] = "- ";
                            }

                            $dbColumns = General::mergeQueryColumn(['npc', 'defence_train', 'no', 'picture', 'imgsvr', 'name', 'injury', 'leadership', 'strength', 'intel', 'officer_level', 'nation', 'crewtype', 'crew', 'train', 'atmos'], 2)[0];
                            if ($showDetailedInfo) {
                                $generals = $db->query(
                                    'SELECT %l from general where city=%i order by turntime',
                                    Util::formatListOfBackticks($dbColumns),
                                    $city['city']
                                );
                            } else {
                                $generals = [];
                            }

                            if ($valid) {
                                $city['trustText'] = round($city['trust'], 1);
                                $city['popRateText'] = round($city['pop'] / $city['pop_max'] * 100, 2);
                            } else {
                                $city['agri'] = '?';
                                $city['comm'] = '?';
                                $city['pop'] = '?';
                                $city['secu'] = '?';
                                $city['trustText'] = '?';
                                $city['popRateText'] = '?';

                                if ($city['nation'] != 0) {
                                    $city['def'] = '?';
                                    $city['wall'] = '?';
                                }
                            }


                            $generalTurnList = [];

                            if ($generals) {
                                foreach ($db->queryAllLists(
                                    'SELECT general_id, turn_idx, brief FROM general_turn WHERE general_id IN %li AND turn_idx < 5 ORDER BY general_id ASC, turn_idx ASC',
                                    array_column($generals, 'no')
                                ) as [$generalID, $turnIdx, $brief]) {
                                    if (!key_exists($generalID, $generalTurnList)) {
                                        $generalTurnList[$generalID] = [];
                                    }
                                    $generalTurnList[$generalID][$turnIdx] = $brief;
                                }
                            }


                            $nationname = [];
                            $nationlevel = [];
                            foreach (getAllNationStaticInfo() as $nation) {
                                $nationname[$nation['nation']] = $nation['name'];
                                $nationlevel[$nation['nation']] = $nation['level'];
                            }

                            //도시명	오	적군	0/0(0)	병장(총)	0/0(4)	90병장	0/0	60병장	0/0	수비○	0/0
                            $generalsFormat = [];


                            foreach ($generals as $general) {
                                $nationInfo = getNationStaticInfo($general['nation']);

                                if ($general['nation'] != 0 && $general['nation'] == $myNation['nation']) {
                                    $ourGeneral = true;
                                } else {
                                    $ourGeneral = false;
                                }

                                if ($userGrade == 6) {
                                    $ourGeneral = true;
                                }

                                $isNPC = $general['npc'] > 1;
                                $wounded = $general['injury'];


                                $name = $general['name'];
                                $nameText = formatName($name, $general['npc']);

                                $leadership = $general['leadership'];
                                $strength = $general['strength'];
                                $intel = $general['intel'];

                                $leadershipText = formatWounded($leadership, $general['injury']);
                                $strengthText = formatWounded($strength, $general['injury']);
                                $intelText = formatWounded($intel, $general['injury']);

                                $officerLevel = $general['officer_level'];
                                $officerLevelText = getOfficerLevelText($officerLevel);

                                $leadershipBonus = calcLeadershipBonus($officerLevel, $nationInfo['level']);
                                $leadershipBonusText = formatLeadershipBonus($leadershipBonus);

                                if ($ourGeneral) {
                                    $defenceTrain = $general['defence_train'];
                                    $defenceTrainText = formatDefenceTrain($defenceTrain);
                                    $crewType = $general['crewtype'];
                                    $crewTypeText = GameUnitConst::byId($crewType)->name;
                                    $crew = $general['crew'];
                                    $train = $general['train'];
                                    $atmos = $general['atmos'];
                                } else {
                                    $defenceTrain = 0;
                                    $defenceTrainText = '';
                                    $crewType = 0;
                                    $crewTypeText = '';
                                    $crew = $general['crew'];
                                    $train = -1;
                                    $atmos = -1;

                                    if (!$valid) {
                                        $crew = -1;
                                    }
                                }

                                $nation = $general['nation'];
                                $nationName = $nationInfo['name'];

                                if ($ourGeneral && !$isNPC) {
                                    $turnText = [];
                                    $generalObj = new General($general, null, null, null, null, null, false);
                                    foreach ($generalTurnList[$generalObj->getID()] as $turnRawIdx => $turn) {
                                        $turnIdx = $turnRawIdx + 1;
                                        $turnText[] = "{$turnIdx} : $turn";
                                    }
                                    $turnText = join('<br>', $turnText);
                                } else {
                                    $turnText = '';
                                }

                                $generalsFormat[] = [
                                    'ourGeneral' => $ourGeneral,
                                    'iconPath' => GetImageURL($general['imgsvr']) . '/' . $general['picture'],
                                    'isNPC' => $isNPC,
                                    'wounded' => $wounded,
                                    'name' => $name,
                                    'nameText' => $nameText,
                                    'leadership' => $leadership,
                                    'leadershipText' => $leadershipText,
                                    'leadershipBonus' => $leadershipBonus,
                                    'leadershipBonusText' => $leadershipBonusText,
                                    'officerLevel' => $officerLevel,
                                    'officerLevelText' => $officerLevelText,
                                    'strength' => $strength,
                                    'strengthText' => $strengthText,
                                    'intel' => $intel,
                                    'intelText' => $intelText,
                                    'defenceTrain' => $defenceTrain,
                                    'defenceTrainText' => $defenceTrainText,
                                    'crewType' => $crewType,
                                    'crewTypeText' => $crewTypeText,
                                    'crew' => $crew,
                                    'train' => $train,
                                    'atmos' => $atmos,
                                    'nation' => $nation,
                                    'nationName' => $nationName,
                                    'turnText' => $turnText
                                ];
                            }

                            $generalsName = array_map(function ($gen) {
                                return getColoredName($gen['name'], $gen['npc']);
                            }, $generals);

                            $enemyCrew = 0;
                            $enemyCnt = 0;
                            $enemyArmedCnt = 0;
                            $crew90 = 0;
                            $gen90 = 0;
                            $crew80 = 0;
                            $gen80 = 0;
                            $crew60 = 0;
                            $gen60 = 0;

                            $crewDef = 0;
                            $genDef = 0;

                            $crewTotal = 0;
                            $armedGenTotal = 0;
                            $genTotal = 0;


                            foreach ($generalsFormat as $general) {
                                if (!$general['nation'] || !$myNation['nation']) {
                                    continue;
                                }
                                if ($general['nation'] != $myNation['nation']) {
                                    $enemyCnt += 1;
                                    if ($general['crew'] >= 0) {
                                        $enemyCrew += $general['crew'];
                                    }
                                    if ($general['crew'] > 0) {
                                        $enemyArmedCnt += 1;
                                    }
                                    continue;
                                }

                                $crewTotal += $general['crew'];
                                $genTotal += 1;

                                if ($general['crew'] == 0) {
                                    continue;
                                }
                                $armedGenTotal += 1;

                                $minTrain = min($general['train'], $general['atmos']);

                                if ($minTrain >= 90) {
                                    $crew90 += $general['crew'];
                                    $gen90 += 1;
                                }

                                $chkDef = false;

                                if ($minTrain >= 80) {
                                    $crew80 += $general['crew'];
                                    $gen80 += 1;
                                }

                                if ($minTrain >= 60) {
                                    $crew60 += $general['crew'];
                                    $gen60 += 1;
                                }

                                if ($minTrain >= $general['defenceTrain']) {
                                    $crewDef += $general['crew'];
                                    $genDef += 1;
                                    $chkDef = true;
                                }
                            }

                            ?>

                            <table align=center width=1000 class='tb_layout bg0'>
                                <tr>
                                    <td><?= backButton() ?></td>
                                </tr>
                            </table>

                            <table align=center width=1000 class='tb_layout bg2'>
                                <tr>
                                    <td colspan=11 align=center style='color:<?= newColor($cityNation['color']) ?>; background:<?= $cityNation['color'] ?>'>【 <?= CityConst::$regionMap[$city['region']] ?> | <?= CityConst::$levelMap[$city['level']] ?> 】 <?= $city['name'] ?></td>
                                    <td style='color:<?= newColor($cityNation['color']) ?>; background:<?= $cityNation['color'] ?>' class='center'><?= date('m-d H:i:s') ?></td>
                                </tr>
                                <tr>
                                    <td align=center width=48 class=bg1>주민</td>
                                    <td align=center width=112><?= $city['pop'] ?>/<?= $city['pop_max'] ?></td>
                                    <td align=center width=48 class=bg1>농업</td>
                                    <td align=center width=108><?= $city['agri'] ?>/<?= $city['agri_max'] ?></td>
                                    <td align=center width=48 class=bg1>상업</td>
                                    <td align=center width=108><?= $city['comm'] ?>/<?= $city['comm_max'] ?></td>
                                    <td align=center width=48 class=bg1>치안</td>
                                    <td align=center width=108><?= $city['secu'] ?>/<?= $city['secu_max'] ?></td>
                                    <td align=center width=48 class=bg1>수비</td>
                                    <td align=center width=108><?= $city['def'] ?>/<?= $city['def_max'] ?></td>
                                    <td align=center width=48 class=bg1>성벽</td>
                                    <td align=center width=108><?= $city['wall'] ?>/<?= $city['wall_max'] ?></td>
                                </tr>
                                <tr>
                                    <td align=center class=bg1>민심</td>
                                    <td align=center><?= $city['trustText'] ?></td>
                                    <td align=center class=bg1>시세</td>
                                    <td align=center><?= $city['trade'] ?>%</td>
                                    <td align=center class=bg1>인구</td>
                                    <td align=center><?= $city['popRateText'] ?>%</td>
                                    <td align=center class=bg1>태수</td>
                                    <td align=center><?= $officer[4]['name'] ?></td>
                                    <td align=center class=bg1>군사</td>
                                    <td align=center><?= $officer[3]['name'] ?></td>
                                    <td align=center class=bg1>종사</td>
                                    <td align=center><?= $officer[2]['name'] ?></td>
                                </tr>
                                <tr>
                                    <td align=center class=bg1>도시명</td>
                                    <td align=center><?= $city['name'] ?></td>
                                    <td align=center class=bg1>적군</td>
                                    <td align=center><?= number_format($enemyCrew) ?>/<?= number_format($enemyArmedCnt) ?>(<?= number_format($enemyCnt) ?>)</td>
                                    <td align=center class=bg1>병장(총)</td>
                                    <td align=center><?= number_format($crewTotal) ?>/<?= number_format($armedGenTotal) ?>(<?= number_format($genTotal) ?>)</td>
                                    <td align=center class=bg1>90병장</td>
                                    <td align=center><?= number_format($crew90) ?>/<?= number_format($gen90) ?></td>
                                    <td align=center class=bg1>60병장</td>
                                    <td align=center><?= number_format($crew60) ?>/<?= number_format($gen60) ?></td>
                                    <td align=center class=bg1>수비○</td>
                                    <td align=center><?= number_format($crewDef) ?>/<?= number_format($genDef) ?></td>
                                </tr>
                                <tr>
                                    <td align=center class=bg1>장수</td>
                                    <td colspan=11><?= $showDetailedInfo ? join(', ', $generalsName) : '<span style="color:gray">알 수 없음</span>' ?></td>
                                </tr>
                            </table>

                            <br>
                            <?php if ($showDetailedInfo) : ?>
                                <table align=center class='tb_layout bg0'>
                                    <thead>
                                        <tr>
                                            <td width=64 align=center class=bg1>얼 굴</td>
                                            <td width=128 align=center class=bg1>이 름</td>
                                            <td width=48 align=center class=bg1>통솔</td>
                                            <td width=48 align=center class=bg1>무력</td>
                                            <td width=48 align=center class=bg1>지력</td>
                                            <td width=78 align=center class=bg1>관 직</td>
                                            <td width=28 align=center class=bg1>守</td>
                                            <td width=78 align=center class=bg1>병 종</td>
                                            <td width=78 align=center class=bg1>병 사</td>
                                            <td width=48 align=center class=bg1>훈련</td>
                                            <td width=48 align=center class=bg1>사기</td>
                                            <td width=280 align=center class=bg1>명 령</td>
                                        </tr>
                                    </thead>
                                    <tbody class='bg0' id='general_list'>
                                        <?php
                                        foreach ($generalsFormat as $general) {
                                            echo $templates->render('cityGeneral', $general);
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                            <table align=center width=1000 class='tb_layout bg0'>
                                <tr>
                                    <td><?= backButton() ?></td>
                                </tr>
                                <tr>
                                    <td><?= banner() ?> </td>
                                </tr>
                            </table>
</body>

</html>