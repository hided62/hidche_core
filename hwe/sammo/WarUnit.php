<?php
namespace sammo;

class WarUnit{
    protected $rawNation;

    protected $logger;
    protected $crewType;

    protected $killed = 0;
    protected $death = 0;

    protected $isAttacker = false;

    protected $updatedVar = [];

    protected $genAtmos = 0;
    protected $genTrain = 0;

    protected $currPhase = 0;
    protected $prePhase = 0;

    protected $oppose;
    protected $warPower;
    protected $warPowerMultiply = 1.0;

    private function __construct(){
    }
    
    function getRaw():array{
        return [];
    }

    function getRawNation():array{
        return $this->rawNation;
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

    function getCrewType():GameUnitDetail{
        return $this->crewType;
    }

    function getLogger():ActionLogger{
        return $this->logger;
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

    function setOppose(WarUnit $oppose){
        $this->oppose = $oppose;
        
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

        $myAtt = $this->getCrewType()->getComputedAttack($this->getRaw(), $this->getRawNation()['tech']);
        $opDef = $oppose->getCrewType()->getComputedDefence($oppose->getRaw(), $oppose->getRawNation()['tech']);
        // 감소할 병사 수
        $warPower = GameConst::$armperphase + $myAtt - $opDef;
        $opposeWarPowerMultiply = 1.0;

        if($warPower <= 0){
            //FIXME: 0으로 잡을때 90~100이면, 1은 너무 억울하지 않나?
            $warPower = rand(90, 100);
        }

        $warPower = getCrew(
            $warPower, 
            CharAtmos(
                $this->getComputedAtmos(), 
                $this->getCharacter()
            ), 
            CharTrain(
                $oppose->getComputedTrain(), 
                $oppose->getCharacter()
            )
        );

        $genDexAtt = getGenDex($this->getRaw(), $this->getCrewType()->id);
        $oppDexDef = getGenDex($oppose->getRaw(), $this->getCrewType()->id);
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
        //TODO: 나머지에 구현
        return 100;
    }

    function getComputeAtmos(){
        //TODO: 나머지에 구현
        return 100;
    }

    function addWin(){
        //TODO: 1승 로그 추가
    }

    function addLose(){
        //TODO: 1패 로그 추가
    }

    function getCharacter(){
        //TODO: 나머지에 구현
        return 0;
    }

    function useBattleInitItem():bool{
        return false;
    }

    function checkBattleBeginSkill():bool{
        return false;
    }

    function checkBattleBeginItem():bool{
        return false;
    }

    function applyBattleBeginSkillAndItem(){
        return false;
    }

    function checkActiveSkill():bool{
        return false;
    }

    function checkActiveItem():bool{
        return false;
    }

    function applyActiveSkillAndItem():bool{
        return false;
    }

    function getHP():int{
        return 1;
    }

    function decreaseHP(int $damage):int{
        return false;
    }

    function tryAttackInPhase():int{
        return 0;
    }

    function tryWound():bool{
        return false;
    }

    function continueWar(&$noRice):bool{
        //전투가 가능하면 true
        $noRice = false;
        return false;
    }




}