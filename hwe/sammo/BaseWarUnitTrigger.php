<?php
namespace sammo;

abstract class BaseWarUnitTrigger extends ObjectTrigger{
    /** @var WarUnit $object */

    const TYPE_NONE = 0;
    const TYPE_ITEM  = 1;
    const TYPE_CONSUMABLE_ITEM = 2;

    protected $raiseType = self::TYPE_NONE;

    public function __construct(WarUnit $unit, int $raiseType = 0){
        $this->object = $unit;
        $this->raiseType = $raiseType;
    }

    public function getUniqueID():string{
        $priority = $this->priority;
        $fqn = static::class;
        return "{$priority}_{$fqn}_{$this->raiseType}";
    }

    public function action(?array $env=null, $arg=null):?array{
        if($env === null){
            $env = [];
        }
        if(!key_exists('e_attacker', $env)){
            $env['e_attacker'] = [];
        }
        if(!key_exists('e_defender', $env)){
            $env['e_defender'] = [];
        }

        if($env['stopNextAction']??false){
            return $env;
        }

        /** @var WarUnitGeneral $attacker */
        /** @var WarUnit $defender */
        [$attacker, $defender] = $arg;
        
        /** @var WarUnit $self */
        $self = $this->object;
        $isAttacker = $self->isAttacker();
        $oppose = $isAttacker?$defender:$attacker;

        $selfEnv = $isAttacker?$env['e_attacker']:$env['e_defender'];
        $opposeEnv = $isAttacker?$env['e_defender']:$env['e_attacker'];

        $callNextAction = $this->actionWar($self, $oppose, $selfEnv, $opposeEnv);

        $env['e_attacker'] = $isAttacker?$selfEnv:$opposeEnv;
        $env['e_defender'] = $isAttacker?$opposeEnv:$selfEnv;

        if($callNextAction){
            $env['stopNextAction'] = true;
        }

        return $env;
    }

    abstract protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool;

    public function processConsumableItem():bool{
        if(!($this->raiseType & static::TYPE_ITEM)){
            return false;
        }

        /** @var WarUnit $self */
        $self = $this->object;

        if($self->hasActivatedSkill('아이템사용')){
            return false;
        }

        $self->activateSkill('아이템사용');
        $item = $self->getGeneral()->getItem();
        $itemName = $item->getName();
        $itemRawName = $item->getRawName();
        $self->activateSkill($itemName);

        if (!($this->raiseType & static::TYPE_CONSUMABLE_ITEM)) {
            return false;
        }

        if($self->hasActivatedSkill('아이템소모')){
            return false;
        }

        $self->activateSkill('아이템소모');
        $josaUl = JosaUtil::pick($itemRawName, '을');
        $self->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        $self->general->deleteItem();

        return true;
    }
}
