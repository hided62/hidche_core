<?php

namespace sammo\Command\Nation;

use\sammo\{
    DB,
    Util,
    JosaUtil,
    General,
    DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    Message,
    CityConst
};

use function\sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic,
    CriticalScoreEx,
    GetImageURL,
    getNationStaticInfo
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Event\Action;

class che_허보 extends Command\NationCommand
{
    static protected $actionName = '허보';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        if (!key_exists('destCityID', $this->arg)) {
            return false;
        }
        if (CityConst::byID($this->arg['destCityID']) === null) {
            return false;
        }
        $destCityID = $this->arg['destCityID'];

        $this->arg = [
            'destCityID' => $destCityID,
        ];

        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['strategic_cmd_limit']);
        
        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::AvailableStrategicCommand(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID']);
        $this->setDestNation($this->destCity['nation']);

        $this->fullConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotNeutralDestCity(),
            ConstraintHelper::NotOccupiedDestCity(),
            ConstraintHelper::AllowDiplomacyBetweenStatus(
                [0, 1],
                '선포, 전쟁중인 상대국에게만 가능합니다.'
            ),
            ConstraintHelper::AvailableStrategicCommand(),
        ];
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        $reqTurn = $this->getPreReqTurn() + 1;
        $postReqTurn = $this->getPostReqTurn();

        return "{$name}/{$reqTurn}턴(재사용 대기 $postReqTurn)";
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
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount * 4) * 10);

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        return $nextTerm;
    }

    public function getBrief(): string
    {
        $commandName = $this->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        return "【{$destCityName}】에 {$commandName}";
    }

    public function run(): bool
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

        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $destNationID = $destCity['nation'];
        $destNationName = getNationStaticInfo($destNationID)['name'];

        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("허보 발동! <1>$date</>");

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $josaYi = JosaUtil::pick($generalName, '이');

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>에 <M>허보</>를 발동하였습니다.";

        $targetGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach ($targetGeneralList as $targetGeneralID) {
            $targetLogger = new ActionLogger($targetGeneralID, $nationID, $year, $month);
            $targetLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $targetLogger->flush();
        }

        $destBroadcastMessage = "상대의 <M>허보</>에 당했다! <1>$date</>";
        $destNationCityList = $db->queryFirstColumn('SELECT city FROM city WHERE nation = %i AND supply = 1', $destNationID);

        $targetGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND city=%i', $destNationID, $destCityID);
        foreach (General::createGeneralObjListFromDB($targetGeneralList) as $targetGeneralID => $targetGeneral) {
            $targetLogger = $targetGeneral->getLogger();
            $targetLogger->pushGeneralActionLog($destBroadcastMessage, ActionLogger::PLAIN);

            $moveCityID = Util::choiceRandom($destNationCityList);
            if ($moveCityID == $destCityID) {
                //현재도시면 다시 랜덤 추첨
                $moveCityID = Util::choiceRandom($destNationCityList);
            }

            $targetGeneral->setVar('city', $moveCityID);
            $targetGeneral->applyDB($db);
        }

        $destNationLogger = new ActionLogger(0, $destNationID, $year, $month);
        $destNationLogger->pushNationalHistoryLog(
            "<D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} 아국의 <G><b>{$destCityName}</b></>에 <M>허보</>를 발동",
            ActionLogger::PLAIN
        );
        $destNationLogger->flush();


        $db->update('city', [
            'def' => $db->sqleval('def * 0.2'),
            'wall' => $db->sqleval('wall * 0.2'),
        ], 'city=%i', $destCityID);

        $josaYiNation = JosaUtil::pick($nationName, '이');

        $logger->pushGeneralHistoryLog("<G><b>{$destCityName}</b></>에 <M>허보</>를 발동");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$destCityName}</b></>에 <M>허보</>를 발동");

        $db->update('nation', [
            'strategic_cmd_limit' => $this->generalObj->onCalcStrategic($this->getName(), 'globalDelay', 9)
        ], 'nation=%i', $nationID);

        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
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
        ob_start();
?>
        <?= \sammo\getMapHtml() ?><br>
        선택된 도시에 허보를 발동합니다.<br>
        전쟁중인 상대국 도시만 가능합니다.<br>
        목록을 선택하거나 도시를 클릭하세요.<br>
        <select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
            <?= \sammo\optionsForCities() ?><br>
        </select> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
        <br>
<?php
        return ob_get_clean();
    }
}
