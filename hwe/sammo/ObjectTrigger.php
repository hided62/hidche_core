<?php
namespace sammo;

abstract class ObjectTrigger{
    const PRIORITY_MAX   = 99999;
    const PRIORITY_BEGIN = 50000;
    const PRIORITY_PRE   = 40000;
    const PRIORITY_BODY  = 30000;
    const PRIORITY_POST  = 20000;
    const PRIORITY_FINAL = 10000;

    static protected $priority;
    protected $object = null;

    static public function getPriority():int{
        return static::$priority;
    }
    abstract public function action(?array $env=null, $arg=null):?array;
    public function getUniqueID():string{
        $priority = static::$priority;
        $hash = spl_object_hash($this->object);
        $fqn = static::class;
        return "{$priority}_{$fqn}_{$hash}";
    }
}