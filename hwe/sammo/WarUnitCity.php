<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    use LazyVarUpdater;

    protected $hp;

    protected $cityRate;

    function __construct($raw, $rawNation, int $year, int $month, $cityRate){
        $general = new DummyGeneral(false);
        $general->setVar('city', $raw['city']);
        $general->setVar('nation', $raw['nation']);
        $general->initLogger($year, $month);
        $this->general = $general;
        $this->raw = $raw;
        $this->rawNation = $rawNation;

        $this->isAttacker = false;
        $this->cityRate = $cityRate;

        $this->logger = $general->getLogger();
        $this->crewType = GameUnitConst::byID(GameUnitConst::CREWTYPE_CASTLE);

        $this->hp = $this->getCityVar('def') * 10; 

        //수비자 보정
        if($this->getCityVar('level') == 1){
            $this->trainBonus += 5;
        }
        else if($this->getCityVar('level') == 3){
            $this->trainBonus += 5;
        }
    }

    function getName():string{
        return $this->getVar('name');
    }

    function getCityVar(string $key){
        return $this->raw[$key];
    }

    function getComputedAttack(){
        return ($this->raw['def'] + $this->raw['wall'] * 9) / 500 + 200;
    }

    function getComputedDefence(){
        return ($this->raw['def'] + $this->raw['wall'] * 9) / 500 + 200;
    }

    function increaseKilled(int $damage):int{
        $this->killed += $damage;
        $this->killedCurr += $damage;
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

    function getDex(GameUnitDetail $crewType){
        return ($this->cityRate - 60) * 7200;
    }

    function decreaseHP(int $damage):int{
        $damage = min($damage, $this->hp);
        $this->dead += $damage;
        $this->deadCurr += $damage;
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

    function heavyDecreaseWealth(){
        $this->multiplyVar('agri', 0.5);
        $this->multiplyVar('comm', 0.5);
        $this->multiplyVar('secu', 0.5);
    }

    function finishBattle(){
        if($this->isFinished){
            return;
        }
        $this->clearActivatedSkill();
        $this->isFinished = true;

        $this->updateVar('def', Util::round($this->getHP() / 10));
        $this->updateVar('wall', Util::round($this->getVar('wall')));

        //NOTE: 전투로 인한 사망자는 여기서 처리하지 않음

        $decWealth = $this->getKilled() / 20;
        $this->increaseVarWithLimit('agri', -$decWealth, 0);
        $this->increaseVarWithLimit('comm', -$decWealth, 0);
        $this->increaseVarWithLimit('secu', -$decWealth, 0);
    }

    function addConflict():bool{
        $conflict = Json::decode($this->getVar('conflict'));
        $oppose = $this->getOppose();

        $nationID = $oppose->getNationVar('nation');
        $newConflict = false;
        
        $dead = max(1, $this->dead);

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
    

    function applyDB(\MeekroDB $db):bool{
        $updateVals = $this->getUpdatedValues();
        $this->getLogger()->rollback(); //수비 도시의 로그는 기록하지 않음

        if(!$updateVals){
            return false;
        }
        
        $db->update('city', $updateVals, 'city=%i', $this->raw['city']);
        $this->flushUpdateValues();
        return $db->affectedRows() > 0;
    }

}