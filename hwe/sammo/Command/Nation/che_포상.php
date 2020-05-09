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
    Command
};

use function\sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic,
    CriticalScoreEx
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_포상 extends Command\NationCommand
{
    static protected $actionName = '포상';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 '포상' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('isGold', $this->arg)) {
            return false;
        }
        if (!key_exists('amount', $this->arg)) {
            return false;
        }
        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $destGeneralID = $this->arg['destGeneralID'];
        if (!is_numeric($amount)) {
            return false;
        }
        $amount = Util::round($amount, -2);
        $amount = Util::valueFit($amount, 100, GameConst::$maxResourceActionAmount);
        if ($amount <= 0) {
            return false;
        }
        if (!is_bool($isGold)) {
            return false;
        }
        if (!is_int($destGeneralID)) {
            return false;
        }
        if ($destGeneralID <= 0) {
            return false;
        }
        $this->arg = [
            'isGold' => $isGold,
            'amount' => $amount,
            'destGeneralID' => $destGeneralID
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['gold', 'rice']);

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'rice', 'nation'], 1);
        $this->setDestGeneral($destGeneral);

        if($this->arg['destGeneralID'] == $this->getGeneral()->getID()){
            $this->fullConditionConstraints=[
                ConstraintHelper::AlwaysFail('본인입니다')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
        ];
        if ($this->arg['isGold']) {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqNationGold(1 + GameConst::$basegold);
        } else {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqNationRice(1 + GameConst::$baserice);
        }
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
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $amountText = number_format($amount, 0);
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;
        $commandName = $this->getName();
        return "【{$destGeneral->getName()}】 {$resName} $amountText {$commandName}";
    }


    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold ? 'gold' : 'rice';
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;

        $amount = Util::valueFit(
            $amount,
            0,
            $nation[$resKey] - ($isGold ? GameConst::$basegold : GameConst::$baserice)
        );
        $amountText = number_format($amount, 0);

        $logger = $general->getLogger();

        $destGeneral->increaseVar($resKey, $amount);
        $db->update('nation', [
            $resKey => $db->sqleval('%b - %i', $resKey, $amount)
        ], 'nation=%i', $nationID);

        $destGeneral->getLogger()->pushGeneralActionLog("{$resName} <C>{$amountText}</>을 포상으로 받았습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("<Y>{$destGeneral->getName()}</>에게 {$resName} <C>$amountText</>을 수여했습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }


    public function getForm(): string
    {
        //TODO: 암행부처럼 보여야...
        $db = DB::db();

        $destRawGenerals = $db->query('SELECT no,name,officer_level,npc,gold,rice FROM general WHERE nation = %i ORDER BY npc,binary(name)', $this->generalObj->getNationID());
        $destGeneralList = [];
        foreach ($destRawGenerals as $destGeneral) {
            $nameColor = \sammo\getNameColor($destGeneral['npc']);
            if ($nameColor) {
                $nameColor = " style='color:{$nameColor}'";
            }

            $name = $destGeneral['name'];
            if ($destGeneral['officer_level'] >= 5) {
                $name = "*{$name}*";
            }

            $destGeneralList[] = [
                'no' => $destGeneral['no'],
                'color' => $nameColor,
                'name' => $name,
                'gold' => $destGeneral['gold'],
                'rice' => $destGeneral['rice']
            ];
        }
        ob_start();
?>
        국고로 장수에게 자금이나 군량을 지급합니다.<br>
        <select class='formInput' name="destGeneralID" id="destGeneralID" size='1' style='color:white;background-color:black;'>
            <?php foreach ($destGeneralList as $destGeneral) : ?>
                <option value='<?= $destGeneral['no'] ?>' <?= $destGeneral['color'] ?>><?= $destGeneral['name'] ?>(금:<?= $destGeneral['gold'] ?>, 쌀:<?= $destGeneral['rice'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <select class='formInput' name="isGold" id="isGold" size='1' style='color:white;background-color:black;'>
            <option value="true">금</option>
            <option value="false">쌀</option>
        </select>
        </select>
        <select class='formInput' name="amount" id="amount" size='1' style='color:white;background-color:black;'>
            <?php foreach (GameConst::$resourceActionAmountGuide as $amount) : ?>
                <option value='<?= $amount ?>'><?= $amount ?></option>
            <?php endforeach; ?>
        </select> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
        <br>
<?php
        return ob_get_clean();
    }
}
