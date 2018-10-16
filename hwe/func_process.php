<?php
namespace sammo;

use Constraint\Constraint;

/**
 * 장수의 통솔을 받아옴
 * 
 * @param array $general 장수 정보, leader, injury, lbonus, horse 사용
 * @param bool $withInjury 부상값 사용 여부
 * @param bool $withItem 아이템 적용 여부
 * @param bool $withStatAdjust 추가 능력치 보정 사용 여부
 * @param bool $useFloor 내림 사용 여부, false시 float 값을 반환할 수도 있음
 * 
 * @return int|float 계산된 능력치
 */
function getGeneralLeadership($general, $withInjury, $withItem, $withStatAdjust, $useFloor = true){
	if($general === null){
		return 0;
	}
    $leadership = $general['leader'];
    if($withInjury){
        $leadership *= (100 - $general['injury']) / 100;
    }

    if($withStatAdjust){
        if($general['special2'] == 72){
            $leadership *= 1.15;
        }
    }

    if(isset($general['lbonus'])){
        $leadership += $general['lbonus'];
    }

    if($withItem){
        $leadership += getHorseEff($general['horse']);
    }

    //$withStatAdjust는 통솔에서 미사용

    if($useFloor){
        return intval($leadership);
    }
    return $leadership;
    
}

/**
 * 장수의 무력을 받아옴
 * 
 * @param array $general 장수 정보, power, injury, weap 사용
 * @param bool $withInjury 부상값 사용 여부
 * @param bool $withItem 아이템 적용 여부
 * @param bool $withStatAdjust 추가 능력치 보정 사용 여부
 * @param bool $useFloor 내림 사용 여부, false시 float 값을 반환할 수도 있음
 * 
 * @return int|float 계산된 능력치
 */
function getGeneralPower($general, $withInjury, $withItem, $withStatAdjust, $useFloor = true){
	if($general === null){
		return 0;
	}
    $power = $general['power'];
    if($withInjury){
        $power *= (100 - $general['injury']) / 100;
    }

    if($withItem){
        $power += getWeapEff($general['weap']);
    }

    if($withStatAdjust){
        $power += Util::round(getGeneralIntel($general, $withInjury, $withItem, false, false) / 4);
    }

    if($useFloor){
        return intval($power);
    }
    return $power;
}

/**
 * 장수의 지력을 받아옴
 * 
 * @param array $general 장수 정보, intel, injury, book 사용
 * @param bool $withInjury 부상값 사용 여부
 * @param bool $withItem 아이템 적용 여부
 * @param bool $withStatAdjust 추가 능력치 보정 사용 여부
 * @param bool $useFloor 내림 사용 여부, false시 float 값을 반환할 수도 있음
 * 
 * @return int|float 계산된 능력치
 */
function getGeneralIntel($general, $withInjury, $withItem, $withStatAdjust, $useFloor = true){
	if($general === null){
		return 0;
	}
	
    $intel = $general['intel'];
    if($withInjury){
        $intel *= (100 - $general['injury']) / 100;
    }

    if($withItem){
        $intel += getBookEff($general['book']);
    }

    if($withStatAdjust){
        $intel += Util::round(getGeneralPower($general, $withInjury, $withItem, false, false) / 4);
    }
    
    if($useFloor){
        return intval($intel);
    }
    return $intel;
}

/**
 * 내정 커맨드 사용시 성공 확률 계산
 * 
 * @param array $general 장수 정보
 * @param int|string $type 내정 커맨드 타입, 0|'leader' = 통솔 기반, 1|'power' = 무력 기반, 2|'intel' = 지력 기반
 * 
 * @return array 계산된 실패, 성공 확률 ('success' => 성공 확률, 'fail' => 실패 확률)
 */
function CriticalRatioDomestic(&$general, $type) {
    $leader = getGeneralLeadership($general, false, true, true, false);
    $power = getGeneralPower($general, false, true, true, false);
    $intel = getGeneralIntel($general, false, true, true, false);

    $avg = ($leader+$power+$intel) / 3;
    /*
    * 능력치가 높아질 수록 성공 확률 감소. 실패 확률도 감소

    * 무력 내정 기준(지력 내정 방식과 구조 동일)
      756510(32%/30%), 707010(28%/25%), 657510(23%/20%)
      106575(23%/20%), 107070(20%/17%), 107565(17%/15%)
      506040(33%/30%), 505050(43%/40%), 504060(50%/50%)

    * 통솔 내정 기준
      756510(25%/22%), 707010(31%/28%), 657510(38%,35%), 
      505050(50%,50%), 107070(50%,50%)
    */
    switch($type) {
    case 'leader':
    case 0: $ratio = $avg / $leader; break;
    case 'power':
    case 1: $ratio = $avg / $power;  break;
    case 'intel':
    case 2: $ratio = $avg / $intel; break;
    default:
        throw new MustNotBeReachedException();
    }
    $ratio = min($ratio, 1.2);

    $fail = pow($ratio / 1.2, 1.4) - 0.3;
    $success = pow($ratio / 1.2, 1.5) - 0.25;

    $fail = min(max($fail, 0), 0.5);
    $success = min(max($success, 0), 0.5);


    return array(
        'success'=>$success,
        'fail'=>$fail
    );
}

