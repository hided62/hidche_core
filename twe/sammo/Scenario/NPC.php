<?php
namespace sammo\Scenario;
use \sammo\Util;
use \sammo\DB;
use \sammo\CityHelper;

class NPC{

    public $affinity; 
    public $name; 
    public $npcID; 
    public $nationID; 
    public $locatedCity; 
    public $leadership; 
    public $power; 
    public $intel; 
    public $birth; 
    public $death; 
    public $ego;
    public $charDomestic = 0; 
    public $charWar = 0; 
    public $text;

    public function __construct(
        int $affinity, 
        string $name, 
        int $npcID, 
        int $nationID, 
        string $locatedCity, 
        int $leadership, 
        int $power, 
        int $intel, 
        int $birth = 150, 
        int $death = 300, 
        string $ego = null,
        string $char = null, 
        string $text = null
    ){
        $this->affinity = $affinity;
        $this->name = $name;
        $this->npcID = $npcID;
        $this->nationID = $nationID;
        $this->locatedCity = $locatedCity;
        $this->leadership = $leadership;
        $this->power = $power;
        $this->intel = $intel;
        $this->birth = $birth;
        $this->death = $death;
        $this->ego = $ego;
        $this->text = $text;

        $char = \sammo\SpecCall($char);
        if($char < 40){
            $this->charDomestic = $char;
        }
        else{
            $this->charWar = $char;
        }
    }


    public function build($env=[]){

    }
}