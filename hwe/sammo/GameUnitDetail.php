<?php
namespace sammo;

class GameUnitDetail{
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
        array $info
    ){
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
    }

    public function costWithTech(int $tech, int $crew=100):float{
        return $this->cost * getTechCost($tech) * $crew / 100;
    }

    public function getAttackCoef($armType):float{
        if($armType instanceof GameUnitDetail){
            $armType = $armType->armType;
        }
        assert(is_numeric($armType), '$armType should be int or GameUnitDetail');
        return $this->attackCoef[$armType]??1;
    }

    public function getDefenceCoef($armType):float{
        if($armType instanceof GameUnitDetail){
            $armType = $armType->armType;
        }
        assert(is_numeric($armType), '$armType should be int or GameUnitDetail');
        return $this->defenceCoef[$armType]??1;
    }

    public function getComputedAttack(array $general, int $tech){
        if($this->armType == GameUnitConst::T_CASTLE){
            assert(isset($general['def']) && isset($general['wall']), '도시 정보가 입력되어야 함');
            return ($general['def']*0.1 + $general['wall']*0.9) / 500 + 200;
        }

        if($this->armType == GameUnitConst::T_WIZARD){
            $ratio = getGeneralIntel($general, true, true, true)*2 - 40;
        }
        else if($this->armType == GameUnitConst::T_SIEGE){
            $ratio = getGeneralLeadership($general, true, true, true)*2 - 40;
        }
        else if($this->armType == GameUnitConst::T_MISC){
            $ratio = getGeneralIntel($general, true, true, true) +
                getGeneralLeadership($general, true, true, true) + 
                getGeneralPower($general, true, true, true);
            $ratio = $ratio*2/3 - 40;
        }
        else{
            $ratio = getGeneralPower($general, true, true, true)*2 - 40;
        }
        if($ratio < 10){
            $ratio = 10;
        }
        if($ratio > 100){
            $ratio = 50 + $ratio/2;
        }

        $att = $this->attack + getTechAbil($tech);
        return $att * $ratio / 100;
    }

    public function getComputedDefence(array $general, int $tech){
        if($this->armType == GameUnitConst::T_CASTLE){
            assert(isset($general['def']) && isset($general['wall']), '도시 정보가 입력되어야 함');
            return ($general['def']*0.1 + $general['wall']*0.9) / 500 + 200;
        }
        $def = $this->defence + getTechAbil($tech);
        $crew = ($general['crew'] / (7000 / 30)) + 70;
        return $def * $crew / 100;
    }

    public function getCriticalRatio(array $general){
        if($this->armType == GameUnitConst::T_CASTLE){
            //성벽은 필살을 사용하지 않는다.
            return 0;
        }

        //  무장 무력 : 65 5%, 70 10%, 75 15%, 80 20%
        //  지장 지력 : 65 5%, 70  8%, 75 10%, 80 13%
        //충차장 통솔:  65 5%, 70  8%, 75 10%, 80 13%
        if($this->armType == GameUnitConst::T_WIZARD){
            $mainstat = getGeneralIntel($general, false, true, true, false);
            $coef = 0.4;
        }
        else if($this->armType == GameUnitConst::T_SIEGE){
            $mainstat = getGeneralLeadership($general, false, true, true, false);
            $coef = 0.4;
        }
        else if($this->armType == GameUnitConst::T_MISC){
            $mainstat = getGeneralIntel($general, false, true, true, false) +
            getGeneralLeadership($general, false, true, true, false) +
            getGeneralPower($general, false, true, true, false);
            $mainstat /= 3;
            $coef = 0.4;
        }
        else{
            $mainstat = getGeneralPower($general, false, true, true, false);
            $coef = 0.5;
        }

        $ratio = max($mainstat - 65, 0);
        $ratio *= $coef;

        return max(50, $ratio);
    }

    public function isValid($ownCities, $ownRegions, $relativeYear, $tech){
        if($relativeYear < $this->reqYear){
            return false;
        }

        if($tech < $this->reqTech){
            return false;
        }

        if($this->reqCities !== null){
            $valid = false;
            foreach($this->reqCities as $reqCity){
                if(\key_exists($reqCity, $ownCities)){
                    $valid = true;
                    break;
                }
            }
            if(!$valid){
                return false;
            }
        }

        if($this->reqRegions !== null){
            $valid = false;
            foreach($this->reqRegions as $reqRegion){
                if(\key_exists($reqRegion, $ownRegions)){
                    $valid = true;
                    break;
                }
            }
            if(!$valid){
                return false;
            }
        }

        return true;
    }
}