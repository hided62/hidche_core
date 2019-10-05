<?php
namespace sammo\Command\General;

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
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_헌납 extends Command\GeneralCommand{
    static protected $actionName = '헌납';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
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
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
        ];
        if($this->arg['isGold']){
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralGold(GameConst::$generalMinimumGold);
        }
        else{
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralRice(GameConst::$generalMinimumRice);
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
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold?'gold':'rice';
        $resName = $isGold?'금':'쌀';

        $amount = Util::valueFit($amount, 0, $general->getVar($resKey));
        $amountText = number_format($amount, 0);
        
        $logger = $general->getLogger();

        $db->update('nation', [
            $resKey=>$db->sqleval('%b + %i', $resKey, $amount)
        ], 'nation=%i', $general->getNationID());

        $general->increaseVarWithLimit($resKey, -$amount, 0);

        $logger->pushGeneralActionLog("{$resName} <C>$amountText</>을 헌납했습니다. <1>$date</>");

        $exp = 70;
        $ded = 100;

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar('leadership2', 1);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        //TODO: 암행부처럼 보여야...
        $form = [];

        $form[] = <<<EOT
        자신의 자금이나 군량을 국가 재산으로 헌납합니다.<br>
<select class='formInput' name="isGold" id="isGold" size='1' style='color:white;background-color:black;'>
    <option value="true">금</option>
    <option value="false">쌀</option>
</select>
</select>
<select class='formInput' name="amount" id="amount" size='1' style='color:white;background-color:black;'>
    <option value=1>100</option>
    <option value=2>200</option>
    <option value=3>300</option>
    <option value=4>400</option>
    <option value=5>500</option>
    <option value=6>600</option>
    <option value=7>700</option>
    <option value=8>800</option>
    <option value=9>900</option>
    <option value=10>1000</option>
    <option value=12>1200</option>
    <option value=15>1500</option>
    <option value=20>2000</option>
    <option value=25>2500</option>
    <option value=30>3000</option>
    <option value=40>4000</option>
    <option value=50>5000</option>
    <option value=60>6000</option>
    <option value=70>7000</option>
    <option value=80>8000</option>
    <option value=90>9000</option>
    <option value=100>10000</option>
</select>
<input type="submit" value="헌납">
EOT;
        return join("\n",$form);
    }
}