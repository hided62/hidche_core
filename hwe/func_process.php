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

