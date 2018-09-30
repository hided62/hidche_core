<?php
namespace sammo;

abstract class BaseGeneralTrigger extends ObjectTrigger{
    /** @var General $object */
    public function __construct(General $general){
        $this->object = $general;
    }
}
