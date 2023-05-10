<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    use LazyVarUpdater;

    protected $hp;

    protected $cityTrainAtmos;
    protected $onSiege = false;

    function __construct(public readonly RandUtil $rng, $raw, $rawNation, int $year, int $month, int $startYear){
        $general = new DummyGeneral(false);
        $general->setVar('city', $raw['city']);
        $general->setVar('nation', $raw['nation']);
        $general->initLogger($year, $month);
        $this->general = $general;
        $this->raw = $raw;
        $this->rawNation = $rawNation;

        $this->isAttacker = false;

        //도시 훈사. 181년 60, 201년 80, 221년 100, 231년 110(최대)
        $this->cityTrainAtmos = Util::clamp($year - $startYear + 59, 60, 110);

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

    function getCityTrainAtmos(): int{
        return $this->cityTrainAtmos;
    }

    function getName():string{
        return $this->getVar('name');
    }

    function getCityVar(string|\BackedEnum $key){
        if($key instanceof \BackedEnum){
            $key = $key->value;
        }
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
        return $this->cityTrainAtmos + $this->trainBonus;
    }

    function getComputedAtmos(){
        return $this->cityTrainAtmos + $this->atmosBonus;
    }

    function getHP():int{
        return $this->hp;
    }

    function setSiege(){
        $this->onSiege = true;
        $this->currPhase = 0;
        $this->prePhase = 0;
        $this->bonusPhase = 0;
    }

    function isSiege(): bool{
        return $this->onSiege;
    }

    function getDex(GameUnitDetail $crewType){
        return ($this->cityTrainAtmos - 60) * 7200;
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

        //본 공성이 아닌 경우에는 한대만 맞아줌
        if(!$this->onSiege){
            return false;
        }

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
        if($this->isFinished || !$this->onSiege){
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