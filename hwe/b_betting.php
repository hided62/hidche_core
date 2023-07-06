<?php

namespace sammo;

use sammo\Enums\RankColumn;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("베팅장", 1);
TurnExecutionHelper::executeAllCommand();

$generalID = $session->generalID;




$me = $db->queryFirstRow(
    'SELECT no,tournament,refresh_score,turntime FROM `general`
    LEFT JOIN general_access_log AS l ON `general`.no = l.general_id where owner=%i', $userID
);

$admin = $gameStor->getValues(['tournament', 'phase', 'tnmt_type', 'turnterm', 'develcost']);
$turnTerm = $admin['turnterm'];
$limitState = checkLimit($me['refresh_score']);
if ($limitState >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

switch ($admin['tnmt_type']) {
    case convertTournamentType('전력전'):
        $tnmt_type = "전력전";
        $color = "cyan";
        $tp = "total";
        $tp2 = "종합";
        $tp3 = "total";
        break;
    case convertTournamentType('통솔전'):
        $tnmt_type = "통솔전";
        $color = "cyan";
        $tp = "leadership";
        $tp2 = "통솔";
        $tp3 = "leadership";
        break;
    case convertTournamentType('일기토'):
        $tnmt_type = "일기토";
        $color = "cyan";
        $tp = "strength";
        $tp2 = "무력";
        $tp3 = "strength";
        break;
    case convertTournamentType('설전'):
        $tnmt_type = "설전";
        $color = "cyan";
        $tp = "intel";
        $tp2 = "지력";
        $tp3 = "intel";
        break;
    default:
        throw new \RuntimeException('Invalid tnmt_type');
}

$bettingID = $gameStor->last_tournament_betting_id ?? 0;
$myBet = [];
$globalBet = [];

if ($bettingID != 0) {
    $betting = $bettingID != 0 ? new Betting($bettingID) : null;
    $info = $betting->getInfo();

    foreach ($db->queryAllLists(
        'SELECT general_id, user_id, amount, betting_type FROM ng_betting WHERE betting_id = %i',
        $bettingID
    ) as [$betGeneralID, $userID, $amount, $bettingTypeKey]) {
        $bettingKey = Json::decode($bettingTypeKey)[0];
        if ($betGeneralID == $generalID) {
            $myBet[$bettingKey] = $amount;
        }

        if (key_exists($bettingKey, $globalBet)) {
            $globalBet[$bettingKey] += $amount;
        } else {
            $globalBet[$bettingKey] = $amount;
        }
    }
} else {
    $info = getDummyBettingInfo($tnmt_type);
}

$myBetTotal = array_sum($myBet);
$globalBetTotal = array_sum($globalBet);

$str1 = getTournament($admin['tournament']);
$str2 = getTournamentTime();
if ($str2) {
    $str2 = ', ' . $str2;
}
$str3 = getTournamentTermText($turnTerm);
if ($str3) {
    $str3 = ', ' . $str3;
}

?>
<!DOCTYPE html>
<html>
<?php if ($limitState == 1) {
    MessageBox("접속제한이 얼마 남지 않았습니다!");
} ?>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 베팅장</title>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('vue', [], true) ?>
    <?= WebUtil::printDist('ts', ['common', 'betting']) ?>
    <?= WebUtil::printStaticValues([
        'staticValues'=>[
            'bettingID'=> $bettingID
        ]
    ])?>
</head>

<body>
    <table align=center width=1120 class='tb_layout bg0'>
        <tr>
            <td>베 팅 장<br><?= closeButton() ?></td>
        </tr>
    </table>
    <table align=center width=1120 class='tb_layout bg0'>
        <tr>
            <td colspan=16><button type="button" class="btn btn-secondary" onclick='location.reload()'>갱신</button></td>
        </tr>
        <tr>
            <td colspan=16 align=center>
                <font color=white size=6><span style="color:<?= $color ?>"><?= $tnmt_type ?></span> (<?= $str1 . $str2 . $str3 ?>)</font>
            </td>
        </tr>
        <tr>
            <td height=50 colspan=16 align=center class='bg2'>
                <font color=limegreen size=6>16강 상황</font><br>
                <font color=orange size=3>(전체 금액 : <?= $globalBetTotal ?> / 내 투자 금액 : <?= $myBetTotal ?>)</font>
            </td>
        </tr>
    </table>
    <table align=center width=1120 class='mimic_flex bg0' style='border:solid 1px gray;font-size:10px;'>
        <tr align=center>
            <td height=10 colspan=16></td>
        </tr>
        <tr align=center>
            <?php
            $generalList = $db->query('SELECT npc,name,win from tournament where grp>=60 order by grp, grp_no LIMIT 1');
            while (count($generalList) < 1) {
                $generalList[] = [
                    'name' => '-',
                    'npc' => 0,
                    'win' => 0
                ];
            }
            foreach ($generalList as $i => $general) {
                if ($general['name'] == "") {
                    $general['name'] = "-";
                }

                $general['name'] = formatName($general['name'], $general['npc']);
                echo "<td colspan=16>{$general['name']}</td>";
            }

            echo "
    </tr>
    <tr align=center>";

            $cent = [];
            $line = [];
            $gen = [];
            for ($i = 0; $i < 1; $i++) {
                $cent[$i] = "<font color=white>";
            }
            $generalList = $db->query('SELECT npc,name,win from tournament where grp>=50 order by grp, grp_no LIMIT 2');
            while (count($generalList) < 2) {
                $generalList[] = [
                    'name' => '-',
                    'npc' => 0,
                    'win' => 0
                ];
            }
            foreach ($generalList as $i => $general) {
                if ($general['name'] == "") {
                    $general['name'] = "-";
                }
                $general['name'] = formatName($general['name'], $general['npc']);
                if ($general['win'] > 0) {
                    $line[$i] = "<font color=red>";
                    $cent[intdiv($i, 2)] = "<font color=red>";
                } else {
                    $line[$i] = "<font color=white>";
                }
                $gen[$i] = $general['name'];
            }
            for ($i = 0; $i < 1; $i++) {
                $cent[$i] = $cent[$i] . "┻" . "</font>";
                $line[$i * 2] =     $line[$i * 2] . "┏━━━━━━━━━━━━━━━━━━━━━━━━━━━" . "</font>";
                $line[$i * 2 + 1] = $line[$i * 2 + 1] . "━━━━━━━━━━━━━━━━━━━━━━━━━━━┓" . "</font>";
                echo "<td colspan=16>{$line[$i * 2]}{$cent[$i]}{$line[$i * 2 + 1]}</td>";
            }
            echo "
    </tr>
    <tr align=center>";

            for ($i = 0; $i < 2; $i++) {
                echo "<td colspan=8>{$gen[$i]}</td>";
            }

            echo "
    </tr>
    <tr align=center>";

            for ($i = 0; $i < 2; $i++) {
                $cent[$i] = "<font color=white>";
            }
            $generalList = $db->query('SELECT npc,name,win from tournament where grp>=40 order by grp, grp_no LIMIT 4');
            while (count($generalList) < 4) {
                $generalList[] = [
                    'name' => '-',
                    'npc' => 0,
                    'win' => 0
                ];
            }
            foreach ($generalList as $i => $general) {
                if ($general['name'] == "") {
                    $general['name'] = "-";
                }
                $general['name'] = formatName($general['name'], $general['npc']);
                if ($general['win'] > 0) {
                    $line[$i] = "<font color=red>";
                    $cent[intdiv($i, 2)] = "<font color=red>";
                } else {
                    $line[$i] = "<font color=white>";
                }
                $gen[$i] = $general['name'];
            }
            for ($i = 0; $i < 2; $i++) {
                $cent[$i] = $cent[$i] . "┻" . "</font>";
                $line[$i * 2] =     $line[$i * 2] . "┏━━━━━━━━━━━━━" . "</font>";
                $line[$i * 2 + 1] = $line[$i * 2 + 1] . "━━━━━━━━━━━━━┓" . "</font>";
                echo "<td colspan=8>{$line[$i * 2]}{$cent[$i]}{$line[$i * 2 + 1]}</td>";
            }
            echo "
    </tr>
    <tr align=center>";

            for ($i = 0; $i < 4; $i++) {
                echo "<td colspan=4>{$gen[$i]}</td>";
            }

            echo "
    </tr>
    <tr align=center>";

            for ($i = 0; $i < 4; $i++) {
                $cent[$i] = "<font color=white>";
            }
            $generalList = $db->query('SELECT npc,name,win from tournament where grp>=30 order by grp, grp_no LIMIT 8');
            while (count($generalList) < 8) {
                $generalList[] = [
                    'name' => '-',
                    'npc' => 0,
                    'win' => 0
                ];
            }
            foreach ($generalList as $i => $general) {
                if ($general['name'] == "") {
                    $general['name'] = "-";
                }
                $general['name'] = formatName($general['name'], $general['npc']);
                if ($general['win'] > 0) {
                    $line[$i] = "<font color=red>";
                    $cent[intdiv($i, 2)] = "<font color=red>";
                } else {
                    $line[$i] = "<font color=white>";
                }
                $gen[$i] = $general['name'];
            }
            for ($i = 0; $i < 4; $i++) {
                $cent[$i] = $cent[$i] . "┻" . "</font>";
                $line[$i * 2] =     $line[$i * 2] . "┏━━━━━━" . "</font>";
                $line[$i * 2 + 1] = $line[$i * 2 + 1] . "━━━━━━┓" . "</font>";
                echo "<td colspan=4>{$line[$i * 2]}{$cent[$i]}{$line[$i * 2 + 1]}</td>";
            }
            echo "
    </tr>
    <tr align=center>";

            for ($i = 0; $i < 8; $i++) {
                echo "<td colspan=2>{$gen[$i]}</td>";
            }
            echo "
    </tr>
    <tr align=center>";

            for ($i = 0; $i < 8; $i++) {
                $cent[$i] = "<font color=white>";
            }
            $generalList = $db->query('SELECT npc,name,win,leadership,strength,intel,leadership+strength+intel as total from tournament where grp>=20 order by grp, grp_no LIMIT 16');
            while (count($generalList) < 16) {
                $generalList[] = [
                    'name' => '-',
                    'npc' => 0,
                    'win' => 0
                ];
            }
            foreach ($generalList as $i => $general) {
                if ($general['name'] == "") {
                    $general['name'] = "-";
                }
                $general['name'] = formatName($general['name'], $general['npc']);
                if ($general['win'] > 0) {
                    $line[$i] = "<font color=red>";
                    $cent[intdiv($i, 2)] = "<font color=red>";
                } else {
                    $line[$i] = "<font color=white>";
                }
                $gen[$i] = $general['name'];
                if (array_key_exists($tp, $general)) {
                    $stat[$i] = $general[$tp];
                } else {
                    $stat[$i] = "-";
                }
            }
            for ($i = 0; $i < 8; $i++) {
                $cent[$i] = $cent[$i] . "┻" . "</font>";
                $line[$i * 2] =     $line[$i * 2] . "┏━━" . "</font>";
                $line[$i * 2 + 1] = $line[$i * 2 + 1] . "━━┓" . "</font>";
                echo "<td colspan=2>{$line[$i * 2]}{$cent[$i]}{$line[$i * 2 + 1]}</td>";
            }
            ?>
        </tr>
        <tr align=center>
            <?php for ($i = 0; $i < 16; $i++) { ?>
                <td width=70><?= $gen[$i] ?></td>
            <?php } ?>
        </tr>
        <tr align=center>
            <?php for ($i = 0; $i < 16; $i++) { ?>
                <td width=70 style="font-size: 14px"><?= $stat[$i] ?></td>
            <?php } ?>
        </tr>
    </table>
    <table align=center width=1120 style="table-layout: fixed" class='tb_layout bg0'>
        <tr align=center>
            <td height=10 colspan=16></td>
        </tr>
        <?php

        $bet = [];
        $gold = [];
        $keyMap = [];
        foreach ($info->candidates as $key => $candidate) {
            $keyMap[$candidate->aux['idx']] = $key;
        }

        foreach ($globalBet as $key => $amount) {
            if ($amount == 0) {
                $bet[$key] = "∞";
                $gold[$key] = 0;
            } else {
                $rewardRatio = round($globalBetTotal / $amount, 2);
                $bet[$key]  = $rewardRatio;
                $gold[$key] = $rewardRatio * ($myBet[$key] ?? 0);
            }
        }
        echo "
    <tr align=center>";

        foreach (range(0, 15) as  $i) {
            $amount = $bet[$keyMap[$i] ?? -1] ?? 0;
            echo "<td width=70><font color=skyblue>{$amount}</font></td>";
        }
        ?>
        </tr>
        <tr align=center>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
            <td>×</td>
        </tr>
        <tr align=center>
            <?php
            foreach (range(0, 15) as  $i) {
                $amount = $myBet[$keyMap[$i] ?? -1] ?? 0;
                echo "<td><font color=orange>{$amount}</font></td>";
            }
            ?>
        </tr>
        <tr align=center>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
            <td>∥</td>
        </tr>
        <tr align=center>
            <?php
            foreach (range(0, 15) as  $i) {
                $amount = $gold[$keyMap[$i] ?? -1] ?? 0;
                echo "<td><font color=cyan>{$amount}</font></td>";
            }

            echo "
    </tr>
    <tr align=center><td height=10 colspan=16></td></tr>";

            if ($admin['tournament'] == 6) {
                echo "
    <tr align=center style='height:2.5em;'>";

                foreach (range(0, 15) as  $i) {
                    $key = $keyMap[$i] ?? -1;
                    echo "
        <td>
            <select size=1 id='target_{$key}' style='color:white;background-color:black;padding:0.2rem 0.1rem;'>
                <option style=color:white; value=10>금10</option>
                <option style=color:white; value=20>금20</option>
                <option style=color:white; value=50>금50</option>
                <option style=color:white; value=100>금100</option>
                <option style=color:white; value=200>금200</option>
                <option style=color:white; value=500>금500</option>
                <option style=color:white; value=1000>최대</option>
            </select>
        </td>";
                }

                echo "
    </tr>
    <tr align=center style='height:2.2em;'>";

                foreach (range(0, 15) as  $i) {
                    $key = $keyMap[$i] ?? -1;
                    echo "
        <td><input type=button class='submitBtn' data-target='{$key}' value=베팅! style='width:100%;color:white;padding:0.2rem 0.1rem;background-color:black;'></td>";
                }

                echo "</tr>";
            }

            ?>
        <tr align=center>
            <td height=30 colspan=16>
                <font color=skyblue size=4>배당률</font> × <font color=orange size=4>베팅금</font> = <font color=cyan size=4>적중시 환수금</font><br>
                <font color=skyblue size=4>( 베팅후 500원 이하일땐 베팅이 불가능합니다. )</font>
            </td>
        </tr>
        <tr align=center>
            <td height=10 colspan=16></td>
        </tr>
    </table>
    <table align=center width=1120 class='tb_layout bg0'>
        <tr align=center>
            <td height=50 colspan=4 class='bg2'>
                <font color=yellow size=6>토너먼트 랭킹</font>
            </td>
        </tr>
        <tr align=center>
            <td colspan=4 class='bg2'>
                <font color=skyblue size=3>순위 / 장수명 / 능력치 / 경기수 / 승리 / 무승부 / 패배 / 집계점수 / 우승횟수</font>
            </td>
        </tr>
        <tr align=center>
            <?php

            $tournamentType = [
                '전 력 전' => [
                    '종합',
                    function (General $general) {
                        return $general->getVar('leadership') + $general->getVar('strength') + $general->getVar('intel');
                    },
                    'tt',
                ],
                '통 솔 전' => [
                    '통솔',
                    function (General $general) {
                        return $general->getVar('leadership');
                    },
                    'tl',
                ],
                '일 기 토' => [
                    '무력',
                    function (General $general) {
                        return $general->getVar('strength');
                    },
                    'ts',
                ],
                '설 전' => [
                    '지력',
                    function (General $general) {
                        return $general->getVar('intel');
                    },
                    'ti',
                ],
            ];

            $type1 = array("전 력 전", "통 솔 전", "일 기 토", "설 전");
            $type2 = array("종합", "통솔", "무력", "지력");
            $type3 = array("tt", "tl", "ts", "ti");
            $type4 = array("total", "leadership", "strength", "intel");

            foreach ($tournamentType as $tournamentTypeText => [$statTypeText, $statFunc, $rankColumn]) : ?>
                <td>
                    <table align=center width=280 class='tb_layout bg0 f_tnum'>
                        <tr>
                            <td colspan=9 align=center style=color:white;background-color:black;>
                                <font size=4><?= $tournamentTypeText ?></font>
                            </td>
                        </tr>
                        <tr class='bg1'>
                            <td align=center>순</td>
                            <td align=center>장수</td>
                            <td align=center><?= $statTypeText ?></td>
                            <td align=center>경</td>
                            <td align=center>승</td>
                            <td align=center>무</td>
                            <td align=center>패</td>
                            <td align=center>점</td>
                            <td align=center>勝</td>
                        </tr>
                        <?php
                        $prizeColumn = RankColumn::from("{$rankColumn}p");
                        $gameColumn = RankColumn::from("{$rankColumn}g");
                        $winColumn = RankColumn::from("{$rankColumn}w");
                        $drawColumn = RankColumn::from("{$rankColumn}d");
                        $loseColumn = RankColumn::from("{$rankColumn}l");
                        $tournamentRankerList = General::createGeneralObjListFromDB(
                            $db->queryFirstColumn('SELECT general_id FROM rank_data WHERE `type`= %s ORDER BY value DESC LIMIT 40', $gameColumn->value),
                            [$prizeColumn->value, $gameColumn->value, $winColumn->value, $drawColumn->value, $loseColumn->value, 'leadership', 'strength', 'intel', 'no', 'npc', 'name'],
                            0
                        );
                        usort($tournamentRankerList, function (General $lhs, General $rhs) use ($gameColumn, $winColumn, $drawColumn, $loseColumn) {
                            $result = - ($lhs->getRankVar($gameColumn) <=> $rhs->getRankVar($gameColumn));
                            if ($result !== 0) return $result;
                            $result = - (
                                ($lhs->getRankVar($winColumn) + $lhs->getRankVar($drawColumn) + $lhs->getRankVar($loseColumn))
                                <=>
                                ($rhs->getRankVar($winColumn) + $rhs->getRankVar($drawColumn) + $rhs->getRankVar($loseColumn))
                            );
                            if ($result !== 0) return $result;
                            $result = - ($lhs->getRankVar($winColumn) <=> $rhs->getRankVar($winColumn));
                            if ($result !== 0) return $result;
                            $result = - ($lhs->getRankVar($drawColumn) <=> $rhs->getRankVar($drawColumn));
                            if ($result !== 0) return $result;
                            return $lhs->getRankVar($loseColumn) <=> $rhs->getRankVar($loseColumn);
                        });
                        $tournamentRankerList = array_splice($tournamentRankerList, 0, 30);
                        foreach ($tournamentRankerList as $rank => $ranker) {
                            printRow(
                                $rank,
                                $ranker->getNPCType(),
                                $ranker->getName(),
                                ($statFunc)($ranker),
                                $ranker->getRankVar($winColumn) + $ranker->getRankVar($drawColumn) + $ranker->getRankVar($loseColumn),
                                $ranker->getRankVar($winColumn),
                                $ranker->getRankVar($drawColumn),
                                $ranker->getRankVar($loseColumn),
                                $ranker->getRankVar($gameColumn),
                                $ranker->getRankVar($prizeColumn),
                                0
                            );
                        }
                        ?>
                    </table>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td colspan=16>
                ㆍ토너먼트의 16강 대진표가 완성되면, 베팅 기간이 주어집니다.<br>
                ㆍ유저들의 베팅 상황에 따라 배당률이 실시간 결정되며, 자신의 베팅금에 따른 예상 환급금을 알 수 있습니다.<br>
                ㆍ베팅은 16슬롯에 각각 베팅 가능하며, 도합 최대 금 1000씩 베팅 가능합니다.<br>
                ㆍ소지금 500원 이하일땐 베팅이 불가능합니다.
                ㆍ삼모와 더불어 토너먼트, 베팅기능으로 즐거운 삼모 되세요!<br>
            </td>
        </tr>
    </table>
    <table align=center width=1120 class='tb_layout bg0'>
        <tr>
            <td><?= closeButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?></td>
        </tr>
    </table>
</body>

</html>