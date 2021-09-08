<?php

namespace sammo;

include "lib.php";
include "func.php";

$type = Util::getPost('type', 'int', 0);
$type2 = Util::getPost('type2', 'int', 0);

if ($type < 0 || $type > 17) {
    $type = 0;
}
if ($type2 < 0 || $type2 > 6) {
    $type2 = 0;
}

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if ($session->userGrade < 5) {
    die(requireAdminPermissionHTML());
}

$db = DB::db();

$sel = [];
$sel2 = [];
$sel[$type] = "selected";
$sel2[$type2] = "selected";

?>
<!DOCTYPE html>
<html>

<head>
    <title>일제정보</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('css/common.css') ?>
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>일 제 정 보<br><?= closeButton() ?></td>
        </tr>
        <tr>
            <td>
                <form name=form1 method=post>정렬순서 :
                    <select name=type size=1>
                        <option <?= $sel[0] ?? '' ?> value=0>국력</option>
                        <option <?= $sel[1] ?? '' ?> value=1>장수</option>
                        <option <?= $sel[2] ?? '' ?> value=2>기술</option>
                        <option <?= $sel[3] ?? '' ?> value=3>국고</option>
                        <option <?= $sel[4] ?? '' ?> value=4>병량</option>
                        <option <?= $sel[5] ?? '' ?> value=5>평금</option>
                        <option <?= $sel[6] ?? '' ?> value=6>평쌀</option>
                        <option <?= $sel[7] ?? '' ?> value=7>평통</option>
                        <option <?= $sel[8] ?? '' ?> value=8>평무</option>
                        <option <?= $sel[9] ?? '' ?> value=9>평지</option>
                        <option <?= $sel[10] ?? '' ?> value=10>평Lv</option>
                        <option <?= $sel[11] ?? '' ?> value=11>접속률</option>
                        <option <?= $sel[12] ?? '' ?> value=12>단기접</option>
                        <option <?= $sel[13] ?? '' ?> value=13>보숙</option>
                        <option <?= $sel[14] ?? '' ?> value=14>궁숙</option>
                        <option <?= $sel[15] ?? '' ?> value=15>기숙</option>
                        <option <?= $sel[16] ?? '' ?> value=16>귀숙</option>
                        <option <?= $sel[17] ?? '' ?> value=17>차숙</option>
                    </select>
                    <select name=type2 size=1>
                        <option <?= $sel2[0] ?? '' ?> value=0>국력</option>
                        <option <?= $sel2[1] ?? '' ?> value=1>국가별성향</option>
                        <option <?= $sel2[2] ?? '' ?> value=2>국가성향</option>
                        <option <?= $sel2[3] ?? '' ?> value=3>장수성격</option>
                        <option <?= $sel2[4] ?? '' ?> value=4>장수특기</option>
                        <option <?= $sel2[5] ?? '' ?> value=5>병종수</option>
                        <option <?= $sel2[6] ?? '' ?> value=6>기타</option>
                    </select>
                    <input type=submit value='정렬하기'>
                </form>
                <form name=form2 method=post action=_admin5_submit.php>
                    <select name=nation size=1 style=color:white;background-color:black>";
                        <option value=0>재야</option>";
                        <?php
                        foreach ($db->query('SELECT nation,name,color,scout,gennum from nation order by power DESC') as $nation) {

                            echo "
            <option value={$nation['nation']}>{$nation['name']}</option>";
                        }
                        ?>
                    </select>
                    <input type=submit name=btn value='국가변경'>
                </form>
            </td>
        </tr>
    </table>

    <table align=center width=1600 class="tb_layout bg0">
        <tr class='bg1'>
            <td align=center>국명</td>
            <td align=center>접률</td>
            <td align=center>단접</td>
            <td align=center>국력</td>
            <td align=center>장수</td>
            <td align=center>속령</td>
            <td align=center>기술</td>
            <td align=center>전략</td>
            <td align=center>국고</td>
            <td align=center>병량</td>
            <td align=center>평금</td>
            <td align=center>평쌀</td>
            <td align=center>평통</td>
            <td align=center>평무</td>
            <td align=center>평지</td>
            <td align=center>평Lv</td>
            <td align=center>보숙</td>
            <td align=center>궁숙</td>
            <td align=center>기숙</td>
            <td align=center>귀숙</td>
            <td align=center>차숙</td>
            <td align=center>총병</td>
            <td align=center>인구</td>
            <td align=center>인구율</td>
            <td align=center>농업</td>
            <td align=center>상업</td>
            <td align=center>치안</td>
            <td align=center>성벽</td>
            <td align=center>수비</td>
            <td align=center>국명</td>
        </tr>
        <?php
        $query = "
