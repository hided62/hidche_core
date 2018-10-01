<?php
namespace sammo\Command;

abstract class NationCommand extends BaseCommand{
    protected $lastTurn;

    public function setLast($lastTurn){
        $this->lastTurn = $lastTurn;
    }

    public function getLast():array{
        return $this->lastTurn;
    }
};