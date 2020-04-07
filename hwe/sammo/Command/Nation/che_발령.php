<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    CityConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_발령 extends Command\NationCommand{
    static protected $actionName = '발령';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        //NOTE: 사망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if(!key_exists('destGeneralID', $this->arg)){
            return false;
        }
        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(CityConst::byID($this->arg['destCityID']) === null){
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        $destCityID = $this->arg['destCityID'];

        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }
        $this->arg = [
            'destGeneralID'=>$destGeneralID,
            'destCityID'=>$destCityID,
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        $this->setDestCity($this->arg['destCityID'], null);

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], null, 1);
        $this->setDestGeneral($destGeneral);
        
        $this->runnableConstraints=[
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral(),
            ConstraintHelper::OccupiedDestCity(),
            ConstraintHelper::SuppliedDestCity(),
        ];
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testRunnable();
        if($failReason === null){
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destGeneralName = $this->destGeneralObj->getName();
        return "{$failReason} <Y>{$destGeneralName}</> {$commandName} 실패.";
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

    public function getBrief():string{
        $commandName = $this->getName();
        $destGeneralName = $this->destGeneralObj->getName();
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "【{$destGeneralName}】【{$destCityName}】{$josaRo} {$commandName}";
    }


    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $destCity = $this->destCity;
        $destCityID = $destCity['city'];
        $destCityName = $destCity['name'];

        $destGeneral = $this->destGeneralObj;
        $destGeneralName = $destGeneral->getName();
        
        $logger = $general->getLogger();

        $destGeneral->setVar('city', $destCityID);

        $josaUl = JosaUtil::pick($destGeneralName, '을');
        $josaRo = JosaUtil::pick($destCityName, '로');
        $destGeneral->getLogger()->pushGeneralActionLog("<Y>{$generalName}</>에 의해 <G><b>{$destCityName}</b></>{$josaRo} 발령됐습니다. <1>$date</>");
        $destGeneral->setAuxVar('last발령', $general->getTurnTime());
        $logger->pushGeneralActionLog("<Y>{$destGeneralName}</>{$josaUl} <G><b>{$destCityName}</b></>{$josaRo} 발령했습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }
    
    public function getJSFiles(): array
    {
        return [
            'js/defaultSelectCityByMap.js'
        ];
    }

    public function getForm(): string
    {
        $db = DB::db();

        $destRawGenerals = $db->query('SELECT no,name,level,npc,gold,rice FROM general WHERE nation = %i AND no != %i ORDER BY npc,binary(name)',$this->generalObj->getNationID(), $this->generalObj->getID());
        $destGeneralList = [];
        foreach($destRawGenerals as $destGeneral){
            $nameColor = \sammo\getNameColor($destGeneral['npc']);
            if($nameColor){
                $nameColor = " style='color:{$nameColor}'";
            }

            $name = $destGeneral['name'];
            if($destGeneral['level'] >= 5){
                $name = "*{$name}*";
            }

            $destGeneralList[] = [
                'no'=>$destGeneral['no'],
                'color'=>$nameColor,
                'name'=>$name,
                'gold'=>$destGeneral['gold'],
                'rice'=>$destGeneral['rice']
            ];
        }
        ob_start();
?>
<?=\sammo\getMapHtml()?><br>
선택된 도시로 아국 장수를 발령합니다.<br>
아국 도시로만 발령이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<select class='formInput' name="destGeneralID" id="destGeneralID" size='1' style='color:white;background-color:black;'>
<?php foreach($destGeneralList as $destGeneral): ?>
<option value='<?=$destGeneral['no']?>' <?=$destGeneral['color']?>><?=$destGeneral['name']?>(금:<?=$destGeneral['gold']?>, 쌀:<?=$destGeneral['rice']?>)</option>
<?php endforeach; ?>
</select>
<select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
<?=\sammo\optionsForCities()?><br>
</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<?php
        return ob_get_clean();
    }
}