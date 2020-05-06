<?php

namespace sammo;

use sammo\Event\Action;

require_once 'process_war.php';
require_once 'func_gamerule.php';
require_once 'func_process.php';
require_once 'func_tournament.php';
require_once 'func_auction.php';
require_once 'func_string.php';
require_once 'func_history.php';
require_once 'func_legacy.php';
require_once 'func_converter.php';
require_once 'func_time_event.php';
require_once('func_template.php');
require_once('func_message.php');
require_once('func_map.php');
require_once('func_command.php');

/**
 * nationID를 이용하여 국가의 '어지간해선' 변경되지 않는 정보(이름, 색, 성향, 규모, 수도)를 반환해줌
 * 
 * @param int|null $nationID 국가 코드, -1인 경우 전체, null인 경우 수행하지 않음. 0인 경우에는 재야임
 * @param bool $forceRefresh 강제 갱신 여부
 * 
 * @return array|null nationID에 해당하는 국가가 있을 경우 array 반환. 그외의 경우 null
 */
function getNationStaticInfo($nationID, $forceRefresh = false)
{
    static $nationList = null;

    if ($forceRefresh) {
        $nationList = null;
    }

    if ($nationID === null) {
        return null;
    }
    if ($nationID === -1 && $nationList !== null) {
        return $nationList;
    }

    if ($nationID === 0) {
        return [
            'nation' => 0,
            'name' => '재야',
            'color' => '#000000',
            'type' => GameConst::$neutralNationType,
            'level' => 0,
            'capital' => 0,
            'gold' => 0,
            'rice' => 2000,
            'tech' => 0,
            'gennum' => 1,
            'power' => 1
        ];
    }

    if ($nationList === null) {
        $nationAll = DB::db()->query("select nation, name, color, type, level, capital, gennum, power from nation");
        $nationList = Util::convertArrayToDict($nationAll, "nation");
    }

    if ($nationID === -1) {
        return $nationList;
    }

    if (isset($nationList[$nationID])) {
        return $nationList[$nationID];
    }
    return null;
}

/**
 * getNationStaticInfo() 함수의 국가 캐시를 초기화
 */
function refreshNationStaticInfo()
{
    getNationStaticInfo(null, true);
}

/**
 * getNationStaticInfo(-1) 의 단축형
 */
function getAllNationStaticInfo()
{
    return getNationStaticInfo(-1);
}

function GetImageURL($imgsvr, $filepath = '')
{
    if ($imgsvr == 0) {
        return ServConfig::getSharedIconPath($filepath);
    } else {
        return AppConf::getUserIconPathWeb($filepath);
    }
}

/**
 * @param null|int $con 장수의 벌점
 * @param null|int $conlimit 최대 벌점
 */
function checkLimit($con = null)
{
    $session = Session::getInstance();
    if ($session->userGrade >= 4) {
        return 0;
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    if ($con === null) {
        $con = $db->queryFirstField('SELECT con FROM general WHERE `owner`=%i', Session::getUserID());
    }
    $conlimit = $gameStor->conlimit;

    if ($con > $conlimit) {
        return 2;
        //접속제한 90%이면 경고문구
    } elseif ($con > $conlimit * 0.9) {
        return 1;
    } else {
        return 0;
    }
}

function getBlockLevel()
{
    return DB::db()->queryFirstField('select block from general where no = %i', Session::getInstance()->generalID);
}

function getRandGenName()
{
    $firstname = Util::choiceRandom(GameConst::$randGenFirstName);
    $middlename = Util::choiceRandom(GameConst::$randGenMiddleName);
    $lastname = Util::choiceRandom(GameConst::$randGenLastName);

    return "{$firstname}{$middlename}{$lastname}";
}



function cityInfo(General $generalObj)
{
    $db = DB::db();

    // 도시 정보
    $city = $generalObj->getRawCity();
    $cityID = $city['city'];

    $nation = getNationStaticInfo($city['nation']);

    if (!$nation) {
        $nation = getNationStaticInfo(0);
    }

    $city['nationName'] = $nation['name'];
    $city['nationTextColor'] = newColor($nation['color']);
    $city['nationColor'] = $nation['color'];
    $city['region'] = CityConst::$regionMap[$city['region']];
    $city['levelText'] = CityConst::$levelMap[$city['level']];

    $officerName = [
        2 => '-',
        3 => '-',
        4 => '-'
    ];

    foreach ($db->query('SELECT `officer_level`, `name`, npc, no FROM general WHERE officer_city = %i', $cityID) as $officer) {
        $officerName[$officer['officer_level']] = getColoredName($officer['name'], $officer['npc']);
    }

    $city['officerName'] = $officerName;

    $templates = new \League\Plates\Engine('templates');
    $templates->registerFunction('bar', '\sammo\bar');
    return $templates->render('mainCityInfo', $city);
}

function myNationInfo(General $generalObj)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['startyear', 'year']);

    $nationID = $generalObj->getNationID();
    $nation = $db->queryFirstRow('SELECT * FROM nation WHERE nation = %i', $nationID)??getNationStaticInfo(0);
    $city = $db->queryFirstRow(
        'SELECT COUNT(*) as cnt, SUM(pop) as totpop, SUM(pop_max) as maxpop from city where nation=%i',
        $nationID
    );
    $general = $db->queryFirstRow('SELECT COUNT(*) as cnt, SUM(crew) as totcrew,SUM(leadership)*100 as maxcrew from general where nation=%i', $nationID);

    $topChiefs = Util::convertArrayToDict($db->query('SELECT officer_level, no, name, npc FROM general WHERE nation = %i AND officer_level >= 11', $nationID), 'officer_level');

    $level12Name = key_exists(12, $topChiefs)?getColoredName($topChiefs[12]['name'], $topChiefs[12]['npc']):'-';
    $level11Name = key_exists(11, $topChiefs)?getColoredName($topChiefs[11]['name'], $topChiefs[11]['npc']):'-';

    echo "<table width=498 class='tb_layout bg2 nation_info'>
    <tr>
        <td colspan=4 ";

    if (!$nationID) {
        echo "style='color:white;background-color:000000;font-weight:bold;font-size:13px;text-align:center;'>【재 야】";
    } else {
        echo "style='color:" . newColor($nation['color']) . ";background-color:{$nation['color']};font-weight:bold;font-size:13px;text-align:center'>국가【 {$nation['name']} 】";
    }

    echo "
        </td>
    </tr>
    <tr>
        <td class='bg1 center'><b>성 향</b></td>
        <td colspan=3 class='center'><font color=\"yellow\">" . getNationType($nation['type']) . "</font> (" . getNationType2($nation['type']) . ")</td>
        </td>
    </tr>
    <tr>
        <td width=68 class='bg1 center'><b>" . getOfficerLevelText(12, $nation['level']) . "</b></td>
        <td width=178 class='center'>{$level12Name}</td>
        <td width=68 class='bg1 center'><b>" . getOfficerLevelText(11, $nation['level']) . "</b></td>
        <td width=178 class='center'>{$level11Name}</td>
    </tr>
    <tr>
        <td class='bg1 center'><b>총주민</b></td>
        <td class='center'>";
    echo $nationID===0 ? "해당 없음" : "{$city['totpop']}/{$city['maxpop']}";
    echo "</td>
        <td class='bg1 center'><b>총병사</b></td>
        <td class='center'>";
    echo $nationID===0 ? "해당 없음" : "{$general['totcrew']}/{$general['maxcrew']}";
    echo "</td>
        </td>
    </tr>
    <tr>
        <td class='bg1 center'><b>국 고</b></td>
        <td class='center'>";
    echo $nationID===0 ? "해당 없음" : "{$nation['gold']}";
    echo "</td>
        <td class='bg1 center'><b>병 량</b></td>
        <td class='center'>";
    echo $nationID===0 ? "해당 없음" : "{$nation['rice']}";
    echo "</td>
    </tr>
    <tr>
        <td class='bg1 center'><b>지급률</b></td>
        <td class='center'>";
    if ($nationID===0) {
        echo "해당 없음";
    } else {
        echo $nation['bill'] == 0 ? "0 %" : "{$nation['bill']} %";
    }
    echo "
        </td>
        <td class='bg1 center'><b>세 율</b></td>
        <td class='center'>";
    if ($nationID===0) {
        echo "해당 없음";
    } else {
        echo $nation['rate'] == 0 ? "0 %" : "{$nation['rate']} %";
    }

    $techCall = getTechCall($nation['tech']);

    if (TechLimit($admin['startyear'], $admin['year'], $nation['tech'])) {
        $nation['tech'] = "<font color=magenta>" . floor($nation['tech']) . "</font>";
    } else {
        $nation['tech'] = "<font color=limegreen>" . floor($nation['tech']) . "</font>";
    }

    $nation['tech'] = "$techCall / {$nation['tech']}";

    if ($nationID===0) {
        $nation['strategic_cmd_limit'] = "<font color=white>해당 없음</font>";
        $nation['surlimit'] = "<font color=white>해당 없음</font>";
        $nation['scout'] = "<font color=white>해당 없음</font>";
        $nation['war'] = "<font color=white>해당 없음</font>";
        $nation['power'] = "<font color=white>해당 없음</font>";
    } else {
        if ($nation['strategic_cmd_limit'] != 0) {
            $nation['strategic_cmd_limit'] = "<font color=red>{$nation['strategic_cmd_limit']}턴</font>";
        } else {
            $nation['strategic_cmd_limit'] = "<font color=limegreen>가 능</font>";
        }

        if ($nation['surlimit'] != 0) {
            $nation['surlimit'] = "<font color=red>{$nation['surlimit']}턴</font>";
        } else {
            $nation['surlimit'] = "<font color=limegreen>가 능</font>";
        }

        if ($nation['scout'] != 0) {
            $nation['scout'] = "<font color=red>금 지</font>";
        } else {
            $nation['scout'] = "<font color=limegreen>허 가</font>";
        }

        if ($nation['war'] != 0) {
            $nation['war'] = "<font color=red>금 지</font>";
        } else {
            $nation['war'] = "<font color=limegreen>허 가</font>";
        }
    }

    echo "
        </td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>속 령</b></td>
        <td style='text-align:center;'>";
    echo $nationID===0 ? "-" : "{$city['cnt']}";
    echo "</td>
        <td style='text-align:center;' class='bg1'><b>장 수</b></td>
        <td style='text-align:center;'>";
    echo $nationID===0 ? "-" : "{$general['cnt']}";
    echo "</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>국 력</b></td>
        <td style='text-align:center;'>{$nation['power']}</td>
        <td style='text-align:center;' class='bg1'><b>기술력</b></td>
        <td style='text-align:center;'>";
    echo $nationID===0 ? "-" : "{$nation['tech']}";
    echo "</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>전 략</b></td>
        <td style='text-align:center;'>{$nation['strategic_cmd_limit']}</td>
        <td style='text-align:center;' class='bg1'><b>외 교</b></td>
        <td style='text-align:center;'>{$nation['surlimit']}</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>임 관</b></td>
        <td style='text-align:center;'>{$nation['scout']}</td>
        <td style='text-align:center;' class='bg1'><b>전 쟁</b></td>
        <td style='text-align:center;'>{$nation['war']}</td>
    </tr>
