<?php
namespace sammo;

class WarUnitGeneral extends WarUnit{
    protected $rawCity;

    protected $logger;
    protected $crewType;

    protected $win = 0;

    protected $updatedVar = [];

    function __construct($raw, $rawCity, $rawNation, $isAttacker, $year, $month){
        setLeadershipBonus($raw, $rawNation['level']);

        $this->raw = $raw;
        $this->rawCity = $rawCity; //read-only
        $this->rawNation = $rawNation; //read-only
        $this->isAttacker = $isAttacker;

        $this->logger = new ActionLogger(
            $this->getVar('no'), 
            $this->getVar('nation'), 
            $year, 
            $month
        );
        $this->crewType = GameUnitConst::byID($this->getVar('crewtype'));

        $cityLevel = $this->getCityVar('level');

        if($isAttacker){
            //공격자 보정
            if($cityLevel == 2){
                $this->atmosBonus += 5;
            }
            if($rawNation['capital'] == $this->getCityVar('city')){
                $this->atmosBonus += 5;
            }
        }
        else{
            //수비자 보정
            if($cityLevel == 1){
                $this->trainBonus += 5;
            }
            else if($cityLevel == 3){
                $this->trainBonus += 5;
            }
        }
    }

    function getName():string{
        return $this->getVar('name');
    }

    function getCityVar(string $key):array{
        return $this->rawCity[$key];
    }
    
    function getSpecialDomestic():int{
        return $this->getVar('special');
    }

    function setOppose(?WarUnit $oppose){
        $this->oppose = $oppose;
        $this->increaseVar('warnum', 1);

        if($this->isAttacker){
            $this->updateVar('recwar', $this->getVar('turntime'));
        }
        else if($oppose !== null){
            $this->updateVar('recwar', $oppose->getVar('turntime'));
        }
        
        $this->activatedSkill = [];
    }

    function getSpecialWar():int{
        return $this->getVar('special2');
    }

    function getItem():int{
        return $this->getVar('item');
    }

    function getMaxPhase():int{
        $phase = $this->getCrewType()->speed;
        if($this->getSpecialWar() == 60){
            $phase += 1;
        }
        return $phase;
    }

    function addTrain(int $train){
        $this->increaseVarWithLimit('train', $train, 0, GameConst::$maxTrainByWar);
    }

    function addAtmos(int $atmos){
        $this->increaseVarWithLimit('atmos', $train, 0, GameConst::$maxAtmosByWar);
    }

    function getComputedTrain(){
        $train = $this->getVar('train');
        $train += $this->trainBonus;
        
        return $train;
    }

    function getComputedAtmos(){
        return GameConst::$maxAtmosByCommand;
    }

    function getComputedAvoidRatio(){
        $avoidRatio = $this->getCrewType()->avoid / 100;
    }

    function addWin(){
        $this->win += 1;
        $this->increaseVar('killnum', 1);

        $this->multiplyVarWithLimit('atmos', 1.1, null, GameConst::$maxAtmosByWar);

        $this->addStatExp(1);
    }

    function addStatExp(int $value = 1){
        if($this->crewType->armType == GameUnitConst::T_WIZARD) {   // 귀병
            $this->increaseVar('intel2', $value);
        } elseif($this->crewType->armType == GameUnitConst::T_SIEGE) {   // 차병
            $this->increaseVar('leader2', $value);
        } else {
            $this->increaseVar('power2', $value);
        }
    }

    function addLevelExp(float $value){
        if(!$this->isAttacker){
            $value *= 0.8;
        }
        $value *= getCharExpMultiplier($this->getCharacter());
        $this->increaseVar('experience', $value);
    }

    function addDedication(float $value){
        $value *= getCharDedMultiplier($this->getCharacter());
        $this->increaseVar('dedication', $value);
    }

    function addLose(){
        $this->increaseVar('deathnum', 1);
        $this->addStatExp(1);
    }

    

    protected function getWarPowerMultiplyBySpecialWar():array{
        //TODO: 장기적으로 if문이 아니라 객체를 이용하여 처리해야함
        $myWarPowerMultiply = 1.0;
        $opposeWarPowerMultiply = 1.0;

        $specialWar = $this->getSpecialWar();

        if($specialWar == 52){
            $myWarPowerMultiply *= 1.20;
        }
        else if($specialWar == 60){
            $myWarPowerMultiply *= 1.10;
        }
        else if($specialWar == 61){
            $myWarPowerMultiply *= 1.10;
        }
        else if($specialWar == 50){
            $opposeWarPowerMultiply *= 0.9;
        }
        else if($specialWar == 62){
            $opposeWarPowerMultiply *= 0.9;
        }
        else if($specialWar == 75){
            $opposeCrewType = $this->oppose->getCrewType();
            if($opposeCrewType->reqCities || $opposeCrewType->reqRegions){
                $myWarPowerMultiply *= 1.1;
                $opposeWarPowerMultiply *= 0.9;
            }
        }


        return [$myWarPowerMultiply, $opposeWarPowerMultiply];
    }

