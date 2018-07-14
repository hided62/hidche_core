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
    public $cost;
    public $rice;
    public $reqTech;
    public $reqCities;
    public $reqRegions;
    public $reqYear;
    public $info;

    public function __construct(
        int $id,
        int $armType,
        string $name, 
        int $attack,
        int $defence,
        int $speed,
        int $avoid,
        int $cost,
        int $rice,
        int $reqTech,
        ?array $reqCities,
        ?array $reqRegions,
        int $reqYear,
        array $info
    ){
        $this->name = $name;
        $this->armType = $armType;
        $this->attack = $attack;
        $this->defence = $defence;
        $this->speed = $speed;
        $this->avoid = $avoid;
        $this->cost = $cost;
        $this->rice = $rice;
        $this->reqTech = $reqTech;
        $this->reqCities = $reqCities;
        $this->reqRegions = $reqRegions;
        $this->reqYear = $reqYear;
        $this->info = $info;
    }

    public function costWithTech(int $tech, int $crew=100):float{
        return $this->cost * getTechCost($tech) * $crew / 100;
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