</table>
";
}

function checkSecretMaxPermission($penalty)
{
    $secretMax = 4;
    if ($penalty['noTopSecret'] ?? false) {
        $secretMax = 1;
    } else if ($penalty['noChief'] ?? false) {
        $secretMax = 1;
    } else if ($penalty['noAmbassador'] ?? false) {
        $secretMax = 2;
    }
    return $secretMax;
}

function checkSecretPermission(array $me, $checkSecretLimit = true)
{
    if (!key_exists('penalty', $me) || !key_exists('permission', $me)) {
        trigger_error('canAccessSecret() 함수에 필요한 인자가 부족');
    }
    $penalty = Json::decode($me['penalty']) ?? [];
    $permission = $me['permission'];

    if (!$me['nation']) {
        return -1;
    }

    if ($me['officer_level'] == 0) {
        return -1;
    }


    if ($penalty['noSecret'] ?? false) {
        return 0;
    }

    $secretMin = 0;
    $secretMax = checkSecretMaxPermission($me, $penalty);


    if ($me['officer_level'] == 12) {
        $secretMin = 4;
    } else if ($me['permission'] == 'ambassador') {
        $secretMin = 4;
    } else if ($me['permission'] == 'auditor') {
        $secretMin = 3;
    } else if ($me['officer_level'] >= 5) {
        $secretMin = 2;
    } else if ($me['officer_level'] > 1) {
        $secretMin = 1;
    } else if ($checkSecretLimit) {
        $db = DB::db();
        $secretLimit = $db->queryFirstField('SELECT secretlimit FROM nation WHERE nation = %i', $me['nation']);
        if ($me['belong'] >= $secretLimit) {
            $secretMin = 1;
        }
    }

    return min($secretMin, $secretMax);
}

function addCommand($typename, $value, $valid = 1, $color = 0)
{
    if ($valid == 1) {
        switch ($color) {
            case 0:
                echo "
    <option style='color:white;background-color:black;' value='{$value}'>{$typename}</option>";
                break;
            case 1:
                echo "
    <option style='color:skyblue;background-color:black;' value='{$value}'>{$typename}</option>";
                break;
            case 2:
                echo "
    <option style='color:orange;background-color:black;' value='{$value}'>{$typename}</option>";
                break;
        }
    } else {
        echo "
    <option style='color:white;background-color:red;' value='{$value}'>{$typename}(불가)</option>";
    }
}

function commandGroup($typename, $type = 0)
{
    if ($type == 0) {
        echo "
    <optgroup label='{$typename}' style='color:skyblue;background-color:black;'>";
    } else {
        echo "
    </optgroup>";
    }
}

function printCommandTable(General $generalObj)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $gameStor->turnOnCache();
    $env = $gameStor->getAll();

?>
    <select id='generalCommandList' name='commandtype' size=1 style='height:20px;width:260px;color:white;background-color:black;font-size:12px;'>";
        <?php

        //보정(Pros,Cons) 여부.
        $getCompensateClassName = function ($value) {
            if ($value > 0) {
                return 'compensatePositive';
            } else if ($value < 0) {
                return 'compensateNegative';
            }
            return 'compensateNeutral';
        };

        foreach (GameConst::$availableGeneralCommand as $commandCategory => $commandList) {
            if ($commandCategory) {
                commandGroup("======= {$commandCategory} =======");
            }

            foreach ($commandList as $commandClassName) {
                $commandObj = buildGeneralCommandClass($commandClassName, $generalObj, $env);
                if (!$commandObj->canDisplay()) {
                    continue;
                }
        ?>
                <option class='commandBasic <?= $getCompensateClassName($commandObj->getCompensationStyle()) ?> <?= $commandObj->hasMinConditionMet() ? '' : 'commandImpossible' ?>' value='<?= Util::getClassNameFromObj($commandObj) ?>' data-reqArg='<?= ($commandObj::$reqArg) ? 'true' : 'false' ?>'><?= $commandObj->getCommandDetailTitle() ?><?= $commandObj->hasMinConditionMet() ? '' : '(불가)' ?></option>
        <?php
            }

            if ($commandCategory) {
                commandGroup('', 1);
            }
        }

        ?>
    </select>
<?php
}

function chiefCommandTable(General $generalObj)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $userID = Session::getUserID();

    $gameStor->turnOnCache();
    $env = $gameStor->getAll();

?>
    <select id='chiefCommandList' name='commandtype' size=1 style='height:20px;color:white;background-color:black;font-size:13px;display:inline-block;'>";
        <?php

        //보정(Pros,Cons) 여부.
        $getCompensateClassName = function ($value) {
            if ($value > 0) {
                return 'compensatePositive';
            } else if ($value < 0) {
                return 'compensateNegative';
            }
            return 'compensateNeutral';
        };

        foreach (GameConst::$availableChiefCommand as $commandCategory => $commandList) {
            if ($commandCategory) {
                commandGroup("======= {$commandCategory} =======");
            }

            foreach ($commandList as $commandClassName) {
                $commandObj = buildNationCommandClass($commandClassName, $generalObj, $env, new LastTurn());
                if (!$commandObj->canDisplay()) {
                    continue;
                }
        ?>
                <option class='commandBasic <?= $getCompensateClassName($commandObj->getCompensationStyle()) ?> <?= $commandObj->hasMinConditionMet() ? '' : 'commandImpossible' ?>' value='<?= Util::getClassNameFromObj($commandObj) ?>' data-reqArg='<?= ($commandObj::$reqArg) ? 'true' : 'false' ?>'><?= $commandObj->getCommandDetailTitle() ?><?= $commandObj->hasMinConditionMet() ? '' : '(불가)' ?></option>
        <?php
            }

            if ($commandCategory) {
                commandGroup('', 1);
            }
        }

        ?>
    </select>
<?php
}

