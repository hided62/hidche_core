<?php
namespace sammo;

class GeneralAI{
    /**
     * @var General $general
     */
    protected $general;
    protected $city;
    protected $nation;
    protected $genType;

    protected $env;
    protected $baseDevelCost;

    protected $leadership;
    protected $strength;
    protected $intel;

    protected $dipState;
    protected $warTargetNation;
    protected $attackable;

    protected $devRate = null;
    
    protected $nationGenerals;
    protected $npcCivilGenerals;
    protected $npcWarGenerals;
    protected $userGenerals;
    protected $lostGenerals;

    const t무장 = 1;
    const t지장 = 2;
    const t통솔장 = 4;

    const d평화 = 0;
    const d선포 = 1;
    const d징병 = 2;
    const d직전 = 3;
    const d전쟁 = 4;

    //수뇌용

    public function __construct(General $general){
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $this->env = $gameStor->getValues(['startyear','year','month','turnterm','killturn','scenario','gold_rate','rice_rate', 'develcost']);
        $this->baseDevelCost = $this->env['develcost'] * 12;
        $this->general = $general;
        if($general->getRawCity() === null){
            $city = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $general->getCityID());
            $general->setRawCity($city);
        }
        $this->city = $general->getRawCity();
        $this->nation = $db->queryFirstRow(
            'SELECT nation,level,capital,tech,gold,rice,rate,type,color,name,war FROM nation WHERE nation = %i',
            $general->getNationID()
        )??[
            'nation'=>0,
            'level'=>0,
            'tech'=>0,
            'gold'=>0,
            'rice'=>0,
            'type'=>GameConst::$neutralNationType,
            'color'=>'#000000',
            'name'=>'재야',
        ];

        $this->leadership = $general->getLeadership(true, true, true, true);
        $this->strength = $general->getStrength(true, true, true, true);
        $this->intel = $general->getIntel(true, true, true, true);

        $this->calcGenType();

