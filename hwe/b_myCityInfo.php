<?php

namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getReq('type', 'int', 10);
if ($type <= 0 || $type > 12) {
    $type = 10;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
increaseRefresh("세력도시", 1);

$me = $db->queryFirstRow('SELECT no,nation,officer_level FROM general WHERE owner=%i', $userID);
$nationID = $me['nation'];

if ($me['officer_level'] == 0) {
    echo "재야입니다.";
    exit();
}

$sel = [$type => "selected"];

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 세력도시</title>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printDist('vue', [], true) ?>
    <?= WebUtil::printDist('ts', ['common', 'extExpandCity']) ?>
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>세 력 도 시<br><?= backButton() ?></td>
        </tr>
        <tr>
            <td>
                <form name=form1 method=post>정렬순서 :
                    <select name=type size=1>
                        <option <?= $sel[1] ?? '' ?> value=1>기본</option>
                        <option <?= $sel[2] ?? '' ?> value=2>인구</option>
                        <option <?= $sel[3] ?? '' ?> value=3>인구율</option>
                        <option <?= $sel[4] ?? '' ?> value=4>민심</option>
                        <option <?= $sel[5] ?? '' ?> value=5>농업</option>
                        <option <?= $sel[6] ?? '' ?> value=6>상업</option>
                        <option <?= $sel[7] ?? '' ?> value=7>치안</option>
                        <option <?= $sel[8] ?? '' ?> value=8>수비</option>
                        <option <?= $sel[9] ?? '' ?> value=9>성벽</option>
                        <option <?= $sel[10] ?? '' ?> value=10>시세</option>
                        <option <?= $sel[11] ?? '' ?> value=11>지역</option>
                        <option <?= $sel[12] ?? '' ?> value=12>규모</option>
                    </select>
                    <input type=submit value='정렬하기'>
                </form>
            </td>
        </tr>
    </table>
    <?php

    $nation = $db->queryFirstRow('SELECT nation, name, color, type, level, capital, gennum, `power`, `rate` from nation WHERE nation = %i', $nationID);  //국가정보
    $nationTypeObj = buildNationTypeClass($nation['type']);

    $officerList = [];
    foreach ($db->query('SELECT no,name,npc,city,officer_level,officer_city,belong FROM general WHERE nation = %i AND 2 <= officer_level AND officer_level <= 4', $nationID) as $officer) {
        $officerCityID = $officer['officer_city'];
        if (!key_exists($officerCityID, $officerList)) {
            $officerList[$officerCityID] = [];
        }
        $officerList[$officerCityID][$officer['officer_level']] = $officer;
    }

    $generalList = $db->query('SELECT npc, name, city FROM general WHERE nation = %i', $me['nation']);
    $cityGeneralList = [];
    foreach ($generalList as $general) {
        $cityID = $general['city'];
        if (!key_exists($cityID, $cityGeneralList)) {
            $cityGeneralList[$cityID] = [];
        }

        $cityGeneralList[$cityID][] = formatName($general['name'], $general['npc']);
    }


    $cityList = $db->query('SELECT *,pop/pop_max as poprate from city where nation=%i', $nationID);


    switch ($type) {
        case  1:
            break;
        case  2:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['pop'] <=> $lhs['pop'];
            });
            break;
        case  3:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['poprate'] <=> $lhs['poprate'];
            });
            break;
        case  4:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['trust'] <=> $lhs['trust'];
            });
            break;
        case  5:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['agri'] <=> $lhs['agri'];
            });
            break;
        case  6:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['comm'] <=> $lhs['comm'];
            });
            break;
        case  7:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['secu'] <=> $lhs['secu'];
            });
            break;
        case  8:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['def'] <=> $lhs['def'];
            });
            break;
        case  9:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['wall'] <=> $lhs['wall'];
            });
            break;
        case 10:
            usort($cityList, function ($lhs, $rhs) {
                return $rhs['trade'] <=> $lhs['trade'];
            });
            break;
        case 11:
            usort($cityList, function ($lhs, $rhs) {
                $cmpTrust = $lhs['region'] <=> $rhs['region'];
                if ($cmpTrust != 0) {
                    return $cmpTrust;
                }
                return $rhs['level'] <=> $lhs['level'];
            });
            break;
        case 12:
            usort($cityList, function ($lhs, $rhs) {
                $cmpTrust = $rhs['level'] <=> $lhs['level'];
                if ($cmpTrust != 0) {
                    return $cmpTrust;
                }
                return $lhs['region'] <=> $rhs['region'];
            });
            break;
    }

    $region = 0;
    $level = 0;

    foreach ($cityList as $city) {
        $cityID = $city['city'];
        if ($city['city'] == $nation['capital']) {
            $city['name'] = "<font color=cyan>[{$city['name']}]</font>";
        }

        $officerQuery = [];
        $officerName = [
            2 => '-',
            3 => '-',
            4 => '-'
        ];

        $cityOfficerList = $officerList[$cityID] ?? [];
        $effectiveOfficerCnt = 0;
        foreach ($cityOfficerList as $cityOfficer) {
            if ($cityOfficer['city'] == $cityID) {
                $effectiveOfficerCnt += 1;
            }
            $officerName[$cityOfficer['officer_level']] = formatName($cityOfficer['name'], $cityOfficer['npc']);
        }

        if ($type == 10 && $city['region'] != $region) {
            echo "<br>";
            $region = $city['region'];
        } elseif ($type == 11 && $city['level'] != $level) {
            echo "<br>";
            $level = $city['level'];
        }

        if ($city['trade'] === null) {
            $city['trade'] = "- ";
        }


        $cityGoldIncome = $nation['rate'] / 20 * calcCityGoldIncome($city, $effectiveOfficerCnt, $nation['capital'] == $cityID, $nation['level'], $nationTypeObj);
        $cityRiceIncome = $nation['rate'] / 20 * calcCityRiceIncome($city, $effectiveOfficerCnt, $nation['capital'] == $cityID, $nation['level'], $nationTypeObj);
        $cityWallIncome = $nation['rate'] / 20 * calcCityWallRiceIncome($city, $effectiveOfficerCnt, $nation['capital'] == $cityID, $nation['level'], $nationTypeObj);

    ?>
        <table align=center width=1000 class='tb_layout bg2'>
            <tr>
                <td colspan=10 style="color:<?= newColor($nation['color']) ?>; background-color:<?= $nation['color'] ?>;">
                    <font size=2>【 <?= CityConst::$regionMap[$city['region']] ?> | <?= CityConst::$levelMap[$city['level']] ?> 】 <?= $city['name'] ?></font>
                </td>
            </tr>
            <tr style='text-align:center;'>
                <td width=60 class='bg1'>주민</td>
                <td width=140 class='pop-value'><?= $city['pop'] ?>/<?= $city['pop_max'] ?></td>
                <td width=60 class='bg1'>인구율</td>
                <td width=140 class='pop-prop-value'><?= round($city['pop'] / $city['pop_max'] * 100, 2) ?>%</td>
                <td width=60 class='bg1'>자금 수입</td>
                <td width=140 class='gold-income'><?= number_format($cityGoldIncome) ?></td>
                <td width=60 class='bg1'>군량 수입</td>
                <td width=140 class='rice-income'><?= number_format($cityRiceIncome) ?></td>
                <td width=60 class='bg1'>둔전 수입</td>
                <td width=140 class='wall-income'><?= number_format($cityWallIncome) ?></td>
            </tr>
            <tr style='text-align:center;'>
                <td class='bg1'>농업</td>
                <td class='agri-value'><?= $city['agri'] ?>/<?= $city['agri_max'] ?></td>
                <td class='bg1'>상업</td>
                <td class='comm-value'><?= $city['comm'] ?>/<?= $city['comm_max'] ?></td>
                <td class='bg1'>치안</td>
                <td class='secu-value'><?= $city['secu'] ?>/<?= $city['secu_max'] ?></td>
                <td class='bg1'>수비</td>
                <td class='def-value'><?= $city['def'] ?>/<?= $city['def_max'] ?></td>
                <td class='bg1'>성벽</td>
                <td class='wall-value'><?= $city['wall'] ?>/<?= $city['wall_max'] ?></td>
            </tr>
            <tr style='text-align:center;'>
                <td class='bg1'>민심</td>
                <td class='trust-value'><?= round($city['trust'], 1) ?></td>
                <td class='bg1'>시세</td>
                <td class='trade-value'><?= $city['trade'] ?>%</td>
                <td class='bg1'>태수</td>
                <td class='officer-4-value'><?= $officerName[4] ?></td>
                <td class='bg1'>군사</td>
                <td class='officer-3-value'><?= $officerName[3] ?></td>
                <td class='bg1'>종사</td>
                <td class='officer-2-value'><?= $officerName[2] ?></td>
            </tr>
            <tr>
                <td style='text-align:center;' class='bg1'>장수</td>
                <td colspan=9 class='city-generals'><?= key_exists($cityID, $cityGeneralList) ? join(', ', $cityGeneralList[$cityID]) : '-' ?></td>
            </tr>
        </table>
    <?php } ?>

    <table align=center width=1000 class='tb_layout bg0 anchor'>
        <tr>
            <td><?= backButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?></td>
        </tr>
    </table>
    <div id="helper_genlist" style="display:none;"></div>
</body>

</html>