function generalInfo(General $generalObj)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $show_img_level = $gameStor->show_img_level;


    $nation = getNationStaticInfo($generalObj->getNationID());

    $lbonus = calcLeadershipBonus($generalObj->getVar('officer_level'), $nation['level']);
    if ($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    if ($generalObj->getVar('troop') == 0) {
        $troopInfo = '-';
    } else {
        $troopCity = $db->queryFirstField('SELECT city FROM general WHERE no=%i', $generalObj->getVar('troop'));
        $troopTurn = $db->queryFirstField('SELECT `brief` FROM general_turn WHERE general_id = %i AND turn_idx = 0', $generalObj->getVar('troop'));
        $troopInfo = $db->queryFirstField('SELECT name FROM troop WHERE troop_leader = %i', $generalObj->getVar('troop'));

        if ($troopTurn !== '집합') {
            $troopInfo = "<strike style='color:gray;'>{$troopInfo}</strike>";
        } else if ($troopCity != $generalObj->getCityID()) {
            $troopCityName = CityConst::byID($troopCity)->name;
            $troopInfo = "<span style='color:orange;'>{$troopInfo}({$troopCityName})</span>";
        }
    }

    $officerLevel = $generalObj->getVar('officer_level');
    $officerLevelText = getOfficerLevelText($officerLevel, $nation['level']);

    if (2 <= $officerLevel && $officerLevel <= 4) {
        $cityOfficerName = CityConst::byID($generalObj->getVar('officer_city'))->name;
        $officerLevelText = "{$cityOfficerName} {$officerLevelText}";
    }

    $call = getCall(...$generalObj->getVars('leadership', 'strength', 'intel'));
    $crewTypeInfo = displayiActionObjInfo($generalObj->getCrewTypeObj());
    $weaponInfo = displayiActionObjInfo($generalObj->getItem('weapon'));
    $bookInfo = displayiActionObjInfo($generalObj->getItem('book'));
    $horseInfo = displayiActionObjInfo($generalObj->getItem('horse'));
    $itemInfo = displayiActionObjInfo($generalObj->getItem('item'));

    $leadership = $generalObj->getLeadership(true, false, false);
    $strength = $generalObj->getStrength(true, false, false);
    $intel = $generalObj->getIntel(true, false, false);


    $injury = $generalObj->getVar('injury');
    if ($injury > 60) {
        $color = "<font color=red>";
        $injury = "위독";
    } elseif ($injury > 40) {
        $color = "<font color=magenta>";
        $injury = "심각";
    } elseif ($injury > 20) {
        $color = "<font color=orange>";
        $injury = "중상";
    } elseif ($injury > 0) {
        $color = "<font color=yellow>";
        $injury = "경상";
    } else {
        $color = "<font color=white>";
        $injury = "건강";
    }

    $remaining = (new \DateTimeImmutable($generalObj->getTurnTime()))->diff(new \DateTimeImmutable())->i;

    if ($nation['color'] == "") {
        $nation['color'] = "#000000";
    }

    $age = $generalObj->getVar('age');
    if ($age < GameConst::$retirementYear * 0.75) {
        $age = "<font color=limegreen>{$age} 세</font>";
    } elseif ($age < GameConst::$retirementYear) {
        $age = "<font color=yellow>{$age} 세</font>";
    } else {
        $age = "<font color=red>{$age} 세</font>";
    }

    $connectCnt = round($generalObj->getVar('connect'), -1);
    $specialDomestic = $generalObj->getVar('special') === GameConst::$defaultSpecialDomestic
        ? "{$generalObj->getVar('specage')}세"
        : "<font color=limegreen>" . displayiActionObjInfo($generalObj->getSpecialDomestic()) . "</font>";
    $specialWar = $generalObj->getVar('special2') === GameConst::$defaultSpecialDomestic
        ? "{$generalObj->getVar('specage2')}세"
        : "<font color=limegreen>" . displayiActionObjInfo($generalObj->getSpecialWar()) . "</font>";

    $atmos = $generalObj->getVar('atmos');
    $atmosBonus = $generalObj->onCalcStat($generalObj, 'bonusAtmos', $atmos) - $atmos;
    if ($atmosBonus > 0) {
        $atmos = "<font color=cyan>{$atmos} (+{$atmosBonus})</font>";
    } else if ($atmosBonus < 0) {
        $atmos = "<font color=magenta>{$atmos} ({$atmosBonus})</font>";
    } else {
        $atmos = "$atmos";
    }

    $train = $generalObj->getVar('train');
    $trainBonus = $generalObj->onCalcStat($generalObj, 'bonusTrain', $train) - $train;
    if ($trainBonus > 0) {
        $train = "<font color=cyan>{$train} (+{$trainBonus})</font>";
    } else if ($trainBonus < 0) {
        $train = "<font color=magenta>{$train} ({$trainBonus})</font>";
    } else {
        $train = "$train";
    }

    if ($generalObj->getVar('defence_train') === 999) {
        $defenceTrain = "<font color=red>수비 안함</font>";
    } else {
        $defenceTrain = "<font color=limegreen>수비 함(훈사{$generalObj->getVar('defence_train')})</font>";
    }

    $crewType = $generalObj->getCrewTypeObj();

    $weapImage = ServConfig::$gameImagePath . "/crewtype{$crewType->id}.png";
    if ($show_img_level < 2) {
        $weapImage = ServConfig::$sharedIconPath . "/default.jpg";
    };
    $imagePath = GetImageURL(...$generalObj->getVars('imgsvr', 'picture'));
    echo "<table width=498 class='tb_layout bg2'>
    <tr>
        <td width=64 height=64 rowspan=3 class='generalIcon' style='text-align:center;background:no-repeat center url(\"{$imagePath}\");background-size:64px;'>&nbsp;</td>
        <td colspan=9 height=16 style=text-align:center;color:" . newColor($nation['color']) . ";background-color:{$nation['color']};font-weight:bold;font-size:13px;>{$generalObj->getName()} 【 {$officerLevelText} | {$call} | {$color}{$injury}</font> 】 " . $generalObj->getTurnTime($generalObj::TURNTIME_HMS) . "</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>통솔</b></td>
        <td style='text-align:center;'>&nbsp;{$color}{$leadership}</font>{$lbonus}&nbsp;</td>
        <td style='text-align:center;' width=45>" . bar(expStatus($generalObj->getVar('leadership_exp')), 20) . "</td>
        <td style='text-align:center;' class='bg1'><b>무력</b></td>
        <td style='text-align:center;'>&nbsp;{$color}{$strength}</font>&nbsp;</td>
        <td style='text-align:center;' width=45>" . bar(expStatus($generalObj->getVar('strength_exp')), 20) . "</td>
        <td style='text-align:center;' class='bg1'><b>지력</b></td>
        <td style='text-align:center;'>&nbsp;{$color}{$intel}</font>&nbsp;</td>
        <td style='text-align:center;' width=45>" . bar(expStatus($generalObj->getVar('intel_exp')), 20) . "</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>명마</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$horseInfo</font></td>
        <td style='text-align:center;' class='bg1'><b>무기</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$weaponInfo</font></td>
        <td style='text-align:center;' class='bg1'><b>서적</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$bookInfo</font></td>
    </tr>
    <tr>
        <td height=64 rowspan=3 style='text-align:center;background:no-repeat center url(\"{$weapImage}\");background-size:64px;'></td>
        <td style='text-align:center;' class='bg1'><b>자금</b></td>
        <td style='text-align:center;' colspan=2>{$generalObj->getVar('gold')}</td>
        <td style='text-align:center;' class='bg1'><b>군량</b></td>
        <td style='text-align:center;' colspan=2>{$generalObj->getVar('rice')}</td>
        <td style='text-align:center;' class='bg1'><b>도구</b></td>
        <td style='text-align:center;' colspan=2><font size=1>$itemInfo</font></td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>병종</b></td>
        <td style='text-align:center;' colspan=2>{$crewTypeInfo}</td>
        <td style='text-align:center;' class='bg1'><b>병사</b></td>
        <td style='text-align:center;' colspan=2>{$generalObj->getVar('crew')}</td>
        <td style='text-align:center;' class='bg1'><b>성격</b></td>
        <td style='text-align:center;' colspan=2>" . displayiActionObjInfo($generalObj->getPersonality()) . "</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>훈련</b></td>
        <td style='text-align:center;' colspan=2>$train</td>
        <td style='text-align:center;' class='bg1'><b>사기</b></td>
        <td style='text-align:center;' colspan=2>$atmos</td>
        <td style='text-align:center;' class='bg1'><b>특기</b></td>
        <td style='text-align:center;' colspan=2>$specialDomestic / $specialWar</td>
    </tr>
    <tr height=20>
        <td style='text-align:center;' class='bg1'><b>Lv</b></td>
        <td style='text-align:center;'>&nbsp;{$generalObj->getVar('explevel')}&nbsp;</td>
        <td style='text-align:center;' colspan=5>" . bar(getLevelPer(...$generalObj->getVars('experience', 'explevel')), 20) . "</td>
        <td style='text-align:center;' class='bg1'><b>연령</b></td>
        <td style='text-align:center;' colspan=2>{$age}</td>
    </tr>
    <tr height=20>
        <td style='text-align:center;' class='bg1'><b>수비</b></td>
        <td style='text-align:center;' colspan=3>{$defenceTrain}</td>
        <td style='text-align:center;' class='bg1'><b>삭턴</b></td>
        <td style='text-align:center;' colspan=2>{$generalObj->getVar('killturn')} 턴</td>
        <td style='text-align:center;' class='bg1'><b>실행</b></td>
        <td style='text-align:center;' colspan=2>$remaining 분 남음</td>
    </tr>
    <tr height=20>
        <td style='text-align:center;' class='bg1'><b>부대</b></td>
        <td style='text-align:center;' colspan=3>{$troopInfo}</td>
        <td style='text-align:center;' class='bg1'><b>벌점</b></td>
        <td style='text-align:center;' colspan=5>" . getConnect($connectCnt) . " {$connectCnt}({$generalObj->getVar('con')})</td>
    </tr>
</table>";
}

