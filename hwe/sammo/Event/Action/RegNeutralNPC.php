<?php
namespace sammo\Event\Action;

use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\Util;

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

        $rng = new RandUtil(new LiteHashDRBG(bin2hex(random_bytes(16))));
        $this->npc=(new \sammo\Scenario\GeneralBuilder(
            $rng,
            $name,
            false,
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

    public function run(array $env){
        $result = $this->npc->fillRemainSpecAsZero($env)->build($env);
        return [__CLASS__, $result];
    }

}