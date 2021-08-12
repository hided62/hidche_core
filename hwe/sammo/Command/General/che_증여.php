<?php

namespace sammo\Command\General;

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

use function \sammo\getDomesticExpLevelBonus;
use function \sammo\CriticalRatioDomestic;
use function \sammo\CriticalScoreEx;
use function \sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_증여 extends Command\GeneralCommand
{
    static protected $actionName = '증여';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 '증여' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
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
        if ($destGeneralID == $this->generalObj->getID()) {
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
        $this->setNation();

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'rice', 'nation'], 1);
        $this->setDestGeneral($destGeneral);

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
        ];
        if ($this->arg['isGold']) {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralGold(GameConst::$generalMinimumGold);
        } else {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralRice(GameConst::$generalMinimumRice);
        }
    }

    public function getCommandDetailTitle(): string
    {
        $name = $this->getName();
        return "{$name}(통솔경험)";
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
        $resText = $this->arg['isGold'] ? '금' : '쌀';
        $name = $this->getName();
        return "【{$destGeneralName}】에게 {$resText} {$this->arg['amount']}을 {$name}";
    }

    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold ? 'gold' : 'rice';
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;

        $amount = Util::valueFit($amount, 0, $general->getVar($resKey) - ($isGold ? GameConst::$generalMinimumGold : GameConst::$generalMinimumRice));
        $amountText = number_format($amount, 0);

        $logger = $general->getLogger();

        $destGeneral->increaseVarWithLimit($resKey, $amount);
        $general->increaseVarWithLimit($resKey, -$amount, 0);

        $destGeneral->getLogger()->pushGeneralActionLog("<Y>{$general->getName()}</>에게서 {$resName} <C>{$amountText}</>을 증여 받았습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("<Y>{$destGeneral->getName()}</>에게 {$resName} <C>$amountText</>을 증여했습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar('leadership_exp', 1);

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        //TODO: 암행부처럼 보여야...
        $db = DB::db();

        $destRawGenerals = $db->query('SELECT no,name,officer_level,npc,gold,rice FROM general WHERE nation != 0 AND nation = %i AND no != %i ORDER BY npc,binary(name)', $this->generalObj->getNationID(), $this->generalObj->getID());
        ob_start();
?>
        자신의 자금이나 군량을 다른 장수에게 증여합니다.<br>
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
                <option value='<?= $destGeneral['no'] ?>' <?= $color ?>><?= $name ?>(금:<?= $destGeneral['gold'] ?>, 쌀:<?= $destGeneral['rice'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <select class='formInput' name="isGold" id="isGold" size='1' style='color:white;background-color:black;'>
            <option value="true">금</option>
            <option value="false">쌀</option>
        </select>
        <select class='formInput' name="amount" id="amount" size='1' style='color:white;background-color:black;'>
            <?php foreach (GameConst::$resourceActionAmountGuide as $amount) : ?>
                <option value='<?= $amount ?>'><?= $amount ?></option>
            <?php endforeach; ?>
        </select> <input type=button id="commonSubmit" value="<?= $this->getName() ?>"><br>
<?php
        return ob_get_clean();
    }
}
