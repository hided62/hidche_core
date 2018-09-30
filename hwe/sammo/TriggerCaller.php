<?php
namespace sammo;
abstract class TriggerCaller{

    protected $triggerListByPriority = [];
    protected $sorted = false;

    abstract function checkValidTrigger(iObjectTrigger $trigger):bool;

    function isEmpty():bool{
        return !$this->triggerListByPriority;
    }

    function __construct(?array $triggerList=null)
    {
        if(!$triggerList){
            return;
        }

        $sorted = true;
        $minPriority = iObjectTrigger::PRIORITY_MAX;

        foreach($triggerList as $trigger){
            if(!checkValidTrigger($trigger)){
                throw new \InvalidArgumentException('Invalid Trigger Type');
            }
            /** @var iObjectTrigger $trigger */
            $priority = $trigger->getPriority();
            $uniqueID = $trigger->getUniqueID();

            if($sorted){
                if($minPriority > $priority){
                    $minPriority = $priority;
                }
                else if($minPriority < $priority){
                    $sorted = false;
                }
            }

            if(!key_exists($priority, $this->triggerListByPriority)){
                $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
                continue;
            }

            $this->triggerListByPriority[$priority][$uniqueID] = $trigger;
        }

        if(!$sorted){
            krsort($this->triggerListByPriority);
        }
        
    }

    function append(iObjectTrigger $trigger){
        if(!checkValidTrigger($trigger)){
            throw new \InvalidArgumentException('Invalid Trigger Type');
        }
        $priority = $trigger->getPriority();
        $uniqueID = $trigger->getUniqueID();

        if(!$this->triggerListByPriority){
            $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
            return;
        }

        $lastKey = Util::array_last_key($this->triggerListByPriority);
        if($lastKey > $priority){
            $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
            return;
        }

        if(key_exists($priority, $this->triggerListByPriority)){
            $this->triggerListByPriority[$priority][$uniqueID] = $trigger;
        }

        $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
        krsort($this->triggerListByPriority);
    }

    function merge(?TriggerCaller $other){
        if($other === null){
            return;
        }

        $newTriggerList = [];
        $iterLhs = new \ArrayIterator($this->triggerListByPriority);
        $iterRhs = new \ArrayIterator($other->triggerListByPriority);

        while($iterLhs->valid() && $iterRhs->valid()){
            if($iterLhs->key() > $iterRhs->key()){
                $newTriggerList[$iterLhs->key()] = $iterLhs->current();
                $iterLhs->next();
                continue;
            }
            if($iterRhs->key() > $iterLhs->key()){
                $newTriggerList[$iterRhs->key()] = $iterRhs->current();
                $iterRhs->next();
                continue;
            }
            $newTriggerList[$iterLhs->key()] = $iterLhs->current() + $iterRhs->current();
            $iterLhs->next();
            $iterRhs->next();
        }

        while($iterLhs->valid()){
            $newTriggerList[$iterLhs->key()] = $iterLhs->current();
            $iterLhs->next();
        }

        while($iterRhs->valid()){
            $newTriggerList[$iterRhs->key()] = $iterRhs->current();
            $iterRhs->next();
        }

        $this->triggerListByPriority = $newTriggerList;
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