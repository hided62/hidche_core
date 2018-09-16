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

        $specialDomesticClass = getGeneralSpecialDomesticClass($raw['special']);
        $this->specialDomesticObj = new $specialDomesticClass;

        $specialWarClass = getGeneralSpecialWarClass($raw['special']);
        $this->specialWarObj = new $specialWarClass;
        //TODO: $specialWarClass 설정

        $personalityClass = getPersonalityClass($raw['personal']);
        $this->personalityObj = new $personalityClass;
    }

    function clearActivatedSkill(){
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

    function setRawCity(?array $city){
        $this->city = $city;
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

    function checkStatChange():bool{
        $logger = $this->getLogger();
        $limit = GameConst::$upgradeLimit;

        $table = [
            ['통솔', 'leader'],
            ['무력', 'power'],
            ['지력', 'intel'],
        ];

        $result = false;

        foreach($table as [$statNickName, $statName]){
            $statExpName = $statName.'2';

            if($this->getVar($statExpName) < 0){
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 떨어졌습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, $limit);
                $this->increaseVar($statName, -1);
                $result = true;
            }
            else if($this->getVar($statExpName) >= $limit){
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 올랐습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, -$limit);
                $this->increaseVar($statName, 1);
                $result = true;
            }
        }

        return $result;
    }

    public function onPreTurnExecute(General $general):array{
        $chain = [];
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $chain[] = $iObj->onPreTurnExecute($general);
        }
        return array_merge([], ...$chain);
    }
    public function onCalcDomestic(string $turnType, string $varType, float $value):float{
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $value = $iObj->onCalcDomestic($turnType, $varType, $value);
        }
        return $value;
    }

    public function onPreGeneralStatUpdate(General $general, string $statName, $value){
        //xxx: $general?
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $value = $iObj->onPreGeneralStatUpdate($this, $statName, $value);
        }
        return $value;
    }

    public function onCalcStrategic(string $turnType, string $varType, $value){
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $value = $iObj->onCalcStrategic($turnType, $varType, $value);
        }
        return $value;
    }

    public function onCalcNationalIncome(string $type, int $amount):int{
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $amount = $iObj->onCalcNationalIncome($type, $amount);
        }
        return $amount;
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        //xxx:$unit
        $att = 1;
        $def = 1;
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            [$attV, $defV] = $iObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        return [$att, $def];
    }
    public function getBattleInitSkillTriggerList(WarUnit $unit):array{
        $chain = [];
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $chain[] = $iObj->getBattleInitSkillTriggerList($unit);
        }
        return array_merge([], ...$chain);
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):array{
        $chain = [];
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            $chain[] = $iObj->getBattlePhaseSkillTriggerList($unit);
        }
        return array_merge([], ...$chain);
    }
}