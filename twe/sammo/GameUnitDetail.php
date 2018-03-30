<?php
namespace sammo;

class GameUnitDetail{
    public $id;
    public $name;
    public $attack;
    public $defence;
    public $speed;
    public $avoid;
    public $cost;
    public $rice;
    public $recruitType;
    public $recruitCondition;
    public $recruitFirst;

    public function __construct(
        int $id,
        string $name, 
        int $attack,
        int $defence,
        int $speed,
        int $avoid,
        int $cost,
        int $rice,
        int $recruitType,
        int $recruitCondition,
        bool $recruitFirst
    ){
        $this->name = $name;
        $this->attack = $attack;
        $this->defence = $defence;
        $this->speed = $speed;
        $this->avoid = $avoid;
        $this->cost = $cost;
        $this->rice = $rice;
        $this->recruitType = $recruitType;
        $this->recruitCondition = $recruitCondition;
        $this->recruitFirst = $recruitFirst;
    }

    public function isValid($ownCities, $ownRegions, $relativeYear, $tech){
        if($relativeYear < 3 && !$this->recruitFirst){
            return false;
        }

        if($this->recruitType == 0){
            if($tech < $this->recruitCondition){
                return false;
            }
        }

        if($this->recruitType == 1){
            if(!key_exists($this->recruitCondition, $ownRegions)){
                return false;
            }
            if ($tech < 1000) {
                return false;
            }
        }

        if($this->recruitType == 2){
            $cityLevel = CityConst::byID($id)->level;
            $cityLevel = CityConst::$regionMap[$cityLevel];

            if(!key_exists($this->recruitCondition, $ownCities)){
                return false;
            }
            if($cityLevel == '특'){
                if ($tech < 3000) {
                    return false;
                }
            }
            else{ //if($cityLevel == '이')
                if ($tech < 2000) {
                    return false;
                }
            }
        }

        return true;
    }
}