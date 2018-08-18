<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    protected $logger;
    protected $crewType;

    protected $hp;

    protected $rice = 0;
    public $cityRate;

    protected $updatedVar = [];

    protected $year;
    protected $month;

    function __construct($raw, $rawNation, $year, $month, $cityRate){
        $this->raw = $raw;
        $this->rawNation = $rawNation;

        $this->year = $year;
        $this->month = $month;

        $this->isAttacker = false;
        $this->cityRate = $cityRate;

        $this->logger = new ActionLogger(0, $raw['nation'], $year, $month, false);
        $this->crewType = GameUnitConst::byID($raw['crewtype']);

        $this->rice = $this->getNationVar('rice');

        $this->crewType = GameUnitConst::byID(GameUnitConst::T_CASTLE);

        $this->hp = $this->getVar('def') * 10; 

        //수비자 보정
        if($raw['level'] == 1){
            $this->trainBonus += 5;
        }
        else if($raw['level'] == 3){
            $this->trainBonus += 5;
        }
    }

    function getName():string{
        return $this->getVar('name');
    }

    function calcDamage():int{
        $warPower = $this->getWarPower();
        $warPower *= Util::randRange(0.9, 1.1);
        return Util::round($warPower);
    }

    function increaseKilled(int $damage):int{
        $this->killed += $damage;
        return $this->killed;
    }

    function getComputedTrain(){
        return $this->cityRate + $this->trainBonus;
    }

    function getComputedAtmos(){
        return $this->cityRate + $this->atmosBonus;
    }

    function getHP():int{
        return $this->hp;
    }

    function decreaseHP(int $damage):int{
        $damage = min($damage, $this->hp);
        $this->dead += $damage;
        $this->hp -= $damage;
        $this->increaseVarWithLimit('wall', -$damage/20, 0);
        
        return $this->hp;
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

    function addWin(){
    }

    function addLose(){
    }

    function heavyDecreseWealth(){
        $this->multiplyVar('agri', 0.5);
        $this->multiplyVar('comm', 0.5);
        $this->multiplyVar('secu', 0.5);
    }

    function finishBattle(){
        $this->updateVar('def', Util::round($this->hp / 10));
        $this->updateVar('wall', Util::round($this->getVar('wall')));

        //NOTE: 전투로 인한 사망자는 여기서 처리하지 않음

        $decWealth = $this->dead / 10;
        $this->increaseVarWithLimit('agri', -$decWealth, 0);
        $this->increaseVarWithLimit('comm', -$decWealth, 0);
        $this->increaseVarWithLimit('secu', -$decWealth, 0);
    }

    function addConflict():bool{
        $conflict = Json::decode($this->getVar('conflict'));
        $oppose = $this->getOppose();

        $nationID = $oppose->getNationVar('nation');
        $newConflict = false;
        
        $dead = $this->dead;

        if(!$conflict || $this->getHP() == 0){ // 선타, 막타 보너스
            $dead *= 1.05;
        }

        if(!$conflict){
            $conflict = [$nationID => $dead];
        }
        else if(key_exists($nationID, $conflict)){
            $conflict[$nationID] += $dead;
            arsort($conflict);
        }
        else{
            $conflict[$nationID] = $dead;
            arsort($conflict);
            $newConflict = true;
        }

        $this->updateVar('conflict', Json::encode($conflict));

        return $newConflict;
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