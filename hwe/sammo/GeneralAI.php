<?php

namespace sammo;

use sammo\Command\GeneralCommand;
use sammo\Command\NationCommand;
use sammo\Scenario\NPC;

class GeneralAI
{
    /** @var General */
    protected $general;
    /** @var array */
    protected $city;
    /** @var array */
    protected $nation;
    /** @var int */
    protected $genType;

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

    //수뇌용

    public function __construct(General $general)
    {
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $this->env = $gameStor->getAll(true);
        $this->baseDevelCost = $this->env['develcost'] * 12;
        $this->general = $general;
        if ($general->getRawCity() === null) {
            $city = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $general->getCityID());
            $general->setRawCity($city);
            $this->city = $city;
        }
        else{
            $this->city = $general->getRawCity();
        }
        
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
        $nationStor->cacheValues(['npc_nation_policy','npc_general_policy','prev_income_gold','prev_income_rice']);

        $this->nationPolicy = new AutorunNationPolicy($general, $this->env['autorun_user']['options'], $nationStor->getValue('npc_nation_policy'), $gameStor->getValue('npc_nation_policy'), $this->nation, $this->env);
        $this->generalPolicy = new AutorunGeneralPolicy($general, $this->env['autorun_user']['options'], $nationStor->getValue('npc_general_policy'), $gameStor->getValue('npc_general_policy'), $this->nation, $this->env);

        $prevIncomeGold = $nationStor->prev_income_gold??1000;
        $prevIncomeRice = $nationStor->prev_income_rice??1000;
        $this->maxResourceActionAmount = Util::round(max(
            $this->nationPolicy->minimumResourceActionAmount,
            $prevIncomeGold / 10,
            $prevIncomeRice / 10,
            $this->nation['gold'] / 5,
            $this->nation['rice'] / 5,
            ($this->env['year'] - $this->env['startyear'] - 3) * 1000
        ), -2);
        if($this->maxResourceActionAmount > GameConst::$maxResourceActionAmount){
            $this->maxResourceActionAmount = GameConst::$maxResourceActionAmount;
        }

        $this->nation['aux'] = Json::decode($this->nation['aux']??'{}');

        $this->leadership = $general->getLeadership();
        $this->strength = $general->getStrength();
        $this->intel = $general->getIntel();

        $this->fullLeadership = $general->getLeadership(false);
        $this->fullStrength = $general->getStrength(false);
        $this->fullIntel = $general->getIntel(false);

        
        $this->genType = $this->calcGenType($general);

