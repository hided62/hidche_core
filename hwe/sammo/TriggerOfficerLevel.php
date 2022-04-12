<?php
namespace sammo;

class TriggerOfficerLevel implements iAction{
    use DefaultAction;

    protected $officerLevel;
    protected $nationLevel;
    protected $lbonus;

    public function __construct(array $general, int $nationLevel){
        $this->officerLevel = $general['officer_level'];

        $this->nationLevel = $nationLevel;

        if(2 <= $this->officerLevel && $this->officerLevel <= 4 && $general['officer_city'] != $general['city']){
            $this->officerLevel = 1;
        }

        $this->lbonus = calcLeadershipBonus($this->officerLevel, $nationLevel);
    }

    public function getLbonus():int{
        return $this->lbonus;
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($varType == 'score'){
            if($turnType == '농업' || $turnType == '상업'){
                if(in_array($this->officerLevel, [12, 11, 9, 7, 5, 3])){
                    return $value * 1.05;
                }
            }
            else if($turnType == '기술'){
                if(in_array($this->officerLevel, [12, 11, 9, 7, 5])){
                    return $value * 1.05;
                }
            }
            else if($turnType == '민심' || $turnType == '인구'){
                if(in_array($this->officerLevel, [12, 11, 2])){
                    return $value * 1.05;
                }
            }
            else if($turnType == '수비' || $turnType == '성벽' || $turnType == '치안'){
                if(in_array($this->officerLevel, [12, 11, 10, 8, 6, 4])){
                    return $value * 1.05;
                }
            }
        }


        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        if($statName == 'leadership'){
            return $value + $this->lbonus;
        }
        return $value;
    }

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        $officerLevel = $this->officerLevel;
        $warPowerMultiply = 1;
        $opposeWarPowerMultiply = 1;
        if ($officerLevel == 12) {
            $warPowerMultiply = 1.05;
            $opposeWarPowerMultiply = 0.95;
        }
        else if($officerLevel == 11){
            $warPowerMultiply = 1.03;
            $opposeWarPowerMultiply = 0.97;
        }
        else if(in_array($officerLevel, [10, 8, 6])){
            $warPowerMultiply = 1.03;
        }
        else if(in_array($officerLevel, [9, 7, 5])){
            $opposeWarPowerMultiply = 0.97;
        }
        else if(in_array($officerLevel, [4, 3, 2])){
            $warPowerMultiply = 1.015;
            $opposeWarPowerMultiply = 0.985;
        }
        return [$warPowerMultiply, $opposeWarPowerMultiply];
    }
}