<?php
namespace sammo;

class WarUnitGeneral extends WarUnit{
    protected $bonusPhase = 0;

    function __construct(General $general, array $rawNation, bool $isAttacker){
        $this->general = $general;
        $this->raw = $general->getRaw();
        $this->rawNation = $rawNation; //read-only
        $this->isAttacker = $isAttacker;

        $this->logger = $this->general->getLogger();
        $this->crewType = $this->general->getCrewTypeObj();

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
        return $this->general->getName();
    }

    function getCityVar(string $key){
        return $this->general->getRawCity()[$key];
    }
    
    function setOppose(?WarUnit $oppose){
        parent::setOppose($oppose);
        $general = $this->general;
        $this->general->increaseVar('warnum', 1);

        if($this->isAttacker){
            $general->updateVar('recent_war', $general->getTurnTime());
        }
        else if($oppose !== null){
            $general->updateVar('recent_war', $oppose->getGeneral()->getTurnTime());
        }
    }

    function getMaxPhase():int{
        $phase = $this->getCrewType()->speed;
        $phase = $this->general->onCalcStat($this->general, 'initWarPhase', $phase);
        return $phase + $this->bonusPhase;
    }

    function addTrain(int $train){
        $this->general->increaseVarWithLimit('train', $train, 0, GameConst::$maxTrainByWar);
    }

    function addAtmos(int $atmos){
        $this->general->increaseVarWithLimit('atmos', $atmos, 0, GameConst::$maxAtmosByWar);
    }

    function getComputedTrain(){
        $train = $this->general->getVar('train');
        $train = $this->general->onCalcStat($this->general, 'bonusTrain', $train);
        $train += $this->trainBonus;
        
        return $train;
    }

    function getComputedAtmos(){
        $atmos = $this->general->getVar('atmos');
        $atmos = $this->general->onCalcStat($this->general, 'bonusAtmos', $atmos);
        $atmos += $this->atmosBonus;
        
        return $atmos;
    }

    function getComputedCriticalRatio():float{
        $general = $this->general;
        $criticalRatio = $this->getCrewType()->getCriticalRatio($general);

        /** @var float $criticalRatio */
        $criticalRatio = $general->onCalcStat($general, 'warCriticalRatio', $criticalRatio, ['isAttacker'=>$this->isAttacker]);
        return $criticalRatio;
    }

    function getComputedAvoidRatio():float{
        $general = $this->general;

        $avoidRatio = $this->getCrewType()->avoid / 100;
        $avoidRatio *= $this->getComputedTrain() / 100;

        /** @var float $avoidRatio */
        $avoidRatio = $general->onCalcStat($general, 'warAvoidRatio', $avoidRatio, ['isAttacker'=>$this->isAttacker]);

        if($this->getOppose()->getCrewType()->armType == GameUnitConst::T_FOOTMAN){
            $avoidRatio *= 0.75;
        }

        return $avoidRatio;
    }

    function addWin(){
        $general = $this->general;
        $general->increaseRankVar('killnum', 1);
        $general->multiplyVarWithLimit('atmos', 1.1, null, GameConst::$maxAtmosByWar);

        $this->addStatExp(1);
    }

    function addStatExp(int $value = 1){
        $general = $this->general;
        if($this->crewType->armType == GameUnitConst::T_WIZARD) {   // 귀병
            $general->increaseVar('intel_exp', $value);
        } elseif($this->crewType->armType == GameUnitConst::T_SIEGE) {   // 차병
            $general->increaseVar('leadership_exp', $value);
        } else {
            $general->increaseVar('strength_exp', $value);
        }
    }

    function addLevelExp(float $value){
        $general = $this->general;
        if(!$this->isAttacker){
            $value *= 0.8;
        }
        $value = $general->onCalcStat($general, 'experience', $value);
        $general->increaseVar('experience', $value);
    }

    function addDedication(float $value){
        $general = $this->general;
        $value = $general->onCalcStat($general, 'dedication', $value);
        $general->increaseVar('dedication', $value);
    }

