<?php
namespace sammo;

class TriggerGeneralLevel implements iAction{
    use DefaultAction;

    protected $generalLevel;
    protected $nationLevel;
    protected $lbonus;

    public function __construct(array $general, int $nationLevel, ?array $city){
        $this->generalLevel = $general['level'];

        $this->nationLevel = $nationLevel;

        if($city === null){
            if(2 <= $this->generalLevel || $this->generalLevel <= 4){
                $this->generalLevel = 1;
            }
        }
        else{
            if($this->generalLevel == 2){
                if($city['gen3'] != $general['no']){
                    $this->generalLevel = 1;
                }
            }
            else if($this->generalLevel == 3){
                if($city['gen2'] != $general['no']){
                    $this->generalLevel = 1;
                }
            }
            else if($this->generalLevel == 4){
                if($city['gen1'] != $general['no']){
                    $this->generalLevel = 1;
                }
            }
        }

        $this->lbonus = calcLeadershipBonus($this->generalLevel, $nationLevel);
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '농업' || $turnType == '상업'){
            if(in_array($this->generalLevel, [12, 11, 9, 7, 5, 3])){
                return $value * 1.05;
            }
        }
        else if($turnType == '기술'){
            if(in_array($this->generalLevel, [12, 11, 9, 7, 5])){
                return $value * 1.05;
            }
        }
        else if($turnType == '민심' || $turnType == '인구'){
            if(in_array($this->generalLevel, [12, 11, 2])){
                return $value * 1.05;
            }
        }
        else if($turnType == '수비' || $turnType == '성벽' || $turnType == '치안'){
            if(in_array($this->generalLevel, [12, 11, 10, 8, 6, 4])){
                return $value * 1.05;
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
}