function generalInfo2(General $generalObj)
{
    $general = $generalObj->getRaw();

    $winRate = round($generalObj->getRankVar('killnum') / max($generalObj->getRankVar('warnum'), 1) * 100, 2);
    $killRate = round($generalObj->getRankVar('killcrew') / max($generalObj->getRankVar('deathcrew'), 1) * 100, 2);

    $experienceBonus = $generalObj->onCalcStat($generalObj, 'experience', 10000) - 10000;
    if ($experienceBonus > 0) {
        $experience = "<font color=cyan>" . getHonor($general['experience']) . " ({$general['experience']})</font>";
    } else if ($experienceBonus < 0) {
        $experience = "<font color=magenta>" . getHonor($general['experience']) . " ({$general['experience']})</font>";
    } else {
        $experience = getHonor($general['experience']) . " ({$general['experience']})";
    }

    $dedicationBonus = $generalObj->onCalcStat($generalObj, 'dedication', 10000) - 10000;
    if ($dedicationBonus > 0) {
        $dedication = "<font color=cyan>" . getHonor($general['dedication']) . " ({$general['dedication']})</font>";
    } else if ($dedicationBonus < 0) {
        $dedication = "<font color=magenta>" . getHonor($general['dedication']) . " ({$general['dedication']})</font>";
    } else {
        $dedication = getHonor($general['dedication']) . " ({$general['dedication']})";
    }

    $dex1  = $general['dex1']  / GameConst::$dexLimit * 100;
    $dex2 = $general['dex2'] / GameConst::$dexLimit * 100;
    $dex3 = $general['dex3'] / GameConst::$dexLimit * 100;
    $dex4 = $general['dex4'] / GameConst::$dexLimit * 100;
    $dex5 = $general['dex5'] / GameConst::$dexLimit * 100;

    if ($dex1 > 100) {
        $dex1 = 100;
    }
    if ($dex2 > 100) {
        $dex2 = 100;
    }
    if ($dex3 > 100) {
        $dex3 = 100;
    }
    if ($dex4 > 100) {
        $dex4 = 100;
    }
    if ($dex5 > 100) {
        $dex5 = 100;
    }

    $general['dex1_text']  = getDexCall($general['dex1']);
    $general['dex2_text'] = getDexCall($general['dex2']);
    $general['dex3_text'] = getDexCall($general['dex3']);
    $general['dex4_text'] = getDexCall($general['dex4']);
    $general['dex5_text'] = getDexCall($general['dex5']);

    $general['dex1_short'] = sprintf('%.1fK', $general['dex1'] / 1000);
    $general['dex2_short'] = sprintf('%.1fK', $general['dex2'] / 1000);
    $general['dex3_short'] = sprintf('%.1fK', $general['dex3'] / 1000);
    $general['dex4_short'] = sprintf('%.1fK', $general['dex4'] / 1000);
    $general['dex5_short'] = sprintf('%.1fK', $general['dex5'] / 1000);

    echo "<table width=498 class='tb_layout bg2'>
    <tr><td style='text-align:center;' colspan=6 class='bg1'><b>추 가 정 보</b></td></tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>명성</b></td>
        <td style='text-align:center;'>$experience</td>
        <td style='text-align:center;' class='bg1'><b>계급</b></td>
        <td style='text-align:center;' colspan=3>$dedication</td>
    </tr>
    <tr>
        <td width=64 style='text-align:center;' class='bg1'><b>전투</b></td>
        <td width=132 style='text-align:center;'>{$generalObj->getRankVar('warnum')}</td>
        <td width=48 style='text-align:center;' class='bg1'><b>계략</b></td>
        <td width=98 style='text-align:center;'>{$generalObj->getRankVar('firenum')}</td>
        <td width=48 style='text-align:center;' class='bg1'><b>사관</b></td>
        <td width=98 style='text-align:center;'>{$general['belong']}년</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>승률</b></td>
        <td style='text-align:center;'>{$winRate} %</td>
        <td style='text-align:center;' class='bg1'><b>승리</b></td>
        <td style='text-align:center;'>{$generalObj->getRankVar('killnum')}</td>
        <td style='text-align:center;' class='bg1'><b>패배</b></td>
        <td style='text-align:center;'>{$generalObj->getRankVar('deathnum')}</td>
    </tr>
    <tr>
        <td style='text-align:center;' class='bg1'><b>살상률</b></td>
        <td style='text-align:center;'>{$killRate} %</td>
        <td style='text-align:center;' class='bg1'><b>사살</b></td>
        <td style='text-align:center;'>{$generalObj->getRankVar('killcrew')}</td>
        <td style='text-align:center;' class='bg1'><b>피살</b></td>
        <td style='text-align:center;'>{$generalObj->getRankVar('deathcrew')}</td>
    </tr>
</table>
<table width=498 class='tb_layout bg2'>
    <tr><td style='text-align:center;' colspan=4 class='bg1'><b>숙 련 도</b></td></tr>
    <tr height=16>
        <td width=64 style='text-align:center;' class='bg1'><b>보병</b></td>
        <td width=40>　{$general['dex1_text']}</td>
        <td width=60 align=right>{$general['dex1_short']}&nbsp;</td>
        <td width=330 style='text-align:center;'>" . bar($dex1, 16) . "</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>궁병</b></td>
        <td>　{$general['dex2_text']}</td>
        <td align=right>{$general['dex2_short']}&nbsp;</td>
        <td style='text-align:center;'>" . bar($dex2, 16) . "</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>기병</b></td>
        <td>　{$general['dex3_text']}</td>
        <td align=right>{$general['dex3_short']}&nbsp;</td>
        <td style='text-align:center;'>" . bar($dex3, 16) . "</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>귀병</b></td>
        <td>　{$general['dex4_text']}</td>
        <td align=right>{$general['dex4_short']}&nbsp;</td>
        <td style='text-align:center;'>" . bar($dex4, 16) . "</td>
    </tr>
    <tr height=16>
        <td style='text-align:center;' class='bg1'><b>차병</b></td>
        <td>　{$general['dex5_text']}</td>
        <td align=right>{$general['dex5_short']}&nbsp;</td>
        <td style='text-align:center;'>" . bar($dex5, 16) . "</td>
    </tr>
</table>";
}

function getOnlineNum()
{
    return KVStorage::getStorage(DB::db(), 'game_env')->online;
}

function onlinegen(General $general)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $nationID = $general->getNationID();
    if ($nationID === null || Util::toInt($nationID) === 0) {
        $onlinegen = $gameStor->onlinegen;
    } else {
        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $onlinegen = $nationStor->onlinegen;
    }
    return $onlinegen;
}

function nationMsg(General $general)
{
    $db = DB::db();
    $nationID = $general->getNationID();
    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    
    return $nationStor->notice??'';
}

function banner()
{

    return sprintf(
        '<font size=2>%s %s / %s</font>',
        GameConst::$title,
        VersionGit::$version,
        GameConst::$banner
    );
}

function addTurn($date, int $turnterm, int $turn = 1, bool $withFraction = true)
{
    $date = new \DateTime($date);
    $target = $turnterm * $turn;
    $date->add(new \DateInterval("PT{$target}M"));
    if ($withFraction) {
        return $date->format('Y-m-d H:i:s.u');
    }
    return $date->format('Y-m-d H:i:s');
}

function subTurn($date, int $turnterm, int $turn = 1, bool $withFraction = true)
{
    $date = new \DateTime($date);
    $target = $turnterm * $turn;
    $date->sub(new \DateInterval("PT{$target}M"));
    if ($withFraction) {
        return $date->format('Y-m-d H:i:s.u');
    }
    return $date->format('Y-m-d H:i:s');
}

function cutTurn($date, int $turnterm, bool $withFraction = true)
{
    $date = new \DateTime($date);

    $baseDate = new \DateTime($date->format('Y-m-d'));
    $baseDate->sub(new \DateInterval("P1D"));
    $baseDate->add(new \DateInterval("PT1H"));

    $diffMin = intdiv($date->getTimeStamp() - $baseDate->getTimeStamp(), 60);
    $diffMin -= $diffMin % $turnterm;

    $baseDate->add(new \DateInterval("PT{$diffMin}M"));
    if ($withFraction) {
        return $baseDate->format('Y-m-d H:i:s.u');
    }
    return $baseDate->format('Y-m-d H:i:s');
}

function cutDay($date, int $turnterm, bool $withFraction = true)
{
    $date = new \DateTime($date);

    $baseDate = new \DateTime($date->format('Y-m-d'));
    $baseDate->sub(new \DateInterval("P1D"));
    $baseDate->add(new \DateInterval("PT1H"));

    $baseGap = 12 * $turnterm;

    $diffMin = intdiv($date->getTimeStamp() - $baseDate->getTimeStamp(), 60);

    $timeAdjust = $diffMin % $baseGap;
    $newMonth = intdiv($timeAdjust, $turnterm) + 1;

    $yearPulled = false;
    if ($newMonth > 3) { //3월 이후일때는
        $yearPulled = true;
        $diffMin += $baseGap;
    }
    $diffMin -= $timeAdjust;

    $baseDate->add(new \DateInterval("PT{$diffMin}M"));
    if ($withFraction) {
        $dateTimeString = $baseDate->format('Y-m-d H:i:s.u');
    } else {
        $dateTimeString = $baseDate->format('Y-m-d H:i:s');
    }

    return [$dateTimeString, $yearPulled, $newMonth];
}

