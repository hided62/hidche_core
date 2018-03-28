<?php
namespace sammo\Event;

class EventHandler{

    private $condition = null;
    private $actions = [];

    public function __construct($rawCondition, $rawActions){
        $this->condition = Condition::build($rawCondition);
        foreach($rawActions as $rawAction){
            $this->condition = Action::build($rawAction);
        }
        
    }

    public function tryRunEvent(array $env=null){
        $result = $this->condition->eval($env);

        if(!$result['value']){
            return $result;
        }

        $resultAction = [];
        foreach($this->actions as $action){
            $resultAction[] = $action->run();
        }
        $result['action'] = $resultAction;

        return $result;
    }
}