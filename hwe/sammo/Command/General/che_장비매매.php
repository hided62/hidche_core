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
    getItemName,
    buildItemClass
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
        if(!key_exists($itemCode, GameConst::$allItems[$itemType])){
            return false;
        }
        $itemClass = buildItemClass($itemCode);
        if(!$itemClass->isBuyable()){
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
        $itemClass = buildItemClass($itemCode);

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::ReqCityTrader($general->getVar('npc')),
            ConstraintHelper::ReqCityCapacity('secu', '치안 수치', $itemClass->getReqSecu()),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

        if($itemCode == 0){
            $this->runnableConstraints[] = ConstraintHelper::ReqGeneralValue($itemType, $itemTypeName, '>=', 1);
        }
        else if($itemCode == $general->getVar($itemType)){
            $this->runnableConstraints[] = ConstraintHelper::AlwaysFail('이미 가지고 있습니다.');
        }
        else if($itemType != 'item' && !buildItemClass($general->getVar($itemType))->isBuyable()){
            $this->runnableConstraints[] = ConstraintHelper::AlwaysFail('이미 진귀한 것을 가지고 있습니다.');
        }

    }

    public function getCost():array{
        if(!$this->isArgValid){
            return [0, 0];
        }
        
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

        if($itemCode == 'None'){
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

    public function getForm(): string
    {
        $form = [];

        $db = DB::db();

        $citySecu = $db->queryFirstField('SELECT secu FROM city WHERE city = %i', $this->generalObj->getCityID());
        $gold = $this->generalObj->getVar('gold');
        $form[] = <<<EOT
<script>
function updateItemType(elem){
    $('#itemType').val($(elem).data('itemType'));
}
</script>
EOT;
        $form[] = '<input type="hidden" class="formInput" name="itemType" id="itemType" value="item">';
        $form[] = <<<EOT
장비를 구입하거나 매각합니다.<br>
현재 구입 불가능한 것은 <font color=red>붉은색</font>으로 표시됩니다.<br>
현재 도시 치안 : {$citySecu} &nbsp;&nbsp;&nbsp;현재 자금 : {$gold}<br>
장비 : <select class='formInput' name="itemCode" id="itemCode" size='1' style='color:white;background-color:black;'>
EOT;
        foreach(GameConst::$allItems as $itemType=>$itemCategories){
            //매각
            $typeName = static::$itemMap[$itemType];
            $form[] = "<option value='None' data-itemType='{$itemType}' onclick='updateItemType(this);' style='color:skyblue'>_____{$typeName}매각(반값)____</option>";

            //구입
            foreach($itemCategories as $itemCode=>$cnt){
                if($cnt > 0){
                    continue;
                }
                $itemClass = buildItemClass($itemCode);
                if(!$itemClass->isBuyable()){
                    continue;
                }
                $itemName = $itemClass->getName();
                $reqSecu = $itemClass->getReqSecu();
                $reqGold = $itemClass->getCost();
                $css = '';
                if($reqSecu < $citySecu){
                    $css = 'color:red;';
                }
                $form[] = "<option value='{$itemCode}' data-itemType='{$itemType}' style='{$css}'>{$itemName} 가격: {$reqGold}</option>";
            }
        }
        $form[] = <<<EOT
</select>
<input type=submit value=거래>
EOT;
        
        return join("\n",$form);
    }
}