<?php

namespace sammo\Command\Nation;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\DummyGeneral;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;
use \sammo\MessageTarget;
use \sammo\Message;
use \sammo\CityConst;
use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\InheritanceKey;
use sammo\Event\Action;
use sammo\Json;

class che_무작위수도이전 extends Command\NationCommand
{
    static protected $actionName = '무작위 수도 이전';

    protected function argTest(): bool
    {
        $this->arg = [];

        return true;
    }

    protected function init()
    {
        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $this->setCity();
        $this->setNation(['capital', 'aux']);

        $this->fullConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeLord(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::BeOpeningPart($relYear + 1),
            ConstraintHelper::ReqNationAuxValue("can_무작위수도이전", 0, '>', 0, '더이상 변경이 불가능합니다.')
        ];
    }


    public function getCommandDetailTitle():string{
        $name = $this->getName();

        $reqTurn = $this->getPreReqTurn()+1;

        return "{$name}/{$reqTurn}턴";
    }


    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 1;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $year = $this->env['year'];
        $month = $this->env['month'];

        $logger = $general->getLogger();

        $oldCityID = $this->nation['capital'];

        $cities = $db->queryFirstColumn('SELECT city FROM city where `level`>=5 and `level`<=6 and nation=0');
        if (!$cities) {
            $logger->pushGeneralActionLog("이동할 수 있는 도시가 없습니다. <1>$date</>");
            return false;
        }
        $destCityID = $rng->choice($cities);
        $this->setDestCity($destCityID, true);


        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $josaRo = JosaUtil::pick($destCityName, '로');

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $aux = Json::decode($this->nation['aux']);
        $aux["can_무작위수도이전"] -= 1;

        $db->update('city', [
            'nation' => $nationID,
            'conflict' => '{}'
        ], 'city=%i', $destCityID);
        $db->update('nation', [
            'capital' => $destCityID,
            'aux'=>Json::encode($aux),
        ], 'nation=%i', $nationID);
        $db->update('city', [
            'nation' => 0,
            'front' => 0,
            'conflict' => '{}',
            'officer_set' => 0,
        ], 'city=%i', $oldCityID);

        $general->setVar('city', $destCityID);
        $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no!=%i', $general->getNationID(), $general->getID());
        if ($generalList) {
            $db->update('general', [
                'city' => $destCityID
            ], 'no IN %li', $generalList);
        }
        foreach ($generalList as $targetGeneralID) {
            $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $year, $month);
            $targetLogger->pushGeneralActionLog("국가 수도를 <G><b>{$destCityName}</b></>{$josaRo} 옮겼습니다.", ActionLogger::PLAIN);
            $targetLogger->flush();
        }

        \sammo\refreshNationStaticInfo();

        $general->increaseInheritancePoint(InheritanceKey::active_action, 1);
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 국가를 옮겼습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>{$josaRo} <M>무작위 수도 이전</>");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaRo} <M>무작위 수도 이전</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>{$josaRo} <M>수도 이전</>하였습니다.");
        $logger->pushGlobalHistoryLog("<S><b>【무작위 수도 이전】</b></><D><b>{$nationName}</b></>{$josaYiNation} <G><b>{$destCityName}</b></>{$josaRo} <M>수도 이전</>하였습니다.");

        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);
        return true;
    }
}