function increaseRefresh($type = "", $cnt = 1)
{
    //FIXME: 로그인, 비로그인 시 처리가 명확하지 않음
    $session = Session::getInstance();
    $userID = $session->userID;
    $generalID = $session->generalID;
    $userGrade = $session->userGrade;

    $date = TimeUtil::now();

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->refresh = $gameStor->refresh + $cnt; //TODO: +로 증가하는 값은 별도로 분리
    $isunited = $gameStor->isunited;

    if ($isunited != 2 && $generalID && $userGrade < 6) {
        $db->update('general', [
            'lastrefresh' => $date,
            'con' => $db->sqleval('con + %i', $cnt),
            'connect' => $db->sqleval('connect + %i', $cnt),
            'refcnt' => $db->sqleval('refcnt + %i', $cnt),
            'refresh' => $db->sqleval('refresh + %i', $cnt)
        ], 'owner=%i', $userID);
    }

    $date = date('Y_m_d H:i:s');
    $date2 = substr($date, 0, 10);
    $online = getOnlineNum();
    file_put_contents(
        __DIR__ . "/logs/" . UniqueConst::$serverID . "/_{$date2}_refresh.txt",
        sprintf(
            "%s, %s, %s, %s, %s, %d\n",
            $date,
            $session->userName,
            $session->generalName,
            $session->ip,
            $type,
            $online
        ),
        FILE_APPEND
    );

    $proxy_headers = array(
        'HTTP_VIA',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR_IP',
        'VIA',
        'X_FORWARDED_FOR',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION'
    );

    $str = "";
    foreach ($proxy_headers as $x) {
        if (isset($_SERVER[$x])) $str .= "//{$x}:{$_SERVER[$x]}";
    }
    if ($str != "") {
        file_put_contents(
            __DIR__ . "/logs/" . UniqueConst::$serverID . "/_{$date2}_ipcheck.txt",
            sprintf(
                "%s, %s, %s%s\n",
                $session->userName,
                $session->generalName,
                $_SERVER['REMOTE_ADDR'],
                $str
            ),
            FILE_APPEND
        );
    }
}

function updateTraffic()
{
    $online = getOnlineNum();
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $admin = $gameStor->getValues(['year', 'month', 'refresh', 'maxonline', 'maxrefresh']);

    //최다갱신자
    $user = $db->queryFirstRow('select name,refresh from general order by refresh desc limit 1');

    if ($admin['maxrefresh'] < $admin['refresh']) {
        $admin['maxrefresh'] = $admin['refresh'];
    }
    if ($admin['maxonline'] < $online) {
        $admin['maxonline'] = $online;
    }
    $gameStor->refresh = 0;
    $gameStor->maxrefresh = $admin['maxrefresh'];
    $gameStor->maxonline = $admin['maxonline'];

    $db->update('general', ['refresh' => 0], true);

    $date = TimeUtil::now();
    //일시|년|월|총갱신|접속자|최다갱신자
    file_put_contents(
        __DIR__ . "/logs/" . UniqueConst::$serverID . "/_traffic.txt",
        Json::encode([
            $date,
            $admin['year'],
            $admin['month'],
            $admin['refresh'],
            $online,
            $user['name'] . "(" . $user['refresh'] . ")"
        ]) . "\n",
        FILE_APPEND
    );
}

function CheckOverhead()
{
    //서버정보
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    list($turnterm, $conlimit) = $gameStor->getValuesAsArray(['turnterm', 'conlimit']);

    $con = Util::round(pow($turnterm, 0.6) * 3) * 10;


    if ($con != $conlimit) {
        $gameStor->conlimit = $con;
    }
}

function isLock()
{
    return DB::db()->queryFirstField("SELECT plock from plock limit 1") != 0;
}

function tryLock(): bool
{
    //NOTE: 게임 로직과 관련한 모든 insert, update 함수들은 lock을 거칠것을 권장함.
    $db = DB::db();

    // 잠금
    $db->update('plock', [
        'plock' => 1,
        'locktime' => TimeUtil::now(true)
    ], 'plock=0');

    return $db->affectedRows() > 0;
}

function unlock(): bool
{
    // 풀림
    $db = DB::db();
    $db->update('plock', [
        'plock' => 0
    ], true);

    return $db->affectedRows() > 0;
}

function timeover(): bool
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    list($turnterm, $turntime) = $gameStor->getValuesAsArray(['turnterm', 'turntime']);
    $diff = (new \DateTime())->getTimestamp() - (new \DateTime($turntime))->getTimestamp();

    $t = min($turnterm, 5);

    $term = $diff;
    if ($term >= $t || $term < 0) {
        return true;
    } else {
        return false;
    }
}

function checkDelay()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    //서버정보
    $now = new \DateTimeImmutable();
    $turntime = new \DateTimeImmutable($gameStor->turntime);
    $timeMinDiff = intdiv($now->getTimestamp() - $turntime->getTimestamp(), 60);

    // 1턴이상 갱신 없었으면 서버 지연
    $term = $gameStor->turnterm;
    if ($term >= 20) {
        $threshold = 1;
    } else if ($term >= 10) {
        $threshold = 3;
    } else {
        $threshold = 6;
    }
    //지연 해야할 밀린 턴 횟수
    $iter = intdiv($timeMinDiff, $term);
    if ($iter > $threshold) {
        $minute = $iter * $term;
        $newTurntime = $turntime->add(new \DateInterval("PT{$minute}M"));
        $newNextTurntime = $turntime->add(new \DateInterval("PT{$term}M"));
        $gameStor->turntime = $newTurntime->format('Y-m-d H:i:s');
        $gameStor->starttime = (new \DateTimeImmutable($gameStor->starttime))
            ->add(new \DateInterval("PT{$minute}M"))
            ->format('Y-m-d H:i:s');

        $db->update('general', [
            'turntime' => $db->sqleval('DATE_ADD(turntime, INTERVAL %i MINUTE)', $minute)
        ], 'turntime<=DATE_ADD(turntime, INTERVAL %i MINUTE)', $term);
        $db->update('auction', [
            'expire' => $db->sqleval('DATE_ADD(expire, INTERVAL %i MINUTE)', $minute)
        ], true);
    }
}

function updateOnline()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect = $db->get();
    $nationname = ["재야"];

    //국가별 이름 매핑
    foreach (getAllNationStaticInfo() as $nation) {
        $nationname[$nation['nation']] = $nation['name'];
    }


    //동접수
    $query = "select no,name,nation from general where lastrefresh > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $result = MYDB_query($query, $connect) or Error(__LINE__ . MYDB_error($connect), "");
    $onlinenum = MYDB_num_rows($result);

    $onnation = array();
    $onnationstr = "";

    //국가별 접속중인 장수
    for ($i = 0; $i < $onlinenum; $i++) {
        $general = MYDB_fetch_array($result);
        if (isset($onnation[$general['nation']])) {
            $onnation[$general['nation']] .= $general['name'] . ', ';
        } else {
            $onnation[$general['nation']] = $general['name'] . ', ';
        }
    }

    //$onnation이 empty라면 굳이 foreach를 수행 할 이유가 없음. 
    if (!empty($onnation)) {
        foreach ($onnation as $key => $val) {
            $onnationstr .= "【{$nationname[$key]}】, ";

            if ($key == 0) {
                $gameStor->onlinegen = $onnation[0];
            } else {
                $nationStor = KVStorage::getStorage($db, $key, 'nation_env');
                $nationStor->onlinegen = $onnation[$key];
            }
        }
    }

    //접속중인 국가
    $gameStor->online = $onlinenum;
    $gameStor->onlinenation = $onnationstr;
}

function addAge()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');


    //나이와 호봉 증가
    $db->update('general', [
        'age' => $db->sqleval('age+1'),
        'belong' => $db->sqleval('belong+1')
    ], true);

    [$startYear, $year, $month] = $gameStor->getValuesAsArray(['startyear', 'year', 'month']);

    if ($year >= $startYear + 3) {
        foreach ($db->query('SELECT no,name,nation,leadership,strength,intel from general where specage<=age and special=%s', GameConst::$defaultSpecialDomestic) as $general) {
            $generalID = $general['no'];
            $special = SpecialityHelper::pickSpecialDomestic($general);
            $specialClass = buildGeneralSpecialDomesticClass($special);
            $specialText = $specialClass->getName();
            $db->update('general', [
                'special' => $special
            ], 'no=%i', $generalID);

            $logger = new ActionLogger($generalID, $general['nation'], $year, $month);

            $josaUl = JosaUtil::pick($specialText, '을');
            $logger->pushGeneralActionLog("특기 【<b><L>{$specialText}</></b>】{$josaUl} 익혔습니다!", ActionLogger::PLAIN);
            $logger->pushGeneralHistoryLog("특기 【<b><C>{$specialText}</></b>】{$josaUl} 습득");
        }

        foreach ($db->query('SELECT no,name,nation,leadership,strength,intel,npc,dex1,dex2,dex3,dex4,dex5 from general where specage2<=age and special2=%s', GameConst::$defaultSpecialWar) as $general) {
            $generalID = $general['no'];
            $special2 = SpecialityHelper::pickSpecialWar($general);
            $specialClass = buildGeneralSpecialWarClass($special2);
            $specialText = $specialClass->getName();

            $db->update('general', [
                'special2' => $special2
            ], 'no=%i', $general['no']);

            $logger = new ActionLogger($generalID, $general['nation'], $year, $month);

            $josaUl = JosaUtil::pick($specialText, '을');
            $logger->pushGeneralActionLog("특기 【<b><L>{$specialText}</></b>】{$josaUl} 익혔습니다!", ActionLogger::PLAIN);
            $logger->pushGeneralHistoryLog("특기 【<b><C>{$specialText}</></b>】{$josaUl} 습득");
        }
    }
}