    function addLose(){
        $general = $this->general;
        $general->increaseRankVar('deathnum', 1);
        $this->addStatExp(1);
    }

    function computeWarPower(){
        [$warPower,$opposeWarPowerMultiply] = parent::computeWarPower();

        $general = $this->general;
        $generalNo = $general->getVar('no');
        $genLevel = $general->getVar('level');

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
            else if(2 <= $genLevel && $genLevel <= 4 && $generalNo == $this->getCityVar('officer'.$genLevel)){
                $opposeWarPowerMultiply *= 0.95;
            }
        }

        $expLevel = $general->getVar('explevel');

        if($this->getOppose() instanceof WarUnitCity){
            $warPower *= 1 + $expLevel / 600;
        }
        else{
            $warPower /= max(0.01, 1 - $expLevel / 300);
            $opposeWarPowerMultiply *= max(0.01, 1 - $expLevel / 300);
        }
        

        [$specialMyWarPowerMultiply, $specialOpposeWarPowerMultiply] = $this->general->getWarPowerMultiplier($this);
        $warPower *= $specialMyWarPowerMultiply;
        $opposeWarPowerMultiply *= $specialOpposeWarPowerMultiply;

        $this->warPower = $warPower;
        $this->oppose->setWarPowerMultiply($opposeWarPowerMultiply);
        return [$warPower,$opposeWarPowerMultiply];
    }

    function getHP():int{
        return $this->general->getVar('crew');
    }

    function addDex(GameUnitDetail $crewType, float $exp){
        $this->general->addDex($crewType, $exp, false);
    }

    function decreaseHP(int $damage):int{
        $general = $this->general;
        $damage = min($damage, $general->getVar('crew'));

        $this->dead += $damage;
        $this->deadCurr += $damage;
        $general->increaseVar('crew', -$damage);

        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->oppose->getCrewType(), $addDex);

        return $general->getVar('crew');
    }

    function increaseKilled(int $damage):int{
        $general = $this->general;
        $this->addLevelExp($damage / 50);

        $rice = $damage / 100;
        if(!$this->isAttacker){
            $rice *= 0.8;
        }

        $rice *= $this->crewType->rice;
        $rice *= getTechCost($this->getNationVar('tech'));

        $general->increaseVarWithLimit('rice', -$rice, 0);
        
        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->getCrewType(), $addDex);

        $this->killed += $damage;
        $this->killedCurr += $damage;
        return $this->killed;
    }

    function tryWound():bool{
        $general = $this->general;
        if($this->hasActivatedSkillOnLog('부상무효')){
            return false;
        }
        if(!Util::randBool(0.05)){
            return false;
        }

        $general->increaseVarWithLimit('injury', Util::randRangeInt(10, 80), null, 80);
        $this->getLogger()->pushGeneralActionLog("전투중 <R>부상</>당했다!", ActionLogger::PLAIN);

        return true;
    }

    function continueWar(&$noRice):bool{
        $general = $this->general;
        if($this->getHP() <= 0){
            $noRice = false;
            return false;
        }
        if($general->getVar('rice') <= $this->getHP() / 100){
            $noRice = true;
            return false;
        }
        return true;
    }

    function checkStatChange():bool{
        return $this->general->checkStatChange();
    }

    function finishBattle(){    
        if($this->isFinished){
            return;
        }
        $this->clearActivatedSkill();
        $this->isFinished = true;
        $general = $this->general;

        $general->increaseRankVar('killcrew', $this->killed);
        $general->increaseRankVar('deathcrew', $this->dead);
        
        $general->updateVar('rice', Util::round($general->getVar('rice')));
        $general->updateVar('experience', Util::round($general->getVar('experience')));
        $general->updateVar('dedication', Util::round($general->getVar('dedication')));

        $this->checkStatChange();
    }

    /**
     * @param \MeekroDB $db
     */
    function applyDB($db):bool{
        $affected = $this->getGeneral()->applyDB($db);
        $this->getLogger()->flush();
        return $affected;
    }

}