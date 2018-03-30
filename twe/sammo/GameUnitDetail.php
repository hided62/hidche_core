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
    public $recruitPlace;
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
        int $recruitPlace,
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
        $this->recruitPlace = $recruitPlace;
        $this->recruitFirst = $recruitFirst;
    }
}