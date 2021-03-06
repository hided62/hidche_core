<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\ActionLogger;
use \sammo\GameUnitConst;
use \sammo\LastTurn;
use \sammo\Command;
use \sammo\Json;

use function \sammo\searchDistance;

use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use sammo\Enums\InheritanceKey;

class che_첩보 extends Command\GeneralCommand
{
    static protected $actionName = '첩보';
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

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['tech']);

        [$reqGold, $reqRice] = $this->getCost();

        $this->minConditionConstraints = [
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::NotBeNeutral(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID']);
        $this->setDestNation($this->destCity['nation'], ['tech']);

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints = [
            ConstraintHelper::NotOccupiedDestCity(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }

    public function getBrief(): string
    {
        $cityName = $this->destCity['name'];
        return "【{$cityName}】에 {$this->getName()} 실행";
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
        $title .= ')';
        return $title;
    }

    public function getCost(): array
    {
        $env = $this->env;
        return [$env['develcost']*3, $env['develcost']*3];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function getFailString(): string
    {
        $commandName = $this->getName();
        $failReason = $this->testFullConditionMet();
        if ($failReason === null) {
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        return "{$failReason} <G><b>{$destCityName}</b></>에 {$commandName} 실패.";
    }

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $nationID = $general->getNationID();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $destCity = $this->destCity;

        $destCityName = $destCity['name'];
        $destCityID = $destCity['city'];
        $destNationID = $destCity['nation'];
        $josaUl = JosaUtil::pick($destCityName, '을');

        $logger = $general->getLogger();

        $dist = searchDistance($general->getCityID(), 2, false)[$destCityID] ?? 3;

        $destCityGeneralList = $db->query('SELECT crew, crewtype FROM general WHERE city = %i AND nation = %i', $destCityID, $destNationID);
        $totalCrew = Util::arraySum($destCityGeneralList, 'crew');
        $totalGenCnt = count($destCityGeneralList);
        $byCrewType = Util::arrayGroupBy($destCityGeneralList, 'crewtype');

        $popText = number_format($destCity['pop']);
        $trustText = number_format($destCity['trust'], 1);
        $agriText = number_format($destCity['agri']);
        $commText = number_format($destCity['comm']);
        $secuText = number_format($destCity['secu']);
        $defText = number_format($destCity['def']);
        $wallText = number_format($destCity['wall']);

        $cityBrief = "【<G>{$destCityName}</>】주민:{$popText}, 민심:{$trustText}, 장수:{$totalGenCnt}, 병력:{$totalCrew}";
        $cityDevel = "【<M>첩보</>】농업:{$agriText}, 상업:{$commText}, 치안:{$secuText}, 수비:{$defText}, 성벽:{$wallText}";

        $logger->pushGlobalActionLog("누군가가 <G><b>{$destCityName}</b></>{$josaUl} 살피는 것 같습니다.");
        if ($dist <= 1) {
            $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>의 정보를 많이 얻었습니다. <1>$date</>");
            $logger->pushGeneralActionLog($cityBrief, ActionLogger::RAWTEXT);
            $logger->pushGeneralActionLog($cityDevel, ActionLogger::RAWTEXT);
            $logger->pushGeneralActionLog('【<S>병종</>】 ' . join(' ', Util::mapWithKey(function ($crewType, $value) {
                $crewTypeText = mb_substr(GameUnitConst::byID($crewType)->name, 0, 2);
                $cnt = count($value);
                return "{$crewTypeText}:{$cnt}";
            }, $byCrewType)), ActionLogger::RAWTEXT);

            if ($this->destNation['nation'] && $general->getNationID()) {
                $techDiff = floor($this->destNation['tech']) - floor($this->nation['tech']);
                if ($techDiff >= 1000) {
                    $techText = '<M>↑</>압도';
                } else if ($techDiff >= 250) {
                    $techText = '<Y>▲</>우위';
                } else if ($techDiff >= -250) {
                    $techText = '<W>↕</>대등';
                } else if ($techDiff >= -1000) {
                    $techText = '<G>▼</>열위';
                } else {
                    $techText = '<C>↓</>미미';
                }
                $logger->pushGeneralActionLog("【<span class='ev_notice'>{$this->destNation['name']}</span>】아국대비기술:{$techText}");
            }
        } else if ($dist == 2) {
            $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>의 정보를 어느 정도 얻었습니다. <1>$date</>");
            $logger->pushGeneralActionLog($cityBrief, ActionLogger::RAWTEXT);
            $logger->pushGeneralActionLog($cityDevel, ActionLogger::RAWTEXT);
        } else {
            $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>의 소문만 들을 수 있었습니다. <1>$date</>");
            $logger->pushGeneralActionLog($cityBrief, ActionLogger::RAWTEXT);
        }

        $rawSpy = $db->queryFirstField('SELECT spy FROM nation WHERE nation = %i', $nationID);
        $spyInfo = Json::decode($rawSpy)??[];
        $spyInfo[$destCityID] = 3;
        $db->update('nation', [
            'spy'=>Json::encode($spyInfo)
        ], 'nation=%i',$nationID);

        $exp = $rng->nextRangeInt(1, 100);
        $ded = $rng->nextRangeInt(1, 70);

        [$reqGold, $reqRice] = $this->getCost();
        $general->increaseInheritancePoint(InheritanceKey::active_action, 0.5);//NOTE: 첩보만 예외!
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        return [
            'procRes' => [
                'cities' => \sammo\JSOptionsForCities(),
                'distanceList' => \sammo\JSCitiesBasedOnDistance($this->generalObj->getCityID(), 3),
            ],
        ];
    }
}
