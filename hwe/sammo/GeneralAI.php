<?php

namespace sammo;

use sammo\Command\GeneralCommand;
use sammo\Command\NationCommand;
use sammo\Enums\RankColumn;
use sammo\Scenario\NPC;

class GeneralAI
{
    protected RandUtil $rng;

    protected array $city;
    protected array $nation;
    protected int $genType;

    /** @var AutorunGeneralPolicy */
    protected $generalPolicy;
    /** @var AutorunNationPolicy */
    protected $nationPolicy;

    /** @var array */
    protected $env;
    protected $baseDevelCost;

    protected $leadership;
    protected $strength;
    protected $intel;

    protected $fullLeadership;
    protected $fullStrength;
    protected $fullIntel;

    protected $dipState;
    protected $warTargetNation;
    /** @var bool */
    protected $attackable;

    protected $devRate = null;

    protected $nationCities;
    protected $frontCities;
    protected $supplyCities;
    protected $backupCities;

    protected $warRoute;

    protected $maxResourceActionAmount;

    /** @var General[] */
    protected $nationGenerals;
    /** @var General[] */
    protected $npcCivilGenerals;
    /** @var General[] */
    protected $npcWarGenerals;
    /** @var General[] */
    protected $userGenerals;
    /** @var General[] (전쟁 가능한 기간만 따져서) 최근 12턴 내에 전투를 수행한, 또는 전투를 수행할 수 있는 유저장 목록*/
    protected $userWarGenerals;
    /** @var General[] */
    protected $userCivilGenerals;
    /** @var General[] */
    protected $chiefGenerals;

    /** @var bool */
    protected $reqUpdateInstance = true;

    /** @var General[] */
    protected $lostGenerals;
    /** @var General[] 이번 턴에 '집합'하는 부대장 목록 */
    protected $troopLeaders;

    const t무장 = 1;
    const t지장 = 2;
    const t통솔장 = 4;

    const d평화 = 0;
    const d선포 = 1;
    const d징병 = 2;
    const d직전 = 3;
    const d전쟁 = 4;

    protected function updateInstance(): void{
        if(!$this->reqUpdateInstance){
            return;
        }

        $this->reqUpdateInstance = false;

        $db = DB::db();
        $this->baseDevelCost = $this->env['develcost'] * 12;
        $general = $this->general;
        $city = $general->getRawCity();
        if ($city === null) {
            $city = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $general->getCityID());
            $general->setRawCity($city);
        }
        $this->city = $city;

        $this->nation = $db->queryFirstRow(
            'SELECT nation,name,color,capital,capset,gennum,gold,rice,bill,rate,rate_tmp,scout,war,strategic_cmd_limit,surlimit,tech,power,level,chief_set,type,aux FROM nation WHERE nation = %i',
            $general->getNationID()
        ) ?? [
            'nation' => 0,
            'level' => 0,
            'capital' => 0,
            'capset' => 0,
            'gennum' => 0,
            'tech' => 0,
            'gold' => 0,
            'rice' => 0,
            'type' => GameConst::$neutralNationType,
            'color' => '#000000',
            'name' => '재야',
        ];
        $nationStor = KVStorage::getStorage($db, $this->nation['nation'], 'nation_env');
        $nationStor->cacheValues(['npc_nation_policy', 'npc_general_policy', 'prev_income_gold', 'prev_income_rice']);

        $this->nationPolicy = new AutorunNationPolicy($general, $this->env['autorun_user']['options'] ?? null, $nationStor->getValue('npc_nation_policy'), ($this->env['npc_nation_policy']) ?? null, $this->nation, $this->env);
        $this->generalPolicy = new AutorunGeneralPolicy($general, $this->env['autorun_user']['options'] ?? null, $nationStor->getValue('npc_general_policy'), ($this->env['npc_general_policy']) ?? null, $this->nation, $this->env);

        $prevIncomeGold = $nationStor->prev_income_gold ?? 1000;
        $prevIncomeRice = $nationStor->prev_income_rice ?? 1000;
        $this->maxResourceActionAmount = Util::valueFit(Util::round(max(
            $this->nationPolicy->minimumResourceActionAmount,
            $prevIncomeGold / 10,
            $prevIncomeRice / 10,
            $this->nation['gold'] / 5,
            $this->nation['rice'] / 5,
            ($this->env['year'] - $this->env['startyear'] - 3) * 1000
        ), -2), null, $this->nationPolicy->maximumResourceActionAmount);
        if ($this->maxResourceActionAmount > GameConst::$maxResourceActionAmount) {
            $this->maxResourceActionAmount = GameConst::$maxResourceActionAmount;
        }

        $this->nation['aux'] = Json::decode($this->nation['aux'] ?? '{}');

        $this->calcDiplomacyState();