function turnDate($curtime)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $admin = $gameStor->getValues(['startyear', 'starttime', 'turnterm', 'year', 'month']);

    $turn = $admin['starttime'];
    $curturn = cutTurn($curtime, $admin['turnterm']);
    $term = $admin['turnterm'];

    $num = intdiv((strtotime($curturn) - strtotime($turn)), $term * 60);

    $date = $admin['startyear'] * 12;
    $date += $num;

    $year = intdiv($date, 12);
    $month = 1 + $date % 12;

    // 바뀐 경우만 업데이트
    if ($admin['month'] != $month || $admin['year'] != $year) {
        $gameStor->year = $year;
        $gameStor->month = $month;
    }

    return [$year, $month];
}


function triggerTournament()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['tournament', 'tnmt_trig']);

    //현재 토너먼트 없고, 자동개시 걸려있을때, 40%확률
    if ($admin['tournament'] == 0 && $admin['tnmt_trig'] > 0 && rand() % 100 < 40) {
        $type = rand() % 5; //  0 : 전력전, 1 : 통솔전, 2 : 일기토, 3 : 설전
        //전력전 40%, 통, 일, 설 각 20%
        if ($type > 3) {
            $type = 0;
        }
        startTournament($admin['tnmt_trig'], $type);
    }
}

function CheckHall($no)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $types = [
        ["experience", 'natural'],
        ["dedication", 'natural'],
        ["firenum", 'rank'],
        ["warnum", 'rank'],
        ["killnum", 'rank'],
        ["winrate", 'calc'],
        ["killcrew", 'rank'],
        ["killrate", 'calc'],
        ["dex1", 'natural'],
        ["dex2", 'natural'],
        ["dex3", 'natural'],
        ["dex4", 'natural'],
        ["dex5", 'natural'],
        ["ttrate", 'calc'],
        ["tlrate", 'calc'],
        ["tsrate", 'calc'],
        ["tirate", 'calc'],
        ["betgold", 'rank'],
        ["betwin", 'rank'],
        ["betwingold", 'rank'],
        ["betrate", 'calc'],
    ];

    $generalObj = General::createGeneralObjFromDB($no, null, 2);

    $ttw = $generalObj->getRankVar('ttw');
    $ttd = $generalObj->getRankVar('ttd');
    $ttl = $generalObj->getRankVar('ttl');

    $tlw = $generalObj->getRankVar('tlw');
    $tld = $generalObj->getRankVar('tld');
    $tll = $generalObj->getRankVar('tll');

    $tsw = $generalObj->getRankVar('tsw');
    $tsd = $generalObj->getRankVar('tsd');
    $tsl = $generalObj->getRankVar('tsl');

    $tiw = $generalObj->getRankVar('tiw');
    $tid = $generalObj->getRankVar('tid');
    $til = $generalObj->getRankVar('til');

    $betWinGold = $generalObj->getRankVar('betwingold');
    $betGold = Util::valueFit($generalObj->getRankVar('betgold'), 1);

    $win = $generalObj->getRankVar('killnum');
    $war = Util::valueFit($generalObj->getRankVar('warnum'), 1);

    $kill = $generalObj->getRankVar('killcrew');
    $death = Util::valueFit($generalObj->getRankVar('deathcrew'), 1);

    $tt = Util::valueFit($ttw + $ttd + $ttl, 1);
    $tl = Util::valueFit($tlw + $tld + $tll, 1);
    $ts = Util::valueFit($tsw + $tsd + $tsl, 1);
    $ti = Util::valueFit($tiw + $tid + $til, 1);

    $calcVar = [];
    $calcVar['ttrate'] = $ttw / $tt;
    $calcVar['tlrate'] = $tlw / $tl;
    $calcVar['tsrate'] = $tsw / $ts;
    $calcVar['tirate'] = $tiw / $ti;
    $calcVar['betrate'] = $betWinGold / $betGold;
    $calcVar['winrate'] = $win / $war;
    $calcVar['killrate'] = $kill / $death;

    if ($generalObj instanceof DummyGeneral) {
        return;
    }

    $unitedDate = TimeUtil::now();
    $nation = $generalObj->getStaticNation();

    $serverCnt = $db->queryFirstField('SELECT count(*) FROM ng_games');

    [$scenarioIdx, $scenarioName, $startTime] = $gameStor->getValuesAsArray(['scenario', 'scenario_text', 'starttime']);

    $ownerName = $generalObj->getVar('owner_name');
    if ($generalObj->getVar('owner')) {
        $ownerName = RootDB::db()->queryFirstField('SELECT name FROM member WHERE no = %i', $generalObj->getVar('owner'));
    }

    foreach ($types as [$typeName, $valueType]) {

        if ($valueType === 'natural') {
            $value = $generalObj->getVar($typeName);
        } else if ($valueType === 'rank') {
            $value = $generalObj->getRankVar($typeName);
        } else if ($valueType === 'calc') {
            $value = $calcVar[$typeName];
        }

        //승률,살상률인데 10회 미만 전투시 스킵
        if (($typeName === 'winrate' || $typeName === 'killrate') && $generalObj->getRankVar('warnum') < 10) {
            continue;
        }
        //토너승률인데 50회 미만시 스킵
        if ($typeName === 'ttrate' && $tt < 50) {
            continue;
        }
        //토너승률인데 50회 미만시 스킵
        if ($typeName === 'tlrate' && $tl < 50) {
            continue;
        }
        //토너승률인데 50회 미만시 스킵
        if ($typeName === 'tsrate' && $ts < 50) {
            continue;
        }
        //토너승률인데 50회 미만시 스킵
        if ($typeName === 'tirate' && $ti < 50) {
            continue;
        }
        //수익률인데 1000미만시 스킵
        if ($typeName === 'betrate' && $generalObj->getRankVar('betgold') < 1000) {
            continue;
        }

        if ($value <= 0) {
            continue;
        }

        $aux = [
            'name' => $generalObj->getName(),
            'nationName' => $nation['name'],
            'bgColor' => $nation['color'],
            'fgColor' => newColor($nation['color']),
            'picture' => $generalObj->getVar('picture'),
            'imgsvr' => $generalObj->getVar('imgsvr'),
            'startTime' => $startTime,
            'unitedTime' => $unitedDate,
            'ownerName' => $ownerName,
            'serverID' => UniqueConst::$serverID,
            'serverIdx' => $serverCnt,
            'serverName' => UniqueConst::$serverName,
            'scenarioName' => $scenarioName,
        ];
        $jsonAux = Json::encode($aux);

        $db->insertIgnore('hall', [
            'server_id' => UniqueConst::$serverID,
            'season' => UniqueConst::$seasonIdx,
            'scenario' => $scenarioIdx,
            'general_no' => $no,
            'type' => $typeName,
            'value' => $value,
            'owner' => $generalObj->getVar('owner'),
            'aux' => $jsonAux
        ]);

        if ($db->affectedRows() == 0) {
            $db->update(
                'hall',
                [
                    'value' => $value,
                    'aux' => $jsonAux
                ],
                'server_id = %s AND scenario = %i AND general_no = %i AND type = %s AND value < %d',
                UniqueConst::$serverID,
                $scenarioIdx,
                $no,
                $typeName,
                $value
            );
        }
    }
}

function giveRandomUniqueItem(General $general, string $acquireType): bool
{
    $db = DB::db();
    //아이템 습득 상황
    $availableUnique = [];

    //TODO: 너무 바보 같다. 장기적으로는 유니크 아이템 테이블 같은게 필요하지 않을까?
    //일단은 '획득' 시에만 동작하므로 이대로 사용하기로...
    $occupiedUnique = [];

    foreach (array_keys(GameConst::$allItems) as $itemType) {
        foreach ($db->queryAllLists('SELECT %b, count(*) as cnt FROM general GROUP BY %b', $itemType, $itemType) as [$itemCode, $cnt]) {
            $itemClass = buildItemClass($itemCode);
            if (!$itemClass) {
                continue;
            }
            if ($itemClass->isBuyable()) {
                continue;
            }
            $occupiedUnique[$itemCode] = $cnt;
        }
    }

    foreach (GameConst::$allItems as $itemType => $itemCategories) {
        foreach ($itemCategories as $itemCode => $cnt) {
            if (!key_exists($itemCode, $occupiedUnique)) {
                $availableUnique[] = [[$itemType, $itemCode], $cnt];
                continue;
            }

            $remain = $cnt - $occupiedUnique[$itemCode];
            if ($remain > 0) {
                $availableUnique[] = [[$itemType, $itemCode], $remain];
            }
        }
    }

    if (!$availableUnique) {
        return false;
    }

    [$itemType, $itemCode] = Util::choiceRandomUsingWeightPair($availableUnique);

    $nationName = $general->getStaticNation()['name'];
    $generalName = $general->getName();
    $josaYi = JosaUtil::pick($generalName, '이');
    $itemObj = buildItemClass($itemCode);
    $itemName = $itemObj->getName();
    $itemRawName = $itemObj->getRawName();
    $josaUl = JosaUtil::pick($itemRawName, '을');


    $general->setVar($itemType, $itemCode);

    $logger = $general->getLogger();

    $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 습득했습니다!");
    $logger->pushGeneralHistoryLog("<C>{$itemName}</>{$josaUl} 습득");
    $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 습득했습니다!");
    $logger->pushGlobalHistoryLog("<C><b>【{$acquireType}】</b></><D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 습득했습니다!");

    return true;
}

