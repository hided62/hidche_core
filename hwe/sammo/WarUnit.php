<?php
namespace sammo;

class WarUnit{
    protected $raw;
    protected $rawNation;

    protected $logger;
    protected $crewType;

    protected $killed = 0;
    protected $dead = 0;

    protected $isAttacker = false;

    protected $updatedVar = [];

    protected $currPhase = 0;
    protected $prePhase = 0;

    protected $atmosBonus = 0;
    protected $trainBonus = 0;

    protected $oppose;
    protected $warPower;
    protected $warPowerMultiply = 1.0;

    protected $activatedSkill = [];

    private function __construct(){
    }
    
    function getRaw():array{
        return $this->raw;
    }

    function getRawNation():array{
        return $this->rawNation;
    }

    function getVar(string $key){
        return $this->raw[$key];
    }

    function updateVar(string $key, $value){
        if($this->raw[$key] === $value){
            return;
        }
        $this->raw[$key] = $value;
        $this->updatedVar[$key] = true;
    }

    function updateVarWithLimit(string $key, $value, $min = null, $max = null){
        if($min !== null && $value < $min){
            $value = $min;
        }
        if($max !== null && $value > $max){
            $value = $max;
        }
        $this->updateVar($key, $value);
    }

    function increaseVar(string $key, $value)
    {
        if($value === 0){
            return;
        }
        $this->raw[$key] += $value;
        $this->updatedVar[$key] = true;
    }

    function increaseVarWithLimit(string $key, $value, $min = null, $max = null){
        $targetValue = $this->raw[$key] + $value;
        if($min !== null && $targetValue < $min){
            $value = $min;
        }
        if($max !== null && $targetValue > $max){
            $value = $max;
        }
        $this->updateVar($key, $targetValue);
    }

    function multiplyVar(string $key, $value)
    {
        if($value === 1){
            return;
        }
        $this->raw[$key] *= $value;
        $this->updatedVar[$key] = true;
    }

    function multiplyVarWithLimit(string $key, $value, $min = null, $max = null){
        $targetValue = $this->raw[$key] * $value;
        if($min !== null && $targetValue < $min){
            $value = $min;
        }
        if($max !== null && $targetValue > $max){
            $value = $max;
        }
        $this->updateVar($key, $targetValue);
    }

    function getNationVar(string $key):array{
        return $this->rawNation[$key];
    }

    function getPhase():int{
        return $this->currPhase;
    }

    function getRealPhase():int{
        return $this->prePhase + $this->currPhase;
    }

    function getName():string{
        return 'EMPTY';
    }

    function isAttacker():bool{
        return $this->isAttacker;
    }

    function getCrewType():GameUnitDetail{
        return $this->crewType;
    }

    function getCrewTypeName():string{
        return $this->getCrewType()->name;
    }

    function getCrewTypeShortName():string{
        return $this->getCrewType()->getShortName();
    }

    function getLogger():ActionLogger{
        return $this->logger;
    }

    function getKilled():int{
        return $this->killed;
    }

    function getDead():int{
        return $this->dead;
    }

    function getSpecialDomestic():int{
        return 0;
    }

    function getSpecialWar():int{
        return 0;
    }

    function getItem():int{
        return 0;
    }

    function getMaxPhase():int{
        return $this->getCrewType()->speed;
    }

    function setPrePhase(int $phase){
        $this->prePhase = $phase;
    }

    function addPhase(){
        $this->currPhase += 1;
    }

    function setOppose(?WarUnit $oppose){
        $this->oppose = $oppose;
        $this->activatedSkill = [];
    }

    function getOppose():?WarUnit{
        return $this->oppose;
    }

    function getWarPower(){
        return $this->warPower * $this->warPowerMultiply;
    }

    function getRawWarPower(){
        return $this->warPower;
    }

    function getWarPowerMultiply(){
        return $this->warPowerMultiply;
    }
    
    function setWarPowerMultiply($multiply = 1.0){
        $this->warPowerMultiply = $multiply;
    }

