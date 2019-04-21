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
    if ($type == 'success') {
        return Util::randRange(2.2, 3.0);
    }
    if ($type == 'fail') {
        return  Util::randRange(0.2, 0.4);
    }
    return 1;
}

