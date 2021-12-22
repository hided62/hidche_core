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
use function sammo\getTechLevel;

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

    public function exportJSVars(): array
    {
        $general = $this->generalObj;
        $db = DB::db();

        [$nationLevel, $tech] = $db->queryFirstList('SELECT level,tech FROM nation WHERE nation=%i', $general->getNationID());
        if (!$nationLevel) {
            $nationLevel = 0;
        }

        if (!$tech) {
            $tech = 0;
        }

        $year = $this->env['year'];
        $startyear = $this->env['startyear'];
        $relativeYear = $year - $startyear;

        $ownCities = [];
        $ownRegions = [];

        foreach (DB::db()->query('SELECT city, region from city where nation = %i', $general->getNationID()) as $city) {
            $ownCities[$city['city']] = 1;
            $ownRegions[$city['region']] = 1;
        }

        $leadership = $general->getLeadership();
        $fullLeadership = $general->getLeadership(false);
        $abil = getTechAbil($tech);

        $armCrewTypes = [];
        foreach (GameUnitConst::allType() as $armType => $armName) {
            $armCrewType = [
                'armType' => $armType,
                'armName' => $armName,
                'values' => [],
            ];

            $crewTypes = [];
            foreach (GameUnitConst::byType($armType) as $unit) {
                $crewObj = new \stdClass;

                $crewObj->id = $unit->id;
                $crewObj->reqTech = $unit->reqTech;
                $crewObj->reqYear = $unit->reqYear;

                /*
                if ($unit->reqTech == 0) {
                    $crewObj->bgcolor = 'green';
                } else {
                    $crewObj->bgcolor = 'limegreen';
                }
                */

                $crewObj->notAvailable = !$unit->isValid($ownCities, $ownRegions, $relativeYear, $tech);

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

                $crewObj->info = $unit->info;

                $armCrewType['values'][] = $crewObj;
            }


            $armCrewTypes[] = $armCrewType;
        }

        $crew = $general->getVar('crew');
        $gold = $general->getVar('gold');
        $crewTypeObj = $general->getCrewTypeObj();

        return [
            'procRes' => [
                'relYear' => $relativeYear,
                'year' => $year,
                'tech' => $tech,
                'techLevel' => getTechLevel($tech),
                'startYear' => $startyear,
                'goldCoeff' => static::$costOffset,
                'leadership' => $leadership,
                'fullLeadership' => $fullLeadership,
                'armCrewTypes' => $armCrewTypes,
                'currentCrewType' => $crewTypeObj->id,
                'crew' => $crew,
                'gold' => $gold,
            ]
        ];
    }
}
