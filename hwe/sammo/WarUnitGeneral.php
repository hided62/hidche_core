<?php

namespace sammo;

use sammo\Enums\RankColumn;

class WarUnitGeneral extends WarUnit
{
    protected $raw;
    protected $killedPerson = 0;
    protected $deadPerson = 0;

    function __construct(public readonly RandUtil $rng, General $general, array $rawNation, bool $isAttacker)
    {
        $this->general = $general;
        $this->raw = $general->getRaw();
        $this->rawNation = $rawNation; //read-only
        $this->isAttacker = $isAttacker;

        $this->logger = $this->general->getLogger();
        $this->crewType = $this->general->getCrewTypeObj();

        $cityLevel = $this->getCityVar('level');

        if ($isAttacker) {
            //공격자 보정
            if ($cityLevel == 2) {
                $this->atmosBonus += 5;
            }
            if ($rawNation['capital'] == $this->getCityVar('city')) {
                $this->atmosBonus += 5;
            }
        } else {
            //수비자 보정
            if ($cityLevel == 1) {
                $this->trainBonus += 5;
            } else if ($cityLevel == 3) {
                $this->trainBonus += 5;
            }
        }
    }

    function getName(): string
    {
        return $this->general->getName();
    }

    function getCityVar(string $key)
    {
        return $this->general->getRawCity()[$key];
    }

    function setOppose(?WarUnit $oppose)
    {
        parent::setOppose($oppose);
        $general = $this->general;
        $this->general->increaseRankVar(RankColumn::warnum, 1);

        if ($this->isAttacker) {
            $semiTurn = $general->getTurnTime();
        } else if ($oppose !== null) {
            $semiTurn = $oppose->getGeneral()->getTurnTime();
        } else {
            LogText("WarUnitGeneral::setOppose", "defender인데 oppose가 null {$general->getID()}, {$general->getTurnTime()}");
            $semiTurn = $general->getTurnTime();
        }
        $phase = $this->getRealPhase();
        $semiTurn = substr($semiTurn, 0, strlen($semiTurn) - 2);
        $semiTurn .=  sprintf("%02d", Util::valueFit($phase, 0, 99));
        $general->setVar('recent_war', $semiTurn);
    }

    function getMaxPhase(): int
    {
        $phase = $this->getCrewType()->speed;
        $phase = $this->general->onCalcStat($this->general, 'initWarPhase', $phase, ['isAttacker' => $this->isAttacker]);
        //maxPhase는 상대가 결정되기 전에 계산되므로 oppose를 호출할 수 없음
        return $phase + $this->bonusPhase;
    }

    function addTrain(int $train)
    {
        $this->general->increaseVarWithLimit('train', $train, 0, GameConst::$maxTrainByWar);
    }

    function addAtmos(int $atmos)
    {
        $this->general->increaseVarWithLimit('atmos', $atmos, 0, GameConst::$maxAtmosByWar);
    }

    function getDex(GameUnitDetail $crewType)
    {
        $dex = $this->general->getDex($crewType);
        $dex = $this->general->onCalcStat($this->general, 'dex' . $crewType->armType, $dex, [
            'isAttacker' => $this->isAttacker,
            'opposeType' => $this->oppose->getCrewType()
        ]);
        $dex = $this->oppose->general->onCalcOpposeStat($this->general, 'dex' . $crewType->armType, $dex, [
            'isAttacker' => $this->isAttacker,
            'opposeType' => $this->oppose->getCrewType()
        ]);
        return $dex;
    }

    function getComputedTrain()
    {
        $train = $this->general->getVar('train');
        $train = $this->general->onCalcStat($this->general, 'bonusTrain', $train, ['isAttacker' => $this->isAttacker]);
        $train = $this->oppose->general->onCalcOpposeStat($this->general, 'bonusTrain', $train, ['isAttacker' => $this->isAttacker]);
        $train += $this->trainBonus;

        return $train;
    }

