<?php
namespace sammo;

class WarUnit{
    use LazyVarUpdater;

    protected $raw = [];
    protected $rawNation;

    protected $logger;
    protected $crewType;

    protected $killedCurr = 0;
    protected $killed = 0;
    protected $deadCurr = 0;
    protected $dead = 0;

    protected $isAttacker = false;

    protected $currPhase = 0;
    protected $prePhase = 0;

    protected $atmosBonus = 0;
    protected $trainBonus = 0;

    protected $oppose;
    protected $warPower;
    protected $warPowerMultiply = 1.0;

    protected $activatedSkill = [];
    protected $logActivatedSkill = [];
    protected $isFinished = false;

    private function __construct(){
    }
    
    protected function clearActivatedSkill(){
        foreach($this->activatedSkill as $skillName=>$state){
            if(!$state){
                continue;
            }

            if(!key_exists($skillName, $this->logActivatedSkill)){
                $this->logActivatedSkill[$skillName] = 1;
            }
            else{
                $this->logActivatedSkill[$skillName] += 1;
            }
        }
        $this->activatedSkill = [];
    }

    function getActivatedSkillLog():array{
        return $this->logActivatedSkill;
    }

    function getRawNation():array{
        return $this->rawNation;
    }

    function getNationVar(string $key){
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

    function getKilledCurrentBattle():int{
        return $this->killedCurr;
    }

    function getDeadCurrentBattle():int{
        return $this->deadCurr;
    }

    function getSpecialDomestic():string{
        return GameConst::$defaultSpecialDomestic;
    }

    function getSpecialWar():string{
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
        $this->killedCurr = 0;
        $this->deadCurr = 0;
        $this->clearActivatedSkill();
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

    function multiplyWarPowerMultiply($multiply){
        $this->warPowerMultiply *= $multiply;
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

    function addTrainBonus(int $trainBonus){
        $this->trainBonus += $trainBonus;
    }

    function addAtmosBonus(int $atmosBonus){
        $this->atmosBonus += $atmosBonus;
    }

    function getComputedTrain(){
        return GameConst::$maxTrainByCommand;
    }

    function getComputedAtmos(){
        return GameConst::$maxAtmosByCommand;
    }

    function getComputedCriticalRatio():float{
        return $this->getCrewType()->getCriticalRatio($this->getRaw());
    }

    function getComputedAvoidRatio():float{
        return $this->getCrewType()->avoid / 100;
    }

    function addWin(){
    }

    function addLose(){
    }

    function finishBattle(){
        throw new NotInheritedMethodException();
    }

    function getCharacter():int{
        return 0;
    }

    function useBattleInitItem():bool{
        return false;
    }

    function beginPhase():void{
        $this->clearActivatedSkill();
        $this->computeWarPower();
    }

    function checkBattleBeginSkill(){
        yield true;
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

    function hasActivatedSkillOnLog(string $skillName):bool{
        if(key_exists($skillName, $this->logActivatedSkill)){
            return true;
        }
        return $this->hasActivatedSkill($skillName);
    }

    function activateSkill(... $skillNames){
        foreach($skillNames as $skillName){
            $this->activatedSkill[$skillName] = true;
        }
    }

    function deactivateSkill(... $skillNames){
        foreach($skillNames as $skillName){
            $this->activatedSkill[$skillName] = false;
        }
    }

    function checkPreActiveSkill(){
        yield true;
    }

    function checkActiveSkill(){
        yield true;
    }

    function checkPostActiveSkill(){
        yield true;
    }

    function applyActiveSkill(){
        yield true;
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
        $warPower = $this->getWarPower();
        $warPower *= Util::randRange(0.9, 1.1);
        return Util::round($warPower);
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

    function criticalDamage():float{
        //전특, 병종에 따라 필살 데미지가 달라질지도 모르므로 static 함수는 아닌 것으로
        return Util::randRange(1.3, 2.0);
    }
}