<?php
namespace sammo;

abstract class ObjectTrigger{
    const PRIORITY_MIN   = 0;
    const PRIORITY_BEGIN = 10000;
    const PRIORITY_PRE   = 20000;
    const PRIORITY_BODY  = 30000;
    const PRIORITY_POST  = 40000;
    const PRIORITY_FINAL = 50000;

    static protected $priority;
    protected $object = null;

    static public function getPriority():int{
        return static::$priority;
    }
    abstract public function action(?array $env=null, $arg=null):?array;
    public function getUniqueID():string{
        $priority = static::$priority;
        $objID = spl_object_id($this->object);
        $fqn = static::class;
        return "{$priority}_{$fqn}_{$objID}";
    }
}