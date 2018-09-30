<?php
namespace sammo;
abstract class TriggerCaller{

    protected $triggerListByPriority = [];
    protected $sorted = false;

    abstract function checkValidTrigger(iObjectTrigger $trigger):bool;

    function __construct(?array $triggerList=null)
    {
        if(!$triggerList){
            return;
        }
        $this->merge($triggerList);
    }

    function merge(array $triggerList){
        foreach($triggerList as $trigger){
            if(!checkValidTrigger($trigger)){
                throw new \InvalidArgumentException('Invalid Trigger Type');
            }
            /** @var iObjectTrigger $trigger */
            $priority = $trigger->getPriority();
            $uniqueID = $trigger->getUniqueID();
            if(!key_exists($priority, $this->triggerListByPriority)){
                $this->triggerListByPriority[$priority] = [];
            }

            $subTriggerList = &$this->triggerListByPriority[$priority];

            if(key_exists($uniqueID, $subTriggerList)){
                continue;
            }
            $subTriggerList[$uniqueID] = $trigger;
        }
        $this->sorted = false;
    }

    function fire(?array $env = null, $arg = null):?array{
        if(!$this->sorted){
            krsort($this->triggerListByPriority);
            $this->sorted = true;
        }

        foreach($this->triggerListByPriority as $subTriggerList){
            /** @var iObjectTrigger[] $subTriggerList */
            foreach($subTriggerList as $trigger){
                $env = $trigger->action($env, $arg);
            }
        }
        return $env;
    }

}