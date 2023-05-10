<?php

namespace sammo;

use Ds\Map;
use sammo\Command\GeneralCommand;
use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;
use sammo\WarUnitTrigger as WarUnitTrigger;

class General implements iAction
{
    use LazyVarUpdater;

    protected $raw = [];
    protected $rawCity = null;


    /** @var Map<RankColumn,int|float> */
    protected Map $rankVarRead;
    /** @var Map<RankColumn,int|float> */
    protected Map $rankVarIncrease;
    /** @var Map<RankColumn,int|float> */
    protected Map $rankVarSet;

    /** @var \sammo\ActionLogger */
    protected $logger;

    protected $activatedSkill = [];
    protected $logActivatedSkill = [];
    protected $isFinished = false;

    /** @var ?iAction */
    protected $nationType = null;
    /** @var ?iAction */
    protected $officerLevelObj = null;
    /** @var ?iAction */
    protected $specialDomesticObj = null;
    /** @var ?iAction */
    protected $specialWarObj = null;
    /** @var ?iAction */
    protected $personalityObj = null;
    /** @var ?iAction[] */
    protected $itemObjs = [];
    /** @var ?iAction */
    protected $inheritBuffObj = null;
    /** @var ?GameUnitDetail */
    protected $crewType = null;

    protected $lastTurn = null;
    protected $resultTurn = null;

    const TURNTIME_FULL_MS = -1;
    const TURNTIME_FULL = 0;
    const TURNTIME_HMS = 1;
    const TURNTIME_HM = 2;

    protected $calcCache = [];

    protected static $prohibitedDirectUpdateVars = [
        //Reason: iAction
        'leadership' => 1,
        'power' => 1,
        'intel' => 1,
        'nation' => 2,
        'officer_level' => 1,
        //NOTE: officerLevelObj로 인해 국가의 '레벨'이 바뀌는 것도 조심해야 하나, 국가 레벨의 변경은 월 초/말에만 일어남.
        'special' => 1,
        'special2' => 1,
        'personal' => 1,
        'horse' => 1,
        'weapon' => 1,
        'book' => 1,
        'item' => 1
    ];


    /**
     * @param array $raw DB row값.
     * @param null|Map<RankColumn,int|float> $rawRank
     * @param null|array $city DB city 테이블의 row값
     * @param int|null $year 게임 연도
     * @param int|null $month 게임 월
     * @param bool $fullConstruct iAction, 및 ActionLogger 초기화 여부, false인 경우 no, name, city, nation, officer_level 정도로 초기화 가능
     */
    public function __construct(array $raw, ?Map $rawRank, ?array $city, ?array $nation, ?int $year, ?int $month, bool $fullConstruct = true)
    {
        //TODO:  밖에서 가져오도록 하면 버그 확률이 높아짐. 필요한 raw 값을 직접 구해야함.

        if ($nation === null) {
            $nation = getNationStaticInfo($raw['nation']);
        }

        $this->raw = $raw;
        $this->rawCity = $city;

        $this->resultTurn = new LastTurn();
        if (key_exists('last_turn', $this->raw)) {
            $this->lastTurn = LastTurn::fromJson($this->raw['last_turn']);
            $this->resultTurn = $this->lastTurn->duplicate();
        }

        if ($year !== null && $month !== null) {
            $this->initLogger($year, $month);
        }

        if ($rawRank) {
            $this->rankVarRead = $rawRank;
        } else {
            $this->rankVarRead = new Map();
        }
        $this->rankVarIncrease = new Map();
        $this->rankVarSet = new Map();

        if (!$fullConstruct) {
            return;
        }

        $this->nationType = buildNationTypeClass($nation['type']);
        $this->officerLevelObj = new TriggerOfficerLevel($this->raw, $nation['level']);

        $this->specialDomesticObj = buildGeneralSpecialDomesticClass($raw['special']);
        $this->specialWarObj = buildGeneralSpecialWarClass($raw['special2']);

        $this->personalityObj = buildPersonalityClass($raw['personal']);

        $this->crewType = GameUnitConst::byID($raw['crewtype'] ?? GameUnitConst::DEFAULT_CREWTYPE);

        $this->itemObjs['horse'] = buildItemClass($raw['horse']);
        $this->itemObjs['weapon'] = buildItemClass($raw['weapon']);
        $this->itemObjs['book'] = buildItemClass($raw['book']);
        $this->itemObjs['item'] = buildItemClass($raw['item']);

        if (key_exists('aux', $this->raw)) {
            $rawInheritBuff = $this->getAuxVar('inheritBuff');
            if ($rawInheritBuff !== null) {
                $this->inheritBuffObj = new TriggerInheritBuff($rawInheritBuff);
            }
        }
    }

    function initLogger(int $year, int $month)
    {
        $this->logger = new ActionLogger(
            $this->getVar('no'),
            $this->getVar('nation'),
            $year,
            $month,
            false
        );
    }