/**
 * 수뇌직 통솔 보너스 계산
 * 
 * @param array &$general 장수 정보. 'lbonus' 값에 통솔 보너스가 입력 됨
 * @param int $nationLevel 국가 등급
 * 
 * @return int 계산된 $general['lbonus'] 값
 */
function setLeadershipBonus(&$general, $nationLevel){
    if($general['level'] == 12) {
        $lbonus = $nationLevel * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nationLevel;
    } else {
        $lbonus = 0;
    }
    $general['lbonus'] = $lbonus;
    return $lbonus;
}

function CriticalScoreEx(string $type):float {
    if($type == 'success'){
        return Util::randRange(2.2, 3.0);
    }
    if($type == 'fail'){
        return  Util::randRange(0.2, 0.4);
    }
    return 1;
}

function process_domestic(array $rawGeneral, int $type){
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $env = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);
    $general = new General($rawGeneral, null, $env['year'], $env['month']);

    //TODO: 최종적으로는 클래스 명 그대로 가야함
    $commandMap = [
        1=>'Command\che_농지개간',
        2=>'Command\che_상업투자',
        3=>'Command\che_기술연구',
        4=>'Command\che_주민선정',
        5=>'Command\che_수비강화',
        6=>'Command\che_성벽보수',
        7=>'Command\che_정착장려',
        8=>'Command\che_치안강화',
        9=>'Command\che_물자조달',
    ];
    $cmdClass = $commandMap[$type]??null;
    if(!$cmdClass){
        throw new \InvalidArgumentException('잘못된 내정 코드');
    }
    $cmdObj = new $cmdClass($general, $env);

    if(!$cmdObj->isRunnable()){
        return;
    }
    $cmdObj->run();
}

