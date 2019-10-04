<?php
namespace sammo;

use Constraint\Constraint;

/**
 * 내정 커맨드 사용시 성공 확률 계산
 * 
 * @param array $general 장수 정보
 * @param string $type 내정 커맨드 타입, 'leader' = 통솔 기반, 'power' = 무력 기반, 'intel' = 지력 기반
 * 
 * @return array 계산된 실패, 성공 확률 ('success' => 성공 확률, 'fail' => 실패 확률)
 */
function CriticalRatioDomestic(General $general, string $type) {
    $leader = $general->getLeadership(false, true, true, false);
    $power = $general->getPower(false, true, true, false);
    $intel = $general->getIntel(false, true, true, false);

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
    case 'leader': $ratio = $avg / $leader; break;
    case 'power': $ratio = $avg / $power;  break;
    case 'intel': $ratio = $avg / $intel; break;
    default:
        throw new MustNotBeReachedException();
    }
    $ratio = min($ratio, 1.2);

    $fail = pow($ratio / 1.2, 1.4) - 0.3;
    $success = pow($ratio / 1.2, 1.5) - 0.25;

    $fail = Util::valueFit($fail, 0, 0.5);
    $success = Util::valueFit($success, 0, 0.5);


    return array(
        'success'=>$success,
        'fail'=>$fail
    );
}

function calcLeadershipBonus($generalLevel, $nationLevel):int{
    if($generalLevel == 12) {
        $lbonus = $nationLevel * 2;
    } elseif($generalLevel >= 5) {
        $lbonus = $nationLevel;
    } else {
        $lbonus = 0;
    }
    return $lbonus;
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
    $lbonus = calcLeadershipBonus($general['level'], $nationLevel);
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

