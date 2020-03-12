<?php

namespace sammo;
use sammo\WarUnitTrigger as WarUnitTrigger;

class General implements iAction{
    use LazyVarUpdater;

    /**
     * @var iAction $nationType
     * @var iAction $levelObj
     * @var iAction $specialDomesticObj
     * @var iAction $specialWarObj
     * @var iAction $personalityObj
     * @var iAction[] $itemObjs
     */

    protected $raw = [];
    protected $rawCity = null;

    /** @var \sammo\ActionLogger */
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

    const TURNTIME_FULL_MS = -1;
    const TURNTIME_FULL = 0;
    const TURNTIME_HMS = 1;
    const TURNTIME_HM = 2;

    protected $calcCache = [];

    protected static $prohibitedDirectUpdateVars = [
        //Reason: iAction
        'leadership'=>1,
        'power'=>1,
        'intel'=>1,
        'nation'=>2,
        'level'=>1,
        //NOTE: levelObj로 인해 국가의 '레벨'이 바뀌는 것도 조심해야 하나, 국가 레벨의 변경은 월 초/말에만 일어남.
        'special'=>1,
        'special2'=>1,
        'personal'=>1,
        'horse'=>1,
        'weapon'=>1,
        'book'=>1,
        'item'=>1
    ];


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
        $this->raw = $raw;
        $this->rawCity = $city;

        if(key_exists('last_turn', $this->raw)){
            $this->lastTurn = LastTurn::fromJson($this->raw['last_turn']);
        }
        $this->resultTurn = new LastTurn();

        if($year !== null || $month !== null){
            $this->initLogger($year, $month);
        }

        if(!$fullConstruct){
            return;
        }

        $this->nationType = buildNationTypeClass($staticNation['type']);
        $this->levelObj = new TriggerGeneralLevel($this->raw, $staticNation['level'], $city);

        $this->specialDomesticObj = buildGeneralSpecialDomesticClass($raw['special']);
        $this->specialWarObj = buildGeneralSpecialWarClass($raw['special2']);

        $this->personalityObj = buildPersonalityClass($raw['personal']);
        
