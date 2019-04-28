<?php
namespace sammo;

abstract class BaseWarUnitTrigger extends ObjectTrigger{
    /** @var WarUnit $object */

    const TYPE_NONE = 0;
    const TYPE_ITEM  = 1;
    const TYPE_CONSUMABLE_ITEM = 2;

    protected $raiseType;

    public function __construct(WarUnit $unit, int $raiseType = 0){
        $this->object = $unit;
        $this->raiseType = $raiseType;
    }

    public function action(?array $env=null, $arg=null):?array{
        /** @var WarUnitGeneral $attacker */
        /** @var WarUnit $defender */
        [$attacker, $defender] = $arg;

        
        /** @var WarUnit $self */
        $self = $this->object;
        $isAttacker = $self->isAttacker();
        $oppose = $isAttacker?$defender:$attacker;

        if($env === null){
            $env = [];
        }
        if(!key_exists('e_attacker', $env)){
            $env['e_attacker'] = [];
        }
        if(!key_exists('e_defender', $env)){
            $env['e_defender'] = [];
        }

        $selfEnv = $isAttacker?$env['e_attacker']:$env['e_defender'];
        $opposeEnv = $isAttacker?$env['e_defender']:$env['e_attacker'];

        $this->actionWar($self, $oppose, $selfEnv, $opposeEnv);

        $env['e_attacker'] = $isAttacker?$selfEnv:$opposeEnv;
        $env['e_defender'] = $isAttacker?$opposeEnv:$selfEnv;

        return $env;
    }

    abstract protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):void;
}
