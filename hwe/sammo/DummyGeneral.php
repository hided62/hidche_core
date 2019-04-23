<?php

namespace sammo;
class DummyGeneral extends General{
    public function __construct(bool $initLogger=true){
        $raw = [
            'no'=>0,
            'name'=>'Dummy',
            'city'=>0,
            'nation'=>0,
            'level'=>0,
        ];

        $this->raw = $raw;

        $this->resultTurn = new LastTurn();

        if($initLogger){
            $this->logger = new ActionLogger(
                $this->getVar('no'), 
                $this->getVar('nation'), 
                1, 
                1,
                false
            );
        }
    }

    function applyDB($db):bool{
        return true;
    }
}