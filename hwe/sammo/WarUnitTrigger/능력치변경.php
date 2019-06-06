<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;

class 능력치변경 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_BEGIN;

    protected $variable;
    protected $operator;
    protected $value;
    protected $limitMin;
    protected $limitMax;


    public function __construct(WarUnit $unit, int $raiseType, string $variable, string $operator, $value, $limitMin=null, $limitMax=null){
        $this->object = $unit;
        $this->raiseType = $raiseType;
        $this->variable = $variable;
        $this->operator = $operator;
        $this->value = $value;
        $this->limitMin = $limitMin;
        $this->limitMax = $limitMax;

        if(!in_array($this->operator, ['=', '+', '-', '*', '/'])){
            throw new \InvalidArgumentException("올바르지 않은 operator : {$operator}");
        }
    }

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');
        $general = $self->getGeneral();
        if($this->operator === '='){
            $general->setVar($this->variable, $this->value);
        }
        else if($this->operator === '+'){
            $general->increaseVarWithLimit($this->variable, $this->value, $this->limitMin, $this->limitMax);
        }
        else if($this->operator === '-'){
            $general->increaseVarWithLimit($this->variable, -$this->value, $this->limitMin, $this->limitMax);
        }
        else if($this->operator === '*'){
            $general->multiplyVarWithLimit($this->variable, $this->value, $this->limitMin, $this->limitMax);
        }
        else if($this->operator === '/'){
            $general->multiplyVarWithLimit($this->variable, 1/$this->value, $this->limitMin, $this->limitMax);
        }
        else{
            throw new \sammo\MustNotBeReachedException();
        }

        $this->processConsumableItem();
        
        return true;
    }
}