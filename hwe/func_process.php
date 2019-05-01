<?php
namespace sammo;

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
function getGeneralLeadership(&$general, $withInjury, $withItem, $withStatAdjust, $useFloor = true){
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
function getGeneralPower(&$general, $withInjury, $withItem, $withStatAdjust, $useFloor = true){
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
function getGeneralIntel(&$general, $withInjury, $withItem, $withStatAdjust, $useFloor = true){
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
 * @param int $type 내정 커맨드 타입, 0 = 통솔 기반, 1 = 무력 기반, 2 = 지력 기반
 * 
 * @return array 계산된 실패, 성공 확률 ('succ' => 성공 확률, 'fail' => 실패 확률)
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
    case 0: $ratio = $avg / $leader; break;
    case 1: $ratio = $avg / $power;  break;
    case 2: $ratio = $avg / $intel; break;
    }
    $ratio = min($ratio, 1.2);

    $fail = pow($ratio / 1.2, 1.4) - 0.3;
    $succ = pow($ratio / 1.2, 1.5) - 0.25;

    $fail = min(max($fail, 0), 0.5);
    $succ = min(max($succ, 0), 0.5);


    return array(
        'succ'=>$succ,
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

function CriticalScore($score, $type) {
    switch($type) {
    case 0:
        $ratio = Util::randF()*0.8 + 2.2;   // 2.2~3.0
        break;
    case 1:
        $ratio = Util::randF()*0.2 + 0.2;   // 0.2~0.4
        break;
    }
    return Util::round($score * $ratio);
}

function process_1(&$general, $type) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);

    if($type == 1)     { $dtype = "농지 개간"; $atype = "을"; $btype = "은"; $stype = "agri"; }
    elseif($type == 2) { $dtype = "상업 투자"; $atype = "를"; $btype = "는"; $stype = "comm"; }

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } else {
        // 민심 50 이하이면 50과 같게
        if($city['rate'] < GameConst::$develrate) { $city['rate'] = GameConst::$develrate; }
        $rate = $city['rate'] / 100;

        $score = getGeneralIntel($general, true, true, true, false) * $rate;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 2 || $nation['type'] == 12) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 8 || $nation['type'] == 11) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 모사 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 9 || $general['level'] == 7 || $general['level'] == 5) { $score *= 1.05; }
        // 군사 보정
        if($general['level'] == 3 && $general['no'] == $city['gen2']) { $score *= 1.05; }

        $rd = Util::randF();
        $r = CriticalRatioDomestic($general, 2);

        // 특기보정 : 경작, 상재
        if($type == 1 && $general['special'] == 1) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($type == 2 && $general['special'] == 2) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }
        //민심 반영
        if($city['rate'] < 80) { $r['succ'] *= $city['rate'] / 80; }
        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}{$atype} <span class='ev_failed'>실패</span>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($r['succ'] + $r['fail'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}{$atype} <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}{$atype} 하여 <C>$score</> 상승했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        if($city['front'] == 1){
            if($city['city'] == $nation['capital']){
                $degrade = 1 - ($admin['year'] - $admin['startyear'] - 5) * 0.025;
                $score *= Util::valueFit($degrade, 0.5, 1);
            }
            else{
                $score *= 0.5;
            }
            
        }

        $score += $city["$stype"];
        if($score > $city["{$stype}2"]) { $score = $city["{$stype}2"]; }
        // 내정 상승
        $query = "update city set {$stype}='$score' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 자금 하락, 경험치 상승
        $general['gold'] -= $admin['develcost'];
        $general['intel2']++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',intel2='{$general['intel2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_3(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);

    $dtype = "기술 연구";

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } else {
        $score = getGeneralIntel($general, true, true, true, false);
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 3 || $nation['type'] == 13)                                                                   { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 5 || $nation['type'] == 6 || $nation['type'] == 7 || $nation['type'] == 8 || $nation['type'] == 12) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 모사 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 9 || $general['level'] == 7 || $general['level'] == 5) { $score *= 1.05; }

        $rd = Util::randF();
        $r = CriticalRatioDomestic($general, 2);
        // 특기보정 : 발명
        if($general['special'] == 3) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }
        //민심 반영
        if($city['rate'] < 80) { $r['succ'] *= $city['rate'] / 80; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}를 <span class='ev_failed'>실패</span>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($r['succ'] + $r['fail'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}를 <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}를 하여 <C>$score</> 상승했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        //장수수 구함
        $gencount = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i', $general['nation']);
        $gencnt_eff = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc != 5', $general['nation']);
        if ($gencnt_eff < GameConst::$initialNationGenLimit) {
            $gencount = max(GameConst::$initialNationGenLimit, $gencount);
            $gencnt_eff = GameConst::$initialNationGenLimit;
        }

        if($gencount != $gencnt_eff){
            $score *= $gencount / $gencnt_eff;
        }
        
        // 부드러운 기술 제한
        if(TechLimit($admin['startyear'], $admin['year'], $nation['tech'])) { $score /= 4; }

        // 내정 상승
        $db->update('nation', [
            'totaltech'=>$db->sqleval('totaltech + %i', Util::round($score)),
            'tech'=>$db->sqleval('totaltech / %i', $gencount)
        ], 'nation=%i', $general['nation']);
        // 자금 하락, 경험치 상승        // 공헌도, 명성 상승 = $score * 10
        $general['gold'] -= $admin['develcost'];

        $general['intel2']++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',intel2='{$general['intel2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_4(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 주민 선정 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. 주민 선정 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 주민 선정 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 주민 선정 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*2) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. 주민 선정 실패. <1>$date</>";
    } elseif($city['rate'] >= 100) {
        $log[] = "<C>●</>{$admin['month']}월:민심은 충분합니다. 주민 선정 실패. <1>$date</>";
    } else {
        $score = getGeneralLeadership($general, true, true, true) / 10;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 2 || $nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 10) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 1 || $nation['type'] == 3 || $nation['type'] == 9)                        { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모 보정
        if($general['level'] == 12 || $general['level'] == 11) { $score *= 1.05; }
        // 종사 보정
        if($general['level'] == 2 && $general['no'] == $city['gen3']) { $score *= 1.05; }

        $rd = Util::randF();
        $r = CriticalRatioDomestic($general, 0);
        // 특기보정 : 인덕
        if($general['special'] == 20) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:선정을 <span class='ev_failed'>실패</span>하여 민심이 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($r['succ'] + $r['fail'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:선정을 <S>성공</>하여 민심이 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:민심이 <C>$score</> 상승했습니다. <1>$date</>";
        }

        $exp = $score * 7;
        $ded = $score * 10;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $score += $city['rate'];
        if($score > 100) { $score = 100; }
        // 민심 상승
        $query = "update city set rate='$score' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 군량 하락 내정보다 2배   지력경험    경험, 공헌 상승
        $general['rice'] -= $admin['develcost'] * 2;
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',rice='{$general['rice']}',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_5(&$general, $type) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);

    if($type == 1) { $dtype = "수비 강화"; $stype = "def"; }
    elseif($type == 2) { $dtype = "성벽 보수"; $stype = "wall"; }

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } else {
        // 민심 50 이하이면 50과 같게
        if($city['rate'] < GameConst::$develrate) { $city['rate'] = GameConst::$develrate; }
        $rate = $city['rate'] / 100;

        $score = getGeneralPower($general, true, true, true, false) * $rate;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 3 || $nation['type'] == 5 || $nation['type'] == 10 || $nation['type'] == 11) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 8  || $nation['type'] == 13) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 장군 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) { $score *= 1.05; }
        // 태수 보정
        if($general['level'] == 4 && $general['no'] == $city['gen1']) { $score *= 1.05; }

        $rd = Util::randF();
        $r = CriticalRatioDomestic($general, 1);
        // 특기보정 : 수비, 축성
        if($type == 1 && $general['special'] == 11) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($type == 2 && $general['special'] == 10) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }
        //민심 반영
        if($city['rate'] < 80) { $r['succ'] *= $city['rate'] / 80; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}를 <span class='ev_failed'>실패</span>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($r['succ'] + $r['fail'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}를 <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}를 하여 <C>$score</> 상승했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        if($city['front'] == 1){
            if($city['city'] == $nation['capital']){
                if($stype == 'def'){
                    $degrade = 1 - ($admin['year'] - $admin['startyear'] - 5) * 0.025;
                    $score *= Util::valueFit($degrade, 0.5, 1);
                }
                else{
                    $degrade = 1 - ($admin['year'] - $admin['startyear'] - 5) * 0.0375;
                    $score *= Util::valueFit($degrade, 0.25, 1);
                }
            }
            else{
                if($stype == 'def'){
                    $score *= 0.5;
                }
                else{
                    $score *= 0.25;
                }
            }
            
            
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $score += $city["$stype"];
        if($score > $city["{$stype}2"]) { $score = $city["{$stype}2"]; }
        // 내정 상승
        $query = "update city set {$stype}='$score' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 자금 하락, 무력 경험     경험, 공헌 상승
        $general['gold'] -= $admin['develcost'];
        $general['power2']++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',power2='{$general['power2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_7(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 정착 장려 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. 정착 장려 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 정착 장려 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 정착 장려 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost'] * 2) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. 정착 장려 실패. <1>$date</>";
    } else {
        $score = getGeneralLeadership($general, true, true, true);
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 2 || $nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 10) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 1 || $nation['type'] == 3 || $nation['type'] == 9)                        { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모 보정
        if($general['level'] == 12 || $general['level'] == 11) { $score *= 1.05; }
        // 종사 보정
        if($general['level'] == 2 && $general['no'] == $city['gen3']) { $score *= 1.05; }

        $rd = Util::randF();
        $r = CriticalRatioDomestic($general, 0);
        // 특기보정 : 인덕
        if($general['special'] == 20) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:장려를 <span class='ev_failed'>실패</span>하여 주민이 <C>{$score}0</>명 증가했습니다. <1>$date</>";
        } elseif($r['succ'] + $r['fail'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:장려를 <S>성공</>하여 주민이 <C>{$score}0</>명 증가했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:주민이 <C>{$score}0</>명 증가했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $score = $city['pop'] + ($score * 10);
        if($score > $city['pop2']) { $score = $city['pop2']; }
        // 민심 상승
        $query = "update city set pop='$score' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 군량 하락 내정보다 2배   지력경험    경험, 공헌 상승
        $general['rice'] -= $admin['develcost'] * 2;
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',rice='{$general['rice']}',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_8(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'develcost']);

    $dtype = "치안"; $stype = "secu";

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 강화 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 강화 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 강화 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 강화 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 강화 실패. <1>$date</>";
    } elseif($city['secu'] >= $city['secu2']) {
        $log[] = "<C>●</>{$admin['month']}월:치안은 충분합니다. $dtype 강화 실패. <1>$date</>";
    } else {
        // 민심 50 이하이면 50과 같게
        if($city['rate'] < GameConst::$develrate) { $city['rate'] = GameConst::$develrate; }
        $rate = $city['rate'] / 100;

        $score = getGeneralPower($general, true, true, true, false) * $rate;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 1 || $nation['type'] == 4) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 6 || $nation['type'] == 9) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 장군 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) { $score *= 1.05; }
        // 태수 보정
        if($general['level'] == 4 && $general['no'] == $city['gen1']) { $score *= 1.05; }

        $rd = Util::randF();
        $r = CriticalRatioDomestic($general, 1);
        // 특기보정 : 통찰
        if($general['special'] == 12) { $r['succ'] += 0.1; $score *= 1.1; $admin['develcost'] *= 0.8; }
        //민심 반영
        if($city['rate'] < 80) { $r['succ'] *= $city['rate'] / 80; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}을 <span class='ev_failed'>실패</span>하여 <C>$score</> 강화했습니다. <1>$date</>";
        } elseif($r['succ'] + $r['fail'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}을 <S>성공</>하여 <C>$score</> 강화했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:{$dtype}을 <C>$score</> 강화했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $score += $city["$stype"];
        if($score > $city["{$stype}2"]) { $score = $city["{$stype}2"]; }
        // 내정 상승
        $query = "update city set {$stype}='$score' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 자금 하락, 무력 경험     경험, 공헌 상승
        $general['gold'] -= $admin['develcost'];
        $general['power2']++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',power2='{$general['power2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_9(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month', 'develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 물자 조달 실패. <1>$date</>";
    } elseif($nation['level'] > 0 && $city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 물자 조달 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $city['nation'] == $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 물자 조달 실패. <1>$date</>";
    } else {
        if(rand() % 2 == 0) { $dtype = 0; $stype = "금"; }
        else                { $dtype = 1; $stype = "쌀"; }

        $score = getGeneralLeadership($general, true, true, true) 
            + getGeneralPower($general, true, true, true) 
            + getGeneralIntel($general, true, true, true);
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        $rd = rand() % 100;   // 현재 20%

        //버그방지
        if($score < 1) $score = 1;

        if(30 > $rd) {
            $score = CriticalScore($score, 1);
            $log[] = "<C>●</>{$admin['month']}월:조달을 <span class='ev_failed'>실패</span>하여 {$stype}을 <C>$score</> 조달했습니다. <1>$date</>";
        } elseif(40 > $rd) {
            $score = CriticalScore($score, 0);
            $log[] = "<C>●</>{$admin['month']}월:조달을 <S>성공</>하여 {$stype}을 <C>$score</> 조달했습니다. <1>$date</>";
        } else {
            $score = Util::round($score);
            $log[] = "<C>●</>{$admin['month']}월:{$stype}을 <C>$score</> 조달했습니다. <1>$date</>";
        }

        $exp = $score * 0.7 / 3;
        $ded = $score * 1.0 / 3;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 물자 상승
        if($dtype == 0) {
            $query = "update nation set gold=gold+'$score' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update nation set rice=rice+'$score' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        switch(Util::choiceRandomUsingWeight([$general['leader'], $general['power'], $general['intel']])) {
            case 0:
                $general['leader2']++;
                $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
                break;
            case 1:
                $general['power2']++;
                $query = "update general set resturn='SUCCESS',power2='{$general['power2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
                break;
            case 2:
                $general['intel2']++;
                $query = "update general set resturn='SUCCESS',intel2='{$general['intel2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
                break;
        }
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
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
    if($city['rate'] < 20) {
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
        $city['rate'] -= Util::round(($crew / $city['pop'])*100); 
    }
    else {
        $city['rate'] -= Util::round(($crew / 2 / $city['pop'])*100); 
    }
    if($city['rate'] < 0) { $city['rate'] = 0; }

    $db->update('city', [
        'pop'=>$db->sqleval('pop-%i', $crew),
        'rate'=>$city['rate']
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
    $log = uniqueItem($general, []);//TODO: uniqueItem 재 구현
    $actLog->pushGeneralActionLog($log, ActionLogger::RAWTEXT);
}

function process_13(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 훈련 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 훈련 실패. <1>$date</>";
    } elseif($general['crew'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:병사가 없습니다. 훈련 실패. <1>$date</>";
    } elseif($general['train'] >= GameConst::$maxTrainByCommand) {
        $log[] = "<C>●</>{$admin['month']}월:병사들은 이미 정예병사들입니다. <1>$date</>";
    } else {
        // 훈련시
        $score = Util::round(getGeneralLeadership($general, true, true, true) * 100 / $general['crew'] * GameConst::$trainDelta);

        $log[] = "<C>●</>{$admin['month']}월:훈련치가 <C>$score</> 상승했습니다. <1>$date</>";
        $exp = 100;
        $ded = 70;
        // 숙련도 증가
        addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], Util::round($general['crew']/100));
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 훈련치 변경
        $score += $general['train'];
        if($score > GameConst::$maxTrainByCommand) { $score = GameConst::$maxTrainByCommand; }
        $query = "update general set train='$score' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 사기 약간 감소
        $score = intval($general['atmos'] * GameConst::$atmosSideEffectByTraining);
        if($score < 0 ) { $score = 0; }
        $query = "update general set atmos='$score' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승        // 공헌도, 명성 상승
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
}

function process_14(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $nation['level']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 사기진작 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 사기진작 실패. <1>$date</>";
    } elseif($general['crew'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:병사가 없습니다. 사기진작 실패. <1>$date</>";
    } elseif($general['gold'] < $general['crew']/100) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 사기진작 실패. <1>$date</>";
    } elseif($general['atmos'] >= GameConst::$maxAtmosByCommand) {
        $log[] = "<C>●</>{$admin['month']}월:이미 사기는 하늘을 찌를듯 합니다. <1>$date</>";
    } else {
        $score = Util::round(getGeneralLeadership($general, true, true, true)*100 / $general['crew'] * GameConst::$atmosDelta);
        $gold = $general['gold'] - Util::round($general['crew']/100);

        $log[] = "<C>●</>{$admin['month']}월:사기치가 <C>$score</> 상승했습니다. <1>$date</>";
        $exp = 100;
        $ded = 70;
        // 숙련도 증가
        addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], Util::round($general['crew']/100));
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 사기치 변경        // 자금 감소        // 경험치 상승        // 공헌도, 명성 상승
        $score += $general['atmos'];
        if($score > GameConst::$maxAtmosByCommand) { $score = GameConst::$maxAtmosByCommand; }
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',atmos='$score',gold='$gold',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGenLog($general, $log);
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

function process_17(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    if($general['crew'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:병사가 없습니다. 소집해제 실패. <1>$date</>";
    } else {
        // 주민으로 돌아감
        $query = "update city set pop=pop+'{$general['crew']}' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set crew='0' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[0] = "<C>●</>{$admin['month']}월:병사들을 <R>소집해제</>하였습니다. <1>$date</>";

        // 경험, 공헌 상승
        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "update general set resturn='SUCCESS',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
}

function process_21(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);


    $db = DB::db();
    $admin = $gameStor->getValues(['year', 'month', 'develcost']);
    $city = CityConst::byID($general['city']);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];
    $destCity = CityConst::byID($destination);


    $josaRo = JosaUtil::pick($destCity->name, '로');
    if(!key_exists($destCity->id, $city->path)) {
        $log[] = "<C>●</>{$admin['month']}월:인접도시가 아닙니다. <G><b>{$destCity->name}</b></>{$josaRo} 이동 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 부족합니다. <G><b>{$destCity->name}</b></>{$josaRo} 이동 실패. <1>$date</>";
    } else {
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destCity->name}</b></>{$josaRo} 이동했습니다. <1>$date</>";
        $exp = 50;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);

        // 이동, 경험치 상승, 명성 상승, 사기 감소
        $general['leader2']++;
        $db->update('general',[
            'resturn'=>'SUCCESS',
            'gold'=>$db->sqleval('gold - %i',$admin['develcost']),
            'city'=>$destCity->id,
            'atmos'=>$db->sqleval('atmos*0.95'),
            'leader2'=>$general['leader2'],
            'experience'=>$db->sqleval('experience + %i',$exp)
        ], 'no = %i', $general['no']);

        if($general['level'] == 12) {
            $nation = getNationStaticInfo($general['nation']);

            if($nation['level'] == 0) {

                $db->update('general', [
                    'city'=>$destCity->id
                ], 'nation = %i', $general['nation']);

                $genlog = ["<C>●</>방랑군 세력이 <G><b>{$destCity->name}</b></>{$josaRo} 이동했습니다."];
                foreach($db->query('SELECT `no` FROM general WHERE nation=%i and `level`<12', $general['nation']) as $follower){
                    pushGenLog($follower, $genlog);
                }
            }
        }

        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
}

function process_26(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $troop = getTroop($general['troop']);

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select nation,name,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no,name,nation,city from general where troop='{$general['troop']}' and no!='{$general['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    if($general['nation'] != $city['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 집합 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 집합 실패. <1>$date</>";
    } elseif($general['no'] != $troop['no']) {
        $log[] = "<C>●</>{$admin['month']}월:부대장이 아닙니다. 집합 실패. <1>$date</>";
    } elseif($gencount == 0) {
        $log[] = "<C>●</>{$admin['month']}월:집합 가능한 부대원이 없습니다. 집합 실패. <1>$date</>";
    } else {
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>에서 집합을 실시했습니다. <1>$date</>";
        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        //부대원에게 로그 전달
        $josaRo = JosaUtil::pick($city['name'], '로');
        $genlog = ["<C>●</><S>{$troop['name']}</>의 부대원들은 <G><b>{$city['name']}</b></>{$josaRo} 집합되었습니다."];

        for($i=0; $i < $gencount; $i++) {
            $troopgen = MYDB_fetch_array($result);
            if($general['city'] != $troopgen['city']) {
                pushGenLog($troopgen, $genlog);
            }
        }

        // 같은 부대원 모두 집합
        $query = "update general set city='{$general['city']}' where troop='{$general['troop']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 경험치 명성 공헌 상승
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_28(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $youlog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    if($nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:방랑군입니다. 귀환 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 귀환 실패. <1>$date</>";
    } elseif(($general['level'] == 1 || $general['level'] >= 5) && $general['city'] == $nation['capital']) {
        $log[] = "<C>●</>{$admin['month']}월:이미 수도입니다. 귀환 실패. <1>$date</>";
    } else {
        if($general['level'] == 2) {
            $query = "select city,name from city where gen3='{$general['no']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($result);
        } elseif($general['level'] == 3) {
            $query = "select city,name from city where gen2='{$general['no']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($result);
        } elseif($general['level'] == 4) {
            $query = "select city,name from city where gen1='{$general['no']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($result);
        } else {
            $query = "select city,name from city where city='{$nation['capital']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($result);
        }

        $josaRo = JosaUtil::pick($city['name'], '로');
        $log[] = "<C>●</>{$admin['month']}월:<G>{$city['name']}</>{$josaRo} 귀환했습니다. <1>$date</>";
        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 귀환
        $query = "update general set city='{$city['city']}' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승        // 명성,공헌 상승
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',leader2='{$general['leader2']}',experience=experience+'$exp',dedication=dedication+'$ded' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
    }

    pushGenLog($general, $log);
    //pushGenLog($you, $youlog);
}

function process_30(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month', 'develcost']);

    $query = "select path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $dist = searchDistance($general['city'], 3, false);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select name from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $cost = $admin['develcost'] * 5;
    
    $josaRo = JosaUtil::pick($destcity['name'], '로');
    if($destination == $general['city']){
        $log[] = "<C>●</>{$admin['month']}월:같은 도시입니다. <G><b>{$destcity['name']}</b></>{$josaRo} 강행 실패. <1>$date</>";
    } elseif(!key_exists($destination, $dist)) {
        $log[] = "<C>●</>{$admin['month']}월:거리가 멉니다. <G><b>{$destcity['name']}</b></>{$josaRo} 강행 실패. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 부족합니다. <G><b>{$destcity['name']}</b></>{$josaRo} 강행 실패. <1>$date</>";
    } else {
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaRo} 강행했습니다. <1>$date</>";
        $exp = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);

        // 이동, 경험치 상승, 명성 상승, 병력/사기/훈련 감소
        $general['leader2']++;
        $query = "update general set resturn='SUCCESS',gold=gold-'$cost',city='$destination',crew=crew*0.95,atmos=atmos*0.9,train=train*0.95,leader2='{$general['leader2']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general['level'] == 12) {
            $nation = getNationStaticInfo($general['nation']);

            if($nation['level'] == 0) {
                $query = "update general set city='$destination' where nation='{$general['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                $query = "select no,name from general where nation='{$general['nation']}' and level<'12'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $gencount = MYDB_num_rows($result);
                $genlog = ["<C>●</>방랑군 세력이 <G><b>{$destcity['name']}</b></>{$josaRo} 강행했습니다."];
                for($j=0; $j < $gencount; $j++) {
                    $gen = MYDB_fetch_array($result);
                    pushGenLog($gen, $genlog);
                }
            }
        }

        $log = checkAbility($general, $log);
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
            $log[] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:{$city['rate']}, 장수:$gencount, 병력:$crew";
        } elseif($dist[$destination] == 2) {
            $josaUl = JosaUtil::pick($city['name'], '을');
            $alllog[] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>{$josaUl} 살피는 것 같습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 어느정도 정보를 얻었습니다. <1>$date</>";
            $log[] = "【<M>첩보</>】농업:{$city['agri']}, 상업:{$city['comm']}, 치안:{$city['secu']}, 수비:{$city['def']}, 성벽:{$city['wall']}";
            $log[] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:{$city['rate']}, 장수:$gencount, 병력:$crew";
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
            $log[] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:{$city['rate']}, 장수:$gencount, 병력:$crew";

            if($general['nation'] != 0 && $city['nation'] != 0) {
                $query = "select name,tech from nation where nation='{$city['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $yourTech = MYDB_fetch_array($result);

                $query = "select tech from nation where nation='{$general['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $myTech = MYDB_fetch_array($result);

                $diff = $yourTech['tech'] - $myTech['tech'];   // 차이
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

function process_41(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month', 'develcost']);

    $ratio = rand() % 100;
    $exp = $general['crew'] / 400;
    $crewexp = $general['crew'] * $general['train'] * $general['atmos'] / 20 / 10000;
    // 랜덤치
    $exp = Util::round($exp * (80 + rand() % 41)/100);   // 80 ~ 120%
    $crewexp = Util::round($crewexp * (80 + rand() % 41)/100);   // 80 ~ 120%

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ratio = CharCritical($ratio, $general['personal']);

    if($general['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 단련 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 단련 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. 단련 실패. <1>$date</>";
    } elseif($general['train'] < 40) {
        $log[] = "<C>●</>{$admin['month']}월:훈련이 너무 낮습니다. 단련 실패. <1>$date</>";
    } elseif($general['atmos'] < 40) {
        $log[] = "<C>●</>{$admin['month']}월:사기가 너무 낮습니다. 단련 실패. <1>$date</>";
    } elseif($crewexp == 0) {
        $log[] = "<C>●</>{$admin['month']}월:병사가 모자랍니다. 단련 실패. <1>$date</>";
    } else {
        $crewstr = GameUnitConst::allType()[GameUnitConst::byID($general['crewtype'])->armType];

        if($ratio < 33) {
            // 숙련도 증가
            addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], $crewexp);
            $log[] = "<C>●</>{$admin['month']}월:$crewstr 숙련도 향상이 <span class='ev_failed'>지지부진</span>했습니다. <1>$date</>";
        } elseif($ratio < 66) {
            $exp = $exp * 2;
            // 숙련도 증가
            addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], $crewexp * 2);
            $log[] = "<C>●</>{$admin['month']}월:$crewstr 숙련도가 향상되었습니다. <1>$date</>";
        } else {
            $exp = $exp * 3;
            // 숙련도 증가
            addGenDex($general['no'], GameConst::$maxAtmosByCommand, GameConst::$maxTrainByCommand, $general['crewtype'], $crewexp * 3);
            $log[] = "<C>●</>{$admin['month']}월:$crewstr 숙련도가 <S>일취월장</>했습니다. <1>$date</>";
        }

        // 경험치 상승    // 명성 상승
        $query = "update general set resturn='SUCCESS',gold=gold-'{$admin['develcost']}',rice=rice-'{$admin['develcost']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
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
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',experience=experience+'$exp' where no='{$general['no']}'";
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
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',experience=experience+'$exp' where no='{$general['no']}'";
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
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',experience=experience+'$exp2' where no='{$general['no']}'";
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
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 25:
        $log[] = "<C>●</>{$admin['month']}월:곰에게 할퀴어 크게 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 30 + 20;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 26:
        $log[] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구하다가 죽을뻔 했습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 50 + 30;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',experience=experience+'$exp2' where no='{$general['no']}'";
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

    //특기 보정 : 거상
    if($general['special'] == 30 && $type != 0) { $cost *= 0.5; }

    if($city['trade'] == 0 && $general['special'] != 30) {
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

    if($city['trade'] == 0 && ($general['special'] == 30 || $general['npc'] >= 2)) {
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
        //특기 보정 : 거상
        if($general['special'] == 30 && $city['trade'] > 100) { $cost = $amount * (6 * $city['trade']/100 - 5); } // 이익인 경우 5배 이득
        if($general['special'] == 30 && $city['trade'] < 100) { $cost = $amount * (0.2 * $city['trade']/100 + 0.8); } // 손해인 경우 1/5배 손해
        $tax = $cost * GameConst::$exchangeFee;
        $cost = $cost - $tax;
    } elseif($type == 2) {
        $dtype = "군량 구입";
        $cost = $amount * $city['trade'] / 100;
        //특기 보정 : 거상
        if($general['special'] == 30 && $city['trade'] < 100) { $cost = $amount * (6 * $city['trade']/100 - 5); } // 이익인 경우 5배 이득
        if($general['special'] == 30 && $city['trade'] > 100) { $cost = $amount * (0.2 * $city['trade']/100 + 0.8); } // 손해인 경우 1/5배 손해
        $tax = $cost * GameConst::$exchangeFee;
        $cost = $cost + $tax;
        if($general['gold'] < $cost) {
            $cost = $general['gold'];
            $tax = $cost * GameConst::$exchangeFee;
            $amount = ($cost-$tax) * 100 / $city['trade'];
            //특기 보정 : 거상
            if($general['special'] == 30 && $city['trade'] < 100) { $amount = ($cost-$tax) / (6 * $city['trade']/100 - 5); } // 이익인 경우 5배 이득
            if($general['special'] == 30 && $city['trade'] > 100) { $amount = ($cost-$tax) / (0.2 * $city['trade']/100 + 0.8); } // 손해인 경우 1/5배 손해
        }
    }

    $cost = Util::round($cost);
    $amount = Util::round($amount);
    $tax = Util::round($tax);

    if($city['trade'] == 0 && $general['special'] != 30 && $general['npc'] < 2) {
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

