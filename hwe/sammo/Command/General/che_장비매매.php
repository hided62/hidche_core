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
    getItemCost,
    getItemInfo,
    getItemName
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_장비매매 extends Command\GeneralCommand{
    static protected $actionName = '장비매매';
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
        $itemType = $this->arg['itemType']??null;
        if(!in_array($itemType, array_keys(static::$itemMap))){
            return false;
        }
        $itemCode = $this->arg['itemCode']??null;
        if(!is_int($itemCode)){
            return false;
        }
        if($itemCode < 0 || 6 < $itemCode){
            return false;
        }
        $this->arg = [
            'itemType'=>$itemType,
            'itemCode'=>$itemCode
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $itemType = $this->arg['itemType'];
        $itemTypeName = static::$itemMap[$itemType];
        $itemCode = $this->arg['itemCode'];

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::ReqCityTrader($general->getVar('npc')),
            ConstraintHelper::ReqCityCapacity('secu', '치안 수치', $itemCode * 1000),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

        if($itemCode == 0){
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralValue($itemType, $itemTypeName, '>=', 1);
        }
        else if($itemCode == $general->getVar($itemType)){
            $this->runnableConstraints[] = ConstraintHelper::AlwaysFail('이미 가지고 있습니다.');
        }
        else if($itemType != 'item' && $general->getVar($itemType) > 6){
            $this->runnableConstraints[] = ConstraintHelper::AlwaysFail('이미 진귀한 것을 가지고 있습니다.');
        }

    }

    public function getCost():array{
        if(!$this->isArgValid){
            return [0, 0];
        }
        
        $itemType = $this->arg['itemType'];
        $itemCode = $this->arg['itemCode'];

        $reqGold = getItemCost($itemCode);
        return [$reqGold, 0];
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

        $itemType = $this->arg['itemType'];
        $itemCode = $this->arg['itemCode'];

        if($itemCode == 0){
            $buying = false;
            $itemCode = $general->getVar($itemType);
            $cost = getItemCost($itemCode);
        }
        else{
            $buying = true;
            $cost = $this->getCost()[0];
        }

        $itemName = getItemName($itemCode);

        $josaUl = JosaUtil::pick($itemName, '을');

        $logger = $general->getLogger();

        if($buying){
            $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 구입했습니다. <1>$date</>");
            $general->increaseVarWithLimit('gold', -$cost, 0);
            $general->setVar($itemType, $itemCode);
        }
        else{
            $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 판매했습니다. <1>$date</>");
            $general->increaseVarWithLimit('gold', $cost / 2);
            $general->setVar($itemType, 0);
        }

        $exp = 10;
        $exp = $general->onCalcStat($general, 'experience', $exp);

        $general->increaseVar('experience', $exp);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}