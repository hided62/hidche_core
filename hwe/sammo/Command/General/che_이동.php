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

use function \sammo\printCitiesBasedOnDistance;
use function sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_이동 extends Command\GeneralCommand
{
    static protected $actionName = '이동';
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
        $this->setNation();


        [$reqGold, $reqRice] = $this->getCost();

        $this->minConditionConstraints = [
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestCity($this->arg['destCityID'], true);

        [$reqGold, $reqRice] = $this->getCost();
        $this->fullConditionConstraints = [
            ConstraintHelper::NotSameDestCity(),
            ConstraintHelper::NearCity(1),
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
        $title .= ', 사기↓)';
        return $title;
    }

    public function getCost(): array
    {
        $env = $this->env;
        return [$env['develcost'], 0];
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

        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 이동했습니다. <1>$date</>");

        $exp = 50;

        $general->setVar('city', $destCityID);

        if ($general->getVar('officer_level') == 12 && $this->nation['level'] == 0) {
            $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no!=%i', $general->getNationID(), $general->getID());
            if ($generalList) {
                $db->update('general', [
                    'city' => $destCityID
                ], 'no IN %li', $generalList);
            }
            foreach ($generalList as $targetGeneralID) {
                $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $env['year'], $env['month']);
                $targetLogger->pushGeneralActionLog("방랑군 세력이 <G><b>{$destCityName}</b></>{$josaRo} 이동했습니다.", ActionLogger::PLAIN);
                $targetLogger->flush();
            }
        }

        [$reqGold, $reqRice] = $this->getCost();
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('atmos', -5, 20);
        $general->addExperience($exp);
        $general->increaseVar('leadership_exp', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);

        $general->applyDB($db);

        return true;
    }

    public function getJSPlugins(): array
    {
        return [
            'defaultSelectCityByMap'
        ];
    }

    public function exportJSVars(): array
    {
        return [
            'cities' => \sammo\JSOptionsForCities()
        ];
    }

    public function getForm(): string
    {
        $currentCityID = $this->generalObj->getCityID();
        $currentCityName = CityConst::byID($currentCityID)->name;

        ob_start();
?>
        <?= \sammo\getMapHtml() ?><br>
        선택된 도시로 이동합니다.<br>
        인접 도시로만 이동이 가능합니다.<br>
        목록을 선택하거나 도시를 클릭하세요.<br>
        <?= $currentCityName ?> => <select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'><br>
            <?= \sammo\optionsForCities() ?><br>
        </select> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
        <br>
        <br>
        <?= printCitiesBasedOnDistance($currentCityID, 1) ?>
<?php
        return ob_get_clean();
    }
}
