<?php
namespace sammo;

class TriggerInheritBuff implements iAction{
    use DefaultAction;

    protected array $inheritBuffList;

    public function __construct(array $inheritBuffList){
        $this->inheritBuffList = $inheritBuffList;
    }

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        return $value;
    }
}