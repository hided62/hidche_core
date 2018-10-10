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

    protected $leadership;
    protected $power;
    protected $intel;

    protected $dipState;
    protected $attackable;
    protected $onGame;

    protected $devRate = null;

    const t무장 = 1;
    const t지장 = 2;
    const t통솔장 = 4;

    const d평화 = 0;
    const d선포 = 1;
    const d징병 = 2;
    const d직전 = 3;
    const d전쟁 = 4;

    public function __construct(General $general){
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $this->env = $gameStor->getValues(['startyear','year','month','turnterm','killturn','scenario','gold_rate','rice_rate']);
        $this->general = $general;
        if($general->getRawCity() === null){
            $city = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $general->getCityID());
            $general->setRawCity($city);
        }
        $this->city = $general->getRawCity();
        $this->nation = $db->queryFirstRow(
            'SELECT nation,level,tech,gold,rice,rate,type,color,name,war FROM nation WHERE nation = %i',
            $generalObj->getNationID()
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

        $this->calcGenType();

        $this->leadership = $general->getLeadership(true, true, true, true);
        $this->power = $general->getPower(true, true, true, true);
        $this->intel = $general->getIntel(true, true, true, true);

        $this->calcDiplomacyState();

    }

    public function getGeneralObj():General{
        return $this->general;
    }

    protected function calcGenType(){
        $leadership = $this->leadership;
        $power = $this->power;
        $intel = $this->intel;

        //무장
        if ($power >= $intel) {
            $genType = self::t무장;
            if ($intel >= $power * 0.8) {  //무지장
                if(Util::randBool($intel / $power / 2)){
                    $genType |= self::t지장;
                }
                
            }
            //지장
        } else {
            $genType = self::t지장;
            if ($power >= $intel * 0.8 && Util::randBol(0.2)) {  //지무장
                if(Util::randBool($power / $intel / 2)){
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

        $frontStatus = $db->queryFirstField('SELECT max(front) FROM city WHERE nation=%i AND supply=1', $nationID);
        // 공격가능도시 있으면
        $this->attackable = ($frontStatus !== null)?$frontStatus:false;

    
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

        if($env['startyear']+2 > $env['year'] || ($env['startyear']+2 == $env['year'] && $env['month'] < 5)) {
            $this->onGame = true;
        } else {
            $this->onGame = false;
        }

        if($this->attackable){
            //전쟁으로 인한 attackable인가?
            $onWar = $db->queryFirstField('SELECT you FROM diplomacy WHERE me = %i AND state=0 LIMIT 1', $nationID) !== null;
            if($onWar){
                $this->dipState = self::d전쟁;
            }
        }
    }

    protected function chooseDevelopTurn(bool &$cityFull):?array{
        $general = $this->general;
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $genType = $this->genType;
        $leadership = $this->leadership;
        $power = $this->power;
        $intel = $this->intel;

        $cityFull = false;

        $develRate = [
            'trust'=>$city['trust'],
            'pop'=>$city['pop']/$city['pop2'],
            'agri'=>$city['agri']/$city['agri2'],
            'comm'=>$city['comm']/$city['comm2'],
            'secu'=>$city['secu']/$city['secu2'],
            'def'=>$city['def']/$city['def2'],
            'wall'=>$city['wall']/$city['wall2'],
        ];

        // 우선 선정
        if($develRate['trust'] < 0.95 && Util::randBool($leadership / 40)){
            return ['che_선정', null];
        }

        $commandList = [];

        if($genType & self::t무장){
            if($develRate['secu'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_치안강화', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_치안강화'] = $power / 3;
                    if(in_array($city['front'], [1,3])){
                        $commandList['che_치안강화'] /= 2;
                    }
                }
            }
            if($develRate['def'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_수비강화', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_수비강화'] = $power / 3;
                }
            }
            if($develRate['wall'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_성벽보수'] = $power / 3;
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
                    $commandList['che_기술연구'] = $intel / 4;
                }
            }
        }
        if($genType & self::t통솔장){
            if($develRate['trust'] < 1){
                $commandObj = buildGeneralCommandClass('che_주민선정', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_주민선정'] = $leadership / 2;
                }
            }
            if($develRate['pop'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_정착장려', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList['che_정착장려'] = $leadership / 2;
                }
            }
        }

        if(!$commandList){
            $cityFull = true;
        }

        $genCount = $db->queryFirstField('SELECT count(no) FROM general');
        $commandList['che_인재탐색'] = 500 / $genCount * 10;
        if(in_array($city['front'], [1,3])){
            $commandList['che_인재탐색'] /= 2;
        }

        $commandList['che_물자조달'] = (
            (GameConst::$minNationalGold + GameConst::$minNationalRice + 10000) / 
            Util::valueFit($nation['gold'] + $nation['rice'], 1000)
        ) * 10;

        return [Util::choiceRandomUsingWeight($commandList), null];
    }

    public function chooseRecruitCrewType():array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $genType = $this->genType;
        $leadership = $this->leadership;
        $power = $this->power;
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
                    $score = $dex[GameConst::T_FOOTMAN] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $score];
                }
            }
            foreach(GameUnitConst::byType(GameUnitConst::T_ARCHER) as $crewtype){
                if($crewtype->isValid($cities, $regions, $relYear, $tech)){
                    $score = $dex[GameConst::T_ARCHER] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $score];
                }
            }
            foreach(GameUnitConst::byType(GameUnitConst::T_CAVALRY) as $crewtype){
                if($crewtype->isValid($cities, $regions, $relYear, $tech)){
                    $score = $dex[GameConst::T_CAVALRY] * $crewtype->pickScore($tech);
                    $types[] = [$crewtype->id, $dex[GameConst::T_CAVALRY] + 500];
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
                    $score = $dex[GameConst::T_WIZARD] * $crewtype->pickScore($tech);
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

        $gold -= $obj훈련->getCost()[0] * 2;
        $gold -= $obj사기진작->getCost()[0] * 2;

        $cost = getCost($type) * getTechCost($tech);
        $cost = $general->onCalcDomestic('징병', 'cost', $cost);
    
        $crew = intdiv($gold, $cost);
        if($leader < $crew) { $crew = $leader; }
        $arg = [
            'crewType'=>$type,
            'amountCrew'=>$crew
        ];
        return ['che_징병', $arg];
    }

    public function chooseNationTurn($command, $arg):array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $cityID = $general->getCityID();
        $nationID = $general->getNationID();

        $genType = $this->genType;
        $leadership = $this->leadership;
        $power = $this->power;
        $intel = $this->intel;

        $db = DB::db();

        if($general->getVar('npc') == 5){
            return [$command, $arg];
        }

        if($command && $command != '휴식'){
            return [$command, $arg];
        }

        if($general->getVar('level') == 12 && $this->dipState == self::d평화 && !$this->attackable){
            $targetNationID = $this->findWarTarget();
            if($targetNationID !== null){
                return ['che_선전포고', ['destNationID'=>$targetNationID]];
            }
        }

        return [$command, $arg];
    }

    public function chooseNeutralTurn():array{

        $general = $this->getGeneralObj();
        $env = $this->env;

        $db = DB::db();


        // 오랑캐는 바로 임관
        if($general->getVar('npc') == 9) {
            $rulerNation = $db->queryFirstField(
                'SELECT nation FROM general WHERE `level`=12 AND npc=9 and nation not in %li ORDER BY RAND() limit 1', 
                Json::decode($general->getVar('nations'))
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
                    $command = 'che_임관';
                    $arg = ['destNationID'=>99];
                }
                break;
            case '거병_견문': //거병이나 견문
                // 초반이면서 능력이 좋은놈 위주로 1.4%확률로 거병
                $prop = Util::randF() * (GameConst::$defaultStatNPCMax + GameConst::$chiefStatMin) / 2;
                $ratio = ($general->getVar('leader') + $general->getVar('power') + $general->getVar('intel')) / 3;
                if($env['startyear']+2 > $env['year'] && $prop < $ratio && Util::randBool(0.014) && $general->getVar('makelimit') == 0) {
                    //거병
                    $command = 'che_거병';
                } else {
                    //견문
                    $command = 'che_견문';
                }
                break;
            case '이동': //이동
                
                $paths = array_keys(CityConst::byID($city['city'])::$path);
                $command = 'che_이동';
                $arg = ['destCityID'=>Util::choiceRandom($paths)];
                break;
            default:
                $command = 'che_견문';
                break;
        }
        return [$command, $arg];
    }

    protected function chooseEndOfNPCTurn($baseArmRice):array{
        $general = $this->getGeneralObj();
        $city = $this->city;
        if($general->getVar('gold') + $general->getVar('rice') == 0){
            return ['che_물자조달', null];
        }
        if($this->dipState == self::d전쟁 &&
            $general->getVar('killturn') > 2 &&
            $general->getVar('rice') >= $baseArmRice &&
            $general->getVar('crew') >= $general->getLeadership(false) / 3
        ){
            //사망 직전 마지막 불꽃
            $trainAndAtmos = $general->getVar('train') + $general->getVar('atmos');
            if(
                $trainAndAtmos >= 180 &&
                $city['front'] >= 2 &&
                $general->getVar('rice') >= $baseArmRice

            ){
                return $this->attackCity();
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
            return ['che_헌납', ['isGold'=>true, 'amount'=>100]];
        }
        else{
            return ['che_헌납', ['isGold'=>false, 'amount'=>100]];
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
        $power = $this->power;
        $intel = $this->intel;

        $startYear = $env['startyear'];
        $year = $env['year'];
        $month = $env['month'];

        $db = DB::db();

        if($general->getVar('npc') == 5){
            if($nationID == 0 && $general->getVar('killturn') > 1){
                $command = '휴식'; //휴식
                $arg = null;
                $generalObj->setVar('killturn', 1);
            }
            else{
                $command = 'che_집합'; //집합
                $arg = [];
                $generalObj->setVar('killturn', rand(70,75));
                //NOTE: 부대 편성에 보여야 하므로 이것만 DB에 직접 접근함.
                $db->update('general_turn', [
                    'action'=>'che_집합',
                    'arg'=>'{}'
                ], 'general_ID=%i AND turn_idx < 6', $generalID);
            }
    
            return [$command, $arg];
        }

        //특별 메세지 있는 경우 출력 하루 4번
        $term = $env['turnterm'];
        if($general->getVar('npcmsg') && Util::randBool($term / (6*60))) {
            $src = new MessageTarget(
                $generalID, 
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

        $baseDevelCost = $env['develcost'] * 12;

        $techCost = getTechCost($nation['tech']);
        $baseArmCost = 10 * GameUnitConst::byID(GameUnitConst::DEFAULT_CREWTYPE)->costWithTech($nation['tech']);//기본 병종 1000기
        $baseArmCost = $general->onCalcDomestic('징병', 'cost', $baseArmCost);

        if($general->getVar('atmos') >= 90 && $general->getVar('train') >= 90) {
            if($general->getVar('mode') == 0) {
                $general->setVar('mode', 1);
            }
        } else {
            if($general->getVar('mode') == 1) {
                $general->setVar('mode', 0);
            }
        }

        if($general->getVar('level') == 12){
            $turn = $this->processLordTurn();
            if($turn !== null){
                return $turn;
            }
        }

        if($general->getVar('killturn') < 5){
            return $this->chooseEndOfNPCTurn($baseArmCost);
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

        if($nation['rice'] < 2000){
            if(($genType & self::t통솔장) && $general->getVar('rice') > $baseArmCost){
                return ['che_헌납', ['isGold'=>false, 'amount'=>intdiv(($general->getVar('rice') - $baseArmCost) / 2, 100)]];
            }
            else if(!($genType & self::t통솔장)){
                return ['che_헌납', ['isGold'=>false, 'amount'=>intdiv($general->getVar('rice') / 2, 100)]];
            }
        }

        if($genType & self::t통솔장){
            $warTurn = $this->processWar();
            if($warTurn !== null){
                return $warTurn;
            }
        }
        
        
    }

    protected function processWar():?array{
        if(!$this->attackable && $this->dipState == self::d평화){
            return null;
        }

        $general = $this->getGeneralObj();
        $city = $this->city;
        $nation = $this->nation;
        $env = $this->env;

        $baseArmCost = GameUnitConst::byID(GameUnitConst::DEFAULT_CREWTYPE)->costWithTech(
            $nation['tech'],
            $general->getLeadership(false) / 2
        );//기본 병종
        $baseArmCost = $general->onCalcDomestic('징병', 'cost', $baseArmCost);

        $baseArmRice = GameUnitConst::byID(GameUnitConst::DEFAULT_CREWTYPE)->costWithTech(
            $nation['tech'],
            $general->getLeadership(false) / 2
        );

        $baseDevelCost = $env['develcost'] * 12;
        if($general->getVar('rice') <= $baseArmRice){
            return null;
        }

        if(
            ($city['front'] > 0 && $city['trust'] < 60) || 
            ($city['front'] == 0 && $city['trust'] < 95)
        ){
            return ['che_주민선정', null];
        }

        if(
            $general->getVar('crew') >= 1000 &&
            $general->getVar('train') >= 90 &&
            $general->getVar('atmos') >= 90
        ){

            if(
                $this->attackable &&
                $env['year'] >= $env['startyear'] + 3 &&
                $city['front'] >= 2
            ){
                return $this->processAttack();
            }

            //TODO: 전방으로
        }

        

        if($general->getVar('crew') >= 1000){
            if($general->getVar('train') < 90){
                $turnObj = buildGeneralCommandClass('che_훈련', $general, $env, null);
                [$reqGold, $reqRice] = $turnObj->getCost();
                if($general->getVar('gold') >= $reqGold && $general->getVar('rice') >= $reqRice){
                    return ['che_훈련', null];
                }
            }

            if($general->getVar('rice') < 90){
                $turnObj = buildGeneralCommandClass('che_사기진작', $general, $env, null);
                [$reqGold, $reqRice] = $turnObj->getCost();
                if($general->getVar('gold') >= $reqGold && $general->getVar('rice') >= $reqRice){
                    return ['che_사기진작', null];
                }
            }
        }

        if($general->getVar('gold') <= $baseArmCost){
            return null;
        }

        $recruitCommand = $this->chooseRecruitCrewType();
        //TODO: 징병 가능한 도시인가? 불가능하다면?
        return $recruitCommand;
    }

    protected function processAttack():array{

    }

    protected function proceessNeutralLordTurn():array{
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
            $nationType = Util::choiceRandom(array_keys(getNationTypeList()));
            $nationColor = Util::choiceRandom(array_keys(GetNationColors()));
            return ['che_건국', [
                'nationName'=>"㉿".mb_substr($general->getName(), 1),
                'nationType'=>$nationType,
                'nationColor'=>$nationColor
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
        foreach(CityConst::byID($general->getVar('city'))->path as $nearCityID){
            if(CityConst::byID($nearCityID)->level < 4){
                $targetCity[$nearCityID] = 0.5;
            }
            else if(!key_exists($nearCities, $occupiedCities)){
                $targetCity[$nearCityID] = 2;
            }
            else{
                $targetCity[$nearCityID] = 0;
            }
            
            $nearCities = searchDistance($nearCityID, 4, true);
            foreach($nearCities as $distance => $distCities){
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
            'SELECT sum(pop)/sum(pop2)*100 as pop,(sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(agri2)+sum(comm2)+sum(secu2)+sum(def2)+sum(wall2))*100 as all from city where nation=%i',
            $nationID
        );
        return $this->devRate;
    }

    protected function findWarTarget():?int{
        $nation = $this->nation;
        $nationID = $nation['nation'];
        SetNationFront($nationID);

        $frontCount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i AND front>0', $nationID);
        if($frontCount > 0){
            return null;
        }

        $devRate = $this->getNationDevelopedRate();
        if(($devRate['pop'] + $devRate['all']) / 2 < 80){
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

        foreach($db->query(
            'SELECT no, npc, level, killturn FROM general WHERE nation = %i AND 12 > level AND level > 4', $nationID
        ) as $chief){

            if($chief['npc'] < 2 && $chief['killturn'] < $minKillturn ){
                $chiefCandidate[$cheif['level']] = $chief['no'];
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
                'SELECT no FROM general WHERE nation = %i AND level = 1 AND killturn > %i AND npc < 2 AND belong >= %i ORDER BY leader DESC LIMIT 1',
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
                'SELECT no FROM general WHERE nation = %i AND level = 1 AND npc >= 2 AND belong >= %i ORDER BY leader DESC LIMIT 1',
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
            $candChiefPower = $db->queryFirstColumn(
                'SELECT no FROM general WHERE nation = %i AND power >= %i AND level = 1 AND belong >= %i ORDER BY power DESC LIMIT %i',
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
            
            $iterCandChiefPower = new \ArrayIterator($candChiefPower);
            $iterCandChiefIntel = new \ArrayIterator($candChiefIntel);

            foreach(range(10, $minChiefLevel, -1) as $cheifLevel){
                if(key_exists($cheifLevel, $chiefCandidate)){
                    continue;
                }

                /** @var \ArrayIterator $iterCurrentType */
                $iterCurrentType = ($cheifLevel % 2 == 0)?$iterCandChiefPower:$iterCandChiefIntel;
                $candidate = $iterCurrentType->current();

                while(key_exist($candidate, $promoted)){
                    $iterCurrentType->next();
                }

                $chiefCandidate[$cheifLevel] = $candidate;
                $promoted[$candidate] = $cheifLevel;
            }

            foreach($chiefCandidate as $chiefLevel=>$chiefID){
                $db->update('general', [
                    'level'=>$cheifLevel
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

            $avg = ($devRate['pop'] + $devRate['all']) / 2;

            if($avg > 95) $rate = 25;
            elseif($avg > 70) $rate = 20;
            elseif($avg > 50) $rate = 15;
            else $rate = 10;

            $db->update('nation', [
                'war'=>0,
                'rate'=>$rate
            ]);
            return $rate;
        }
    }
    
    protected function calcGoldBillRate():int{
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];
    
        $incomeList = getGoldIncome($nation['nation'], $nation['rate'], $env['gold_rate'], $env['type']);
        $income = $gold + $incomeList[0] + $incomeList[1];
        $outcome = getGoldOutcome($nation['nation'], 100);    // 100%의 지급량
        $bill = intval($income / $outcome * 80); // 수입의 80% 만 지급
    
        if($bill < 20)  { $bill = 20; }
        if($bill > 200) { $bill = 200; }
    
        $db->update('nation', [
            'bill'=>$bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }

    protected function caclRiceBillRate():int{
        $db = DB::db();
        $nation = $this->nation;
        $env = $this->env;

        $nationID = $nation['nation'];

        $incomeList = getRiceIncome($nation['nation'], $nation['rate'], $env['gold_rate'], $env['type']);
        $income = $rice + $incomeList[0] + $incomeList[1];
        $outcome = getRiceOutcome($nation['nation'], 100);    // 100%의 지급량
        $bill = intval($income / $outcome * 80); // 수입의 80% 만 지급

        if($bill < 20)  { $bill = 20; }
        if($bill > 200) { $bill = 200; }

        $db->update('nation', [
            'bill'=>$bill,
        ], 'nation=%i', $nationID);

        return $bill;
    }
}