<?php
namespace sammo\Event;

class EventHandler{

    private $condition = null;
    private $actions = [];

    public function __construct($rawCondition, $rawActions){
        $this->condition = Condition::build($rawCondition);
        foreach($rawActions as $rawAction){
            $this->actions[] = Action::build($rawAction);
        }
        
    }

    public function tryRunEvent(array $env){
        $result = $this->condition->eval($env);

        if(!$result['value']){
            return $result;
        }

        $resultAction = [];
        foreach($this->actions as $action){
            $resultAction[] = $action->run($env);
        }
        $result['action'] = $resultAction;

        return $result;
    }
}