function tryUniqueItemLottery(General $general, string $acquireType = '아이템'): bool
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    if ($general->getVar('npc') >= 2) {
        return false;
    }

    if ($general->getVar('npc') > 6) {
        return false;
    }

    foreach ($general->getItems() as $item) {
        if (!$item) {
            continue;
        }
        if (!$item->isBuyable()) {
            return false;
        }
    }

    $scenario = $gameStor->scenario;
    $genCount = $db->queryFirstField('SELECT count(*) FROM general WHERE npc<2');

    if ($scenario < 100) {
        $prob = 1 / ($genCount * 5); // 5~6개월에 하나씩 등장
    } else {
        $prob = 1 / $genCount; // 1~2개월에 하나씩 등장
    }

    if ($acquireType == '투표') {
        $prob = 1 / ($genCount * 0.7 / 3); // 투표율 70%, 투표 한번에 2~3개 등장
    } else if ($acquireType == '랜덤 임관') {
        $prob = 1 / ($genCount / 10 / 2); // 랜임시 2개(10%) 등장(200명중 20명 랜임시도?)
    } else if ($acquireType == '건국') {
        $prob = 1 / ($genCount / 10 / 4); // 건국시 4개(20%) 등장(200명시 20국 정도 됨)
    }

    $prob = Util::valueFit($prob, 1 / 3, 1);

    if (!Util::randBool($prob)) {
        return false;
    }

    return giveRandomUniqueItem($general, $acquireType);
}

function getAdmin()
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    return $gameStor->getAll();
}

/** @return General[] */
function deleteNation(General $lord, bool $applyDB):array
{
    $lordID = $lord->getID();
    $nationID = $lord->getNationID();

    DeleteConflict($nationID);

    $db = DB::db();
    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    
    $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $nationID);
    $nationName = $oldNation['name'];

    $logger = $lord->getLogger();

    $josaUn = JosaUtil::pick($nationName, '은');
    $logger->pushGlobalHistoryLog("<R><b>【멸망】</b></><D><b>{$nationName}</b></>{$josaUn} <R>멸망</>했습니다.");

    
    $oldNationGeneralList = General::createGeneralObjListFromDB(
        $db->queryFirstColumn(
            'SELECT `no` FROM general WHERE nation=%i AND no != %i',
            $nationID,
            $lordID
        ), 
        ['npc', 'gold', 'rice', 'experience', 'explevel', 'dedication', 'dedlevel', 'aux'], 1
    );
    $oldNationGeneralList[$lordID] = $lord;
    
    $oldNation['generals'] = array_keys($oldNationGeneralList);
    $oldNation['aux'] = Json::decode($oldNation['aux']);
    $oldNation['msg'] = $nationStor->notice;
    $oldNation['scout_msg'] = $nationStor->scout_msg;
    $oldNation['aux'] += $nationStor->max_power;
    $oldNation['history'] = getNationHistoryAll($nationID);

    $josaYi = JosaUtil::pick($nationName, '이');
    $destroyLog = "<D><b>{$nationName}</b></>{$josaYi} <R>멸망</>했습니다.";
    $destroyHistoryLog = "<D><b>{$nationName}</b></>{$josaYi} <R>멸망</>";

    // 전 장수 재야로
    foreach($oldNationGeneralList as $general){
        $general->setVar('belong', 0);
        $general->setVar('troop', 0);
        $general->setVar('officer_level', 0);
        $general->setVar('officer_city', 0);
        $general->setVar('nation', 0);
        $general->setVar('permission', 'normal');
        $logger = $general->getLogger();
        $logger->pushGeneralActionLog($destroyLog, ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog($destroyHistoryLog);

        if($applyDB){
            $general->applyDB($db);
        }
    }

    // 도시 공백지로
    $db->update('city', [
        'nation' => 0,
        'front' => 0,
    ], 'nation=%i', $nationID);
    // 부대 삭제
    $db->delete('troop', 'nation=%i', $nationID);

    // 국가 삭제
    $db->insert('ng_old_nations', [
        'server_id' => UniqueConst::$serverID,
        'nation' => $nationID,
        'data' => Json::encode($oldNation)
    ]);
    $db->delete('nation', 'nation=%i', $nationID);
    $db->delete('nation_turn', 'nation_id=%i', $nationID);
    // 외교 삭제
    $db->delete('diplomacy', 'me = %i OR you = %i', $nationID, $nationID);

    refreshNationStaticInfo();

    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    $nationStor->resetValues();

    return $oldNationGeneralList;
}

function nextRuler(General $general)
{
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
    $nation = $general->getStaticNation();
    $nationName = $nation['name'];
    $nationID = $nation['nation'];

    $candidate = null;

    //npc or npc유저인 경우 후계 찾기
    if ($general->getVar('npc') > 0) {
        $candidate = $db->queryFirstRow(
            'SELECT no,name,officer_level,IF(ABS(affinity-%i)>75,150-ABS(affinity-%i),ABS(affinity-%i)) as npcmatch2 from general where nation=%i and officer_level!=12 and 1 <= npc and npc<=3 order by npcmatch2,rand() LIMIT 1',
            $general->getVar('affinity'),
            $general->getVar('affinity'),
            $general->getVar('affinity'),
            $nationID
        );
    }
    if (!$candidate) {
        $candidate = $db->queryFirstRow(
            'SELECT no,name,npc,officer_level FROM general WHERE nation=%i and officer_level!= 12 AND officer_level >= 9 and npc != 5 ORDER BY officer_level DESC LIMIT 1',
            $nationID
        );
    }
    if (!$candidate) {
        $candidate = $db->queryFirstRow(
            'SELECT no,name,npc,officer_level FROM general WHERE nation=%i and officer_level!= 12 and npc != 5 ORDER BY dedication DESC LIMIT 1',
            $nationID
        );
    }


    if (!$candidate) {
        deleteNation($general, true);
        return;
    }

    $nextRulerID = $candidate['no'];
    $nextRulerName = $candidate['name'];

    $general->setVar('officer_level', 1);
    $general->setVar('officer_city', 0);

    $db->update('general', [
        'officer_level' => 12,
        'officer_city' => 0,
    ], 'no=%i AND nation=%i', $nextRulerID, $nationID);
    if ($db->affectedRows() == 0) {
        throw new \RuntimeException('선양되지 않음');
    }

    $josaYi = JosaUtil::pick($nextRulerName, '이');

    $nextRulerLogger = new ActionLogger($nextRulerID, $nationID, $year, $month);
    $nextRulerLogger->pushGlobalHistoryLog("<C><b>【유지】</b></><Y>{$nextRulerName}</>{$josaYi} <D><b>{$nationName}</b></>의 유지를 이어 받았습니다");
    $nextRulerLogger->pushGeneralHistoryLog("<C><b>【유지】</b></><Y>{$nextRulerName}</>{$josaYi} <D><b>{$nationName}</b></>의 유지를 이어 받음.");
    $nextRulerLogger->flush();
    // 장수 삭제 및 부대처리는 checkTurn에서
}

/**
 * $maxDist 이내의 도시를 검색하는 함수
 * @param $from 기준 도시 코드
 * @param $maxDist 검색하고자 하는 최대 거리
 * @param $distForm 리턴 타입. true일 경우 $result[$dist] = [...$city] 이며, false일 경우 $result[$city] = $dist 임
 */
function searchDistance(int $from, int $maxDist = 99, bool $distForm = false)
{
    $queue = new \SplQueue();

    $cities = [];
    $distanceList = [];

    $queue->enqueue([$from, 0]);

    while (!$queue->isEmpty()) {
        list($cityID, $dist) = $queue->dequeue();
        if (key_exists($cityID, $cities)) {
            continue;
        }

        if (!key_exists($dist, $distanceList)) {
            $distanceList[$dist] = [];
        }
        $distanceList[$dist][] = $cityID;

        $cities[$cityID] = $dist;
        if ($dist >= $maxDist) {
            continue;
        }

        foreach (array_keys(CityConst::byID($cityID)->path) as $connCityID) {
            if (key_exists($connCityID, $cities)) {
                continue;
            }
            $queue->enqueue([$connCityID, $dist + 1]);
        }
    }

    if ($distForm) {
        unset($distanceList[0]);
        return $distanceList;
    } else {
        return $cities;
    }
}

/**
 * $from 으로 지정한 도시부터 $to 도시까지의 최단 거리를 계산해 줌
 * @param $from 기준 도시 코드
 * @param $to 대상 도시 코드
 * @param array|null $linkNationList 경로에 해당하는 국가 리스트, null인 경우 제한 없음
 * @return null|int 거리. 
 */
