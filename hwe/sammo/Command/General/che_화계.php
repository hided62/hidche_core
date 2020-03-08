<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use function \sammo\{
    searchDistance,
    printCitiesBasedOnDistance
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_화계 extends Command\GeneralCommand{
    static protected $actionName = '화계';
    static public $reqArg = true;

    static protected $statType = 'intel';
    static protected $injuryGeneral = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(!key_exists($this->arg['destCityID'], CityConst::all())){
            return false;
        }
        $this->arg = [
            'destCityID'=>$this->arg['destCityID']
        ];
        return true;
    }

    protected function calcSabotageAttackProb():float{
        $statType = static::$statType;
        $general = $this->generalObj;
        $nation = $this->nation;

        if($statType === 'leadership'){
            $genScore = $general->getLeadership();
        }
        else if($statType === 'strength'){
            $genScore = $general->getStrength();
        }
        else if($statType === 'intel'){
            $genScore = $general->getIntel();
        }
        else{
            throw new MustNotBeReachedException();
        }

        $prob = $genScore / GameConst::$sabotageProbCoefByStat;
        $prob = $general->onCalcDomestic('계략', 'success', $prob);
        return $prob;
    }

    protected function calcSabotageDefenceProb(array $destCityGeneralList):float{
        $statType = static::$statType;
        $destCity = $this->destCity;
        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];

        $maxGenScore = 0;
        foreach($destCityGeneralList as $destGeneral){
            /** @var General $destGeneral */
            if($destGeneral->getNationID() != $destNationID){
                continue;
            }

            if($statType === 'leadership'){
                $genScore = $destGeneral->getLeadership();
            }
            else if($statType === 'strength'){
                $genScore = $destGeneral->getStrength();
            }
            else if($statType === 'intel'){
                $genScore = $destGeneral->getIntel();
            }
            else{
                throw new MustNotBeReachedException();
            }
            $maxGenScore = max($maxGenScore, $genScore);
        }

        $prob = $maxGenScore / GameConst::$sabotageProbCoefByStat;

        $prob += $destCity['secu'] / $destCity['secu2'] / 5; //최대 20%p
        $prob += $destCity['supply'] ? 0.1 : 0;
        return $prob;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setDestCity($this->arg['destCityID'], null); //xxx: 이대로라면 메인 페이지 갱신시마다 DB query를 하게 된다.
        $this->setDestNation($this->destCity['nation']);

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::NotOccupiedDestCity(),
            ConstraintHelper::NotNeutralDestCity(),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
            ConstraintHelper::DisallowDiplomacyStatus(
                $this->generalObj->getNationID(),
                [7 => '불가침국입니다.']
            ),
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        $statTypeBase = [
            'leadership'=>'통솔경험',
            'strength'=>'무력경험',
            'intel'=>'지력경험',
        ];
        $statType = $statTypeBase[static::$statType];
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}({$statType}";
        if($reqGold > 0){
            $title .= ", 자금{$reqGold}";
        }
        if($reqRice > 0){
            $title .= ", 군량{$reqRice}";
        }
        $title .= ')';
        return $title;
    }

    public function getCost():array{
        $env = $this->env;
        $cost = $env['develcost'] * 5;
        return [$cost, $cost];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testRunnable();
        if($failReason === null){
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        return "{$failReason} <G><b>{$destCityName}</b></>에 {$commandName} 실패.";
    }

    protected function affectDestCity(int $injuryCount){
        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();

        $destCity = $this->destCity;

        $destCityName = $destCity['name'];
        $destCityID = $destCity['city'];

        $commandName = $this->getName();

        $agriAmount = Util::valueFit(Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax), null, $destCity['agri']);
        $commAmount = Util::valueFit(Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax), null, $destCity['comm']);
        $destCity['agri'] -= $agriAmount;
        $destCity['comm'] -= $commAmount;

        DB::db()->update('city', [
            'state'=>32,
            'agri'=>$destCity['agri'],
            'comm'=>$destCity['comm']
        ], 'city=%i', $destCityID);

        $agriAmountText = number_format($agriAmount);
        $commAmountText = number_format($commAmount);

        $josaYi = JosaUtil::pick($destCityName, '이');
        $logger->pushGlobalActionLog("<G><b>{$destCityName}</b></>{$josaYi} 불타고 있습니다.");
        $josaYi = JosaUtil::pick($commandName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$commandName}{$josaYi} 성공했습니다. <1>$date</>");

        $logger->pushGeneralActionLog(
            "도시의 농업이 <C>{$agriAmountText}</>, 상업이 <C>{$commAmountText}</>만큼 감소하고, 장수 <C>{$injuryCount}</>명이 부상 당했습니다.",
            ActionLogger::PLAIN
        );
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $destCity = $this->destCity;

        $destCityName = $destCity['name'];
        $destCityID = $destCity['city'];
        $destNationID = $destCity['nation'];

        $commandName = $this->getName();
        $statType = static::$statType;

        $logger = $general->getLogger();

        $dist = searchDistance($general->getCityID(), 5, false)[$destCityID]??99;

        $destCityGeneralList = [];
        
        [$year, $month] = [$env['year'], $env['month']];
        
        foreach($db->query(
            'SELECT `no`,name,city,nation,level,leadership,horse,strength,weapon,intel,book,item,last_turn,injury,special,special2,injury,crew,atmos,train FROM general WHERE city = %i',
            $destCityID,
            $destNationID
        ) as $rawDestCityGeneral){
            $destCityGeneralList[] = new General($rawDestCityGeneral, $destCity, $year, $month, true);
            //계략에 성공할 경우 logger를 사용해야 하므로 해야하므로, 미리 초기화한다.
            //실패하면 날리는거지 뭐~
        };

        $prob = GameConst::$sabotageDefaultProb + $this->calcSabotageAttackProb() - $this->calcSabotageDefenceProb($destCityGeneralList);
        $prob /= $dist[$destCityID];

        if(!Util::randBool($prob)){
            $josaYi = JosaUtil::pick($commandName, '이');
            $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$commandName}{$josaYi} 실패했습니다. <1>$date</>");

            $exp = Util::randRangeInt(1, 100);
            $ded = Util::randRangeInt(1, 70);

            $exp = $general->onCalcStat($general, 'experience', $exp);
            $ded = $general->onCalcStat($general, 'dedication', $ded);

            [$reqGold, $reqRice] = $this->getCost();
            $general->increaseVarWithLimit('gold', -$reqGold, 0);
            $general->increaseVarWithLimit('rice', -$reqRice, 0);
            $general->increaseVar('experience', $exp);
            $general->increaseVar('dedication', $ded);
            $general->increaseVar($statType.'2', 1);

            $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
            $general->checkStatChange();
            $general->applyDB($db);
            return false;
        }

        if(static::$injuryGeneral){
            $injuryCount = \sammo\SabotageInjury($destCityGeneralList, '계략');
        }
        else{
            $injuryCount = 0;
        }

        $this->affectDestCity($injuryCount);

        $itemObj = $general->getItem();
        if($itemObj->isConsumableNow('GeneralCommand', '계략') && $itemObj::$consumable){
            $itemName = $itemObj->getName();
            $itemRawName = $itemObj->getRawName();
            $josaUl = JosaUtil::pick($itemRawName, '을');
            $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
            $general->deleteItem();
        }

        $exp = Util::randRangeInt(201, 300);
        $ded = Util::randRangeInt(141, 210);

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);
        
        [$reqGold, $reqRice] = $this->getCost();
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar($statType.'2', 1);
        $general->increaseVar('firenum', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

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
        $currentCityID = $this->generalObj->getCityID();
        $commandName = $this->getName();
        $josaUl = JosaUtil::pick($commandName, '을');

        ob_start();
?>
<?=\sammo\getMapHtml()?><br>
선택된 도시에 <?=$commandName?><?=$josaUl?> 실행합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'><br>
<?=\sammo\optionsForCities()?><br>
</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<br>
<?=printCitiesBasedOnDistance($currentCityID, 2)?>
<?php
        return ob_get_clean();
    }

    
}