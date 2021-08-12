<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\Session;
use \sammo\KVStorage;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\GameUnitConst;
use \sammo\LastTurn;
use \sammo\Command;
use \sammo\ServConfig;

use function \sammo\getTechCall;
use function \sammo\tryUniqueItemLottery;
use function \sammo\getTechAbil;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;



class che_징병 extends Command\GeneralCommand
{
    static protected $actionName = '징병';
    static protected $costOffset = 1;
    static public $reqArg = true;

    static protected $defaultTrain;
    static protected $defaultAtmos;

    protected $maxCrew = 0;
    protected $reqCrew = 0;
    /** @var \sammo\GameUnitDetail */
    protected $reqCrewType;
    /** @var \sammo\GameUnitDetail */
    protected $currCrewType;

    static protected $isInitStatic = false;
    protected static function initStatic()
    {
        static::$defaultTrain = GameConst::$defaultTrainLow;
        static::$defaultAtmos = GameConst::$defaultAtmosLow;
    }

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        if (!key_exists('crewType', $this->arg)) {
            return false;
        }
        if (!key_exists('amount', $this->arg)) {
            return false;
        }
        $crewType = $this->arg['crewType'];
        $amount = $this->arg['amount'];

        if (!is_int($crewType)) {
            return false;
        }
        if (!is_numeric($amount)) {
            return false;
        }
        $amount = (int) $amount;

