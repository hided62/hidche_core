<?php
namespace sammo\Event\Action;

//이전 RegNPC 함수를 EventAction으로 재구성
class RegNPC extends \sammo\Event\Action{

    private $npc;

    public function __construct(
        int $affinity, 
        string $name, 
        $picturePath, 
        int $nationID,
        $locatedCity, 
        int $leadership, 
        int $strength, 
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
            $strength, 
            $intel, 
            $birth, 
            $death, 
            $ego, 
            $char, 
            $text?:''
        );
    }

    public function run($env=null){
        $result = $this->npc->build($env);
        return [__CLASS__, $result];
    }

}