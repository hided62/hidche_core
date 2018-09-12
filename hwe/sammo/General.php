<?php

namespace sammo;

class General implements iActionTrigger{
    use LazyVarUpdater;

    /**
     * @var iActionTrigger $nationType
     * @var iActionTrigger $levelObj
     * @var iActionTrigger $specialDomesticObj
     */

    protected $raw = [];
    protected $rawCity = null;

    protected $logger;

    protected $activatedSkill = [];
    protected $logActivatedSkill = [];
    protected $isFinished = false;


    protected $nationType = null;
    protected $levelObj = null;
    protected $specialDomesticObj = null;
    protected $specialWarObj = null;
    protected $personalityObj = null;
    protected $itemObjs = [];

    public function __construct(array $raw, ?array $city, int $year, int $month){
        //TODO:  밖에서 가져오도록 하면 버그 확률이 높아짐. 필요한 raw 값을 직접 구해야함.

        $staticNation = getNationStaticInfo($raw['nation']);
        setLeadershipBonus($raw, $staticNation['level']);
        $this->raw = $raw;
        $this->rawCity = $city;


        $this->logger = new ActionLogger(
            $this->getVar('no'), 
            $this->getVar('nation'), 
            $year, 
            $month,
            false
        );

        $nationTypeClass = getNationTypeClass($staticNation['type']);
        $this->nationType = new $nationTypeClass;
        $this->levelObj = new TriggerGeneralLevel($this->raw, $city);
        $personalityClass = getPersonalityClass($raw['personal']);
        $this->personalityObj = new $personalityClass;
    }

    protected function clearActivatedSkill(){
        foreach ($this->activatedSkill as $skillName=>$state) {
            if (!$state) {
                continue;
            }

            if (!key_exists($skillName, $this->logActivatedSkill)) {
                $this->logActivatedSkill[$skillName] = 1;
            } else {
                $this->logActivatedSkill[$skillName] += 1;
            }
        }
        $this->activatedSkill = [];
    }

    function getActivatedSkillLog():array{
        return $this->logActivatedSkill;
    }

    function hasActivatedSkill(string $skillName):bool{
        return $this->activatedSkill[$skillName] ?? false;
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

    function getName():string{
        return $this->raw['name'];
    }

    function getRawCity():?array{
        return $this->rawCity;
    }

    function getCityID():int{
        return $this->raw['city'];
    }

    function getNationID():int{
        return $this->raw['nation'];
    }

    function getStaticNation():array{
        return getNationStaticInfo($this->raw['nation']);
    }

    function getLogger():ActionLogger{
        return $this->logger;
    }

    public function getNationTypeObj():iActionTrigger{
        return $this->nationType;
    }

    public function getGeneralLevelObj():iActionTrigger{
        return $this->levelObj;
    }

    //TODO: 장기적으로 General 클래스로 모두 옮겨와야함.
    function getLeadership($withInjury = true, $withItem = true, $withStatAdjust = true, $useFloor = true):float{
        return getGeneralLeadership($this->raw, $withInjury, $withItem, $withStatAdjust, $useFloor);
    }

    function getPower($withInjury = true, $withItem = true, $withStatAdjust = true, $useFloor = true):float{
        return getGeneralPower($this->raw, $withInjury, $withItem, $withStatAdjust, $useFloor);
    }

    function getIntel($withInjury = true, $withItem = true, $withStatAdjust = true, $useFloor = true):float{
        return getGeneralIntel($this->raw, $withInjury, $withItem, $withStatAdjust, $useFloor);
    }


    /**
     * @param \MeekroDB $db
     */
    function applyDB($db):bool{
        $updateVals = $this->getUpdatedValues();

        if(!$updateVals){
            return false;
        }
        
        $db->update('general', $updateVals, 'no=%i', $this->raw['no']);
        $this->getLogger()->flush();
        return $db->affectedRows() > 0;
    }

    public function onPreTurnExecute(General $general, ?array $nation):array{
        $chain = [];
        if($this->nationType){
            $chain[] = $nationType->onPreTurnExecute($general, $nation);
        }
        if($this->levelObj){
            $chain[] = $levelObj->onPreTurnExecute($general, $nation);
        }
        if($this->specialDomesticObj){
            $chain[] = $specialDomesticObj->onPreTurnExecute($general, $nation);
        }
        if($this->specialWarObj){
            $chain[] = $specialWarObj->onPreTurnExecute($general, $nation);
        }
        if($this->personalityObj){
            $chain[] = $personalityObj->onPreTurnExecute($general, $nation);
        }
        foreach($this->itemObjs as $itemObj){
            $chain[] = $itemObj->onPreTurnExecute($general, $nation);
        }
        return array_merge([], ...$chain);
    }
    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        if($this->nationType){
            $value = $nationType->onCalcDomestic($turnType, $varType, $value);
        }
        if($this->levelObj){
            $value = $levelObj->onCalcDomestic($turnType, $varType, $value);
        }
        if($this->specialDomesticObj){
            $value = $specialDomesticObj->onCalcDomestic($turnType, $varType, $value);
        }
        if($this->specialWarObj){
            $value = $specialWarObj->onCalcDomestic($turnType, $varType, $value);
        }
        if($this->personalityObj){
            $value = $personalityObj->onCalcDomestic($turnType, $varType, $value);
        }
        foreach($this->itemObjs as $itemObj){
            $value = $itemObj->onCalcDomestic($turnType, $varType, $value);
        }
        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        //xxx: $general?
        if($this->nationType){
            $value = $nationType->onPreGeneralStatUpdate($this, $statName, $value);
        }
        if($this->levelObj){
            $value = $levelObj->onPreGeneralStatUpdate($this, $statName, $value);
        }
        if($this->specialDomesticObj){
            $value = $specialDomesticObj->onPreGeneralStatUpdate($this, $statName, $value);
        }
        if($this->specialWarObj){
            $value = $specialWarObj->onPreGeneralStatUpdate($this, $statName, $value);
        }
        if($this->personalityObj){
            $value = $personalityObj->onPreGeneralStatUpdate($this, $statName, $value);
        }
        foreach($this->itemObjs as $itemObj){
            $value = $itemObj->onPreGeneralStatUpdate($this, $statName, $value);
        }
        return $value;
    }

