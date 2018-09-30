<?php
namespace sammo;

abstract class BaseWarUnitTrigger extends ObjectTrigger{
    /** @var WarUnit $object */
    public function __construct(WarUnit $unit){
        $this->object = $unit;
    }
}