        $this->itemObjs['horse'] = buildItemClass($raw['horse']);
        $this->itemObjs['weapon'] = buildItemClass($raw['weapon']);
        $this->itemObjs['book'] = buildItemClass($raw['book']);
        $this->itemObjs['item'] = buildItemClass($raw['item']);
    }

    function initLogger(int $year, int $month){
        $this->logger = new ActionLogger(
            $this->getVar('no'), 
            $this->getVar('nation'), 
            $year, 
            $month,
            false
        );
    }

    function getTurnTime(int $short=self::TURNTIME_FULL_MS){
        return [
            self::TURNTIME_FULL_MS=>function($turntime){return $turntime;},
            self::TURNTIME_FULL=>function($turntime){return substr($turntime, 0, 19);},
            self::TURNTIME_HMS=>function($turntime){return substr($turntime, 11, 8);},
            self::TURNTIME_HM=>function($turntime){return substr($turntime, 11, 5);},
        ][$short]($this->getVar('turntime'));
    }

    function setItem(string $itemKey='item', ?string $itemCode){
        if($itemCode === null){
            $this->deleteItem($itemKey);
        }

        $this->setVar($itemKey, $itemCode);
        $this->itemObjs[$itemKey] = buildItemClass($itemCode);
    }

    function deleteItem(string $itemKey='item'){
        $this->setVar($itemKey, 'None');
        $this->itemObjs[$itemKey] = new ActionItem\None();
    }

    function getItem(string $itemKey='item'):BaseItem{
        return $this->itemObjs[$itemKey];
    }

    function getItems():array{
        return $this->itemObjs;
    }

    function getPersonality():iAction{
        return $this->personalityObj;
    }

    function getSpecialDomestic():iAction{
        return $this->specialDomesticObj;
    }

    function getSpecialWar():iAction{
        return $this->specialWarObj;
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

    function getInfo():string{
        //iAction용 info로는 적절하지 않음
        return '';
    }

    function getID():int{
        return $this->raw['no'];
    }

    function getRawCity():?array{
        return $this->rawCity;
    }

    function setRawCity(?array $city){
        $this->rawCity = $city;
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

    function getLogger():?ActionLogger{
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

    /**
     * 장수의 스탯을 계산해옴
     * 
     * @param string $statName 스탯값, leadership, strength, intel 가능
     * @param bool $withInjury 부상값 사용 여부
     * @param bool $withIActionObj 아이템, 특성, 성격 등 보정치 적용 여부
     * @param bool $withStatAdjust 능력치간 보정 사용 여부
     * @param bool $useFloor 내림 사용 여부, false시 float 값을 반환할 수도 있음
     * 
     * @return int|float 계산된 능력치
     */

    protected function getStatValue(string $statName, $withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true):float{
        $cKey = "{$statName}_{$withInjury}_{$withIActionObj}_{$withStatAdjust}";
        if(key_exists($cKey, $this->calcCache)){
            $statValue = $this->calcCache[$cKey];
            if($useFloor){
                return Util::toInt($statValue);
            }
            return $statValue;
        }

        $statValue = $this->getVar($statName);

        if($withInjury){
            $statValue *= (100 - $this->getVar('injury')) / 100;
        }

        if($withStatAdjust){
            if($statName === 'strength'){
                $statValue += Util::round($this->getStatValue('intel', $withInjury, $withIActionObj, false, false) / 4);
            }
            else if($statName === 'intel'){
                $statValue += Util::round($this->getStatValue('strength', $withInjury, $withIActionObj, false, false) / 4);
            }
        }

        if($withIActionObj){
            foreach([
                $this->nationType,
                $this->levelObj,
                $this->specialDomesticObj,
                $this->specialWarObj,
                $this->personalityObj
            ] as $actionObj){
                if($actionObj !== null){
                    $statValue = $actionObj->onCalcStat($this, $statName, $statValue);
                }
            }
        
            foreach($this->itemObjs as $actionObj){
                if($actionObj !== null){
                    $statValue = $actionObj->onCalcStat($this, $statName, $statValue);
                }
            }
        }

        $this->calcCache[$cKey] = $statValue;

        if($useFloor){
            return Util::toInt($statValue);
        }
        
        return $statValue;
    }

    function getLeadership($withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true):float{
        return $this->getStatValue('leadership', $withInjury, $withIActionObj, $withStatAdjust, $useFloor);
    }

    function getStrength($withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true):float{
        return $this->getStatValue('strength', $withInjury, $withIActionObj, $withStatAdjust, $useFloor);
    }

    function getIntel($withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true):float{
        return $this->getStatValue('intel', $withInjury, $withIActionObj, $withStatAdjust, $useFloor);
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

    function updateVar(string $key, $value){
        if(!key_exists($key, $this->updatedVar)){
            $this->updatedVar[$key] = $this->raw[$key];
            $this->calcCache = [];
        }
        if($this->raw[$key] === $value){
            return;
        }
        $this->raw[$key] = $value;
        $this->calcCache = [];
    }

    /**
     * @param \MeekroDB $db
     */
    function kill($db){
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $generalID = $this->getID();
        $logger = $this->getLogger();

        $generalName = $this->getName();

        // 군주였으면 유지 이음
        $generalLevel = $this->getVar('level');
        if($generalLevel == 12) {
            nextRuler($this);
        }

        //도시의 태수, 군사, 종사직도 초기화
        if(2 <= $generalLevel && $generalLevel <= 4){
            $db->update('city', [
                'officer'.$generalLevel=>0
            ], "officer{$generalLevel} = %i", $generalID);
        }

        // 부대 처리
        $troopLeaderID = $this->getVar('troop');
        if($troopLeaderID == $generalID){
            //부대장일 경우
            // 모두 탈퇴
            $db->update('general', [
                'troop'=>0
            ], 'troop_leader=%i', $troopLeaderID);
            // 부대 삭제
            $db->delete('troop', 'troop_leader=%i', $troopLeaderID);
        }

        $dyingMessage = new TextDecoration\DyingMessage($this);
        $logger->pushGlobalActionLog($dyingMessage->getText());

        $db->delete('general', 'no=%i', $generalID);
        $db->delete('general_turn', 'general_id=%i', $generalID);
        $this->updatedVar = [];

        $db->update('nation', [
            'gennum'=>$db->sqleval('gennum - 1')
        ], 'nation=%i', $this->getVar('nation'));
    }

    function rebirth(){
        $logger = $this->getLogger();

        $generalName = $this->getName();

        $this->multiplyVarWithLimit('leadership', 0.85, 10);
        $this->multiplyVarWithLimit('strength', 0.85, 10);
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
            ['통솔', 'leadership'],
            ['무력', 'strength'],
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
            $this->getCrewTypeObj(),
        ], $this->itemObjs) as $iObj){
            
            if(!$iObj){
                continue;
            }
            if($caller->isEmpty()){
                continue;
            }
            /** @var iAction $iObj */
            $caller->merge($iObj->getPreTurnExecuteTriggerList($general));
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
            $this->getCrewTypeObj(),
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            /** @var iAction $iObj */
            $value = $iObj->onCalcDomestic($turnType, $varType, $value, $aux);
        }
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        //xxx: $general?
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
            $this->getCrewTypeObj(),
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            /** @var iAction $iObj */
            $value = $iObj->onCalcStat($this, $statName, $value, $aux);
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
            $this->getCrewTypeObj(),
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
            $this->getCrewTypeObj(),
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
            $this->getCrewTypeObj(),
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
    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        $caller = new WarUnitTriggerCaller();
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
            $this->getCrewTypeObj(),
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            /** @var iAction $iObj */
            $caller->merge($iObj->getBattleInitSkillTriggerList($unit));
        }

        return $caller;
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        $caller = new WarUnitTriggerCaller(
            new WarUnitTrigger\che_필살시도($unit),
            new WarUnitTrigger\che_필살발동($unit),
            new WarUnitTrigger\che_회피시도($unit),
            new WarUnitTrigger\che_회피발동($unit),
            new WarUnitTrigger\che_계략시도($unit),
            new WarUnitTrigger\che_계략발동($unit),
            new WarUnitTrigger\che_계략실패($unit)
        );
        foreach(array_merge([
            $this->nationType, 
            $this->levelObj, 
            $this->specialDomesticObj, 
            $this->specialWarObj, 
            $this->personalityObj, 
            $this->getCrewTypeObj(),
        ], $this->itemObjs) as $iObj){
            if(!$iObj){
                continue;
            }
            /** @var iAction $iObj */
            $caller->merge($iObj->getBattlePhaseSkillTriggerList($unit));
        }

        return $caller;
    }

    //TODO:createGeneralObjListFromDB로, 조건으로 select query나 generalIDList가 들어가는 녀석이 필요할 수 있음

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
            'horse', 'weapon', 'book', 'item', 'last_turn'
        ];
        $fullColumn = [
            'no', 'name', 'name2', 'picture', 'imgsvr', 'nation', 'city', 'troop', 'injury', 'affinity', 
            'leadership', 'leadership2', 'strength', 'strength2', 'intel', 'intel2', 'weapon', 'book', 'horse', 'item', 
            'experience', 'dedication', 'level', 'gold', 'rice', 'crew', 'crewtype', 'train', 'atmos', 'turntime',
            'makelimit', 'killturn', 'block', 'dedlevel', 'explevel', 'age', 'startage', 'belong',
            'personal', 'special', 'special2', 'defence_train', 'tnmt', 'npc', 'npc_org', 'deadyear', 'npcmsg',
            'dex0', 'dex10', 'dex20', 'dex30', 'dex40', 
            'warnum', 'firenum', 'killnum', 'deathnum', 'killcrew', 'deathcrew', 'recwar', 'last_turn', 'myset',
            'specage', 'specage2', 'con', 'connect', 'owner', 'aux'
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

        $rawGeneral = $db->queryFirstRow('SELECT %l FROM general WHERE no = %i', Util::formatListOfBackticks($column), $generalID);
        if(!$rawGeneral){
            return new DummyGeneral($constructMode > 0);
        }

        $general = new static($rawGeneral, null, $year, $month, $constructMode > 1);
        
        return $general;
    }
}