    public function onCalcStrategic(string $turnType, string $varType, $value){
        if($this->nationType){
            $value = $nationType->onCalcStrategic($turnType, $varType, $value);
        }
        if($this->levelObj){
            $value = $levelObj->onCalcStrategic($turnType, $varType, $value);
        }
        if($this->specialDomesticObj){
            $value = $specialDomesticObj->onCalcStrategic($turnType, $varType, $value);
        }
        if($this->specialWarObj){
            $value = $specialWarObj->onCalcStrategic($turnType, $varType, $value);
        }
        if($this->personalityObj){
            $value = $personalityObj->onCalcStrategic($turnType, $varType, $value);
        }
        foreach($this->itemObjs as $itemObj){
            $value = $itemObj->onCalcStrategic($turnType, $varType, $value);
        }
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        if($this->nationType){
            $amount = $nationType->onCalcNationalIncome($type, $amount);
        }
        if($this->levelObj){
            $amount = $levelObj->onCalcNationalIncome($type, $amount);
        }
        if($this->specialDomesticObj){
            $amount = $specialDomesticObj->onCalcNationalIncome($type, $amount);
        }
        if($this->specialWarObj){
            $amount = $specialWarObj->onCalcNationalIncome($type, $amount);
        }
        if($this->personalityObj){
            $amount = $personalityObj->onCalcNationalIncome($type, $amount);
        }
        foreach($this->itemObjs as $itemObj){
            $amount = $itemObj->onCalcNationalIncome($type, $amount);
        }
        return $amount;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        //xxx:$unit
        $att = 1;
        $def = 1;
        if($this->nationType){
            [$attV, $defV] = $nationType->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        if($this->levelObj){
            [$attV, $defV] = $levelObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        if($this->specialDomesticObj){
            [$attV, $defV] = $specialDomesticObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        if($this->specialWarObj){
            [$attV, $defV] = $specialWarObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        if($this->personalityObj){
            [$attV, $defV] = $personalityObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        foreach($this->itemObjs as $itemObj){
            [$attV, $defV] = $itemObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        return [$att, $def];
    }
    public function getBattleInitSkillTriggerList(WarUnit $unit):array{
        $chain = [];
        if($this->nationType){
            $chain[] = $nationType->getBattleInitSkillTriggerList($unit);
        }
        if($this->levelObj){
            $chain[] = $levelObj->getBattleInitSkillTriggerList($unit);
        }
        if($this->specialDomesticObj){
            $chain[] = $specialDomesticObj->getBattleInitSkillTriggerList($unit);
        }
        if($this->specialWarObj){
            $chain[] = $specialWarObj->getBattleInitSkillTriggerList($unit);
        }
        if($this->personalityObj){
            $chain[] = $personalityObj->getBattleInitSkillTriggerList($unit);
        }
        foreach($this->itemObjs as $itemObj){
            $chain[] = $itemObj->getBattleInitSkillTriggerList($unit);
        }
        return array_merge([], ...$chain);
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):array{
        $chain = [];
        if($this->nationType){
            $chain[] = $nationType->getBattlePhaseSkillTriggerList($unit);
        }
        if($this->levelObj){
            $chain[] = $levelObj->getBattlePhaseSkillTriggerList($unit);
        }
        if($this->specialDomesticObj){
            $chain[] = $specialDomesticObj->getBattlePhaseSkillTriggerList($unit);
        }
        if($this->specialWarObj){
            $chain[] = $specialWarObj->getBattlePhaseSkillTriggerList($unit);
        }
        if($this->personalityObj){
            $chain[] = $personalityObj->getBattlePhaseSkillTriggerList($unit);
        }
        foreach($this->itemObjs as $itemObj){
            $chain[] = $itemObj->getBattlePhaseSkillTriggerList($unit);
        }
        return array_merge([], ...$chain);
    }
}