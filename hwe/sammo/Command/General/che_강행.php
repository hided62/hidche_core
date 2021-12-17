<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\GameUnitConst;
use \sammo\LastTurn;
use \sammo\Command;

use function sammo\getMapTheme;
use function \sammo\printCitiesBasedOnDistance;
use function sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_강행 extends Command\GeneralCommand
{
    static protected $actionName = '강행';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        if (!key_exists('destCityID', $this->arg)) {
            return false;
        }
        if (!key_exists($this->arg['destCityID'], CityConst::all())) {
            return false;
        }
        $this->arg = [
            'destCityID' => $this->arg['destCityID']
        ];
        return true;
    }

    protected function init()
    {
        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints = [];
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID'], true);

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints = [
            ConstraintHelper::NotSameDestCity(),
            ConstraintHelper::NearCity(3),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}(통솔경험";
        if ($reqGold > 0) {
            $title .= ", 자금{$reqGold}";
        }
        if ($reqRice > 0) {
            $title .= ", 군량{$reqRice}";
        }
        $title .= ', 병력,훈련,사기↓)';
        return $title;
    }

    public function getCost(): array
    {
        $env = $this->env;
        return [$env['develcost'] * 5, 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function getBrief(): string
    {
        $commandName = $this->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "【{$destCityName}】{$josaRo} {$commandName}";
    }

    public function getFailString(): string
    {
        $commandName = $this->getName();
        $failReason = $this->testFullConditionMet();
        if ($failReason === null) {
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "{$failReason} <G><b>{$destCityName}</b></>{$josaRo} {$commandName} 실패.";
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $destCityName = $this->destCity['name'];
        $destCityID = $this->destCity['city'];
        $josaRo = JosaUtil::pick($destCityName, '로');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 강행했습니다. <1>$date</>");

        $exp = 100;
        $general->setVar('city', $destCityID);

        if ($general->getVar('officer_level') == 12 && $this->nation['level'] == 0) {

            $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no!=%i', $general->getNationID(), $general->getID());
            if ($generalList) {
                $db->update('general', [
                    'city' => $destCityID
                ], 'no IN %li and nation=%i', $generalList, $general->getNationID());
            }

            foreach ($generalList as $targetGeneralID) {
                $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $env['year'], $env['month']);
                $targetLogger->pushGeneralActionLog("방랑군 세력이 <G><b>{$destCityName}</b></>{$josaRo} 강행했습니다.", ActionLogger::PLAIN);
                $targetLogger->flush();
            }
        }

        [$reqGold, $reqRice] = $this->getCost();
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->addExperience($exp);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);

        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        return [
            'cities' => \sammo\JSOptionsForCities(),
            'mapTheme' => getMapTheme(),
            'distanceList' => \sammo\JSCitiesBasedOnDistance($this->generalObj->getCityID(), 3),
        ];
    }
}