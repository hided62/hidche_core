<?php

namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

use function sammo\tryUniqueItemLottery;

class che_헌납 extends Command\GeneralCommand
{
    static protected $actionName = '헌납';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        if (!key_exists('isGold', $this->arg)) {
            return false;
        }
        if (!key_exists('amount', $this->arg)) {
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        if (!is_numeric($amount)) {
            return false;
        }
        $amount = Util::round($amount, -2);
        $amount = Util::valueFit($amount, 100, GameConst::$maxResourceActionAmount);
        if (!is_bool($isGold)) {
            return false;
        }
        $this->arg = [
            'isGold' => $isGold,
            'amount' => $amount
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
        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
        ];
        if ($this->arg['isGold']) {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralGold(GameConst::$generalMinimumGold);
        } else {
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralRice(GameConst::$generalMinimumRice);
        }
    }

    public function getBrief(): string
    {
        $resText = $this->arg['isGold'] ? '금' : '쌀';
        $name = $this->getName();
        return "{$resText} {$this->arg['amount']}을 {$name}";
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

        $amount = Util::valueFit($amount, 0, $general->getVar($resKey));
        $amountText = number_format($amount, 0);

        $logger = $general->getLogger();

        $db->update('nation', [
            $resKey => $db->sqleval('%b + %i', $resKey, $amount)
        ], 'nation=%i', $general->getNationID());

        $general->increaseVarWithLimit($resKey, -$amount, 0);

        $logger->pushGeneralActionLog("{$resName} <C>$amountText</>을 헌납했습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;

        $general->addExperience($exp);
        $general->addDedication($ded);
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
            'procRes' => [
                'minAmount' => 100,
                'maxAmount' => GameConst::$maxResourceActionAmount,
                'amountGuide' => GameConst::$resourceActionAmountGuide,
            ]
        ];
    }
}
