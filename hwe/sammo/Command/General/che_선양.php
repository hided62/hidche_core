<?php

namespace sammo\Command\General;

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
    Command
};

use function\sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic,
    CriticalScoreEx,
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_선양 extends Command\GeneralCommand
{
    static protected $actionName = '선양';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 '선양' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        if (!is_int($destGeneralID)) {
            return false;
        }
        if ($destGeneralID <= 0) {
            return false;
        }
        if ($destGeneralID == $this->generalObj->getID()) {
            return false;
        }
        $this->arg = [
            'destGeneralID' => $destGeneralID
        ];
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setNation();

        $this->minConditionConstraints = [
            ConstraintHelper::BeLord()
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'nation'], 1);
        $this->setDestGeneral($destGeneral);

        $this->fullConditionConstraints = [
            ConstraintHelper::BeLord(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral(),
        ];
    }

    public function getCost(): array
    {
        return [0, 0];
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
        $destGeneralName = $this->destGeneralObj->getName();
        $name = $this->getName();
        return "【{$destGeneralName}】에게 {$name}";
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $destGeneral = $this->destGeneralObj;

        $generalName = $general->getName();
        $destGeneralName = $destGeneral->getName();

        $nationName = $this->nation['name'];

        $logger = $general->getLogger();
        $destLogger = $destGeneral->getLogger();

        $destGeneral->setVar('officer_level', 12);
        $destGeneral->setVar('officer_city', 0);
        $general->setVar('officer_level', 1);
        $general->setVar('officer_city', 0);
        $general->multiplyVar('experience', 0.7);

        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalHistoryLog("<Y><b>【선양】</b></><Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>의 군주 자리를 <Y>{$destGeneralName}</>에게 선양했습니다.");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <Y>{$destGeneralName}</>에게 선양");

        $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>에게 군주의 자리를 물려줍니다. <1>$date</>");
        $destLogger->pushGeneralActionLog("<Y>{$generalName}</>에게서 군주의 자리를 물려받습니다.");

        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>의 군주자리를 <Y>{$destGeneralName}</>에게 선양");
        $destLogger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>의 군주자리를 물려 받음");

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        //TODO: 암행부처럼 보여야...
        $db = DB::db();

        $destRawGenerals = $db->query('SELECT no,name,officer_level,npc FROM general WHERE nation != 0 AND nation = %i AND no != %i ORDER BY npc,binary(name)', $this->generalObj->getNationID(), $this->generalObj->getID());
        ob_start();
?>
        군주의 자리를 다른 장수에게 물려줍니다.<br>
        장수를 선택하세요.<br>
        <select class='formInput' name="destGeneralID" id="destGeneralID" size='1' style='color:white;background-color:black;'>
            <?php foreach ($destRawGenerals as $destGeneral) :
                $color = \sammo\getNameColor($destGeneral['npc']);
                if ($color) {
                    $color = " style='color:{$color}'";
                }
                $name = $destGeneral['name'];
                if ($destGeneral['officer_level'] >= 5) {
                    $name = "*{$name}*";
                }
            ?>
                <option value='<?= $destGeneral['no'] ?>' <?= $color ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
<?php
        return ob_get_clean();
    }
}
