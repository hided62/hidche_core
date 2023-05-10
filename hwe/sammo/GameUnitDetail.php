<?php

namespace sammo;

class GameUnitDetail implements iAction
{
    use DefaultAction;

    public $id;
    public $armType;
    public $name;
    public $attack;
    public $defence;
    public $speed;
    public $avoid;
    public $magicCoef;
    public $cost;
    public $rice;
    public $reqTech;
    public $reqCities;
    public $reqRegions;
    public $reqYear;
    public $attackCoef;
    public $defenceCoef;
    public $info;
    public $initSkillTrigger;
    public $phaseSkillTrigger;
    /** @var iAction[]|null iActionList */
    public $iActionList;

    public function __construct(
        int $id,
        int $armType,
        string $name,
        int $attack,
        int $defence,
        int $speed,
        int $avoid,
        float $magicCoef,
        int $cost,
        int $rice,
        int $reqTech,
        ?array $reqCities,
        ?array $reqRegions,
        int $reqYear,
        array $attackCoef,
        array $defenceCoef,
        array $info,
        ?array $initSkillTrigger,
        ?array $phaseSkillTrigger,
        ?array $iActionList,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->armType = $armType;
        $this->attack = $attack;
        $this->defence = $defence;
        $this->speed = $speed;
        $this->avoid = $avoid;
        $this->magicCoef = $magicCoef;
        $this->cost = $cost;
        $this->rice = $rice;
        $this->reqTech = $reqTech;
        $this->reqCities = $reqCities;
        $this->reqRegions = $reqRegions;
        $this->reqYear = $reqYear;
        $this->attackCoef = $attackCoef;
        $this->defenceCoef = $defenceCoef;
        $this->info = $info;
        $this->initSkillTrigger = $initSkillTrigger;
        $this->phaseSkillTrigger = $phaseSkillTrigger;
        $this->iActionList = array_map('buildActionCrewTypeClass', $iActionList ?? []);
    }

    public function getInfo(): string
    {
        return join("\n<br>", $this->info);
    }

    public function getShortName(): string
    {
        return StringUtil::subStringForWidth($this->name, 0, 4);
    }

    public function riceWithTech(int $tech, int $crew = 100): float
    {
        return $this->rice * getTechCost($tech) * $crew / 100;
    }

    public function costWithTech(int $tech, int $crew = 100): float
    {
        return $this->cost * getTechCost($tech) * $crew / 100;
    }

    public function getAttackCoef(GameUnitDetail $opposeCrewType): float
    {
        $opposeCrewTypeID = $opposeCrewType->id;
        if (key_exists($opposeCrewTypeID, $this->attackCoef)) {
            return $this->attackCoef[$opposeCrewTypeID];
        }
        $opposeArmType = $opposeCrewType->armType;
        return $this->attackCoef[$opposeArmType] ?? 1;
    }

    public function getDefenceCoef(GameUnitDetail $opposeCrewType): float
    {
        $opposeCrewTypeID = $opposeCrewType->id;
        if (key_exists($opposeCrewTypeID, $this->defenceCoef)) {
            return $this->defenceCoef[$opposeCrewTypeID];
        }
        $opposeArmType = $opposeCrewType->armType;
        return $this->defenceCoef[$opposeArmType] ?? 1;
    }

    public function getComputedAttack(General $general, int $tech)
    {
        if ($this->armType == GameUnitConst::T_WIZARD) {
            $ratio = $general->getIntel(true, true, true) * 2 - 40;
        } else if ($this->armType == GameUnitConst::T_SIEGE) {
            $ratio = $general->getLeadership(true, true, true) * 2 - 40;
        } else if ($this->armType == GameUnitConst::T_MISC) {
            $ratio = $general->getIntel(true, true, true) +
                $general->getLeadership(true, true, true) +
                $general->getStrength(true, true, true);
            $ratio = $ratio * 2 / 3 - 40;
        } else {
            $ratio = $general->getStrength(true, true, true) * 2 - 40;
        }
        if ($ratio < 10) {
            $ratio = 10;
        }
        if ($ratio > 100) {
            $ratio = 50 + $ratio / 2;
        }

        $att = $this->attack + getTechAbil($tech);
        return $att * $ratio / 100;
    }

    public function getComputedDefence(General $general, int $tech)
    {
        $def = $this->defence + getTechAbil($tech);
        $crew = ($general->getVar('crew') / (7000 / 30)) + 70;
        return $def * $crew / 100;
    }