    function computeWarPower(){
        [$warPower,$opposeWarPowerMultiply] = parent::computeWarPower();

        $generalNo = $this->getVar('no');
        $genLevel = $this->getVar('level');

        if($this->isAttacker){
            if($genLevel == 12){
                $warPower *= 1.10;
            }
            else if($genLevel == 11 | $genLevel == 10 || $genLevel == 8 || $genLevel == 6){
                $warPower *= 1.05;
            }
        }
        else{
            if($genLevel == 12){
                $opposeWarPowerMultiply *= 0.90;
            }
            else if($genLevel == 11 || $genLevel == 9 || $genLevel == 7 || $genLevel == 5){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 4 && $generalNo == $this->getCityVar('gen1')){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 3 && $generalNo == $this->getCityVar('gen2')){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 2 && $generalNo == $this->getCityVar('gen3')){
                $opposeWarPowerMultiply *= 0.95;
            }
        }

        $expLevel = $this->getVar('explevel');
        $warPower /= max(0.01, 1 - $expLevel / 300);
        $opposeWarPowerMultiply *= max(0.01, 1 - $expLevel / 300);

        [$specialMyWarPowerMultiply, $specialOpposeWarPowerMultiply] = $this->getWarPowerMultiplyBySpecialWar();
        $warPower *= $specialMyWarPowerMultiply;
        $opposeWarPowerMultiply *= $specialOpposeWarPowerMultiply;

        $this->warPower = $warPower;
        $this->oppose->setWarPowerMultiply($opposeWarPowerMultiply);
        return [$warPower,$opposeWarPowerMultiply];
    }

    ///전투 개시 시에 작동하여 1회에만 작동하는 아이템
    function useBattleInitItem():bool{
        $item = $this->getItem();

        if($item == 0){
            return false;
        }

        $itemActivated = false;
        $itemConsumed = false;
        $itemName = getItemName($item);

        if($item == 3){
            //탁주 사용
            $this->addAtmos(3);
            $itemActivated = true;
            $itemConsumed = true;
        }
        else if($item >= 14 && $item <= 16){
            //의적주, 두강주, 보령압주 사용
            $this->addAtmos(5);
            $itemActivated = true;
        }
        else if($item >= 19 && $item <= 20){
            //춘화첩, 초선화 사용
            $this->addAtmos(7);
            $itemActivated = true;
        }
        else if($item == 4){
            //청주 사용
            $this->addTrain(3);
            $itemActivated = true;
            $itemConsumed = true;
        }
        else if($item >= 12 && $item <= 13){
            //과실주, 이강주 사용
            $this->addTrain(5);
            $itemActivated = true;
        }
        else if($item >= 18 && $item <= 18){
            //철벽서, 단결도 사용
            $this->addTrain(7);
            $itemActivated = true;
        }

        if($itemConsumed){
            $this->updateVar('item', 0);
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }

        return $itemActivated;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 스킬
    function checkBattleBeginSkill():bool{
        $skillResult = false;
        $oppose = $this->getOppose();

        $specialWar = $this->getSpecialWar();
        if (
            $specialWar == 70 &&
            $this->oppose instanceof WarUnitGeneral &&
            !$this->hasActivateSkill('저격') &&
            Util::randBool(1/3)
        ) {
            $oppose->activateSkill('저격');
            $skillResult = true;
        }
        
        return $skillResult;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 아이템
    function checkBattleBeginItem():bool{
        $item = $this->getItem();
        $oppose = $this->getOppose();
        if(!$item){
            return false;
        }

        $itemActivated = false;
        $itemConsumed = false;
        $itemName = getItemName($item);

        if(
            $item == 2 &&
            $this->oppose instanceof WarUnitGeneral &&
            !$this->hasActivateSkill('저격') &&
            Util::randBool(1/5)
        ){
                $itemActivated = true;
                $itemConsumed = true;
                $this->activateSkill('저격');
        }

        if($itemConsumed){
            //NOTE: 소비 아이템은 하나인가?, 1회용인가?
            $this->updateVar('item', 0);
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }
        
        return $itemActivated;
    }

    function applyBattleBeginSkillAndItem():bool{
        $result = false;
        $oppose = $this->getOppose();

        if($this->hasActivatedSkill('저격')){
            $result = true;

            $oppose->getLogger()->pushGeneralActionLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $oppose->getLogger()->pushGeneralBattleDetailLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $this->getLogger()->pushGeneralActionLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);
            $this->getLogger()->pushGeneralBattleDetailLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);

            $oppose->increaseVarWithLimit('injury', Util::randRangeInt(20, 60), null, 80);
        }

        return $result;
    }

