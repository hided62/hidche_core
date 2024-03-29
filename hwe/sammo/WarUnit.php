<?php

namespace sammo;

class WarUnit
{
    protected $general;
    protected $rawNation;

    protected $logger;
    protected $crewType;

    protected $killedCurr = 0;
    protected $killed = 0;
    protected $deadCurr = 0;
    protected $dead = 0;

    protected $isAttacker = false;

    protected $currPhase = 0;
    protected $prePhase = 0;
    protected $bonusPhase = 0;

    protected $atmosBonus = 0;
    protected $trainBonus = 0;

    protected $oppose;
    protected $warPower;
    protected $warPowerMultiply = 1.0;

    protected $activatedSkill = [];
    protected $logActivatedSkill = [];
    protected $isFinished = false;

    private function __construct(public readonly RandUtil $rng, General $general)
    {
        $this->rng = $rng;
        $this->general = $general;
    }

    /* XXX:Dirty wrapper */
    function getRaw(): array
    {
        return $this->general->getRaw();
    }

    function getVar(string $key)
    {
        return $this->general->getVar($key);
    }

    function touchVar(string $key): bool
    {
        return $this->general->touchVar($key);
    }

    function setVar(string $key, $value)
    {
        return $this->general->setVar($key, $value);
    }

    function updateVar(string $key, $value)
    {
        return $this->general->updateVar($key, $value);
    }

    function updateVarWithLimit(string $key, $value, $min = null, $max = null)
    {
        return $this->general->updateVarWithLimit($key, $value, $min, $max);
    }

    function increaseVar(string $key, $value)
    {
        return $this->general->increaseVar($key, $value);
    }

    function increaseVarWithLimit(string $key, $value, $min = null, $max = null)
    {
        return $this->general->increaseVarWithLimit($key, $value, $min, $max);
    }

    function multiplyVar(string $key, $value)
    {
        return $this->general->multiplyVar($key, $value);
    }

    function multiplyVarWithLimit(string $key, $value, $min = null, $max = null)
    {
        return $this->general->multiplyVarWithLimit($key, $value, $min, $max);
    }

    function getUpdatedValues(): array
    {
        return $this->general->getUpdatedValues();
    }

    function flushUpdateValues(): void
    {
        $this->general->flushUpdateValues();
    }

    protected function clearActivatedSkill()
    {
        foreach ($this->activatedSkill as $skillName => $state) {
            if (!$state) {
                continue;
            }

            if (!key_exists($skillName, $this->logActivatedSkill)) {
                $this->logActivatedSkill[$skillName] = 1;
            } else {
                $this->logActivatedSkill[$skillName] += 1;
            }
        }
        $this->activatedSkill = [];
    }

    function getActivatedSkillLog(): array
    {
        return $this->logActivatedSkill;
    }

    function getRawNation(): array
    {
        return $this->rawNation;
    }

    function getNationVar(string $key)
    {
        return $this->rawNation[$key];
    }

    function getPhase(): int
    {
        return $this->currPhase;
    }

    function getRealPhase(): int
    {
        return $this->prePhase + $this->currPhase;
    }

    function getName(): string
    {
        return 'EMPTY';
    }

    function isAttacker(): bool
    {
        return $this->isAttacker;
    }

    function getCrewType(): GameUnitDetail
    {
        return $this->crewType;
    }

    function getCrewTypeName(): string
    {
        return $this->getCrewType()->name;
    }

    function getCrewTypeShortName(): string
    {
        return $this->getCrewType()->getShortName();
    }

    function getLogger(): ActionLogger
    {
        $logger = $this->getGeneral()->getLogger();
        if ($logger === null) {
            throw new \RuntimeException();
        }
        return $logger;
    }

    function getKilled(): int
    {
        return $this->killed;
    }

    function getDead(): int
    {
        return $this->dead;
    }

    function getKilledCurrentBattle(): int
    {
        return $this->killedCurr;
    }

    function getDeadCurrentBattle(): int
    {
        return $this->deadCurr;
    }

    function getGeneral(): General
    {
        return $this->general;
    }

    function getMaxPhase(): int
    {
        $phase = $this->getCrewType()->speed;
        return $phase + $this->bonusPhase;
    }

    function setPrePhase(int $phase)
    {
        $this->prePhase = $phase;
    }

    function addPhase(int $phase = 1)
    {
        $this->currPhase += $phase;
    }

    function addBonusPhase(int $cnt)
    {
        $this->bonusPhase += $cnt;
    }

    function setOppose(?WarUnit $oppose)
    {
        $this->oppose = $oppose;
        $this->killedCurr = 0;
        $this->deadCurr = 0;
        $this->clearActivatedSkill();
    }

    function getOppose(): ?WarUnit
    {
        return $this->oppose;
    }

    function getWarPower()
    {
        return $this->warPower * $this->warPowerMultiply;
    }

    function getRawWarPower()
    {
        return $this->warPower;
    }

    function getWarPowerMultiply()
    {
        return $this->warPowerMultiply;
    }

