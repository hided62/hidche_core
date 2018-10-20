<?php

namespace sammo;

class General implements iAction{
    use LazyVarUpdater;

    /**
     * @var iAction $nationType
     * @var iAction $levelObj
     * @var iAction $specialDomesticObj
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

    protected $lastTurn = null;
    protected $resultTurn = null;

    /**
     * @param array $raw DB row값.
     * @param null|array $city DB city 테이블의 row값
     * @param int $year 게임 연도
     * @param int $month 게임 월
     * @param bool $fullConstruct iAction, 및 ActionLogger 초기화 여부, false인 경우 no, name, city, nation, level 정도로 초기화 가능
     */
    public function __construct(array $raw, ?array $city, ?int $year, ?int $month, bool $fullConstruct=true){
        //TODO:  밖에서 가져오도록 하면 버그 확률이 높아짐. 필요한 raw 값을 직접 구해야함.

        $staticNation = getNationStaticInfo($raw['nation']);
        setLeadershipBonus($raw, $staticNation['level']);
        $this->raw = $raw;
        $this->rawCity = $city;

        if(key_exists('last_turn', $this->raw)){
            $this->lastTurn = LastTurn::fromJson($this->raw['last_turn']);
        }
        $this->resultTurn = new LastTurn();

        if($year !== null || $month !== null){
            $this->logger = new ActionLogger(
                $this->getVar('no'), 
                $this->getVar('nation'), 
                $year, 
                $month,
                false
            );
        }

        if(!$fullConstruct){
            return;
        }

        

        $nationTypeClass = getNationTypeClass($staticNation['type']);
        $this->nationType = new $nationTypeClass;
        $this->levelObj = new TriggerGeneralLevel($this->raw, $city);

        $specialDomesticClass = getGeneralSpecialDomesticClass($raw['special']);
        $this->specialDomesticObj = new $specialDomesticClass;

        $specialWarClass = getGeneralSpecialWarClass($raw['special2']);
        $this->specialWarObj = new $specialWarClass;

        $personalityClass = getPersonalityClass($raw['personal']);
        $this->personalityObj = new $personalityClass;
        
        $itemClass = getItemClass($raw['item']);
        if($itemClass !== null){
            $this->itemObjs['item'] = new $itemClass;
        }
        else{
            $this->itemObjs['item'] = new ActionItem\che_Dummy($raw['item']);
        }
        //TODO: $specialItemClass 설정
    }

    function deleteItem(){
        $this->setVar('item', 0);
        $this->itemObjs['item'] = new ActionItem\che_Dummy(0);
    }

    function getItem():BaseItem{
        return $this->itemObjs['item'];
    }

    function getLastTurn():LastTurn{
        return $this->lastTurn;
    }

    function setResultTurn(LastTurn $resultTurn){
        $this->resultTurn = $resultTurn;
    }

    function getResultTurn():LastTurn{
        return $this->resultTurn;
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

    function getID():int{
        return $this->raw['no'];
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

    public function getNationTypeObj():iAction{
        return $this->nationType;
    }

    public function getGeneralLevelObj():iAction{
        return $this->levelObj;
    }

    function getCrewTypeObj():GameUnitDetail{
        return GameUnitConst::byID($this->getVar('crewtype'));
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

    function addDex(GameUnitDetail $crewType, float $exp, bool $affectTrainAtmos=false){
        $armType = $crewType->armType;

        if($armType == GameUnitConst::T_CASTLE){
            $armType = GameUnitConst::T_SIEGE;
        }

        if($armType < 0){
            return;
        }

        if($armType == GameUnitConst::T_WIZARD) {
            $exp *= 0.9;
        }
        else if($armType == GameUnitConst::T_SIEGE) {
            $exp *= 0.9;
        }

        if($affectTrainAtmos){
            $exp *= ($this->getVar('train') + $this->getVar('atmos')) / 200;
        }

        $ntype = $armType*10;
        $dexType = "dex{$ntype}";

        $this->increaseVar($dexType, $exp);
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
        if($this->lastTurn && $this->getLastTurn() != $this->getResultTurn()){
            $this->setVar('last_turn', $this->getResultTurn()->toJson());
        }
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
                $logger->pushGeneralActionLog("<S>{$statNickName}</>이 <C>1</> 올랐습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, -$limit);
                $this->increaseVar($statName, 1);
                $result = true;
            }
        }

        return $result;
    }

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        $caller = new GeneralTriggerCaller();
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
            /** @var iAction $iObj */
            $caller->merge($iObj->getPreTurnExecuteTriggerList($general));
        }

        if($caller->isEmpty()){
            return null;
        }
        return $caller;
    }
    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
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
            /** @var iAction $iObj */
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
            /** @var iAction $iObj */
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
            /** @var iAction $iObj */
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
            /** @var iAction $iObj */
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
            /** @var iAction $iObj */
            [$attV, $defV] = $iObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        return [$att, $def];
    }
    public function getBattleInitSkillTriggerList(WarUnit $unit):array{
        $caller = new WarUnitTriggerCaller();
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
            /** @var iAction $iObj */
            $caller->merge($iObj->getBattleInitSkillTriggerList($unit));
        }

        if($caller->isEmpty()){
            return null;
        }
        return $caller;
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):array{
        $caller = new WarUnitTriggerCaller();
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
            /** @var iAction $iObj */
            $caller->merge($iObj->getBattlePhaseSkillTriggerList($unit));
        }

        if($caller->isEmpty()){
            return null;
        }
        return $caller;
    }

    static public function createGeneralObjFromDB(int $generalID, ?array $column=null, int $constructMode=2):self{
        $db = DB::db();
        if($constructMode > 0){
            $gameStor = KVStorage::getStorage($db, 'game_env');
            [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        }
        else{
            $year = null;
            $month = null;
        }
        $minimumColumn = ['no', 'name', 'city', 'nation', 'level'];
        $defaultEventColumn = [
            'no', 'name', 'city', 'nation', 'level',
            'special', 'special2', 'personal',
            'horse', 'weap', 'book', 'item', 'last_turn'
        ];
        $fullColumn = [
            'no', 'name', 'name2', 'picture', 'imgsvr', 'nation', 'nations', 'city', 'troop', 'injury', 'affinity', 
            'leader', 'leader2', 'power', 'power2', 'intel', 'intel2', 'weap', 'book', 'horse', 'item', 
            'experience', 'dedication', 'level', 'gold', 'rice', 'crew', 'crewtype', 'train', 'atmos', 'turntime',
            'makenation', 'makelimit', 'killturn', 'block', 'dedlevel', 'explevel', 'age', 'belong',
            'personal', 'special', 'special2', 'term', 'mode', 'npc', 'npc_org', 'npcid', 'deadyear', 'npcmsg',
            'dex0', 'dex10', 'dex20', 'dex30', 'dex40', 
            'warnum', 'killnum', 'deathnum', 'killcrew', 'deathcrew', 'recwar', 'last_turn'
        ];

        if($column === null){
            $column = $fullColumn;
        }
        else if($constructMode > 1){
            $column = array_unique(array_merge($defaultEventColumn, $column));
        }
        else{
            $column = array_unique(array_merge($minimumColumn, $column));
        }

        $rawGeneral = $db->queryFirstRow('SELECT $lb FROM general WHERE no = %i', $generalID);
        if(!$rawGeneral){
            throw new NoDBResultException("generalID에 해당하는 장수가 없음: {$generalID}");
        }

        $general = new static($rawGeneral, null, $year, $month, $constructMode > 1);
        
        return $general;
    }
}