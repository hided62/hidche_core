<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    StringUtil
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    getAllNationStaticInfo,
    getNationStaticInfo
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_물자원조 extends Command\NationCommand{
    static protected $actionName = '원조';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }

        if(!key_exists('destNationID', $this->arg)){
            return false;
        }
        $destNationID = $this->arg['destNationID'];
        if(!is_int($destNationID)){
            return false;
        }
        if($destNationID < 1){
            return false;
        }

        if(!key_exists('amountList', $this->arg)){
            return false;
        }
        $amountList = $this->arg['amountList'];
        if(!is_array($amountList)){
            return false;
        }

        if(count($amountList) != 2){
            return false;
        }

        [$goldAmount, $riceAmount] = $amountList;

        if(!is_int($goldAmount) || !is_int($riceAmount)){
            return false;
        }
        if($goldAmount < 0 || $riceAmount < 0){
            return false;
        }
        if($goldAmount == 0 && $riceAmount == 0){
            return false;
        }

        $this->arg = [
            'destNationID'=>$destNationID,
            'amountList'=>[$goldAmount, $riceAmount]
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation(['gold', 'rice', 'surlimit']);

        $destNationID = $this->arg['destNationID'];
        $this->setDestNation($destNationID, ['gold', 'rice', 'surlimit']);

        [$goldAmount, $riceAmount] = $this->arg['amountList'];
        $limit = $this->nation['level'] * GameConst::$coefAidAmount;

        if($goldAmount > $limit || $riceAmount > $limit){
            $this->runnableConstraints[
                ConstraintHelper::AlwaysFail('작위 제한량 이상은 보낼 수 없습니다.')
            ];
            return;
        }
        
        $this->runnableConstraints=[
            ConstraintHelper::ExistsDestNation(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ReqNationGold(GameConst::$basegold+(($goldAmount>0)?1:0)),
            ConstraintHelper::ReqNationRice(GameConst::$baserice+(($riceAmount>0)?1:0)),
            ConstraintHelper::ReqNationValue('surlimit', '외교제한', '==', 0, '외교제한중입니다.'),
            ConstraintHelper::ReqDestNationValue('surlimit', '외교제한', '==', 0, '상대국이 외교제한중입니다.'),
        ];
    }
    
    public function getCost():array{
        return [0, 0];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 12;
    }

    public function getBrief():string{
        [$goldAmount, $riceAmount] = $this->arg['amountList'];
        $goldAmountText = number_format($goldAmount);
        $riceAmountText = number_format($riceAmount);
        $destNationName = $this->destNation['name'];
        $commandName = $this->getName();
        return "【{$destNationName}】에게 국고 {$goldAmountText} 병량 {$riceAmountText} {$commandName}";
    }


    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalID = $general->getID();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNationID = $this->destNation['nation'];
        $destNationName = $this->destNation['name'];


        [$goldAmount, $riceAmount] = $this->arg['amountList'];

        
        $goldAmount = Util::valueFit(
            $goldAmount, 
            0, 
            $nation['gold'] - GameConst::$basegold
        );

        $riceAmount = Util::valueFit(
            $riceAmount, 
            0, 
            $nation['rice'] - GameConst::$baserice
        );
        
        $goldAmountText = number_format($goldAmount);
        $riceAmountText = number_format($riceAmount);

        
        $logger = $general->getLogger();

        $year = $this->env['year'];
        $month = $this->env['month'];

        

        $josaRo = JosaUtil::pick($destNationName, '로');

        

        $broadcastMessage = "<D><b>{$destNationName}</b></>{$josaRo} 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>을 지원했습니다.";

        $chiefList = $db->queryFirstColumn('SELECT no FROM general WHERE officer_level >= 5 AND no != %i AND nation = %i', $generalID, $nationID);
        foreach($chiefList as $chiefID){
            $chiefLogger = new ActionLogger($chiefID, $nationID, $year, $month);
            $chiefLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $chiefLogger->flush();
        }

        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>{$josaRo} 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>을 지원");
        $logger->pushNationalHistoryLog("<D><b>{$destNationName}</b></>{$josaRo} 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>을 지원");
        $logger->pushGlobalHistoryLog("<Y><b>【원조】</b></><D><b>{$nationName}</b></>에서 <D><b>{$destNationName}</b></>{$josaRo} 물자를 지원합니다");

        $logger->pushGeneralActionLog($broadcastMessage);
        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaRo} 물자를 지원합니다. <1>$date</>", ActionLogger::PLAIN);

        $destBroadcastMessage = $broadcastMessage = "<D><b>{$nationName}</b></>에서 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>을 원조했습니다.";
        $destChiefList = $db->queryFirstColumn('SELECT no FROM general WHERE officer_level >= 5 AND nation = %i', $destNationID);
        foreach($destChiefList as $destChiefID){
            $destChiefLogger = new ActionLogger($destChiefID, $nationID, $year, $month);
            $destChiefLogger->pushGeneralActionLog($destBroadcastMessage, ActionLogger::PLAIN);
            $destChiefLogger->flush();
        }

        $josaRoSrc = JosaUtil::pick($nationName, '로');
        $destNationLogger = new ActionLogger(0, $destChiefID, $year, $month);
        $destNationLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>{$josaRoSrc}부터 금<C>{$goldAmountText}</> 쌀<C>{$riceAmountText}</>을 지원 받음");



        $db->update('nation', [
            'gold'=>$db->sqleval('gold - %i', $goldAmount),
            'rice'=>$db->sqleval('rice - %i', $riceAmount),
            'surlimit'=>$db->sqleval('surlimit + %i', 12)
        ], 'nation = %i', $nationID);

        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $goldAmount),
            'rice'=>$db->sqleval('rice + %i', $riceAmount),
        ], 'nation = %i', $destNationID);

        $general->addExperience(5);
        $general->addDedication(5);


        $general->applyDB($db);

        return true;
    }

    public function getJSFiles(): array
    {
        return [
            'js/defaultSelectNationByMap.js'
        ];
    }

    
    public function getForm(): string
    {
        $currentNationLevel = getNationStaticInfo($this->generalObj->getNationID())['level'];
        ob_start();
?>
<?=\sammo\getMapHtml()?><br>
타국에게 원조합니다.<br>
작위별로 금액 제한이 있습니다.<br>
<?php foreach(\sammo\getNationLevelList() as $level => [$levelText,,]): ?>
<?=StringUtil::padStringAlignRight($levelText, 10)?>: <?=number_format($level*GameConst::$coefAidAmount)?><br>
<?php endforeach; ?>
원조할 국가를 목록에서 선택하세요.<br>
<select class='formInput' name="destNationID" id="destNationID" size='1' style='color:white;background-color:black;'>
<?php foreach(getAllNationStaticInfo() as $nation): ?>
    <option 
        value='<?=$nation['nation']?>' 
        style='color:<?=$nation['color']?>;'
    >【<?=$nation['name']?> 】</option>
<?php endforeach; ?>
</select>
국고 <select class='formInput' name="amountList[]" class="amountList" size='1' style='color:white;background-color:black;'>
<?php foreach(Util::range($currentNationLevel+1) as $nationLevel): ?>
    <option value='<?=$nationLevel*GameConst::$coefAidAmount?>'><?=$nationLevel*GameConst::$coefAidAmount?></option>
<?php endforeach; ?>
</select> 
병량 <select class='formInput' name="amountList[]" class="amountList" size='1' style='color:white;background-color:black;'>
<?php foreach(Util::range($currentNationLevel+1) as $nationLevel): ?>
    <option value='<?=$nationLevel*GameConst::$coefAidAmount?>'><?=$nationLevel*GameConst::$coefAidAmount?></option>
<?php endforeach; ?>
</select> 
<input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<?php
        return ob_get_clean();
    }
}