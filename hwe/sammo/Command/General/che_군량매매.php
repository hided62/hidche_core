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


use function \sammo\{
    tryUniqueItemLottery,
    getItemCost, getItemCost2,
    getItemInfo,
    getHorseName, getWeapName, getBookName, getItemName
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\MustNotBeReachedException;

class che_군량매매 extends Command\GeneralCommand{
    static protected $actionName = '군량매매';
    static public $reqArg = true;

    static $itemMap = [
        'horse'=>'명마',
        'weap'=>'무기',
        'book'=>'서적',
        'item'=>'도구',
    ];

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        $buyRice = $this->arg['buyRice']??null;
        if(!is_bool($buyRice)){
            return false;
        }
        $amount = $this->arg['amount']??null;
        if(!is_int($amount)){
            return false;
        }
        $amount = Util::valueFit($amount, 100, 10000);
        
        $this->arg = [
            'buyRice'=>$buyRice,
            'amount'=>$amount
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();    
        
        $this->runnableConstraints=[
            ConstraintHelper::ReqCityTrader($general->getVar('npc')),
            ConstraintHelper::OccupiedCity(true),
            ConstraintHelper::SuppliedCity(),
        ];

        if($this->arg['buyRice']){
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralGold(1);
        }
        else{
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralRice(1);
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

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $tradeRate = $this->city['trade'];
        $date = substr($general->getVar('turntime'),11,5);

        $buyRice = $this->arg['buyRice'];

        if($tradeRate === null){
            if($general->getVar('npc') >= 2){
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
        $general->increaseVarWithLimit($sellKey, $sellAmount, 0);

        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $tax)
        ], 'nation=%i', $general->getNationID());

        $exp = 30;
        $ded = 50;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $incStat = Util::choiceRandomUsingWeight([
            'leader2'=>$general->getLeadership(false, false, false, false),
            'power2'=>$general->getPower(false, false, false, false),
            'intel2'=>$general->getIntel(false, false, false, false)
        ]);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar($incStat, 1);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}