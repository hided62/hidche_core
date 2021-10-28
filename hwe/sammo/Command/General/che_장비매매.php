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

use function \sammo\buildItemClass;
use function sammo\tryUniqueItemLottery;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_장비매매 extends Command\GeneralCommand{
    static protected $actionName = '장비매매';
    static public $reqArg = true;

    static $itemMap = [
        'horse'=>'명마',
        'weapon'=>'무기',
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
        if(!key_exists($itemCode, GameConst::$allItems[$itemType]) && $itemCode !== 'None'){
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

        $this->minConditionConstraints=[
            ConstraintHelper::ReqCityTrader($general->getNPCType()),
        ];
    }

    protected function initWithArg()
    {
        $general = $this->generalObj;

        $itemType = $this->arg['itemType'];
        $itemTypeName = static::$itemMap[$itemType];
        $itemCode = $this->arg['itemCode'];
        $itemClass = buildItemClass($itemCode);

        [$reqGold, $reqRice] = $this->getCost();

        $this->fullConditionConstraints=[
            ConstraintHelper::ReqCityTrader($general->getNPCType()),
            ConstraintHelper::ReqCityCapacity('secu', '치안 수치', $itemClass->getReqSecu()),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

        if($itemCode === 'None'){
            $this->fullConditionConstraints[] = ConstraintHelper::ReqGeneralValue($itemType, $itemTypeName, '!=', 'None');
        }
        else if($itemCode == $general->getVar($itemType)){
            $this->fullConditionConstraints[] = ConstraintHelper::AlwaysFail('이미 가지고 있습니다.');
        }
        else if(!buildItemClass($general->getVar($itemType))->isBuyable()){
            $this->fullConditionConstraints[] = ConstraintHelper::AlwaysFail('이미 진귀한 것을 가지고 있습니다.');
        }
    }

    public function getCost():array{
        if(!$this->isArgValid){
            return [0, 0];
        }

        $itemCode = $this->arg['itemCode'];
        $itemObj = buildItemClass($itemCode);

        $reqGold = $itemObj->getCost();
        return [$reqGold, 0];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getBrief():string{
        $itemType = $this->arg['itemType'];
        $itemCode = $this->arg['itemCode'];

        if($itemCode === 'None'){
            $itemTypeName = static::$itemMap[$itemType];
            $josaUl = JosaUtil::pick($itemTypeName, '을');
            return "{$itemTypeName}{$josaUl} 판매";
        }


        $itemObj = buildItemClass($itemCode);
        $itemName = $itemObj->getName();
        $itemRawName = $itemObj->getRawName();
        $josaUl = JosaUtil::pick($itemRawName, '을');
        return "【{$itemName}】{$josaUl} 구입";
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $itemType = $this->arg['itemType'];
        $itemCode = $this->arg['itemCode'];

        if($itemCode === 'None'){
            $buying = false;
            $itemCode = $general->getVar($itemType);
        }
        else{
            $buying = true;
        }
        $itemObj = buildItemClass($itemCode);
        $cost = $itemObj->getCost();
        $itemName = $itemObj->getName();
        $itemRawName = $itemObj->getRawName();

        $josaUl = JosaUtil::pick($itemRawName, '을');

        $logger = $general->getLogger();

        if($buying){
            $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 구입했습니다. <1>$date</>");
            $general->increaseVarWithLimit('gold', -$cost, 0);
            $general->setVar($itemType, $itemCode);
        }
        else{
            $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 판매했습니다. <1>$date</>");
            $general->increaseVarWithLimit('gold', $cost / 2);
            $general->deleteItem($itemType);
        }

        $exp = 10;

        $general->addExperience($exp);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        $form = [];

        $db = DB::db();

        $citySecu = $db->queryFirstField('SELECT secu FROM city WHERE city = %i', $this->generalObj->getCityID());
        $gold = $this->generalObj->getVar('gold');

        ob_start();
?>
<script>
function updateItemType(){
    $('#itemType').val($('#itemCode option:selected').data('item_type'));
}
$(function(){
$('#customSubmit').click(function(){
    updateItemType();
    submitAction();
});
});
</script>
<input type="hidden" class="formInput" name="itemType" id="itemType" value="item">
장비를 구입하거나 매각합니다.<br>
현재 구입 불가능한 것은 <font color=red>붉은색</font>으로 표시됩니다.<br>
현재 도시 치안 : <?=$citySecu?> &nbsp;&nbsp;&nbsp;현재 자금 : <?=$gold?><br>
장비 : <select class='formInput' name="itemCode" id="itemCode" onchange='updateItemType();' size='1' style='color:white;background-color:black;'>
<?php foreach(GameConst::$allItems as $itemType=>$itemCategories):
    //매각
    $typeName = static::$itemMap[$itemType];
?>
    <option value='None' data-item_type='<?=$itemType?>' style='color:skyblue'>_____<?=$typeName?>매각(반값)____</option>
    <?php foreach($itemCategories as $itemCode=>$cnt) :
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
            if($reqSecu > $citySecu){
                $css = 'color:red;';
            }
?>
        <option value='<?=$itemCode?>' data-item_type='<?=$itemType?>' style='<?=$css?>'><?=$itemName?> 가격: <?=$reqGold?></option>
    <?php endforeach; ?>
<?php endforeach; ?>
</select>
<input type=button id="customSubmit" value="<?=$this->getName()?>"><br>
<?php
        return ob_get_clean();
    }
}