    function getTurnTime(int $short = self::TURNTIME_FULL_MS)
    {
        return [
            self::TURNTIME_FULL_MS => function ($turntime) {
                return $turntime;
            },
            self::TURNTIME_FULL => function ($turntime) {
                return substr($turntime, 0, 19);
            },
            self::TURNTIME_HMS => function ($turntime) {
                return substr($turntime, 11, 8);
            },
            self::TURNTIME_HM => function ($turntime) {
                return substr($turntime, 11, 5);
            },
        ][$short]($this->getVar('turntime'));
    }

    function setItem(string $itemKey = 'item', ?string $itemCode)
    {
        if ($itemCode === null) {
            $this->deleteItem($itemKey);
            return;
        }

        $this->setVar($itemKey, $itemCode);
        $this->itemObjs[$itemKey] = buildItemClass($itemCode);
    }

    function deleteItem(string $itemKey = 'item')
    {
        $this->setVar($itemKey, 'None');
        $this->itemObjs[$itemKey] = new ActionItem\None();
    }

    function getItem(string $itemKey = 'item'): BaseItem
    {
        return $this->itemObjs[$itemKey];
    }

    function getNPCType(): int
    {
        return $this->raw['npc'];
    }

    /** @return BaseItem[] */
    function getItems(): array
    {
        return $this->itemObjs;
    }

    function getPersonality(): iAction
    {
        return $this->personalityObj;
    }

    function getSpecialDomestic(): iAction
    {
        return $this->specialDomesticObj;
    }

    function getSpecialWar(): iAction
    {
        return $this->specialWarObj;
    }

    function getLastTurn(): LastTurn
    {
        return $this->lastTurn;
    }

    function _setResultTurn(LastTurn $resultTurn)
    {
        $this->resultTurn = $resultTurn;
    }

    function getResultTurn(): LastTurn
    {
        return $this->resultTurn;
    }

