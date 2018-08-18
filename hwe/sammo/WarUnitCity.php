<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    protected $raw;
    
    protected $logger;
    protected $crewType;

    protected $rice = 0;

    protected $updatedVar = [];

    protected $def;
    protected $wall;
    

    function __construct($raw, $rawNation, $year, $month){
        $this->raw = $raw;
        $this->rawNation = $rawNation;

        $this->isAttacker = false;

        $this->logger = new ActionLogger(0, $raw['nation'], $year, $month);
        $this->crewType = GameUnitConst::byID($raw['crewtype']);

        $this->def = $raw['def'] * 10;
        $this->wall = $raw['wall'] * 10;
        $this->rice = $this->rawNation['rice'];

        $this->crewType = GameUnitConst::byID(GameUnitConst::T_CASTLE);
    }

    function getRaw():array{
        return $this->raw;
    }

    function getName():string{
        return $this->raw['name'];
    }

    function tryAttackInPhase():int{
        $warPower = $this->getWarPower();
        $warPower *= Util::randRange(0.9, 1.1);
    }

    function continueWar(&$noRice):bool{
        //전투가 가능하면 true
        $noRice = false;
        if($this->def <= 0){
            return false;
        }

        //도시 성벽은 쌀이 소모된다고 항복하지 않음
        return true;
    }

    function applyDB($db):bool{
        $updateVals = [];
        foreach(array_keys($this->updatedVar) as $key){
            $updateVals[$key] = $this->raw[$key];
        }

        if(!$updateVals){
            return false;
        }
        
        $db->update('city', $updateVals, 'city=%i', $this->raw['city']);
        return $db->affectedRows() > 0;
    }

}