    function computeWarPower(){
        $oppose = $this->oppose;

        $myAtt = $this->getCrewType()->getComputedAttack($this->getRaw(), $this->getNationVar('tech'));
        $opDef = $oppose->getCrewType()->getComputedDefence($oppose->getRaw(), $oppose->getNationVar('tech'));
        // 감소할 병사 수
        $warPower = GameConst::$armperphase + $myAtt - $opDef;
        $opposeWarPowerMultiply = 1.0;

        if($warPower < 100){
            //최소 전투력 50 보장
            $warPower = max(0, $warPower);
            $warPower = ($warPower + 100) / 2;
            $warPower = rand($warPower, 100);
        }

        $warPower *= CharAtmos(
            $this->getComputedAtmos(), 
            $this->getCharacter()
        );
        
        $warPower /= CharTrain(
            $oppose->getComputedTrain(), 
            $oppose->getCharacter()
        );

        if($this instanceof WarUnitGeneral){
            $genDexAtt = getGenDex($this->getRaw(), $this->getCrewType()->id);
        }
        else{
            $genDexAtt = ($this->cityRate - 60) * 7200;
        }
        
        if($this instanceof WarUnitGeneral){
            $oppDexDef = getGenDex($oppose->getRaw(), $this->getCrewType()->id);
        }
        else{
            $oppDexDef = ($this->cityRate - 60) * 7200;
        }
        
        $warPower *= getDexLog($genDexAtt, $oppDexDef);
        
        $warPower *= $this->getCrewType()->getAttackCoef($oppose->getCrewType());
        $opposeWarPowerMultiply *= $this->getCrewType()->getDefenceCoef($oppose->getCrewType());

        $this->warPower = $warPower;
        $this->oppose->setWarPowerMultiply($opposeWarPowerMultiply);

        return [$warPower, $opposeWarPowerMultiply];
    }

    function addTrain(int $train){
        return;
    }

    function addAtmos(int $atmos){
        return;
    }

    function getComputedTrain(){
        return GameConst::$maxTrainByCommand;
    }

    function getComputedAtmos(){
        return GameConst::$maxAtmosByCommand;
    }

    function getComputedAvoidRatio(){
        return $this->getCrewType()->avoid / 100;
    }

    function addWin(){
        throw new NotInheritedMethodException();
    }

    function addLose(){
        throw new NotInheritedMethodException();
    }

    function finishBattle(){
        throw new NotInheritedMethodException();
    }

    function getCharacter(){
        //TODO: 나머지에 구현
        return 0;
    }

    function useBattleInitItem():bool{
        return false;
    }

    function beginPhase():void{
        $this->activatedSkill = [];
        $this->computeWarPower();
    }

    function checkBattleBeginSkill():bool{
        return false;
    }

    function checkBattleBeginItem():bool{
        return false;
    }

    function applyBattleBeginSkillAndItem():bool{
        return false;
    }

    function hasActivatedSkill(string $skillName):bool{
        return $this->activatedSkill[$skillName] ?? false;
    }

    function activateSkill(string $skillName):bool{
        $this->activatedSkill[$skillName] = true;
    }

    function deactivateSkill(string $skillName):bool{
        $this->activatedSkill[$skillName] = false;
    }

    function checkPreActiveSkill():bool{
        return false;
    }

    function checkActiveSkill():bool{
        return false;
    }

    function checkPostActiveSkill():bool{
        return false;
    }

    function applyActiveSkill():bool{
        return false;
    }

    function getHP():int{
        throw new NotInheritedMethodException();
    }

    function decreaseHP(int $damage):int{
        $this->dead += $damage;
        throw new NotInheritedMethodException();
    }

    function increaseKilled(int $damage):int{
        $this->killed += $damage;
        throw new NotInheritedMethodException();
    }

    function calcDamage():int{
        return $this->getWarPower();
    }

    function tryWound():bool{
        return false;
    }

    function continueWar(&$noRice):bool{
        //전투가 가능하면 true
        $noRice = false;
        return false;
    }

    function logBattleResult(){
        $this->getLogger()->pushBattleResultTemplate($this, $this->getOppose());
    }

    function applyDB($db):bool{
        throw new NotInheritedMethodException();
    }


    function criticalDamage():float{
        //전특, 병종에 따라 필살 데미지가 달라질지도 모르므로 static 함수는 아닌 것으로
        return Util::randRange(1.3, 2.0);
    }


}