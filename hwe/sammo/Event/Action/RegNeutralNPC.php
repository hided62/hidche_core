<?php
namespace sammo\Event\Action;

class RegNeutralNPC extends \sammo\Event\Action{

    /** @var \sammo\Scenario\GeneralBuilder */
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
        $this->npc=(new \sammo\Scenario\GeneralBuilder(
            $name, 
            0,
            $picturePath, 
            $nationID 
        ))
        ->setCity($locatedCity)
        ->setStat($leadership, $strength, $intel)
        ->setEgo($ego)
        ->setSpecialSingle($char)
        ->setNPCText($text?:'')
        ->setAffinity($affinity)
        ->setLifeSpan($birth, $death)
        ->setNPCType(6);
    }

    public function run($env=null){
        $result = $this->npc->fillRemainSpecAsZero($env)->build($env);
        return [__CLASS__, $result];
    }

}