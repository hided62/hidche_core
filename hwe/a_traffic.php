<?php

namespace sammo;

use sammo\Enums\GeneralAccessLogColumn;

include "lib.php";
include "func.php";

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("갱신정보", 1);

$admin = $gameStor->getValues(['year', 'month', 'refresh', 'maxrefresh', 'maxonline', 'recentTraffic']);

$recentTraffic = $admin['recentTraffic'] ?? [];

$curonline = getOnlineNum();

$recentTraffic[] = [
    'year'    => $admin['year'],
    'month'   => $admin['month'],
    'refresh' => $admin['refresh'],
    'online'  => $curonline,
    'date' => TimeUtil::now()
];

if ($admin['maxrefresh'] == 0) {
    $admin['maxrefresh'] = 1;
}
if ($admin['maxrefresh'] < $admin['refresh']) {
    $admin['maxrefresh'] = $admin['refresh'];
}
if ($admin['maxonline'] == 0) {
    $admin['maxonline'] = 1;
}
if ($admin['maxonline'] < $curonline) {
    $admin['maxonline'] = $curonline;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 트래픽정보</title>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <style>
        .big_bar {
            float: left;
            position: relative;
            height: 30px;
        }

        .big_bar span {
            float: right;
            padding: 0;
            margin: 0;
            line-height: 30px;
            padding-right: 1ch;
        }

        .little_bar {
            float: left;
            position: relative;
            height: 17px;
        }

        span.out_bar {
            line-height: 30px;
            margin-left: 1ch;
        }
    </style>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printDist('vue', [], true) ?>
    <?= WebUtil::printDist('ts', ['common']) ?>
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>트 래 픽 정 보<br><?= closeButton() ?></td>
        </tr>
    </table>
    <br>
    <table align=center width=1016>
        <tr>
            <td align=left>
                <table align=center class='tb_layout bg0'>
                    <tr>
                        <td colspan=4 align=center class='bg2'>
                            <font size=5>접 속 량</font>
                        </td>
                    </tr>
                    <?php
                    foreach ($recentTraffic as $trafficItem) {
                        $value = $trafficItem['refresh'];
                        $w = round($value / $admin['maxrefresh'] * 100, 1);
                        $color = getTrafficColor($w);
                        $dt = substr($trafficItem['date'], 11, 5); ?>
                        <tr height=30>
                            <td width=100 align=center><?= $trafficItem['year'] ?>년 <?= $trafficItem['month'] ?>월</td>
                            <td width=60 align=center class='bg2'><?= $dt ?></td>
                            <td width=2 align=center class='bg1'></td>
                            <td width=320>
                                <?php if ($w == 0) : ?>
                                    <span class="out_bar"><?= $value ?></span>
                                <?php elseif ($w < 10) : ?>
                                    <div class='big_bar' style='width:<?= $w ?>%;background-color:<?= $color ?>;'></div><span class="out_bar"><?= $value ?></span>
                                <?php else : ?>
                                    <div class='big_bar' style='width:<?= $w ?>%;background-color:<?= $color ?>;'><span><?= $value ?></span></div>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td colspan=4 height=5 align=center class='bg1'></td>
                    </tr>
                    <tr>
                        <td colspan=4 height=30 align=center class='bg0'>최고기록: <?= $admin['maxrefresh'] ?></td>
                    </tr>


                </table>
            </td>
            <td align=right>
                <table align=center class='tb_layout bg0'>
                    <tr>
                        <td colspan=4 align=center class='bg2'>
                            <font size=5>접 속 자</font>
                        </td>
                    </tr>
                    <?php
                    foreach ($recentTraffic as $trafficItem) {
                        $value = $trafficItem['online'];
                        $w = round($value / $admin['maxonline'] * 100, 1);
                        $color = getTrafficColor($w);
                        $dt = substr($trafficItem['date'], 11, 5); ?>
                        <tr height=30>
                            <td width=100 align=center><?= $trafficItem['year'] ?>년 <?= $trafficItem['month'] ?>월</td>
                            <td width=60 align=center class='bg2'><?= $dt ?></td>
                            <td width=2 align=center class='bg1'></td>
                            <td width=320>
                                <?php if ($w == 0) : ?>
                                    <span class="out_bar"><?= $value ?></span>
                                <?php elseif ($w < 10) : ?>
                                    <div class='big_bar' style='width:<?= $w ?>%;background-color:<?= $color ?>;'></div><span class="out_bar"><?= $value ?></span>
                                <?php else : ?>
                                    <div class='big_bar' style='width:<?= $w ?>%;background-color:<?= $color ?>;'><span><?= $value ?></span></div>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td colspan=4 height=5 align=center class='bg1'></td>
                    </tr>
                    <tr>
                        <td colspan=4 height=30 align=center class='bg0'>최고기록: <?= $admin['maxonline'] ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <table align=center class='tb_layout bg0'>
        <tr>
            <td colspan=3 align=center class='bg2'>
                <font size=5>주 의 대 상 자 (순간과도갱신)</font>
            </td>
        </tr>
        <?php
        $totalRefresh = [
            ...$db->queryFirstRow('SELECT sum(`refresh`) as `refresh`, sum(`refresh_score_total`) as `refresh_score_total` from general_access_log'),
            'name' => '접속자 총합'
        ];

        $top5Refresh = $db->query(
            'SELECT `name`, `log`.`refresh`, `refresh_score_total` FROM `general_access_log` AS `log`
            INNER JOIN `general` ON `log`.`general_id` = `general`.`no` ORDER BY `log`.`refresh` DESC LIMIT 5'
        );

        foreach (array_merge([$totalRefresh], $top5Refresh) as $i => $user) {
            $w = round($user['refresh'] / max(1, $totalRefresh['refresh']) * 100, 1);
            $w2 = round(100 - $w, 1);
            $color = getTrafficColor($w);
        ?>
            <tr>
                <td width=98 align=center><?= $user['name'] ?></td>
                <td width=98 align=center><?= $user['refresh_score_total'] ?>(<?= $user['refresh'] ?>)</td>
                <td width=798>
                    <?php if ($w == 0) : ?>
                    <?php elseif ($w < 10) : ?>
                        <div class='little_bar' style='width:<?= $w ?>%;background-color:<?= $color ?>;'></div>
                    <?php else : ?>
                        <div class='little_bar' style='width:<?= $w ?>%;background-color:<?= $color ?>;'></div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </table>
    <br>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td><?= closeButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?> </td>
        </tr>
    </table>
</body>

</html>

<?php
function getTrafficColor($per)
{
    $r = getHex($per);
    $b = getHex(100 - $per);
    $color = $r . "00" . $b;
    return '#' . $color;
}

function getHex($dec)
{
    $hex = intdiv($dec * 255, 100);
    $code = getHexCode(intdiv($hex, 16));
    $code .= getHexCode($hex % 16);
    return $code;
}

function getHexCode($hex)
{
    switch ($hex) {
        case  0:
            return "0";
        case  1:
            return "1";
        case  2:
            return "2";
        case  3:
            return "3";
        case  4:
            return "4";
        case  5:
            return "5";
        case  6:
            return "6";
        case  7:
            return "7";
        case  8:
            return "8";
        case  9:
            return "9";
        case 10:
            return "A";
        case 11:
            return "B";
        case 12:
            return "C";
        case 13:
            return "D";
        case 14:
            return "E";
        case 15:
            return "F";
    }
}
