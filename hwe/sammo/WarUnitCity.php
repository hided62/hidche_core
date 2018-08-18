<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    protected $raw;
    
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

        $this->rice = $this->rawNation['rice'];

        $this->crewType = GameUnitConst::byID(GameUnitConst::T_CASTLE);

        $this->hp = $this->raw['def'] * 10; 

        //수비자 보정
        if($raw['level'] == 1){
            $this->trainBonus += 5;
        }
        else if($raw['level'] == 3){
            $this->trainBonus += 5;
        }
    }

    function getRaw():array{
        return $this->raw;
    }

    function getName():string{
        return $this->raw['name'];
    }

    function calcDamage():int{
        $warPower = $this->getWarPower();
        $warPower *= Util::randRange(0.9, 1.1);
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
        $this->raw['wall'] = max(0, $this->raw['wall'] - $damage / 20);
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
        $this->raw['agri'] *= 0.5;
        $this->updatedVar['agri'] = true;
        $this->raw['comm'] *= 0.5;
        $this->updatedVar['comm'] = true;
        $this->raw['secu'] *= 0.5;
        $this->updatedVar['secu'] = true;
    }

    function finishBattle(){
        $this->raw['def'] = Util::round($this->hp / 10);
        $this->updatedVar['def'] = true;
        Util::setRound($this->raw['wall']);
        $this->updatedVar['wall'] = true;

        //NOTE: 전투로 인한 사망자는 여기서 처리하지 않음

        $decWealth = $this->dead / 10;
        $this->raw['agri'] = max(0, Util::round($this->raw['agri'] - $decWealth));
        $this->updatedVar['agri'] = true;
        $this->raw['comm'] = max(0, Util::round($this->raw['comm'] - $decWealth));
        $this->updatedVar['comm'] = true;
        $this->raw['secu'] = max(0, Util::round($this->raw['secu'] - $decWealth));
        $this->updatedVar['secu'] = true;
    }

    function addConflict():bool{
        $conflict = Json::decode($this->raw['conflict']);
        $opposeNation = $this->getOppose()->getRawNation();

        $nationID = $opposeNation['nation'];
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

        $this->raw['conflict'] = Json::encode($conflict);
        $this->updatedVar['conflict'] = true;

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