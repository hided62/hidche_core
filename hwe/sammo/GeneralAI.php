<?php
namespace sammo;

class GeneralAI{
    /**
     * @var General $general
     */
    protected $general;
    protected $city;
    protected $nation;
    protected $dipState;
    protected $genType;
    protected $env;

    protected $leadership;
    protected $power;
    protected $intel;

    protected $attackable;

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
        $this->env = $gameStor->getValues(['startyear','year','month','turnterm','scenario','gold_rate','rice_rate']);
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

        $cityCount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i AND supply=1 AND front>0', $nationID);
        // 공격가능도시 있으면
        $this->attackable = $cityCount > 0;

    
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
                    $commandList[$commandName] = $power / 3;
                }
            }
            if($develRate['def'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_수비강화', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $power / 3;
                }
            }
            if($develRate['wall'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_성벽보수', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $power / 3;
                }
            }
        }
        if($genType & self::t지장){
            if($develRate['agri'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_농지개간', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $intel / 2;
                }
            }
            if($develRate['comm'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_상업투자', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $intel / 2;
                }
            }
            if(!TechLimit($env['startyear'], $env['year'], $nation['tech'])){
                $commandObj = buildGeneralCommandClass('che_기술연구', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $intel / 4;
                }
            }
        }
        if($genType & self::t통솔장){
            if($develRate['trust'] < 1){
                $commandObj = buildGeneralCommandClass('che_주민선정', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $leadership / 2;
                }
            }
            if($develRate['pop'] < 0.99){
                $commandObj = buildGeneralCommandClass('che_정착장려', $general, $env);
                if($commandObj->isRunnable()){
                    $commandList[$commandName] = $leadership / 2;
                }
            }
        }

        if(!$commandList){
            $cityFull = true;
        }

        $genCount = $db->queryFirstField('SELECT count(no) FROM general');
        $commandList['che_인재탐색'] = 500 / $genCount * 10;

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

        return [$command, $arg];
    }

    public function chooseNeutralTurn():array{
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

        switch(Util::choiceRandomUsingWeight([11.4, 40, 20, 28.6])) {
            //임관
            case 0:
    
                $available = true;
    
                if($admin['startyear']+3 > $admin['year']){
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
            case 1: //거병이나 견문
                // 초반이면서 능력이 좋은놈 위주로 1.4%확률로 거병
                $prop = Util::randF() * (GameConst::$defaultStatNPCMax + GameConst::$chiefStatMin) / 2;
                $ratio = ($general->getVar('leader') + $general->getVar('power') + $general->getVar('intel')) / 3;
                if($admin['startyear']+2 > $admin['year'] && $prop < $ratio && Util::randBool(0.014) && $general->getVar('makelimit') == 0) {
                    //거병
                    $command = 'che_거병';
                } else {
                    //견문
                    $command = 'che_견문';
                }
                break;
            case 2: //이동
                
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

        $db = DB::db();

        if($general->getVar('npc') == 5){
            if($nationID == 0 && $general->getVar('killturn') > 1){
                $command = 'che_휴식'; //휴식
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

        if($general->getVar('level') == 0){
            return $this->chooseNeutralTurn();
        }

        $baseDevelCost = $env['develcost'] * 12;

        $techCost = getTechCost($nation['tech']);
        $baseArmCost = 10 * GameUnitConst::byID(GameUnitConst::DEFAULT_CREWTYPE)->costWithTect($nation['tech']);//기본 병종 1000기
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

    }
}