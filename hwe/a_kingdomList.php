<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gameStor->cacheValues(['killturn', 'autorun_user', 'turnterm']);
increaseRefresh("세력일람", 2);

$me = $db->queryFirstRow('SELECT con, turntime FROM general WHERE owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 세력일람</title>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('ts', ['common', 'extKingdoms']) ?>
    <?= WebUtil::printStaticValues($gameStor->getValues(['killturn', 'autorun_user', 'turnterm'])) ?>
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>세 력 일 람<br><?= closeButton() ?></td>
        </tr>
    </table>
    <?php

    $nations = getAllNationStaticInfo();
    uasort($nations, function ($lhs, $rhs) {
        return $rhs['power'] <=> $lhs['power'];
    });

    $nations[0] = getNationStaticInfo(0);

    foreach ($db->query('SELECT npc,name,city,nation,officer_level,penalty,permission FROM general ORDER BY dedication DESC') as $general) {
        $nationID = $general['nation'];

        if (!key_exists('generals', $nations[$nationID])) {
            $nations[$nationID]['generals'] = [];
        }
        $nations[$nationID]['generals'][] = $general;
    }

    foreach ($db->queryAllLists('SELECT city, name, nation FROM city') as [$cityID, $cityName, $nationID]) {
        if (!key_exists('cities', $nations[$nationID])) {
            $nations[$nationID]['cities'] = [];
        }
        $nations[$nationID]['cities'][$cityID] = $cityName;
    }

    foreach ($nations as $nation) {
        if ($nation['nation'] == 0) {
            //재야 도시, 장수
            continue;
        }
        $generals = $nation['generals'];

        $chiefs = [];
        $ambassadors = [];
        $auditors = [];
        foreach ($generals as $general) {
            $officerLevel = $general['officer_level'];
            if ($officerLevel >= 5) {
                $chiefs[$officerLevel] = $general;
            }
            $generalPermission = checkSecretPermission($general, false);
            if ($generalPermission == 4) {
                $ambassadors[] = $general['name'];
            } else if ($generalPermission == 3) {
                $auditors[] = $general['name'];
            }
        }

        echo "
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=8 align=center style=color:" . newColor($nation['color']) . "; bgcolor={$nation['color']}>【 {$nation['name']} 】</td>
    </tr>
    <tr>
        <td width=80 align=center class='bg1'>성 향</td>
        <td width=170 align=center><font color=yellow>" . getNationType($nation['type']) . "</font></td>
        <td width=80 align=center class='bg1'>작 위</td>
        <td width=170 align=center>" . getNationLevel($nation['level']) . "</td>
        <td width=80 align=center class='bg1'>국 력</td>
        <td width=170 align=center>{$nation['power']}</td>
        <td width=80 align=center class='bg1'>장수 / 속령</td>
        <td width=170 align=center>{$nation['gennum']} / " . count($nation['cities'] ?? []) . "</td>
    ";
        for ($chiefLevel = 12; $chiefLevel >= 5; $chiefLevel--) {
            if ($chiefLevel % 4 == 0) {
                echo '</tr><tr>';
            }
            $chief = $chiefs[$chiefLevel] ?? ['name' => '-', 'npc' => 0];
            $officerLevelText = getOfficerLevelText($chiefLevel, $nation['level']);
            $chiefText = getColoredName($chief['name'], $chief['npc']);
            echo "<td class='center bg1'>{$officerLevelText}</td>
        <td class='center'>{$chiefText}</td>";
        }
        echo "</tr>
    <tr>
        <td align=center class='bg1'>외교권자</td><td colspan=5>";
        echo join(', ', $ambassadors);
        echo "</td><td align=center class='bg1'>조언자</td><td align=center >";
        echo count($auditors) . '명';
        echo "</td></tr>
    <tr>
        <td colspan=8>";
        if ($nation['level'] > 0) {
            echo "속령 일람 : ";

            foreach ($nation['cities'] as $cityID => $cityName) {
                if ($cityID == $nation['capital']) {
                    echo "<font color=cyan>[{$cityName}]</font>, ";
                } else {
                    echo "{$cityName}, ";
                }
            }
        } else {
            $cityName = CityConst::byID($chiefs[12]['city'])->name;

            echo "현재 위치 : <font color=yellow>{$cityName}</font>";
        }
        echo "
        </td>
    </tr>
    <tr>
        <td colspan=8> 장수 일람 : ";
        foreach ($generals as $general) {
            echo formatName($general['name'], $general['npc']).', ';
        }
        echo "
        </td>
    </tr>
</table>
<br>";
    }

    //재야
    echo "
<table align=center width=1000 class='tb_layout bg2'>
    <tr>
        <td colspan=5 align=center>【 재 야 】</td>
    </tr>
    <tr>
        <td width=498 align=center>&nbsp;</td>
        <td width=123 align=center class='bg1'>장 수</td>
        <td width=123 align=center>{$nations[0]['gennum']}</td>
        <td width=123 align=center class='bg1'>속 령</td>
        <td width=123 align=center>" . count($nations[0]['cities'] ?? []) . "</td>
    </tr>
    <tr>
        <td colspan=5> 속령 일람 : ";
    foreach ($nations[0]['cities'] ?? [] as $cityName) {
        echo "{$cityName}, ";
    }
    echo "
        </td>
    </tr>
    <tr>
        <td colspan=5> 장수 일람 : ";
    foreach ($nations[0]['generals'] as $general) {
        $generalText = getColoredName($general['name'], $general['npc']);
        echo "{$generalText}, ";
    }
    echo "
        </td>
    </tr>
</table>";

    ?>

    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td><?= closeButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?></td>
        </tr>
    </table>
</body>

</html>