    public function getCriticalRatio(General $general)
    {
        if ($this->armType == GameUnitConst::T_CASTLE) {
            //성벽은 필살을 사용하지 않는다.
            return 0;
        }

        //  무장 무력 : 65 5%, 70 10%, 75 15%, 80 20%
        //  지장 지력 : 65 5%, 70  8%, 75 10%, 80 13%
        //충차장 통솔:  65 5%, 70  8%, 75 10%, 80 13%
        if ($this->armType == GameUnitConst::T_WIZARD) {
            $mainstat = $general->getIntel(false, true, true, false);
            $coef = 0.4;
        } else if ($this->armType == GameUnitConst::T_SIEGE) {
            $mainstat = $general->getLeadership(false, true, true, false);
            $coef = 0.4;
        } else if ($this->armType == GameUnitConst::T_MISC) {
            $mainstat = $general->getIntel(false, true, true, false) +
                $general->getLeadership(false, true, true, false) +
                $general->getStrength(false, true, true, false);
            $mainstat /= 3;
            $coef = 0.4;
        } else {
            $mainstat = $general->getStrength(false, true, true, false);
            $coef = 0.5;
        }

        $ratio = Util::valueFit($mainstat - 65, 0);
        $ratio *= $coef;

        return min(50, $ratio) / 100;
    }

    public function pickScore($tech)
    {
        $defaultWar = GameConst::$armperphase + $this->attack + $this->defence + getTechAbil($tech) * 2;
        $defaultWar *= 1 + $this->speed / 2;
        $defaultWar /= Util::valueFit(1 - $this->avoid / 100, 0.1);
        $defaultWar *= 1 + $this->magicCoef / 2;
        return $defaultWar;
    }

    public function isValid($ownCities, $ownRegions, $relativeYear, $tech)
    {
        //음수 없음
        $relativeYear = max(0, $relativeYear);

        if ($relativeYear < $this->reqYear) {
            return false;
        }

        if ($tech < $this->reqTech) {
            return false;
        }

        if ($this->reqCities !== null) {
            $valid = false;
            foreach ($this->reqCities as $reqCity) {
                if (\key_exists($reqCity, $ownCities)) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                return false;
            }
        }

        if ($this->reqRegions !== null) {
            $valid = false;
            foreach ($this->reqRegions as $reqRegion) {
                if (\key_exists($reqRegion, $ownRegions)) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                return false;
            }
        }

        return true;
    }

    //iAction
    public function getBattleInitSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        if (!$this->initSkillTrigger) {
            return null;
        }
        $triggerList = [];
        foreach ($this->initSkillTrigger as $triggerArgs) {
            if (is_string($triggerArgs)) {
                $typeName = $triggerArgs;
                $triggerList[] = buildWarUnitTriggerClass($typeName, $unit);
            } else {
                $typeName = $triggerArgs[0];
                //WarUnit 다음 인자는 $raiseType이며, 0이어야할 것이다
                $triggerArgs[0] = 0;
                $triggerList[] = buildWarUnitTriggerClass($typeName, $unit, $triggerArgs);
            }
        }
        return new WarUnitTriggerCaller(...$triggerList);
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        if (!$this->phaseSkillTrigger) {
            return null;
        }
        $triggerList = [];
        foreach ($this->phaseSkillTrigger as $triggerArgs) {
            if (is_string($triggerArgs)) {
                $typeName = $triggerArgs;
                $triggerList[] = buildWarUnitTriggerClass($typeName, $unit);
            } else {
                $typeName = $triggerArgs[0];
                //WarUnit 다음 인자는 $raiseType이며, 0이어야할 것이다
                $triggerArgs[0] = 0;
                $triggerList[] = buildWarUnitTriggerClass($typeName, $unit, $triggerArgs);
            }
        }
        return new WarUnitTriggerCaller(...$triggerList);
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        if (!$this->iActionList) {
            return $value;
        }

        foreach ($this->iActionList as $iAction) {
            $value = $iAction->onCalcDomestic($turnType, $varType, $value, $aux);
        }
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        if (!$this->iActionList) {
            return;
        }

        foreach ($this->iActionList as $iAction) {
            $value = $iAction->onCalcStat($general, $statName, $value, $aux);
        }
        return $value;
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        if (!$this->iActionList) {
            return;
        }

        foreach ($this->iActionList as $iAction) {
            $value = $iAction->onCalcOpposeStat($general, $statName, $value, $aux);
        }
        return $value;
    }
    public function onCalcStrategic(string $turnType, string $varType, $value)
    {
        if (!$this->iActionList) {
            return;
        }

        foreach ($this->iActionList as $iAction) {
            $value = $iAction->onCalcStrategic($turnType, $varType, $value);
        }
        return $value;
    }
    public function onCalcNationalIncome(string $type, $amount)
    {
        if (!$this->iActionList) {
            return;
        }

        foreach ($this->iActionList as $iAction) {
            $amount = $iAction->onCalcNationalIncome($type, $amount);
        }
        return $amount;
    }

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        if (!$this->iActionList) {
            return [1, 1];
        }

        $attack = 1;
        $defence = 1;
        foreach ($this->iActionList as $iAction) {
            $attack *= $iAction->getWarPowerMultiplier($unit)[0];
            $defence *= $iAction->getWarPowerMultiplier($unit)[1];
        }
        return [$attack, $defence];
    }

    public function onArbitraryAction(General $general, RandUtil $rng, string $actionType, ?string $phase = null, ?array $aux = null): null|array
    {
        if (!$this->iActionList) {
            return null;
        }

        foreach ($this->iActionList as $iAction) {
            $aux = $iAction->onArbitraryAction($general, $rng, $actionType, $phase, $aux);
        }
        return $aux;
    }
}
