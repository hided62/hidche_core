<?php
namespace sammo;

abstract class ObjectTrigger{
    const PRIORITY_MIN   = 0;
    const PRIORITY_BEGIN = 10000;
    const PRIORITY_PRE   = 20000;
    const PRIORITY_BODY  = 30000;
    const PRIORITY_POST  = 40000;
    const PRIORITY_FINAL = 50000;

    protected $priority;
    protected $object = null;

    public function getPriority():int{
        return $this->priority;
    }

    public function setPriority(int $newPriority):self{
        $this->priority = $newPriority;
        return $this;
    }

    abstract public function action(?array $env=null, $arg=null):?array;
    public function getUniqueID():string{
        $priority = $this->priority;
        $fqn = static::class;
        return "{$priority}_{$fqn}";
    }
}