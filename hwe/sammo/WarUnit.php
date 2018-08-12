<?php
namespace sammo;

class WarUnit{
    protected $rawGeneral;
    protected $logger;
    protected $crewType;

    protected $killed = 0;
    protected $death = 0;

    protected $updatedVar = [];


    function __construct($rawGeneral, $year, $month){
        $this->rawGeneral = $rawGeneral;
        $this->logger = new ActionLogger($rawGeneral['no'], $rawGeneral['nation'], $year, $month);
        $this->crewType = GameUnitConst::byID($rawGeneral['crewtype']);
    }
    
    function getCrewType():GameUnitConst{
        return $this->crewType;
    }

    function getLogger():ActionLogger{
        return $this->logger;
    }

    function getSpecialDomestic():int{
        return $this->rawGeneral['special'];
    }

    function getSpecialWar():int{
        return $this->rawGeneral['special2'];
    }

    function getItem():int{
        return $this->rawGeneral['item'];
    }

    function getMaxPhase():int{
        $phase = $this->getCrewType()->speed;
        if($this->getSpecialWar() == 60){
            $phase += 1;
        }
        return $phase;
    }

    function doUseBattleInitItem():bool{
        $item = $this->getItem();

        
    }




}