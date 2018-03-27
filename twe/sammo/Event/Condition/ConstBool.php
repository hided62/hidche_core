<?php
namespace sammo\Event\Condition;

class ConstBool extends sammo\Event\Condition{
    private $fixedResult = true;

    public function __construct(bool $value){
        $this->fixedResult = $value;
    }

    public function eval($env=null){
        return [
            'value'=>$this->fixedResult,
            'chain'=>['ConstBool']
        ];
    }
}