function calcCityDistance(int $from, int $to, ?array $linkNationList): ?int
{
    $queue = new \SplQueue();

    $cities = [];

    if ($linkNationList === []) {
        return null;
    }

    if ($linkNationList !== null) {
        $db = DB::db();
        //TODO: 도시-국가 캐싱이 있으면 쓸모 있지 않을까 
        $allowedCityList = [];
        foreach ($db->queryFirstColumn(
            'SELECT city FROM city WHERE nation IN %li',
            $linkNationList
        ) as $cityID) {
            $allowedCityList[$cityID] = $cityID;
        }
    } else {
        $allowedCityList = CityConst::all();
    }

    if (!key_exists($to, $allowedCityList)) {
        return null;
    }

    if ($from === $to) {
        return 0;
    }

    $queue->enqueue([$from, 0]);

    while (!$queue->isEmpty()) {
        list($cityID, $dist) = $queue->dequeue();
        if (key_exists($cityID, $cities)) {
            continue;
        }

        $cities[$cityID] = $dist;

        if ($cityID === $to) {
            return $dist;
        }

        foreach (array_keys(CityConst::byID($cityID)->path) as $connCityID) {
            if (!key_exists($connCityID, $allowedCityList)) {
                continue;
            }
            if (key_exists($connCityID, $cities)) {
                continue;
            }
            $queue->enqueue([$connCityID, $dist + 1]);
        }
    }

    //길이 없음
    return null;
}
/**
 * $from 으로 지정한 도시의 인접 도시와 $to 도시의 최단 거리를 계산해 줌
 * @param $from 기준 도시 코드
 * @param $to 대상 도시 코드
 * @param $linkNationList 경로에 해당하는 국가 리스트
 * @return array  $dist=>[cityID, $nation] 배열 가장 앞이 가장 가까움
 */
function searchDistanceListToDest(int $from, int $to, array $linkNationList)
{
    $queue = new \SplQueue();

    $cities = [];

    $db = DB::db();

    //TODO: 도시-국가 캐싱이 있으면 쓸모 있지 않을까 
    $allowedCityList = [];
    foreach ($db->queryAllLists(
        'SELECT city, nation FROM city WHERE nation IN %li',
        $linkNationList
    ) as [$cityID, $nationID]) {
        $allowedCityList[$cityID] = $nationID;
    }

    $remainFromCities = [];
    foreach (array_keys(CityConst::byID($from)->path) as $fromCityID) {
        if (key_exists($fromCityID, $allowedCityList)) {
            $remainFromCities[$fromCityID] = true;
        }
    }

    if (!key_exists($to, $allowedCityList)) {
        return [];
    }

    $result = [];
    $queue->enqueue([$to, 0]);

    while (!empty($remainFromCities) && !$queue->isEmpty()) {
        list($cityID, $dist) = $queue->dequeue();
        if (key_exists($cityID, $cities)) {
            continue;
        }

        $cities[$cityID] = $dist;

        if (key_exists($cityID, $remainFromCities)) {
            unset($remainFromCities[$cityID]);
            $result[$dist][] = [$cityID, $allowedCityList[$cityID]];
        }

        foreach (array_keys(CityConst::byID($cityID)->path) as $connCityID) {
            if ($allowedCityList !== null && !key_exists($connCityID, $allowedCityList)) {
                continue;
            }
            if (key_exists($connCityID, $cities)) {
                continue;
            }
            $queue->enqueue([$connCityID, $dist + 1]);
        }
    }

    return $result;
}

/**
 * 지정된 도시 내의 최단 거리를 계산해 줌(Floyd-Warshall)
 * @param $cityIDList 이동 가능한 도시 리스트.
 * @return array [from][to] 로 표시되는 거리값
 */
function searchAllDistanceByCityList(array $cityIDList): array
{
    if (!$cityIDList) {
        return [];
    }
    $cityList = [];
    foreach ($cityIDList as $cityID) {
        $cityList[$cityID] = $cityID;
    }

    $distanceList = [];
    foreach ($cityIDList as $cityID) {
        $nearList = [$cityID => 0];
        foreach (array_keys(CityConst::byID($cityID)->path) as $nextCityID) {
            if (!key_exists($nextCityID, $cityList)) {
                continue;
            }
            $nearList[$nextCityID] = 1;
        }
        $distanceList[$cityID] = $nearList;
    }

    //Floyd-Warshall
    foreach ($cityList as $cityStop) {
        foreach ($cityList as $cityFrom) {
            foreach ($cityList as $cityTo) {
                if (!key_exists($cityStop, $distanceList[$cityFrom])) {
                    continue;
                }
                if (!key_exists($cityTo, $distanceList[$cityStop])) {
                    continue;
                }

                if (!key_exists($cityTo, $distanceList[$cityFrom])) {
                    $distanceList[$cityFrom][$cityTo] = $distanceList[$cityFrom][$cityStop] + $distanceList[$cityStop][$cityTo];
                    continue;
                }

                $distanceList[$cityFrom][$cityTo] = min($distanceList[$cityFrom][$cityStop] + $distanceList[$cityStop][$cityTo], $distanceList[$cityFrom][$cityTo]);
            }
        }
    }

    return $distanceList;
}

/**
 * 지정된 국가 내의 모든 도시들의 최단 거리를 계산해 줌(Floyd-Warshall)
 * @param $linkNationList 경로에 해당하는 국가 리스트
 * @param $suppliedCityOnly 보급된 도시만을 이용해 검색
 * @return array [from][to] 로 표시되는 거리값
 */
function searchAllDistanceByNationList(array $linkNationList, bool $suppliedCityOnly = false): array
{
    if (!$linkNationList) {
        return [];
    }
    $db = DB::db();
    if ($suppliedCityOnly) {
        $cityIDList = $db->queryFirstColumn('SELECT city FROM city WHERE nation IN %li AND supply=1', $linkNationList);
    } else {
        $cityIDList = $db->queryFirstColumn('SELECT city FROM city WHERE nation IN %li', $linkNationList);
    }
    return searchAllDistanceByCityList($cityIDList);
}

function isNeighbor(int $nation1, int $nation2, bool $includeNoSupply = true)
{
    if ($nation1 === $nation2) {
        return false;
    }
    $db = DB::db();

    $nation1Cities = [];

    if ($includeNoSupply) {
        $supplySql = '';
    } else {
        $supplySql = 'AND supply = 1';
    }

    foreach ($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i %l', $nation1, $supplySql) as $city) {
        $nation1Cities[$city] = $city;
    }

    foreach ($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i %l', $nation2, $supplySql) as $city) {
        foreach (array_keys(CityConst::byID($city)->path) as $adjCity) {
            if (key_exists($adjCity, $nation1Cities)) {
                return true;
            }
        }
    }

    return false;
}

function SabotageInjury(array $cityGeneralList, string $reason): int
{
    $injuryCount = 0;
    $josaRo = JosaUtil::pick($reason, '로');
    $text = "<M>{$reason}</>{$josaRo} 인해 <R>부상</>을 당했습니다.";

    $db = DB::db();

    foreach ($cityGeneralList as $general) {
        /** @var General $general */
        if (!Util::randBool(0.3)) {
            continue;
        }
        $general->getLogger()->pushGeneralActionLog($text);

        $general->increaseVarWithLimit('injury', Util::randRangeInt(1, 16), 0, 80);
        $general->multiplyVar('crew', 0.98);
        $general->multiplyVar('atmos', 0.98);
        $general->multiplyVar('train', 0.98);

        $general->applyDB($db);

        $injuryCount += 1;
    }

    return $injuryCount;
}

function getRandTurn($term, ?\DateTimeInterface $baseDateTime = null)
{
    if ($baseDateTime === null) {
        $baseDateTime = new \DateTimeImmutable();
    } else if ($baseDateTime instanceof \DateTime) {
        $baseDateTime = \DateTimeImmutable::createFromMutable($baseDateTime);
    } else if ($baseDateTime instanceof \DateTimeImmutable) {
        //do Nothing
    } else {
        throw new MustNotBeReachedException();
    }

    $randSecond = Util::randRangeInt(0, 60 * $term - 1);
    $randFraction = Util::randRangeInt(0, 999999) / 1000000; //6자리 소수

    return $baseDateTime->add(TimeUtil::secondsToDateInterval($randSecond + $randFraction))->format('Y-m-d H:i:s');
}

function getRandTurn2($term, ?\DateTimeInterface $baseDateTime = null)
{
    if ($baseDateTime === null) {
        $baseDateTime = new \DateTimeImmutable();
    } else if ($baseDateTime instanceof \DateTime) {
        $baseDateTime = \DateTimeImmutable::createFromMutable($baseDateTime);
    } else {
        throw new MustNotBeReachedException();
    }
    $randSecond = Util::randRangeInt(0, 60 * $term - 1);
    $randFraction = Util::randRangeInt(0, 999999) / 1000000; //6자리 소수

    return $baseDateTime->sub(TimeUtil::secondsToDateInterval($randSecond + $randFraction))->format('Y-m-d H:i:s');
}