        $this->calcDiplomacyState();

    }

    public function getGeneralObj():General{
        return $this->general;
    }

    protected function calcGenType(){
        $leadership = $this->leadership;
        $strength = Util::valueFit($this->strength, 1);
        $intel = Util::valueFit($this->intel, 1);

        //무장
        if ($strength >= $intel) {
            $genType = self::t무장;
            if ($intel >= $strength * 0.8) {  //무지장
                if(Util::randBool($intel / $strength / 2)){
                    $genType |= self::t지장;
                }
                
            }
            //지장
        } else {
            $genType = self::t지장;
            if ($strength >= $intel * 0.8 && Util::randBool(0.2)) {  //지무장
                if(Util::randBool($strength / $intel / 2)){
                    $genType |= self::t무장;
                }
            }
        }

        //통솔
        if ($leadership >= 40) {
            $genType |= self::t통솔장;
        }
        $this->genType = $genType;
    }

    protected function calcDiplomacyState(){
        $db = DB::db();
        $nationID = $this->general->getNationID();
        $env = $this->env;

        if(Util::joinYearMonth($env['year'], $env['month']) <= Util::joinYearMonth($env['startyear']+2, 5)){
            $this->dipState = self::d평화;
            $this->attackable = false;
            return;
        }

        $frontStatus = $db->queryFirstField('SELECT max(front) FROM city WHERE nation=%i AND supply=1', $nationID);
        // 공격가능도시 있으면
        $this->attackable = ($frontStatus !== null)?$frontStatus:false;

        $warTarget = $db->queryAllLists(
            'SELECT you, state FROM diplomacy WHERE me = %i AND (state = 0 OR (state = 1 AND term < 5))',
            $nationID
        );

        $onWar = false;
        $warTargetNation = [];
        foreach($warTarget as [$warNationID, $warState]){
            if($warState == 0){
                $onWar = true;
                $warTargetNation[$warNationID] = 2;
            }
            else{
                $warTargetNation[$warNationID] = 1;
            }
        }
        $warTargetNation[0] = 0;
        $this->warTargetNation = $warTargetNation;

    
        $minWarTerm = $db->queryFirstField('SELECT min(term) FROM diplomacy WHERE me = %i AND state=1', $nationID);
        if($minWarTerm === null){
            $this->dipState = self::d평화;
        }
        else if($minWarTerm > 8){
            $this->dipState = self::d선포;
        }
        else if($minWarTerm > 5){
            $this->dipState = self::d징병;
        }
        else{
            $this->dipState = self::d직전;
        }

        if($this->attackable){
            //전쟁으로 인한 attackable인가?
            if($onWar){
                $this->dipState = self::d전쟁;
            }
            else{
                $this->dipState = self::d징병;
            }
        }
    }

    protected function chooseDevelopTurn(bool &$cityFull):array{
        $general = $this->general;
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $genType = $this->genType;
        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $cityFull = false;

        $develRate = [
            'trust'=>$city['trust'],
            'pop'=>$city['pop']/$city['pop_max'],
            'agri'=>$city['agri']/$city['agri_max'],
            'comm'=>$city['comm']/$city['comm_max'],
            'secu'=>$city['secu']/$city['secu_max'],
            'def'=>$city['def']/$city['def_max'],
            'wall'=>$city['wall']/$city['wall_max'],
        ];

        // 우선 선정
        if($develRate['trust'] < 1 && Util::randBool($leadership / 60)){
            return ['che_주민선정', null];
        }

        $commandList = [];

        if($genType & self::t무장){
            if($develRate['secu'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_치안강화', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_치안강화'] = $strength / 3;
                    if(in_array($city['front'], [1,3])){
                        $commandList['che_치안강화'] /= 2;
                    }
                }
            }
            if($develRate['def'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_수비강화', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_수비강화'] = $strength / 3;
                }
            }
            if($develRate['wall'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_성벽보수'] = $strength / 3;
                    if(in_array($city['front'], [1,3])){
                        $commandList['che_성벽보수'] /= 2;
                    }
                }
            }
        }
        if($genType & self::t지장){
            if($develRate['agri'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_농지개간', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_농지개간'] = $intel / 2;
                    if(in_array($city['front'], [1,3])){
                        $commandList['che_농지개간'] /= 2;
                    }
                }
            }
            if($develRate['comm'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_상업투자', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_상업투자'] = $intel / 2;
                    if(in_array($city['front'], [1,3])){
                        $commandList['che_상업투자'] /= 2;
                    }
                }
            }
            if(!TechLimit($env['startyear'], $env['year'], $nation['tech'])){
                $commandObj = buildGeneralCommandClass('che_기술연구', $general, $env);
                if($commandObj->isRunnable()){
                    if(!TechLimit($env['startyear'], $env['year'], $nation['tech']+1000)){
                        //한등급 이상 뒤쳐져 있다면, 조금 더 열심히 하자.
                        $commandList['che_기술연구'] = $intel;
                    }
                    else{
                        $commandList['che_기술연구'] = $intel / 4;
                    }
                    
                }
            }
        }
        if($genType & self::t통솔장){
            if($develRate['trust'] < 1){
                $commandObj = buildGeneralCommandClass('che_주민선정', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_주민선정'] = power($leadership * 2, 1 / ($develRate['trust'] + 0.001));
                }
            }
            if($develRate['pop'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_정착장려', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_정착장려'] = $leadership / 2;
                    if(in_array($city['front'], [1,3])){
                        $commandList['che_정착장려'] *= 2;
                    }
                }
            }
        }

        if(!$commandList){
            $cityFull = true;
        }

        $genCount = DB::db()->queryFirstField('SELECT count(no) FROM general');
        $commandList['che_인재탐색'] = 500 / $genCount * 10;
        if(in_array($city['front'], [1,3])){
            $commandList['che_인재탐색'] /= 2;
        }

        $commandList['che_물자조달'] = (
            (GameConst::$minNationalGold + GameConst::$minNationalRice + 24*5*$env['develcost']) / 
            Util::valueFit($nation['gold'] + $nation['rice'], (GameConst::$defaultGold + GameConst::$defaultRice)/2)
        );

        return [Util::choiceRandomUsingWeight($commandList), null];
    }

    public function chooseRecruitCrewType():?array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $general->getNationID();

        $genType = $this->genType;
        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $tech = $nation['tech'];

        $db = DB::db();
    
        $raw = $general->getRaw();

        $dex = [
            GameUnitConst::T_FOOTMAN=>sqrt($general->getVar('dex0') + 500),
            GameUnitConst::T_ARCHER=>sqrt($general->getVar('dex10') + 500),
            GameUnitConst::T_CAVALRY=>sqrt($general->getVar('dex20') + 500),
            GameUnitConst::T_WIZARD=>sqrt($general->getVar('dex30') + 500),
            GameUnitConst::T_SIEGE=>sqrt($general->getVar('dex40') + 500),
        ];
        
    
        $cities = [];
        $regions = [];
    
        foreach($db->queryAllLists('SELECT city, region FROM city WHERE nation = %i', $nationID) as [$cityID, $regionID]){
            $cities[$cityID] = true;
            $regions[$regionID] = true;
        }
        $relYear = Util::valueFit($env['year'] - $env['startyear'], 0);
    
        
        $typesAll = [];
        if($genType & self::t무장){
            $types = [];
            foreach(GameUnitConst::byType(GameUnitConst::T_FOOTMAN) as $crewtype){
                if($crewtype->isValid($cities, $regions, $relYear, $tech)){
                    $score = $dex[GameUnitConst::T_FOOTMAN] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $score];
                }
            }
            foreach(GameUnitConst::byType(GameUnitConst::T_ARCHER) as $crewtype){
                if($crewtype->isValid($cities, $regions, $relYear, $tech)){
                    $score = $dex[GameUnitConst::T_ARCHER] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $score];
                }
            }
            foreach(GameUnitConst::byType(GameUnitConst::T_CAVALRY) as $crewtype){
                if($crewtype->isValid($cities, $regions, $relYear, $tech)){
                    $score = $dex[GameUnitConst::T_CAVALRY] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $dex[GameUnitConst::T_CAVALRY] + 500];
                }
            }
            foreach($types as [$crewtype, $score]){
                $typesAll[] = [$crewtype, $score / count($types)];
            }
        }
        
        if($genType & self::t지장){
            $types = [];
            foreach(GameUnitConst::byType(GameUnitConst::T_WIZARD) as $crewtype){
                if($crewtype->isValid($cities, $regions, $relYear, $tech)){
                    $score = $dex[GameUnitConst::T_WIZARD] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $score];
                }
            }
            foreach($types as [$crewtype, $score]){
                $typesAll[] = [$crewtype, $score / count($types)];
            }
        }

        if($typesAll){
            $type = Util::choiceRandomUsingWeightPair($types);
        }
        else{
            $type = GameUnitConst::DEFAULT_CREWTYPE;
        }
    
        //NOTE: 훈련과 사기진작은 '금만 사용한다'는 가정을 하고 있음
        $obj훈련 = buildGeneralCommandClass('che_훈련', $general, $env);
        $obj사기진작 = buildGeneralCommandClass('che_사기진작', $general, $env);

        $gold = $general->getVar('gold');
        $gold -= $obj훈련->getCost()[0] * 2;
        $gold -= $obj사기진작->getCost()[0] * 2;

        $cost = $general->getCrewTypeObj()->costWithTech($tech);
        $cost = $general->onCalcDomestic('징병', 'cost', $cost);
    
        $crew = intdiv($gold, $cost);
        $crew *= 100;

        return ['che_징병', [
            'crewType'=>$type,
            'amount'=>$crew
        ]];
    }

    public function getPayTurnCandidates(string $resName):array{
        $nation = $this->nation;
        $remainResource = ['gold'=>$nation['gold'], 'rice'=>$nation['rice']];
        $candidateCommand = [];
        //무지장 포상 여부
        if($this->npcCivilGenerals && $this->npcCivilGenerals[0]->$resName < $this->baseDevelCost){
            if($this->remainResource[$resName] >= $this->baseDevelCost / 2){
                $candidateCommand[] = [
                    ['che_포상', [
                        'destGeneralID' => $this->npcCivilGenerals[0]->id,
                        'isGold'=>$resName=='gold',
                        'amount'=>round($this->baseDevelCost,-2)
                    ]],
                    1
                ];
            }
            
        }

        if($this->npcWarGenerals){
            $targetNpcWarGeneral = Util::array_first($this->npcWarGenerals);
            $crewtype = GameUnitConst::byID($targetNpcWarGeneral->crewtype);
            //징병 2바퀴?
            $reqMoney = $crewtype->costWithTech($this->nation['tech'], $targetNpcWarGeneral->leadership) * 2.2;
            $enoughMoney = $reqMoney * 2;

            if($targetNpcWarGeneral->$resName < $reqMoney){
                //국고 1/5과 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetNpcWarGeneral->$resName) * ($remainResource[$resName] / 5));
                if ($remainResource[$resName] >= $payAmount / 2) {
                    $candidateCommand[] = [
                        ['che_포상', [
                            'destGeneralID' => $targetNpcWarGeneral->id,
                            'isGold'=>$resName=='gold',
                            'amount'=>$payAmount
                        ]],
                        2
                    ];
                }
            }
        }
        
        if($this->userGenerals){
            $targetUserGeneral = Util::array_first($this->userGenerals);
            $reqMoney = $crewtype->costWithTech($this->nation['tech'], $targetNpcWarGeneral->leadership) * 2*4*1.1;
            if($this->env['year'] > $this->env['startyear'] + 5){
                $reqMoney = max($reqMoney, 21000);
            }
            $enoughMoney = $reqMoney * 1.5;

            if($targetNpcWarGeneral->$resName < $reqMoney){
                //국고와 '충분한 금액'의 기하평균
                $payAmount = sqrt(($enoughMoney - $targetNpcWarGeneral->$resName) * $remainResource[$resName]);

                if($remainResource[$resName] >= $payAmount / 2){
                    $candidateCommand[] = [
                        ['che_포상', [
                            'destGeneralID' => $targetNpcWarGeneral->id,
                            'isGold'=>$resName=='gold',
                            'amount'=>$payAmount
                        ]],
                        2
                    ];
                }
                
            }
        }

        if(is_array($this->npcCivilGenerals)){
            $targetNpcCivilGeneral = Util::array_last($this->npcCivilGenerals??[]);
            $tooMuchMoney = $this->baseDevelCost * 2.5;
            $enoughMoney = $this->baseDevelCost * 1.5;
            
            if($targetNpcCivilGeneral->$resName >= $tooMuchMoney){
                $returnAmount = $targetNpcCivilGeneral->$resName - $enoughMoney;
                $candidateCommand[] = [
                    ['che_몰수', [
                        'destGeneralID' => $targetNpcCivilGeneral->id,
                        'isGold'=>$resName=='gold',
                        'amount'=>$returnAmount
                    ]],
                    3
                ];
            }
        }

        return $candidateCommand;
    }

    public function chooseNationTurn($command, $arg):array{
        $generalObj = $this->getGeneralObj();
        //NOTE: 수뇌 턴에서는 general과 city이름의 충돌 가능성이 있음
        $env = $this->env;

        $chiefID = $generalObj->getID();
        $nationID = $generalObj->getNationID();
        $nation = $this->nation;

        $db = DB::db();

        if($generalObj->getVar('npc') == 5){
            return [$command, $arg];
        }

        if($command && $command != '휴식'){
            return [$command, $arg];
        }

        if($this->nation['level'] == 0){
            return ['휴식', null];
        }

        if($generalObj->getVar('level') == 12 && $this->dipState == self::d평화 && !$this->attackable){
            

            $targetNationID = $this->findWarTarget();
            if($targetNationID !== null){
                return ['che_선전포고', ['destNationID'=>$targetNationID]];
            }
        }

        $nationCities = [];
        $frontCitiesID = [];
        $frontImportantCitiesID = [];
        $supplyCitiesID = [];
        $backupCitiesID = [];

        $tech = getTechCost($nation['tech']);

        foreach ($db->query('SELECT * FROM city WHERE nation = %i', $nationID) as $nationCity) {
            $nationCity['generals'] = [];
            $cityID = $nationCity['city'];
            $dev = 
                ($nationCity['agri'] + $nationCity['comm'] + $nationCity['secu'] + $nationCity['def'] + $nationCity['wall'])/
                ($nationCity['agri'] + $nationCity['comm'] + $nationCity['secu'] + $nationCity['def'] + $nationCity['wall']);
            $dev += $nationCity['pop'] / $nationCity['pop_max'];
            $dev /= 50;
    
            $nationCity['dev'] = $dev;
    
            $nationCities[$cityID] = $nationCity;
            
            if($nationCity['supply']){
                $supplyCitiesID[] = $cityID;
                if($nationCity['front']){
                    $frontCitiesID[] = $cityID;
                    if($nationCity['officer4']){
                        $frontImportantCitiesID[] = $cityID;
                    }
                }
                else{
                    $backupCitiesID[] = $cityID;
                }
            }
        }
        Util::shuffle_assoc($nationCities);
        shuffle($frontCitiesID);
        shuffle($supplyCitiesID);

        assert($supplyCitiesID);

        
        $commandList = [];

        

        $userGenerals = [];
        $lostGenerals = [];
        $npcCivilGenerals = [];
        $npcWarGenerals = [];
        
        foreach($db->query('SELECT `no`, nation, city, npc, `gold`, `rice`, leadership, `strength`, intel, killturn, crew, train, atmos, `level`, troop FROM general WHERE nation = %i', $nationID) as $rawNationGeneral) {

            $nationGeneral = (object)$rawNationGeneral;
            $cityID = $nationGeneral->city;
            $generalID = $nationGeneral->no;
    
            if($generalID == $chiefID){
                continue;
            }
    
            if(key_exists($cityID, $nationCities)){
                $nationCities[$cityID]['generals'][] = $nationGeneral;
                if(!$nationCities[$cityID]['supply']){
                    $lostGenerals[] = $nationGeneral;    
                }
            }
            else{
                $lostGenerals[] = $nationGeneral;
            }
    
            if($nationGeneral->npc<2 && $nationGeneral->killturn >= 5){
                $userGenerals[] = $nationGeneral;
            }
            else if($nationGeneral->leadership>=40 && $nationGeneral->killturn >= 5){
                $npcWarGenerals[] = $nationGeneral;
            }
            else{
                //삭턴이 몇 안남은 장수는 '내정장 npc'로 처리
                $npcCivilGenerals[] = $nationGeneral;
            }
    
            $nationGenerals[$generalID] = $nationGeneral;
        }
        Util::shuffle_assoc($nationGenerals);

        $this->nationGenerals = $nationGenerals;
        $this->npcCivilGenerals = $npcCivilGenerals;
        $this->npcWarGenerals = $npcWarGenerals;

        shuffle($lostGenerals);
        $this->lostGenerals = $lostGenerals;
        
        
    
        uasort($nationCities, function($lhs, $rhs){ 
            //키 순서를 지키지 않지만, 원래부터 random order를 목표로 하므로 크게 신경쓰지 않는다.
            return count($lhs['generals']) <=> count($rhs['generals']);
        });

        //타 도시에 있는 '유저장' 발령
        foreach($lostGenerals as $lostGeneral){
            if($lostGeneral->npc < 2){
                if(in_array($this->dipState, [self::d직전, self::d전쟁]) && $frontCitiesID){
                    $selCityID = Util::choiceRandom($frontCitiesID);
                }
                else{
                    $selCityID = Util::choiceRandom($supplyCitiesID);
                }
                $commandList[] = [['che_발령', ['destGeneralID'=>$lostGeneral->no, 'destCityID'=>$selCityID]], 200];
            }
        }

        $resName = Util::choiceRandom(['gold', 'rice']);

        usort($userGenerals, function($lhs, $rhs) use ($resName){
            return $lhs->$resName <=> $rhs->$resName;
        });

        usort($npcWarGenerals, function($lhs, $rhs) use ($resName){
            return $lhs->$resName <=> $rhs->$resName;
        });

        usort($npcCivilGenerals, function($lhs, $rhs) use ($resName){
            return $lhs->$resName <=> $rhs->$resName;
        });

        $avgUserRes = 0;
        foreach ($userGenerals as $userGeneral){
            $avgUserRes += $userGeneral->$resName;
        }
        $avgUserRes /= max(1, count($userGenerals));

        $avgNpcWarRes = 0;
        foreach ($npcWarGenerals as $npcWarGeneral){
            $avgNpcWarRes += $npcWarGeneral->$resName;
        }
        $avgNpcWarRes /= max(1, count($npcWarGenerals));

        $avgNpcCivilRes = 0;
        foreach ($npcCivilGenerals as $npcCivilGeneral){
            $avgNpcCivilRes += $npcCivilGeneral->$resName;
        }
        $avgNpcCivilRes /= max(1, count($npcCivilGenerals));

        //금쌀이 부족한 '유저장' 먼저 포상
        while ($nation[$resName] > ($resName=='gold'?1:2)*3000 && $userGenerals) {
            $isWarUser = null;
            
            foreach($userGenerals as $compUser){
                if($compUser->leadership >= 50){
                    $isWarUser = true;
                    break;
                }
                if(Util::randBool(0.2)){
                    $isWarUser = false;
                    break;
                }
            }

            if($isWarUser === null){
                break;
            }

            $compRes = $compUser->$resName;

            $work = false;
            if(!$isWarUser){
                $work = false;
            } else if ($compRes < $avgNpcWarRes*3) {
                $work = true;
            } elseif ($compRes < $avgNpcCivilRes * 4) {
                $work = true;
            }
            
            if((($isWarUser || $resName == 'gold') && $compUser->$resName < 21000) || ($compUser->$resName < 5000)){
                if($work){
                    //TODO: 새로 구현한 코드로 이전
                    $amount = min(GameConst::$maxIncentiveAmount, intdiv(($nation[$resName]-($resName=='rice'?(GameConst::$baserice):(GameConst::$basegold))), 3000)*1000 + 1000);
                    $commandList[] = [['che_포상', [
                        'destGeneralID'=>$userGenerals[0]->no,
                        'isGold'=>$resName=='gold',
                        'amount'=>$amount
                    ]], 10]; // 금,쌀 1000단위 포상
                }
                else{
                    $amount = min(GameConst::$maxIncentiveAmount, intdiv(($nation[$resName]-($resName=='rice'?(GameConst::$baserice):(GameConst::$basegold))), 5000)*1000 + 1000);
                    $commandList[] = [['che_포상', [
                        'destGeneralID'=>$userGenerals[0]->no,
                        'isGold'=>$resName=='gold',
                        'amount'=>$amount
                    ]], 1]; // 금,쌀 1000단위 포상
                }
                
            }
            break;
        }

        $minRes = $env['develcost'] * 24 * $tech;

        if($nation[$resName] < ($resName=='gold'?1:2)*3000) {  // 몰수
            // 몰수 대상
            $compUser = Util::array_last($userGenerals);
            $compNpcWar = Util::array_last($npcWarGenerals);
            $compNpcCivil = Util::array_last($npcCivilGenerals);
            
            $compUserRes = $compUser?$compUser->$resName:0;
            $compNpcWarRes = $compNpcWar?$compNpcWar->$resName*5:0;
            $compNpcCivilRes = $compNpcCivil?$compNpcCivil->$resName*10:0;
    
            [$compRes, $targetGeneral] = max(
                [$compNpcCivilRes, $compNpcCivil],
                [$compNpcWarRes, $compNpcWar],
                [$compUserRes, $compUser]
            );
    
            if($targetGeneral){
                if($targetGeneral === $compNpcCivil){
                    $amount = Util::round($targetGeneral->$resName - $minRes * 3, -2);
                }
                else{
                    $amount = min(10000, intdiv($targetGeneral->$resName, 5000)*1000 + 1000);
                }
                
                if($amount > 0){
                    $commandList[] = [['che_몰수', [
                        'destGeneralID'=>$targetGeneral->no,
                        'isGold'=>$resName=='gold',
                        'amount'=>$amount
                    ]], 3];
                }
                
            }
        } else{    // 포상
            $compNpcWar = Util::array_first($npcWarGenerals); 
            $compNpcCivil = null;
            foreach($npcCivilGenerals as $npcCivil){
                if($npcCivil->npc == 5){
                    continue;
                }
                $compNpcCivil = $npcCivil;
                break;
            }
    
            if($compNpcWar && $compNpcWar->$resName < 21000){
                $amount = min(100, intdiv(($nation[$resName]-($resName=='rice'?(GameConst::$baserice):(GameConst::$basegold))), 5000)*10 + 10)*100;
                $commandList[] = [['che_몰수', [
                    'destGeneralID'=>$compNpcWar->no,
                    'isGold'=>$resName=='gold',
                    'amount'=>$amount
                ]], 3];
            }
            if($compNpcCivil && $compNpcCivil->$resName < $minRes){
                $amount = intdiv($minRes+99, 100);
                $commandList[] = [['che_몰수', [
                    'destGeneralID'=>$compNpcCivil->no,
                    'isGold'=>$resName=='gold',
                    'amount'=>$amount
                ]], 2];
            }
        }

        //고립 도시 장수 발령
        foreach($lostGenerals as $lostGeneral){
            if($lostGeneral->npc<2){
                //고립 유저 장수는 이미 세팅했음
                continue;
            }
            if(in_array($this->dipState, [self::d직전, self::d전쟁]) && $frontCitiesID){
                $selCityID = Util::choiceRandom($frontCitiesID);
            }
            else{
                $selCityID = Util::choiceRandom($supplyCitiesID);
            }
            //고립된 장수가 많을 수록 발령 확률 증가
            $commandList[] = [['che_발령', [
                'destGeneralID'=>$lostGeneral->no,
                'destCityID'=>$selCityID
            ]], sqrt(count($lostGenerals)) * 10];
        }

        // 평시엔 균등 발령만
        if(in_array($this->dipState, [self::d평화, self::d선포]) && count($supplyCitiesID) > 1) {
            $targetCity = null;
            $minCity = null;
            $maxCity = null;
            $maxDevCity = null;
            foreach($nationCities as $nationCity){
                if($nationCity['dev']>=95){
                    continue;
                }
                if($nationCity['supply']){
                    $minCity = $nationCity;
                    break;
                }
                
            }

            //reverse_order T_T
            $maxCity = end($nationCities);
            if(!$minCity){
                $minCity = $maxCity;
            }
            while($maxCity['city'] !== $minCity['city']){
                if($nationCity['supply']){
                    break;
                }
                $maxCity = prev($nationCities);
            }

            foreach($nationCities as $nationCity){
                if($nationCity['city'] == $maxCity['city']){
                    break;
                }
                if(!$nationCity['supply']){
                    continue;
                }
                if($nationCity['dev'] < 70){
                    $targetCity = $nationCity;
                    break;
                }
            }

            foreach ($nationCities as $nationCity) {
                if(!$nationCity['supply']){
                    continue;
                }
                if(count($nationCity['generals']) == 0){
                    continue;
                }
                if($maxDevCity === null || $maxDevCity['dev'] < $nationCity['dev']){
                    $maxDevCity = $nationCity;
                }
            }

            if($targetCity === null || (count($targetCity['generals']) >= count($maxCity['generals']) - 1)){
                $targetCity = $minCity;
            }

            if($maxDevCity['dev'] >= 95 && $targetCity['city'] != $maxDevCity['city'] && $targetCity['dev'] <= 70){
                $targetGeneral = Util::choiceRandom($maxDevCity['generals']);
                if($targetGeneral->troop != 0){
                    $commandList[] = [['che_발령', [
                        'destGeneralID'=>$targetGeneral->no,
                        'destCityID'=>$targetCity['city']
                    ]], 2];
                }
            }

            if(count($targetCity['generals']) < count($maxCity['generals']) - 2){
                //세명 이상 차이나야 함
                $targetGeneral = Util::choiceRandom($maxCity['generals']);
                if($targetGeneral->npc==5){
                    
                }
                else if($targetGeneral->npc>=2 || $maxCity['dev'] >= 95 && $targetGeneral->troop == 0){
                    //유저장은 의도가 있을 것이므로 삽나지 않는 이상 발령 안함!
                    $commandList[] = [['che_발령', [
                        'destGeneralID'=>$targetGeneral->no,
                        'destCityID'=>$targetCity['city']
                    ]], 5];
                }
                
            }
        }

        // 병사있고 쌀있고 후방에 있는 장수
        if($frontCitiesID){
            $workRemain = 3;
            foreach($nationGenerals as $nationGeneral){
                $generalCity = $nationCities[$nationGeneral->city]??null;
                if(!$generalCity){
                    continue;
                }
                if($nationGeneral->crew < 2000){
                    continue;
                }
                if($nationGeneral->rice < 700 * $tech){
                    continue;
                }
                if($generalCity['front']){
                    continue;
                }
                if($nationGeneral->train * $nationGeneral->atmos < 75 * 75){
                    continue;
                }

                if($nationGeneral->troop != 0){
                    continue;
                }
        
                $score = 5;
                if($nationGeneral->npc<2){
                    $score *= 4;
                }
        
                if(Util::randBool(0.3) && $frontImportantCitiesID){
                    $targetCityID = Util::choiceRandom($frontImportantCitiesID);
                }
                else{
                    $targetCityID = Util::choiceRandom($frontCitiesID);
                }
                
                $command = ['che_발령', [
                    'destGeneralID'=>$nationGeneral->no,
                    'destCityID'=>$targetCityID
                ]];

                if($nationGeneral->npc<2 && ($workRemain&2)){
                    $workRemain ^= 2;
                    $commandList[] = [$command, $score];
                }
                else if($nationGeneral->npc>=2 && ($workRemain&1)){
                    $workRemain ^= 1;
                    $commandList[] = [$command, $score];
                }

                if($workRemain <= 0){
                    break;
                }
            }
        }

        //병사 없고 인구없는 전방에 있는 장수
        if($frontCitiesID && $backupCitiesID){
            $workRemain = 3;
            foreach($nationGenerals as $nationGeneral){
                $generalCity = $nationCities[$nationGeneral->city]??null;
                if(!$generalCity){                
                    continue;
                }
                if($nationGeneral->crew >= 1000){
                    continue;
                }
                if($nationGeneral->rice < 700 * $tech){
                    continue;
                }
                if(!$generalCity['front']){
                    continue;
                }
                if($generalCity['pop'] - 33000 > $nationGeneral->leadership){
                    continue;
                }
                if($nationGeneral->troop != 0){
                    continue;
                }
        
                $score = 5;
                if($nationGeneral->npc<2){
                    $score *= 4;
                }
        
                $popTrial = 5;
                for($popTrial = 0; $popTrial < 5; $popTrial++){
                    $targetCity = $nationCities[Util::choiceRandom($backupCitiesID)];
                    if($targetCity['pop'] < 33000 + $nationGeneral->leadership){
                        continue;
                    }
                    if (Util::randBool($targetCity['pop'] / $targetCity['pop_max'])) {
                        break;
                    }
                }
                
                
                $command = ['che_발령', [
                    'destGeneralID'=>$nationGeneral->no,
                    'destCityID'=>$targetCity['city']
                ]];

                if($nationGeneral->npc<2 && ($workRemain&2)){
                    $workRemain ^= 2;
                    $commandList[] = [$command, $score];
                }
                else if($nationGeneral->npc>=2 && ($workRemain&1)){
                    $workRemain ^= 1;
                    $commandList[] = [$command, $score];
                }

                if($workRemain <= 0){
                    break;
                }
            }
        }

        if(!$commandList)return ['휴식', null];
        return Util::choiceRandomUsingWeightPair($commandList);
    }

    public function chooseNeutralTurn():array{

        $general = $this->getGeneralObj();
        $city = $this->city;
        $env = $this->env;

        $db = DB::db();

        $arg = null;

        // 오랑캐는 바로 임관
        if($general->getVar('npc') == 9) {
            $rulerNation = $db->queryFirstField(
                'SELECT nation FROM general WHERE `level`=12 AND npc=9 and nation not in %li ORDER BY RAND() limit 1', 
                $general->getAuxVar('joinedNations')??[0]
            );

            if($rulerNation){
                return ['che_임관', ['destNationID'=>$rulerNation]];
            }
            return ['che_견문', null];
        }

        switch(Util::choiceRandomUsingWeight([
            '임관'=>11.4,
            '거병_견문'=>40,
            '이동'=>20,
            '기타'=>28.6
        ])) {
            //임관
            case '임관':
    
                $available = true;
    
                if($env['startyear']+3 > $env['year']){
                    //초기 임관 기간에서는 국가가 적을수록 임관 시도가 적음
                    $nationCnt = $db->queryFirstField('SELECT count(nation) FROM nation');
                    $notFullNationCnt = $db->queryFirstField('SELECT count(nation) FROM nation WHERE gennum < %i', GameConst::$initialNationGenLimit);
                    if($nationCnt == 0 || $notFullNationCnt == 0){
                        $available = false;
                    }
                    else if(Util::randBool(pow(1 / $nationCnt / pow($notFullNationCnt, 3), 1/4))){
                        //국가가 1개일 경우에는 '임관하지 않음'
                        $available = false;
                    }
                }
    
                if($general->getVar('affinity') == 999 || !$available){
                    $command = 'che_견문'; //견문
                }
                else{
                    //랜임 커맨드 입력.
                    $command = 'che_랜덤임관';
                    $arg = [
                        'destNationIDList'=>[]
                    ];
                }
                break;
            case '거병_견문': //거병이나 견문
                // 초반이면서 능력이 좋은놈 위주로 1.4%확률로 거병
                $prop = Util::randF() * (GameConst::$defaultStatNPCMax + GameConst::$chiefStatMin) / 2;
                $ratio = ($general->getVar('leadership') + $general->getVar('strength') + $general->getVar('intel')) / 3;
                if($env['startyear']+2 > $env['year'] && $prop < $ratio && Util::randBool(0.014) && $general->getVar('makelimit') == 0) {
                    //거병
                    $command = 'che_거병';
                } else {
                    //견문
                    $command = 'che_견문';
                }
                break;
            case '이동': //이동
                
                $paths = array_keys(CityConst::byID($city['city'])->path);
                $command = 'che_이동';
                $arg = ['destCityID'=>Util::choiceRandom($paths)];
                break;
            default:
                $command = 'che_견문';
                break;
        }
        return [$command, $arg];
    }

    protected function chooseEndOfNPCTurn():array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;

        if($general->getVar('gold') + $general->getVar('rice') == 0){
            return ['che_물자조달', null];
        }

        [$baseArmGold, $baseArmRice] = $this->getBaseArmCost();

        if($this->dipState == self::d전쟁 &&
            $general->getVar('killturn') > 2 &&
            $general->getVar('gold') >= $baseArmGold / 3 &&
            $general->getVar('rice') >= $baseArmRice &&
            $general->getVar('crew') >= $general->getLeadership(false) / 3
        ){
            //사망 직전 마지막 불꽃
            $trainAndAtmos = $general->getVar('train') + $general->getVar('atmos');
            if(
                $trainAndAtmos >= 180 &&
                $city['front'] >= 2 &&
                $general->getVar('rice') >= $baseArmRice &&
                $nation['war'] == 0
            ){
                return $this->processAttack();
            }

            if(180 >= $trainAndAtmos && $trainAndAtmos >= 160){
                if($general->getVar('train') < 80){
                    return ['che_훈련', null];
                }
                else{
                    return ['che_사기진작', null];
                }
            }
        }

        if($general->getVar('gold') >= $general->getVar('rice')){
            return ['che_헌납', ['isGold'=>true, 'amount'=>GameConst::$maxResourceActionAmount]];
        }
        else{
            return ['che_헌납', ['isGold'=>false, 'amount'=>GameConst::$maxResourceActionAmount]];
        }
    }

    public function chooseGeneralTurn($command, $arg):array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $cityID = $general->getCityID();
        $nationID = $general->getNationID();

        $genType = $this->genType;
        $leadership = $this->leadership;
        $strength = $this->strength;
        $intel = $this->intel;

        $startYear = $env['startyear'];
        $year = $env['year'];
        $month = $env['month'];

        $db = DB::db();

        if($general->getVar('npc') == 5){
            if($nationID == 0 && $general->getVar('killturn') > 1){
                $command = '휴식'; //휴식
                $arg = null;
                $general->setVar('killturn', 1);
            }
            else if($general->getVar('level') == 12){
                $command = 'che_선양';
                $arg = [
                    'destGeneralID'=> $db->queryFirstField('SELECT `no` FROM general WHERE nation = %i AND npc != 5 ORDER BY RAND() LIMIT 1', $general->getNationID())
                ];
            }
            else{
                $command = 'che_집합'; //집합
                $arg = [];
                $general->setVar('killturn', rand(70,75));
                //NOTE: 부대 편성에 보여야 하므로 이것만 DB에 직접 접근함.
                $db->update('general_turn', [
                    'action'=>'che_집합',
                    'arg'=>'{}',
                    'brief'=>'집합'
                ], 'general_ID=%i AND turn_idx < 6', $general->getID());
            }
    
            return [$command, $arg];
        }

        //특별 메세지 있는 경우 출력 하루 4번
        $term = $env['turnterm'];
        if($general->getVar('npcmsg') && Util::randBool($term / (6*60))) {
            $src = new MessageTarget(
                $general->getID(), 
                $general->getVar('name'),
                $general->getVar('nation'),
                $nation['name'],
                $nation['color'],
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

        if($command && $command != '휴식'){
            return [$command, $arg];
        }


        if($general->getVar('level') == 0){
            return $this->chooseNeutralTurn();
        }

        $techCost = getTechCost($nation['tech']);

        if($general->getVar('defence_train') < 80) {
            $general->setVar('defence_train', 80);
        }

        if($general->getVar('level') == 12){
            $turn = $this->processLordTurn();
            if($turn !== null){
                return $turn;
            }
        }

        if($general->getVar('killturn') < 5){
            return $this->chooseEndOfNPCTurn();
        }

        if($general->getVar('injury') > 10){
            return ['che_요양', null];
        }

        if($nation['level'] == 0){
            //아직 건국을 안했다.
            if($startYear + 3 <= $year){
                return ['che_하야', null];
            }

            if(Util::randBool(0.2)){
                return ['che_견문', null];
            }
            else{
                return ['che_물자조달', null];
            }
        }

        if($city['nation'] != $nationID || !$city['supply']){
            return ['che_귀환', null];
        }

        [$baseArmGold, $baseArmRice] = $this->getBaseArmCost();

        if($nation['rice'] < 2000){
            if(($genType & self::t통솔장) && $general->getVar('rice') > $baseArmRice){
                return ['che_헌납', ['isGold'=>false, 'amount'=>Util::toInt(($general->getVar('rice') - $baseArmRice) / 2)]];
            }
            else if(!($genType & self::t통솔장)){
                return ['che_헌납', ['isGold'=>false, 'amount'=>Util::toInt($general->getVar('rice') / 2)]];
            }
        }

        if($genType & self::t통솔장){
            $warTurn = $this->processWar();
            if($warTurn !== null){
                return $warTurn;
            }

            if($general->getVar('rice') < $baseArmRice && $general->getVar('gold') >= $baseArmGold * 3){
                return [
                    'che_군량매매',
                    [
                        'buyRice'=>true,
                        'amount'=> Util::toInt($general->getVar('gold') - $baseArmGold)
                    ]
                ];
            }
            if($general->getVar('gold') < $baseArmGold && $general->getVar('gold') >= $baseArmRice * 3){
                return [
                    'che_군량매매', 
                    [
                        'buyRice'=>false,
                        'amount'=> Util::toInt($general->getVar('rice') - $baseArmRice)
                    ]
                ];
            }
        }
        else{
            if($general->getVar('rice') < $this->baseDevelCost && $general->getVar('gold') >= $this->baseDevelCost * 3){
                return [
                    'che_군량매매',
                    [
                        'buyRice'=>true,
                        'amount'=> Util::toInt($general->getVar('gold') - $this->baseDevelCost)
                    ]
                ];
            }
            if($general->getVar('gold') < $this->baseDevelCost && $general->getVar('gold') >= $this->baseDevelCost * 3){
                return [
                    'che_군량매매',
                    [
                        'buyRice'=>false,
                        'amount'=> Util::toInt($general->getVar('rice') - $this->baseDevelCost)
                    ]
                ];
            }
        }

        $cityFull = false;
        $developTurn = $this->chooseDevelopTurn($cityFull);

        if($cityFull && Util::randBool(0.2)){
            $moveRawCities = $db->query('SELECT city,front,(pop/10+agri+comm+secu+def+wall)/(pop_max/10+agri_max+comm_max+secu_max+def_max+wall_max)*100 as dev, officer3 FROM city WHERE nation=%i', $nationID);

            $moveCities = [];
            foreach($moveRawCities as $moveCity){
                $moveCityID = $moveCity['city'];
                if($moveCity['dev'] > 99){
                    continue;
                }
                $score = 1 / $moveCity['dev'];
                if($moveCity['officer3']){
                    $score *= 1.3;
                }
                $moveCities[$moveCityID] = $score;

            }
            if($moveCities){
                return ['che_NPC능동', [
                    'optionText'=>'순간이동',
                    'destCityID'=>Util::choiceRandomUsingWeight($moveCities),
                ]];
            }
            
        }

        return $developTurn;

    }

    protected function getBaseArmCost(){
        //총 통솔의 절반을 징병하는 것을 기준으로 함
        $general = $this->getGeneralObj();
        $tech = $this->nation['tech'];
        $crewType = $general->getCrewTypeObj();
        $baseArmCost = $crewType->costWithTech(
            $tech,
            $general->getLeadership(false) * 50
        );//기본 병종
        $baseArmCost = $general->onCalcDomestic('징병', 'cost', $baseArmCost);
        $baseArmRice = $general->getLeadership(false) / 2 * $crewType->rice * getTechCost($tech);
        if($general->getRankVar('deathcrew') > 500 && $general->getRankVar('killcrew') > 500){
            $baseArmRice *= $general->getRankVar('killcrew') / $general->getRankVar('deathcrew');
        }

        return [$baseArmCost, $baseArmRice];
    }

    protected function processWar():?array{

        $db = DB::db();

        $general = $this->getGeneralObj();
        if(!$this->attackable && $this->dipState == self::d평화){
            if($this->dipState == self::d평화 && $general->getVar('crew')>=1000 && Util::randBool(0.25)){
                return ['che_소집해제', null];
            }

            return null;
        }

        $city = $this->city;
        $nation = $this->nation;
        $cityID = $city['city'];
        $nationID = $nation['nation'];
        $env = $this->env;

        [$baseArmGold, $baseArmRice] = $this->getBaseArmCost();
        
        if($general->getVar('rice') <= $baseArmRice){
            return null;
        }

        if(
            ($city['front'] > 0 && $city['trust'] < 60) || 
            ($city['front'] == 0 && $city['trust'] < 95)
        ){
            return ['che_주민선정', null];
        }

        if($general->getVar('crew') < 1000){
            if ($general->getVar('gold') <= $baseArmGold) {
               //반징도 불가? 내정
               return null;
            }

            $sumLeadershipInCity = $db->queryFirstField('SELECT sum(leadership) FROM general WHERE nation = %i AND city = %i AND leadership > 40', $nationID, $cityID);
            if(
                $sumLeadershipInCity > 0 &&
                $city['pop'] > 30000 + $general->getLeadership(false) * 100 * 1.3 &&
                Util::randBool(($city['pop'] - 30000) / $sumLeadershipInCity * 100)
            ){
                return $this->chooseRecruitCrewType();
            }

            $recruitableCityList = $db->queryAllLists(
                'SELECT city, (pop - 30000) as relPop FROM city WHERE nation = %i AND pop > %i AND supply = 1',
                $nationID,
                30000 + $general->getLeadership(false) * 100
            );
            
            if(!$recruitableCityList){
                //징병 가능한 도시가 없구려
                return ['che_정착장려', null];
            }

            return ['che_NPC능동', [
                'optionText'=>'순간이동',
                'destCityID'=>Util::choiceRandomUsingWeightPair($recruitableCityList),
            ]];
        }

        if(
            $general->getVar('train') >= 90 &&
            $general->getVar('atmos') >= 90
        ){
            //출병 가능!

            if(
                $this->attackable &&
                $env['year'] >= $env['startyear'] + 3 &&
                $city['front'] >= 2 &&
                $nation['war'] == 0
            ){
                return $this->processAttack();
            }

            if($city['front'] > 0){
                //전방에 훈사까지 완료되어있으면 내정을 해야..
                return null;
            }

            $frontCities = $db->query('SELECT city, front, officer4 FROM city WHERE nation=%i AND front > 0 AND supply = 1', $nationID);

            if(!$frontCities){
                //접경이 아직 없음
                return null;
            }

            $nearCities = [];
            $attackableCities = [];
            foreach($frontCities as $frontCity){
                $frontCityID = $frontCity['city'];

                $score = 0.2;
                if($frontCity['front'] > 1 && $frontCity['officer4']){
                    $score += 3;
                }
                $attackableCities[$frontCityID] = $score;
                
                foreach(array_keys(CityConst::byID($frontCityID)->path) as $nearCity){
                    if(!key_exists($nearCity, $nearCities)){
                        $nearCities[$nearCity] = [$frontCityID];
                    }
                    else{
                        $nearCities[$nearCity][] = $frontCityID;
                    }
                }
            }           
            
            foreach($db->query(
                'SELECT city FROM city WHERE nation IN %li AND city IN %li', 
                array_keys($this->warTargetNation),
                array_keys($nearCities)
            ) as $targetCity){
                $targetCityID = $targetCity['city'];
                foreach($nearCities[$targetCityID] as $attackableCity){
                    $attackableCities[$attackableCity] += 1;
                    
                }
            }

            if($attackableCities){
                return ['che_NPC능동', [
                    'optionText'=>'순간이동',
                    'destCityID'=>Util::choiceRandomUsingWeight($attackableCities),
                ]];
            }
            
        }

        if($general->getVar('train') < 90){
            $turnObj = buildGeneralCommandClass('che_훈련', $general, $env, null);
            [$reqGold, $reqRice] = $turnObj->getCost();
            if($general->getVar('gold') >= $reqGold && $general->getVar('rice') >= $reqRice){
                return ['che_훈련', null];
            }
        }

        if($general->getVar('atmos') < 90 ){
            $turnObj = buildGeneralCommandClass('che_사기진작', $general, $env, null);
            [$reqGold, $reqRice] = $turnObj->getCost();
            if($general->getVar('gold') >= $reqGold && $general->getVar('rice') >= $reqRice){
                return ['che_사기진작', null];
            }
        }


        //훈사할 금조차도 없다고..? 아마 쌀을 팔아야 할 것.
        return null;
    }

    protected function processAttack():array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;

        $cityID = $city['city'];
        $nationID = $nation['nation'];

        $db = DB::db();

        if($city['front'] <= 1){
            throw new \RuntimeException('출병 불가'.$cityID);
        }

        $attackableNations = [];
        foreach($this->warTargetNation as $targetNationID=>$state){
            if($state == 1){
                continue;
            }
            $attackableNations[] = $targetNationID;
        }
        $nearCities = array_keys(CityConst::byID($cityID)->path);
        
        $attackableCities = $db->queryFirstColumn(
            'SELECT city FROM city WHERE nation IN %li AND city IN %li',
            $attackableNations,
            $nearCities
        );

        if(count($attackableCities) == 0){
            throw new \RuntimeException('출병 불가'.$cityID.var_export($attackableNations, true).var_export($nearCities, true));
        }

        return ['che_출병', ['destCityID'=>Util::choiceRandom($attackableCities)]];
    }

    protected function proceessNeutralLordTurn():array{

        $db = DB::db();

        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $startYear = $env['startyear'];
        $year = $env['year'];
        $month = $env['month'];

        if($startYear+2 <= $year){
            return ['che_해산', null];
        }

        if($city['nation'] == 0 && ($city['level'] == 5 || $city['level'] == 6)) {
            $nationType = Util::choiceRandom(GameConst::$availableNationType);
            $nationColor = Util::choiceRandom(array_keys(GetNationColors()));
            return ['che_건국', [
                'nationName'=>"㉿".mb_substr($general->getName(), 1),
                'nationType'=>$nationType,
                'colorType'=>$nationColor
            ]];

        }

        //모든 공백지 조사
        $lordCities = $db->queryFirstColumn('SELECT city.city FROM general LEFT JOIN city ON general.city = city.city WHERE general.level = 12 AND city.nation != 0');
        $nationCities = $db->queryFirstColumn('SELECT city FROM city WHERE nation != 0');

        $occupiedCities = [];
        foreach($lordCities as $tCityId){
            $occupiedCities[$tCityId] = 2;
        }
        foreach($nationCities as $tCityId){
            $occupiedCities[$tCityId] = 1;
        }

        $targetCity = [];
        //NOTE: 최단 거리가 현재 도시에서 '어떻게 가야' 가장 짧은지 알 수가 없으므로, 한칸 간 다음 계산하기로
        foreach(array_keys(CityConst::byID($general->getVar('city'))->path) as $nearCityID){
            if(CityConst::byID($nearCityID)->level < 4){
                $targetCity[$nearCityID] = 0.5;
            }
            else if(!key_exists($nearCityID, $occupiedCities)){
                $targetCity[$nearCityID] = 2;
            }
            else{
                $targetCity[$nearCityID] = 0;
            }
            
            $distanceFrom = searchDistance($nearCityID, 4, true);
            foreach($distanceFrom as $distance => $distCities){
                foreach($distCities as $distCity){
                    if(key_exists($distCity, $occupiedCities)){
                        continue;
                    }
                    if(CityConst::byID($distCity)->level < 4){
                        continue;
                    }

                    $targetCity[$nearCityID] += 1 / (2 ** $distance);
                }
            }
        }
        
        return ['che_이동', ['destCityID'=>Util::choiceRandomUsingWeight($targetCity)]];
    }

    protected function processLordTurn():?array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $year = $env['year'];
        $month = $env['month'];

        if($general->getVar('npc') == 9 && $this->dipState == self::d평화 && !$this->attackable){
            if($nation['level'] == 0){
                return ['che_해산', null];
            }
            else{
                return ['che_방랑', null];
            }
        }

        if(in_array($month, [1,4,7,10])){
            $this->calcPromotion();
        }
        else if($month == 12){
            $this->calcTexRate();
            $this->calcGoldBillRate();
        }
        else if($month == 6){
            $this->calcTexRate();
            $this->calcRiceBillRate();
        }

        if($nation['level'] == 0){
            return $this->proceessNeutralLordTurn();
        }

        
        
        return null;
    }

    protected function getNationDevelopedRate(){
        if($this->devRate !== null){
            return $this->devRate;
        }

        $db = DB::db();
        $nationID = $this->nation['nation'];

        $this->devRate = $db->queryFirstRow(
            'SELECT sum(pop)/sum(pop_max) as pop_p,(sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(agri_max)+sum(comm_max)+sum(secu_max)+sum(def_max)+sum(wall_max)) as all_p from city where nation=%i',
            $nationID
        );
        return $this->devRate;
    }

    protected function findWarTarget():?int{

        $db = DB::db();

        $nation = $this->nation;
        $nationID = $nation['nation'];
        SetNationFront($nationID);

        $frontCount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i AND front>0', $nationID);
        if($frontCount > 0){
            return null;
        }

        $devRate = $this->getNationDevelopedRate();
        if(($devRate['pop_p'] + $devRate['all_p']) / 2 < 0.8){
            return null;
        }

        $nations = [];
        foreach ($db->queryAllLists('SELECT nation, power FROM nation WHERE level>0') as [$destNationID, $destNationPower]) {
            if(!isNeighbor($nationID, $destNationID)){
                continue;
            }
            $nations[$destNationID] = 1/sqrt($destNationPower+1);
        }
        if(!$nations){
            return null;
        }
        return Util::choiceRandomUsingWeight($nations);
    }

    protected function calcPromotion(){
        $db = DB::db();

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $minChiefLevel = getNationChiefLevel($nation['level']);

        $minKillturn = $this->env['killturn'] - Util::toInt(30 / $this->env['turnterm']);
        $chiefCandidate = [];

        //이 함수를 부르는건 군주 AI이므로, 군주는 세지 않아도 됨
        $userChief = []; 

        $db->update('general', [
            'permission'=>'ambassador',
        ], 'nation=%i AND npc < 2 AND level > 4', $nationID);

        foreach($db->query(
            'SELECT no, npc, level, killturn FROM general WHERE nation = %i AND 12 > level AND level > 4', $nationID
        ) as $chief){

            if($chief['npc'] < 2 && $chief['killturn'] < $minKillturn ){
                $chiefCandidate[$chief['level']] = $chief['no'];
                $userChief[$chief['no']] = $chief['level'];
            }
        }

        $db->update('general', [
            'level'=>1
        ], 'level < 12 AND level > 4 AND nation = %i', $nationID);

        $maxBelong = $db->queryFirstField('SELECT max(belong) FROM `general` WHERE nation=%i', $nationID);
        $maxBelong = min($maxBelong - 1, 3);
        
        if(!$userChief){
            $candUserChief = $db->queryFirstField(
                'SELECT no FROM general WHERE nation = %i AND level = 1 AND killturn > %i AND npc < 2 AND belong >= %i ORDER BY leadership DESC LIMIT 1',
                $nationID,
                $minKillturn,
                $maxBelong
            );
            if($candUserChief){
                $userChief[$candUserChief] = 11;
                $chiefCandidate[11] = $candUserChief;
            }
        }

        $promoted = $userChief;

        if(!key_exists(11, $chiefCandidate)){
            $candChiefHead = $db->queryFirstField(
                'SELECT no FROM general WHERE nation = %i AND level = 1 AND npc >= 2 AND belong >= %i ORDER BY leadership DESC LIMIT 1',
                $nationID,
                $maxBelong
            );
            if($candChiefHead){
                $chiefCandidate[11] = $candChiefHead;
                $promoted[$candChiefHead] = 11;
            }
        }

        if($minChiefLevel < 11){
            //무장 수뇌 후보
            $candChiefStrength = $db->queryFirstColumn(
                'SELECT no FROM general WHERE nation = %i AND strength >= %i AND level = 1 AND belong >= %i ORDER BY strength DESC LIMIT %i',
                $nationID,
                GameConst::$chiefStatMin,
                $maxBelong, 12 - $minChiefLevel
            );
            //지장 수뇌 후보
            $candChiefIntel = $db->queryFirstColumn(
                'SELECT no FROM general WHERE nation = %i AND intel >= %i AND level = 1 AND belong >= %i ORDER BY intel DESC LIMIT %i',
                $nationID,
                GameConst::$chiefStatMin,
                $maxBelong,
                12 - $minChiefLevel
            );
            //무력, 지력이 모두 높은 장수를 고려하여..
            
            $iterCandChiefStrength = new \ArrayIterator($candChiefStrength);
            $iterCandChiefIntel = new \ArrayIterator($candChiefIntel);

            foreach(range(10, $minChiefLevel, -1) as $chiefLevel){
                if(key_exists($chiefLevel, $chiefCandidate)){
                    continue;
                }

                /** @var \ArrayIterator $iterCurrentType */
                $iterCurrentType = ($chiefLevel % 2 == 0)?$iterCandChiefStrength:$iterCandChiefIntel;
                $candidate = $iterCurrentType->current();

                while(key_exists($candidate, $promoted)){
                    $iterCurrentType->next();
                    if(!$iterCurrentType->valid()){
                        break;
                    }
                    $candidate = $iterCurrentType->current();
                }

                if($candidate){
                    $chiefCandidate[$chiefLevel] = $candidate;
                    $promoted[$candidate] = $chiefLevel;
                }
                
            }

            foreach($chiefCandidate as $chiefLevel=>$chiefID){
                $db->update('general', [
                    'level'=>$chiefLevel
                ], 'no=%i',$chiefID);
            }
        }
        
    }

    protected function calcTexRate():int{
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        //도시
        $cityCount = $db->queryFirstField('SELECT count(*) FROM city WHERE nation = %i', $nationID);

        if($cityCount == 0) {
            $db->update('nation', [
                'war'=>0,
                'rate'=>15
            ], 'nation=%i', $nationID);
            return 15;
        } else {
            $devRate = $this->getNationDevelopedRate();

            $avg = ($devRate['pop_p'] + $devRate['all_p']) / 2;

            if($avg > 0.95) $rate = 25;
            elseif($avg > 0.70) $rate = 20;
            elseif($avg > 0.50) $rate = 15;
            else $rate = 10;

            $db->update('nation', [
                'war'=>0,
                'rate'=>$rate
            ], 'nation=%i', $nationID);
            return $rate;
        }
    }
    
    protected function calcGoldBillRate():int{
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        $cityList = $db->query('SELECT * FROM city WHERE nation=%i', $nationID);

        if(!$cityList){
            return 20;
        }

        $dedicationList = $db->query('SELECT dedication FROM general WHERE nation=%i AND npc!=5', $nationID);

        $goldIncome  = getGoldIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $warIncome  = getWarGoldIncome($nation['type'], $cityList);
        $income = $goldIncome + $warIncome;

        $outcome = getOutcome(100, $dedicationList);
    
        $bill = intval($income / $outcome * 80); // 수입의 80% 만 지급
    
        if($bill < 20)  { $bill = 20; }
        if($bill > 200) { $bill = 200; }
    
        $db->update('nation', [
            'bill'=>$bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }

    protected function calcRiceBillRate():int{
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        $cityList = $db->query('SELECT * FROM city WHERE nation=%i', $nationID);

        if(!$cityList){
            return 20;
        }

        $dedicationList = $db->query('SELECT dedication FROM general WHERE nation=%i AND npc!=5', $nationID);

        $riceIncome = getRiceIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $wallIncome = getWallIncome($nation['nation'], $nation['level'], $nation['rate'], $nation['capital'], $nation['type'], $cityList);
        $income = $riceIncome + $wallIncome;

        $outcome = getOutcome(100, $dedicationList);
    
        $bill = intval($income / $outcome * 80); // 수입의 80% 만 지급

        if($bill < 20)  { $bill = 20; }
        if($bill > 200) { $bill = 200; }

        $db->update('nation', [
            'bill'=>$bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }
}