        $this->calcDiplomacyState();
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
                if (Util::randBool($intel / $strength / 2)) {
                    $genType |= self::t지장;
                }
            }
            //지장
        } else {
            $genType = self::t지장;
            if ($strength >= $intel * 0.8) {  //지무장
                if (Util::randBool($strength / $intel / 2)) {
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

        if (Util::joinYearMonth($env['year'], $env['month']) <= Util::joinYearMonth($env['startyear'] + 2, 5)) {
            $this->dipState = self::d평화;
            $this->attackable = false;
            return;
        }

        $frontStatus = $db->queryFirstField('SELECT max(front) FROM city WHERE nation=%i AND supply=1', $nationID);
        // 공격가능도시 있으면
        $this->attackable = !!$frontStatus;

        $warTarget = $db->queryAllLists(
            'SELECT you, state, term FROM diplomacy WHERE me = %i AND state IN (0, 1)',
            $nationID
        );

        $onWar = 0;
        $onWarReady = 0;
        $onWarYet = 0;
        $warTargetNation = [];
        foreach ($warTarget as [$warNationID, $warState, $warTerm]) {
            if ($warState == 0) {
                $onWar += 1;
                $warTargetNation[$warNationID] = 2;
            } else if($warState == 1 && $warTerm < 5){
                $onWarReady += 1;
                $warTargetNation[$warNationID] = 1;
            }
            else{
                $onWarYet += 1;
            }
        }

        if(!$onWar && !$onWarReady && !$onWarYet){
            $warTargetNation[0] = 1;
        }
        
        $this->warTargetNation = $warTargetNation;
        

        $minWarTerm = $db->queryFirstField('SELECT min(term) FROM diplomacy WHERE me = %i AND state=1', $nationID);
        if ($minWarTerm === null) {
            $this->dipState = self::d평화;
        } else if ($minWarTerm > 8) {
            $this->dipState = self::d선포;
        } else if ($minWarTerm > 5) {
            $this->dipState = self::d징병;
        } else {
            $this->dipState = self::d직전;
        }

        if ($this->attackable) {
            //전쟁으로 인한 attackable인가?
            if ($onWar || (!$onWarReady && !$onWarYet)) {
                $this->dipState = self::d전쟁;
            }
        }
    }

    protected function calcWarRoute(){
        if($this->warRoute !== null){
            return;
        }
        $target = $this->warTargetNation;
        $target[] = $this->nation['nation'];

        $this->warRoute = searchAllDistanceByNationList($target, false);
    }

    protected function do부대전방발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(!$this->frontCities){
            return null;
        }
        $this->calcWarRoute();
        $troopCandidate = [];
        
        $chiefTurn = cutTurn($this->general->getTurnTime(), $this->env['turnterm']);
        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);

        foreach($this->troopLeaders as $troopLeader){
            $leaderID = $troopLeader->getID();
            if(!key_exists($leaderID, $this->nationPolicy->CombatForce)){
                continue;
            }

            $currentCityID = $troopLeader->getCityID();

            if(key_exists($currentCityID, $this->frontCities)){
                continue;
            }

            $last발령 = $troopLeader->getAuxVar('last발령');
            if($last발령){
                $leaderTurn = cutTurn($troopLeader->getTurnTime(), $this->env['turnterm']);
                $compYearMonth = $yearMonth;
                if($chiefTurn < $leaderTurn){
                    $compYearMonth += 1;
                }
                if($compYearMonth === $yearMonth){
                    //한턴마다 한번씩만 발령하자.
                    continue;
                }
            }

            [$fromCityID, $toCityID] = $this->nationPolicy->CombatForce[$leaderID];

            if(!key_exists($fromCityID, $this->warRoute) && !key_exists($toCityID, $this->warRoute)){
                //공격 루트 상실, 전방 아무데나
                $troopCandidate[] = [$leaderID, Util::choiceRandom($this->frontCities)['city']];
                continue;
            }

            if(!key_exists($toCityID, $this->warRoute[$fromCityID])){
                //공격 루트 상실, 전방 아무데나
                $troopCandidate[] = [$leaderID, Util::choiceRandom($this->frontCities)['city']];
                continue;
            }

            if(key_exists($fromCityID, $this->supplyCities) && key_exists($toCityID, $this->supplyCities)){
                //점령 완료, 전방 아무데나
                $troopCandidate[] = [$leaderID, Util::choiceRandom($this->frontCities)['city']];
                continue;
            }
            
            
            //출발지가 아국땅이 아닌경우  수도->출발지
            if(!key_exists($fromCityID, $this->supplyCities)){
                $toCityID = $fromCityID;
                $fromCityID = $this->nation['capital'];
            }

            $targetCityID = $fromCityID;
            //접경에 도달할때까지 전진
            while(!key_exists($targetCityID, $this->frontCities)){
                $distance = $this->warRoute[$targetCityID][$toCityID];
                $nextCityCandidate = [];
                foreach(array_keys(CityConst::byID($targetCityID)->path) as $nearCityID){
                    if(!key_exists($nearCityID, $this->warRoute) || !key_exists($toCityID, $this->warRoute[$nearCityID])){
                        continue;
                    }
                    if($this->warRoute[$nearCityID][$toCityID] + 1 > $distance){
                        continue;
                    }
                    $nextCityCandidate[] = $nearCityID;
                }
                if(!$nextCityCandidate){
                    throw new MustNotBeReachedException('경로 계산 버그');
                }
                if(count($nextCityCandidate) == 1){
                    $targetCityID = $nextCityCandidate[0];
                    continue;
                }
                $targetCityID = Util::choiceRandom($nextCityCandidate);
            }

            $troopCandidate[] = ['destGenaralID'=>$leaderID, 'destCityID'=>$targetCityID];
        }

        if(!$troopCandidate){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, Util::choiceRandom($troopCandidate));
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do부대후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(!$this->frontCities){
            return null;
        }

        $chiefTurn = cutTurn($this->general->getTurnTime(), $this->env['turnterm']);
        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);
        
        $troopCandidate = [];
        foreach($this->troopLeaders as $troopLeader){
            $leaderID = $troopLeader->getID();
            if(!in_array($leaderID, $this->nationPolicy->SupportForce)){
                continue;
            }
            $currentCityID = $troopLeader->getCityID();
            if(!key_exists($currentCityID, $this->supplyCities)){
               $troopCandidate[$leaderID] = $troopLeader;
               continue;
            }

            //충분히 징병 가능한 도시의 부대는 제자리 유지
            $city = $this->supplyCities[$currentCityID];
            if($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }

            $last발령 = $troopLeader->getAuxVar('last발령');
            if($last발령){
                $leaderTurn = cutTurn($troopLeader->getTurnTime(), $this->env['turnterm']);
                $compYearMonth = $yearMonth;
                if($chiefTurn < $leaderTurn){
                    $compYearMonth += 1;
                }
                if($compYearMonth === $yearMonth){
                    //한턴마다 한번씩만 발령하자.
                    continue;
                }
            }


            $troopCandidate[$leaderID] = $troopLeader;
        }

        if(!$troopCandidate){
            return null;
        }
        
        if(count($this->supplyCities) == 1){
            return null;
        }

        $cityCandidates = [];

        foreach($this->backupCities as $city){
            if($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            $cityCandidates[] = $city;
        }

        if(!$cityCandidates){
            foreach($this->supplyCities as $city){
                if($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio){
                    continue;
                }
                $cityCandidates[] = $city;
            }
        }

        if(!$cityCandidates){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($troopCandidate)->getID(),
            'destCityID'=>Util::choiceRandom($cityCandidates)['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do부대구출발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(!$this->frontCities){
            return null;
        }

        $troopCandidate = [];
        foreach($this->troopLeaders as $troopLeader){
            $leaderID = $troopLeader->getID();
            if(in_array($leaderID, $this->nationPolicy->SupportForce)){
                continue;
            }
            if(key_exists($leaderID, $this->nationPolicy->CombatForce)){
                continue;
            }

            $currentCityID = $troopLeader->getCityID();
            if(key_exists($currentCityID, $this->supplyCities)){
               continue;
            }

            $troopCandidate[$leaderID] = $troopLeader;
        }

        if(!$troopCandidate){
            return null;
        }
        
        $cityCandidates = [];

        foreach($this->frontCities as $city){
            $cityCandidates[] = $city;
        }

        if(!$cityCandidates){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($troopCandidate)->getID(),
            'destCityID'=>Util::choiceRandom($cityCandidates)['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }


    protected function do부대유저장후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->frontCities){
            return null;
        }
        if($this->dipState !== self::d전쟁){
            return null;
        }

        $generalCadidates = [];

        foreach($this->userWarGenerals as $userGeneral){
            $generalID = $userGeneral->getID();
            if($generalID == $this->general->getID()){
                continue;
            }
            if(!key_exists($userGeneral->getCityID(), $this->frontCities)){
                continue;
            }
            if(!key_exists($userGeneral->getCityID(), $this->nationCities)){
                continue;
            }
            $city = $this->nationCities[$userGeneral->getCityID()];
            $troopLeaderID = $userGeneral->getVar('troop');
            if(!$troopLeaderID || !key_exists($troopLeaderID, $this->troopLeaders)){
                continue;
            }
            if($troopLeaderID === $userGeneral->getID()){
                continue;
            }
            $troopLeader = $this->nationGenerals[$troopLeaderID];
            if($troopLeader->getCityID() !== $userGeneral->getCityID()){
                continue;
            }
            if(!key_exists($troopLeader->getCityID(), $this->supplyCities)){
                continue;
            }
            if($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            if($userGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew){
                continue;
            }

            $generalTurnTime = $userGeneral->getTurnTime();
            $troopTurnTime =  $troopLeader->getTurnTime();

            if($generalTurnTime < $troopTurnTime){//NOTE: 어차피 수뇌 턴이 제일 빠르다
                $generalCadidates[$generalID] = $userGeneral;
            }
        }

        if(!$generalCadidates){
            return null;
        }

        $turnList = General::getReservedTurnByGeneralList($generalCadidates, 0, $this->env);
        $generalCadidates = array_filter($generalCadidates, function(General $general)use($turnList){
            $generalID = $general->getID();
            if($turnList[$generalID] instanceof Command\General\che_징병){
                return true;
            }
            else{
                return false;
            }
        });

        if(!$generalCadidates){
            return null;
        }

        if(count($this->supplyCities) == 1){
            return null;
        }

        $cityCandidates = [];

        foreach($this->backupCities as $city){
            if($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            $cityCandidates[] = $city;
        }

        if(!$cityCandidates){
            foreach($this->supplyCities as $city){
                if($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio){
                    continue;
                }
                $cityCandidates[] = $city;
            }
        }

        if(!$cityCandidates){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($generalCadidates)->getID(),
            'destCityID'=>Util::choiceRandom($cityCandidates)['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }
    protected function do유저장후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if($this->dipState !== self::d전쟁){
            return null;
        }

        $generalCadidates = [];

        foreach($this->userWarGenerals as $userGeneral){
            $generalID = $userGeneral->getID();
            if($generalID == $this->general->getID()){
                continue;
            }
            if(!key_exists($userGeneral->getCityID(), $this->supplyCities)){
                continue;
            }
            $city = $this->supplyCities[$userGeneral->getCityID()];
            if($userGeneral->getVar('troop') !== 0){
                continue;
            }
            if($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            if($userGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew){
                continue;
            }
            $generalCadidates[$generalID] = $userGeneral;
        }

        if(!$generalCadidates){
            return null;
        }

        if(count($this->supplyCities) == 1){
            return null;
        }

        $cityCandidates = [];

        if($this->backupCities){
            $cities = $this->backupCities;
        }
        else{
            $cities = $this->supplyCities;
        }

        foreach($cities as $city){
            if($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            $cityCandidates[] = $city;
        }

        if(!$cityCandidates){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($generalCadidates)->getID(),
            'destCityID'=>Util::choiceRandom($cityCandidates)['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do유저장구출발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }

        //고립 도시 장수 발령
        $args = [];
        foreach ($this->lostGenerals as $lostGeneral) {
            if ($lostGeneral->getNPCType() >= 2) {
                continue;
            }
            if(
                $lostGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew &&
                $lostGeneral->getVar('train') >= $lostGeneral->getVar('defence_train') &&
                $lostGeneral->getVar('atmos') >= $lostGeneral->getVar('defence_train')
            ){
                //수비도 가능한데, 일부러 가 있는 것으로 보임
                continue;
            }

            $troopID = $lostGeneral->getVar('troop');
            if($troopID && key_exists($troopID, $this->troopLeaders)){
                $troopLeader = $this->troopLeaders[$troopID];
                
                if(
                    key_exists($troopLeader->getCityID(), $this->supplyCities) && 
                    $this->troopLeaders[$troopID]->getTurnTime() < $lostGeneral->getTurnTime()
                ){
                    //이미 탈출 가능한 부대를 탔다
                    continue;
                }
            }

            if (in_array($this->dipState, [self::d직전, self::d전쟁]) && count($this->frontCities) > 2) {
                $selCity = Util::choiceRandom($this->frontCities);
            } else {
                $selCity = Util::choiceRandom($this->supplyCities);
            }
            //고립된 장수가 많을 수록 발령 확률 증가
            $args[] = [
                'destGeneralID' => $lostGeneral->getID(),
                'destCityID' => $selCity['city']
            ];
        }
        if(!$args){
            return null;
        }
        
        $arg = Util::choiceRandom($args);
        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, $arg);
        
        if(!$cmd->hasFullConditionMet()){
            LogText('구출실패', [$arg, $cmd->getFailString()]);
            return null;
        }
        return $cmd;
    }

    protected function do유저장전방발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(!$this->frontCities){
            return null;
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        $generalCandidates = [];
        foreach($this->userWarGenerals as $userGeneral){
            $generalID = $userGeneral->getID();
            $cityID = $userGeneral->getCityID();
            if(!key_exists($cityID, $this->nationCities)){
                continue;
            }
            if(key_exists($cityID, $this->frontCities)){
                continue;
            }
            if($userGeneral->getVar('crew') < $this->nationPolicy->minWarCrew){
                continue;
            }
            if($userGeneral->getVar('troop')){
                continue;
            }

            $train = $userGeneral->getVar('train');
            $atmos = $userGeneral->getVar('atmos');

            if(max($train, $atmos) < $this->nationPolicy->properWarTrainAtmos){
                continue;
            }

            $generalCandidates[$generalID] = $userGeneral;
        }

        if(!$generalCandidates){
            return null;
        }

        $cityCandidates = [];
        foreach($this->frontCities as $frontCity){
            $cityCandidates[$frontCity['city']] = $frontCity['important'];
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($generalCandidates)->getID(),
            'destCityID'=>Util::choiceRandomUsingWeight($cityCandidates)
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }
    
    protected function do유저장내정발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(count($this->supplyCities) === 1){
            return null;
        }

        $avgDev = array_sum(array_column($this->supplyCities, 'dev')) / count($this->supplyCities);
        if($avgDev >= 0.99){
            return null;
        }

        $userGenerals = $this->userCivilGenerals;
        if(in_array($this->dipState, [self::d평화, self::d선포])){
            $userGenerals += $this->userWarGenerals;
        }

        $generalCandidates = [];
        foreach($userGenerals as $general){
            /** @var General $general */
            if($general->getVar('troop')){
                continue;
            }
            $cityID = $general->getCityID();
            if(!key_exists($cityID, $this->supplyCities)){
                continue;
            }
            
            $city = $this->supplyCities[$cityID];

            if($city['dev'] < 0.95){
                continue;
            }
            $generalCandidates[] = $general;
        }

        if(!$generalCandidates){
            return null;
        }

        $cityCandidiates = [];
        foreach($this->supplyCities as $city){
            $dev = min($city['dev'], 0.999);
            $score = 1 - $dev;
            $score **= 2;
            $score /= sqrt(count($city['generals']??[]) + 1);
            $cityCandidiates[$city['city']] = $score;
        }

        /** @var General */
        $destGeneral = Util::choiceRandom($generalCandidates);
        $srcCity = $this->supplyCities[$destGeneral->getCityID()];
        $destCity = $this->supplyCities[Util::choiceRandomUsingWeight($cityCandidiates)];

        if($srcCity['dev'] <= $destCity['dev']){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>$destGeneral->getID(),
            'destCityID'=>$destCity['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function doNPC후방발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(!$this->frontCities){
            return null;
        }
        if($this->dipState !== self::d전쟁){
            return null;
        }

        $generalCadidates = [];

        foreach($this->npcWarGenerals as $npcGeneral){
            $generalID = $npcGeneral->getID();
            if($generalID == $this->general->getID()){
                continue;
            }
            if(!key_exists($npcGeneral->getCityID(), $this->supplyCities)){
                continue;
            }
            $city = $this->supplyCities[$npcGeneral->getCityID()];
            if($npcGeneral->getVar('troop') !== 0){
                continue;
            }
            if($city['pop'] / $city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            if($npcGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew){
                continue;
            }
            $generalCadidates[$generalID] = $npcGeneral;
        }

        if(!$generalCadidates){
            return null;
        }

        if(count($this->supplyCities) == 1){
            return null;
        }

        $cityCandidates = [];

        if($this->backupCities){
            $cities = $this->backupCities;
        }
        else{
            $cities = $this->supplyCities;
        }

        foreach($cities as $city){
            if($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            $cityCandidates[] = $city;
        }

        if(!$cityCandidates){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($generalCadidates)->getID(),
            'destCityID'=>Util::choiceRandom($cityCandidates)['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }


    protected function doNPC구출발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        //고립 도시 장수 발령
        $args = [];
        foreach ($this->lostGenerals as $lostGeneral) {
            if ($lostGeneral->getNPCType() < 2 || $lostGeneral->getNPCType() == 5) {
                continue;
            }
            $selCity = Util::choiceRandom($this->supplyCities);
            //고립된 장수가 많을 수록 발령 확률 증가
            $args[] = [
                'destGeneralID' => $lostGeneral->getID(),
                'destCityID' => $selCity['city']
            ];
        }
        if(!$args){
            return null;
        }
        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, Util::choiceRandom($args));
        if(!$cmd->hasFullConditionMet()){
            return null;
        }
        return $cmd;
    }

    protected function doNPC전방발령(LastTurn $lastTurn): ?NationCommand
    {
        $me = $this->general;
        $cityName = CityConst::byID($me->getCityID())->name;

        if(!$this->nation['capital']){
            return null;
        }
        if(!$this->frontCities){
            return null;
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        $generalCandidates = [];
        foreach($this->npcWarGenerals as $npcGeneral){
            $generalID = $npcGeneral->getID();
            $cityID = $npcGeneral->getCityID();
            if(key_exists($cityID, $this->frontCities)){
                continue;
            }
            if(!key_exists($cityID, $this->nationCities)){
                continue;
            }
            if($npcGeneral->getVar('crew') < $this->nationPolicy->minWarCrew){
                continue;
            }
            if($npcGeneral->getVar('troop')){
                continue;
            }

            $train = $npcGeneral->getVar('train');
            $atmos = $npcGeneral->getVar('atmos');

            if(max($train, $atmos) < $this->nationPolicy->properWarTrainAtmos){
                continue;
            }

            $generalCandidates[$generalID] = $npcGeneral;
        }

        if(!$generalCandidates){
            return null;
        }

        $cityCandidates = [];
        foreach($this->frontCities as $frontCity){
            $cityCandidates[$frontCity['city']] = $frontCity['important'];
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>Util::choiceRandom($generalCandidates)->getID(),
            'destCityID'=>Util::choiceRandomUsingWeight($cityCandidates)
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function doNPC내정발령(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->nation['capital']){
            return null;
        }
        if(count($this->supplyCities) === 1){
            return null;
        }

        $avgDev = array_sum(array_column($this->supplyCities, 'dev')) / count($this->supplyCities);
        if($avgDev >= 0.99){
            return null;
        }

        $npcGenerals = $this->npcCivilGenerals;
        if(in_array($this->dipState, [self::d평화, self::d선포])){
            $npcGenerals += $this->npcWarGenerals;
        }

        $generalCandidates = [];
        foreach($npcGenerals as $general){
            /** @var General $general */
            $cityID = $general->getCityID();
            if(!key_exists($cityID, $this->supplyCities)){
                continue;
            }
            $city = $this->supplyCities[$cityID];

            if($city['dev'] < 0.95){
                continue;
            }
            $generalCandidates[] = $general;
        }

        if(!$generalCandidates){
            return null;
        }

        $cityCandidiates = [];
        foreach($this->supplyCities as $city){
            $dev = min($city['dev'], 0.999);
            $score = 1 - $dev;
            $score **= 2;
            $score /= sqrt(count($city['generals']??[]) + 1);
            $cityCandidiates[$city['city']] = $score;
        }

        /** @var General */
        $destGeneral = Util::choiceRandom($generalCandidates);
        $srcCity = $this->supplyCities[$destGeneral->getCityID()];
        $destCity = $this->supplyCities[Util::choiceRandomUsingWeight($cityCandidiates)];

        if($srcCity['dev'] <= $destCity['dev']){
            return null;
        }

        $cmd = buildNationCommandClass('che_발령', $this->general, $this->env, $lastTurn, [
            'destGeneralID'=>$destGeneral->getID(),
            'destCityID'=>$destCity['city']
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }


    protected function do유저장긴급포상(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->userWarGenerals){
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

        foreach($remainResource as $resName=>[$resVal,$reqHumanMinRes]){
            usort($userWarGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach($userWarGenerals as $idx=>$targetUserGeneral){
                if($targetUserGeneral->getVar($resName) >= $reqHumanMinRes){
                    break;
                }
                if($targetUserGeneral->getVar('killturn') <= 5){
                    continue;
                }

                $crewtype = $targetUserGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($this->nation['tech'], $targetUserGeneral->getLeadership(false)) * 100 * 3 * 1.1;
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
                if($payAmount < $this->nationPolicy->minimumResourceActionAmount){
                    continue;
                }

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [[
                        'destGeneralID' => $targetUserGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    count($userWarGenerals)-$idx
                ];
            }
        }

        if(!$candidateArgs){
            return null;
        }
        
        $cmd = buildNationCommandClass(
            'che_포상', $this->general, $this->env, $lastTurn, 
            Util::choiceRandomUsingWeightPair($candidateArgs)
        );
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do유저장포상(LastTurn $lastTurn): ?NationCommand
    {

        if(!$this->userGenerals){
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
        

        foreach($remainResource as $resName=>[$reqNationRes, $resVal,$reqHumanMinWarRes,$reqHumanMinDevelRes]){
            if($resVal < $reqNationRes){
                continue;
            }
            usort($userGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach($userGenerals as $idx=>$targetUserGeneral){
                if($targetUserGeneral->getVar($resName) >= $reqHumanMinWarRes){
                    break;
                }
                if($targetUserGeneral->getVar('killturn') <= 5){
                    continue;
                }

                if(key_exists($targetUserGeneral->getID(), $this->userWarGenerals)){
                    $isWarGeneral = true;
                }
                else{
                    $isWarGeneral = false;
                }

                if($isWarGeneral){
                    $reqHumanMinRes = $reqHumanMinWarRes;
                }
                else{
                    $reqHumanMinRes = $reqHumanMinDevelRes;
                }

                $crewtype = $targetUserGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($this->nation['tech'], $targetUserGeneral->getLeadership(false)) * 100 * 6 * 1.1;
                if ($this->env['year'] > $this->env['startyear'] + 3) {
                    $reqMoney = max($reqMoney, $reqHumanMinRes);
                }
                $enoughMoney = $reqMoney * 1.2;
    
                if ($targetUserGeneral->getVar($resName) >= $reqMoney) {
                    continue;
                }
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetUserGeneral->getVar($resName)) * $resVal);
                $payAmount = Util::valueFit($payAmount, $resVal - $reqNationRes, $enoughMoney - $targetUserGeneral->getVar($resName));

                if($payAmount < $this->nationPolicy->minimumResourceActionAmount){
                    continue;
                }

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [[
                        'destGeneralID' => $targetUserGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    count($userGenerals)-$idx
                ];
            }
        }
        
        if(!$candidateArgs){
            return null;
        }
        
        $cmd = buildNationCommandClass(
            'che_포상', $this->general, $this->env, $lastTurn, 
            Util::choiceRandomUsingWeightPair($candidateArgs)
        );
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function doNPC긴급포상(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->npcWarGenerals){
            return null;
        }

        $nation = $this->nation;
        $candidateArgs = [];
        $remainResource = [
            'gold' => [
                $this->nationPolicy->reqNationGold,
                $nation['gold'],
                $this->nationPolicy->reqNPCWarGold/2
            ],
            'rice' => [
                $this->nationPolicy->reqNationRice,
                $nation['rice'],
                $this->nationPolicy->reqNPCWarRice/2
            ]
        ];


        $npcWarGenerals = $this->npcWarGenerals;

        foreach($remainResource as $resName=>[$reqNationRes, $resVal,$reqNPCMinWarRes]){
            if($resVal < $reqNationRes){
                continue;
            }
            usort($npcWarGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach($npcWarGenerals as $idx=>$targetNPCGeneral){
                if($targetNPCGeneral->getVar($resName) >= $reqNPCMinWarRes){
                    break;
                }
                if($targetNPCGeneral->getVar('killturn') <= 5){
                    continue;
                }

                $crewtype = $targetNPCGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($this->nation['tech'], $targetNPCGeneral->getLeadership(false)) * 100 * 1.5;
                if ($this->env['year'] > $this->env['startyear'] + 5) {
                    $reqMoney = max($reqMoney, $reqNPCMinWarRes);
                }
                $enoughMoney = $reqMoney * 1.2;
    
                if ($targetNPCGeneral->getVar($resName) >= $reqMoney) {
                    continue;
                }
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetNPCGeneral->getVar($resName)) * $resVal);
                $payAmount = Util::valueFit($payAmount, $resVal - $reqNationRes*0.9, $enoughMoney - $targetNPCGeneral->getVar($resName));

                if($payAmount < $this->nationPolicy->minimumResourceActionAmount){
                    continue;
                }

                if ($resVal < $payAmount / 2) {
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [[
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    count($npcWarGenerals)-$idx
                ];
            }
        }

        if(!$candidateArgs){
            return null;
        }
        
        $cmd = buildNationCommandClass(
            'che_포상', $this->general, $this->env, $lastTurn, 
            Util::choiceRandomUsingWeightPair($candidateArgs)
        );
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function doNPC포상(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->npcWarGenerals && !$this->npcCivilGenerals){
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

        foreach($remainResource as $resName=>[$reqNationRes, $resVal,$reqNPCMinWarRes,$reqNPCMinDevelRes]){
            if($resVal < $reqNationRes){
                continue;
            }
            usort($npcWarGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach($npcWarGenerals as $idx=>$targetNPCGeneral){
                if($targetNPCGeneral->getVar($resName) >= $reqNPCMinWarRes){
                    break;
                }
                if($targetNPCGeneral->getVar('killturn') <= 5){
                    continue;
                }

                $crewtype = $targetNPCGeneral->getCrewTypeObj();
                $reqMoney = $crewtype->costWithTech($nation['tech'], $targetNPCGeneral->getLeadership(false)) * 100 * 3 * 1.1;
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

                $candidateArgs[] = [[
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => Util::valueFit($payAmount, 100, $this->maxResourceActionAmount)
                    ],
                    max(count($npcWarGenerals), count($npcCivilGenerals))-$idx
                ];
            }

            usort($npcCivilGenerals, function ($lhs, $rhs) use ($resName) {
                return $lhs->getVar($resName) <=> $rhs->getVar($resName);
            });

            foreach($npcCivilGenerals as $idx=>$targetNPCGeneral){
                if($targetNPCGeneral->getVar($resName) >= $reqNPCMinDevelRes){
                    break;
                }
                if($targetNPCGeneral->getVar('killturn') <= 5){
                    continue;
                }

                $enoughMoney = $reqNPCMinDevelRes * 1.5;

                $payAmount = $enoughMoney - $targetNPCGeneral->getVar($resName);
                if($payAmount < $this->nationPolicy->minimumResourceActionAmount){
                    continue;
                }

                $payAmount = Util::valueFit($payAmount, 100, $this->maxResourceActionAmount);

                $candidateArgs[] = [[
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $payAmount
                    ],
                    max(count($npcWarGenerals), count($npcCivilGenerals)) - $idx
                ];
            }

        }

        if(!$candidateArgs){
            return null;
        }
        
        $cmd = buildNationCommandClass(
            'che_포상', $this->general, $this->env, $lastTurn, 
            Util::choiceRandomUsingWeightPair($candidateArgs)
        );
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function doNPC몰수(LastTurn $lastTurn): ?NationCommand
    {
        if(!$this->npcWarGenerals && !$this->npcCivilGenerals){
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

        foreach($remainResource as $resName=>[$resVal,$reqNationResVal,$reqNPCMinWarRes,$reqNPCMinDevelRes]){

            usort($npcCivilGenerals, function ($lhs, $rhs) use ($resName) {
                return -($lhs->getVar($resName) <=> $rhs->getVar($resName));
            });

            foreach($npcCivilGenerals as $idx=>$targetNPCGeneral){
                if($targetNPCGeneral->getVar($resName) <= $reqNPCMinDevelRes * 1.5){
                    break;
                }

                $takeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinDevelRes * 1.2;
                $takeAmount = Util::valueFit($takeAmount, 100, $this->maxResourceActionAmount);
                if($takeAmount < $this->nationPolicy->minimumResourceActionAmount){
                    break;
                }

                $candidateArgs[] = [[
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $takeAmount
                    ],
                    $takeAmount
                ];
            }


            //전투 NPC는 국고가 충분하지 않아보일때
            $reqResValDelta = $reqNationResVal * 1.5 - $resVal;
            if($reqResValDelta < 0){
                continue;
            }

            if($resVal >= $reqNationResVal){
                $willTakeSmallAmount = true;
            }
            else{
                $willTakeSmallAmount = false;
            }


            usort($npcWarGenerals, function ($lhs, $rhs) use ($resName) {
                return -($lhs->getVar($resName) <=> $rhs->getVar($resName));
            });

            

            foreach($npcWarGenerals as $idx=>$targetNPCGeneral){
                if($willTakeSmallAmount){
                    if($targetNPCGeneral->getVar($resName) <= $reqNPCMinWarRes * 2){
                        break;
                    }
                    
                }
                else if($targetNPCGeneral->getVar($resName) <= $reqNPCMinWarRes){
                    break;
                }

                if(!$willTakeSmallAmount){
                    $takeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinWarRes;
                    $takeAmount = Util::valueFit(sqrt($takeAmount * $reqResValDelta), 0, $takeAmount);
                }
                else{
                    $maxTakeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinWarRes;
                    $minTakeAmount = $targetNPCGeneral->getVar($resName) - $reqNPCMinWarRes * 2;
                    $takeAmount = Util::valueFit(sqrt($minTakeAmount * $reqResValDelta), 0, $maxTakeAmount);
                }

                if($takeAmount < 100){
                    break;
                }

                if($takeAmount < $this->nationPolicy->minimumResourceActionAmount){
                    break;
                }

                $takeAmount = Util::valueFit($takeAmount, 100, $this->maxResourceActionAmount);
                $candidateArgs[] = [[
                        'destGeneralID' => $targetNPCGeneral->getID(),
                        'isGold' => $resName == 'gold',
                        'amount' => $takeAmount
                    ],
                    $takeAmount
                ];
            }

        }

        if(!$candidateArgs){
            return null;
        }
        
        $cmd = buildNationCommandClass(
            'che_몰수', $this->general, $this->env, $lastTurn, 
            Util::choiceRandomUsingWeightPair($candidateArgs)
        );
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }


    // 군주 행동
    protected function do선전포고(LastTurn $lastTurn): ?NationCommand
    {
        $general = $this->general;

        if($general->getVar('officer_level') < 12){
            return null;
        }

        if($this->dipState !== self::d평화){
            return null;
        }

        if($this->attackable){
            return null;
        }

        if(!$this->nation['capital']){
            return null;
        }

        if($this->frontCities){
            return null;
        }

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $currentTech = $nation['tech'];
        if(!TechLimit($this->env['startyear'], $this->env['year'], $currentTech + 1000)){
            return null;
        }

        $avgGold = 0;
        $avgRice = 0;
        $genCnt = 0;
        foreach($this->npcWarGenerals as $general){
            $avgGold += $general->getVar('gold');
            $avgRice += $general->getVar('rice');
            $genCnt += 1;
        }
        foreach($this->npcCivilGenerals as $general){
            $avgGold += $general->getVar('gold');
            $avgRice += $general->getVar('rice');
            $genCnt += 1;
        }
        foreach($this->userWarGenerals as $general){
            $avgGold += $general->getVar('gold') / 2;
            $avgRice += $general->getVar('rice') / 2;
            $genCnt += 1;
        }
        foreach($this->userCivilGenerals as $general){
            $avgGold += $general->getVar('gold') / 2;
            $avgRice += $general->getVar('rice') / 2;
            $genCnt += 1;
        }
        $avgGold += $nation['gold'];
        $avgRice += $nation['rice'];

        if($genCnt == 0){
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
        $trialProp = $trialProp**6;

        if(!Util::randBool($trialProp)){
            return null;
        }

        $nations = [];
        foreach(getAllNationStaticInfo() as $destNation){
            if($destNation['level'] == 0){
                continue;
            }
            $destNationID = $destNation['nation'];
            $destNationPower = $destNation['power'];
            if (!isNeighbor($nationID, $destNationID)) {
                continue;
            }
            $nations[$destNationID] = 1 / sqrt($destNationPower + 1);
        }
        if (!$nations) {
            return null;
        }

        $cmd = buildNationCommandClass('che_선전포고', $this->general, $this->env, $lastTurn, [
            'destNationID' => Util::choiceRandomUsingWeight($nations)
        ]);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do천도(LastTurn $lastTurn): ?NationCommand
    {
        $general = $this->general;

        $db = DB::db();

        $nationStor = KVStorage::getStorage($db, $general->getNationID(), 'nation_env');
        $turnTerm = $this->env['turnterm'];

        //천도를 한턴 넣었다면 계속 넣는다.
        if($lastTurn->getCommand() === '천도' && $lastTurn->getArg()['destCityID'] != $this->nation['capital']){
            $cmd = buildNationCommandClass('che_천도', $this->general, $this->env, $lastTurn, $lastTurn->getArg());
            if($cmd->hasFullConditionMet()){
                $nationStor->last천도Trial = [$general->getVar('officer_level'), $general->getTurnTime()];
                return $cmd;
            }
        }

        $lastTrial = $nationStor->last천도Trial;
        if($lastTrial){
            [$lastTrialLevel, $lastTrialTurnTime] = $lastTrial;
            $timeDiffSeconds = TimeUtil::DateIntervalToSeconds(
                date_create_immutable($lastTrialTurnTime)->diff(
                    date_create_immutable($general->getTurnTime())
                )
            );
            if($timeDiffSeconds < $turnTerm * 30 && $lastTrialLevel !== $general->getVar('officer_level')){ //0.5Turn
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
        foreach($this->nationCities as $city){
            $nationCityIDList[$city['city']] = true;
        }

        //애초에 도시랄 것이 없음
        if(count($nationCityIDList) <= 1){
            return null;
        }

        $queue = new \SplQueue();
        $capital = $this->nation['capital'];
        $cityList = [
            $capital=>0
        ];

        $queue->enqueue($capital);

        //수도와 연결된 도시 탐색
        while(!$queue->isEmpty()){
            $cityID = $queue->dequeue();
            
            foreach(array_keys(CityConst::byID($cityID)->path) as $nextCityID){
                if(!key_exists($nextCityID, $nationCityIDList)){
                    continue;
                }
                if(key_exists($nextCityID, $cityList)){
                    continue;
                }
                $cityList[$nextCityID] = 0;
                $queue->enqueue($nextCityID);
            }
        }

        $cityList = array_keys($cityList);

        //수도와 연결된 도시가 없음
        if(count($cityList) == 1){
            return null;
        }

        $distanceList = searchAllDistanceByCityList($cityList);


        $maxDistance = 0;
        foreach($distanceList as $cityID=>$subDistanceList){
            $maxDistance = max($maxDistance, array_sum($subDistanceList));
        }

        $cityScoreList = [];
        foreach($cityList as $cityID){
            $city = $this->nationCities[$cityID];

            $cityScoreList[$cityID] =  $city['pop'] * $maxDistance / array_sum($distanceList[$cityID]) * sqrt($city['dev']);
        }

        arsort($cityScoreList);

        $enoughLimit = ceil(count($cityScoreList) * 0.25);
        foreach(array_keys($cityScoreList) as $idx=>$cityID){
            if($idx > $enoughLimit){
                break;
            }
            if($cityID === $capital){
                return null;
            }
        }

        $finalCityID = Util::array_first_key($cityScoreList);
        $dist = $distanceList[$capital][$finalCityID];
        $targetCityID = $finalCityID;
        if($dist > 1){
            $candidates = [];
            foreach(array_keys(CityConst::byID($capital)->path) as $stopID){
                if(!key_exists($stopID, $distanceList)){
                    continue;
                }
                if($distanceList[$stopID][$finalCityID] + 1 === $dist){
                    $candidates[] = $stopID;
                }
            }
            $targetCityID = Util::choiceRandom($candidates);
        }
        
        $cmd = buildNationCommandClass('che_천도', $this->general, $this->env, $lastTurn, [
            'destCityID'=>$targetCityID
        ]);

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        
        $nationStor->last천도Trial = [$general->getVar('officer_level'), $general->getTurnTime()];
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

        if($nation['rice'] < GameConst::$baserice){
            return null;
        }

        if ($genType & self::t통솔장) {
            if ($develRate['trust'] < 0.98) {
                $cmd = buildGeneralCommandClass('che_주민선정', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['trust']/2-0.2, 0.001) * 2];
                }
            }
            if ($develRate['pop'] < 0.8) {
                $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'], 0.001)];
                }
            }
            else if ($develRate['pop'] < 0.99) {
                $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'] / 4, 0.001)];
                }
            }
        }

        if($genType & self::t무장){
            if($develRate['def'] < 1){
                $cmd = buildGeneralCommandClass('che_수비강화', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['def'], 0.001)];
                }
            }
            if($develRate['wall'] < 1){
                $cmd = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['wall'], 0.001)];
                }
            }
            if($develRate['secu'] < 0.9){
                $cmd = buildGeneralCommandClass('che_치안강화', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['secu'] / 0.8, 0.001, 1)];
                }
            }
            else if($develRate['secu'] < 1){
                $cmd = buildGeneralCommandClass('che_치안강화', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / 2 / Util::valueFit($develRate['secu'], 0.001)];
                }
            }
        }

        if($genType & self::t지장){
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
                    $cmdList[] = [$cmd, ($isSpringSummer?1.2:0.8) * $intel / Util::valueFit($develRate['agri'], 0.001, 1)];
                }
            }
            if ($develRate['comm'] < 1) {
                $cmd = buildGeneralCommandClass('che_상업투자', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    $cmdList[] = [$cmd, ($isSpringSummer?0.8:1.2) * $intel / Util::valueFit($develRate['comm'], 0.001, 1)];
                }
            }
        }

        if(!$cmdList){
            return null;
        }

        return Util::choiceRandomUsingWeightPair($cmdList);
    }

    protected function do긴급내정(): ?GeneralCommand
    {
        if($this->dipState === self::d평화){
            return null;
        }

        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $general = $this->general;
        $env = $this->env;
        $genType = $this->genType;

        $city = $this->city;

        if($city['trust'] < 70 && Util::randBool($leadership / GameConst::$chiefStatMin)){
            $cmd = buildGeneralCommandClass('che_주민선정', $general, $env);
            if($cmd->hasFullConditionMet()){
                return $cmd;
            }
        }

        if($city['pop'] < $this->nationPolicy->minNPCRecruitCityPopulation && Util::randBool($leadership / GameConst::$chiefStatMin / 2)){
            $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
            if($cmd->hasFullConditionMet()){
                return $cmd;
            }
        }

        return null;
    }

    protected function do전쟁내정(): ?GeneralCommand
    {

        if($this->dipState === self::d평화){
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

        if($nation['rice'] < GameConst::$baserice){
            return null;
        }

        $develRate = Util::squeezeFromArray($this->calcCityDevelRate($city), 0);
        $isSpringSummer = $this->env['month'] <= 6;
        $cmdList = [];

        if(Util::randBool(0.3)){
            return null;
        }

        if ($genType & self::t통솔장) {
            if ($develRate['trust'] < 0.98) {
                $cmd = buildGeneralCommandClass('che_주민선정', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['trust']/2-0.2, 0.001) * 2];
                }
            }
            if ($develRate['pop'] < 0.8) {
                $cmd = buildGeneralCommandClass('che_정착장려', $general, $env);
                if($cmd->hasFullConditionMet()){
                    if (in_array($city['front'], [1, 3])) {
                        $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'], 0.001)];
                    }
                    else{
                        $cmdList[] = [$cmd, $leadership / Util::valueFit($develRate['pop'], 0.001) / 2];
                    }
                }
            }
        }

        if($genType & self::t무장){
            if($develRate['def'] < 0.5){
                $cmd = buildGeneralCommandClass('che_수비강화', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['def'], 0.001) / 2];
                }
            }
            if($develRate['wall'] < 0.5){
                $cmd = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['wall'], 0.001) / 2];
                }
            }
            if($develRate['secu'] < 0.5){
                $cmd = buildGeneralCommandClass('che_치안강화', $general, $env);
                if($cmd->hasFullConditionMet()){
                    $cmdList[] = [$cmd, $strength / Util::valueFit($develRate['secu'] / 0.8, 0.001, 1) / 4];
                }
            }
        }

        if($genType & self::t지장){
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
                        $cmdList[] = [$cmd, ($isSpringSummer?1.2:0.8) * $intel / 4 / Util::valueFit($develRate['agri'], 0.001, 1)];
                    }
                    else{
                        $cmdList[] = [$cmd, ($isSpringSummer?1.2:0.8) * $intel / 2 / Util::valueFit($develRate['agri'], 0.001, 1)];
                    }
                }
            }
            if ($develRate['comm'] < 0.5) {
                $cmd = buildGeneralCommandClass('che_상업투자', $general, $env);
                if ($cmd->hasFullConditionMet()) {
                    if (in_array($city['front'], [1, 3])) {
                        $cmdList[] = [$cmd, ($isSpringSummer?0.8:1.2) * $intel / 4 / Util::valueFit($develRate['comm'], 0.001, 1)];
                    }
                    else{
                        $cmdList[] = [$cmd, ($isSpringSummer?0.8:1.2) * $intel / 2 / Util::valueFit($develRate['comm'], 0.001, 1)];
                    }
                }
            }
        }

        if(!$cmdList){
            return null;
        }

        $cmd = Util::choiceRandomUsingWeightPair($cmdList);
        return $cmd;
    }


    protected function do금쌀구매(): ?GeneralCommand
    {
        $general = $this->general;

        if($this->city['trade'] === null && !$this->generalPolicy->can상인무시){
            return null;
        }

        $kill = $general->getRankVar('killcrew') + 50000;
        $death = $general->getRankVar('deathcrew') + 50000;
        $deathRate = $death/$kill;

        $absGold = $general->getVar('gold');
        $absRice = $general->getVar('rice');
        
        $relGold = $absGold;
        $relRice = $absRice * $deathRate;

        if($absGold + $absRice < $this->baseDevelCost * 2){
            return null;
        }

        $crewType = $general->getCrewTypeObj();
        if($this->generalPolicy->can모병){
            $costCmd = buildGeneralCommandClass('che_모병', $general, $this->env, [
                'crewType'=>$crewType->id,
                'amount'=>$this->fullLeadership*100
            ]);
        }
        else{
            $costCmd = buildGeneralCommandClass('che_징병', $general, $this->env, [
                'crewType'=>$crewType->id,
                'amount'=>$this->fullLeadership*100
            ]);
        }

        $goldCost = $costCmd->getCost()[0];
        $riceCost = $crewType->riceWithTech(
            $this->nation['tech'],
            $this->fullLeadership*100
        );

        if(($relGold + $relRice) * 1.5 <= $goldCost + $riceCost){
            return null;
        }

        $tryBuying = false;
        if ($this->generalPolicy->can상인무시){
            if($relRice * 1.5 < $relGold && $relRice < $riceCost * 2)
            {
                $tryBuying = true;
            }
            else if($relRice * 2 < $relGold)
            {
                $tryBuying = true;
            }
            
        }
        else{
            if($relRice * 2 < $relGold && $relRice < $riceCost * 3)
            {
                $tryBuying = true;
            }
        }

        if ($tryBuying) {
            //1:1
            $amount = Util::valueFit(Util::toInt(($relGold - $relRice) / (1 + $deathRate)), 100, GameConst::$maxResourceActionAmount);
            if($amount >= $this->nationPolicy->minimumResourceActionAmount){
                $cmd = buildGeneralCommandClass('che_군량매매', $general, $this->env,
                    [
                        'buyRice' => true,
                        'amount' => $amount
                    ]
                );
                if($cmd->hasFullConditionMet()){
                    return $cmd;
                }
            }
        }

        $trySelling = false;
        if ($this->generalPolicy->can상인무시){
            if($relGold * 1.5 < $relRice && $relGold < $goldCost * 2)
            {
                $trySelling = true;
            }
            else if($relGold * 2 < $relRice)
            {
                $trySelling = true;
            }
            
        }
        else{
            if($relGold * 2 < $relRice && $relGold < $goldCost * 3)
            {
                $trySelling = true;
            }
        }

        if ($trySelling) {
            $amount = Util::valueFit(Util::toInt(($relRice - $relGold) / (1 + $deathRate)), 100, GameConst::$maxResourceActionAmount);
            if($amount >= $this->nationPolicy->minimumResourceActionAmount){
                $cmd = buildGeneralCommandClass('che_군량매매', $general, $this->env,
                    [
                        'buyRice' => false,
                        'amount' => $amount
                    ]
                );
                if($cmd->hasFullConditionMet()){
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

        if(!($this->genType & self::t통솔장)){
            return null;
        }

        

        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        if($general->getVar('crew') >= $this->nationPolicy->minWarCrew){
            return null;
        }

        if(!$this->generalPolicy->can한계징병){
            $remainPop = $city['pop'] - $this->nationPolicy->minNPCRecruitCityPopulation - $this->fullLeadership * 100;
            if($remainPop <= 0){
                return null;
            }

            $maxPop = $city['pop_max'] - $this->nationPolicy->minNPCRecruitCityPopulation;
            if(($city['pop'] / $city['pop_max'] < $this->nationPolicy->safeRecruitCityPopulationRatio) &&
            (Util::randF($remainPop/$maxPop))){
                return null;
            }
            
        }

        $nationID = $general->getNationID();

        $genType = $this->genType;

        $tech = $nation['tech'];

        $db = DB::db();

        $armType =$general->getAuxVar('armType');
        if($armType){
            if(!($genType & self::t지장) && $armType == GameUnitConst::T_WIZARD){
                $armType = null;
            }
            else if(!($genType & self::t무장) && in_array($armType, [GameUnitConst::T_FOOTMAN, GameUnitConst::T_ARCHER, GameUnitConst::T_CAVALRY])){
                $armType = null;
            }
        }

        if(!$armType){
            $dex = [
                GameUnitConst::T_FOOTMAN => sqrt($general->getVar('dex1') + 500),
                GameUnitConst::T_ARCHER => sqrt($general->getVar('dex2') + 500),
                GameUnitConst::T_CAVALRY => sqrt($general->getVar('dex3') + 500),
                GameUnitConst::T_WIZARD => sqrt($general->getVar('dex4') + 500),
                GameUnitConst::T_SIEGE => sqrt($general->getVar('dex5') + 500),
            ];
            
            $availableArmType = [];
            if($this->fullStrength > $this->fullIntel * 0.9){
                $availableArmType[GameUnitConst::T_FOOTMAN] = $dex[GameUnitConst::T_FOOTMAN] * $this->fullStrength;
                $availableArmType[GameUnitConst::T_ARCHER] = $dex[GameUnitConst::T_ARCHER] * $this->fullStrength;
                $availableArmType[GameUnitConst::T_CAVALRY] = $dex[GameUnitConst::T_CAVALRY] * $this->fullStrength;
            }
            if($this->fullIntel > $this->fullStrength * 0.9){
                $availableArmType[GameUnitConst::T_WIZARD] = $dex[GameUnitConst::T_WIZARD] * $this->fullIntel * 3;
            }

            $armType = Util::choiceRandomUsingWeight($availableArmType);
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
            $type = Util::choiceRandomUsingWeight($types);
        } else {
            throw new MustNotBeReachedException('에러:'.var_dump([$general->getName(), $general->getAuxVar('armType'), $armType, $cities, $regions, $relYear, $tech], true));
        }

        if($this->generalPolicy->can고급병종){
            $currCrewType = $general->getCrewTypeObj();
            if ($currCrewType->isValid($cities, $regions, $relYear, $tech)){
                if($currCrewType->reqTech >= 2000) {
                    $type = $currCrewType->id;
                }
                else if($currCrewType->armType != $armType && $currCrewType->reqTech >= 1000){
                    //굳이 뽑은 이유가 있겠지
                    $type = $currCrewType->id;
                }
            } 
        }

        //XXX: 훈련, 사기진작 금액을 하드코딩으로 계산중
        $gold = $general->getVar('gold');
        $gold -= $this->fullLeadership * 3;
        $rice = $general->getVar('rice');
        $rice -= $this->fullLeadership * 4 + 500;

        if($gold <= 0 || $rice <= 0){
            return null;
        }

        $crew = $this->fullLeadership * 100;
        $crewType = GameUnitConst::byID($type);

        $riceCost = $crewType->riceWithTech(
            $this->nation['tech'],
            $this->fullLeadership*100 * 
                $general->getRankVar('killcrew')/max($general->getRankVar('deathcrew'),1)*1.2
        );

        $cmd = buildGeneralCommandClass('che_징병', $general, $env, [
            'crewType' => $type,
            'amount' => $crew
        ]);

        $cost = $cmd->getCost()[0];

        if($this->generalPolicy->can모병 && $gold >= $cost * 6){
            $cmd = buildGeneralCommandClass('che_모병', $general, $env, [
                'crewType' => $type,
                'amount' => $crew
            ]);
        }
        else if($gold < $cost && $gold * 2 >= $cost){
            $crew *= 0.5; 
            $riceCost *= 0.5;
            $crew = Util::round($crew-49, -2);
            $cmd = buildGeneralCommandClass('che_징병', $general, $env, [
                'crewType' => $type,
                'amount' => $crew
            ]);
        }

        if(!$this->generalPolicy->can한계징병 && $rice * 1.1 <= $riceCost){
            //이 쌀도 없어?
            return null;
        }
        
        if(!$cmd->hasFullConditionMet()){
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

        if($train < $this->nationPolicy->properWarTrainAtmos){
            $cmd = buildGeneralCommandClass('che_훈련', $general, $this->env);
            if($cmd->hasFullConditionMet()){
                $cmdList[] = [$cmd, GameConst::$maxTrainByCommand / Util::valueFit($train, 1)];
            }
        }

        if($atmos < $this->nationPolicy->properWarTrainAtmos){
            $cmd = buildGeneralCommandClass('che_사기진작', $general, $this->env);
            if($cmd->hasFullConditionMet()){
                $cmdList[] = [$cmd, GameConst::$maxAtmosByCommand / Util::valueFit($atmos, 1)];
            }
        }

        if(!$cmdList){
            return null;
        }
        return Util::choiceRandomUsingWeightPair($cmdList);
    }

    public function do소집해제(): ?GeneralCommand
    {
        if ($this->attackable){
            return null;
        }
        if ($this->dipState !== self::d평화) {
            return null;
        }
        if ($this->general->getVar('crew') == 0){
            return null;
        }
        if (Util::randBool(0.75)) {
            return null;
        }
        $cmd = buildGeneralCommandClass('che_소집해제', $this->general, $this->env);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }
        return $cmd;
    }


    protected function do출병(): ?GeneralCommand
    {
        if (!$this->attackable) {
            return null;
        }

        if($this->dipState !== self::d전쟁){
            return null;
        }

        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;

        if($nation['rice'] < GameConst::$baserice){
            return null;
        }

        $cityID = $city['city'];
        $nationID = $nation['nation'];

        $db = DB::db();

        if($general->getVar('train') < $this->nationPolicy->properWarTrainAtmos){
            return null;
        }
        if($general->getVar('atmos') < $this->nationPolicy->properWarTrainAtmos){
            return null;
        }
        if($general->getVar('crew') < $this->nationPolicy->minWarCrew){
            return null;
        }

        if($city['front'] === 0){
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
        if(!$attackableNations){
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

        $cmd = buildGeneralCommandClass('che_출병', $general, $this->env, ['destCityID' => Util::choiceRandom($attackableCities)]);
        if(!$cmd->hasFullConditionMet()){
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

        foreach($resourceMap as [$resKey, $reqNation, $reqNPCWar, $reqNPCDevel]){
            $genRes = $general->getVar($resKey);
            
            if($this->genType & self::t통솔장){
                $reqRes = $reqNPCWar;
            }
            else{
                $reqRes = $reqNPCDevel;
                if($genRes >= $reqNPCWar && $reqNPCWar > $reqNPCDevel + 1000){
                    $amount = $genRes - $reqNPCDevel;
                    $args[] = [[
                        'isGold'=>$resKey==='gold',
                        'amount'=>$amount
                    ], $amount];
                    continue;
                }

                if($genRes >= $reqNPCDevel * 5 && $genRes >= 5000){
                    $amount = $genRes - $reqNPCDevel;
                    $args[] = [[
                        'isGold'=>$resKey==='gold',
                        'amount'=>$amount
                    ], $amount];
                    continue;
                }
            }

            if($this->nation[$resKey] >= $reqNation){
                continue;
            }
            if($resKey === 'rice' && $this->nation[$resKey] <= GameConst::$minNationalRice / 2 && $genRes >= GameConst::$minNationalRice / 2){
                if($genRes < GameConst::$minNationalRice){
                    $args[] = [[
                        'isGold'=>'rice',
                        'amount'=>$genRes
                    ], $genRes];
                }
                else{
                    $args[] = [[
                        'isGold'=>'rice',
                        'amount'=>$genRes/2
                    ], $genRes/2];
                }
            }
            if($genRes < $reqRes * 1.5){
                continue;
            }
            if(!Util::randBool(($genRes / $reqRes)-0.5)){
                continue;
            }
            $amount = $genRes - $reqRes;
            if($amount < $this->nationPolicy->minimumResourceActionAmount){
                continue;
            }
            $args[] = [[
                'isGold'=>$resKey==='gold',
                'amount'=>$amount
            ], $amount];
        }

        if(!$args){
            return null;
        }

        $cmd = buildGeneralCommandClass('che_헌납', $general, $this->env, Util::choiceRandomUsingWeightPair($args));
        if(!$cmd->hasFullConditionMet()){
            return null;
        }
        return $cmd;
    }


    protected function do후방워프(): ?GeneralCommand
    {
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        if(!$this->generalPolicy->can징병){
            return null;
        }

        if(!($this->genType & self::t통솔장)){
            return null;
        }

        if($this->general->getVar('crew') >= $this->nationPolicy->minWarCrew){
            return null;
        }

        $city = $this->city;
        if($this->generalPolicy->can한계징병){
            if($city['pop'] >= $this->fullLeadership * 100 + GameConst::$minAvailableRecruitPop){
                return null;
            }
        }
        else{
            if($city['pop']/$city['pop_max'] >= $this->nationPolicy->safeRecruitCityPopulationRatio){
                return null;
            }
        }

        $this->categorizeNationCities();

        $recruitableCityList = [];

        foreach($this->backupCities as $candidateCity){
            $pop_ratio = $candidateCity['pop']/$candidateCity['pop_max'];
            $cityID = $candidateCity['city'];
            if($pop_ratio < $this->nationPolicy->safeRecruitCityPopulationRatio){
                continue;
            }
            $recruitableCityList[$cityID] = $pop_ratio;
        }

        if(!$recruitableCityList){
            foreach($this->supplyCities as $candidateCity){
                if($candidateCity['pop'] <= $this->fullLeadership * 100 + GameConst::$minAvailableRecruitPop){
                    continue;
                }
                $pop_ratio = $candidateCity['pop']/$candidateCity['pop_max'];
                $cityID = $candidateCity['city'];
                if(key_exists($cityID, $this->frontCities)){
                    $recruitableCityList[$cityID] = $pop_ratio / 2;
                }
                else{
                    $recruitableCityList[$cityID] = $pop_ratio;
                }
            }
        }

        if(!$recruitableCityList){
            return null;
        }


        $cmd = buildGeneralCommandClass('che_NPC능동', $this->general, $this->env, [
            'optionText' => '순간이동',
            'destCityID' => Util::choiceRandomUsingWeight($recruitableCityList),
        ]);
    

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do전방워프(): ?GeneralCommand
    {
        if(!$this->attackable){
            return null;
        }
        if (in_array($this->dipState, [self::d평화, self::d선포])) {
            return null;
        }

        if(!($this->genType & self::t통솔장)){
            return null;
        }

        if($this->general->getVar('crew') < $this->nationPolicy->minWarCrew){
            return null;
        }

        if($this->city['front']){
            return null;
        }

        if($this->nation['rice'] < GameConst::$baserice){
            return null;
        }

        $this->categorizeNationCities();
        $this->categorizeNationGeneral();

        $candidateCities = [];

        if(!$this->frontCities){
            throw new \sammo\MustNotBeReachedException('attackable인데 frontCities가 없음');
        }

        foreach($this->frontCities as $frontCity){
            $candidateCities[$frontCity['city']] = $frontCity['important'];
        }

        $cmd = buildGeneralCommandClass('che_NPC능동', $this->general, $this->env, [
            'optionText' => '순간이동',
            'destCityID' => Util::choiceRandomUsingWeight($candidateCities),
        ]);
    

        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do내정워프(): ?GeneralCommand
    {
        if($this->nation['rice'] < GameConst::$baserice){
            return null;
        }
        
        $city = $this->city;
        if(Util::randBool(0.6)){
            return null;
        }

        $develRate = $this->calcCityDevelRate($city);
        $availableTypeCnt = 0;

        $warpProp = 1;
        foreach($develRate as $develKey => [$develVal, $develType]){
            if(!($this->genType & $develType)){
                continue;
            }
            $warpProp *= $develVal;
            $availableTypeCnt += 1;
        }

        if($availableTypeCnt === 0){
            //무능장은 인탐을 하세요.
            return null;
        }

        if(!Util::randBool($warpProp)){
            return null;
        }

        $this->categorizeNationCities();
        $candidateCities = [];
        foreach($this->supplyCities as $candidate){
            if($city['city'] === $candidate['city']){
                continue;
            }
            $realDevelRate = 0.0001; //하단의 나눗셈

            foreach($this->calcCityDevelRate($candidate) as $develKey => [$develVal, $develType]){
                if(!($this->genType & $develType)){
                    continue;
                }
                $realDevelRate += $develVal;
            }

            
            $realDevelRate /= $availableTypeCnt;
            
            if($realDevelRate >= 0.95){
                continue;
            }

            $candidateCities[$candidate['city']] = 1 / ($realDevelRate * \sqrt(count($candidate['generals']??[]) + 1));
        }

        if(!$candidateCities){
            return null;
        }

        $cmd = buildGeneralCommandClass('che_NPC능동', $this->general, $this->env, [
            'optionText' => '순간이동',
            'destCityID' => Util::choiceRandomUsingWeight($candidateCities),
        ]);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }


    protected function do귀환(): ?GeneralCommand
    {
        $general = $this->general;
        $city = $this->city;
        if($city['nation'] == $general->getNationID() && $city['supply']){
            return null;
        }

        $cmd = buildGeneralCommandClass('che_귀환', $this->general, $this->env);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do집합(): ?GeneralCommand
    {
        $general = $this->general;

        if($general->getNPCType() == 5){
            $newKillTurn = ($general->getVar('killturn') + Util::randRangeInt(2, 4)) % 5;
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
        $nationCities = $db->queryFirstColumn('SELECT city a FROM city WHERE nation != 0');

        $occupiedCities = [];
        foreach ($lordCities as $tCityId) {
            $occupiedCities[$tCityId] = 2;
        }
        foreach ($nationCities as $tCityId) {
            $occupiedCities[$tCityId] = 1;
        }

        $currCityID = $general->getCityID();

        $targetCity = [];

        //NOTE: 최단 거리가 현재 도시에서 '어떻게 가야' 가장 짧은지 알 수가 없으므로, 한칸 간 다음 계산하기로
        //출발지가 정해져 있으므로, searchAllDistanceByCityList는 비효율적.
        foreach (array_keys(CityConst::byID($currCityID)->path) as $nearCityID) {
            if (CityConst::byID($nearCityID)->level < 4) {
                $targetCity[$nearCityID] = 0.5;
            } else if (!key_exists($nearCityID, $occupiedCities)) {
                $targetCity[$nearCityID] = 2;
            } else {
                $targetCity[$nearCityID] = 0;
            }

            $distanceFrom = searchDistance($nearCityID, 4, true);
            foreach ($distanceFrom as $distance => $distCities) {
                foreach ($distCities as $distCity) {
                    if (key_exists($distCity, $occupiedCities)) {
                        continue;
                    }
                    if (CityConst::byID($distCity)->level < 4) {
                        continue;
                    }

                    $targetCity[$nearCityID] += 1 / (2 ** $distance);
                }
            }
        }

        $cmd = buildGeneralCommandClass('che_이동', $general, $this->env, [
            'destCityID'=>Util::choiceRandomUsingWeight($targetCity)
        ]);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do거병(): ?GeneralCommand
    {
        $general = $this->general;
        // 초반이면서 능력이 좋은놈 위주로 거병
        if($general->getVar('makelimit')){
            return null;
        }
        if(!$this->generalPolicy->can건국){
            return null;
        }

        $prop = Util::randF() * (GameConst::$defaultStatNPCMax + GameConst::$chiefStatMin) / 2;
        $ratio = ($this->fullLeadership + $this->fullStrength + $this->fullIntel) / 3;
        

        if($prop >= $ratio){
            return null;
        }

        //XXX: 건국기한 2년
        $more = Util::valueFit(3 - $this->env['year'] + $this->env['init_year'], 1, 3);
        if(!Util::randBool(0.005*$more)){
            return null;
        }

        $cmd = buildGeneralCommandClass('che_거병', $general, $this->env, null);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do해산(): ?GeneralCommand
    {
        $cmd = buildGeneralCommandClass('che_해산', $this->general, $this->env, null);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do건국(): ?GeneralCommand
    {
        $nationType = Util::choiceRandom(GameConst::$availableNationType);
        $nationColor = Util::choiceRandom(array_keys(GetNationColors()));
        $cmd = buildGeneralCommandClass('che_건국', $this->general, $this->env, [
            'nationName' => "㉿" . mb_substr($this->general->getName(), 1),
            'nationType' => $nationType,
            'colorType' => $nationColor
        ]);
        if(!$cmd->hasFullConditionMet()){
            return null;
        }

        return $cmd;
    }

    protected function do선양(): ?GeneralCommand
    {
        $db = DB::db();
        $cmd = buildGeneralCommandClass('che_선양', $this->general, $this->env, [
            'destGeneralID' => $db->queryFirstField('SELECT `no` FROM general WHERE nation = %i AND npc != 5 ORDER BY RAND() LIMIT 1', $this->general->getNationID())
        ]);

        if(!$cmd->hasFullConditionMet()){
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
                'SELECT nation FROM general WHERE `officer_level`=12 AND npc=9 and nation not in %li ORDER BY RAND() limit 1',
                $general->getAuxVar('joinedNations') ?? [0]
            );

            if ($rulerNation) {
                $cmd = buildGeneralCommandClass('che_임관', $general, $env, ['destNationID' => $rulerNation]);
                if(!$cmd->hasFullConditionMet()){
                    return null;
                }

                return $cmd;
            }
        }

        if(Util::randBool(0.3)){
            if ($env['startyear'] + 3 > $env['year']) {
                //초기 임관 기간에서는 국가가 적을수록 임관 시도가 적음
                $nationCnt = $db->queryFirstField('SELECT count(nation) FROM nation');
                $notFullNationCnt = $db->queryFirstField('SELECT count(nation) FROM nation WHERE gennum < %i', GameConst::$initialNationGenLimit);
                if ($nationCnt == 0 || $notFullNationCnt == 0) {
                    return null;
                }
                
                if (Util::randBool(pow(1 / $nationCnt / pow($notFullNationCnt, 3), 1 / 4))) {
                    //국가가 1개일 경우에는 '임관하지 않음'
                    return null;
                }
            }

            if ($general->getVar('affinity') == 999) {
                return null;
            }

            //랜임 커맨드 입력.
            $cmd = buildGeneralCommandClass('che_랜덤임관', $general, $env);
            if(!$cmd->hasFullConditionMet()){
                return null;
            }

            return $cmd;
        }

        if(Util::randBool(0.2)){
            $paths = array_keys(CityConst::byID($city['city'])->path);

            $cmd = buildGeneralCommandClass('che_이동', $general, $env, ['destCityID' => Util::choiceRandom($paths)]);
            if(!$cmd->hasFullConditionMet()){
                return null;
            }

            return $cmd;
        }
        return null;
    }

    protected function doNPC사망대비(): ?GeneralCommand
    {
        $general = $this->getGeneralObj();

        if($general->getVar('killturn') > 5){
            return null;
        }

        if($general->getNationID() == 0){
            $cmd = buildGeneralCommandClass('che_인재탐색', $general, $this->env);
            if(!$cmd->hasFullConditionMet() || Util::randBool()){
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
        if($general->getNationID() == 0){
            $cmd = buildGeneralCommandClass('che_인재탐색', $general, $this->env);
            if(!$cmd->hasFullConditionMet() || Util::randBool(0.8)){
                $cmd = buildGeneralCommandClass('che_견문', $general, $this->env);
            }
            return $cmd;
        }


        $candidate = [];
        $nation = $this->nation;
        if($nation['gold'] < $this->nationPolicy->reqNationGold){
            $candidate[] = 'che_물자조달';
        }
        if($nation['rice'] < $this->nationPolicy->reqNationRice){
            $candidate[] = 'che_물자조달';
        }
        $candidate[] = 'che_인재탐색';


        $cmd = buildGeneralCommandClass(Util::choiceRandom($candidate), $this->general, $this->env);
        if(!$cmd->hasFullConditionMet()){
            return buildGeneralCommandClass('che_물자조달', $this->general, $this->env);
        }
        return $cmd;
    }

    protected function categorizeNationCities():void{

        if($this->nationCities !== null){
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

            if($nationCity['supply']){
                $supplyCities[$cityID] = $nationCity;
            }
            if($nationCity['front']){
                $frontCities[$cityID] = $nationCity;
            }
            else{
                $backupCities[$cityID] = $nationCity;
            }

            $nationCities[$cityID] = $nationCity;
        }

        $this->nationCities = $nationCities;
        $this->frontCities = $frontCities;
        $this->supplyCities = $supplyCities;
        $this->backupCities = $backupCities;
    }


    protected function categorizeNationGeneral():void{
        if($this->userGenerals !== null){
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
        foreach($nationGenerals as $nationGeneral){
            $recentWar = $nationGeneral->calcRecentWarTurn($this->env['turnterm']);
            if($recentWar >= $nationGeneral->getVar('belong') * 12){
                //임관전 전투는 제외
                continue;
            }

            $lastWar = min($lastWar, $recentWar);
        }

        foreach($nationGenerals as $nationGeneral){
            $generalID = $nationGeneral->getID();
            $cityID = $nationGeneral->getCityID();
            $npcType = $nationGeneral->getNPCType();
            $officerLevel = $nationGeneral->getVar('officer_level');
            $officerCity = $nationGeneral->getVar('officer_city');

            if($officerLevel > 4){
                $chiefGenerals[$officerLevel] = $nationGeneral;
            }
            else if($officerLevel > 2){
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

            if($npcType == 5){
                //부대장임
                $troopLeaders[$generalID] = $nationGeneral;
            }
            else if($nationGeneral->getVar('troop') === $generalID && $nationGeneral->getReservedTurn(0, $this->env)->getName() === 'che_집합'){
                //비 NPC부대장임
                $troopLeaders[$generalID] = $nationGeneral;
            }
            else if($nationGeneral->getVar('killturn') < 5){
                //삭턴이 몇 안남은 장수는 '내정장 npc'로 처리
                $npcCivilGenerals[$generalID] = $nationGeneral;
            }
            else if($npcType < 2) {
                $userGenerals[$generalID] = $nationGeneral;
                if($nationGeneral->calcRecentWarTurn($this->env['turnterm']) <= $lastWar + 12){
                    $userWarGenerals[$generalID] = $nationGeneral;
                }
                else if(
                    $this->dipState !== self::d평화 &&
                    $nationGeneral->getVar('crew') >= $this->nationPolicy->minWarCrew
                ){
                    $userWarGenerals[$generalID] = $nationGeneral;
                    //TODO: 훈련,사기 나중에 되돌릴 것
                    //TODO: 징,모병턴 기준으로 계산하는 코드를 추가할 것
                }
                else{
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


    public function chooseNationTurn(NationCommand $reservedCommand): NationCommand{
        //TODO: NationTurn과 InstantNationTurn 구분 필요
        $lastTurn = $reservedCommand->getLastTurn();
        $general = $this->general;
        $npcType = $general->getNPCType();

        $this->categorizeNationGeneral();
        $this->categorizeNationCities();

        $month = $this->env['month'];
        if($npcType >= 2){
            if(!($general->getAuxVar('use_auto_nation_turn')??1)){
                $general->setAuxVar('use_auto_nation_turn', 1);
            }
            if($general->getVar('officer_level') == 12){
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
            }
            else if(in_array($month, [3, 6, 9, 12])){
                $this->chooseNonLordPromotion();
            }
        }

        if(!($reservedCommand instanceof Command\Nation\휴식)){
            if($reservedCommand->hasFullConditionMet()){
                $reservedCommand->reason = 'reserved';
                return $reservedCommand;
            }
            $date = $general->getTurnTime($general::TURNTIME_HM);
            $failString = $reservedCommand->getFailString();
            $text = "{$failString} <1>{$date}</>";
            $general->getLogger()->pushGeneralActionLog($text);
        }

        foreach($this->nationPolicy->priority as $actionName){
            
            if(!property_exists($this->nationPolicy, 'can'.$actionName)){
                continue;
            }
            if(!$this->nationPolicy->{'can'.$actionName}){
                continue;
            }
            if($npcType < 2 && !($this->nationPolicy::$availableInstantTurn[$actionName]??false)){
                continue;
            }
            /** @var ?NationCommand */
            $result = $this->{'do'.$actionName}($lastTurn);
            if($result !== null){
                $result->reason = 'do'.$actionName;
                return $result;
            }
        }
        $cmd = buildNationCommandClass(null, $this->general, $this->env, $this->general->getLastTurn());
        $cmd->reason = 'neutral';
        return $cmd;
    }

    public function chooseInstantNationTurn(NationCommand $reservedCommand): ?NationCommand{
        if($reservedCommand->hasFullConditionMet()){
            return $reservedCommand;
        }

        foreach($this->nationPolicy->priority as $actionName){
            /** @var ?NationCommand */
            if(!key_exists($actionName, $this->nationPolicy::$availableInstantTurn)){
                continue;
            }
            if(!$this->nationPolicy->{'can'.$actionName}){
                continue;
            }
            $result = $this->{'do'.$actionName}($reservedCommand);
            if($result !== null){
                return $result;
            }
        }
        return buildNationCommandClass(null, $this->general, $this->env, $this->general->getLastTurn());
    }

    public function chooseGeneralTurn(GeneralCommand $reservedCommand): GeneralCommand{
        $general = $this->general;
        $npcType = $general->getNPCType();
        $nationID = $general->getNationID();

        //특별 메세지 있는 경우 출력 하루 4번
        $term = $this->env['turnterm'];
        if ($general->getVar('npcmsg') && Util::randBool($term / (6 * 60))) {
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


        if($npcType >= 2 && $general->getVar('defence_train') != 80){
            $general->setVar('defence_train', 80);
        }

        if($general->getVar('officer_level') === 12 && $this->generalPolicy->can선양){
            $result = $this->do선양();
            if($result !== null){
                $result->reason='do선양';
                return $result;
            }
        }
        
        if($npcType == 5){
            if($general->getNationID()==0){
                $general->setVar('killturn', 1);
                $reservedCommand->reason='사망';
                return $reservedCommand;
            }
            $result = $this->do집합();
            if(!$result){
                throw new MustNotBeReachedException();
            }
            $result->reason='do집합';
            return $result;
        }

        if(!($reservedCommand instanceof Command\General\휴식)){
            $reservedCommand->reason='do예약턴';
            return $reservedCommand;
        }

        if ($general->getVar('injury') > 10) {
            $result = buildGeneralCommandClass('che_요양', $general, $this->env);
            $result->reason='do요양';
            return $result;
        }

        if(($npcType == 2 || $npcType == 3) && $nationID == 0){
            $result = $this->do거병();
            if($result !== null){
                $result->reason='do거병';
                return $result;
            }
        }

        if($nationID === 0 && $this->generalPolicy->can국가선택){
            $result = $this->do국가선택();
            if($result !== null){
                $result->reason='do국가선택';
                return $result;
            }
            $result = $this->do중립();
            $result->reason='do중립';
            return $result;
        }

        if($npcType < 2 && $nationID === 0 && !$this->generalPolicy->can국가선택){
            $reservedCommand->reason = '재야유저';
            return $reservedCommand;
        }

        if($npcType >= 2 && $general->getVar('officer_level') == 12 && !$this->nation['capital']){
            //방랑군 건국
            $result = $this->do건국();
            if($result !== null){
                $result->reason='do건국';
                return $result;
            }
            $result = $this->do방랑군이동();
            if($result !== null){
                $result->reason='do방랑군이동';
                return $result;
            }

            $result = $this->do해산();
            if($result !== null){
                $result->reason='do해산';
                return $result;
            }
        }

        foreach($this->generalPolicy->priority as $actionName){
            if(!property_exists($this->generalPolicy, 'can'.$actionName)){
                continue;
            }
            if(!($this->generalPolicy->{'can'.$actionName})){
                continue;
            }
            /** @var ?GeneralCommand */
            $result = $this->{'do'.$actionName}();
            if($result !== null){
                $result->reason='do'.$actionName;
                return $result;
            }
        }

        $result = $this->do중립();
        $result->reason='do중립';
        return $result;
    }

    protected function calcNationDevelopedRate()
    {
        if ($this->devRate !== null) {
            return $this->devRate;
        }

        $devRate = [
            'all'=>0,
        ];


        foreach($this->supplyCities as $city){
            foreach($this->calcCityDevelRate($city) as $develKey => [$devScore, $devType]){
                if($develKey == 'trust'){
                    continue;
                }
                if(!key_exists($develKey, $devRate)){
                    $devRate[$develKey] = 0;
                }
                $devRate[$develKey] += $devScore;
                $devRate['all'] += $devScore;
            }
        }
        foreach(array_keys($devRate) as $key){
            $devRate[$key] /= count($this->supplyCities);
        }
        $devRate['all'] /= count($devRate) - 1;
        $this->devRate = $devRate;
        return $this->devRate;
    }

    protected function chooseNonLordPromotion(){
        //빈자리는 아무나 채움
        $db = DB::db();

        $setChiefLevel = 0;
        $minChiefLevel = getNationChiefLevel($this->nation['level']);
        $chiefSet = $this->nation['chief_set'];
        foreach(Util::range($minChiefLevel, 12) as $chiefLevel){
            if(isOfficerSet($chiefSet, $chiefLevel)){
                continue;
            }
            if(key_exists($chiefLevel, $this->chiefGenerals)){
                continue;
            }
            if($this->general->getVar('officer_level') == $chiefLevel){
                continue;
            }

            
            $picked = false;
            foreach(Util::range(5) as $idx){
                
                /** @var General */
                if($this->npcWarGenerals){
                    $randGeneral = Util::choiceRandom($this->npcWarGenerals);
                }
                else if($this->npcCivilGenerals){
                    $randGeneral = Util::choiceRandom($this->npcCivilGenerals);
                }
                else if($this->userWarGenerals){
                    $randGeneral = Util::choiceRandom($this->userWarGenerals);
                }
                else if($this->userCivilGenerals){
                    $randGeneral = Util::choiceRandom($this->userCivilGenerals);
                }
                else{
                    break;
                }
                
                if($randGeneral->getVar('officer_level') != 1){
                    continue;
                }

                if($chiefLevel == 11){
                    $picked = true;
                    break;
                }

                if($chiefLevel % 2 == 0){
                    if($randGeneral->getStrength(false, false, false, false) < GameConst::$chiefStatMin){
                        continue;
                    }
                }
                else{
                    if($randGeneral->getIntel(false, false, false, false) < GameConst::$chiefStatMin){
                        continue;
                    }
                }
                $picked = true;
                break;
            }

            if(!$picked || !$randGeneral){
                continue;
            }

            /** @var General $randGeneral */
            $randGeneral->setVar('officer_level', $chiefLevel);
            $randGeneral->setVar('officer_city', 0);
            $randGeneral->applyDB($db);
            $this->nation['chief_set'] |= doOfficerSet(0, $chiefLevel);
            $setChiefLevel |= doOfficerSet(0, $chiefLevel);
            $this->chiefGenerals[$chiefLevel] = $randGeneral;
        }

        if($setChiefLevel){
            $db->update('nation', [
                'chief_set' => $db->sqleval('chief_set | %i', $setChiefLevel)
            ], 'nation=%i', $this->nation['nation']);
        }
    }

    protected function calcCityDevelRate(array $city){
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

    protected function choosePromotion(){
        $db = DB::db();

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $minChiefLevel = getNationChiefLevel($nation['level']);

        $userChiefCnt = 0;

        $minUserKillturn = $this->env['killturn'] - Util::toInt(240 / $this->env['turnterm']);
        $minNPCKillturn = 36;

        foreach(Util::range($minChiefLevel, 12) as $chiefLevel){
            if(!key_exists($chiefLevel, $this->chiefGenerals)){
                continue;
            }
            $chief = $this->chiefGenerals[$chiefLevel];
            if($chief->getNPCType() < 2 && $chief->getVar('killturn') >= $minUserKillturn){
                $userChiefCnt+=1;
                $chief->setVar('permission', 'ambassador');
            }
        }

        

        $minBelong = min($this->general->getVar('belong') - 1, 3);
        $updatedChiefSet = 0;

        /** @var General[] */
        $nextChiefs = [];

        if($userChiefCnt == 0 && $this->userGenerals && !isOfficerSet($nation['chief_set'], 11)){
            $userGenerals = $this->userGenerals;
            uasort($userGenerals, function(General $lhs, General $rhs){
                return -($lhs->getVar('leadership')<=>$rhs->getVar('leadership'));
            });
            foreach($userGenerals as $general){
                if($general->getVar('killturn') < $minUserKillturn){
                    continue;
                }
                if($general->getVar('belong') < $minBelong){
                    continue;
                }
                if($general->getVar('officer_level') !== 1){
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
        uasort($generals, function(General $lhs, General $rhs){
            $lhsStat = $lhs->getLeadership(false, false, false, false) * 2
                + $lhs->getStrength(false, false, false, false)
                + $lhs->getIntel(false, false, false, false);
            $rhsStat = $rhs->getLeadership(false, false, false, false) * 2
                + $rhs->getStrength(false, false, false, false)
                + $rhs->getIntel(false, false, false, false);
            return -($lhsStat <=> $rhsStat);
        });


        
        foreach(Util::range(11, $minChiefLevel-1, -1) as $chiefLevel) {
            if(isOfficerSet($nation['chief_set'], $chiefLevel)){
                continue;
            }
            if($this->general->getVar('officer_level') === $chiefLevel){
                continue;
            }
            
            if(key_exists($chiefLevel, $this->chiefGenerals)){
                $oldChief = $this->chiefGenerals[$chiefLevel];
                if($oldChief->getNPCType() < 2 && $oldChief->getVar('killturn') >= $minChiefLevel){
                    continue;
                }
            }

            if(!key_exists($chiefLevel, $this->chiefGenerals) && !key_exists($chiefLevel, $nextChiefs)){
                $newChiefProb = 1;
            }
            else{
                $newChiefProb = Util::randF(0.1);
            }

            if($newChiefProb < 1 && !Util::randF($newChiefProb)){
                continue;
            }


            $newChief = null;
            foreach($generals as $general){
                if($general->getVar('officer_level') !== 1){
                    continue;
                }
                if($general->getNPCType() < 2 && $general->getVar('killturn') < $minUserKillturn){
                    continue;
                }
                if($general->getNPCType() >= 2 && $general->getVar('killturn') < $minNPCKillturn){
                    continue;
                }

                if($chiefLevel == 11){
                }
                else if($chiefLevel % 2 == 0 && $general->getStrength(false, false, false, false) < GameConst::$chiefStatMin){
                    continue;
                }
                else if($chiefLevel % 2 == 1 && $general->getIntel(false, false, false, false) < GameConst::$chiefStatMin){
                    continue;
                }

                if($general->getNPCType() < 2 && $userChiefCnt >= 3){
                    continue;
                }

                $newChief = $general;
                break;  
            }

            if(!$newChief){
                continue;
            }

            if($newChief->getNPCType() < 2){
                $userChiefCnt += 1;
                $newChief->setVar('permission', 'ambassador');
            }

            $nextChiefs[$chiefLevel] = $newChief;
            $newChief->setVar('officer_level', $chiefLevel);
            $newChief->setVar('officer_city', 0);
            $nation['chief_set'] |= doOfficerSet(0, $chiefLevel);
            $updatedChiefSet |= doOfficerSet(0, $chiefLevel);
        }

        foreach($nextChiefs as $chiefLevel=>$chief){
            $oldChief = $this->chiefGenerals[$chiefLevel]??null;
            if($oldChief){
                $oldChief->setVar('officer_level', 1);
                $oldChief->setVar('officer_city', 0);
                $oldChief->applyDB($db);
            }
            $chief->applyDB($db);
            $this->chiefGenerals[$chiefLevel] = $chief;
        }
        if($updatedChiefSet){
            $db->update('nation', [
                'chief_set'=>$db->sqleval('chief_set | %i', $updatedChiefSet)
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
        if($this->supplyCities){
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

        $dedicationList = array_map(function(General $general){
            return $general->getRaw();
        }, array_filter($this->nationGenerals, function(General $rawGeneral){
            return $rawGeneral->getVar('officer_level') != 5;
        }));

        $goldIncome  = getGoldIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $warIncome  = getWarGoldIncome($nation['type'], $cityList);
        $income = $goldIncome + $warIncome;

        $outcome = getOutcome(100, $dedicationList);

        $bill = intval($income / $outcome * 90); // 수입의 90% 만 지급
        if($nation['gold'] + $income - $outcome > $this->nationPolicy->reqNationGold * 2){
            $moreBill = ($nation['gold'] + $income - $this->nationPolicy->reqNationGold * 2) / $outcome * 80;
            if($moreBill > $bill){
                $bill = intval(($moreBill + $bill)/2);
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

        $dedicationList = array_map(function(General $general){
            return $general->getRaw();
        }, array_filter($this->nationGenerals, function(General $rawGeneral){
            return $rawGeneral->getVar('officer_level') != 5;
        }));

        $riceIncome = getRiceIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $wallIncome = getWallIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $income = $riceIncome + $wallIncome;

        $outcome = getOutcome(100, $dedicationList);

        $bill = intval($income / $outcome * 90); // 수입의 90% 만 지급
        if($nation['rice'] + $income - $outcome > $this->nationPolicy->reqNationRice * 2){
            $moreBill = ($nation['rice'] + $income - $this->nationPolicy->reqNationRice * 2) / $outcome * 80;
            if($moreBill > $bill){
                $bill = intval(($moreBill + $bill)/2);
            }
        }

        $bill = Util::valueFit($bill, 20, 200);

        $db->update('nation', [
            'bill' => $bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }
}