SELECT
    A.nation,
    A.name,
    A.power,
    A.color,
    A.tech,
    A.strategic_cmd_limit,
    A.gold,
    A.rice,
    COUNT(B.nation) AS gennum,
    ROUND(AVG(B.connect), 1) AS connect,
    ROUND(AVG(B.con), 1) AS con,
    ROUND(AVG(B.dex1)) AS dex1,
    ROUND(AVG(B.dex2)) AS dex2,
    ROUND(AVG(B.dex3)) AS dex3,
    ROUND(AVG(B.dex4)) AS dex4,
    ROUND(AVG(B.dex5)) AS dex5
FROM nation A, general B
WHERE A.nation=B.nation
GROUP BY B.nation
";

        switch ($type) {
            case  0:
                $query .= " order by power desc";
                break;
            case  1:
                $query .= " order by gennum desc";
                break;
            case  2:
                $query .= " order by A.tech desc";
                break;
            case  3:
                $query .= " order by A.gold desc";
                break;
            case  4:
                $query .= " order by A.rice desc";
                break;
            case  5:
                $query .= " order by avg(B.gold) desc";
                break;
            case  6:
                $query .= " order by avg(B.rice) desc";
                break;
            case  7:
                $query .= " order by avg(B.leadership) desc";
                break;
            case  8:
                $query .= " order by avg(B.strength) desc";
                break;
            case  9:
                $query .= " order by avg(B.intel) desc";
                break;
            case 10:
                $query .= " order by avg(B.explevel) desc";
                break;
            case 11:
                $query .= " order by avg(B.connect) desc";
                break;
            case 12:
                $query .= " order by avg(B.con) desc";
                break;
            case 13:
                $query .= " order by avg(B.dex1) desc";
                break;
            case 14:
                $query .= " order by avg(B.dex2) desc";
                break;
            case 15:
                $query .= " order by avg(B.dex3) desc";
                break;
            case 16:
                $query .= " order by avg(B.dex4) desc";
                break;
            case 17:
                $query .= " order by avg(B.dex5) desc";
                break;
        }
        foreach($db->query($query) as $nation){
            $gen = $db->queryFirstRow('SELECT COUNT(*) as cnt,
            ROUND(AVG(gold)) as avgg,
            ROUND(AVG(rice)) as avgr,
            SUM(leadership) as leadership,  ROUND(AVG(leadership), 1) as avgl,
                                    ROUND(AVG(strength), 1) as avgs,
                                    ROUND(AVG(intel), 1) as avgi,
                                    ROUND(AVG(explevel), 1) as avge,
            SUM(crew) as crew
from general where nation=%i',$nation['nation']);

            $city = $db->queryFirstRow('SELECT COUNT(*) as cnt,
            SUM(pop) as pop,    SUM(pop_max) as pop_max,
            ROUND(SUM(pop)/SUM(pop_max)*100, 2) as rate,
            trust,
            ROUND(SUM(agri)/SUM(agri_max)*100, 2) as agri,
            ROUND(SUM(comm)/SUM(comm_max)*100, 2) as comm,
            ROUND(SUM(secu)/SUM(secu_max)*100, 2) as secu,
            ROUND(SUM(wall)/SUM(wall_max)*100, 2) as wall,
            ROUND(SUM(def)/SUM(def_max)*100, 2) as def
from city where nation=%i', $nation['nation']);

            echo "
    <tr>
        <td align=center style=background-color:{$nation['color']};color:" . newColor($nation['color']) . ";>{$nation['name']}</td>
        <td align=center>&nbsp;{$nation['connect']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['con']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['power']}&nbsp;</td>
        <td align=center>&nbsp;{$gen['cnt']}&nbsp;</td>
        <td align=center>&nbsp;{$city['cnt']}&nbsp;</td>
        <td align=right>&nbsp;" . sprintf('%.1f', $nation['tech']) . "&nbsp;</td>
        <td align=center>&nbsp;{$nation['strategic_cmd_limit']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['gold']}&nbsp;</td>
        <td align=center>&nbsp;{$nation['rice']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['avgg']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['avgr']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['avgl']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['avgs']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['avgi']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['avge']}&nbsp;</td>
        <td align=right>&nbsp;{$nation['dex1']}&nbsp;</td>
        <td align=right>&nbsp;{$nation['dex2']}&nbsp;</td>
        <td align=right>&nbsp;{$nation['dex3']}&nbsp;</td>
        <td align=right>&nbsp;{$nation['dex4']}&nbsp;</td>
        <td align=right>&nbsp;{$nation['dex5']}&nbsp;</td>
        <td align=right>&nbsp;{$gen['crew']}/{$gen['leadership']}00&nbsp;</td>
        <td align=center>&nbsp;{$city['pop']}/{$city['pop_max']}&nbsp;</td>
        <td align=center>&nbsp;" . sprintf('%.1f', $city['pop'] / Util::valueFit($city['pop_max'], 1) * 100) . "%&nbsp;</td>
        <td align=center>&nbsp;{$city['agri']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['comm']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['secu']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['wall']}%&nbsp;</td>
        <td align=center>&nbsp;{$city['def']}%&nbsp;</td>
        <td align=center style=background-color:{$nation['color']};color:" . newColor($nation['color']) . ";>{$nation['name']}</td>
    </tr>
";
        }

        ?>
    </table>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td><?= getSabotageLogRecent(20) ?></td>
        </tr>
    </table>

    <table align=center width=1760 class="tb_layout bg0">
        <tr class='bg1'>
            <td width=30 align=center>년</td>
            <td width=30 align=center>월</td>
            <td width=50 align=center>국가수</td>
            <td width=50 align=center>장수수</td>
            <?php
            switch ($type2) {
                default:
                case 0:
                    echo "<td width=1600>국력(국력,장수수,도시수,인구/100,최대인구/100,국가자원/100,장수자원/100,능력치,숙련/1000,경험공헌/100)</td>";
                    break;
                case 1:
                    echo "<td width=1600>국가별성향</td>";
                    break;
                case 2:
                    echo "<td width=1600>국가성향</td>";
                    break;
                case 3:
                    echo "<td width=1600>장수성격</td>";
                    break;
                case 4:
                    echo "<td width=1600>장수특기</td>";
                    break;
                case 5:
                    echo "<td width=1600>병종수</td>";
                    break;
                case 6:
                    echo "<td width=1600>기타</td>";
                    break;
            }
            ?>
        </tr>
        <?php
        foreach($db->query('SELECT * from statistic where month=1 or no=1') as $stat){
            echo "
    <tr>
        <td align=center>{$stat['year']}</td>
        <td align=center>{$stat['month']}</td>
        <td align=center>{$stat['nation_count']}</td>
        <td align=center>{$stat['gen_count']}</td>
";
            switch ($type2) {
                default:
                case 0:
                    echo "<td>{$stat['power_hist']}</td>";
                    break;
                case 1:
                    echo "<td>{$stat['nation_name']}</td>";
                    break;
                case 2:
                    echo "<td>{$stat['nation_hist']}</td>";
                    break;
                case 3:
                    echo "<td>{$stat['personal_hist']}</td>";
                    break;
                case 4:
                    echo "<td>{$stat['special_hist']}</td>";
                    break;
                case 5:
                    echo "<td>{$stat['crewtype']}</td>";
                    break;
                case 6:
                    echo "<td>{$stat['etc']}</td>";
                    break;
            }

            echo "
    </tr>
";
        }
        ?>
    </table>

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