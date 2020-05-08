<?php
namespace sammo;
abstract class TriggerCaller{

    protected $triggerListByPriority = [];

    abstract function checkValidTrigger(ObjectTrigger $trigger):bool;

    function isEmpty():bool{
        return !$this->triggerListByPriority;
    }

    function __construct(ObjectTrigger ...$triggerList)
    {
        if(!$triggerList){
            return;
        }

        $sorted = true;
        $maxPriority = ObjectTrigger::PRIORITY_MIN;

        foreach($triggerList as $trigger){
            if(!$this->checkValidTrigger($trigger)){
                throw new \InvalidArgumentException('Invalid Trigger Type');
            }
            /** @var ObjectTrigger $trigger */
            $priority = $trigger->getPriority();
            $uniqueID = $trigger->getUniqueID();

            if($sorted){
                if($maxPriority < $priority){
                    $maxPriority = $priority;
                }
                else if($maxPriority > $priority){
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
            ksort($this->triggerListByPriority);
        }
        
    }

    function append(ObjectTrigger $trigger):self{
        if(!$this->checkValidTrigger($trigger)){
            throw new \InvalidArgumentException('Invalid Trigger Type');
        }
        $priority = $trigger->getPriority();
        $uniqueID = $trigger->getUniqueID();

        if(!$this->triggerListByPriority){
            $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
            return $this;
        }

        $lastKey = Util::array_last_key($this->triggerListByPriority);
        if($lastKey < $priority){
            $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
            return $this;
        }

        if(key_exists($priority, $this->triggerListByPriority)){
            $this->triggerListByPriority[$priority][$uniqueID] = $trigger;
        }

        $this->triggerListByPriority[$priority] = [$uniqueID=>$trigger];
        ksort($this->triggerListByPriority);
        return $this;
    }

    function merge(?TriggerCaller $other):self{
        if($other === null){
            return $this;
        }

        //NOTE: array_merge로 계속 가야하는가? merge가 많으면 SPL의 LinkedList가 낫지 않나?

        $newTriggerList = [];
        $iterLhs = new \ArrayIterator($this->triggerListByPriority);
        $iterRhs = new \ArrayIterator($other->triggerListByPriority);

        while($iterLhs->valid() && $iterRhs->valid()){
            if($iterLhs->key() < $iterRhs->key()){
                $newTriggerList[$iterLhs->key()] = $iterLhs->current();
                $iterLhs->next();
                continue;
            }
            if($iterRhs->key() < $iterLhs->key()){
                $newTriggerList[$iterRhs->key()] = $iterRhs->current();
                $iterRhs->next();
                continue;
            }
            $newTriggerList[$iterLhs->key()] = array_merge($iterLhs->current(), $iterRhs->current());
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
        return $this;
    }

    function fire(?array $env = null, $arg = null):?array{
        foreach($this->triggerListByPriority as $priority=>$subTriggerList){
            /** @var ObjectTrigger[] $subTriggerList */
            foreach($subTriggerList as $trigger){
                $env = $trigger->action($env, $arg);
            }
        }
        return $env;
    }

}