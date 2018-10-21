<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    uniqueItemEx
};

use \sammo\Constraint\Constraint;


class che_헌납 extends Command\GeneralCommand{
    static protected $actionName = '헌납';

    protected function argTest():bool{
        if(!key_exists('isGold', $this->arg)){
            return false;
        }
        if(!key_exists('amount', $this->arg)){
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        if(!is_int($amount)){
            return false;
        }
        $amount = Util::valueFit($amount, 100, 10000);
        if(!is_bool($isGold)){
            return false;
        }
        $this->arg = [
            'isGold'=>$isGold,
            'amount'=>$amount
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        $this->runnableConstraints=[
            ['NoNeutral'], 
            ['OccupiedCity'],
            ['SuppliedCity'],
        ];
        if($this->arg['isGold']){
            $this->runnableConstraints[] = ['ReqGeneralGold', 1];
        }
        else{
            $this->runnableConstraints[] = ['ReqGeneralRice', 1];
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
        $date = substr($general->getVar('turntime'),11,5);

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold?'gold':'rice';
        $resName = $isGold?'금':'쌀';

        $amount = Util::valueFit($amount, $general->getVar($resKey));
        $amountText = number_format($amount, 0);
        
        $logger = $general->getLogger();

        $db->update('nation', [
            $resKey=>$db->sqleval('%b + %i', $resKey, $amount)
        ], 'nation=%i', $general->getNationID());

        $general->increaseVarWithLimit($resKey, -$amount, 0);

        $logger->pushGeneralActionLog("{$resName} <C>$amountText</>을 헌납했습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;

        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('leader2', 1);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}