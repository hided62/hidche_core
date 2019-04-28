<?php
namespace sammo\WarInitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;

class che_저격시도 extends BaseWarUnitTrigger{
    static protected $priority = 20000;

    protected $woundMin;
    protected $woundMax;

    public function __construct(WarUnit $unit, int $raiseType, float $woundMin, float $woundMax){
        $this->object = $unit;
        $this->raiseType = $raiseType;
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):void{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        if(!$oppose instanceof WarUnitGeneral){
            return;
        }
        if($self->hasActivatedSkill('저격')){
            return;
        }
        if($self->hasActivatedSkill('저격불가')){
            return;
        }
        if(!Util::randBool(1/5)){
            return;
        }

        $this->activateSkill('저격');
        $selfEnv['woundMin'] = $this->woundMin;
        $selfEnv['woundMax'] = $this->woundMax;

        if(!($this->raiseType & static::TYPE_ITEM)){
            return;
        }

        $self->activateSkill('아이템사용');
        $item = $self->getGeneral()->getItem();
        $itemName = $item->getName();
        $self->activateSkill($itemName);

        if (!($this->raiseType & static::TYPE_CONSUMABLE_ITEM)) {
            return;
        }

        $self->activateSkill('아이템소모');
        $josaUl = JosaUtil::pick($itemName, '을');
        $self->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        $self->general->deleteItem();
    }
}