        if (GameUnitConst::byID($crewType) === null) {
            return false;
        }
        if ($amount < 0) {
            return false;
        }
        $this->arg = [
            'crewType' => $crewType,
            'amount' => $amount
        ];
        return true;
    }

    protected function init()
    {
        $this->setCity();
        $this->setNation(['tech']);

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqCityCapacity('pop', '주민', GameConst::$minAvailableRecruitPop + 100),
            ConstraintHelper::ReqCityTrust(20),
        ];
    }

    protected function initWithArg()
    {
        $general = $this->generalObj;

        $leadership = $general->getLeadership(true);
        $currCrewType = $general->getCrewTypeObj();
        $maxCrew = $leadership * 100;

        $reqCrewType = GameUnitConst::byID($this->arg['crewType']);
        if ($reqCrewType->id == $currCrewType->id) {
            $maxCrew -= $general->getVar('crew');
        }
        $this->maxCrew = Util::valueFit($this->arg['amount'], 100, $maxCrew);
        $reqCrew = Util::valueFit($this->arg['amount'], 100);
        $this->reqCrew = $reqCrew;
        $this->reqCrewType = $reqCrewType;
        $this->currCrewType = $currCrewType;

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqCityCapacity('pop', '주민', GameConst::$minAvailableRecruitPop + $reqCrew),
            ConstraintHelper::ReqCityTrust(20),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::ReqGeneralCrewMargin($reqCrewType->id),
            ConstraintHelper::AvailableRecruitCrewType($reqCrewType->id)
        ];
    }

    public function getBrief(): string
    {
        $crewTypeName = $this->reqCrewType->name;
        $amount = $this->reqCrew;
        $commandName = static::getName();
        return "【{$crewTypeName}】 {$amount}명 {$commandName}";
    }

    public function getCommandDetailTitle(): string
    {
        return "{$this->getName()}(통솔경험)";
    }

    public function getCost(): array
    {
        if (!$this->isArgValid) {
            return [0, 0];
        }
        $reqGold = $this->reqCrewType->costWithTech($this->nation['tech'], $this->maxCrew);
        $reqGold = $this->generalObj->onCalcDomestic('징병', 'cost', $reqGold, ['armType' => $this->reqCrewType->armType]);
        $reqGold *= static::$costOffset;
        $reqRice = $this->maxCrew / 100;

        $reqGold = Util::round($reqGold);
        $reqRice = Util::round($reqRice);
        return [$reqGold, $reqRice];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $reqCrew = $this->maxCrew;
        $reqCrewText = number_format($reqCrew);
        $reqCrewType = $this->reqCrewType;

        $currCrew = $general->getVar('crew');
        $currCrewType = $this->currCrewType;

        $crewTypeName = $reqCrewType->name;

        $logger = $general->getLogger();

        if ($reqCrewType->id == $currCrewType->id && $currCrew > 0) {
            $logger->pushGeneralActionLog("{$crewTypeName} <C>{$reqCrewText}</>명을 추가{$this->getName()}했습니다. <1>$date</>");
            $train = ($currCrew * $general->getVar('train') + $reqCrew * static::$defaultTrain) / ($currCrew + $reqCrew);
            $atmos = ($currCrew * $general->getVar('atmos') + $reqCrew * static::$defaultAtmos) / ($currCrew + $reqCrew);

            $general->increaseVar('crew', $reqCrew);
            $general->setVar('train', $train);
            $general->setVar('atmos', $atmos);
        } else {
            $logger->pushGeneralActionLog("{$crewTypeName} <C>{$reqCrewText}</>명을 {$this->getName()}했습니다. <1>$date</>");
            $general->setVar('crewtype', $reqCrewType->id);
            $general->setVar('crew', $reqCrew);
            $general->setVar('train', static::$defaultTrain);
            $general->setVar('atmos', static::$defaultAtmos);
        }

        $newTrust = Util::valueFit($this->city['trust'] - ($reqCrew / $this->city['pop']) / static::$costOffset * 100, 0);

        $db->update('city', [
            'trust' => $newTrust,
            'pop' => $this->city['pop'] - $reqCrew
        ], 'city=%i', $general->getCityID());

        $exp = Util::round($reqCrew / 100);
        $ded = Util::round($reqCrew / 100);

        $general->addDex($reqCrewType, $reqCrew / 100, false);

        [$reqGold, $reqRice] = $this->getCost();

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->setAuxVar('armType', $reqCrewType->armType);
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    public function getJSFiles(): array
    {
        return [
            'js/recruitCrewForm.js'
        ];
    }

    public function getForm(): string
    {
        $db = DB::db();

        $general = $this->generalObj;

        [$nationLevel, $tech] = $db->queryFirstList('SELECT level,tech FROM nation WHERE nation=%i', $general->getNationID());
        if (!$nationLevel) {
            $nationLevel = 0;
        }

        if (!$tech) {
            $tech = 0;
        }

        $ownCities = [];
        $ownRegions = [];
        $year = $this->env['year'];
        $startyear = $this->env['startyear'];

        $relativeYear = $year - $startyear;

        foreach (DB::db()->query('SELECT city, region from city where nation = %i', $general->getNationID()) as $city) {
            $ownCities[$city['city']] = 1;
            $ownRegions[$city['region']] = 1;
        }

        $leadership = $general->getLeadership();
        $fullLeadership = $general->getLeadership(false);
        $abil = getTechAbil($tech);

        $armTypes = [];

        foreach (GameUnitConst::allType() as $armType => $armName) {
            $armTypeCrews = [];

            foreach (GameUnitConst::byType($armType) as $unit) {
                $crewObj = new \stdClass;
                $crewObj->showDefault = 'true';

                $crewObj->id = $unit->id;

                if ($unit->reqTech == 0) {
                    $crewObj->bgcolor = 'green';
                } else {
                    $crewObj->bgcolor = 'limegreen';
                }

                if (!$unit->isValid($ownCities, $ownRegions, $relativeYear, $tech)) {
                    $crewObj->showDefault = 'false';
                    $crewObj->bgcolor = 'red';
                }

                $crewObj->baseRice = $general->onCalcDomestic($this->getName(), 'rice', $unit->riceWithTech($tech), ['armType' => $unit->armType]);
                $crewObj->baseCost = $general->onCalcDomestic($this->getName(), 'cost', $unit->costWithTech($tech), ['armType' => $unit->armType]);

                $crewObj->name = $unit->name;
                $crewObj->attack = $unit->attack + $abil;
                $crewObj->defence = $unit->defence + $abil;
                $crewObj->speed = $unit->speed;
                $crewObj->avoid = $unit->avoid;
                if ($this->env['show_img_level'] < 2) {
                    $crewObj->img = ServConfig::$sharedIconPath . "/default.jpg";
                } else {
                    $crewObj->img = ServConfig::$gameImagePath . "/crewtype" . $unit->id . ".png";
                }

                $crewObj->baseRiceShort = round($crewObj->baseRice, 1);
                $crewObj->baseCostShort = round($crewObj->baseCost, 1);

                $crewObj->info = join('<br>', $unit->info);


                $armTypeCrews[] = $crewObj;
            }
            $armTypes[] = [$armName, $armTypeCrews];
        }
        $commandName = $this->getName();

        $techLevelText = getTechCall($tech);

        $crew = $general->getVar('crew');
        $gold = $general->getVar('gold');
        $crewTypeObj = $general->getCrewTypeObj();

        ob_start();
?>

        <font size=2>병사를 모집합니다.
            <?php if ($commandName == '징병') : ?>
                훈련과 사기치는 낮지만 가격이 저렴합니다.<br>
            <?php else : ?>
                훈련과 사기치는 높지만 자금이 많이 듭니다.
            <?php endif; ?>
            가능한 수보다 많게 입력하면 가능한 최대 병사를 모집합니다.<br>
            이미 병사가 있는 경우 추가<?= $commandName ?>되며, 병종이 다를경우는 기존의 병사는 소집해제됩니다.<br>
            현재 <?= $commandName ?> 가능한 병종은 <font color=green>녹색</font>으로 표시되며,<br>
            현재 <?= $commandName ?> 가능한 특수병종은 <font color=limegreen>초록색</font>으로 표시됩니다.<br>

            <table class='tb_layout' style='margin:auto;'>
                <thead>
                    <tr>
                        <td colspan=11>
                            <div style='float:right'><input type='checkbox' id="show_unavailable_troops">불가능한 병종 표시</input></div>
                            <?php if ($commandName == '모병') : ?>
                                <div style='text-align:center;'>모병은 가격 2배의 자금이 소요됩니다.</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=11 align=center class='bg2'>
                            현재 기술력 : <?= $techLevelText ?>
                            현재 통솔 : <?= $leadership ?><?= ($leadership != $fullLeadership) ? "({$fullLeadership})" : '' ?>
                            현재 병종 : <?= $crewTypeObj->name ?>
                            현재 병사 : <?= $crew ?>
                            현재 자금 : <?= $gold ?>
                        </td>
                    </tr>
                    <tr>
                        <td width=64 align=center class='bg1'>사진</td>
                        <td width=64 align=center class='bg1'>병종</td>
                        <td width=40 align=center class='bg1'>공격</td>
                        <td width=40 align=center class='bg1'>방어</td>
                        <td width=40 align=center class='bg1'>기동</td>
                        <td width=40 align=center class='bg1'>회피</td>
                        <td width=40 align=center class='bg1'>가격</td>
                        <td width=40 align=center class='bg1'>군량</td>
                        <td width=180 align=center class='bg1'>병사수</td>
                        <td width=50 align=center class='bg1'>행동</td>
                        <td width=300 align=center class='bg1'>특징</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($armTypes as [$armName, $armTypeCrews]) : ?>
                        <tr>
                            <td colspan=11><?= $armName ?> 계열</td>
                        </tr>
                        <?php foreach ($armTypeCrews as $crewObj) : ?>
                            <tr id="crewType<?= $crewObj->id ?>" class="show_default_<?= $crewObj->showDefault ?>" style='height:64px;background-color:<?= $crewObj->bgcolor ?>' data-rice="<?= $crewObj->baseRice ?>" data-cost="<?= $crewObj->baseCost ?>">
                                <td style='background:#222222 no-repeat center url("<?= $crewObj->img ?>");background-size:64px'></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->name ?></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->attack ?></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->defence ?></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->speed ?></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->avoid ?></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->baseCostShort ?></td>
                                <td style='text-align:center;vertical-align:middle;'><?= $crewObj->baseRiceShort ?></td>
                                <td style='text-align:center;vertical-align:middle;' class='input_form' data-crewtype='<?= $crewObj->id ?>'>
                                    <input type=button value='절반' class='btn_half'><input type=button value='채우기' class='btn_fill'><input type=button value='가득' class='btn_full'><br>
                                    <input type=text data-crewtype='<?= $crewObj->id ?>' class=form_double name=double maxlength=3 size=3 style=text-align:right;color:white;background-color:black>00명
                                    <input type=text class=form_cost name=cost maxlength=5 size=5 readonly style=text-align:right;color:white;background-color:black>원

                                </td>
                                <td style='position:relative;height:64px;'><input type=submit value='<?= $commandName ?>' class='submit_btn' style='width:100%;height:44px;margin:10px 0;display:block;position: absolute;left:0;top:0;'></td>
                                <td><?= $crewObj->info ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <input type='hidden' id='amount' value='1'>
            <input type='hidden' id='crewType' value='<?= $crewTypeObj->id ?>'>
            <script>
                window.currentTech = <?= $tech ?>;
                window.leadership = <?= $leadership ?>;
                window.fullLeadership = <?= $fullLeadership ?>;
                window.currentCrewType = <?= $crewTypeObj->id ?>;
                window.currentCrew = <?= $crew ?>;
                window.currentGold = <?= $gold ?>;
                window.is모병 = <?= ($this->getName() == '모병') ? 'true' : 'false' ?>;
            </script>
    <?php
        return ob_get_clean();
    }
}