    function getComputedAtmos()
    {
        $atmos = $this->general->getVar('atmos');
        $atmos = $this->general->onCalcStat($this->general, 'bonusAtmos', $atmos, ['isAttacker' => $this->isAttacker]);
        $atmos = $this->oppose->general->onCalcOpposeStat($this->general, 'bonusAtmos', $atmos, ['isAttacker' => $this->isAttacker]);
        $atmos += $this->atmosBonus;

        return $atmos;
    }

    function getComputedCriticalRatio(): float
    {
        $general = $this->general;
        $criticalRatio = $this->getCrewType()->getCriticalRatio($general);

        /** @var float $criticalRatio */
        $criticalRatio = $general->onCalcStat($general, 'warCriticalRatio', $criticalRatio, ['isAttacker' => $this->isAttacker]);
        $criticalRatio = $this->oppose->general->onCalcOpposeStat($general, 'warCriticalRatio', $criticalRatio, ['isAttacker' => $this->isAttacker]);
        return $criticalRatio;
    }

    function getComputedAvoidRatio(): float
    {
        $general = $this->general;

        $avoidRatio = $this->getCrewType()->avoid / 100;
        $avoidRatio *= $this->getComputedTrain() / 100;

        /** @var float $avoidRatio */
        $avoidRatio = $general->onCalcStat($general, 'warAvoidRatio', $avoidRatio, ['isAttacker' => $this->isAttacker]);
        $avoidRatio = $this->oppose->general->onCalcOpposeStat($general, 'warAvoidRatio', $avoidRatio, ['isAttacker' => $this->isAttacker]);

        if ($this->getOppose()->getCrewType()->armType == GameUnitConst::T_FOOTMAN) {
            $avoidRatio *= 0.75;
        }

        return $avoidRatio;
    }

    function addWin()
    {
        $general = $this->general;
        $general->increaseRankVar(RankColumn::killnum, 1);

        $oppose = $this->getOppose();
        if ($oppose instanceof WarUnitCity) {
            $general->increaseRankVar(RankColumn::occupied, 1);
        }

        if($this->isAttacker()){
            $general->multiplyVarWithLimit('atmos', 1.1, null, GameConst::$maxAtmosByWar);
        }
        else{
            $general->multiplyVarWithLimit('atmos', 1.05, null, GameConst::$maxAtmosByWar);
        }


        $this->addStatExp(1);
    }

    function addStatExp(int $value = 1)
    {
        $general = $this->general;
        if ($this->crewType->armType == GameUnitConst::T_WIZARD) {   // 귀병
            $general->increaseVar('intel_exp', $value);
        } elseif ($this->crewType->armType == GameUnitConst::T_SIEGE) {   // 차병
            $general->increaseVar('leadership_exp', $value);
        } else {
            $general->increaseVar('strength_exp', $value);
        }
    }

    function addLevelExp(float $value)
    {
        $general = $this->general;
        if (!$this->isAttacker) {
            $value *= 0.8;
        }
        $general->addExperience($value);
    }

    function addDedication(float $value)
    {
        $general = $this->general;
        $general->addDedication($value);
    }

    function addLose()
    {
        $general = $this->general;
        $general->increaseRankVar(RankColumn::deathnum, 1);
        $this->addStatExp(1);
    }

    function computeWarPower()
    {
        [$warPower, $opposeWarPowerMultiply] = parent::computeWarPower();

        $general = $this->general;

        $expLevel = $general->getVar('explevel');

        if ($this->getOppose() instanceof WarUnitCity) {
            $warPower *= 1 + $expLevel / 600;
        } else {
            $warPower /= max(0.01, 1 - $expLevel / 300);
            $opposeWarPowerMultiply *= max(0.01, 1 - $expLevel / 300);
        }

        [$specialMyWarPowerMultiply, $specialOpposeWarPowerMultiply] = $this->general->getWarPowerMultiplier($this);
        $warPower *= $specialMyWarPowerMultiply;
        $opposeWarPowerMultiply *= $specialOpposeWarPowerMultiply;

        $this->warPower = $warPower;
        $this->oppose->setWarPowerMultiply($opposeWarPowerMultiply);
        return [$warPower, $opposeWarPowerMultiply];
    }