    function getHP():int{
        return $this->getVar('crew');
    }

    function addDex(GameUnitDetail $crewType, float $exp){
        $armType = $crewType->armType;

        if($armType == GameUnitConst::T_CASTLE){
            $armType = GameUnitConst::T_SIEGE;
        }

        if($armType < 0){
            return;
        }

        if($armType == GameUnitConst::T_WIZARD) {
            $exp *= 0.9;
        }
        else if($armType == GameUnitConst::T_SIEGE) {
            $exp *= 0.9;
        }
        $exp *= ($this->getComputedTrain() + $this->getComputedAtmos()) / 200;

        $ntype = $armType*10;
        $dexType = "dex{$ntype}";

        $this->increaseVar($dexType, $exp);
    }

    function decreaseHP(int $damage):int{
        $damage = min($damage, $this->getVar('crew'));

        $this->dead += $damage;
        $this->increaseVar('crew', -$damage);

        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->oppose->getCrewType(), $addDex);

        return $this->getVar('crew');
    }

    function increaseKilled(int $damage):int{
        $this->addLevelExp($damage / 50);

        $rice = $damage / 100;
        if(!$this->isAttacker){
            $rice *= 0.8;
        }

        $rice *= getCharExpMultiplier($this->getCharacter());
        $rice *= $this->crewType->rice;
        $rice *= getTechCost($this->getNationVar('tech'));

        $this->increaseVarWithLimit('rice', -$rice, 0);
        
        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->oppose->getCrewType(), $addDex);

        $this->killed += $damage;
        return $this->killed;
    }

    function checkPreActiveSkill():bool{
        return false;
    }

    function checkActiveSkill():bool{
        return false;
    }

    function checkPostActiveSkill():bool{
        return false;
    }

    function applyActiveSkill():bool{
        return false;
    }

    function tryWound():bool{
        if(!Util::randBool(0.05)){
            return false;
        }

        $this->increaseVarWithLimit('injury', Util::randRangeInt(10, 80), null, 80);
        $this->getLogger()->pushGeneralActionLog("전투중 <R>부상</>당했다!", ActionLogger::PLAIN);

        return true;
    }

    function continueWar(&$noRice):bool{
        if($this->getVar('crew') <= 0){
            $noRice = false;
            return false;
        }
        if($this->getVar('rice') <= $this->getHP() / 100){
            $noRice = true;
            return false;
        }
        return true;
    }

    ///checkAbility의 method 버전
    function checkStatChange():bool{
        //FIXME: 장기적으로는 General 클래스가 별도로 있어야 함
        $logger = $this->getLogger();
        $limit = GameConst::$upgradeLimit;

        $table = [
            ['통솔', 'leader'],
            ['무력', 'power'],
            ['지력', 'intel'],
        ];

        $result = false;

        foreach($table as [$statNickName, $statName]){
            $statExpName = $statName.'2';

            if($this->getVar($statExpName) < 0){
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 떨어졌습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, $limit);
                $this->increaseVar($statName, -1);
                $result = true;
            }
            else if($this->getVar($statExpName) >= $limit){
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 올랐습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, -$limit);
                $this->increaseVar($statName, 1);
                $result = true;
            }
        }

        return $result;
    }

    function finishBattle(){        
        $this->increaseVar('killcrew', $this->killed);
        $this->increaseVar('deathcrew', $this->dead);
        
        $this->updateVar('rice', Util::round($this->getVar('rice')));
        $this->updateVar('experience', Util::round($this->getVar('experience')));
        $this->updateVar('dedication', Util::round($this->getVar('dedication')));

        $this->checkStatChange();
    }

    /**
     * @param \MeekroDB $db
     */
    function applyDB($db):bool{
        $updateVals = [];
        foreach(array_keys($this->updatedVar) as $key){
            $updateVals[$key] = $this->raw[$key];
        }

        if(!$updateVals){
            return false;
        }
        
        $db->update('general', $updateVals, 'no=%i', $this->raw['no']);
        return $db->affectedRows() > 0;
    }

}