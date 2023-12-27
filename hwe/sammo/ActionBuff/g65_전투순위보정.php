<?php

namespace sammo\ActionBuff;

use sammo\GameUnitConst;
use sammo\General;
use \sammo\iAction;

class g65_전투순위보정 implements iAction
{
    use \sammo\DefaultAction;

    function onCalcStat(General $defender, string $statName, $value, $aux = null)
    {
        //방어자 입장에서 coef가 높다면 전투순위를 높인다.
        if ($statName == 'battleOrder') {
            if($aux === null) throw new \RuntimeException('전투순위보정에 필요한 aux가 없습니다.');
            /** @var General */
            $attacker = $aux['attacker'];
            $defenderCrewType = $defender->getCrewTypeObj();
            $attackerCrewType = $attacker->getCrewTypeObj();

            $attackerCoef = $attackerCrewType->getAttackCoef($defenderCrewType) * $defenderCrewType->getDefenceCoef($attackerCrewType);
            $defenderCoef = $defenderCrewType->getAttackCoef($attackerCrewType) * $attackerCrewType->getDefenceCoef($defenderCrewType);

            if($attackerCoef > $defenderCoef){
                return $value * 3;
            } else if($attackerCoef < $defenderCoef){
                return $value / 3;
            }
        }
        return $value;
    }

    function onCalcOpposeStat(General $defender, string $statName, $value, $aux = null)
    {
        //공격자 입장에서 coef가 높다면 전투순위를 높인다.
        if ($statName == 'battleOrder') {
            if($aux === null) throw new \RuntimeException('전투순위보정에 필요한 aux가 없습니다.');
            /** @var General */
            $attacker = $aux['attacker'];
            $defenderCrewType = $defender->getCrewTypeObj();
            $attackerCrewType = $attacker->getCrewTypeObj();

            $attackerCoef = $attackerCrewType->getAttackCoef($defenderCrewType) * $defenderCrewType->getDefenceCoef($attackerCrewType);
            $defenderCoef = $defenderCrewType->getAttackCoef($attackerCrewType) * $attackerCrewType->getDefenceCoef($defenderCrewType);

            if($attackerCoef < $defenderCoef){
                return $value * 3;
            } else if($attackerCoef > $defenderCoef){
                return $value / 3;
            }
        }
        return $value;
    }
}
