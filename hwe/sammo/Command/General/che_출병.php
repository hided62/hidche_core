<?php

namespace sammo\Command\General;

use\sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    ActionLogger,
    GameConst,
    GameUnitConst,
    LastTurn,
    Command
};


use function\sammo\{
    tryUniqueItemLottery,
    processWar
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_출병 extends Command\GeneralCommand
{
    static protected $actionName = '출병';
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
        $this->setNation(['war', 'gennum', 'tech', 'gold', 'rice',  'color', 'type', 'level', 'capital']);

        [$reqGold, $reqRice] = $this->getCost();
        $relYear = $this->env['year'] - $this->env['startyear'];

        $this->minConditionConstraints = [
            ConstraintHelper::NotOpeningPart($relYear+2),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralCrew(),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

        
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID']);

        [$reqGold, $reqRice] = $this->getCost();
        $relYear = $this->env['year'] - $this->env['startyear'];

        $this->fullConditionConstraints = [
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::NotSameDestCity(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::ReqGeneralCrew(),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::AllowWar(),
            ConstraintHelper::HasRouteWithEnemy(),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        //[$reqGold, $reqRice] = $this->getCost();

        return "{$name}(통솔경험, 병종숙련, 군량↓)";
    }

    public function getCost(): array
    {
        return [0, Util::round($this->generalObj->getVar('crew') / 100)];
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
        $attackerNationID = $general->getNationID();
        $defenderNationID = $this->destCity['nation'];
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $attackerCityID = $general->getCityID();


        $finalTargetCityID = $this->destCity['city'];
        $finalTargetCityName = $this->destCity['name'];


        $logger = $general->getLogger();

        $allowedNationList = $db->queryFirstColumn('SELECT you FROM diplomacy WHERE state = 0 AND me = %i', $attackerNationID);
        $allowedNationList[] = $attackerNationID;
        $allowedNationList[] = 0;

        $distanceList = \sammo\searchDistanceListToDest($attackerCityID, $finalTargetCityID, $allowedNationList);

        $candidateCities = [];

        $minDist = Util::array_first_key($distanceList);
        do {
            //1: 최단 거리 도시 중 공격 대상이 있는가 확인
            //2: 최단 거리 + 1 도시 중 공격 대상이 있는가 확인
            foreach ($distanceList as $dist => $distCitiesInfo) {
                if ($dist > $minDist + 1) {
                    break;
                }
                $currDist = $dist;
                foreach ($distCitiesInfo as [$distCityID, $distCityNation]) {
                    if ($distCityNation !== $attackerNationID) {
                        $candidateCities[] = $distCityID;
                    }
                }

                if ($candidateCities) {
                    break 2;
                }
            }

            //3: 최단 거리 도시 중 아군 도시 선택
            foreach ($distanceList[$minDist] as [$distCityID, $distCityNation]) {
                if ($distCityNation === $attackerNationID) {
                    $candidateCities[] = $distCityID;
                }
            }
        } while (false);

        $defenderCityID = (int) Util::choiceRandom($candidateCities);
        $this->setDestCity($defenderCityID);
        $defenderCityName = $this->destCity['name'];
        $josaRo = JosaUtil::pick($defenderCityName, '로');
        $defenderNationID = $this->destCity['nation'];

        if ($attackerNationID == $defenderNationID) {
            $defenderCityName = $this->destCity['name'];
            $josaRo = JosaUtil::pick($defenderCityName, '로');
            if($this->arg['destCityID'] == $defenderCityID){
                $logger->pushGeneralActionLog("본국입니다. <G><b>{$defenderCityName}</b></>{$josaRo} 이동합니다. <1>$date</>");
            }
            else{
                $logger->pushGeneralActionLog("가까운 경로에 적군 도시가 없습니다. <G><b>{$defenderCityName}</b></>{$josaRo} 이동합니다. <1>$date</>");
            }
            
            $this->alternative = new che_이동($general, $this->env, ['destCityID' => $defenderCityID]);
            return false;
        }

        if ($finalTargetCityID !== $defenderCityID) {
            $josaRo = JosaUtil::pick($finalTargetCityName, '로');
            $josaUl = JosaUtil::pick($defenderCityName, '을');
            if ($minDist == $currDist) {
                $logger->pushGeneralActionLog("<G><b>{$finalTargetCityName}</b></>{$josaRo} 가기 위해 <G><b>{$defenderCityName}</b></>{$josaUl} 거쳐야 합니다. <1>$date</>");
            } else {
                $logger->pushGeneralActionLog("<G><b>{$finalTargetCityName}</b></>{$josaRo} 가는 도중 <G><b>{$defenderCityName}</b></>{$josaUl} 거치기로 합니다. <1>$date</>");
            }
        }

        $db->update('city', [
            'state' => 43,
            'term' => 3
        ], 'city=%i', $defenderCityID);

        $this->destCity['state'] = 43;
        $this->destCity['term'] = 3;

        $general->addDex($general->getCrewTypeObj(), $general->getVar('crew') / 100);


        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        processWar($general, $this->nation, $this->destCity);

        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    public function getJSFiles(): array
    {
        return [
            'js/defaultSelectCityByMap.js'
        ];
    }

    public function getForm(): string
    {
        $srcCityName = \sammo\CityConst::byID($this->generalObj->getCityID())->name;
        ob_start();
?>
        <?= \sammo\getMapHtml() ?><br>
        선택된 도시를 향해 침공을 합니다.<br>
        침공 경로에 적군의 도시가 있다면 전투를 벌입니다.<br>
        목록을 선택하거나 도시를 클릭하세요.<br>
        <?= $srcCityName ?> =><select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
            <?= \sammo\optionsForCities() ?><br>
        </select> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
        <br>
<?php
        return ob_get_clean();
    }
}
