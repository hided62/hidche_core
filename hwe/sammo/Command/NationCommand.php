<?php
namespace sammo\Command;

abstract class NationCommand extends BaseCommand{
    protected $lastTurn;
    protected $resultTurn;

    public function __construct(General $generalObj, array $env, LastTurn $lastTurn, $arg = null){
        $this->lastTurn = $lastTurn;
        $this->resultTurn = new LastTurn();
        parent::__construct($generalObj, $env, $arg);
    }

    public function getLastTurn():LastTurn{
        return $this->lastTurn;
    }

    public function setResultTurn(LastTurn $lastTurn){
        $this->resultTurn = $lastTurn;
    }

    public function getResultTurn():LastTurn{
        return $this->resultTurn;
    }
    

    
};