function process_11(&$general, $type) {
    if($type == 1){
        $type = '징병';
    }
    else{
        $type = '모병';
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $date = substr($general['turntime'],11,5);

    if($type === '징병') { 
        $defaultatmos = GameConst::$defaultAtmosLow;
        $defaulttrain = GameConst::$defaultTrainLow;
    }
    else {
        $defaultatmos = GameConst::$defaultAtmosHigh;
        $defaulttrain = GameConst::$defaultTrainHigh;
    }

    [$startyear, $year, $month] = $gameStor->getValuesAsArray(['startyear', 'year', 'month']);

    $actLog = new ActionLogger($general['no'], $general['nation'], $year, $month);

    $command = DecodeCommand($general['turn0']);
    $crewType = $command[2];
    $rawCrew = $command[1];
    

    if($crewType != $general['crewtype']) {
        $general['crew'] = 0;
        $general['train'] = $defaulttrain;
        $general['atmos'] = $defaultatmos;
    }

    if($general['crew'] != 0) { 
        $dtype = "추가".$type; 
    }
    else {
        $dtype = $type;
    }

    if(!$general['nation']){
        $actLog->pushGeneralActionLog("재야입니다. $dtype 실패. <1>$date</>");
        return;
    }

    $city = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $general['city']);

    if($city['nation'] != $general['nation']){
        $actLog->pushGeneralActionLog("아국이 아닙니다. $dtype 실패. <1>$date</>");
        return;
    }

    $crewTypeObj = GameUnitConst::byID($crewType);
    if($crewTypeObj === null){
        $actLog->pushGeneralActionLog("병종 코드 에러. $type 실패. <1>$date</>");
        return;
    }

    [$nationLevel, $tech] = $db->queryFirstList('SELECT `level`,tech FROM nation WHERE nation=%i', $general['nation']);

    $lbonus = setLeadershipBonus($general, $nationLevel);

    //NOTE: 입력 변수는 100명 단위임
    $crew = $rawCrew * 100;	
    if($crew + $general['crew'] > getGeneralLeadership($general, true, true, true)*100) { 
        $crew = max(0, getGeneralLeadership($general, true, true, true) * 100 - $general['crew']);
    }

    if($crew <= 0) {
        $actLog->pushGeneralActionLog("더이상 $dtype 할 수 없습니다. $dtype 실패. <1>$date</>");
        return;
    }

    $cost = $crewTypeObj->costWithTech($tech, $crew);
	if($type === '모병') { 
		$cost *= 2;
	}
    //성격 보정
    $cost = Util::round(CharCost($cost, $general['personal']));

    //특기 보정 : 징병, 보병, 궁병, 기병, 귀병, 공성
    if($general['special2'] == 72) { $cost *= 0.5; }
    else if($general['special2'] == 50 && $crewTypeObj->armType == GameUnitConstBase::T_FOOTMAN) { $cost *= 0.9; }
    else if($general['special2'] == 51 && $crewTypeObj->armType == GameUnitConstBase::T_ARCHER) { $cost *= 0.9; }
    else if($general['special2'] == 52 && $crewTypeObj->armType == GameUnitConstBase::T_CAVALRY) { $cost *= 0.9; }
    else if($general['special2'] == 40 && $crewTypeObj->armType == GameUnitConstBase::T_WIZARD) { $cost *= 0.9; }
    else if($general['special2'] == 53 && $crewTypeObj->armType == GameUnitConstBase::T_SIEGE) { $cost *= 0.9; }
    
    if($general['gold'] < $cost){
        $actLog->pushGeneralActionLog("자금이 모자랍니다. $dtype 실패. <1>$date</>");
        return;
    }

    if($general['rice'] < $crew / 100) {
        $actLog->pushGeneralActionLog("군량이 모자랍니다. $dtype 실패. <1>$date</>");
        return;
    }

    $ownCities = [];
    $ownRegions = [];
    foreach($db->queryFirstColumn('SELECT city FROM city WHERE nation = %i', $general['nation']) as $ownCity){
        $ownCities[$ownCity] = 1;
        $ownRegions[CityConst::byId($ownCity)->region] = 1;
    }
    $valid = $crewTypeObj->isValid($ownCities, $ownRegions, $year - $startyear, $tech);

    if(!$valid) {
        $actLog->pushGeneralActionLog("현재 $dtype 할 수 없는 병종입니다. $dtype 실패. <1>$date</>");
        return;
    }
    
    if($city['pop']-30000 < $crew) {    // 주민 30000명 이상만 가능
        $actLog->pushGeneralActionLog("주민이 모자랍니다. $dtype 실패. <1>$date</>");
        return;
    }
    if($city['trust'] < 20) {
        $actLog->pushGeneralActionLog("민심이 낮아 주민들이 도망갑니다. $dtype 실패. <1>$date</>");
        return;
    }
    
    $josaUl = JosaUtil::pick($crewTypeObj->name, '을');
    $actLog->pushGeneralActionLog($crewTypeObj->name."{$josaUl} <C>{$crew}</>명 {$dtype}했습니다. <1>$date</>");
    $exp = Util::round($crew / 100);
    $ded = Util::round($crew / 100);
    // 숙련도 증가
    addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $crewType, $crew/100);

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    $atmos = Util::round(($general['atmos'] * $general['crew'] + $defaultatmos * $crew) / ($general['crew'] + $crew));
    $train = Util::round(($general['train'] * $general['crew'] + $defaulttrain * $crew) / ($general['crew'] + $crew));
    $general['crew'] += $crew;
    $general['gold'] -= $cost;
    // 주민수 감소        // 민심 감소
    if($type === '징병') {
        $city['trust'] -= ($crew / $city['pop'])*100; 
    }
    else {
        $city['trust'] -= ($crew / 2 / $city['pop'])*100; 
    }
    if($city['trust'] < 0) { $city['trust'] = 0; }

    $db->update('city', [
        'pop'=>$db->sqleval('pop-%i', $crew),
        'trust'=>$city['trust']
    ], 'city = %i', $general['city']);

    // 통솔경험, 병종 변경, 병사수 변경, 훈련치 변경, 사기치 변경, 자금 군량 하락, 공헌도, 명성 상승
    $general['leader2']++;
    $db->update('general', [
        'resturn'=>'SUCCESS',
        'leader2'=>$general['leader2'],
        'crewtype'=>$crewTypeObj->id,
        'crew'=>$general['crew'],
        'train'=>$train,
        'atmos'=>$atmos,
        'gold'=>$general['gold'],
        'rice'=>$db->sqleval('rice - %i', Util::round($crew/100)),
        'dedication'=>$db->sqleval('dedication + %i', $ded),
        'experience'=>$db->sqleval('experience + %i', $exp)
    ], 'no=%i', $general['no']);

    checkAbilityEx($general['no'], $actLog);
    uniqueItemEx($general['no'], $actLog);
    $actLog->pushGeneralActionLog($log, ActionLogger::RAWTEXT);
}

