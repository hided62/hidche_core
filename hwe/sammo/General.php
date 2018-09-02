<?php

namespace sammo;

class General{
    use LazyVarUpdater;

    protected $raw = [];
    protected $rawNation;

    protected $logger;

    protected $activatedSkill = [];
    protected $logActivatedSkill = [];
    protected $isFinished = false;

    public function __construct(array $raw, int $year, int $month){
        //TODO:  밖에서 가져오도록 하면 버그 확률이 높아짐. 필요한 raw 값을 직접 구해야함.
        if($raw['nation']){
            $staticNation = getNationStaticInfo($raw['nation']);
            setLeadershipBonus($raw, $staticNation['level']);
        }
        $this->raw = $raw;

        $this->logger = new ActionLogger(
            $this->getVar('no'), 
            $this->getVar('nation'), 
            $year, 
            $month,
            false
        );
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