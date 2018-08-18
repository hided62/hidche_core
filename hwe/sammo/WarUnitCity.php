<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    protected $raw;
    
    protected $logger;
    protected $crewType;

    protected $hp;
    protected $trainAtmos;

    protected $rice = 0;

    protected $updatedVar = [];

    function __construct($raw, $rawNation, $year, $month){
        $this->raw = $raw;
        $this->rawNation = $rawNation;

        $this->isAttacker = false;

        $this->logger = new ActionLogger(0, $raw['nation'], $year, $month);
        $this->crewType = GameUnitConst::byID($raw['crewtype']);

        $this->rice = $this->rawNation['rice'];

        $this->crewType = GameUnitConst::byID(GameUnitConst::T_CASTLE);

        $this->hp = $this->raw['def'] * 10;; 
        $this->trainAtmos = $this->raw['wall'] * 10;
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

    

    function increaseKilled(int $damage):int{
        $this->killed += $damage;
        return $this->killed;
    }

    function getHP():int{
        return $this->hp;
    }

    function decreaseHP(int $damage):int{
        $damage = min($damage, $this->hp);
        $this->dead += $damage;
        $this->hp -= $damage;
    }

    function continueWar(&$noRice):bool{
        //전투가 가능하면 true
        $noRice = false;
        if($this->getHP() <= 0){
            return false;
        }

        //도시 성벽은 쌀이 소모된다고 항복하지 않음
        return true;
    }

    function finishBattle(){
        $this->raw['def'] = Util::round($this->hp / 10);
        $this->updatedVar['def'] = true;
        $this->raw['wall'] = Util::round($this->trainAtmos / 10);
        $this->updatedVar['wall'] = true;

        $this->raw['dead'] += $this->dead;
        $this->updatedVar['dead'] = true;

        $decWealth = $this->dead / 10;
        $this->raw['agri'] = max(0, Util::round($this->raw['agri'] - $decWealth));
        $this->updatedVar['agri'] = true;
        $this->raw['comm'] = max(0, Util::round($this->raw['comm'] - $decWealth));
        $this->updatedVar['comm'] = true;
        $this->raw['secu'] = max(0, Util::round($this->raw['secu'] - $decWealth));
        $this->updatedVar['secu'] = true;
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