function process_15(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select nation,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($general['term']%100 == 15) {
        $term = intdiv($general['term'], 100) + 1;
        $code = $term * 100 + 15;
    } else {
        $term = 1;
        $code = 100 + 15;
    }

    $cost = Util::round($general['crew']/100 * 3 * getTechCost($nation['tech']));

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 전투태세 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 전투태세 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 전투태세 실패. <1>$date</>";
    } elseif($general['crew'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:병사가 없습니다. 전투태세 실패. <1>$date</>";
    } elseif($general['atmos'] >= 90 && $general['train'] >= 90) {
        $log[] = "<C>●</>{$admin['month']}월:이미 병사들은 날쌔고 용맹합니다. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 전투태세 실패. <1>$date</>";
    } elseif($term < 3) {
        $log[] = "<C>●</>{$admin['month']}월:병사들을 열심히 훈련중... ({$term}/3) <1>$date</>";

        $query = "update general set resturn='ONGOING',term={$code} where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        //기술로 가격
        $gold = $general['gold'] - $cost;

        $log[] = "<C>●</>{$admin['month']}월:전투태세 완료! <1>$date</>";
        $exp = 100 * 3;
        $ded = 70 * 3;
        // 숙련도 증가
        addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], Util::round($general['crew']/100 * 3));
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 훈련,사기치 변경        // 자금 감소        // 경험치 상승        // 공헌도, 명성 상승
        $general['leader2']+=3;
        $query = "update general set resturn='SUCCESS',term='0',atmos='95',train='95',gold='$gold',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_16(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    $query = "select nation,war,sabotagelimit,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select nation,sabotagelimit,tech from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(key_exists($destination, CityConst::byID($general['city'])->path)){
        $nearCity = true;
    }
    else{
        $nearCity = false;
    }

    $josaRo = JosaUtil::pick($destcity['name'], '로');
    if($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:현재 초반 제한중입니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif(!$nearCity) {
        $log[] = "<C>●</>{$admin['month']}월:인접도시가 아닙니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($general['crew'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:병사가 없습니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($general['rice'] <= Util::round($general['crew']/100)) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($dip['state'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:교전중인 국가가 아닙니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:본국에서만 출병가능합니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($nation['war'] == 1) {
        $log[] = "<C>●</>{$admin['month']}월:현재 전쟁 금지입니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:본국입니다. <G><b>{$destcity['name']}</b></>{$josaRo} 출병 실패. <1>$date</>";
        pushGenLog($general, $log);
        process_21($general);
        return;
    } else {
        // 전쟁 표시
        $query = "update city set state=43,term=3 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 숙련도 증가
        addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], Util::round($general['crew']/100));
        // 전투 처리
        processWar($general, $destcity);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_31(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);
    $msg = [];

    $admin = $gameStor->getValues(['year', 'month', 'develcost']);

    $dist = searchDistance($general['city'], 2, false);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if(!$city) {
        $log[] = "<C>●</>{$admin['month']}월:없는 도시입니다. 첩보 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*3) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*3) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
    } elseif($general['nation'] == $city['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
//    } elseif(!key_exists($destination, $dist)) {
//        $log[] = "<C>●</>{$admin['month']}월:너무 멉니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
    } else {
        $query = "select crew,crewtype from general where city='$destination' and nation='{$city['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $crew = 0;
        $typecount = [];
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            if($gen['crew'] != 0) {
                if(!key_exists($gen['crewtype'], $typecount)){
                    $typecount[$gen['crewtype']] = 1;
                }
                else{
                    $typecount[$gen['crewtype']]+=1;
                }
                
                $crew += $gen['crew'];
            }
        }
        if(!key_exists($destination, $dist)) {
            $josaUl = JosaUtil::pick($city['name'], '을');
            $alllog[] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>{$josaUl} 살피는 것 같습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 소문만 들을 수 있었습니다. <1>$date</>";
            $log[] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:".round($city['trust'], 1).", 장수:$gencount, 병력:$crew";
        } elseif($dist[$destination] == 2) {
            $josaUl = JosaUtil::pick($city['name'], '을');
            $alllog[] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>{$josaUl} 살피는 것 같습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 어느정도 정보를 얻었습니다. <1>$date</>";
            $log[] = "【<M>첩보</>】농업:{$city['agri']}, 상업:{$city['comm']}, 치안:{$city['secu']}, 수비:{$city['def']}, 성벽:{$city['wall']}";
            $log[] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:".round($city['trust'], 1).", 장수:$gencount, 병력:$crew";
        } else {
            $josaUl = JosaUtil::pick($city['name'], '을');
            $alllog[] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>{$josaUl} 살피는 것 같습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 많은 정보를 얻었습니다. <1>$date</>";
            $msg[] = "【<S>병종</>】";

            foreach($typecount as $crewtype=>$cnt){
                $crewtypeText = mb_substr(GameUnitConst::byID($crewtype)->name, 0, 2);
                $msg[] = "{$crewtypeText}:{$cnt}";
            }

            $log[] = join(' ', $msg);
            $msg = [];
            
            $log[] = "【<M>첩보</>】농업:{$city['agri']}, 상업:{$city['comm']}, 치안:{$city['secu']}, 수비:{$city['def']}, 성벽:{$city['wall']}";
            $log[] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:".round($city['trust'], 1).", 장수:$gencount, 병력:$crew";

            if($general['nation'] != 0 && $city['nation'] != 0) {
                $query = "select name,tech from nation where nation='{$city['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $yourTech = MYDB_fetch_array($result);

                $query = "select tech from nation where nation='{$general['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $myTech = MYDB_fetch_array($result);

                $diff = floor($yourTech['tech']) - floor($myTech['tech']);   // 차이
                if($diff >= 1000) {      $log[] = "【<span class='ev_notice'>{$yourTech['name']}</span>】아국대비기술:<M>↑</>압도"; }
                elseif($diff >=  250) {  $log[] = "【<span class='ev_notice'>{$yourTech['name']}</span>】아국대비기술:<Y>▲</>우위"; }
                elseif($diff >= -250) {  $log[] = "【<span class='ev_notice'>{$yourTech['name']}</span>】아국대비기술:<W>↕</>대등"; }
                elseif($diff >= -1000) { $log[] = "【<span class='ev_notice'>{$yourTech['name']}</span>】아국대비기술:<G>▼</>열위"; }
                else {                   $log[] = "【<span class='ev_notice'>{$yourTech['name']}</span>】아국대비기술:<C>↓</>미미"; }
            }
        }

        // 자금 하락        // 경험치 상승        // 공헌도, 명성 상승
        $exp = rand() % 100 + 1;
        $ded = rand() % 70 + 1;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general['leader2']++;
        $general['gold'] -= $admin['develcost']*3;
        $general['rice'] -= $admin['develcost']*3;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");



        $rawSpy = $db->queryFirstField('SELECT spy FROM nation WHERE nation = %i', $general['nation']);
        
        if($rawSpy == ''){
            $spyInfo = [];
        }
        else if(strpos($rawSpy, '|') !== false || is_numeric($rawSpy)){
            //TODO: 0.8 버전 이후에는 삭제할 것. 이후 버전은 json으로 변경됨.
            $spyInfo = [];
            foreach(explode('|', $rawSpy) as $value){
                $value = intval($value);
                $cityNo = intdiv($value, 10);
                $remainMonth = $value % 10;
                $spyInfo[$cityNo] = $remainMonth;
            }
        }
        else{
            $spyInfo = Json::decode($rawSpy);
        }

        $spyInfo[$destination] = 3;

        $db->update('nation', [
            'spy'=>Json::encode($spyInfo, Json::EMPTY_ARRAY_IS_DICT)
        ], 'nation=%i', $general['nation']);

        $log = checkAbility($general, $log);
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_42(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $type = rand() % 27 + 1;
    $exp = 30;
    $ded = 0;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    $exp2 = $exp * 2;

    switch($type) {
    case 1:
        $log[] = "<C>●</>{$admin['month']}월:지나가는 행인에게서 금을 <C>300</> 받았습니다. <1>$date</>";
        // 자금 상승        // 명성 상승
        $query = "update general set resturn='SUCCESS',gold=gold+300,experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 2:
        $log[] = "<C>●</>{$admin['month']}월:지나가는 행인에게서 쌀을 <C>300</> 받았습니다. <1>$date</>";
        // 군량 상승        // 명성 상승
        $query = "update general set resturn='SUCCESS',rice=rice+300,experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 3:
        $log[] = "<C>●</>{$admin['month']}월:어느 명사와 설전을 벌여 멋지게 이겼습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['intel2'] += 2;
        $query = "update general set resturn='SUCCESS',intel2='{$general['intel2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 4:
        $log[] = "<C>●</>{$admin['month']}월:명사와 설전을 벌였으나 망신만 당했습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 5:
        $log[] = "<C>●</>{$admin['month']}월:동네 장사와 힘겨루기를 하여 멋지게 이겼습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['power2'] += 2;
        $query = "update general set resturn='SUCCESS',power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 6:
        $log[] = "<C>●</>{$admin['month']}월:동네 장사와 힘겨루기를 했지만 망신만 당했습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 7:
        $log[] = "<C>●</>{$admin['month']}월:산적과 싸워 금 <C>300</>을 빼앗았습니다. <1>$date</>";
        // 자금 상승        // 경험치 상승        // 명성 상승
        $general['power2'] += 2;
        $query = "update general set resturn='SUCCESS',gold=gold+300,power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 8:
        $log[] = "<C>●</>{$admin['month']}월:산적을 만나 금 <C>200</>을 빼앗겼습니다. <1>$date</>";
        $general['gold'] -= 200;
        if($general['gold'] <= 0) { $general['gold'] = 0; }
        // 자금 하락        // 경험 상승
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 9:
        $log[] = "<C>●</>{$admin['month']}월:호랑이를 잡아 고기 <C>300</>을 얻었습니다. <1>$date</>";
        // 군량 상승        // 경험치 상승
        $general['power2'] += 2;
        $query = "update general set resturn='SUCCESS',rice=rice+300,power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 10:
        $log[] = "<C>●</>{$admin['month']}월:호랑이에게 물려 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 10 + 10;
        $general['power2']--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='{$general['power2']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 11:
        $log[] = "<C>●</>{$admin['month']}월:곰을 잡아 고기 <C>300</>을 얻었습니다. <1>$date</>";
        // 군량 상승        // 경험치 상승        // 명성 상승
        $general['power2'] += 2;
        $query = "update general set resturn='SUCCESS',rice=rice+300,power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 12:
        $log[] = "<C>●</>{$admin['month']}월:곰에게 할퀴어 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 10 + 10;
        $general['power2']--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='{$general['power2']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 13:
        $log[] = "<C>●</>{$admin['month']}월:주점에서 사람들과 어울려 술을 마셨습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 14:
        $log[] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구해주었습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 15:
        $log[] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구해주다가 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 10 + 10;
        $general['power2']--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 16:
        $log[] = "<C>●</>{$admin['month']}월:돈을 빌려주었다가 이자 <C>300</>을 받았습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['intel2']++;
        $query = "update general set resturn='SUCCESS',gold=gold+300,intel2='{$general['intel2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 17:
        $log[] = "<C>●</>{$admin['month']}월:돈을 <C>200</> 빌려주었다가 떼어먹혔습니다. <1>$date</>";
        $general['gold'] -= 200;
        if($general['gold'] <= 0) { $general['gold'] = 0; }
        // 명성 상승
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 18:
        $log[] = "<C>●</>{$admin['month']}월:쌀을 빌려주었다가 이자 <C>300</>을 받았습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['intel2']++;
        $query = "update general set resturn='SUCCESS',rice=rice+300,intel2='{$general['intel2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 19:
        $log[] = "<C>●</>{$admin['month']}월:쌀을 <C>200</> 빌려주었다가 떼어먹혔습니다. <1>$date</>";
        $general['rice'] -= 200;
        if($general['rice'] <= 0) { $general['rice'] = 0; }
        // 명성 상승
        $query = "update general set resturn='SUCCESS',rice='{$general['rice']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 20:
        $log[] = "<C>●</>{$admin['month']}월:거리에서 글 모르는 아이들을 모아 글을 가르쳤습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['intel2'] += 2;
        $query = "update general set resturn='SUCCESS',intel2='{$general['intel2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 21:
        $log[] = "<C>●</>{$admin['month']}월:백성들에게 현인의 가르침을 설파했습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['leader2'] += 2;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 22:
        $log[] = "<C>●</>{$admin['month']}월:어느 집의 무너진 울타리를 고쳐주었습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['power2'] += 2;
        $query = "update general set resturn='SUCCESS',power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 23:
        $log[] = "<C>●</>{$admin['month']}월:어느 집의 도망친 가축을 되찾아 주었습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general['leader2'] += 2;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 24:
        $log[] = "<C>●</>{$admin['month']}월:호랑이에게 물려 크게 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 30 + 20;
        $general['power2']--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='{$general['power2']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 25:
        $log[] = "<C>●</>{$admin['month']}월:곰에게 할퀴어 크게 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 30 + 20;
        $general['power2']--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='{$general['power2']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 26:
        $log[] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구하다가 죽을뻔 했습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 50 + 30;
        $general['power2']--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='{$general['power2']}',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    default:
        $log[] = "<C>●</>{$admin['month']}월:아무일도 일어나지 않았습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    }

    $log = checkAbility($general, $log);
    pushGenLog($general, $log);
}

function process_43(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $genlog = [];
    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $command = DecodeCommand($general['turn0']);
    $what = $command[3];
    $who = $command[2];
    $amount = $command[1];
    $amount *= 100;    // 100~10000까지

    if($amount > 10000) { $amount = 10000; }
    if($amount < 100) { $amount = 100; }
    if($what == 1) { $dtype = "금"; }
    elseif($what == 2) { $dtype = "쌀"; }
    else { $what = 2; $dtype = "쌀"; }

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no,name,nation,gold,rice from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if($what == 1 && $general['gold'] < $amount) { $amount = $general['gold']; }
    if($what == 2 && $general['rice'] < $amount) { $amount = $general['rice']; }

    if(!$gen) {
        $log[] = "<C>●</>{$admin['month']}월:없는 장수입니다. 증여 실패. <1>$date</>";
    } elseif($what == 1 && $general['gold'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 없습니다. 증여 실패. <1>$date</>";
    } elseif($what == 2 && $general['rice'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 없습니다. 증여 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야는 불가능합니다. 증여 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 증여 실패. <1>$date</>";
    } elseif($general['nation'] != $gen['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. 증여 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 증여 실패. <1>$date</>";
    } else {
        $genlog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게서 $dtype <C>$amount</>을 증여 받았습니다.";
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$gen['name']}</>에게 $dtype <C>$amount</>을 증여했습니다. <1>$date</>";

        if($what == 1) {
            $gen['gold'] += $amount;
            $query = "update general set gold='{$gen['gold']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $general['gold'] -= $amount;
            $query = "update general set gold='{$general['gold']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($what == 2) {
            $gen['rice'] += $amount;
            $query = "update general set rice='{$gen['rice']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $general['rice'] -= $amount;
            $query = "update general set rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 경험치 상승        // 공헌도, 명성 상승
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
    pushGenLog($gen, $genlog);
}

function process_44(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select name,gold,rice from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general['turn0']);
    $what = $command[2];
    $amount = $command[1];
    $amount *= 100;    // 100~10000까지

    if($amount > 10000) { $amount = 10000; }
    if($amount < 100) { $amount = 100; }
    if($what == 1) {
        $dtype = "금";
        if($general['gold'] < $amount) { $amount = $general['gold']; }
    } elseif($what == 2) {
        $dtype = "쌀";
        if($general['rice'] < $amount) { $amount = $general['rice']; }
    } else {
        $what = 2;
        $dtype = "쌀";
        if($general['rice'] < $amount) { $amount = $general['rice']; }
    }

    if($general['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 헌납 실패. <1>$date</>";
    } elseif($what == 1 && $general['gold'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 없습니다. 헌납 실패. <1>$date</>";
    } elseif($what == 2 && $general['rice'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 없습니다. 헌납 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 헌납 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 헌납 실패. <1>$date</>";
    } else {
//        $alllog[] = "<C>●</>{$admin['month']}월:<D><b>{$nation['name']}</b></>에서 장수들이 재산을 헌납 하고 있습니다.";
        $log[] = "<C>●</>{$admin['month']}월: $dtype <C>$amount</>을 헌납했습니다. <1>$date</>";

        if($what == 1) {
            $general['gold'] -= $amount;
            $query = "update general set gold='{$general['gold']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['gold'] += $amount;
            $query = "update nation set gold='{$nation['gold']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($what == 2) {
            $general['rice'] -= $amount;
            $query = "update general set rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['rice'] += $amount;
            $query = "update nation set rice='{$nation['rice']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 경험치 상승        // 공헌도, 명성 상승
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}


function process_48(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $city = getCity($general['city']);

    $command = DecodeCommand($general['turn0']);
    $type = $command[1];

    if($type < 100) { $isweap = 0; }
    elseif($type < 200) { $type -= 100; $isweap = 1; }
    elseif($type < 300) { $type -= 200; $isweap = 2; }
    elseif($type < 400) { $type -= 300; $isweap = 3; }
    else { $type = 7; }

    if($isweap == 3) { $cost = getItemCost2($type); }
    else { $cost = getItemCost($type); }

    if($city['trade'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:도시에 상인이 없습니다. 장비매매 실패. <1>$date</>";
    } elseif($city['secu']/1000 < $type) {
        $log[] = "<C>●</>{$admin['month']}월:이 도시에서는 구할 수 없었습니다. 구입 실패. <1>$date</>";
    } elseif($type > 6 || $type < 0) {
        $log[] = "<C>●</>{$admin['month']}월:구입할 수 있는 물건이 아닙니다. 구입 실패. <1>$date</>";
    } elseif($general['gold'] < $cost && $type != 0) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 구입 실패. <1>$date</>";
    } elseif($general['weap'] == 0 && $isweap == 0 && $type == 0) {
        $log[] = "<C>●</>{$admin['month']}월:무기가 없습니다. 판매 실패. <1>$date</>";
    } elseif($general['book'] == 0 && $isweap == 1 && $type == 0) {
        $log[] = "<C>●</>{$admin['month']}월:서적이 없습니다. 판매 실패. <1>$date</>";
    } elseif($general['horse'] == 0 && $isweap == 2 && $type == 0) {
        $log[] = "<C>●</>{$admin['month']}월:명마가 없습니다. 판매 실패. <1>$date</>";
    } elseif($general['item'] == 0 && $isweap == 3 && $type == 0) {
        $log[] = "<C>●</>{$admin['month']}월:도구가 없습니다. 판매 실패. <1>$date</>";
    } else {
        if($isweap == 0) {
            if($type != 0) {
                $josaUl = JosaUtil::pick(getWeapName($type), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getWeapName($type)."</>{$josaUl} 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',weap='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = Util::round(getItemCost($general['weap']) / 2);
                $josaUl = JosaUtil::pick(getWeapName($general['weap']), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getWeapName($general['weap'])."</>{$josaUl} 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',weap='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        } elseif($isweap == 1) {
            if($type != 0) {
                $josaUl = JosaUtil::pick(getBookName($type), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getBookName($type)."</>{$josaUl} 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',book='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = Util::round(getItemCost($general['book']) / 2);
                $josaUl = JosaUtil::pick(getBookName($general['book']), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getBookName($general['book'])."</>{$josaUl} 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',book='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        } elseif($isweap == 2) {
            if($type != 0) {
                $josaUl = JosaUtil::pick(getHorseName($type), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getHorseName($type)."</>{$josaUl} 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',horse='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = Util::round(getItemCost($general['horse']) / 2);
                $josaUl = JosaUtil::pick(getHorseName($general['horse']), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getHorseName($general['horse'])."</>{$josaUl} 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',horse='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        } elseif($isweap == 3) {
            if($type != 0) {
                $josaUl = JosaUtil::pick(getItemName($type), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getItemName($type)."</>{$josaUl} 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',item='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = Util::round(getItemCost2($general['item']) / 2);
                $josaUl = JosaUtil::pick(getItemName($general['item']), '을');
                $log[] = "<C>●</>{$admin['month']}월:<C>".getItemName($general['item'])."</>{$josaUl} 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',item='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }

    $exp = 10;
    $ded = 0;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    // 명성 상승
    $query = "update general set experience=experience+'$exp' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushGenLog($general, $log);
}

function process_49(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    $city = getCity($general['city']);

    $command = DecodeCommand($general['turn0']);
    $type = $command[2];
    $amount = $command[1];
    $amount *= 100;

    if($type != 1 && $type != 2) { $type = 1; }
    if($amount < 100) { $amount = 100; }
    elseif($amount > 10000) { $amount = 10000; }

    if($city['trade'] == 0 && $general['npc'] >= 2) {
        $city['trade'] = 100;
    }

    // 거상 f배 이득시 금 계산
    // a : 쌀, b : 물가, f : 배수, c : 금
    // (0.7 ~1.3)  : c = a((1+f)b-f)
    // (0.99~1.01) : c = a((1-f)b+f)
    if($type == 1) {
        $dtype = "군량 판매";
        if($general['rice'] < $amount) { $amount = $general['rice']; }
        $cost = $amount * $city['trade'] / 100;
        $tax = $cost * GameConst::$exchangeFee;
        $cost = $cost - $tax;
    } elseif($type == 2) {
        $dtype = "군량 구입";
        $cost = $amount * $city['trade'] / 100;
        $tax = $cost * GameConst::$exchangeFee;
        $cost = $cost + $tax;
        if($general['gold'] < $cost) {
            $cost = $general['gold'];
            $tax = $cost * GameConst::$exchangeFee;
            $amount = ($cost-$tax) * 100 / $city['trade'];
        }
    }

    $cost = Util::round($cost);
    $amount = Util::round($amount);
    $tax = Util::round($tax);

    if($city['trade'] == 0 && $general['npc'] < 2) {
        $log[] = "<C>●</>{$admin['month']}월:도시에 상인이 없습니다. $dtype 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($type == 1 && $general['rice'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 없습니다. $dtype 실패. <1>$date</>";
    } elseif($type == 2 && $general['gold'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 없습니다. $dtype 실패. <1>$date</>";
    } else {
        // 판매
        if($type == 1) {
            $log[] = "<C>●</>{$admin['month']}월:군량 <C>$amount</>을 팔아 자금 <C>$cost</>을 얻었습니다. <1>$date</>";
            // 군량 감소
            $query = "update general set rice=rice-{$amount} where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 자금 증가
            $query = "update general set gold=gold+{$cost} where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 구입
        } elseif($type == 2) {
            $log[] = "<C>●</>{$admin['month']}월:군량 <C>$amount</>을 사서 자금 <C>$cost</>을 썼습니다. <1>$date</>";
            // 군량 증가
            $query = "update general set rice=rice+{$amount} where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 자금 감소
            $query = "update general set gold=gold-{$cost} where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 세금 국고로
        $query = "update nation set gold=gold+'$tax' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $exp = 30;
        $ded = 50;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 공헌도, 명성 상승
        $query = "update general set resturn='SUCCESS',dedication=dedication+'$ded', experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승
        switch(Util::choiceRandomUsingWeight([$general['leader'], $general['power'], $general['intel']])) {
        case 0:
            $general['leader2']++;
            $query = "update general set leader2='{$general['leader2']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            break;
        case 1:
            $general['power2']++;
            $query = "update general set power2='{$general['power2']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            break;
        case 2:
            $general['intel2']++;
            $query = "update general set intel2='{$general['intel2']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            break;
        }

        $log = checkAbility($general, $log);
    }

    pushGenLog($general, $log);
}

function process_50(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $month = $gameStor->month;

    $log[] = "<C>●</>{$month}월:건강 회복을 위해 요양합니다. <1>$date</>";
    // 경험치 상승        // 공헌도, 명성 상승
    $exp = 10;
    $ded = 7;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);
    $query = "update general set resturn='SUCCESS',injury='0',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushGenLog($general, $log);
}

function process_99(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $log[] = "<C>●</>{$admin['month']}월:아직 구현되지 않았습니다. <1>$date</>";

    $exp = 100;
    $ded = 0;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    // 명성 상승
    $query = "update general set experience=experience+'$exp' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushGenLog($general, $log);
}

