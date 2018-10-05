<?php
namespace sammo;

class GeneralAI{
    protected $general;
    protected $nation;
    protected $dipState;
    protected $genType;
    protected $env;

    public function __construct(General $general){
        $db = DB::db();
        $this->general = $general;
        if($general->getRawCity() === null){
            $general->setRawCity($db->queryFirstRow('SELECT * FROM city WHERE city = %i', $general->getCityID()));
        }

    }

    public function chooseNationTurn($nationCommand, $nationArg):array{

    }

    public function chooseGeneralTurn($generalCommand, $generalArg):array{

    }
}