    function clearActivatedSkill()
    {
        foreach ($this->activatedSkill as $skillName => $state) {
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

    function getActivatedSkillLog(): array
    {
        return $this->logActivatedSkill;
    }

    function hasActivatedSkill(string $skillName): bool
    {
        return $this->activatedSkill[$skillName] ?? false;
    }

    function activateSkill(...$skillNames)
    {
        foreach ($skillNames as $skillName) {
            $this->activatedSkill[$skillName] = true;
        }
    }

    function deactivateSkill(...$skillNames)
    {
        foreach ($skillNames as $skillName) {
            $this->activatedSkill[$skillName] = false;
        }
    }

    function getName(): string
    {
        return $this->raw['name'];
    }

    function getInfo(): string
    {
        //iAction용 info로는 적절하지 않음
        return '';
    }

    function getID(): int
    {
        return $this->raw['no'];
    }

    function getRawCity(): ?array
    {
        return $this->rawCity;
    }

    function setRawCity(?array $city)
    {
        $this->rawCity = $city;
    }

    function getCityID(): int
    {
        return $this->raw['city'];
    }

    function getNationID(): int
    {
        return $this->raw['nation'];
    }

    function getStaticNation(): array
    {
        return getNationStaticInfo($this->raw['nation']);
    }

    function getLogger(): ?ActionLogger
    {
        return $this->logger;
    }

    public function getNationTypeObj(): iAction
    {
        return $this->nationType;
    }

    public function getOfficerLevelObj(): iAction
    {
        return $this->officerLevelObj;
    }

    function getCrewTypeObj(): GameUnitDetail
    {
        if($this->crewType === null) {
            throw new \InvalidArgumentException('Invalid CrewType:' . $this->getVar('crewtype'));
        }
        return $this->crewType;
    }

    function calcRecentWarTurn(int $turnTerm): int
    {
        $cacheKey = "recent_war_turn_{$turnTerm}";
        if (key_exists($cacheKey, $this->calcCache)) {
            return $this->calcCache[$cacheKey];
        }
        if (!$this->getVar('recent_war')) {
            $result = 12 * 1000;
            $this->calcCache[$cacheKey] = $result;
            return $result;
        }
        $recwar = new \DateTimeImmutable($this->getVar('recent_war'));
        $turnNow = new \DateTimeImmutable($this->getVar('turntime'));
        $secDiff = TimeUtil::DateIntervalToSeconds($recwar->diff($turnNow));

        if ($secDiff <= 0) {
            $this->calcCache[$cacheKey] = 0;
            return 0;
        }

        $result = intdiv(Util::toInt($secDiff), 60 * $turnTerm);
        $this->calcCache[$cacheKey] = $result;
        return $result;
    }

    function getReservedTurn(int $turnIdx, array $env): GeneralCommand
    {
        $db = DB::db();
        $rawCmd = $db->queryFirstRow('SELECT * FROM general_turn WHERE general_id = %i AND turn_idx = %i', $this->getID(), $turnIdx);
        if (!$rawCmd) {
            return buildGeneralCommandClass(null, $this, $env);
        }
        return buildGeneralCommandClass($rawCmd['action'], $this, $env, Json::decode($rawCmd['arg'] ?? null));
    }

    /**
     * @param General[] $generalList
     * @param int $turnIdxFrom [$turnIdxFrom, $turnIdxTo)
     * @param int $turnIdxTo [$turnIdxFrom, $turnIdxTo)
     * @param array $env
     * @return GeneralCommand[]
     */
    public function getReservedTurnList(int $turnIdxFrom, int $turnIdxTo, array $env)
    {
        if ($turnIdxFrom < 0 || $turnIdxFrom >= GameConst::$maxTurn) {
            throw new \OutOfRangeException('$turnIdxFrom 범위 초과' . $turnIdxFrom);
        }

        if ($turnIdxTo <= $turnIdxFrom || GameConst::$maxTurn < $turnIdxTo) {
            throw new \OutOfRangeException('$turnIdxTo 범위 초과' . $turnIdxTo);
        }

        $db = DB::db();

        $generalID = $this->getID();

        $result = [];

        $rawCmds = $db->queryFirstRow('SELECT * FROM general_turn WHERE general_id = %i AND %i <= turn_idx AND turn_idx < %i ORDER BY turn_idx ASC', $generalID, $turnIdxFrom, $turnIdxTo);

        if (!$rawCmds) {
            foreach (Util::range($turnIdxFrom, $turnIdxTo) as $turnIdx) {
                $result[$turnIdx] = buildGeneralCommandClass(null, $this, $env);
            }
            return $result;
        }

        foreach ($rawCmds as $turnIdx => $rawCmd) {
            $result[$turnIdx] = buildGeneralCommandClass($rawCmd['action'], $this, $env, Json::decode($rawCmd['arg'] ?? null));
        }

        return $result;
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
     * @return float 계산된 능력치
     */

    protected function getStatValue(string $statName, $withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true): float
    {
        $cKey = "{$statName}_{$withInjury}_{$withIActionObj}_{$withStatAdjust}";
        if (key_exists($cKey, $this->calcCache)) {
            $statValue = $this->calcCache[$cKey];
            if ($useFloor) {
                return Util::toInt($statValue);
            }
            return $statValue;
        }

        $statValue = $this->getVar($statName);

        if ($withInjury) {
            $statValue *= (100 - $this->getVar('injury')) / 100;
        }

        if ($withStatAdjust) {
            if ($statName === 'strength') {
                $statValue += Util::round($this->getStatValue('intel', $withInjury, $withIActionObj, false, false) / 4);
            } else if ($statName === 'intel') {
                $statValue += Util::round($this->getStatValue('strength', $withInjury, $withIActionObj, false, false) / 4);
            }
        }

        $statValue = Util::clamp($statValue, 0, GameConst::$maxLevel);

        if ($withIActionObj) {
            foreach ([
                $this->nationType,
                $this->officerLevelObj,
                $this->specialDomesticObj,
                $this->specialWarObj,
                $this->personalityObj,
                $this->inheritBuffObj,
            ] as $actionObj) {
                if ($actionObj !== null) {
                    $statValue = $actionObj->onCalcStat($this, $statName, $statValue);
                }
            }

            foreach ($this->itemObjs as $actionObj) {
                if ($actionObj !== null) {
                    $statValue = $actionObj->onCalcStat($this, $statName, $statValue);
                }
            }
        }

        $this->calcCache[$cKey] = $statValue;

        $statValue = Util::clamp($statValue, 0, GameConst::$maxLevel);

        if ($useFloor) {
            return Util::toInt($statValue);
        }

        return $statValue;
    }

    function getLeadership($withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true): float
    {
        return $this->getStatValue('leadership', $withInjury, $withIActionObj, $withStatAdjust, $useFloor);
    }

    function getStrength($withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true): float
    {
        return $this->getStatValue('strength', $withInjury, $withIActionObj, $withStatAdjust, $useFloor);
    }

    function getIntel($withInjury = true, $withIActionObj = true, $withStatAdjust = true, $useFloor = true): float
    {
        return $this->getStatValue('intel', $withInjury, $withIActionObj, $withStatAdjust, $useFloor);
    }

    function getDex(GameUnitDetail $crewType)
    {
        $armType = $crewType->armType;

        if ($armType == GameUnitConst::T_CASTLE) {
            $armType = GameUnitConst::T_SIEGE;
        }

        return $this->getVar("dex{$armType}");
    }

    function addDex(GameUnitDetail $crewType, float $exp, bool $affectTrainAtmos = false)
    {
        $armType = $crewType->armType;

        if ($armType == GameUnitConst::T_CASTLE) {
            $armType = GameUnitConst::T_SIEGE;
        }

        if ($armType < 0) {
            return;
        }

        if ($armType == GameUnitConst::T_WIZARD) {
            $exp *= 0.9;
        } else if ($armType == GameUnitConst::T_SIEGE) {
            $exp *= 0.9;
        }

        if ($affectTrainAtmos) {
            $exp *= ($this->getVar('train') + $this->getVar('atmos')) / 200;
        }

        $dexType = "dex{$armType}";
        $exp = $this->onCalcStat($this, 'addDex', $exp, ['armType' => $armType]);

        $this->increaseVar($dexType, $exp);
    }

    function addExperience(float $experience, bool $affectTrigger = true)
    {
        if ($affectTrigger) {
            $experience = $this->onCalcStat($this, 'experience', $experience);
        }

        $this->increaseVar('experience', $experience);
        $nextExpLevel = getExpLevel($this->getVar('experience'));
        $comp = $nextExpLevel <=> $this->getVar('explevel');
        if ($comp === 0) {
            return;
        }

        $this->updateVar('explevel', $nextExpLevel);

        $josaRo = JosaUtil::pick($nextExpLevel, '로');
        if ($comp > 0) {
            $this->getLogger()->pushGeneralActionLog("<C>Lv {$nextExpLevel}</>{$josaRo} <C>레벨업</>!", ActionLogger::PLAIN);
        } else {
            $this->getLogger()->pushGeneralActionLog("<C>Lv {$nextExpLevel}</>{$josaRo} <R>레벨다운</>!", ActionLogger::PLAIN);
        }
    }

    function addDedication(float $dedication, bool $affectTrigger = true)
    {
        if ($affectTrigger) {
            $dedication = $this->onCalcStat($this, 'dedication', $dedication);
        }

        $this->increaseVar('dedication', $dedication);
        $nextDedLevel = getDedLevel($this->getVar('dedication'));
        $comp = $nextDedLevel <=> $this->getVar('dedlevel');
        if ($comp === 0) {
            return;
        }

        $this->updateVar('dedlevel', $nextDedLevel);

        $dedLevelText = getDedLevelText($nextDedLevel);
        $billText = number_format(getBillByLevel($nextDedLevel));
        $josaRoDed = JosaUtil::pick($dedLevelText, '로');
        $josaRoBill = JosaUtil::pick($billText, '로');
        if ($comp > 0) {
            $this->getLogger()->pushGeneralActionLog("<Y>{$dedLevelText}</>{$josaRoDed} <C>승급</>하여 봉록이 <C>{$billText}</>{$josaRoBill} <C>상승</>했습니다!", ActionLogger::PLAIN);
        } else {
            $this->getLogger()->pushGeneralActionLog("<Y>{$dedLevelText}</>{$josaRoDed} <R>강등</>되어 봉록이 <C>{$billText}</>{$josaRoBill} <R>하락</>했습니다!", ActionLogger::PLAIN);
        }
    }

    function updateVar(string $key, $value)
    {
        if (($this->raw[$key] ?? null) === $value) {
            return;
        }
        if (!key_exists($key, $this->updatedVar)) {
            $this->updatedVar[$key] = true;
        }
        $this->raw[$key] = $value;
        $this->calcCache = [];
    }

    /**
     * @param \MeekroDB $db
     */
    function kill($db, bool $sendDyingMessage = true, ?string $dyingMessage = null)
    {
        $generalID = $this->getID();
        $logger = $this->getLogger();

        $generalName = $this->getName();

        //유산포인트 관련 항목 환불
        if ($this->getNPCType() < 2) {

            $refundPoint = 0;
            $userID = $this->getVar('owner');
            $userLogger = new UserLogger($userID);
            if ($this->getAuxVar('inheritRandomUnique')) {
                $this->setAuxVar('inheritRandomUnique', null);
                $userLogger->push(sprintf("사망으로 랜덤 유니크 구입 %d 포인트 반환", GameConst::$inheritItemRandomPoint), "inheritPoint");
                $refundPoint += GameConst::$inheritItemRandomPoint;
            }

            //TODO: 경매 최우선 입찰자인경우 반환

            if ($this->getAuxVar('inheritSpecificSpecialWar')) {
                $this->setAuxVar('inheritSpecificSpecialWar', null);
                $userLogger->push(sprintf("사망으로 전투 특기 지정 %d 포인트 반환", GameConst::$inheritSpecificSpecialPoint), "inheritPoint");
                $refundPoint += GameConst::$inheritSpecificSpecialPoint;
            }

            if ($refundPoint > 0) {
                $this->increaseInheritancePoint(InheritanceKey::previous, $refundPoint);
                $this->increaseRankVar(RankColumn::inherit_point_spent_dynamic, -$refundPoint);
            }

            $inheritPointManager = InheritancePointManager::getInstance();
            $inheritPointManager->mergeTotalInheritancePoint($this);
            $inheritPointManager->applyInheritanceUser($this->getVar('owner'));
        }


        // 군주였으면 유지 이음
        $officerLevel = $this->getVar('officer_level');
        if ($officerLevel == 12) {
            nextRuler($this);
            $this->setVar('officer_level', 1);
        }

        // 부대 처리
        $troopLeaderID = $this->getVar('troop');
        if ($troopLeaderID == $generalID) {
            //부대장일 경우
            // 모두 탈퇴
            $db->update('general', [
                'troop' => 0
            ], 'troop=%i', $troopLeaderID);
            // 부대 삭제
            $db->delete('troop', 'troop_leader=%i', $troopLeaderID);
        }


        if ($sendDyingMessage) {
            if ($dyingMessage) {
                $logger->pushGlobalActionLog($dyingMessage);
            } else {
                $dyingMessageObj = new TextDecoration\DyingMessage($this);
                $logger->pushGlobalActionLog($dyingMessageObj->getText());
            }
            $logger->flush();
        }

        $db->update('select_pool', [
            'general_id' => null,
            'owner' => null,
            'reserved_until' => null,
        ], 'general_id=%i', $generalID);

        storeOldGeneral($generalID, $logger->getYear(), $logger->getMonth());

        $db->delete('general', 'no=%i', $generalID);
        $db->delete('general_turn', 'general_id=%i', $generalID);
        $db->delete('rank_data', 'general_id=%i', $generalID);
        $this->updatedVar = [];

        $db->update('nation', [
            'gennum' => $db->sqleval('gennum - 1')
        ], 'nation=%i', $this->getVar('nation'));
    }

    function rebirth()
    {
        $logger = $this->getLogger();

        $generalName = $this->getName();

        $inheritPointManager = InheritancePointManager::getInstance();

        $ownerID = $this->getVar('owner');
        if ($ownerID) {
            $inheritPointManager->mergeTotalInheritancePoint($this, true);
            $inheritPointManager->applyInheritanceUser($ownerID, true);
        }

        $this->multiplyVarWithLimit('leadership', 0.85, 10);
        $this->multiplyVarWithLimit('strength', 0.85, 10);
        $this->multiplyVarWithLimit('intel', 0.85, 10);
        $this->setVar('injury', 0);
        $this->multiplyVar('experience', 0.5);
        $this->multiplyVar('dedication', 0.5);
        $this->setVar('age', 20);
        $this->setVar('specage', 0);
        $this->setVar('specage2', 0);
        $this->multiplyVar('dex1', 0.5);
        $this->multiplyVar('dex2', 0.5);
        $this->multiplyVar('dex3', 0.5);
        $this->multiplyVar('dex4', 0.5);
        $this->multiplyVar('dex5', 0.5);

        foreach (RankColumn::cases() as $rankKey) {
            $this->setRankVar($rankKey, 0);
        }

        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <R>은퇴</>하고 그 자손이 유지를 이어받았습니다.");
        $logger->pushGeneralActionLog('나이가 들어 <R>은퇴</>하고 자손에게 자리를 물려줍니다.', ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog('나이가 들어 은퇴하고, 자손에게 관직을 물려줌');
    }

    function increaseRankVar(RankColumn $key, int $value)
    {
        if ($this->rankVarSet->hasKey($key)) {
            $this->rankVarSet[$key] += $value;
            return;
        }

        if ($this->rankVarRead->hasKey($key)) {
            $this->rankVarSet[$key] = $this->rankVarRead[$key] + $value;
            $this->rankVarRead->remove($key);
            return;
        }

        if ($this->rankVarIncrease->hasKey($key)) {
            $this->rankVarIncrease[$key] += $value;
            return;
        }

        $this->rankVarIncrease[$key] = $value;
    }

    function setRankVar(RankColumn $key, int $value)
    {
        if ($this->rankVarRead->hasKey($key)) {
            $this->rankVarRead->remove($key);
        } else if ($this->rankVarIncrease->hasKey($key)) {
            $this->rankVarIncrease->remove($key);
        }
        $this->rankVarSet[$key] = $value;
    }

    function getRankVar(RankColumn $key, $defaultValue = null): int
    {
        if ($this->rankVarSet->hasKey($key)) {
            return $this->rankVarSet[$key];
        }

        if (!$this->rankVarRead->hasKey($key)) {
            if ($defaultValue === null) {
                throw new \RuntimeException('인자가 없음 : ' . $key->value);
            }
            return $defaultValue;
        }

        return $this->rankVarRead[$key];
    }

    /**
     * @param \MeekroDB $db
     */
    function applyDB($db): bool
    {
        if ($this->lastTurn && $this->getLastTurn() != $this->getResultTurn()) {
            $this->setVar('last_turn', $this->getResultTurn()->toJson());
        }
        $updateVals = $this->getUpdatedValues();


        $generalID = $this->getID();
        $result = false;

        if ($updateVals) {
            $db->update('general', $updateVals, 'no=%i', $generalID);
            $result = $result || $db->affectedRows() > 0;
            if (key_exists('nation', $updateVals)) {
                $db->update('rank_data', [
                    'nation_id' => $updateVals['nation']
                ], 'general_id = %i', $generalID);
                $result = true;
            }
            $this->flushUpdateValues();
        }

        if ($this->rankVarIncrease->count()) {
            foreach ($this->rankVarIncrease as $rankKey => $rankVal) {
                $db->update('rank_data', [
                    'value' => $db->sqleval('value + %i', $rankVal)
                ], 'general_id = %i AND type = %s', $generalID, $rankKey->value);
            }
            $result = true;
        }

        if ($this->rankVarSet->count()) {
            foreach ($this->rankVarSet as $rankKey => $rankVal) {
                $db->update('rank_data', [
                    'value' => $rankVal
                ], 'general_id = %i AND type = %s', $generalID, $rankKey->value);
                $this->rankVarRead[$rankKey] = $rankVal;
            }
            $result = true;
        }

        $this->rankVarIncrease = new Map();
        $this->rankVarSet = new Map();

        $this->getLogger()->flush();
        return $result;
    }

    function checkStatChange(): bool
    {
        $logger = $this->getLogger();
        $limit = GameConst::$upgradeLimit;

        $table = [
            ['통솔', 'leadership'],
            ['무력', 'strength'],
            ['지력', 'intel'],
        ];

        $result = false;

        foreach ($table as [$statNickName, $statName]) {
            $statExpName = $statName . '_exp';

            if ($this->getVar($statExpName) < 0) {
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 떨어졌습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, $limit);
                $this->increaseVar($statName, -1);
                $result = true;
            } else if ($this->getVar($statExpName) >= $limit) {
                if($this->getVar($statName) < GameConst::$maxLevel) {
                    $logger->pushGeneralActionLog("<S>{$statNickName}</>이 <C>1</> 올랐습니다!", ActionLogger::PLAIN);
                    $this->increaseVar($statName, 1);
                }
                $this->increaseVar($statExpName, -$limit);
                $result = true;
            }
        }

        return $result;
    }

    protected function getActionList(): array{
        return array_merge([
            $this->nationType,
            $this->officerLevelObj,
            $this->specialDomesticObj,
            $this->specialWarObj,
            $this->personalityObj,
            $this->crewType,
            $this->inheritBuffObj,
        ], $this->itemObjs);
    }

    public function getPreTurnExecuteTriggerList(General $general): ?GeneralTriggerCaller
    {
        $caller = new GeneralTriggerCaller();
        foreach ($this->getActionList() as $iObj) {

            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $caller->merge($iObj->getPreTurnExecuteTriggerList($general));
        }

        return $caller;
    }
    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux = null): float
    {
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $value = $iObj->onCalcDomestic($turnType, $varType, $value, $aux);
        }
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux = null)
    {
        //xxx: $general?
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $value = $iObj->onCalcStat($this, $statName, $value, $aux);
        }
        return $value;
    }

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        //xxx: $general?
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $value = $iObj->onCalcOpposeStat($this, $statName, $value, $aux);
        }
        return $value;
    }

    public function onCalcStrategic(string $turnType, string $varType, $value)
    {
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $value = $iObj->onCalcStrategic($turnType, $varType, $value);
        }
        return $value;
    }

    public function onCalcNationalIncome(string $type, $amount)
    {
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $amount = $iObj->onCalcNationalIncome($type, $amount);
        }
        return $amount;
    }

    public function onArbitraryAction(General $general, RandUtil $rng, string $actionType, ?string $phase = null, $aux = null): null|array
    {
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $aux = $iObj->onArbitraryAction($general, $rng, $actionType, $phase, $aux);
        }
        return $aux;
    }

    public function getWarPowerMultiplier(WarUnit $unit): array
    {
        //xxx:$unit
        $att = 1;
        $def = 1;
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            [$attV, $defV] = $iObj->getWarPowerMultiplier($unit);
            $att *= $attV;
            $def *= $defV;
        }
        return [$att, $def];
    }
    public function getBattleInitSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        $caller = new WarUnitTriggerCaller();
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $caller->merge($iObj->getBattleInitSkillTriggerList($unit));
        }

        return $caller;
    }
    public function getBattlePhaseSkillTriggerList(WarUnit $unit): ?WarUnitTriggerCaller
    {
        $caller = new WarUnitTriggerCaller(
            new WarUnitTrigger\che_필살시도($unit),
            new WarUnitTrigger\che_필살발동($unit),
            new WarUnitTrigger\che_회피시도($unit),
            new WarUnitTrigger\che_회피발동($unit),
            new WarUnitTrigger\che_계략시도($unit),
            new WarUnitTrigger\che_계략발동($unit),
            new WarUnitTrigger\che_계략실패($unit)
        );
        foreach ($this->getActionList() as $iObj) {
            if (!$iObj) {
                continue;
            }
            /** @var iAction $iObj */
            $caller->merge($iObj->getBattlePhaseSkillTriggerList($unit));
        }

        return $caller;
    }

    static public function mergeQueryColumn(?array $reqColumns = null, int $constructMode = 2): array
    {
        $minimumColumn = ['no', 'name', 'npc', 'city', 'nation', 'officer_level', 'officer_city'];
        $defaultEventColumn = [
            'no', 'name', 'npc', 'owner', 'city', 'nation', 'officer_level', 'officer_city',
            'special', 'special2', 'personal',
            'horse', 'weapon', 'book', 'item', 'last_turn', 'aux',
        ];
        $fullColumn = [
            'no', 'name', 'owner', 'owner_name', 'picture', 'imgsvr', 'nation', 'city', 'troop', 'injury', 'affinity',
            'leadership', 'leadership_exp', 'strength', 'strength_exp', 'intel', 'intel_exp', 'weapon', 'book', 'horse', 'item',
            'experience', 'dedication', 'officer_level', 'officer_city', 'gold', 'rice', 'crew', 'crewtype', 'train', 'atmos', 'turntime',
            'makelimit', 'killturn', 'block', 'dedlevel', 'explevel', 'age', 'startage', 'belong',
            'personal', 'special', 'special2', 'defence_train', 'tnmt', 'npc', 'npc_org', 'deadyear', 'npcmsg',
            'dex1', 'dex2', 'dex3', 'dex4', 'dex5', 'betray',
            'recent_war', 'last_turn', 'myset',
            'specage', 'specage2', 'con', 'connect', 'aux', 'lastrefresh', 'permission', 'penalty',
        ];

        if ($reqColumns === null) {
            return [$fullColumn, RankColumn::cases()];
        }

        /** @var RankColumn[] */
        $rankColumn = [];
        $subColumn = [];
        foreach ($reqColumns as $column) {
            if ($column instanceof RankColumn) {
                $rankColumn[] = $column;
                continue;
            }

            $rankKey = RankColumn::tryFrom($column);
            if ($rankKey !== null) {
                $rankColumn[] = $rankKey;
            } else {
                $subColumn[] = $column;
            }
        }

        if ($constructMode > 1) {
            return [array_unique(array_merge($defaultEventColumn, $subColumn)), $rankColumn];
        }

        return [array_unique(array_merge($minimumColumn, $subColumn)), $rankColumn];
    }

    /**
     * @param ?int[] $generalIDList
     * @param null|array<string|RankColumn> $column
     * @param int $constructMode
     * @return \sammo\General[]
     * @throws MustNotBeReachedException
     */
    static public function createGeneralObjListFromDB(?array $generalIDList, ?array $column = null, int $constructMode = 2): array
    {
        if ($generalIDList === []) {
            return [];
        }

        $db = DB::db();
        if ($constructMode > 0) {
            $gameStor = KVStorage::getStorage($db, 'game_env');
            [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        } else {
            $year = null;
            $month = null;
        }

        /**
         * @var string[] $column
         * @var RankColumn[] $rankColumn
         */
        [$column, $rankColumn] = static::mergeQueryColumn($column, $constructMode);

        if ($generalIDList === null) {
            $rawGenerals = Util::convertArrayToDict(
                $db->query('SELECT %l FROM general WHERE 1', Util::formatListOfBackticks($column)),
                'no'
            );
            $generalIDList = array_keys($rawGenerals);
        } else {
            $rawGenerals = Util::convertArrayToDict(
                $db->query('SELECT %l FROM general WHERE no IN %li', Util::formatListOfBackticks($column), $generalIDList),
                'no'
            );
        }


        /** @var Map<int,Map<RankColumn,int|float>> */
        $rawRanks = new Map();
        if ($rankColumn) {
            $rawValue = $db->queryAllLists(
                'SELECT `general_id`, `type`, `value` FROM rank_data WHERE general_id IN %li AND `type` IN %ls',
                $generalIDList,
                array_map(fn (\BackedEnum $e) => $e->value, $rankColumn)
            );
            foreach ($rawValue as [$generalID, $rawRankType, $rankValue]) {
                if (!$rawRanks->hasKey($generalID)) {
                    $rawRanks[$generalID] = new Map();
                }

                $rankType = RankColumn::from($rawRankType);
                $rawRanks[$generalID][$rankType] = $rankValue;
            }
        }

        $result = [];
        foreach ($generalIDList as $generalID) {
            if (!key_exists($generalID, $rawGenerals)) {
                $result[$generalID] = new DummyGeneral($constructMode > 0);
                continue;
            }
            if ($rawRanks->hasKey($generalID) && $rawRanks[$generalID]->count() !== count($rankColumn)) {
                throw new \RuntimeException('column의 수가 일치하지 않음 : ' . $generalID);
            }
            $result[$generalID] = new static($rawGenerals[$generalID], $rawRanks[$generalID] ?? null, null, null, $year, $month, $constructMode > 1);
        }

        return $result;
    }

    static public function createGeneralObjFromDB(int $generalID, ?array $column = null, int $constructMode = 2): self
    {
        $db = DB::db();
        if ($constructMode > 0) {
            $gameStor = KVStorage::getStorage($db, 'game_env');
            [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        } else {
            $year = null;
            $month = null;
        }

        /**
         * @var string[] $column
         * @var RankColumn[] $rankColumn
         */
        [$column, $rankColumn] = static::mergeQueryColumn($column, $constructMode);

        $rawGeneral = $db->queryFirstRow('SELECT %l FROM general WHERE no = %i', Util::formatListOfBackticks($column), $generalID);
        if (!$rawGeneral) {
            return new DummyGeneral($constructMode > 0);
        }

        $rawRankValues = new Map();
        if ($rankColumn) {
            $rawValue = $db->queryAllLists(
                'SELECT `type`, `value` FROM rank_data WHERE general_id = %i AND `type` IN %ls',
                $generalID,
                array_map(fn (\BackedEnum $e) => $e->value, $rankColumn)
            );
            foreach ($rawValue as [$rawRankType, $rankValue]) {
                $rankType = RankColumn::tryFrom($rawRankType);
                $rawRankValues->put($rankType, $rankValue);
            }
        }


        $general = new static($rawGeneral, $rawRankValues, null, null, $year, $month, $constructMode > 1);

        return $general;
    }

    /**
     * @param General[] $generalList
     * @param int $turnIdx
     * @param array $env
     * @return GeneralCommand[]
     */
    static public function getReservedTurnByGeneralList(array $generalList, int $turnIdx, array $env)
    {
        if (!$generalList) {
            return [];
        }

        $generalIDList = array_map(function (General $general) {
            return $general->getID();
        }, $generalList);

        $db = DB::db();
        $result = [];
        $rawCmds = Util::convertArrayToDict($db->query('SELECT * FROM general_turn WHERE general_id IN %li AND turn_idx = %i', $generalIDList, $turnIdx), 'general_id');
        foreach ($generalList as $general) {
            $generalID = $general->getID();
            if (!key_exists($generalID, $rawCmds)) {
                $result[$generalID] = buildGeneralCommandClass(null, $general, $env);
                continue;
            }
            $rawCmd = $rawCmds[$generalID];
            $result[$generalID] = buildGeneralCommandClass($rawCmd['action'], $general, $env, Json::decode($rawCmd['arg']));
        }
        return $result;
    }

    /**
     * @param General[] $generalList
     * @param int $turnIdxFrom [$turnIdxFrom, $turnIdxTo)
     * @param int $turnIdxTo [$turnIdxFrom, $turnIdxTo)
     * @param array $env
     * @return GeneralCommand[][]
     */
    static public function getReservedTurnListByGeneralList(array $generalList, int $turnIdxFrom, int $turnIdxTo, array $env)
    {
        //XXX: static인데 return값이 General이 아니라고?? GeneralCommandHelper같은게 있어야하지 않을까?
        if (!$generalList) {
            return [];
        }

        if ($turnIdxFrom < 0 || $turnIdxFrom >= GameConst::$maxTurn) {
            throw new \OutOfRangeException('$turnIdxFrom 범위 초과' . $turnIdxFrom);
        }

        if ($turnIdxTo <= $turnIdxFrom || GameConst::$maxTurn < $turnIdxTo) {
            throw new \OutOfRangeException('$turnIdxTo 범위 초과' . $turnIdxTo);
        }

        $generalIDList = array_map(function (General $general) {
            return $general->getID();
        }, $generalList);

        $db = DB::db();

        $rawCmds = $db->queryFirstRow('SELECT * FROM general_turn WHERE general_id IN %i AND %i <= turn_idx AND turn_idx < %i ORDER BY general_id ASC, turn_idx ASC', $generalIDList, $turnIdxFrom, $turnIdxTo);
        $orderedRawCmds = [];
        foreach ($rawCmds as $rawCmd) {
            $generalID = $rawCmd['general_id'];
            $turnIdx = $rawCmd['turn_idx'];
            if (!key_exists($generalID, $orderedRawCmds)) {
                $orderedRawCmds[$generalID] = [];
            }
            $orderedRawCmds[$generalID][$turnIdx] = $rawCmd;
        }

        $result = [];
        foreach ($generalList as $general) {
            $generalID = $general->getID();
            $result[$generalID] = [];
            if (!key_exists($generalID, $orderedRawCmds)) {
                foreach (Util::range($turnIdxFrom, $turnIdxTo) as $turnIdx) {
                    $result[$generalID][$turnIdx] = buildGeneralCommandClass(null, $general, $env);
                }
                continue;
            }
            foreach ($orderedRawCmds[$generalID] as $turnIdx => $rawCmd) {
                $result[$generalID][$turnIdx] = buildGeneralCommandClass($rawCmd['action'], $general, $env, Json::decode($rawCmd['arg']));
            }
        }
        return $result;
    }

    public function getInheritancePoint(InheritanceKey $key, &$aux = null, bool $forceCalc = false): int|float|null
    {
        return InheritancePointManager::getInstance()->getInheritancePoint($this, $key, $aux, $forceCalc);
    }

    public function setInheritancePoint(InheritanceKey $key, $value, $aux = null)
    {
        return InheritancePointManager::getInstance()->setInheritancePoint($this, $key, $value, $aux);
    }

    public function increaseInheritancePoint(InheritanceKey $key, $value, $aux = null)
    {
        return InheritancePointManager::getInstance()->increaseInheritancePoint($this, $key, $value, $aux);
    }

    public function mergeTotalInheritancePoint(bool $isRebirth = false)
    {
        InheritancePointManager::getInstance()->mergeTotalInheritancePoint($this, $isRebirth);
    }
}
