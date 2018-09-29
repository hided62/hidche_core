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
    function kill($db){
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $generalID = $this->getRaw('no');
        $logger = $this->getLogger();

        $generalName = $this->getName();

        // 군주였으면 유지 이음
        if($this->getRaw('level') == 12) {
            nextRuler($this);
        }

        //도시의 태수, 군사, 시중직도 초기화
        $db->update('city', [
            'gen1'=>0,
        ], 'gen1=%i', $generalID);

        $db->update('city', [
            'gen2'=>0,
        ], 'gen2=%i', $generalID);

        $db->update('city', [
            'gen3'=>0,
        ], 'gen3=%i', $generalID);

        // 부대 처리
        $troopID = $this->getRaw('troop');
        $troopLeaderID = $db->queryFirstField('SELECT `no` FROM troop WHERE troop=%i', $troopID);
        if($troopLeaderID == $generalID){
            //부대장일 경우
            // 모두 탈퇴
            $db->update('general', [
                'troop'=>0
            ], 'troop=%i', $troopID);

            // 부대 삭제
            $db->delete('troop', 'troop=%i', $troopID);
        }

        $dyingMessage = new TextDecoration\DyingMessage($general['name'], $general['npc']);
        $logger->pushGlobalActionLog($dyingMessage->getText());

        $db->delete('general', 'no=%i', $generalID);
        $this->updatedVar = [];

        $db->update('nation', [
            'gennum'=>$db->sqleval('gennum - 1')
        ], 'nation=%i', $this->getVar('nation'));
    }

    function rebirth(){
        $logger = $this->getLogger();

        $generalName = $this->getName();

        $this->multiplyVarWithLimit('leader', 0.85, 10);
        $this->multiplyVarWithLimit('power', 0.85, 10);
        $this->multiplyVarWithLimit('intel', 0.85, 10);
        $this->setVar('injury', 0);
        $this->multiplyVar('experience', 0.5);
        $this->multiplyVar('dedication', 0.5);
        $this->setVar('firenum', 0);
        $this->setVar('warnum', 0);
        $this->setVar('killnum', 0);
        $this->setVar('killcrew', 0);
        $this->setVar('age', 20);
        $this->setVar('specage', 0);
        $this->setVar('specage2', 0);
        $this->multiplyVar('dex0', 0.5);
        $this->multiplyVar('dex10', 0.5);
        $this->multiplyVar('dex20', 0.5);
        $this->multiplyVar('dex30', 0.5);
        $this->multiplyVar('dex40', 0.5);

        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalActionLog("{$generalName}</>{$josaYi} <R>은퇴</>하고 그 자손이 유지를 이어받았습니다.");
        $logger->pushGeneralActionLog('나이가 들어 <R>은퇴</>하고 자손에게 자리를 물려줍니다.', ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog('나이가 들어 은퇴하고, 자손에게 관직을 물려줌');
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