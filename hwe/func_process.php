<?php
namespace sammo;

/**
 * 내정 커맨드 사용시 성공 확률 계산
 * 
 * @param General $general 장수 정보
 * @param string $type 내정 커맨드 타입, 'leadership' = 통솔 기반, 'strength' = 무력 기반, 'intel' = 지력 기반
 * 
 * @return array 계산된 실패, 성공 확률 ('success' => 성공 확률, 'fail' => 실패 확률)
 */
function CriticalRatioDomestic(General $general, string $type) {
    $leadership = $general->getLeadership(false, true, true, false);
    $strength = $general->getStrength(false, true, true, false);
    $intel = $general->getIntel(false, true, true, false);

    $avg = ($leadership+$strength+$intel) / 3;
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
    case 'leadership': $ratio = $avg / $leadership; break;
    case 'strength': $ratio = $avg / $strength;  break;
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

function calcLeadershipBonus($officerLevel, $nationLevel):int{
    if($officerLevel == 12) {
        $lbonus = $nationLevel * 2;
    } elseif($officerLevel >= 5) {
        $lbonus = $nationLevel;
    } else {
        $lbonus = 0;
    }
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