    function getHP(): int
    {
        return $this->general->getVar('crew');
    }

    function addDex(GameUnitDetail $crewType, float $exp)
    {
        $this->general->addDex($crewType, $exp, false);
    }

    function decreaseHP(int $damage): int
    {
        $general = $this->general;
        $damage = min($damage, $general->getVar('crew'));

        $this->dead += $damage;
        $this->deadCurr += $damage;
        $general->increaseVar('crew', -$damage);

        $addDex = $damage;
        if (!$this->isAttacker) {
            $addDex *= 0.1;
        }
        $this->addDex($this->oppose->getCrewType(), $addDex);

        if($this->oppose instanceof WarUnitGeneral){
            $this->deadPerson += $damage;
        }

        return $general->getVar('crew');
    }

    function calcRiceConsumption(int $damage): float
    {
        $rice = $damage / 100;
        if (!$this->isAttacker) {
            $rice *= 0.8;
        }
        if ($this->oppose instanceof WarUnitCity){
            $rice *= 0.8;
        }

        $rice *= $this->crewType->rice;
        $rice *= getTechCost($this->getNationVar('tech'));
        $rice = $this->general->onCalcStat($this->general, 'killRice', $rice);

        return $rice;
    }

    function increaseKilled(int $damage): int
    {
        $general = $this->general;
        $this->addLevelExp($damage / 50);

        $rice = $this->calcRiceConsumption($damage);

        $general->increaseVarWithLimit('rice', -$rice, 0);

        $addDex = $damage;
        if (!$this->isAttacker) {
            $addDex *= 0.8;
        }
        $this->addDex($this->getCrewType(), $addDex);

        $this->killed += $damage;
        $this->killedCurr += $damage;

        if($this->oppose instanceof WarUnitGeneral){
            $this->killedPerson += $damage;
        }
        return $this->killed;
    }

    function tryWound(): bool
    {
        $general = $this->general;
        if ($this->hasActivatedSkillOnLog('부상무효')) {
            return false;
        }
        if ($this->hasActivatedSkillOnLog('퇴각부상무효')) {
            return false;
        }
        if (!$this->rng->nextBool(0.05)) {
            return false;
        }

        $this->activateSkill('부상');

        $general->increaseVarWithLimit('injury', $this->rng->nextRangeInt(10, 80), null, 80);
        $this->getLogger()->pushGeneralActionLog("전투중 <R>부상</>당했다!", ActionLogger::PLAIN);

        return true;
    }

    function continueWar(&$noRice): bool
    {
        $general = $this->general;
        if ($this->getHP() <= 0) {
            $noRice = false;
            return false;
        }
        if ($general->getVar('rice') <= $this->getHP() / 100) {
            $noRice = true;
            return false;
        }
        return true;
    }

    function checkStatChange(): bool
    {
        return $this->general->checkStatChange();
    }

    function finishBattle()
    {
        if ($this->isFinished) {
            return;
        }
        $this->clearActivatedSkill();
        $this->isFinished = true;
        $general = $this->general;

        $general->increaseRankVar(RankColumn::killcrew, $this->killed);
        $general->increaseRankVar(RankColumn::deathcrew, $this->dead);

        if($this->killedPerson){
            $general->increaseRankVar(RankColumn::killcrew_person, $this->killedPerson);
        }
        if($this->deadPerson){
            $general->increaseRankVar(RankColumn::deathcrew_person, $this->deadPerson);
        }

        $general->updateVar('rice', Util::round($general->getVar('rice')));
        $general->updateVar('experience', Util::round($general->getVar('experience')));
        $general->updateVar('dedication', Util::round($general->getVar('dedication')));

        $this->checkStatChange();
    }

    function applyDB(\MeekroDB $db): bool
    {
        $affected = $this->getGeneral()->applyDB($db);
        $this->getLogger()->flush();
        return $affected;
    }
}
