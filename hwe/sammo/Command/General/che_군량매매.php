<?php
namespace sammo\Command\General;

//TODO: 아이템 클래스 재확정 후 재 구현!

use \sammo\{
    DB, Util, JosaUtil,
    General,
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\MustNotBeReachedException;

use function sammo\tryUniqueItemLottery;

class che_군량매매 extends Command\GeneralCommand{
    static protected $actionName = '군량매매';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        $buyRice = $this->arg['buyRice']??null;
        if(!is_bool($buyRice)){
            return false;
        }
        $buyRice = boolval($buyRice);
        $amount = $this->arg['amount']??null;
        if(!is_numeric($amount)){
            return false;
        }
        $amount = Util::round($amount, -2);
        $amount = Util::valueFit($amount, 100, GameConst::$maxResourceActionAmount);
        if($amount <= 0){
            return false;
        }
        $this->arg = [
            'buyRice'=>$buyRice,
            'amount'=>$amount
        ];
        return true;
    }

    public function getBrief(): string
    {
        $buyRiceText = $this->arg['buyRice']?'구입':'판매';
        return "군량 {$this->arg['amount']}을 {$buyRiceText}";
    }

    protected function init()
    {

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints=[
            ConstraintHelper::ReqCityTrader($general->getNPCType()),
            ConstraintHelper::OccupiedCity(true),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $general = $this->generalObj;

        $this->fullConditionConstraints=[
            ConstraintHelper::ReqCityTrader($general->getNPCType()),
            ConstraintHelper::OccupiedCity(true),
            ConstraintHelper::SuppliedCity(),
        ];

        if($this->arg['buyRice']){
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralGold(1);
        }
        else{
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralRice(1);
        }
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $tradeRate = $this->city['trade'];
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $buyRice = $this->arg['buyRice'];

        if($tradeRate === null){
            if($general->getNPCType() >= 2){
                $tradeRate = 1.0;
            }
            else{
                throw new MustNotBeReachedException();
            }
        }
        else{
            $tradeRate /= 100;
        }

        if($buyRice){
            $buyKey = 'rice';
            $sellKey = 'gold';
            $sellAmount = Util::valueFit($this->arg['amount'] * $tradeRate, null, $general->getVar('gold'));
            $tax = $sellAmount * GameConst::$exchangeFee;
            if($sellAmount + $tax > $general->getVar('gold')){
                $sellAmount *= $general->getVar('gold') / ($sellAmount + $tax);
                $tax = $general->getVar('gold') - $sellAmount;
            }
            $buyAmount = $sellAmount / $tradeRate;
            $sellAmount += $tax;
        }
        else{
            $buyKey = 'gold';
            $sellKey = 'rice';
            $sellAmount = Util::valueFit($this->arg['amount'], null, $general->getVar('rice'));
            $buyAmount = $sellAmount * $tradeRate;
            $tax = $buyAmount * GameConst::$exchangeFee;
            $buyAmount -= $tax;
        }

        $logger = $general->getLogger();

        $buyAmountText = number_format($buyAmount);
        $sellAmountText = number_format($sellAmount);

        if($buyRice){
            $logger->pushGeneralActionLog("군량 <C>{$buyAmountText}</>을 사서 자금 <C>{$sellAmountText}</>을 썼습니다. <1>$date</>");
        }
        else{
            $logger->pushGeneralActionLog("군량 <C>{$sellAmountText}</>을 팔아 자금 <C>{$buyAmountText}</>을 얻었습니다. <1>$date</>");
        }

        $general->increaseVar($buyKey, $buyAmount);
        $general->increaseVarWithLimit($sellKey, -$sellAmount, 0);

        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $tax)
        ], 'nation=%i', $general->getNationID());

        $exp = 30;
        $ded = 50;

        $incStat = $rng->choiceUsingWeight([
            'leadership_exp'=>$general->getLeadership(false, false, false, false),
            'strength_exp'=>$general->getStrength(false, false, false, false),
            'intel_exp'=>$general->getIntel(false, false, false, false)
        ]);

        $general->addExperience($exp);
        $general->addDedication($ded);
        $general->increaseVar($incStat, 1);

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);

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