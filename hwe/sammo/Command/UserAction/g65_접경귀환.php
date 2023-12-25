<?php

namespace sammo\Command\UserAction;

use sammo\CityConst;
use \sammo\Command;
use sammo\Constraint\ConstraintHelper;
use sammo\DB;
use sammo\JosaUtil;

use function sammo\searchDistance;

class g65_접경귀환 extends Command\UserActionCommand
{
    static protected $actionName = '접경귀환';

    protected function argTest(): bool
    {
        return true;
    }

    public function getBrief(): string
    {
        return '접경 귀환';
    }

    public function getCommandDetailTitle(): string
    {
        $postReqTurn = $this->getPostReqTurn();
        return "적군 도시 소재 시 접경으로 귀환(재사용 대기 {$postReqTurn})";
    }

    protected function init()
    {
        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::NotOccupiedCity()
        ];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 60;
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function run(\Sammo\RandUtil $rng): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $cityID = $general->getCityID();
        $logger = $general->getLogger();

        $distanceList = searchDistance($cityID, 3, true);

        $occupiedCityList = new \Ds\Set($db->queryFirstColumn(
            'SELECT city FROM city WHERE nation = %i AND city IN %li AND supply = 1',
            $general->getNationID(),
            array_merge(...$distanceList)
        ));

        $nearestCityList = [];
        foreach ($distanceList as $cityList) {
            foreach ($cityList as $cityID) {
                if ($occupiedCityList->contains($cityID)) {
                    $nearestCityList[] = $cityID;
                }
            }
            if ($nearestCityList) {
                break;
            }
        }

        if (!$nearestCityList) {
            $logger->pushGeneralActionLog("3칸 이내에 아국 도시가 없습니다.");
            return false;
        }

        $destCityID = $rng->choice($nearestCityList);
        $destCityName = CityConst::byID($destCityID)->name;

        $josaRo = JosaUtil::pick($destCityName, '로');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 접경귀환했습니다.");
        $general->setVar('city', $destCityID);

        $general->applyDB($db);
        return true;
    }
}
