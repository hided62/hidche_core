<?php
namespace sammo\Event\Action;

class RegNeutralNPC extends \sammo\Event\Action{

    private $npc;

    public function __construct(
        int $affinity, 
        string $name, 
        $picturePath, 
        int $nationID,
        $locatedCity, 
        int $leadership, 
        int $power, 
        int $intel, 
        int $birth = 160, 
        int $death = 300, 
        $ego = null,
        $char = '', 
        $text = ''
    ){
        $this->npc = new \sammo\Scenario\NPC(
            $affinity, 
            $name, 
            $picturePath, 
            $nationID, 
            $locatedCity, 
            $leadership, 
            $power, 
            $intel, 
            $birth, 
            $death, 
            $ego, 
            $char, 
            $text?:''
        );
        $this->npc = 6;
    }

    public function run($env=null){
        $result = $this->npc->build($env);
        return [__CLASS__, $result];
    }

}