        $this->genType = $this->calcGenType($general);
    }

    public function __construct(protected General $general)
    {
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $this->env = $gameStor->getAll(true);

        $this->rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'GeneralAI',
            $this->env['year'],
            $this->env['month'],
            $general->getID(),
        )));

        $this->leadership = $general->getLeadership();
        $this->strength = $general->getStrength();
        $this->intel = $general->getIntel();

        $this->fullLeadership = $general->getLeadership(false);
        $this->fullStrength = $general->getStrength(false);
        $this->fullIntel = $general->getIntel(false);
    }

    public function getGeneralObj(): General
    {
        return $this->general;
    }

    protected function calcGenType(General $general)
    {
        $leadership = $general->getLeadership(false);
        $strength = Util::valueFit($general->getStrength(false), 1);
        $intel = Util::valueFit($general->getIntel(false), 1);

        //무장
        if ($strength >= $intel) {
            $genType = self::t무장;
            if ($intel >= $strength * 0.8) {  //무지장
                if ($this->rng->nextBool($intel / $strength / 2)) {
                    $genType |= self::t지장;
                }
            }
            //지장
        } else {
            $genType = self::t지장;
            if ($strength >= $intel * 0.8) {  //지무장
                if ($this->rng->nextBool($strength / $intel / 2)) {
                    $genType |= self::t무장;
                }
            }
        }

        //통솔
        if ($leadership >= $this->nationPolicy->minNPCWarLeadership) {
            $genType |= self::t통솔장;
        }
        return $genType;
    }

    protected function calcDiplomacyState()
    {
        $db = DB::db();
        $nationID = $this->general->getNationID();
        $env = $this->env;

        $yearMonth = Util::joinYearMonth($env['year'], $env['month']);

        $warTarget = $db->queryAllLists(
            'SELECT you, state, term FROM diplomacy WHERE me = %i AND state IN (0, 1)',
            $nationID
        );

        if ($yearMonth <= Util::joinYearMonth($env['startyear'] + 2, 5)) {
            if(!$warTarget){
                $this->dipState = self::d평화;
                $this->attackable = false;
            }
            else{
                $this->dipState = self::d선포;
                $this->attackable = false;
            }
            return;
        }

        $frontStatus = $db->queryFirstField('SELECT max(front) FROM city WHERE nation=%i AND supply=1', $nationID);
        // 공격가능도시 있으면
        $this->attackable = !!$frontStatus;

        $onWar = 0;
        $onWarReady = 0;
        $onWarYet = 0;
        $warTargetNation = [];
        foreach ($warTarget as [$warNationID, $warState, $warTerm]) {
            if ($warState == 0) {
                $onWar += 1;
                $warTargetNation[$warNationID] = 2;
            } else if ($warState == 1 && $warTerm < 5) {
                $onWarReady += 1;
                $warTargetNation[$warNationID] = 1;
            } else {
                $onWarYet += 1;
            }
        }

        if (!$onWar && !$onWarReady) {
            $warTargetNation[0] = 1;
        }

        $this->warTargetNation = $warTargetNation;

        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $minWarTerm = $db->queryFirstField('SELECT min(term) FROM diplomacy WHERE me = %i AND state=1', $nationID);
        if ($minWarTerm === null) {
            $this->dipState = self::d평화;
        } else if ($minWarTerm > 8) {
            $this->dipState = self::d선포;
        } else if ($minWarTerm > 5) {
            $this->dipState = self::d징병;
        } else {
            $this->dipState = self::d직전;
            $nationStor->last_attackable = $yearMonth;
        }

        if (key_exists(0, $warTargetNation) && $this->attackable) {
            $this->dipState = self::d전쟁;
            $nationStor->last_attackable = $yearMonth;
        } else if ($onWar) {
            if ($this->attackable) {
                $this->dipState = self::d전쟁;
                $nationStor->last_attackable = $yearMonth;
            } else if ($nationStor->last_attackable >= $yearMonth - 5) {
                //그러나 접경이 없음. 대신, 접경이 사라진지 아직 5개월 이내.
                $this->dipState = self::d전쟁;
            }
        }
    }

    protected function calcWarRoute()
    {
        if ($this->warRoute !== null) {
            return;
        }
        $target = array_keys($this->warTargetNation ?? []);
        $target[] = $this->nation['nation'];

        $this->warRoute = searchAllDistanceByNationList($target, false);
    }

    protected function do부대전방발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (!$this->frontCities) {
            return null;
        }
        $this->calcWarRoute();
        $troopCandidate = [];

        $chiefTurn = cutTurn($this->general->getTurnTime(), $this->env['turnterm']);
        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);

        foreach ($this->troopLeaders as $troopLeader) {
            $leaderID = $troopLeader->getID();
            if (!key_exists($leaderID, $this->nationPolicy->CombatForce)) {
                continue;
            }

            $currentCityID = $troopLeader->getCityID();

            if (key_exists($currentCityID, $this->frontCities)) {
                continue;
            }

            $last발령 = $troopLeader->getAuxVar('last발령');
            if ($last발령) {
                $leaderTurn = cutTurn($troopLeader->getTurnTime(), $this->env['turnterm']);
                $compYearMonth = $yearMonth;
                if ($chiefTurn < $leaderTurn) {
                    $compYearMonth += 1;
                }
                if ($compYearMonth === $yearMonth) {
                    //한턴마다 한번씩만 발령하자.
                    continue;
                }
            }

            [$fromCityID, $toCityID] = $this->nationPolicy->CombatForce[$leaderID];

            if (!key_exists($fromCityID, $this->warRoute) && !key_exists($toCityID, $this->warRoute)) {
                //공격 루트 상실, 전방 아무데나
                $troopCandidate[] = [$leaderID, $this->rng->choice($this->frontCities)['city']];
                continue;
            }

            if (!key_exists($toCityID, $this->warRoute[$fromCityID])) {
                //공격 루트 상실, 전방 아무데나
                $troopCandidate[] = [$leaderID, $this->rng->choice($this->frontCities)['city']];
                continue;
            }

            if (key_exists($fromCityID, $this->supplyCities) && key_exists($toCityID, $this->supplyCities)) {
                //점령 완료, 전방 아무데나
                $troopCandidate[] = [$leaderID, $this->rng->choice($this->frontCities)['city']];
                continue;
            }


            //출발지가 아국땅이 아닌경우  수도->출발지
            if (!key_exists($fromCityID, $this->supplyCities)) {
                $toCityID = $fromCityID;
                $fromCityID = $this->nation['capital'];
            }

            $targetCityID = $fromCityID;
            //접경에 도달할때까지 전진
            while (!key_exists($targetCityID, $this->frontCities)) {
                $distance = $this->warRoute[$targetCityID][$toCityID];
                $nextCityCandidate = [];
                foreach (array_keys(CityConst::byID($targetCityID)->path) as $nearCityID) {
                    if (!key_exists($nearCityID, $this->warRoute) || !key_exists($toCityID, $this->warRoute[$nearCityID])) {
                        continue;
                    }
                    if ($this->warRoute[$nearCityID][$toCityID] + 1 > $distance) {
                        continue;
                    }
                    $nextCityCandidate[] = $nearCityID;
                }
                if (!$nextCityCandidate) {
                    throw new MustNotBeReachedException('경로 계산 버그');
                }
                if (count($nextCityCandidate) == 1) {
                    $targetCityID = $nextCityCandidate[0];
                    continue;
                }
                $targetCityID = $this->rng->choice($nextCityCandidate);
            }

            $troopCandidate[] = ['destGenaralID' => $leaderID, 'destCityID' => $targetCityID];
        }

        if (!$troopCandidate) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, $this->rng->choice($troopCandidate));
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do부대후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (!$this->frontCities) {
            return null;
        }

        $chiefTurn = cutTurn($this->general->getTurnTime(), $this->env['turnterm']);
        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);

        $troopCandidate = [];
        foreach ($this->troopLeaders as $troopLeader) {
            $leaderID = $troopLeader->getID();
            if (!in_array($leaderID, $this->nationPolicy->SupportForce)) {
                continue;
            }
            $currentCityID = $troopLeader->getCityID();
            if (!key_exists($currentCityID, $this->supplyCities)) {
                $troopCandidate[$leaderID] = $troopLeader;
                continue;
            }

            //충분히 징병 가능한 도시의 부대는 제자리 유지
            $city = $this->supplyCities[$currentCityID];
            if ($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }

            $last발령 = $troopLeader->getAuxVar('last발령');
            if ($last발령) {
                $leaderTurn = cutTurn($troopLeader->getTurnTime(), $this->env['turnterm']);
                $compYearMonth = $yearMonth;
                if ($chiefTurn < $leaderTurn) {
                    $compYearMonth += 1;
                }
                if ($compYearMonth === $yearMonth) {
                    //한턴마다 한번씩만 발령하자.
                    continue;
                }
            }


            $troopCandidate[$leaderID] = $troopLeader;
        }

        if (!$troopCandidate) {
            return null;
        }

        if (count($this->supplyCities) == 1) {
            return null;
        }

        $cityCandidates = [];

        foreach ($this->backupCities as $city) {
            if ($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }
            $cityCandidates[] = $city;
        }

        if (!$cityCandidates) {
            foreach ($this->supplyCities as $city) {
                if ($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                    continue;
                }
                $cityCandidates[] = $city;
            }
        }

        if (!$cityCandidates) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $this->rng->choice($troopCandidate)->getID(),
            'destCityID' => $this->rng->choice($cityCandidates)['city']
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do부대구출발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (!$this->frontCities) {
            return null;
        }

        $troopCandidate = [];
        foreach ($this->troopLeaders as $troopLeader) {
            $leaderID = $troopLeader->getID();
            if (in_array($leaderID, $this->nationPolicy->SupportForce)) {
                continue;
            }
            if (key_exists($leaderID, $this->nationPolicy->CombatForce)) {
                continue;
            }

            $currentCityID = $troopLeader->getCityID();
            if (key_exists($currentCityID, $this->supplyCities)) {
                continue;
            }

            $troopCandidate[$leaderID] = $troopLeader;
        }

        if (!$troopCandidate) {
            return null;
        }

        $cityCandidates = [];

        foreach ($this->frontCities as $city) {
            $cityCandidates[] = $city;
        }

        if (!$cityCandidates) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $this->rng->choice($troopCandidate)->getID(),
            'destCityID' => $this->rng->choice($cityCandidates)['city']
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }


    protected function do부대유저장후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->frontCities) {
            return null;
        }
        if ($this->dipState !== self::d전쟁) {
            return null;
        }

        $generalCadidates = [];

        foreach ($this->userWarGenerals as $userGeneral) {
            $generalID = $userGeneral->getID();
            if ($generalID == $this->general->getID()) {
                continue;
            }
            if (!key_exists($userGeneral->getCityID(), $this->frontCities)) {
                continue;
            }
            if (!key_exists($userGeneral->getCityID(), $this->nationCities)) {
                continue;
            }
            $city = $this->nationCities[$userGeneral->getCityID()];
            $troopLeaderID = $userGeneral->getVar('troop');
            if (!$troopLeaderID || !key_exists($troopLeaderID, $this->troopLeaders)) {
                continue;
            }
            if ($troopLeaderID === $userGeneral->getID()) {
                continue;
            }
            $troopLeader = $this->nationGenerals[$troopLeaderID];
            if ($troopLeader->getCityID() !== $userGeneral->getCityID()) {
                continue;
            }
            if (!key_exists($troopLeader->getCityID(), $this->supplyCities)) {
                continue;
            }
            if ($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }
            if ($userGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew) {
                continue;
            }
            if ($userGeneral->onCalcDomestic('징집인구', 'score', 100) <= 1) {
                continue;
            }

            $generalTurnTime = $userGeneral->getTurnTime();
            $troopTurnTime =  $troopLeader->getTurnTime();

            if ($generalTurnTime < $troopTurnTime) { //NOTE: 어차피 수뇌 턴이 제일 빠르다
                $generalCadidates[$generalID] = $userGeneral;
            }
        }

        if (!$generalCadidates) {
            return null;
        }

        $turnList = General::getReservedTurnByGeneralList($generalCadidates, 0, $this->env);
        $generalCadidates = array_filter($generalCadidates, function (General $general) use ($turnList) {
            $generalID = $general->getID();
            if ($turnList[$generalID] instanceof Command\General\che_징병) {
                return true;
            } else {
                return false;
            }
        });

        if (!$generalCadidates) {
            return null;
        }

        if (count($this->supplyCities) == 1) {
            return null;
        }

        $cityCandidates = [];

        foreach ($this->backupCities as $city) {
            if ($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }
            $cityCandidates[] = $city;
        }

        if (!$cityCandidates) {
            foreach ($this->supplyCities as $city) {
                if ($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                    continue;
                }
                $cityCandidates[] = $city;
            }
        }

        if (!$cityCandidates) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $this->rng->choice($generalCadidates)->getID(),
            'destCityID' => $this->rng->choice($cityCandidates)['city']
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }
    protected function do유저장후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if ($this->dipState !== self::d전쟁) {
            return null;
        }

        $generalCadidates = [];

        foreach ($this->userWarGenerals as $userGeneral) {
            $generalID = $userGeneral->getID();
            if ($generalID == $this->general->getID()) {
                continue;
            }
            if (!key_exists($userGeneral->getCityID(), $this->supplyCities)) {
                continue;
            }
            $city = $this->supplyCities[$userGeneral->getCityID()];
            if ($userGeneral->getVar('troop') !== 0) {
                continue;
            }
            if ($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }
            if ($userGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew) {
                continue;
            }
            if ($userGeneral->onCalcDomestic('징집인구', 'score', 100) <= 1) {
                continue;
            }
            $generalCadidates[$generalID] = $userGeneral;
        }

        if (!$generalCadidates) {
            return null;
        }

        if (count($this->supplyCities) == 1) {
            return null;
        }

        /** @var General */
        $pickedGeneral = $this->rng->choice($generalCadidates);
        $pickedGeneralLeadership = $pickedGeneral->getLeadership(false, true, true, true);
        $minRecruitPop = $pickedGeneralLeadership * 100 + GameConst::$minAvailableRecruitPop;

        $recruitableCityList = [];

        foreach ($this->backupCities as $candidateCity) {
            $pop_ratio = $candidateCity['pop'] / $candidateCity['pop_max'];
            $cityID = $candidateCity['city'];
            if ($candidateCity['city'] == $this->city['city']) {
                continue;
            }
            if ($candidateCity['pop'] < $minRecruitPop) {
                continue;
            }

            if ($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                $pop_ratio /= 4;
            }

            $recruitableCityList[$cityID] = $pop_ratio;
        }

        if (!$recruitableCityList) {
            foreach ($this->supplyCities as $candidateCity) {
                $pop_ratio = $candidateCity['pop'] / $candidateCity['pop_max'];
                $cityID = $candidateCity['city'];
                if ($candidateCity['city'] == $this->city['city']) {
                    continue;
                }
                if ($candidateCity['pop'] <= $minRecruitPop) {
                    continue;
                }

                if ($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                    $pop_ratio /= 2;
                }

                if (key_exists($cityID, $this->frontCities)) {
                    $pop_ratio / 2;
                }

                $recruitableCityList[$cityID] = $pop_ratio;
            }
        }

        if (!$recruitableCityList) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $pickedGeneral->getID(),
            'destCityID' => $this->rng->choiceUsingWeight($recruitableCityList)
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do유저장구출발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }

        //고립 도시 장수 발령
        $args = [];
        foreach ($this->lostGenerals as $lostGeneral) {
            if ($lostGeneral->getNPCType() >= 2) {
                continue;
            }
            if (
                $lostGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew &&
                $lostGeneral->getVar('train') >= $lostGeneral->getVar('defence_train') &&
                $lostGeneral->getVar('atmos') >= $lostGeneral->getVar('defence_train')
            ) {
                //수비도 가능한데, 일부러 가 있는 것으로 보임
                continue;
            }

            $troopID = $lostGeneral->getVar('troop');
            if ($troopID && key_exists($troopID, $this->troopLeaders)) {
                $troopLeader = $this->troopLeaders[$troopID];

                if (
                    key_exists($troopLeader->getCityID(), $this->supplyCities) &&
                    $this->troopLeaders[$troopID]->getTurnTime() < $lostGeneral->getTurnTime()
                ) {
                    //이미 탈출 가능한 부대를 탔다
                    continue;
                }
            }

            if (in_array($this->dipState, [self::d직전, self::d전쟁]) && count($this->frontCities) > 2) {
                $selCity = $this->rng->choice($this->frontCities);
            } else {
                $selCity = $this->rng->choice($this->supplyCities);
            }
            //고립된 장수가 많을 수록 발령 확률 증가
            $args[] = [
                'destGeneralID' => $lostGeneral->getID(),
                'destCityID' => $selCity['city']
            ];
        }
        if (!$args) {
            return null;
        }

        $arg = $this->rng->choice($args);
        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, $arg);

        if (!$cmd->hasFullConditionMet()) {
            LogText('구출실패', [$arg, $cmd->getFailString()]);
            return null;
        }
        return $cmd;
    }

    protected function do유저장전방발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (!$this->frontCities) {
            return null;
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        $generalCandidates = [];
        foreach ($this->userWarGenerals as $userGeneral) {
            $generalID = $userGeneral->getID();
            $cityID = $userGeneral->getCityID();
            if (!key_exists($cityID, $this->nationCities)) {
                continue;
            }
            if (key_exists($cityID, $this->frontCities)) {
                continue;
            }
            if ($userGeneral->getVar('crew') < $this->nationPolicy->minWarCrew) {
                continue;
            }
            if ($userGeneral->getVar('troop')) {
                continue;
            }

            $train = $userGeneral->getVar('train');
            $atmos = $userGeneral->getVar('atmos');

            if (max($train, $atmos) < $this->nationPolicy->properWarTrainAtmos) {
                continue;
            }

            $generalCandidates[$generalID] = $userGeneral;
        }

        if (!$generalCandidates) {
            return null;
        }

        $cityCandidates = [];
        foreach ($this->frontCities as $frontCity) {
            $cityCandidates[$frontCity['city']] = $frontCity['important'];
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $this->rng->choice($generalCandidates)->getID(),
            'destCityID' => $this->rng->choiceUsingWeight($cityCandidates)
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do유저장내정발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (count($this->supplyCities) === 1) {
            return null;
        }

        $avgDev = array_sum(array_column($this->supplyCities, 'dev')) / count($this->supplyCities);
        if ($avgDev >= 0.99) {
            return null;
        }

        $userGenerals = $this->userCivilGenerals;
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            $userGenerals = array_merge($this->userWarGenerals, $userGenerals);
        }

        $generalCandidates = [];
        foreach ($userGenerals as $general) {
            /** @var General $general */
            if ($general->getVar('troop')) {
                continue;
            }
            $cityID = $general->getCityID();
            if (!key_exists($cityID, $this->supplyCities)) {
                continue;
            }

            $city = $this->supplyCities[$cityID];

            if ($city['dev'] < 0.95) {
                continue;
            }
            $generalCandidates[] = $general;
        }

        if (!$generalCandidates) {
            return null;
        }

        $cityCandidiates = [];
        foreach ($this->supplyCities as $city) {
            $dev = min($city['dev'], 0.999);
            $score = 1 - $dev;
            $score **= 2;
            $score /= sqrt(count($city['generals'] ?? []) + 1);
            $cityCandidiates[$city['city']] = $score;
        }

        /** @var General */
        $destGeneral = $this->rng->choice($generalCandidates);
        $srcCity = $this->supplyCities[$destGeneral->getCityID()];
        $destCity = $this->supplyCities[$this->rng->choiceUsingWeight($cityCandidiates)];

        if ($srcCity['dev'] <= $destCity['dev']) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $destGeneral->getID(),
            'destCityID' => $destCity['city']
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function doNPC후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (!$this->frontCities) {
            return null;
        }
        if ($this->dipState !== self::d전쟁) {
            return null;
        }

        $generalCadidates = [];

        foreach ($this->npcWarGenerals as $npcGeneral) {
            $generalID = $npcGeneral->getID();
            if ($generalID == $this->general->getID()) {
                continue;
            }
            if (!key_exists($npcGeneral->getCityID(), $this->supplyCities)) {
                continue;
            }
            $city = $this->supplyCities[$npcGeneral->getCityID()];
            if ($npcGeneral->getVar('troop') !== 0) {
                continue;
            }
            if ($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }
            if ($npcGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew) {
                continue;
            }
            if ($npcGeneral->onCalcDomestic('징집인구', 'score', 100) <= 1) {
                continue;
            }
            $generalCadidates[$generalID] = $npcGeneral;
        }

        if (!$generalCadidates) {
            return null;
        }

        if (count($this->supplyCities) == 1) {
            return null;
        }

        /** @var General */
        $pickedGeneral = $this->rng->choice($generalCadidates);
        $pickedGeneralLeadership = $pickedGeneral->getLeadership(false, true, true, true);
        $minRecruitPop = $pickedGeneralLeadership * 100 + GameConst::$minAvailableRecruitPop;
        $minRecruitPop = max($minRecruitPop, $pickedGeneralLeadership * 100 + $this->nationPolicy->minNPCRecruitCityPopulation);

        $recruitableCityList = [];

        foreach ($this->backupCities as $candidateCity) {
            $pop_ratio = $candidateCity['pop'] / $candidateCity['pop_max'];
            $cityID = $candidateCity['city'];
            if ($candidateCity['city'] == $this->city['city']) {
                continue;
            }
            if ($candidateCity['pop'] < $this->nationPolicy->minNPCRecruitCityPopulation) {
                continue;
            }
            if ($candidateCity['pop'] < $minRecruitPop) {
                continue;
            }
            if ($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }

            $recruitableCityList[$cityID] = $pop_ratio;
        }

        if (!$recruitableCityList) {
            foreach ($this->supplyCities as $candidateCity) {
                $pop_ratio = $candidateCity['pop'] / $candidateCity['pop_max'];
                $cityID = $candidateCity['city'];
                if ($candidateCity['city'] == $this->city['city']) {
                    continue;
                }
                if ($candidateCity['pop'] < $this->nationPolicy->minNPCRecruitCityPopulation) {
                    continue;
                }
                if ($candidateCity['pop'] <= $minRecruitPop) {
                    continue;
                }
                if ($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                    continue;
                }

                if (key_exists($cityID, $this->frontCities)) {
                    $pop_ratio / 2;
                }

                $recruitableCityList[$cityID] = $pop_ratio;
            }
        }

        if (!$recruitableCityList) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $pickedGeneral->getID(),
            'destCityID' => $this->rng->choiceUsingWeight($recruitableCityList)
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }


    protected function doNPC구출발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        //고립 도시 장수 발령
        $args = [];
        foreach ($this->lostGenerals as $lostGeneral) {
            if ($lostGeneral->getNPCType() < 2 || $lostGeneral->getNPCType() == 5) {
                continue;
            }
            $selCity = $this->rng->choice($this->supplyCities);
            //고립된 장수가 많을 수록 발령 확률 증가
            $args[] = [
                'destGeneralID' => $lostGeneral->getID(),
                'destCityID' => $selCity['city']
            ];
        }
        if (!$args) {
            return null;
        }
        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, $this->rng->choice($args));
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }
        return $cmd;
    }

    protected function doNPC전방발령(LastTurn $lastTurn): ?NationCommand
    {
        $me = $this->general;
        $cityName = CityConst::byID($me->getCityID())->name;

        if (!$this->nation['capital']) {
            return null;
        }
        if (!$this->frontCities) {
            return null;
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        $generalCandidates = [];
        foreach ($this->npcWarGenerals as $npcGeneral) {
            $generalID = $npcGeneral->getID();
            $cityID = $npcGeneral->getCityID();
            if (key_exists($cityID, $this->frontCities)) {
                continue;
            }
            if (!key_exists($cityID, $this->nationCities)) {
                continue;
            }
            if ($npcGeneral->getVar('crew') < $this->nationPolicy->minWarCrew) {
                continue;
            }
            if ($npcGeneral->getVar('troop')) {
                continue;
            }

            $train = $npcGeneral->getVar('train');
            $atmos = $npcGeneral->getVar('atmos');

            if (max($train, $atmos) < $this->nationPolicy->properWarTrainAtmos) {
                continue;
            }

            $generalCandidates[$generalID] = $npcGeneral;
        }

        if (!$generalCandidates) {
            return null;
        }

        $cityCandidates = [];
        foreach ($this->frontCities as $frontCity) {
            $cityCandidates[$frontCity['city']] = $frontCity['important'];
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $this->rng->choice($generalCandidates)->getID(),
            'destCityID' => $this->rng->choiceUsingWeight($cityCandidates)
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function doNPC내정발령(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->nation['capital']) {
            return null;
        }
        if (count($this->supplyCities) === 1) {
            return null;
        }

        $avgDev = array_sum(array_column($this->supplyCities, 'dev')) / count($this->supplyCities);
        if ($avgDev >= 0.99) {
            return null;
        }

        $npcGenerals = $this->npcCivilGenerals;
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            $npcGenerals = array_merge($this->npcWarGenerals, $npcGenerals);
        }

        $generalCandidates = [];
        foreach ($npcGenerals as $general) {
            /** @var General $general */
            $cityID = $general->getCityID();
            if (!key_exists($cityID, $this->supplyCities)) {
                continue;
            }
            $city = $this->supplyCities[$cityID];

            if ($city['dev'] < 0.95) {
                continue;
            }
            $generalCandidates[] = $general;
        }

        if (!$generalCandidates) {
            return null;
        }

        $cityCandidiates = [];
        foreach ($this->supplyCities as $city) {
            $dev = min($city['dev'], 0.999);
            $score = 1 - $dev;
            $score **= 2;
            $score /= sqrt(count($city['generals'] ?? []) + 1);
            $cityCandidiates[$city['city']] = $score;
        }

        /** @var General */
        $destGeneral = $this->rng->choice($generalCandidates);
        $srcCity = $this->supplyCities[$destGeneral->getCityID()];
        $destCity = $this->supplyCities[$this->rng->choiceUsingWeight($cityCandidiates)];

        if ($srcCity['dev'] <= $destCity['dev']) {
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID' => $destGeneral->getID(),
            'destCityID' => $destCity['city']
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }


    protected function do유저장긴급포상(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->userWarGenerals) {
            return null;
        }

        $nation = $this->nation;
        $candidateArgs = [];
        $remainResource = [
            'gold' => [
                $nation['gold'],
                $this->nationPolicy->reqHumanWarUrgentGold
            ],
            'rice' => [
                $nation['rice'],
                $this->nationPolicy->reqHumanWarUrgentRice
            ]
        ];


        $userWarGenerals = $this->userWarGenerals;

        foreach ($remainResource as $resName => [$resVal, $reqHumanMinRes]) {
            usort($userWarGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach ($userWarGenerals as $idx => $targetUserGeneral) {
                if ($targetUserGeneral->getVar($resName) >= $reqHumanMinRes) {
                    break;
                }
                if ($targetUserGeneral->getVar('killturn') <= 5) {
                    continue;
                }

                $crewtype = $targetUserGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($this->nation['tech'], Util::toInt($targetUserGeneral->getLeadership(false))) * 100 * 3 * 1.1;
                if ($this->env['year'] > $this->env['startyear'] + 3) {
                    $reqMoney = max($reqMoney, $reqHumanMinRes);
                }
                $enoughMoney = $reqMoney * 1.1;

                if ($targetUserGeneral->getVar($resName) >= $reqMoney) {
                    continue;
                }
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetUserGeneral->getVar($resName)) * $resVal);
                $payAmount = Util::valueFit($payAmount, null, $enoughMoney - $targetUserGeneral->getVar($resName));
                if ($payAmount < $this->nationPolicy->minimumResourceActionAmount) {
                    continue;
                }

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetUserGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    count($userWarGenerals) - $idx
                ];
            }
        }

        if (!$candidateArgs) {
            return null;
        }

        $cmd = buildNationCommandClass(
            'che_포상',
            $this->general,
            $this->env,
            $lastTurn,
            $this->rng->choiceUsingWeightPair($candidateArgs)
        );
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        $this->reqUpdateInstance = true;
        return $cmd;
    }

    protected function do유저장포상(LastTurn $lastTurn): ?NationCommand
    {

        if (!$this->userGenerals) {
            return null;
        }

        $nation = $this->nation;
        $candidateArgs = [];
        $remainResource = [
            'gold' => [
                $this->nationPolicy->reqNationGold,
                $nation['gold'],
                $this->nationPolicy->reqHumanWarRecommandGold,
                $this->nationPolicy->reqHumanDevelGold,
            ],
            'rice' => [
                $this->nationPolicy->reqNationRice,
                $nation['rice'],
                $this->nationPolicy->reqHumanWarRecommandRice,
                $this->nationPolicy->reqHumanDevelRice
            ]
        ];


        $userGenerals = $this->userGenerals;


        foreach ($remainResource as $resName => [$reqNationRes, $resVal, $reqHumanMinWarRes, $reqHumanMinDevelRes]) {
            if ($resVal < $reqNationRes) {
                continue;
            }
            usort($userGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach ($userGenerals as $idx => $targetUserGeneral) {
                if ($targetUserGeneral->getVar($resName) >= $reqHumanMinWarRes) {
                    break;
                }
                if ($targetUserGeneral->getVar('killturn') <= 5) {
                    continue;
                }

                if (key_exists($targetUserGeneral->getID(), $this->userWarGenerals)) {
                    $isWarGeneral = true;
                } else {
                    $isWarGeneral = false;
                }

                if ($isWarGeneral) {
                    $crewtype = $targetUserGeneral->getCrewTypeObj();
                    $reqMoney = $crewtype->costWithTech($this->nation['tech'], Util::toInt($targetUserGeneral->getLeadership(false))) * 100 * 6 * 1.1;
                    if ($this->env['year'] > $this->env['startyear'] + 3) {
                        $reqMoney = max($reqMoney, $reqHumanMinWarRes);
                    }
                    $enoughMoney = $reqMoney * 1.2;
                } else {
                    $enoughMoney = $reqHumanMinDevelRes * 1.2;
                }

                if ($targetUserGeneral->getVar($resName) >= $enoughMoney) {
                    continue;
                }
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetUserGeneral->getVar($resName)) * $resVal);
                $payAmount = Util::valueFit($payAmount, $resVal - $reqNationRes, $enoughMoney - $targetUserGeneral->getVar($resName));

                if ($payAmount < $this->nationPolicy->minimumResourceActionAmount) {
                    continue;
                }

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetUserGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    count($userGenerals) - $idx
                ];
            }
        }

        if (!$candidateArgs) {
            return null;
        }

        $cmd = buildNationCommandClass(
            'che_포상',
            $this->general,
            $this->env,
            $lastTurn,
            $this->rng->choiceUsingWeightPair($candidateArgs)
        );
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function doNPC긴급포상(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->npcWarGenerals) {
            return null;
        }

        $nation = $this->nation;
        $candidateArgs = [];
        $remainResource = [
            'gold' => [
                $this->nationPolicy->reqNationGold,
                $nation['gold'],
                $this->nationPolicy->reqNPCWarGold / 2
            ],
            'rice' => [
                $this->nationPolicy->reqNationRice,
                $nation['rice'],
                $this->nationPolicy->reqNPCWarRice / 2
            ]
        ];


        $npcWarGenerals = $this->npcWarGenerals;

        foreach ($remainResource as $resName => [$reqNationRes, $resVal, $reqNPCMinWarRes]) {
            if ($resVal < $reqNationRes) {
                continue;
            }
            usort($npcWarGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach ($npcWarGenerals as $idx => $targetNPCGeneral) {
                if ($targetNPCGeneral->getVar($resName) >= $reqNPCMinWarRes) {
                    break;
                }
                if ($targetNPCGeneral->getVar('killturn') <= 5) {
                    continue;
                }

                $crewtype = $targetNPCGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($this->nation['tech'], Util::toInt($targetNPCGeneral->getLeadership(false))) * 100 * 1.5;
                if ($this->env['year'] > $this->env['startyear'] + 5) {
                    $reqMoney = max($reqMoney, $reqNPCMinWarRes);
                }
                $enoughMoney = $reqMoney * 1.2;

                if ($targetNPCGeneral->getVar($resName) >= $reqMoney) {
                    continue;
                }
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetNPCGeneral->getVar($resName)) * $resVal);
                $payAmount = Util::valueFit($payAmount, $resVal - $reqNationRes * 0.9, $enoughMoney - $targetNPCGeneral->getVar($resName));

                if ($payAmount < $this->nationPolicy->minimumResourceActionAmount) {
                    continue;
                }

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    count($npcWarGenerals) - $idx
                ];
            }
        }

        if (!$candidateArgs) {
            return null;
        }

        $cmd = buildNationCommandClass(
            'che_포상',
            $this->general,
            $this->env,
            $lastTurn,
            $this->rng->choiceUsingWeightPair($candidateArgs)
        );
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        $this->reqUpdateInstance = true;
        return $cmd;
    }

    protected function doNPC포상(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->npcWarGenerals && !$this->npcCivilGenerals) {
            return null;
        }

        $nation = $this->nation;
        $candidateArgs = [];
        $remainResource = [
            'gold' => [
                $this->nationPolicy->reqNationGold,
                $nation['gold'],
                $this->nationPolicy->reqNPCWarGold,
                $this->nationPolicy->reqNPCDevelGold
            ],
            'rice' => [
                $this->nationPolicy->reqNationRice,
                $nation['rice'],
                $this->nationPolicy->reqNPCWarRice,
                $this->nationPolicy->reqNPCDevelRice
            ]
        ];


        $npcWarGenerals = $this->npcWarGenerals;
        $npcCivilGenerals = $this->npcCivilGenerals;

        foreach ($remainResource as $resName => [$reqNationRes, $resVal, $reqNPCMinWarRes, $reqNPCMinDevelRes]) {
            if ($resVal < $reqNationRes) {
                continue;
            }
            usort($npcWarGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach ($npcWarGenerals as $idx => $targetNPCGeneral) {
                if ($targetNPCGeneral->getVar($resName) >= $reqNPCMinWarRes) {
                    break;
                }
                if ($targetNPCGeneral->getVar('killturn') <= 5) {
                    continue;
                }

                $crewtype = $targetNPCGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($nation['tech'], Util::toInt($targetNPCGeneral->getLeadership(false))) * 100 * 3 * 1.1;
                if ($this->env['year'] > $this->env['startyear'] + 5) {
                    $reqMoney = max($reqMoney, $reqNPCMinWarRes);
                }
                $enoughMoney = $reqMoney * 1.5;

                if ($targetNPCGeneral->getVar($resName) >= $reqMoney) {
                    continue;
                }
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetNPCGeneral->getVar($resName)) * $resVal);
                $payAmount = Util::valueFit($payAmount, $resVal - $reqNationRes, $enoughMoney - $targetNPCGeneral->getVar($resName));

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => Util::valueFit($payAmount, 100, $this->maxResourceActionAmount)
                    ],
                    max(count($npcWarGenerals), count($npcCivilGenerals)) - $idx
                ];
            }

            usort($npcCivilGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach ($npcCivilGenerals as $idx => $targetNPCGeneral) {
                if ($targetNPCGeneral->getVar($resName) >= $reqNPCMinDevelRes) {
                    break;
                }
                if ($targetNPCGeneral->getVar('killturn') <= 5) {
                    continue;
                }

                $enoughMoney = $reqNPCMinDevelRes * 1.5;

                $payAmount = $enoughMoney - $targetNPCGeneral->getVar($resName);
                if ($payAmount < $this->nationPolicy->minimumResourceActionAmount) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    max(count($npcWarGenerals), count($npcCivilGenerals)) - $idx
                ];
            }
        }

        if (!$candidateArgs) {
            return null;
        }

        $cmd = buildNationCommandClass(
            'che_포상',
            $this->general,
            $this->env,
            $lastTurn,
            $this->rng->choiceUsingWeightPair($candidateArgs)
        );
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function doNPC몰수(LastTurn $lastTurn): ?NationCommand
    {
        if (!$this->npcWarGenerals && !$this->npcCivilGenerals) {
            return null;
        }


        $nation = $this->nation;
        $candidateArgs = [];
        $remainResource = [
            'gold' => [
                $nation['gold'],
                $this->nationPolicy->reqNationGold,
                $this->nationPolicy->reqNPCWarGold,
                $this->nationPolicy->reqNPCDevelGold,
            ],
            'rice' => [
                $nation['rice'],
                $this->nationPolicy->reqNationRice,
                $this->nationPolicy->reqNPCWarRice,
                $this->nationPolicy->reqNPCDevelRice,
            ]
        ];

        $npcWarGenerals = $this->npcWarGenerals;
        $npcCivilGenerals = $this->npcCivilGenerals;

        foreach ($remainResource as $resName => [$resVal, $reqNationResVal, $reqNPCMinWarRes, $reqNPCMinDevelRes]) {

            usort($npcCivilGenerals, function ($lhs, $rhs) use ($resName) {
                return - ($lhs->getVar($resName) <=> $rhs->getVar($resName));
            });

            foreach ($npcCivilGenerals as $idx => $targetNPCGeneral) {
                if ($targetNPCGeneral->getVar($resName) <= $reqNPCMinDevelRes * 1.5) {
                    break;
                }

                $takeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinDevelRes * 1.2;
                $takeAmount = Util::valueFit($takeAmount, 100, $this->maxResourceActionAmount);
                if ($takeAmount < $this->nationPolicy->minimumResourceActionAmount) {
                    break;
                }

                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $takeAmount
                    ],
                    $takeAmount
                ];
            }


            //전투 NPC는 국고가 충분하지 않아보일때
            $reqResValDelta = $reqNationResVal * 1.5 - $resVal;
            if ($reqResValDelta < 0) {
                continue;
            }

            if ($resVal >= $reqNationResVal) {
                $willTakeSmallAmount = true;
            } else {
                $willTakeSmallAmount = false;
            }


            usort($npcWarGenerals, function ($lhs, $rhs) use ($resName) {
                return - ($lhs->getVar($resName) <=> $rhs->getVar($resName));
            });



            foreach ($npcWarGenerals as $idx => $targetNPCGeneral) {
                if ($willTakeSmallAmount) {
                    if ($targetNPCGeneral->getVar($resName) <= $reqNPCMinWarRes * 2) {
                        break;
                    }
                } else if ($targetNPCGeneral->getVar($resName) <= $reqNPCMinWarRes) {
                    break;
                }

                if (!$willTakeSmallAmount) {
                    $takeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinWarRes;
                    $takeAmount = Util::valueFit(sqrt($takeAmount * $reqResValDelta), 0, $takeAmount);
                } else {
                    $maxTakeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinWarRes;
                    $minTakeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinWarRes * 2;
                    $takeAmount = Util::valueFit(sqrt($minTakeAmount * $reqResValDelta), 0, $maxTakeAmount);
                }

                if ($takeAmount < 100) {
                    break;
                }

                if ($takeAmount < $this->nationPolicy->minimumResourceActionAmount) {
                    break;
                }

                $takeAmount = Util::valueFit($takeAmount, 100, $this->maxResourceActionAmount);
                $candidateArgs[] = [
                    [
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $takeAmount
                    ],
                    $takeAmount
                ];
            }
        }

        if (!$candidateArgs) {
            return null;
        }

        $cmd = buildNationCommandClass(
            'che_몰수',
            $this->general,
            $this->env,
            $lastTurn,
            $this->rng->choiceUsingWeightPair($candidateArgs)
        );
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    // 군주 행동
    protected function do불가침제의(LastTurn $lastTurn): ?NationCommand
    {
        $general = $this->general;

        if ($general->getVar('officer_level') < 12) {
            return null;
        }

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $nationStor = KVStorage::getStorage(DB::db(), $nationID, 'nation_env');
        $recvAssist = $nationStor->getValue('recv_assist') ?? [];
        $respAssist = $nationStor->getValue('resp_assist') ?? [];
        $respAssistTry = $nationStor->getValue('resp_assist_try') ?? [];

        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);

        $candidateList = [];
        foreach ($recvAssist as [$candNationID, $amount]) {
            $amount -= $respAssist["n{$candNationID}"][1] ?? 0;
            if ($amount <= 0) {
                continue;
            }
            if (key_exists($candNationID, $this->warTargetNation)) {
                continue;
            }
            if (($respAssistTry["n{$candNationID}"][1] ?? 0) >= $yearMonth - 8) {
                continue;
            }
            $candidateList[$candNationID] = $amount;
        }

        if (!$candidateList) {
            return null;
        }

        $cityList = $this->supplyCities;

        if (!$cityList) {
            return null;
        }

        $goldIncome = getGoldIncome($nation['nation'], $nation['level'], 15, $nation['capital'], $nation['type'], $cityList);
        $riceIncome = getRiceIncome($nation['nation'], $nation['level'], 15, $nation['capital'], $nation['type'], $cityList);
        $wallIncome = getWallIncome($nation['nation'], $nation['level'], 15, $nation['capital'], $nation['type'], $cityList);
        $income = $goldIncome + $riceIncome + $wallIncome;

        arsort($candidateList);
        $destNationID = null;
        $diplomatMonth = 0;
        foreach ($candidateList as $candNationID => $amount) {
            if ($amount * 4 < $income) {
                break;
            }
            $destNationID = $candNationID;
            $diplomatMonth = 24 * $amount / $income;
            break;
        }

        if ($destNationID === null) {
            return null;
        }


        [$targetYear, $targetMonth] = Util::parseYearMonth($yearMonth + $diplomatMonth);

        $cmd = buildNationCommandClass('che_불가침제의', $this->general, $this->env, $lastTurn, [
            'destNationID' => $destNationID,
            'year' => $targetYear,
            'month' => $targetMonth,
        ]);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        $respAssistTry["n{$destNationID}"] = [$destNationID, $yearMonth];
        $nationStor->setValue('resp_assist_try', $respAssistTry);

        return $cmd;
    }

    // 군주 행동
    protected function do선전포고(LastTurn $lastTurn): ?NationCommand
    {
        $general = $this->general;

        if ($general->getVar('officer_level') < 12) {
            return null;
        }

        if ($this->dipState !== self::d평화) {
            return null;
        }

        if ($this->attackable) {
            return null;
        }

        if (!$this->nation['capital']) {
            return null;
        }

        if ($this->frontCities) {
            return null;
        }

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $currentTech = $nation['tech'];
        if (!TechLimit($this->env['startyear'], $this->env['year'], $currentTech + 1000)) {
            return null;
        }

        $avgGold = 0;
        $avgRice = 0;
        $genCnt = 0;
        foreach ($this->npcWarGenerals as $general) {
            $avgGold += $general->getVar('gold');
            $avgRice += $general->getVar('rice');
            $genCnt += 1;
        }
        foreach ($this->npcCivilGenerals as $general) {
            $avgGold += $general->getVar('gold');
            $avgRice += $general->getVar('rice');
            $genCnt += 1;
        }
        foreach ($this->userWarGenerals as $general) {
            $avgGold += $general->getVar('gold') / 2;
            $avgRice += $general->getVar('rice') / 2;
            $genCnt += 1;
        }
        foreach ($this->userCivilGenerals as $general) {
            $avgGold += $general->getVar('gold') / 2;
            $avgRice += $general->getVar('rice') / 2;
            $genCnt += 1;
        }
        $avgGold += $nation['gold'];
        $avgRice += $nation['rice'];

        if ($genCnt == 0) {
            //장수가 없는데 무슨 선포야.
            return null;
        }
        $avgGold /= $genCnt;
        $avgRice /= $genCnt;

        $trialProp = $avgGold / max($this->nationPolicy->reqNPCWarGold * 1.5, 2000);
        $trialProp += $avgRice / max($this->nationPolicy->reqNPCWarRice * 1.5, 2000);

        $devRate = $this->calcNationDevelopedRate();

        $trialProp += ($devRate['pop'] + $devRate['all']) / 2;

        $trialProp /= 4;
        $trialProp = $trialProp ** 6;

        if (!$this->rng->nextBool($trialProp)) {
            return null;
        }

        $db = DB::db();
        $lowTargetNations = Util::convertArrayToSetLike($db->queryFirstColumn(
            'SELECT DISTINCT(me) FROM diplomacy WHERE me != %i AND state IN %li',
            $nationID,
            [0, 1]
        ));

        $nations = [];
        $warNations = [];
        foreach (getAllNationStaticInfo() as $destNation) {
            if ($destNation['level'] == 0) {
                continue;
            }
            $destNationID = $destNation['nation'];
            $destNationPower = $destNation['power'];
            if (!isNeighbor($nationID, $destNationID)) {
                continue;
            }

            if (!key_exists($destNationID, $lowTargetNations)) {
                $nations[$destNationID] = 1 / sqrt($destNationPower + 1);
            } else {
                $warNations[$destNationID] = 1 / sqrt($destNationPower + 1);
            }
        }
        if (!$nations) {
            if (!$warNations) {
                return null;
            }
            if (!$lowTargetNations) {
                return null;
            }
            if ($this->rng->nextBool(1 / count($lowTargetNations))) {
                return null;
            }
            $nations = $warNations;
        }

        $cmd = buildNationCommandClass('che_선전포고', $this->general, $this->env, $lastTurn, [
            'destNationID' => $this->rng->choiceUsingWeight($nations)
        ]);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        $this->reqUpdateInstance = true;
        return $cmd;
    }

    protected function do천도(LastTurn $lastTurn): ?NationCommand
    {
        $general = $this->general;

        $db = DB::db();

        $nationStor = KVStorage::getStorage($db, $general->getNationID(), 'nation_env');
        $turnTerm = $this->env['turnterm'];

        //천도를 한턴 넣었다면 계속 넣는다.
        if ($lastTurn->getCommand() === '천도' && $lastTurn->getArg()['destCityID'] != $this->nation['capital']) {
            $cmd = buildNationCommandClass('che_천도', $this->general, $this->env, $lastTurn, $lastTurn->getArg());
            if ($cmd->hasFullConditionMet()) {
                $nationStor->last천도Trial = [$general->getVar('officer_level'), $general->getTurnTime()];
                $this->reqUpdateInstance = true;
                return $cmd;
            }
        }

        $lastTrial = $nationStor->last천도Trial;
        if ($lastTrial) {
            [$lastTrialLevel, $lastTrialTurnTime] = $lastTrial;
            $timeDiffSeconds = TimeUtil::DateIntervalToSeconds(
                date_create_immutable($lastTrialTurnTime)->diff(
                    date_create_immutable($general->getTurnTime())
                )
            );
            if ($timeDiffSeconds < $turnTerm * 30 && $lastTrialLevel !== $general->getVar('officer_level')) { //0.5Turn
                return null;
            }
        }

        /*
        도시 점수 공식
        sqrt(인구) * sqrt(모든 아국 도시까지의 거리의 합) * sqrt(내정률)
        가장 높은 도시로 이동하되, 상위 25% 내에 들었다면 정지
        */


        //checkSupply()와 비슷하면서 다름

        $nationCityIDList = [];
        $this->categorizeNationCities();
        foreach ($this->nationCities as $city) {
            $nationCityIDList[$city['city']] = true;
        }

        //애초에 도시랄 것이 없음
        if (count($nationCityIDList) <= 1) {
            return null;
        }

        $queue = new \SplQueue();
        $capital = $this->nation['capital'];
        $cityList = [
            $capital => 0
        ];

        $queue->enqueue($capital);

        //수도와 연결된 도시 탐색
        while (!$queue->isEmpty()) {
            /** @var int */
            $cityID = $queue->dequeue();

            foreach (array_keys(CityConst::byID($cityID)->path) as $nextCityID) {
                if (!key_exists($nextCityID, $nationCityIDList)) {
                    continue;
                }
                if (key_exists($nextCityID, $cityList)) {
                    continue;
                }
                $cityList[$nextCityID] = 0;
                $queue->enqueue($nextCityID);
            }
        }

        $cityList = array_keys($cityList);

        //수도와 연결된 도시가 없음
        if (count($cityList) == 1) {
            return null;
        }

        $distanceList = searchAllDistanceByCityList($cityList);


        $maxDistance = 0;
        foreach ($distanceList as $cityID => $subDistanceList) {
            $maxDistance = max($maxDistance, array_sum($subDistanceList));
        }

        $cityScoreList = [];
        foreach ($cityList as $cityID) {
            $city = $this->nationCities[$cityID];

            $cityScoreList[$cityID] =  $city['pop'] * $maxDistance / array_sum($distanceList[$cityID]) * sqrt($city['dev']);
        }

        arsort($cityScoreList);

        $enoughLimit = ceil(count($cityScoreList) * 0.25);
        foreach (array_keys($cityScoreList) as $idx => $cityID) {
            if ($idx > $enoughLimit) {
                break;
            }
            if ($cityID === $capital) {
                return null;
            }
        }

        $finalCityID = Util::array_first_key($cityScoreList);
        $dist = $distanceList[$capital][$finalCityID];
        $targetCityID = $finalCityID;
        if ($dist > 1) {
            $candidates = [];
            foreach (array_keys(CityConst::byID($capital)->path) as $stopID) {
                if (!key_exists($stopID, $distanceList)) {
                    continue;
                }
                if ($distanceList[$stopID][$finalCityID] + 1 === $dist) {
                    $candidates[] = $stopID;
                }
            }
            $targetCityID = $this->rng->choice($candidates);
        }

        $cmd = buildNationCommandClass('che_천도', $this->general, $this->env, $lastTurn, [
            'destCityID' => $targetCityID
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }


        $nationStor->last천도Trial = [$general->getVar('officer_level'), $general->getTurnTime()];
        $this->reqUpdateInstance = true;
        return $cmd;
    }

    //일반장 행동
    protected function do일반내정(): ?GeneralCommand
    {
        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $general = $this->general;
        $env = $this->env;
        $genType = $this->genType;

        $city = $this->city;
        $nation = $this->nation;

        $develRate = Util::squeezeFromArray($this->calcCityDevelRate($city), 0);
        $isSpringSummer = $this->env['month'] <= 6;

        $cmdList = [];

        if (($nation['rice'] < GameConst::$baserice) && $this->rng->nextBool(0.3)) {
            return null;
        }

        if ($genType & self::t통솔장) {
            if ($develRate['trust'] < 0.98) {
                $cmd = buildGeneralCommandClass('che_주민선정', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['trust'] / 2 - 0.2, 0.001) * 2];
                }
            }
            if ($develRate['pop'] < 0.8) {
                $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'], 0.001)];
                }
            } else if ($develRate['pop'] < 0.99) {
                $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'] / 4, 0.001)];
                }
            }
        }

        if ($genType & self::t무장) {
            if ($develRate['def'] < 1) {
                $cmd = buildGeneralCommandClass('che_수비강화', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['def'], 0.001)];
                }
            }
            if ($develRate['wall'] < 1) {
                $cmd = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['wall'], 0.001)];
                }
            }
            if ($develRate['secu'] < 0.9) {
                $cmd = buildGeneralCommandClass('che_치안강화', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['secu'] / 0.8, 0.001, 1)];
                }
            } else if ($develRate['secu'] < 1) {
                $cmd = buildGeneralCommandClass('che_치안강화', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / 2 / Util::valueFit($develRate['secu'], 0.001)];
                }
            }
        }

        if ($genType & self::t지장) {
            if (!TechLimit($env['startyear'], $env['year'], $nation['tech'])) {
                $cmd = buildGeneralCommandClass('che_기술연구', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $nextTech = $nation['tech'] % 1000 + 1;
                    if (!TechLimit($env['startyear'], $env['year'], $nation['tech'] + 1000)) {
                        //한등급 이상 뒤쳐져 있다면, 조금 더 열심히 하자.
                        $cmdList[] = [$cmd, $intel / ($nextTech / 2000)];
                    } else {
                        $cmdList[] = [$cmd, $intel];
                    }
                }
            }
            if ($develRate['agri'] < 1) {
                $cmd = buildGeneralCommandClass('che_농지개간', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, ($isSpringSummer ? 1.2 : 0.8) * $intel / Util::valueFit($develRate['agri'], 0.001, 1)];
                }
            }
            if ($develRate['comm'] < 1) {
                $cmd = buildGeneralCommandClass('che_상업투자', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, ($isSpringSummer ? 0.8 : 1.2) * $intel / Util::valueFit($develRate['comm'], 0.001, 1)];
                }
            }
        }

        if (!$cmdList) {
            return null;
        }

        return $this->rng->choiceUsingWeightPair($cmdList);
    }

    protected function do긴급내정(): ?GeneralCommand
    {
        if ($this->dipState === self::d평화) {
            return null;
        }

        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $general = $this->general;
        $env = $this->env;
        $genType = $this->genType;

        $city = $this->city;

        if ($city['trust'] < 70 && $this->rng->nextBool($leadership / GameConst::$chiefStatMin)) {
            $cmd = buildGeneralCommandClass('che_주민선정', $general, $env);
            if ($cmd->hasFullConditionMet()) {
                return $cmd;
            }
        }

        if ($city['pop'] < $this->nationPolicy->minNPCRecruitCityPopulation && $this->rng->nextBool($leadership / GameConst::$chiefStatMin / 2)) {
            $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
            if ($cmd->hasFullConditionMet()) {
                return $cmd;
            }
        }

        return null;
    }

    protected function do전쟁내정(): ?GeneralCommand
    {

        if ($this->dipState === self::d평화) {
            return null;
        }

        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $general = $this->general;
        $env = $this->env;
        $genType = $this->genType;

        $city = $this->city;
        $nation = $this->nation;

        if (($nation['rice'] < GameConst::$baserice) && $this->rng->nextBool(0.3)) {
            return null;
        }

        $develRate = Util::squeezeFromArray($this->calcCityDevelRate($city), 0);
        $isSpringSummer = $this->env['month'] <= 6;
        $cmdList = [];

        if ($this->rng->nextBool(0.3)) {
            return null;
        }

        if ($genType & self::t통솔장) {
            if ($develRate['trust'] < 0.98) {
                $cmd = buildGeneralCommandClass('che_주민선정', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['trust'] / 2 - 0.2, 0.001) * 2];
                }
            }
            if ($develRate['pop'] < 0.8) {
                $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    if (in_array($city['front'], [1, 3])) {
                        $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'], 0.001)];
                    } else {
                        $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'], 0.001) / 2];
                    }
                }
            }
        }

        if ($genType & self::t무장) {
            if ($develRate['def'] < 0.5) {
                $cmd = buildGeneralCommandClass('che_수비강화', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['def'], 0.001) / 2];
                }
            }
            if ($develRate['wall'] < 0.5) {
                $cmd = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['wall'], 0.001) / 2];
                }
            }
            if ($develRate['secu'] < 0.5) {
                $cmd = buildGeneralCommandClass('che_치안강화', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['secu'] / 0.8, 0.001, 1) / 4];
                }
            }
        }

        if ($genType & self::t지장) {
            if (!TechLimit($env['startyear'], $env['year'], $nation['tech'])) {
                $cmd = buildGeneralCommandClass('che_기술연구', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $nextTech = $nation['tech'] % 1000 + 1;
                    if (!TechLimit($env['startyear'], $env['year'], $nation['tech'] + 1000)) {
                        //한등급 이상 뒤쳐져 있다면, 조금 더 열심히 하자. 전쟁중이면 더더욱
                        $cmdList[] = [$cmd, $intel / ($nextTech / 3000)];
                    } else {
                        $cmdList[] = [$cmd, $intel];
                    }
                }
            }
            if ($develRate['agri'] < 0.5) {
                $cmd = buildGeneralCommandClass('che_농지개간', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    if (in_array($city['front'], [1, 3])) {
                        $cmdList[] = [$cmd, ($isSpringSummer ? 1.2 : 0.8) * $intel / 4 / Util::valueFit($develRate['agri'], 0.001, 1)];
                    } else {
                        $cmdList[] = [$cmd, ($isSpringSummer ? 1.2 : 0.8) * $intel / 2 / Util::valueFit($develRate['agri'], 0.001, 1)];
                    }
                }
            }
            if ($develRate['comm'] < 0.5) {
                $cmd = buildGeneralCommandClass('che_상업투자', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    if (in_array($city['front'], [1, 3])) {
                        $cmdList[] = [$cmd, ($isSpringSummer ? 0.8 : 1.2) * $intel / 4 / Util::valueFit($develRate['comm'], 0.001, 1)];
                    } else {
                        $cmdList[] = [$cmd, ($isSpringSummer ? 0.8 : 1.2) * $intel / 2 / Util::valueFit($develRate['comm'], 0.001, 1)];
                    }
                }
            }
        }

        if (!$cmdList) {
            return null;
        }

        $cmd = $this->rng->choiceUsingWeightPair($cmdList);
        return $cmd;
    }


    protected function do금쌀구매(): ?GeneralCommand
    {
        $general = $this->general;

        if ($this->city['trade'] === null && !$this->generalPolicy->can상인무시) {
            return null;
        }

        $kill = $general->getRankVar(RankColumn::killcrew) + 50000;
        $death = $general->getRankVar(RankColumn::deathcrew) + 50000;
        $deathRate = $death / $kill;

        $absGold = $general->getVar('gold');
        $absRice = $general->getVar('rice');

        $relGold = $absGold;
        $relRice = $absRice * $deathRate;

        if ($absGold + $absRice < $this->baseDevelCost * 2) {
            return null;
        }

        $crewType = $general->getCrewTypeObj();
        if ($this->generalPolicy->can모병) {
            $costCmd = buildGeneralCommandClass('che_모병', $general, $this->env, [
                'crewType' => $crewType->id,
                'amount' => $this->fullLeadership * 100
            ]);
        } else {
            $costCmd = buildGeneralCommandClass('che_징병', $general, $this->env, [
                'crewType' => $crewType->id,
                'amount' => $this->fullLeadership * 100
            ]);
        }

        $goldCost = $costCmd->getCost()[0];
        $riceCost = $crewType->riceWithTech(
            $this->nation['tech'],
            Util::toInt($this->fullLeadership * 100)
        );

        if (($relGold + $relRice) * 1.5 <= $goldCost + $riceCost) {
            return null;
        }

        $tryBuying = false;
        if ($general->getNPCType() < 2 && $relGold >= $goldCost * 3 && $relRice >= $riceCost * 3) {
            return null;
        }
        if ($this->generalPolicy->can상인무시) {
            if ($relRice * 1.5 < $relGold && $relRice < $riceCost * 2) {
                $tryBuying = true;
            } else if ($relRice * 2 < $relGold) {
                $tryBuying = true;
            }
        } else {
            if ($relRice * 2 < $relGold && $relRice < $riceCost * 3) {
                $tryBuying = true;
            }
        }

        if ($tryBuying) {
            //1:1
            $amount = Util::valueFit(Util::toInt(($relGold - $relRice) / (1 + $deathRate)), 100, GameConst::$maxResourceActionAmount);
            if ($amount >= $this->nationPolicy->minimumResourceActionAmount) {
                $cmd = buildGeneralCommandClass(
                    'che_군량매매',
                    $general,
                    $this->env,
                    [
                        'buyRice' => true,
                        'amount' => $amount
                    ]
                );
                if ($cmd->hasFullConditionMet()) {
                    return $cmd;
                }
            }
        }

        $trySelling = false;
        if ($this->generalPolicy->can상인무시) {
            if ($relGold * 1.5 < $relRice && $relGold < $goldCost * 2) {
                $trySelling = true;
            } else if ($relGold * 2 < $relRice) {
                $trySelling = true;
            }
        } else {
            if ($relGold * 2 < $relRice && $relGold < $goldCost * 3) {
                $trySelling = true;
            }
        }

        if ($trySelling) {
            $amount = Util::valueFit(Util::toInt(($relRice - $relGold) / (1 + $deathRate)), 100, GameConst::$maxResourceActionAmount);
            if ($amount >= $this->nationPolicy->minimumResourceActionAmount) {
                $cmd = buildGeneralCommandClass(
                    'che_군량매매',
                    $general,
                    $this->env,
                    [
                        'buyRice' => false,
                        'amount' => $amount
                    ]
                );
                if ($cmd->hasFullConditionMet()) {
                    return $cmd;
                }
            }
        }



        return null;
    }

    protected function do징병(): ?GeneralCommand
    {
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        if (!($this->genType & self::t통솔장)) {
            return null;
        }



        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        if ($general->getVar('crew') >= $this->nationPolicy->minWarCrew) {
            return null;
        }

        if (!$this->generalPolicy->can한계징병) {
            $remainPop = $city['pop'] - $this->nationPolicy->minNPCRecruitCityPopulation - $this->fullLeadership * 100;
            if ($remainPop <= 0) {
                return null;
            }

            $maxPop = $city['pop_max'] - $this->nationPolicy->minNPCRecruitCityPopulation;
            if (($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio) &&
                ($this->rng->nextBool($remainPop / $maxPop))
            ) {
                return null;
            }
        }

        $nationID = $general->getNationID();

        $genType = $this->genType;

        $tech = $nation['tech'];

        $db = DB::db();

        $armType = $general->getAuxVar('armType');
        if ($armType) {
            if (!($genType & self::t지장) && $armType == GameUnitConst::T_WIZARD) {
                $armType = null;
            } else if (!($genType & self::t무장) && in_array($armType, [GameUnitConst::T_FOOTMAN, GameUnitConst::T_ARCHER, GameUnitConst::T_CAVALRY])) {
                $armType = null;
            }
        }

        if (!$armType) {
            $dex = [
                GameUnitConst::T_FOOTMAN => sqrt($general->getVar('dex1') + 500),
                GameUnitConst::T_ARCHER => sqrt($general->getVar('dex2') + 500),
                GameUnitConst::T_CAVALRY => sqrt($general->getVar('dex3') + 500),
                GameUnitConst::T_WIZARD => sqrt($general->getVar('dex4') + 500),
                GameUnitConst::T_SIEGE => sqrt($general->getVar('dex5') + 500),
            ];

            $availableArmType = [];
            if ($this->fullStrength > $this->fullIntel * 0.9) {
                $availableArmType[GameUnitConst::T_FOOTMAN] = $dex[GameUnitConst::T_FOOTMAN] * $this->fullStrength;
                $availableArmType[GameUnitConst::T_ARCHER] = $dex[GameUnitConst::T_ARCHER] * $this->fullStrength;
                $availableArmType[GameUnitConst::T_CAVALRY] = $dex[GameUnitConst::T_CAVALRY] * $this->fullStrength;
            }
            if ($this->fullIntel > $this->fullStrength * 0.9) {
                $availableArmType[GameUnitConst::T_WIZARD] = $dex[GameUnitConst::T_WIZARD] * $this->fullIntel * 3;
            }

            $armType = $this->rng->choiceUsingWeight($availableArmType);
        }


        $cities = [];
        $regions = [];

        foreach ($db->queryAllLists('SELECT city, region FROM city WHERE nation = %i', $nationID) as [$cityID, $regionID]) {
            $cities[$cityID] = true;
            $regions[$regionID] = true;
        }
        $relYear = Util::valueFit($env['year'] - $env['startyear'], 0);


        $types = [];
        foreach (GameUnitConst::byType($armType) as $crewtype) {
            if ($crewtype->isValid($cities, $regions, $relYear, $tech)) {
                $score = $crewtype->pickScore($tech);
                $types[$crewtype->id] = $score;
            }
        }

        if ($types) {
            $type = $this->rng->choiceUsingWeight($types);
        } else {
            throw new MustNotBeReachedException('에러:' . print_r([$general->getName(), $general->getAuxVar('armType'), $armType, $cities, $regions, $relYear, $tech], true));
        }

        if ($this->generalPolicy->can고급병종) {
            $currCrewType = $general->getCrewTypeObj();
            if ($currCrewType->isValid($cities, $regions, $relYear, $tech)) {
                if ($currCrewType->reqTech >= 2000) {
                    $type = $currCrewType->id;
                } else if ($currCrewType->armType != $armType && $currCrewType->reqTech >= 1000) {
                    //굳이 뽑은 이유가 있겠지
                    $type = $currCrewType->id;
                }
            }
        }

        //XXX: 훈련, 사기진작 금액을 하드코딩으로 계산중
        $gold = $general->getVar('gold');
        $gold -= $this->fullLeadership * 3;
        $rice = $general->getVar('rice');
        $rice -= $this->fullLeadership * 4;

        if ($gold <= 0 || $rice <= 0) {
            return null;
        }

        $crew = $this->fullLeadership * 100;
        $crewType = GameUnitConst::byID($type);

        $riceCost = $crewType->riceWithTech(
            $this->nation['tech'],
            Util::toInt($this->fullLeadership * 100 *
                $general->getRankVar(RankColumn::killcrew) / max($general->getRankVar(RankColumn::deathcrew), 1) * 1.2)
        );

        $cmd = buildGeneralCommandClass('che_징병', $general, $env, [
            'crewType' => $type,
            'amount' => $crew
        ]);

        $cost = $cmd->getCost()[0];

        if ($this->generalPolicy->can모병 && $gold >= $cost * 6) {
            $cmd = buildGeneralCommandClass('che_모병', $general, $env, [
                'crewType' => $type,
                'amount' => $crew
            ]);
        } else if ($gold < $cost && $gold * 2 >= $cost) {
            $crew *= 0.5;
            $riceCost *= 0.5;
            $crew = Util::round($crew - 49, -2);
            $cmd = buildGeneralCommandClass('che_징병', $general, $env, [
                'crewType' => $type,
                'amount' => $crew
            ]);
        }

        if (!$this->generalPolicy->can한계징병 && $rice * 1.1 <= $riceCost) {
            //이 쌀도 없어?
            return null;
        }

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }
        return $cmd;
    }

    protected function do전투준비(): ?GeneralCommand
    {
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }
        $cmdList = [];
        $general = $this->general;

        $train = $general->getVar('train');
        $atmos = $general->getVar('atmos');

        if ($train < $this->nationPolicy->properWarTrainAtmos) {
            $cmd = buildGeneralCommandClass('che_훈련', $general, $this->env);
            if ($cmd->hasFullConditionMet()) {
                $cmdList[] = [$cmd, GameConst::$maxTrainByCommand / Util::valueFit($train, 1)];
            }
        }

        if ($atmos < $this->nationPolicy->properWarTrainAtmos) {
            $cmd = buildGeneralCommandClass('che_사기진작', $general, $this->env);
            if ($cmd->hasFullConditionMet()) {
                $cmdList[] = [$cmd, GameConst::$maxAtmosByCommand / Util::valueFit($atmos, 1)];
            }
        }

        if (!$cmdList) {
            return null;
        }
        return $this->rng->choiceUsingWeightPair($cmdList);
    }

    protected function do소집해제(): ?GeneralCommand
    {
        if ($this->attackable) {
            return null;
        }
        if ($this->dipState !== self::d평화) {
            return null;
        }
        if ($this->general->getVar('crew') == 0) {
            return null;
        }
        if ($this->rng->nextBool(0.75)) {
            return null;
        }
        $cmd = buildGeneralCommandClass('che_소집해제', $this->general, $this->env);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }
        return $cmd;
    }


    protected function do출병(): ?GeneralCommand
    {
        if (!$this->attackable) {
            return null;
        }

        if ($this->dipState !== self::d전쟁) {
            return null;
        }

        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;

        if (($nation['rice'] < GameConst::$baserice) && $this->rng->nextBool(0.7)) {
            return null;
        }

        $cityID = $city['city'];
        $nationID = $nation['nation'];

        $db = DB::db();

        if ($general->getVar('train') < min(100, $this->nationPolicy->properWarTrainAtmos)) {
            return null;
        }
        if ($general->getVar('atmos') < min(100, $this->nationPolicy->properWarTrainAtmos)) {
            return null;
        }
        if ($general->getVar('crew') < min(($this->fullLeadership - 2) * 100, $this->nationPolicy->minWarCrew)) {
            return null;
        }

        if ($city['front'] === 0) {
            return null;
        }

        if ($city['front'] === 1) {
            return null;
        }

        $attackableNations = [];
        foreach ($this->warTargetNation as $targetNationID => $state) {
            if ($state == 1) {
                continue;
            }
            $attackableNations[] = $targetNationID;
        }
        if (!$attackableNations) {
            $attackableNations[] = 0;
        }
        $nearCities = array_keys(CityConst::byID($cityID)->path);

        $attackableCities = $db->queryFirstColumn(
            'SELECT city, nation FROM city WHERE nation IN %li AND city IN %li',
            $attackableNations,
            $nearCities
        );

        if (count($attackableCities) == 0) {
            throw new \RuntimeException('출병 불가' . $cityID . var_export($attackableNations, true) . var_export($nearCities, true));
        }

        $cmd = buildGeneralCommandClass('che_출병', $general, $this->env, ['destCityID' => $this->rng->choice($attackableCities)]);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }


    /*
    protected function doNPC증여(): ?GeneralCommand
    {
        return null;
    }
    */

    protected function doNPC헌납(): ?GeneralCommand
    {
        $policy = $this->nationPolicy;
        $general = $this->general;
        $resourceMap = [
            ['rice', $policy->reqNationRice, $policy->reqNPCWarRice, $policy->reqNPCDevelRice],
            ['gold', $policy->reqNationGold, $policy->reqNPCWarGold, $policy->reqNPCDevelGold],
        ];

        $args = [];

        foreach ($resourceMap as [$resKey, $reqNation, $reqNPCWar, $reqNPCDevel]) {
            $genRes = $general->getVar($resKey);

            if ($this->genType & self::t통솔장) {
                $reqRes = $reqNPCWar;
            } else {
                $reqRes = $reqNPCDevel;
                if ($genRes >= $reqNPCWar && $reqNPCWar > $reqNPCDevel + 1000) {
                    $amount = $genRes - $reqNPCDevel;
                    $args[] = [[
                        'isGold' => $resKey === 'gold',
                        'amount' => $amount
                    ], $amount];
                    continue;
                }

                if ($genRes >= $reqNPCDevel * 5 && $genRes >= 5000) {
                    $amount = $genRes - $reqNPCDevel;
                    $args[] = [[
                        'isGold' => $resKey === 'gold',
                        'amount' => $amount
                    ], $amount];
                    continue;
                }
            }

            if ($this->nation[$resKey] >= $reqNation) {
                continue;
            }
            if ($resKey === 'rice' && $this->nation[$resKey] <= GameConst::$minNationalRice / 2 && $genRes >= GameConst::$minNationalRice / 2) {
                if ($genRes < GameConst::$minNationalRice) {
                    $args[] = [[
                        'isGold' => 'rice',
                        'amount' => $genRes
                    ], $genRes];
                } else {
                    $args[] = [[
                        'isGold' => 'rice',
                        'amount' => $genRes / 2
                    ], $genRes / 2];
                }
            }
            if ($genRes < $reqRes * 1.5) {
                continue;
            }
            if ($reqRes > 0 && !$this->rng->nextBool(($genRes / $reqRes) - 0.5)) {
                continue;
            }
            $amount = $genRes - $reqRes;
            if ($amount < $this->nationPolicy->minimumResourceActionAmount) {
                continue;
            }
            $args[] = [[
                'isGold' => $resKey === 'gold',
                'amount' => $amount
            ], $amount];
        }

        if (!$args) {
            return null;
        }

        $cmd = buildGeneralCommandClass('che_헌납', $general, $this->env, $this->rng->choiceUsingWeightPair($args));
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }
        return $cmd;
    }


    protected function do후방워프(): ?GeneralCommand
    {
        $minRecruitPop = $this->fullLeadership * 100 + GameConst::$minAvailableRecruitPop;
        if (!$this->generalPolicy->can한계징병) {
            $minRecruitPop = max($minRecruitPop, $this->fullLeadership * 100 + $this->nationPolicy->minNPCRecruitCityPopulation);
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 외교 상태", $this->dipState);
            return null;
        }

        if (!$this->generalPolicy->can징병) {
            LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 징병 금지", '');
            return null;
        }

        if (!($this->genType & self::t통솔장)) {
            LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 통솔장", [$this->genType, $this->general->getLeadership(false)]);
            return null;
        }

        if ($this->general->getVar('crew') >= $this->nationPolicy->minWarCrew) {
            LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 병력 충분", [$this->general->getVar('crew'), $this->nationPolicy->minWarCrew]);
            return null;
        }

        $city = $this->city;
        if ($this->generalPolicy->can한계징병) {
            if ($city['pop'] >= $minRecruitPop) {
                return null;
            }
        } else {
            if ($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio) {
                if ($city['pop'] >= $this->nationPolicy->minNPCRecruitCityPopulation && $city['pop'] >= $minRecruitPop) {
                    LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 인구 충분", [$city['pop'] / $city['pop_max'], $this->nationPolicy->safeRecruitCityPopulationRatio]);
                    return null;
                }
            }
        }

        $this->categorizeNationCities();

        $recruitableCityList = [];

        foreach ($this->backupCities as $candidateCity) {
            $pop_ratio = $candidateCity['pop'] / $candidateCity['pop_max'];
            $cityID = $candidateCity['city'];
            if ($candidateCity['city'] == $this->city['city']) {
                continue;
            }
            if ($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                continue;
            }
            if ($candidateCity['pop'] < $this->nationPolicy->minNPCRecruitCityPopulation) {
                continue;
            }
            if ($candidateCity['pop'] < $minRecruitPop) {
                continue;
            }
            $recruitableCityList[$cityID] = $pop_ratio;
        }

        if (!$recruitableCityList) {
            foreach ($this->supplyCities as $candidateCity) {
                $pop_ratio = $candidateCity['pop'] / $candidateCity['pop_max'];
                $cityID = $candidateCity['city'];
                if ($candidateCity['city'] == $this->city['city']) {
                    continue;
                }
                if ($candidateCity['pop'] < $this->nationPolicy->minNPCRecruitCityPopulation) {
                    continue;
                }
                if ($candidateCity['pop'] <= $minRecruitPop) {
                    continue;
                }
                if ($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio) {
                    continue;
                }
                if (key_exists($cityID, $this->frontCities)) {
                    $recruitableCityList[$cityID] = $pop_ratio / 2;
                } else {
                    $recruitableCityList[$cityID] = $pop_ratio;
                }
            }
        }

        if (!$recruitableCityList) {
            LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 배후 도시", [count($this->backupCities), count($this->supplyCities)]);
            return null;
        }


        $cmd = buildGeneralCommandClass('che_NPC능동', $this->general, $this->env, [
            'optionText' => '순간이동',
            'destCityID' => $this->rng->choiceUsingWeight($recruitableCityList),
        ]);


        if (!$cmd->hasFullConditionMet()) {
            LogText("{$this->general->getName()}, {$this->general->getID()} 후방워프 불가: 조건 불충족", $cmd->getArg());
            return null;
        }

        return $cmd;
    }

    protected function do전방워프(): ?GeneralCommand
    {
        if (!$this->attackable) {
            return null;
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        if (!($this->genType & self::t통솔장)) {
            return null;
        }

        if ($this->general->getVar('crew') < $this->nationPolicy->minWarCrew) {
            return null;
        }

        if ($this->city['front']) {
            return null;
        }

        $this->categorizeNationCities();
        $this->categorizeNationGeneral();

        $candidateCities = [];

        if (!$this->frontCities) {
            throw new \sammo\MustNotBeReachedException('attackable인데 frontCities가 없음');
        }

        foreach ($this->frontCities as $frontCity) {
            if(!$frontCity['supply']){
                continue;
            }
            $candidateCities[$frontCity['city']] = $frontCity['important'];
        }

        $cmd = buildGeneralCommandClass('che_NPC능동', $this->general, $this->env, [
            'optionText' => '순간이동',
            'destCityID' => $this->rng->choiceUsingWeight($candidateCities),
        ]);


        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do내정워프(): ?GeneralCommand
    {
        if (($this->genType & self::t통솔장) && in_array($this->dipState, [self::d징병, self::d직전, self::d전쟁])) {
            return null;
        }

        $city = $this->city;
        if ($this->rng->nextBool(0.6)) {
            return null;
        }

        $develRate = $this->calcCityDevelRate($city);
        $availableTypeCnt = 0;

        $warpProp = 1;
        foreach ($develRate as $develKey => [$develVal, $develType]) {
            if (!($this->genType & $develType)) {
                continue;
            }
            $warpProp *= $develVal;
            $availableTypeCnt += 1;
        }

        if ($availableTypeCnt === 0) {
            //무능장은 인탐을 하세요.
            return null;
        }

        if (!$this->rng->nextBool($warpProp)) {
            return null;
        }

        $this->categorizeNationCities();
        $candidateCities = [];
        foreach ($this->supplyCities as $candidate) {
            if ($city['city'] === $candidate['city']) {
                continue;
            }
            $realDevelRate = 0.0001; //하단의 나눗셈

            foreach ($this->calcCityDevelRate($candidate) as $develKey => [$develVal, $develType]) {
                if (!($this->genType & $develType)) {
                    continue;
                }
                $realDevelRate += $develVal;
            }


            $realDevelRate /= $availableTypeCnt;

            if ($realDevelRate >= 0.95) {
                continue;
            }

            $candidateCities[$candidate['city']] = 1 / ($realDevelRate * \sqrt(count($candidate['generals'] ?? []) + 1));
        }

        if (!$candidateCities) {
            return null;
        }

        $cmd = buildGeneralCommandClass('che_NPC능동', $this->general, $this->env, [
            'optionText' => '순간이동',
            'destCityID' => $this->rng->choiceUsingWeight($candidateCities),
        ]);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }


    protected function do귀환(): ?GeneralCommand
    {
        $general = $this->general;
        $city = $this->city;
        if ($city['nation'] == $general->getNationID() && $city['supply']) {
            return null;
        }

        $cmd = buildGeneralCommandClass('che_귀환', $this->general, $this->env);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do집합(): ?GeneralCommand
    {
        $general = $this->general;

        if ($general->getNPCType() == 5) {
            $newKillTurn = ($general->getVar('killturn') + $this->rng->nextRangeInt(2, 4)) % 5;
            $newKillTurn += 70;
            $general->setVar('killturn', $newKillTurn);
        }

        $cmd = buildGeneralCommandClass('che_집합', $general, $this->env);
        _setGeneralCommand($cmd, iterator_to_array(Util::range(GameConst::$maxTurn)));

        return $cmd;
    }

    protected function do방랑군이동(): ?GeneralCommand
    {
        $db = DB::db();

        $general = $this->general;
        $lordCities = $db->queryFirstColumn('SELECT city.city as city FROM general LEFT JOIN city ON general.city = city.city WHERE general.officer_level = 12 AND city.nation = 0');
        $nationCities = $db->queryFirstColumn('SELECT city FROM city WHERE nation != 0');

        $occupiedCities = [];
        foreach ($lordCities as $tCityId) {
            $occupiedCities[$tCityId] = 2;
        }
        foreach ($nationCities as $tCityId) {
            $occupiedCities[$tCityId] = 1;
        }

        $movingTargetCityID = $general->getAuxVar('movingTargetCityID');
        $currCityID = $general->getCityID();

        if ($movingTargetCityID === $currCityID) {
            $movingTargetCityID = null;
        } else if (key_exists($movingTargetCityID, $occupiedCities)) {
            $movingTargetCityID = null;
        }

        if ($movingTargetCityID === null) {
            //어느 도시로 갈 것인가?
            $candidateCities = [];
            foreach (searchDistance($currCityID, 4) as $testCityID => $dist) {
                if (key_exists($testCityID, $occupiedCities)) {
                    continue;
                }
                $cityLevel = CityConst::byID($testCityID)->level;
                if ($cityLevel < 5 || 6 < $cityLevel) {
                    continue;
                }
                $candidateCities[] = [$testCityID, 1 / pow(2, $dist)];
            }

            if (!$candidateCities) {
                return null;
            }
            $movingTargetCityID = $this->rng->choiceUsingWeightPair($candidateCities);
            $general->setAuxVar('movingTargetCityID', $movingTargetCityID);
        }

        if ($movingTargetCityID == $currCityID) {
            return buildGeneralCommandClass('che_인재탐색', $general, $this->env);
        }

        $distMap = searchDistance($movingTargetCityID, 99);

        $targetDistance = $distMap[$currCityID];
        $candidateCities = [];

        foreach (array_keys(CityConst::byID($currCityID)->path) as $nearCityID) {
            $cityLevel = CityConst::byID($nearCityID)->level;
            if (5 <= $cityLevel && $cityLevel <= 6 && !key_exists($nearCityID, $occupiedCities)) {
                //바로 옆 도시로 이동하면 건국 가능하다면? 가보자
                $candidateCities[] = [$nearCityID, 10];
            }
            if ($distMap[$nearCityID] + 1 == $targetDistance) {
                $candidateCities[] = [$nearCityID, 1];
            }
        }
        if (!$candidateCities) {
            return null;
        }

        $cmd = buildGeneralCommandClass('che_이동', $general, $this->env, [
            'destCityID' => $this->rng->choiceUsingWeightPair($candidateCities)
        ]);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do거병(): ?GeneralCommand
    {
        $general = $this->general;
        // 초반이면서 능력이 좋은놈 위주로 거병
        if ($general->getVar('makelimit')) {
            return null;
        }
        if ($general->getNPCType() > 2) {
            return null;
        }
        if (!$this->generalPolicy->can건국) {
            return null;
        }

        $currentCityLevel = CityConst::byID($general->getCityID())->level;
        if (($currentCityLevel < 5 || 6 < $currentCityLevel) && $this->rng->nextBool(0.5)) {
            return null;
        }

        $db = DB::db();

        $lordCities = $db->queryFirstColumn('SELECT city.city as city FROM general LEFT JOIN city ON general.city = city.city WHERE general.officer_level = 12 AND city.nation = 0');
        $nationCities = $db->queryFirstColumn('SELECT city FROM city WHERE nation != 0');

        $occupiedCities = [];
        foreach ($lordCities as $tCityId) {
            $occupiedCities[$tCityId] = 2;
        }
        foreach ($nationCities as $tCityId) {
            $occupiedCities[$tCityId] = 1;
        }

        $availableNearCity = false;
        foreach (searchDistance($general->getCityID(), 3) as $targetCityID => $dist) {
            if (key_exists($targetCityID, $occupiedCities)) {
                continue;
            }
            $cityLevel = CityConst::byID($targetCityID)->level;
            if ($cityLevel < 5 || 6 < $cityLevel) {
                continue;
            }
            if ($dist == 3 && $this->rng->nextBool()) {
                continue;
            }
            $availableNearCity = true;
            break;
        }
        if (!$availableNearCity) {
            return null;
        }

        $prop = $this->rng->nextFloat1() * (GameConst::$defaultStatNPCMax + GameConst::$chiefStatMin) / 2;
        $ratio = ($this->fullLeadership + $this->fullStrength + $this->fullIntel) / 3;


        if ($prop >= $ratio) {
            return null;
        }

        //XXX: 건국기한 2년
        $more = Util::valueFit(3 - $this->env['year'] + $this->env['init_year'], 1, 3);
        if (!$this->rng->nextBool(0.0075 * $more)) {
            return null;
        }

        $cmd = buildGeneralCommandClass('che_거병', $general, $this->env, null);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do해산(): ?GeneralCommand
    {
        $cmd = buildGeneralCommandClass('che_해산', $this->general, $this->env, null);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        $this->general->setAuxVar('movingTargetCityID', null);

        return $cmd;
    }

    protected function do건국(): ?GeneralCommand
    {
        $nationType = $this->rng->choice(GameConst::$availableNationType);
        $nationColor = $this->rng->choice(array_keys(GetNationColors()));
        $cmd = buildGeneralCommandClass('che_건국', $this->general, $this->env, [
            'nationName' => "㉿" . mb_substr($this->general->getName(), 1),
            'nationType' => $nationType,
            'colorType' => $nationColor
        ]);
        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        $this->general->setAuxVar('movingTargetCityID', null);

        return $cmd;
    }

    protected function do선양(): ?GeneralCommand
    {
        $db = DB::db();
        $cmd = buildGeneralCommandClass('che_선양', $this->general, $this->env, [
            'destGeneralID' => $db->queryFirstField('SELECT `no` FROM general WHERE nation = %i AND npc != 5 ORDER BY RAND() LIMIT 1', $this->general->getNationID())
        ]);

        if (!$cmd->hasFullConditionMet()) {
            return null;
        }

        return $cmd;
    }

    protected function do국가선택(): ?GeneralCommand
    {
        $general = $this->getGeneralObj();
        $city = $this->city;
        $env = $this->env;

        $db = DB::db();

        // 오랑캐는 바로 임관
        if ($general->getNPCType() == 9) {
            $rulerNation = $db->queryFirstField(
                'SELECT nation FROM general WHERE `officer_level`=12 AND npc=9 and nation ORDER BY RAND() limit 1'
            );

            if ($rulerNation) {
                $cmd = buildGeneralCommandClass('che_임관', $general, $env, ['destNationID' => $rulerNation]);
                if (!$cmd->hasFullConditionMet()) {
                    return null;
                }

                return $cmd;
            }
        }

        if ($this->rng->nextBool(0.3)) {
            if ($general->getVar('affinity') == 999) {
                return null;
            }

            if ($env['year'] < $env['startyear'] + 3) {
                //초기 임관 기간에서는 국가가 적을수록 임관 시도가 적음
                $nationCnt = $db->queryFirstField('SELECT count(nation) FROM nation');
                $notFullNationCnt = $db->queryFirstField('SELECT count(nation) FROM nation WHERE gennum < %i', GameConst::$initialNationGenLimit);
                if ($nationCnt == 0 || $notFullNationCnt == 0) {
                    return null;
                }

                if ($this->rng->nextBool(pow(1 / ($nationCnt + 1) / pow($notFullNationCnt, 3), 1 / 4))) {
                    return null;
                }
            }
            else{
                //임관 기간이 끝나면 고정 확률로 낮춤 0.3 * 0.5 = 0.15
                if ($this->rng->nextBool()){
                    return null;
                }
            }

            //랜임 커맨드 입력.
            $cmd = buildGeneralCommandClass('che_랜덤임관', $general, $env);
            if (!$cmd->hasFullConditionMet()) {
                return null;
            }

            return $cmd;
        }

        if ($this->rng->nextBool(0.2)) {
            $paths = array_keys(CityConst::byID($city['city'])->path);

            $cmd = buildGeneralCommandClass('che_이동', $general, $env, ['destCityID' => $this->rng->choice($paths)]);
            if (!$cmd->hasFullConditionMet()) {
                return null;
            }

            return $cmd;
        }
        return null;
    }

    protected function doNPC사망대비(): ?GeneralCommand
    {
        $general = $this->getGeneralObj();

        if ($general->getVar('killturn') > 5) {
            return null;
        }

        if ($general->getNationID() == 0) {
            $cmd = buildGeneralCommandClass('che_인재탐색', $general, $this->env);
            if (!$cmd->hasFullConditionMet() || $this->rng->nextBool()) {
                $cmd = buildGeneralCommandClass('che_견문', $general, $this->env);
            }
            return $cmd;
        }

        if ($general->getVar('gold') + $general->getVar('rice') == 0) {
            return buildGeneralCommandClass('che_물자조달', $general, $this->env);
        }

        if ($general->getVar('gold') >= $general->getVar('rice')) {
            return buildGeneralCommandClass('che_헌납', $general, $this->env, [
                'isGold' => true,
                'amount' => GameConst::$maxResourceActionAmount
            ]);
        } else {
            return buildGeneralCommandClass('che_헌납', $general, $this->env, [
                'isGold' => false,
                'amount' => GameConst::$maxResourceActionAmount
            ]);
        }
    }

    protected function do중립(): GeneralCommand
    {
        $general = $this->general;
        if ($general->getNationID() == 0) {
            $cmd = buildGeneralCommandClass('che_인재탐색', $general, $this->env);
            if (!$cmd->hasFullConditionMet() || $this->rng->nextBool(0.8)) {
                $cmd = buildGeneralCommandClass('che_견문', $general, $this->env);
            }
            return $cmd;
        }


        $candidate = ['che_물자조달', 'che_인재탐색'];
        $nation = $this->nation;
        if ($nation['gold'] < $this->nationPolicy->reqNationGold) {
            $candidate = ['che_물자조달'];
        }
        if ($nation['rice'] < $this->nationPolicy->reqNationRice) {
            $candidate = ['che_물자조달'];
        }


        $cmd = buildGeneralCommandClass($this->rng->choice($candidate), $this->general, $this->env);
        if (!$cmd->hasFullConditionMet()) {
            return buildGeneralCommandClass('che_물자조달', $this->general, $this->env);
        }
        return $cmd;
    }

    protected function categorizeNationCities(): void
    {

        if ($this->nationCities !== null) {
            return;
        }

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $db = DB::db();

        $nationCities = [];
        $frontCities = [];
        $supplyCities = [];
        $backupCities = [];

        foreach ($db->query('SELECT * FROM city WHERE nation = %i', $nationID) as $nationCity) {
            $nationCity['generals'] = new \ArrayObject();
            $cityID = $nationCity['city'];
            $dev =
                ($nationCity['agri'] + $nationCity['comm'] + $nationCity['secu'] + $nationCity['def'] + $nationCity['wall']) /
                ($nationCity['agri_max'] + $nationCity['comm_max'] + $nationCity['secu_max'] + $nationCity['def_max'] + $nationCity['wall_max']);

            $nationCity['dev'] = $dev;

            $nationCity['important'] = 1;

            if ($nationCity['supply']) {
                $supplyCities[$cityID] = $nationCity;
            }
            if ($nationCity['front']) {
                $frontCities[$cityID] = $nationCity;
            } else if($nationCity['supply']) {
                $backupCities[$cityID] = $nationCity;
            }

            $nationCities[$cityID] = $nationCity;
        }

        $this->nationCities = $nationCities;
        $this->frontCities = $frontCities;
        $this->supplyCities = $supplyCities;
        $this->backupCities = $backupCities;
    }


    protected function categorizeNationGeneral(): void
    {
        if ($this->userGenerals !== null) {
            return;
        }
        $userGenerals = [];
        $userCivilGenerals = [];
        $userWarGenerals = [];

        $lostGenerals = [];
        $npcCivilGenerals = [];
        $npcWarGenerals = [];
        $troopLeaders = [];
        $chiefGenerals = [];

        $nationID = $this->nation['nation'];

        $this->categorizeNationCities();
        $nationCities = &$this->nationCities;

        $db = DB::db();
        $generalIDList = $db->queryFirstColumn('SELECT no FROM general WHERE nation = %i AND no != %i', $nationID, $this->general->getID());

        $nationGenerals = General::createGeneralObjListFromDB($generalIDList, null, 2);

        $lastWar = \PHP_INT_MAX;
        foreach ($nationGenerals as $nationGeneral) {
            $recentWar = $nationGeneral->calcRecentWarTurn($this->env['turnterm']);
            if ($recentWar >= ($nationGeneral->getVar('belong') - 1) * 12) {
                //임관전 전투는 제외
                continue;
            }

            $lastWar = min($lastWar, $recentWar);
        }

        foreach ($nationGenerals as $nationGeneral) {
            $generalID = $nationGeneral->getID();
            $cityID = $nationGeneral->getCityID();
            $npcType = $nationGeneral->getNPCType();
            $officerLevel = $nationGeneral->getVar('officer_level');
            $officerCity = $nationGeneral->getVar('officer_city');

            if ($officerLevel > 4) {
                $chiefGenerals[$officerLevel] = $nationGeneral;
            } else if ($officerLevel >= 2) {
                $nationCities[$officerCity]['important'] += 1;
            }

            if (key_exists($cityID, $nationCities)) {
                $nationCities[$cityID]['generals'][$generalID] = $nationGeneral;
                if (!$nationCities[$cityID]['supply']) {
                    $lostGenerals[$generalID] = $nationGeneral;
                }
            } else {
                $lostGenerals[$generalID] = $nationGeneral;
            }

            if ($npcType == 5) {
                //부대장임
                $troopLeaders[$generalID] = $nationGeneral;
            } else if ($nationGeneral->getVar('troop') === $generalID && $nationGeneral->getReservedTurn(0, $this->env)->getName() === 'che_집합') {
                //비 NPC부대장임
                $troopLeaders[$generalID] = $nationGeneral;
            } else if ($nationGeneral->getVar('killturn') < 5) {
                //삭턴이 몇 안남은 장수는 '내정장 npc'로 처리
                $npcCivilGenerals[$generalID] = $nationGeneral;
            } else if ($npcType < 2) {
                $userGenerals[$generalID] = $nationGeneral;
                if ($nationGeneral->calcRecentWarTurn($this->env['turnterm']) <= $lastWar + 12) {
                    $userWarGenerals[$generalID] = $nationGeneral;
                } else if (
                    $this->dipState !== self::d평화 &&
                    $nationGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew
                ) {
                    $userWarGenerals[$generalID] = $nationGeneral;
                    //TODO: 훈련,사기 나중에 되돌릴 것
                    //TODO: 징,모병턴 기준으로 계산하는 코드를 추가할 것
                } else {
                    $userCivilGenerals[$generalID] = $nationGeneral;
                }
            } else if ($nationGeneral->getLeadership(false) >= $this->nationPolicy->minNPCWarLeadership) {
                $npcWarGenerals[$generalID] = $nationGeneral;
            } else {
                $npcCivilGenerals[$generalID] = $nationGeneral;
            }
        }

        $this->nationGenerals = $nationGenerals;
        $this->userGenerals = $userGenerals;
        $this->userCivilGenerals = $userCivilGenerals;
        $this->userWarGenerals = $userWarGenerals;
        $this->lostGenerals = $lostGenerals;
        $this->npcCivilGenerals = $npcCivilGenerals;
        $this->npcWarGenerals = $npcWarGenerals;
        $this->troopLeaders = $troopLeaders;
        $this->chiefGenerals = $chiefGenerals;
    }


    public function chooseNationTurn(NationCommand $reservedCommand): NationCommand
    {
        $this->updateInstance();

        //TODO: NationTurn과 InstantNationTurn 구분 필요
        $lastTurn = $reservedCommand->getLastTurn();
        $general = $this->general;
        $npcType = $general->getNPCType();

        $this->categorizeNationGeneral();
        $this->categorizeNationCities();

        $month = $this->env['month'];
        if ($npcType >= 2) {
            if (!($general->getAuxVar('use_auto_nation_turn') ?? 1)) {
                $general->setAuxVar('use_auto_nation_turn', 1);
            }
            if ($general->getVar('officer_level') == 12) {
                if (in_array($month, [3, 6, 9, 12])) {
                    $this->choosePromotion();
                }
                if ($month == 12) {
                    $this->chooseTexRate();
                    $this->chooseGoldBillRate();
                }
                if ($month == 6) {
                    $this->chooseTexRate();
                    $this->chooseRiceBillRate();
                }
            } else if (in_array($month, [3, 6, 9, 12])) {
                $this->chooseNonLordPromotion();
            }
        }

        if (!($reservedCommand instanceof Command\Nation\휴식)) {
            if ($reservedCommand->hasFullConditionMet()) {
                $reservedCommand->reason = 'reserved';
                return $reservedCommand;
            }
            $date = $general->getTurnTime($general::TURNTIME_HM);
            $failString = $reservedCommand->getFailString();
            $text = "{$failString} <1>{$date}</>";
            $general->getLogger()->pushGeneralActionLog($text);
        }

        foreach ($this->nationPolicy->priority as $actionName) {

            if (!property_exists($this->nationPolicy, 'can' . $actionName)) {
                trigger_error("can{$actionName}이 없음", E_USER_NOTICE);
                continue;
            }
            if (!$this->nationPolicy->{'can' . $actionName}) {
                continue;
            }
            if ($npcType < 2 && !($this->nationPolicy::$availableInstantTurn[$actionName] ?? false)) {
                continue;
            }
            /** @var ?NationCommand */
            $result = $this->{'do' . $actionName}($lastTurn);
            if ($result !== null) {
                $result->reason = 'do' . $actionName;
                return $result;
            }
        }
        $cmd = buildNationCommandClass(null, $this->general, $this->env, $this->general->getLastTurn());
        $cmd->reason = 'neutral';
        return $cmd;
    }

    public function chooseInstantNationTurn(NationCommand $reservedCommand): ?NationCommand
    {
        if ($reservedCommand->hasFullConditionMet()) {
            return $reservedCommand;
        }

        $this->updateInstance();

        foreach ($this->nationPolicy->priority as $actionName) {
            /** @var ?NationCommand */
            if (!key_exists($actionName, $this->nationPolicy::$availableInstantTurn)) {
                continue;
            }
            if (!$this->nationPolicy->{'can' . $actionName}) {
                continue;
            }
            $result = $this->{'do' . $actionName}($reservedCommand);
            if ($result !== null) {
                return $result;
            }
        }
        return buildNationCommandClass(null, $this->general, $this->env, $this->general->getLastTurn());
    }

    public function chooseGeneralTurn(GeneralCommand $reservedCommand): GeneralCommand
    {
        $general = $this->general;
        $npcType = $general->getNPCType();
        $nationID = $general->getNationID();

        $this->updateInstance();

        //특별 메세지 있는 경우 출력
        $term = $this->env['turnterm'];
        if ($general->getVar('npcmsg') && $this->rng->nextBool(GameConst::$npcMessageFreqByDay * $term / (60 * 24))) {
            $src = new MessageTarget(
                $general->getID(),
                $general->getVar('name'),
                $general->getVar('nation'),
                $this->nation['name'],
                $this->nation['color'],
                GetImageURL($general->getVar('imgsvr'), $general->getVar('picture'))
            );
            $msg = new Message(
                Message::MSGTYPE_PUBLIC,
                $src,
                $src,
                $general->getVar('npcmsg'),
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send();
        }


        if ($npcType >= 2 && $general->getVar('defence_train') != 80) {
            $general->setVar('defence_train', 80);
        }

        if ($general->getVar('officer_level') === 12 && $this->generalPolicy->can선양) {
            $result = $this->do선양();
            if ($result !== null) {
                $result->reason = 'do선양';
                return $result;
            }
        }

        if ($npcType == 5) {
            if ($general->getNationID() == 0) {
                $general->setVar('killturn', 1);
                $reservedCommand->reason = '사망';
                return $reservedCommand;
            }
            $result = $this->do집합();
            if (!$result) {
                throw new MustNotBeReachedException();
            }
            $result->reason = 'do집합';
            return $result;
        }

        if (!($reservedCommand instanceof Command\General\휴식)) {
            $reservedCommand->reason = 'do예약턴';
            return $reservedCommand;
        }

        if ($general->getVar('injury') > $this->nationPolicy->cureThreshold) {
            $result = buildGeneralCommandClass('che_요양', $general, $this->env);
            $result->reason = 'do요양';
            return $result;
        }

        if (($npcType == 2 || $npcType == 3) && $nationID == 0) {
            $result = $this->do거병();
            if ($result !== null) {
                $result->reason = 'do거병';
                return $result;
            }
        }

        if ($nationID === 0 && $this->generalPolicy->can국가선택) {
            $result = $this->do국가선택();
            if ($result !== null) {
                $result->reason = 'do국가선택';
                return $result;
            }
            $result = $this->do중립();
            $result->reason = 'do중립';
            return $result;
        }

        if ($npcType < 2 && $nationID === 0 && !$this->generalPolicy->can국가선택) {
            $reservedCommand->reason = '재야유저';
            return $reservedCommand;
        }

        if ($npcType >= 2 && $general->getVar('officer_level') == 12 && !$this->nation['capital']) {
            //방랑군 건국
            $result = $this->do건국();
            if ($result !== null) {
                $result->reason = 'do건국';
                return $result;
            }
            $result = $this->do방랑군이동();
            if ($result !== null) {
                $result->reason = 'do방랑군이동';
                return $result;
            }

            $result = $this->do해산();
            if ($result !== null) {
                $result->reason = 'do해산';
                return $result;
            }
        }

        foreach ($this->generalPolicy->priority as $actionName) {
            if (!property_exists($this->generalPolicy, 'can' . $actionName)) {
                trigger_error("can{$actionName}이 없음", E_USER_NOTICE);
                continue;
            }
            if (!($this->generalPolicy->{'can' . $actionName})) {
                continue;
            }
            /** @var ?GeneralCommand */
            $result = $this->{'do' . $actionName}();
            if ($result !== null) {
                $result->reason = 'do' . $actionName;
                return $result;
            }
        }

        $result = $this->do중립();
        $result->reason = 'do중립';
        return $result;
    }

    protected function calcNationDevelopedRate()
    {
        if ($this->devRate !== null) {
            return $this->devRate;
        }

        $devRate = [
            'all' => 0,
        ];


        foreach ($this->supplyCities as $city) {
            foreach ($this->calcCityDevelRate($city) as $develKey => [$devScore, $devType]) {
                if ($develKey == 'trust') {
                    continue;
                }
                if (!key_exists($develKey, $devRate)) {
                    $devRate[$develKey] = 0;
                }
                $devRate[$develKey] += $devScore;
                $devRate['all'] += $devScore;
            }
        }
        foreach (array_keys($devRate) as $key) {
            $devRate[$key] /= count($this->supplyCities);
        }
        $devRate['all'] /= count($devRate) - 1;
        $this->devRate = $devRate;
        return $this->devRate;
    }

    protected function chooseNonLordPromotion()
    {
        //빈자리는 아무나 채움
        $db = DB::db();

        $setChiefLevel = 0;
        $minChiefLevel = getNationChiefLevel($this->nation['level']);
        $chiefSet = $this->nation['chief_set'];
        foreach (Util::range($minChiefLevel, 12) as $chiefLevel) {
            if (isOfficerSet($chiefSet, $chiefLevel)) {
                continue;
            }
            if (key_exists($chiefLevel, $this->chiefGenerals)) {
                continue;
            }
            if ($this->general->getVar('officer_level') == $chiefLevel) {
                continue;
            }


            $picked = false;

            /** @var General|null */
            $randGeneral = null;
            foreach (Util::range(5) as $idx) {

                /** @var General */
                if ($this->npcWarGenerals) {
                    /** @var General */
                    $randGeneral = $this->rng->choice($this->npcWarGenerals);
                } else if ($this->npcCivilGenerals) {
                    /** @var General */
                    $randGeneral = $this->rng->choice($this->npcCivilGenerals);
                } else if ($this->userWarGenerals) {
                    /** @var General */
                    $randGeneral = $this->rng->choice($this->userWarGenerals);
                } else if ($this->userCivilGenerals) {
                    /** @var General */
                    $randGeneral = $this->rng->choice($this->userCivilGenerals);
                } else {
                    break;
                }

                if ($randGeneral->getVar('officer_level') != 1) {
                    continue;
                }

                if ($chiefLevel == 11) {
                    $picked = true;
                    break;
                }

                if ($chiefLevel % 2 == 0) {
                    if ($randGeneral->getStrength(false, false, false, false) < GameConst::$chiefStatMin) {
                        continue;
                    }
                } else {
                    if ($randGeneral->getIntel(false, false, false, false) < GameConst::$chiefStatMin) {
                        continue;
                    }
                }
                $picked = true;
                break;
            }

            if (!$picked || $randGeneral === null) {
                continue;
            }

            $randGeneral->setVar('officer_level', $chiefLevel);
            $randGeneral->setVar('officer_city', 0);
            $randGeneral->applyDB($db);
            $this->nation['chief_set'] |= doOfficerSet(0, $chiefLevel);
            $setChiefLevel |= doOfficerSet(0, $chiefLevel);
            $this->chiefGenerals[$chiefLevel] = $randGeneral;
        }

        if ($setChiefLevel) {
            $db->update('nation', [
                'chief_set' => $db->sqleval('chief_set | %i', $setChiefLevel)
            ], 'nation=%i', $this->nation['nation']);
        }
    }

    protected function calcCityDevelRate(array $city)
    {
        return [
            'trust' => [$city['trust'] / 100, self::t통솔장],
            'pop' => [$city['pop'] / $city['pop_max'], self::t통솔장],
            'agri' => [$city['agri'] / $city['agri_max'], self::t지장],
            'comm' => [$city['comm'] / $city['comm_max'], self::t지장],
            'secu' => [$city['secu'] / $city['secu_max'], self::t무장],
            'def' => [$city['def'] / $city['def_max'], self::t무장],
            'wall' => [$city['wall'] / $city['wall_max'], self::t무장],
        ];
    }

    protected function choosePromotion()
    {
        $db = DB::db();

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $minChiefLevel = getNationChiefLevel($nation['level']);

        $userChiefCnt = 0;

        $minUserKillturn = $this->env['killturn'] - Util::toInt(240 / $this->env['turnterm']);
        $minNPCKillturn = 36;

        foreach (Util::range($minChiefLevel, 12) as $chiefLevel) {
            if (!key_exists($chiefLevel, $this->chiefGenerals)) {
                continue;
            }
            $chief = $this->chiefGenerals[$chiefLevel];
            if ($chief->getNPCType() < 2 && $chief->getVar('killturn') >= $minUserKillturn) {
                $userChiefCnt += 1;
                $chief->setVar('permission', 'ambassador');
            }
        }



        $minBelong = min($this->general->getVar('belong') - 1, 3);
        $updatedChiefSet = 0;

        /** @var General[] */
        $nextChiefs = [];

        if ($userChiefCnt == 0 && $this->userGenerals && !isOfficerSet($nation['chief_set'], 11)) {
            $userGenerals = $this->userGenerals;
            uasort($userGenerals, function (General $lhs, General $rhs) {
                return - ($lhs->getVar('leadership') <=> $rhs->getVar('leadership'));
            });
            foreach ($userGenerals as $general) {
                if ($general->getVar('killturn') < $minUserKillturn) {
                    continue;
                }
                if ($general->getVar('belong') < $minBelong) {
                    continue;
                }
                if ($general->getVar('officer_level') !== 1) {
                    continue;
                }
                $nextChiefs[11] = $general;
                $general->setVar('officer_level', 11);
                $general->setVar('officer_city', 0);
                $general->setVar('permission', 'ambassador');
                $nation['chief_set'] |= doOfficerSet(0, 11);
                $updatedChiefSet |= doOfficerSet(0, 11);
                $userChiefCnt += 1;
                break;
            }
        }

        $generals = $this->nationGenerals;
        uasort($generals, function (General $lhs, General $rhs) {
            $lhsStat = $lhs->getLeadership(false, false, false, false) * 2
                + $lhs->getStrength(false, false, false, false)
                + $lhs->getIntel(false, false, false, false);
            $rhsStat = $rhs->getLeadership(false, false, false, false) * 2
                + $rhs->getStrength(false, false, false, false)
                + $rhs->getIntel(false, false, false, false);
            return - ($lhsStat <=> $rhsStat);
        });



        foreach (Util::range(11, $minChiefLevel - 1, -1) as $chiefLevel) {
            if (isOfficerSet($nation['chief_set'], $chiefLevel)) {
                continue;
            }
            if ($this->general->getVar('officer_level') === $chiefLevel) {
                continue;
            }

            if (key_exists($chiefLevel, $this->chiefGenerals)) {
                $oldChief = $this->chiefGenerals[$chiefLevel];
                if ($oldChief->getNPCType() < 2 && $oldChief->getVar('killturn') >= $minChiefLevel) {
                    continue;
                }
            }

            if (!key_exists($chiefLevel, $this->chiefGenerals) && !key_exists($chiefLevel, $nextChiefs)) {
                $newChiefProb = 1;
            } else {
                $newChiefProb = $this->rng->nextBool(0.1)?1:0;
            }

            if ($newChiefProb < 1 && !$this->rng->nextBool($newChiefProb)) {
                continue;
            }


            $newChief = null;
            foreach ($generals as $general) {
                if ($general->getVar('officer_level') !== 1) {
                    continue;
                }
                if ($general->getNPCType() < 2 && $general->getVar('killturn') < $minUserKillturn) {
                    continue;
                }
                if ($general->getNPCType() >= 2 && $general->getVar('killturn') < $minNPCKillturn) {
                    continue;
                }

                if ($chiefLevel == 11) {
                } else if ($chiefLevel % 2 == 0 && $general->getStrength(false, false, false, false) < GameConst::$chiefStatMin) {
                    continue;
                } else if ($chiefLevel % 2 == 1 && $general->getIntel(false, false, false, false) < GameConst::$chiefStatMin) {
                    continue;
                }

                if ($general->getNPCType() < 2 && $userChiefCnt >= 3) {
                    continue;
                }

                $newChief = $general;
                break;
            }

            if (!$newChief) {
                continue;
            }

            if ($newChief->getNPCType() < 2) {
                $userChiefCnt += 1;
                $newChief->setVar('permission', 'ambassador');
            }

            $nextChiefs[$chiefLevel] = $newChief;
            $newChief->setVar('officer_level', $chiefLevel);
            $newChief->setVar('officer_city', 0);
            $nation['chief_set'] |= doOfficerSet(0, $chiefLevel);
            $updatedChiefSet |= doOfficerSet(0, $chiefLevel);
        }

        foreach ($nextChiefs as $chiefLevel => $chief) {
            $oldChief = $this->chiefGenerals[$chiefLevel] ?? null;
            if ($oldChief) {
                $oldChief->setVar('officer_level', 1);
                $oldChief->setVar('officer_city', 0);
                $oldChief->applyDB($db);
            }
            $chief->applyDB($db);
            $this->chiefGenerals[$chiefLevel] = $chief;
        }
        if ($updatedChiefSet) {
            $db->update('nation', [
                'chief_set' => $db->sqleval('chief_set | %i', $updatedChiefSet)
            ], 'nation=%i', $nationID);
        }
    }

    protected function chooseTexRate(): int
    {
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        $rate = 15;
        //도시
        if ($this->supplyCities) {
            $devRate = $this->calcNationDevelopedRate();

            $avg = ($devRate['pop'] + $devRate['all']) / 2;

            if ($avg > 0.95) $rate = 25;
            elseif ($avg > 0.70) $rate = 20;
            elseif ($avg > 0.50) $rate = 15;
            else $rate = 10;
        }
        $this->nation['rate'] = $rate;

        $db->update('nation', [
            'war' => 0,
            'rate' => $rate
        ], 'nation=%i', $nationID);
        return $rate;
    }

    protected function chooseGoldBillRate(): int
    {
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        $cityList = $this->supplyCities;

        if (!$cityList) {
            return 20;
        }

        $nationGenerals = $this->nationGenerals;
        $nationGenerals[] = $this->general;

        $dedicationList = array_map(function (General $general) {
            return $general->getRaw();
        }, array_filter($this->nationGenerals, function (General $rawGeneral) {
            return $rawGeneral->getVar('npc') != 5;
        }));


        $goldIncome  = getGoldIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $warIncome  = getWarGoldIncome($nation['type'], $cityList);
        $income = $goldIncome + $warIncome;

        $outcome = Util::valueFit(getOutcome(100, $dedicationList), 1);

        $bill = intval($income / $outcome * 90); // 수입의 90% 만 지급
        if ($nation['gold'] + $income - $outcome > $this->nationPolicy->reqNationGold * 2) {
            $moreBill = ($nation['gold'] + $income - $this->nationPolicy->reqNationGold * 2) / $outcome * 80;
            if ($moreBill > $bill) {
                $bill = intval(($moreBill + $bill) / 2);
            }
        }

        $bill = Util::valueFit($bill, 20, 200);

        $db->update('nation', [
            'bill' => $bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }

    protected function chooseRiceBillRate(): int
    {
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        $cityList = $this->supplyCities;

        if (!$cityList) {
            return 20;
        }

        $nationGenerals = $this->nationGenerals;
        $nationGenerals[] = $this->general;

        $dedicationList = array_map(function (General $general) {
            return $general->getRaw();
        }, array_filter($this->nationGenerals, function (General $rawGeneral) {
            return $rawGeneral->getVar('npc') != 5;
        }));

        $riceIncome = getRiceIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $wallIncome = getWallIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $income = $riceIncome + $wallIncome;

        $outcome = Util::valueFit(getOutcome(100, $dedicationList), 1);

        $bill = intval($income / $outcome * 90); // 수입의 90% 만 지급
        if ($nation['rice'] + $income - $outcome > $this->nationPolicy->reqNationRice * 2) {
            $moreBill = ($nation['rice'] + $income - $this->nationPolicy->reqNationRice * 2) / $outcome * 80;
            if ($moreBill > $bill) {
                $bill = intval(($moreBill + $bill) / 2);
            }
        }

        $bill = Util::valueFit($bill, 20, 200);

        $db->update('nation', [
            'bill' => $bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }
}
