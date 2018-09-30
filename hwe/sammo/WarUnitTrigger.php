<?php
namespace sammo;

abstract class WarUnitTrigger extends ObjectTrigger{
    abstract public function __construct(WarUnit $unit);
}