    function setWarPowerMultiply($multiply = 1.0)
    {
        $this->warPowerMultiply = $multiply;
    }

    function multiplyWarPowerMultiply($multiply)
    {
        $this->warPowerMultiply *= $multiply;
    }

    function getComputedAttack()
    {
        return $this->getCrewType()->getComputedAttack($this->general, $this->getNationVar('tech'));
    }

    function getComputedDefence()
    {
        return $this->getCrewType()->getComputedDefence($this->general, $this->getNationVar('tech'));
    }

    function computeWarPower()
    {
        $oppose = $this->getOppose();
        $general = $this->general;
        $opposeGeneral = $oppose->getGeneral();

        $myAtt = $this->getComputedAttack();
        $opDef = $oppose->getComputedDefence();
        // 감소할 병사
        $warPower = GameConst::$armperphase + $myAtt - $opDef;
        $opposeWarPowerMultiply = 1.0;

        if ($warPower < 100) {
            //최소 전투력 50 보장
            $warPower = max(0, $warPower);
            $warPower = ($warPower + 100) / 2;
            $warPower = $this->rng->nextRangeInt($warPower, 100);
        }

        $warPower *= $this->getComputedAtmos();
        $warPower /= $oppose->getComputedTrain();

        $genDexAtt = $this->getDex($this->getCrewType(), true);

        $oppDexDef = $oppose->getDex($this->getCrewType(), false);

        $warPower *= getDexLog($genDexAtt, $oppDexDef);

        $warPower *= $this->getCrewType()->getAttackCoef($oppose->getCrewType());
        $opposeWarPowerMultiply *= $this->getCrewType()->getDefenceCoef($oppose->getCrewType());

        $this->warPower = $warPower;
        $this->oppose->setWarPowerMultiply($opposeWarPowerMultiply);

        return [$warPower, $opposeWarPowerMultiply];
    }

    function addTrain(int $train)
    {
        return;
    }

    function addAtmos(int $atmos)
    {
        return;
    }

    function addTrainBonus(int $trainBonus)
    {
        $this->trainBonus += $trainBonus;
    }

    function addAtmosBonus(int $atmosBonus)
    {
        $this->atmosBonus += $atmosBonus;
    }

    function getComputedTrain()
    {
        return GameConst::$maxTrainByCommand;
    }

    function getComputedAtmos()
    {
        return GameConst::$maxAtmosByCommand;
    }

    function getComputedCriticalRatio(): float
    {
        return $this->getCrewType()->getCriticalRatio($this->general);
    }

    function getComputedAvoidRatio(): float
    {
        return $this->getCrewType()->avoid / 100;
    }

    function addWin()
    {
    }

    function addLose()
    {
    }

    function getDex(GameUnitDetail $crewType)
    {
        throw new NotInheritedMethodException();
    }

    function finishBattle()
    {
        throw new NotInheritedMethodException();
    }

    function beginPhase(): void
    {
        $this->clearActivatedSkill();
        $this->computeWarPower();
    }

    function hasActivatedSkill(string $skillName): bool
    {
        return $this->activatedSkill[$skillName] ?? false;
    }

    function hasActivatedSkillOnLog(string $skillName): int
    {
        return ($this->logActivatedSkill[$skillName] ?? 0) + ($this->hasActivatedSkill($skillName) ? 1 : 0);
    }

    function activateSkill(...$skillNames)
    {
        foreach ($skillNames as $skillName) {
            $this->activatedSkill[$skillName] = true;
        }
    }

    function deactivateSkill(...$skillNames)
    {
        foreach ($skillNames as $skillName) {
            $this->activatedSkill[$skillName] = false;
        }
    }

    function getHP(): int
    {
        throw new NotInheritedMethodException();
    }

    function decreaseHP(int $damage): int
    {
        $this->dead += $damage;
        throw new NotInheritedMethodException();
    }

    function increaseKilled(int $damage): int
    {
        $this->killed += $damage;
        throw new NotInheritedMethodException();
    }

    function calcDamage(): int
    {
        $warPower = $this->getWarPower();
        $warPower *= $this->rng->nextRange(0.9, 1.1);
        return Util::round($warPower);
    }

    function tryWound(): bool
    {
        return false;
    }

    function continueWar(&$noRice): bool
    {
        //전투가 가능하면 true
        $noRice = false;
        return false;
    }

    function logBattleResult()
    {
        $this->getLogger()->pushBattleResultTemplate($this, $this->getOppose());
    }

    function criticalDamage(): float
    {
        $range = [1.3, 2.0];

        if($this instanceof WarUnitGeneral){
            $general = $this->general;
            $range = $general->onCalcStat($general, 'criticalDamageRange', $range);
        }
        //전특, 병종에 따라 필살 데미지가 달라질지도 모르므로 static 함수는 아닌 것으로
        return $this->rng->nextRange(...$range);
    }

    function applyDB(\MeekroDB $db): bool
    {
        throw new MustNotBeReachedException('Must be WarUnitCity or WarUnitGeneral');
    }
}
