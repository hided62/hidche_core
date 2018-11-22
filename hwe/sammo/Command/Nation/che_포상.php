<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_포상 extends Command\NationCommand{
    static protected $actionName = '포상';
    static public $reqArg = true;

    protected function argTest():bool{
        //NOTE: 사망 직전에 '포상' 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if(!key_exists('isGold', $this->arg)){
            return false;
        }
        if(!key_exists('amount', $this->arg)){
            return false;
        }
        if(!key_exists('destGeneralID', $this->arg)){
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $destGeneralID = $this->arg['destGeneralID'];
        if(!is_int($amount)){
            return false;
        }
        $amount = Util::valueFit($amount, 100, 10000);
        if(!is_bool($isGold)){
            return false;
        }
        if(!is_int($destGeneralID)){
            return false;
        }
        if($destGeneralID <= 0){
            return false;
        }
        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }
        $this->arg = [
            'isGold'=>$isGold,
            'amount'=>$amount,
            'destGeneralID'=>$destGeneralID
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['gold', 'rice']);

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'nation'], 1);
        $this->setDestGeneral($destGeneral);
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
        ];
        if($this->arg['isGold']){
            $this->runnableConstraints[] = ConstraintHelper::ReqNationGold(1);
        }
        else{
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralRice(1+GameConst::$baserice);
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

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold?'gold':'rice';
        $resName = $isGold?'금':'쌀';
        $destGeneral = $this->destGeneralObj;
        
        $amount = Util::valueFit($amount, 0, $nation[$resKey]);
        $amountText = number_format($amount, 0);
        
        $logger = $general->getLogger();

        $destGeneral->increaseVarWithLimit($resKey, $amount);
        $db->update('nation', [
            $resKey=>$db->sqleval('%b - %i', $resKey, $amount)
        ], 'nation=%i', $nationID);

        $destGeneral->getLogger()->pushGeneralActionLog("{$resName} <C>{$amountText}</>을 포상으로 받았습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("{$destGeneral->getName()}에게 {$resName} <C>$amountText</>을 수여했습니다. <1>$date</>");

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }
}