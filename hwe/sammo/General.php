<?php

namespace sammo;

class General{
    use LazyVarUpdater;

    protected $raw = [];
    protected $rawCity = null;

    protected $logger;

    protected $activatedSkill = [];
    protected $logActivatedSkill = [];
    protected $isFinished = false;

    protected $nationType;
    protected $levelObj;
    protected $specialDomesticObj;

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
}