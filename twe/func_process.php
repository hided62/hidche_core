<?php

function CriticalRatio($leader, $power, $intel, $type=0) {
    $avg = ($leader+$power+$intel) / 3;
    // 707010장수 18/21% 706515장수 16/27% 706020장수 14/33% xx50xx장수 10/50%
    switch($type) {
    case 0: $ratio = $avg / $leader; break;
    case 1: $ratio = $avg / $power;  break;
    case 2: $ratio = $avg / $intel; break;
    }
    if($ratio > 1) $ratio = 1;

    $r['fail'] = (0.2 / $ratio - 0.1) * 100;
    $r['succ'] = ($ratio - 0.5) * 100;

    if($r['fail'] < 0) { $r['fail'] = 0; }
    $r['succ'] += $r['fail'];
    if($r['succ'] > 100) { $r['succ'] = 100; }

    return $r;
}

function CriticalScore($score, $type) {
    switch($type) {
    case 0:
        $ratio = (rand()%9 + 20)/10;   // 2.0~2.8
        break;
    case 1:
        $ratio = (rand()%3 + 2)/10;     // 0.2~0.4
        break;
    }
    return round($score * $ratio);
}

function process_1($connect, &$general, $type) {
    global $_develrate;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    if($type == 1)     { $dtype = "농지 개간"; $atype = "을"; $btype = "은"; $stype = "agri"; }
    elseif($type == 2) { $dtype = "상업 투자"; $atype = "를"; $btype = "는"; $stype = "comm"; }

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } elseif($city["$stype"] >= $city["$stype"."2"]) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}{$btype} 충분합니다. $dtype 실패. <1>$date</>";
    } else {
        // 민심 50 이하이면 50과 같게
        if($city['rate'] < $_develrate) { $city['rate'] = $_develrate; }
        $rate = $city['rate'] / 100;

        $score = ($general['intel'] * (100 - $general['injury'])/100 + getBookEff($general['book'])) * $rate;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 2 || $nation['type'] == 12) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 8 || $nation['type'] == 11) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 모사 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 9 || $general['level'] == 7 || $general['level'] == 5) { $score *= 1.05; }
        // 군사 보정
        if($general['level'] == 3 && $general['no'] == $city[gen2]) { $score *= 1.05; }

        $rd = rand() % 100;
        $r = CriticalRatio($general['leader']+getHorseEff($general['horse'])+$lbonus, $general['power']+getWeapEff($general['weap']), $general['intel']+getBookEff($general['book']), 2);

        // 특기보정 : 경작, 상재
        if($type == 1 && $general['special'] == 1) { $r['succ'] += 10; $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($type == 2 && $general['special'] == 2) { $r['succ'] += 10; $score *= 1.1; $admin['develcost'] *= 0.8; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}{$atype} <O>실패</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($city['rate'] >= 80 && $r['succ'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}{$atype} <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}{$atype} 하여 <C>$score</> 상승했습니다. <1>$date</>";
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
        // 자금 하락, 경험치 상승
        $general['gold'] -= $admin['develcost'];
        $general[intel2]++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',intel2='$general[intel2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_3($connect, &$general) {
    global $_develrate;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dtype = "기술 연구";

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } else {
        $score = ($general['intel'] * (100 - $general['injury'])/100 + getBookEff($general['book']));
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 3 || $nation['type'] == 13)                                                                   { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 5 || $nation['type'] == 6 || $nation['type'] == 7 || $nation['type'] == 8 || $nation['type'] == 12) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 모사 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 9 || $general['level'] == 7 || $general['level'] == 5) { $score *= 1.05; }

        $rd = rand() % 100;
        $r = CriticalRatio($general['leader']+getHorseEff($general['horse'])+$lbonus, $general['power']+getWeapEff($general['weap']), $general['intel']+getBookEff($general['book']), 0);
        // 특기보정 : 발명
        if($general['special'] == 3) { $score *= 1.1; $admin['develcost'] *= 0.8; $r['succ'] += 10; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}를 <O>실패</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($city['rate'] >= 80 && $r['succ'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}를 <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}를 하여 <C>$score</> 상승했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 부드러운 기술 제한
        if(TechLimit($admin['startyear'], $admin['year'], $nation['tech'])) { $score = floor($score/4); }

        //장수수 구함
        $query = "select no from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        if($gencount < 10) $gencount = 10;
        // 내정 상승
        $query = "update nation set totaltech=totaltech+'$score',tech=totaltech/'$gencount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 자금 하락, 경험치 상승        // 공헌도, 명성 상승 = $score * 10
        $general['gold'] -= $admin['develcost'];

        $general[intel2]++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',intel2='$general[intel2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_4($connect, &$general) {
    global $_develrate;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 주민 선정 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. 주민 선정 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 주민 선정 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 주민 선정 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. 주민 선정 실패. <1>$date</>";
    } elseif($city['rate'] >= 100) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:민심은 충분합니다. 주민 선정 실패. <1>$date</>";
    } else {
        $score = ($general['leader'] * (100 - $general['injury'])/100 + getHorseEff($general['horse']) + $lbonus) / 10;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 2 || $nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 10) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 1 || $nation['type'] == 3 || $nation['type'] == 9)                        { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모 보정
        if($general['level'] == 12 || $general['level'] == 11) { $score *= 1.05; }
        // 시중 보정
        if($general['level'] == 2 && $general['no'] == $city[gen3]) { $score *= 1.05; }

        $rd = rand() % 100;
        $r = CriticalRatio($general['leader']+getHorseEff($general['horse'])+$lbonus, $general['power']+getWeapEff($general['weap']), $general['intel']+getBookEff($general['book']), 0);
        // 특기보정 : 인덕
        if($general['special'] == 20) { $r['succ'] += 10; $admin['develcost'] *= 0.8; $score *= 1.1; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:선정을 <O>실패</>하여 민심이 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($r['succ'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:선정을 <S>성공</>하여 민심이 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:민심이 <C>$score</> 상승했습니다. <1>$date</>";
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
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',rice='{$general['rice']}',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_5($connect, &$general, $type) {
    global $_develrate;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    if($type == 1) { $dtype = "수비 강화"; $stype = "def"; }
    elseif($type == 2) { $dtype = "성벽 보수"; $stype = "wall"; }

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } elseif($city["$stype"] >= $city["$stype"."2"]) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}는 충분합니다. $dtype 실패. <1>$date</>";
    } else {
        // 민심 50 이하이면 50과 같게
        if($city['rate'] < $_develrate) { $city['rate'] = $_develrate; }
        $rate = $city['rate'] / 100;

        $score = ($general['power'] * (100 - $general['injury'])/100 + getWeapEff($general['weap'])) * $rate;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 3 || $nation['type'] == 5 || $nation['type'] == 10 || $nation['type'] == 11) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 8  || $nation['type'] == 13) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 장군 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) { $score *= 1.05; }
        // 태수 보정
        if($general['level'] == 4 && $general['no'] == $city[gen1]) { $score *= 1.05; }

        $rd = rand() % 100;   // 현재 20%
        $r = CriticalRatio($general['leader']+getHorseEff($general['horse'])+$lbonus, $general['power']+getWeapEff($general['weap']), $general['intel']+getBookEff($general['book']), 0);
        // 특기보정 : 수비, 축성
        if($type == 1 && $general['special'] == 11) { $r['succ'] += 10; $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($type == 2 && $general['special'] == 10) { $r['succ'] += 10; $score *= 1.1; $admin['develcost'] *= 0.8; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}를 <O>실패</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } elseif($city['rate'] >= 80 && $r['succ'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}를 <S>성공</>하여 <C>$score</> 상승했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}를 하여 <C>$score</> 상승했습니다. <1>$date</>";
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
        $general[power2]++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',power2='$general[power2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_7($connect, &$general) {
    global $_develrate;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 정착 장려 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. 정착 장려 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 정착 장려 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 정착 장려 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost'] * 2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. 정착 장려 실패. <1>$date</>";
    } elseif($city['pop'] >= $city[pop2]) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 포화상태입니다. 정착 장려 실패. <1>$date</>";
    } else {
        $score = $general['leader'] * (100 - $general['injury'])/100 + getHorseEff($general['horse']) + $lbonus;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 2 || $nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 10) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 1 || $nation['type'] == 3 || $nation['type'] == 9)                        { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모 보정
        if($general['level'] == 12 || $general['level'] == 11) { $score *= 1.05; }
        // 시중 보정
        if($general['level'] == 2 && $general['no'] == $city[gen3]) { $score *= 1.05; }

        $rd = rand() % 100;   // 현재 20%
        $r = CriticalRatio($general['leader']+getHorseEff($general['horse'])+$lbonus, $general['power']+getWeapEff($general['weap']), $general['intel']+getBookEff($general['book']), 0);
        // 특기보정 : 인덕
        if($general['special'] == 20) { $r['succ'] += 10; $score *= 1.1; $admin['develcost'] *= 0.8; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:장려를 <O>실패</>하여 주민이 <C>{$score}0</>명 증가했습니다. <1>$date</>";
        } elseif($r['succ'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:장려를 <S>성공</>하여 주민이 <C>{$score}0</>명 증가했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:주민이 <C>{$score}0</>명 증가했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $score = $city['pop'] + ($score * 10);
        if($score > $city[pop2]) { $score = $city[pop2]; }
        // 민심 상승
        $query = "update city set pop='$score' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 군량 하락 내정보다 2배   지력경험    경험, 공헌 상승
        $general['rice'] -= $admin['develcost'] * 2;
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',rice='{$general['rice']}',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_8($connect, &$general) {
    global $_develrate;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dtype = "치안"; $stype = "secu";

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 강화 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 방랑군은 불가능합니다. $dtype 강화 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 강화 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 강화 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 강화 실패. <1>$date</>";
    } elseif($city['secu'] >= $city[secu2]) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:치안은 충분합니다. $dtype 강화 실패. <1>$date</>";
    } else {
        // 민심 50 이하이면 50과 같게
        if($city['rate'] < $_develrate) { $city['rate'] = $_develrate; }
        $rate = $city['rate'] / 100;

        $score = ($general['power'] * (100 - $general['injury'])/100 + getWeapEff($general['weap'])) * $rate;
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        // 국가보정
        if($nation['type'] == 1 || $nation['type'] == 4) { $score *= 1.1; $admin['develcost'] *= 0.8; }
        if($nation['type'] == 6 || $nation['type'] == 9) { $score *= 0.9; $admin['develcost'] *= 1.2; }

        // 군주, 참모, 장군 보정
        if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) { $score *= 1.05; }
        // 태수 보정
        if($general['level'] == 4 && $general['no'] == $city[gen1]) { $score *= 1.05; }

        $rd = rand() % 100;   // 현재 20%
        $r = CriticalRatio($general['leader']+getHorseEff($general['horse'])+$lbonus, $general['power']+getWeapEff($general['weap']), $general['intel']+getBookEff($general['book']), 0);
        // 특기보정 : 통찰
        if($general['special'] == 12) { $r['succ'] += 10; $score *= 1.1; $admin['develcost'] *= 0.8; }

        //버그방지
        if($score < 1) $score = 1;

        if($r['fail'] > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}을 <O>실패</>하여 <C>$score</> 강화했습니다. <1>$date</>";
        } elseif($city['rate'] >= 80 && $r['succ'] > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}을 <S>성공</>하여 <C>$score</> 강화했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$dtype}을 <C>$score</> 강화했습니다. <1>$date</>";
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
        $general[power2]++;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',power2='$general[power2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_9($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 물자 조달 실패. <1>$date</>";
    } elseif($nation['level'] > 0 && $city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 물자 조달 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $city['nation'] == $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 물자 조달 실패. <1>$date</>";
    } else {
        if(rand() % 2 == 0) { $dtype = 0; $stype = "금"; }
        else                { $dtype = 1; $stype = "쌀"; }

        $score = (($general['leader']+$general['power']+$general['intel']) * (100 - $general['injury'])/100 + getHorseEff($general['horse'])+$lbonus+getWeapEff($general['weap'])+getBookEff($general['book']));
        $score = $score * (100 + $general['explevel']/5)/100;
        $score = $score * (80 + rand() % 41)/100;   // 80 ~ 120%

        $rd = rand() % 100;   // 현재 20%

        //버그방지
        if($score < 1) $score = 1;

        if(30 > $rd) {
            $score = CriticalScore($score, 1);
            $log[count($log)] = "<C>●</>{$admin['month']}월:조달을 <O>실패</>하여 {$stype}을 <C>$score</> 조달했습니다. <1>$date</>";
        } elseif(40 > $rd) {
            $score = CriticalScore($score, 0);
            $log[count($log)] = "<C>●</>{$admin['month']}월:조달을 <S>성공</>하여 {$stype}을 <C>$score</> 조달했습니다. <1>$date</>";
        } else {
            $score = round($score);
            $log[count($log)] = "<C>●</>{$admin['month']}월:{$stype}을 <C>$score</> 조달했습니다. <1>$date</>";
        }

        $exp = $score * 0.7;
        $ded = $score * 1.0;

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

        switch(rand()%3) {
            case 0:
                $general[leader2]++;
                $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
                break;
            case 1:
                $general[power2]++;
                $query = "update general set resturn='SUCCESS',power2='$general[power2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
                break;
            case 2:
                $general[intel2]++;
                $query = "update general set resturn='SUCCESS',intel2='$general[intel2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
                break;
        }
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_11($connect, &$general, $type) {
    global $_defaultatmos, $_defaulttrain, $_defaultatmos2, $_defaulttrain2;
    $date = substr($general['turntime'],11,5);

    if($type == 1) { $defaultatmos = $_defaultatmos; $defaulttrain = $_defaulttrain; }
    else { $defaultatmos = $_defaultatmos2; $defaulttrain = $_defaulttrain2; }

    $query = "select year,month,startyear from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select level,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general[turn0]);
    $armtype = $command[2];
    $crew = $command[1];

    if($armtype != $general['crewtype']) { $general['crew'] = 0; $general['train'] = $defaulttrain; $general['atmos'] = $defaultatmos; }

    if($crew*100 + $general['crew'] > (floor($general['leader'] * (100 - $general['injury'])/100)+getHorseEff($general['horse'])+$lbonus)*100) { $crew = round(((floor($general['leader'] * (100 - $general['injury'])/100)+getHorseEff($general['horse'])+$lbonus)*100 - $general['crew'])/100, 0); }
    if($crew < 0) { $crew = 0; }
    $cost = $crew * getCost($connect, $armtype);
    //기술로 가격
    $cost *= getTechCost($nation['tech']);
    //성격 보정
    $cost = CharCost($cost, $general['personal']);
    $cost = round($cost);

    //특기 보정 : 보병, 궁병, 기병, 귀병, 공성, 징병
    if(floor($armtype/10) == 0 && $general[special2] == 50) { $cost *= 0.9; }
    if(floor($armtype/10) == 1 && $general[special2] == 51) { $cost *= 0.9; }
    if(floor($armtype/10) == 2 && $general[special2] == 52) { $cost *= 0.9; }
    if(floor($armtype/10) == 3 && $general[special2] == 40) { $cost *= 0.9; }
    if(floor($armtype/10) == 4 && $general[special2] == 43) { $cost *= 0.9; }
    if($general[special2] == 72) { $cost *= 0.5; }

    if($type == 1) { $dtype = "징병"; }
    elseif($type == 2) { $dtype = "모병"; $cost *= 2; }
    if($general['crew'] != 0) { $dtype = "추가".$dtype; }

    //현재 가능한지 검사
    switch($armtype) {
        case 0:  case 10:  case 20:  case 30: case 35: // 보병 궁병 기병 귀병 남귀병
            $sel = 0; break;

        case  1: $sel = 1; $rg =  2; break; // 청주병(중원)
        case  2: $sel = 1; $rg =  7; break; // 수병(오월)
        case  3: $sel = 2; $ct = 64; break; // 자객병(저)
        case  4: $sel = 2; $ct =  3; break; // 근위병(낙양)
        case  5: $sel = 1; $rg =  5; break; // 등갑병(남중)

        case 11: $sel = 1; $rg =  8; break; // 궁기병(동이)
        case 12: $sel = 1; $rg =  4; break; // 연노병(서촉)
        case 13: $sel = 2; $ct =  6; break; // 강궁병(양양)
        case 14: $sel = 2; $ct =  7; break; // 석궁병(건업)

        case 21: $sel = 1; $rg =  1; break; // 백마병(하북)
        case 22: $sel = 1; $rg =  3; break; // 중장기병(서북)
        case 23: $sel = 2; $ct = 65; break; // 돌격기병(흉노)
        case 24: $sel = 2; $ct = 63; break; // 철기병(강)
        case 25: $sel = 2; $ct = 67; break; // 수렵기병(산월)
        case 26: $sel = 2; $ct = 66; break; // 맹수병(남만)
        case 27: $sel = 2; $ct =  2; break; // 호표기병(허창)

        case 31: $sel = 1; $rg =  6; break; // 신귀병(초)
        case 32: $sel = 2; $ct = 68; break; // 백귀병(오환)
        case 33: $sel = 2; $ct = 69; break; // 흑귀병(왜)
        case 34: $sel = 2; $ct =  4; break; // 악귀병(장안)
        case 36: $sel = 2; $ct =  3; break; // 황귀병(낙양)
        case 37: $sel = 2; $ct =  5; break; // 천귀병(성도)
        case 38: $sel = 2; $ct =  1; break; // 마귀병(업)

        case 40: $sel = 0; break; // 정란
        case 41: $sel = 0; break; // 충차
        case 42: $sel = 2; $ct =  1; break; // 벽력거(업)
        case 43: $sel = 2; $ct =  5; break; // 목우(성도)

        default: $sel = 0; $armtype = 0; break;
    }
    if($sel == 0) {
        // 남귀병은 기술1등급부터
        // 충차는 기술1등급부터
        if($armtype == 35 && $nation['tech'] < 1000) {
            $cnt = 0;
        } elseif($armtype == 41 && $nation['tech'] < 1000) {
            $cnt = 0;
        } else {
            $cnt = 1;
        }
    } elseif($sel == 1) {
        $query = "select city,level from city where nation='{$general['nation']}' and region='$rg'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $rgcity = MYDB_fetch_array($result);
        // 기술 1000 이상부터 지역병
        if($cnt > 0 && $nation['tech'] < 1000) {
            $cnt = 0;
        }
    } else {
        $query = "select city,level from city where nation='{$general['nation']}' and city='$ct'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $ctcity = MYDB_fetch_array($result);
        // 기술 2000 이상부터 이민족병
        if($cnt > 0 && $ctcity['level'] == 4 && $nation['tech'] < 2000) {
            $cnt = 0;
        }
        // 기술 3000 이상부터 특수병
        if($cnt > 0 && $ctcity['level'] == 8 && $nation['tech'] < 3000) {
            $cnt = 0;
        }
    }
    if($cnt > 0) { $valid = 1; }
    else { $valid = 0; }

    // 초반 제한중 차병 불가
    if($admin['year'] < $admin['startyear']+3 && floor($armtype/10) == 4) {
        $valid = 0;
    }

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. $dtype 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($crew <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:더이상 $dtype 할 수 없습니다. $dtype 실패. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. $dtype 실패. <1>$date</>";
    } elseif($general['rice'] < $crew) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. $dtype 실패. <1>$date</>";
    } elseif($valid == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 $dtype 할 수 없는 병종입니다. $dtype 실패. <1>$date</>";
    } elseif($city['pop']-30000 < $crew*100) {    // 주민 30000명 이상만 가능
        $log[count($log)] = "<C>●</>{$admin['month']}월:주민이 모자랍니다. $dtype 실패. <1>$date</>";
    } elseif($city['rate'] < 20) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:민심이 낮아 주민들이 도망갑니다. $dtype 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:".getTypename($armtype)."을(를) <C>{$crew}00</>명 {$dtype}했습니다. <1>$date</>";
        $exp = $crew;
        $ded = $crew;
        // 숙련도 증가
        addGenDex($connect, $general['no'], $armtype, $crew);
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $atmos = round(($general['atmos'] * $general['crew'] + $defaultatmos * $crew*100) / ($general['crew'] + $crew*100));
        $train = round(($general['train'] * $general['crew'] + $defaulttrain * $crew*100) / ($general['crew'] + $crew*100));
        $general['crew'] += $crew*100;
        $general['gold'] -= $cost;
        // 주민수 감소        // 민심 김소
        if($type == 1) { $city['rate'] = $city['rate'] - round(($crew*100 / $city['pop'])*100); }
        else { $city['rate'] = $city['rate'] - round(($crew*100 / $city['pop'])*50); }
        if($city['rate'] < 0) { $city['rate'] = 0; }
        $query = "update city set pop=pop-({$crew}*100),rate='{$city['rate']}' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 통솔경험, 병종 변경, 병사수 변경, 훈련치 변경, 사기치 변경, 자금 군량 하락, 공헌도, 명성 상승
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',crewtype='$armtype',crew='{$general['crew']}',train='$train',atmos='$atmos',gold='{$general['gold']}',rice=rice-'$crew',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_13($connect, &$general) {
    global $_maxtrain, $_training, $_atmosing;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select level from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 훈련 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 훈련 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 훈련 실패. <1>$date</>";
    } elseif($general['crew'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사가 없습니다. 훈련 실패. <1>$date</>";
    } elseif($general['train'] >= $_maxtrain) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사들은 이미 정예병사들입니다. <1>$date</>";
//    } elseif(floor($general['crewtype']/10) == 4) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:병기는 훈련이 불가능합니다. <1>$date</>";
    } else {
        // 훈련시
        $score = round((floor($general['leader'] * (100 - $general['injury'])/100)+getHorseEff($general['horse'])+$lbonus)*100 / $general['crew'] * $_training);

        $log[count($log)] = "<C>●</>{$admin['month']}월:훈련치가 <C>$score</> 상승했습니다. <1>$date</>";
        $exp = 100;
        $ded = 70;
        // 숙련도 증가
        addGenDex($connect, $general['no'], $general['crewtype'], round($general['crew']/100));
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 훈련치 변경
        $score += $general['train'];
        if($score > $_maxtrain) { $score = $_maxtrain; }
        $query = "update general set train='$score' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 사기 약간 감소
        $score = floor($general['atmos'] * $_atmosing);
        if($score < 0 ) { $score = 0; }
        $query = "update general set atmos='$score' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승        // 공헌도, 명성 상승
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_14($connect, &$general) {
    global $_maxatmos, $_training;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select level from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 사기진작 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 사기진작 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 사기진작 실패. <1>$date</>";
    } elseif($general['crew'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사가 없습니다. 사기진작 실패. <1>$date</>";
    } elseif($general['gold'] < $general['crew']/100) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 사기진작 실패. <1>$date</>";
    } elseif($general['atmos'] >= $_maxatmos) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 사기는 하늘을 찌를듯 합니다. <1>$date</>";
//    } elseif(floor($general['crewtype']/10) == 4) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:병기는 사기 진작이 불가능합니다. <1>$date</>";
    } else {
        $score = round((floor($general['leader'] * (100 - $general['injury'])/100)+getHorseEff($general['horse'])+$lbonus)*100 / $general['crew'] * $_training);
        $gold = $general['gold'] - round($general['crew']/100);

        $log[count($log)] = "<C>●</>{$admin['month']}월:사기치가 <C>$score</> 상승했습니다. <1>$date</>";
        $exp = 100;
        $ded = 70;
        // 숙련도 증가
        addGenDex($connect, $general['no'], $general['crewtype'], round($general['crew']/100));
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 사기치 변경        // 자금 감소        // 경험치 상승        // 공헌도, 명성 상승
        $score += $general['atmos'];
        if($score > $_maxatmos) { $score = $_maxatmos; }
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',atmos='$score',gold='$gold',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_15($connect, &$general) {
    global $_maxatmos, $_maxtrain;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($general['term']%100 == 15) {
        $term = floor($general['term']/100) + 1;
        $code = $term * 100 + 15;
    } else {
        $term = 1;
        $code = 100 + 15;
    }

    $cost = round($general['crew']/100 * 3 * getTechCost($nation['tech']));

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 전투태세 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 전투태세 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 전투태세 실패. <1>$date</>";
    } elseif($general['crew'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사가 없습니다. 전투태세 실패. <1>$date</>";
    } elseif($general['atmos'] >= 90 && $general['train'] >= 90) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 병사들은 날쌔고 용맹합니다. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 전투태세 실패. <1>$date</>";
    } elseif($term < 3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사들을 열심히 훈련중... ({$term}/3) <1>$date</>";

        $query = "update general set resturn='ONGOING',term={$code} where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        //기술로 가격
        $gold = $general['gold'] - $cost;

        $log[count($log)] = "<C>●</>{$admin['month']}월:전투태세 완료! <1>$date</>";
        $exp = 100 * 3;
        $ded = 70 * 3;
        // 숙련도 증가
        addGenDex($connect, $general['no'], $general['crewtype'], round($general['crew']/100 * 3));
        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 훈련,사기치 변경        // 자금 감소        // 경험치 상승        // 공헌도, 명성 상승
        $general[leader2]+=3;
        $query = "update general set resturn='SUCCESS',term='0',atmos='95',train='95',gold='$gold',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_16($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,war,tricklimit,tech from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select path,nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $path = explode("|", $city['path']);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select nation,tricklimit,tech from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    for($i=0; $i < count($path); $i++) {
        if($path[$i] == $destination) { $valid = 1; }
    }

    if($admin['year'] < $admin['startyear']+3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 초반 제한중입니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
//    } elseif($city['supply'] == 0) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif(!$valid) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:인접도시가 아닙니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($general['crew'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사가 없습니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($general['rice'] <= round($general['crew']/100)) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($dip['state'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:교전중인 국가가 아닙니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:본국에서만 출병가능합니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($nation['war'] == 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 전쟁 금지입니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:본국입니다. <G><b>{$destcity['name']}</b></>(으)로 출병 실패. <1>$date</>";
        pushGenLog($general, $log);
        process_21($connect, $general);
        return;
    } else {
        // 전쟁 표시
        $query = "update city set state=43,term=3 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 숙련도 증가
        addGenDex($connect, $general['no'], $general['crewtype'], round($general['crew']/100));
        // 전투 처리
        $dead = processWar($connect, $general, $destcity);

        // 기술력 따라서 보정
        $dead['att'] = round($dead['att'] * getTechCost($nation['tech']));
        $dead['def'] = round($dead['def'] * getTechCost($dnation['tech']));

        // 사상자 누적
        if($nation['nation'] > 0 && $dnation['nation'] > 0) {
            $query = "update diplomacy set dead=dead+'{$dead['att']}' where me='{$nation['nation']}' and you='{$dnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "update diplomacy set dead=dead+'{$dead['def']}' where you='{$nation['nation']}' and me='{$dnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        $log = uniqueItem($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_17($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    if($general['crew'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사가 없습니다. 소집해제 실패. <1>$date</>";
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

        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_21($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $path = explode("|", $city['path']);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select name from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    for($i=0; $i < count($path); $i++) {
        if($path[$i] == $destination) { $valid = 1; }
    }

    if(!$valid) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:인접도시가 아닙니다. <G><b>{$destcity['name']}</b></>(으)로 이동 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 부족합니다. <G><b>{$destcity['name']}</b></>(으)로 이동 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(으)로 이동했습니다. <1>$date</>";
        $exp = 50;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);

        // 이동, 경험치 상승, 명성 상승, 사기 감소
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',gold=gold-'{$admin['develcost']}',city='$destination',atmos=atmos*0.95,leader2='$general[leader2]',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general['level'] == 12) {
            $query = "select level from nation where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);

            if($nation['level'] == 0) {
                $query = "update general set city='$destination' where nation='{$general['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                $query = "select no,name from general where nation='{$general['nation']}' and level<'12'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $gencount = MYDB_num_rows($result);
                $genlog[0] = "<C>●</>방랑군 세력이 <G><b>{$destcity['name']}</b></>(으)로 이동했습니다.";
                for($j=0; $j < $gencount; $j++) {
                    $gen = MYDB_fetch_array($result);
                    pushGenLog($gen, $genlog);
                }
            }
        }

        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_22($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($general[turn0]);
    $who = $command[1];

    $query = "select * from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $you = MYDB_fetch_array($result);

    $cost = round($admin['develcost'] + ($you['experience'] + $you['dedication'])/1000) * 10;

    if(!$you) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 장수입니다. 등용 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반 제한중입니다. 등용 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 등용 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 등용 실패. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 등용 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>{$you['name']}</>에게 등용 권유 서신을 보냈습니다. <1>$date</>";
        $exp = 100;
        $ded = 200;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        ScoutMsg($connect, $general['no'], $nation['name'], $who, $you['msgindex']);

        $general[intel2]++;
        $query = "update general set resturn='SUCCESS',gold=gold-'$cost',intel2='$general[intel2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_23($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select gold,rice,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $what = $command[3];
    $who = $command[2];
    $amount = $command[1];
    $amount *= 100;    // 100~10000까지

    if($amount > 10000) { $amount = 10000; }
    if($amount < 100) { $amount = 100; }
    if($what == 1) {
        $dtype = "금";
        if($nation['gold']-$_basegold < $amount) { $amount = $nation['gold'] - $_basegold; }
    } elseif($what == 2) {
        $dtype = "쌀";
        if($nation['rice']-$_baserice < $amount) { $amount = $nation['rice'] - $_baserice; }
    } else {
        $what = 2;
        $dtype = "쌀";
        if($nation['rice']-$_baserice < $amount) { $amount = $nation['rice'] - $_baserice; }
    }

    $query = "select no,nation,level,name,gold,rice from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if(!$gen) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 장수입니다. 포상 실패. <1>$date</>";
    } elseif($general['no'] == $who) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자기 자신입니다. 포상 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 포상 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 포상 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 포상 실패. <1>$date</>";
    } elseif($what == 1 && $amount <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:국고가 부족합니다. 포상 실패. <1>$date</>";
    } elseif($what == 2 && $amount <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병량이 부족합니다. 포상 실패. <1>$date</>";
    } elseif($gen['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. 포상 실패. <1>$date</>";
    } else {
        $genlog[count($genlog)] = "<C>●</>$dtype <C>$amount</>을 포상으로 받았습니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>{$gen['name']}</>에게 $dtype <C>$amount</>을 수여했습니다. <1>$date</>";
        $exp = 1;
        $ded = 1;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        if($what == 1) {
            $gen['gold'] += $amount;
            $query = "update general set gold='{$gen['gold']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['gold'] -= $amount;
            $query = "update nation set gold='{$nation['gold']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($what == 2) {
            $gen['rice'] += $amount;
            $query = "update general set rice='{$gen['rice']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['rice'] -= $amount;
            $query = "update nation set rice='{$nation['rice']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 경험치 상승
        $query = "update general set resturn='SUCCESS',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
    pushGenLog($gen, $genlog);
}

function process_24($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,scenario,startyear from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,color,gold,rice,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $what = $command[3];
    $who = $command[2];
    $amount = $command[1];
    $amount *= 100;    // 100~10000까지

    $query = "select no,nation,level,name,gold,rice,npc,picture,imgsvr from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if($amount > 10000) { $amount = 10000; }
    if($amount < 100) { $amount = 100; }
    if($what == 1) {
        $dtype = "금";
        if($gen['gold'] < $amount) { $amount = $gen['gold']; }
    } elseif($what == 2) {
        $dtype = "쌀";
        if($gen['rice'] < $amount) { $amount = $gen['rice']; }
    } else {
        $what = 2;
        $dtype = "쌀";
        if($gen['rice'] < $amount) { $amount = $gen['rice']; }
    }

    if(!$gen) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 장수입니다. 몰수 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 초반 제한중입니다. 몰수 실패. <1>$date</>";
    } elseif($general['no'] == $who) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자기 자신입니다. 몰수 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 몰수 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 몰수 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 몰수 실패. <1>$date</>";
    } elseif($gen['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. 몰수 실패. <1>$date</>";
    } else {
        if($gen['npc'] >= 2 && rand()%100 == 0) {
            switch(rand()%5) {
            case 0: $str = "몰수를 하다니... 이것이 윗사람이 할 짓이란 말입니까..."; break;
            case 1: $str = "사유재산까지 몰수해가면서 이 나라가 잘 될거라 믿습니까? 정말 이해할 수가 없군요..."; break;
            case 2: $str = "내 돈 내놔라! 내 돈! 몰수가 왠 말이냐!"; break;
            case 3: $str = "몰수해간 내 자금... 언젠가 몰래 다시 빼내올 것이다..."; break;
            case 4: $str = "몰수로 인한 사기 저하는 몰수로 얻은 물자보다 더 손해란걸 모른단 말인가!"; break;
            }

            PushMsg(1, 0, $gen['picture'], $gen['imgsvr'], "{$gen['name']}:", $nation['color'], $nation['name'], $nation['color'], $str);
        }

        $genlog[count($genlog)] = "<C>●</>$dtype {$amount}을 몰수 당했습니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>{$gen['name']}</>에게서 $dtype <C>$amount</>을 몰수했습니다. <1>$date</>";
        $exp = 1;
        $ded = 1;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        if($what == 1) {
            $gen['gold'] -= $amount;
            $query = "update general set gold='{$gen['gold']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['gold'] += $amount;
            $query = "update nation set gold='{$nation['gold']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($what == 2) {
            $gen['rice'] -= $amount;
            $query = "update general set rice='{$gen['rice']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['rice'] += $amount;
            $query = "update nation set rice='{$nation['rice']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 경험치 상승
        $query = "update general set resturn='SUCCESS',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
    pushGenLog($gen, $genlog);
}

function process_25($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general[turn0]);
    $where = $command[1];

    // 랜덤임관인 경우
    if($where == 99) {
        // 초반시 10명이하, 임관금지없음 국가
        if($admin['year'] < $admin['startyear']+3) {
            $query = "select name,nation,scout,level from nation where nation not in (0{$general['nations']}0) and gennum<10 and scout=0 order by rand() limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);
        } else {
            $query = "select name,nation,scout,level from nation where nation not in (0{$general['nations']}0) and scout=0 order by rand() limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);
        }
    } elseif($where == 98) {
        // 초반시 10명이하, 임관금지없음 국가, 방랑군 제외
        if($admin['year'] < $admin['startyear']+3) {
            $query = "select name,nation,scout,level from nation where nation not in (0{$general['nations']}0) and gennum<10 and scout=0 and level>0 order by rand() limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);
        } else {
            $query = "select name,nation,scout,level from nation where nation not in (0{$general['nations']}0) and scout=0 and level>0 order by rand() limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);
        }
    } else {
        $query = "select name,nation,scout,level from nation where nation='$where'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);
    }
    $query = "select no from general where nation='{$nation['nation']}'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);

    if(!$nation) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:임관할 국가가 없습니다. 임관 실패. <1>$date</>";
    } elseif($general['nation'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야가 아닙니다. 임관 실패. <1>$date</>";
    } elseif($nation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 국가입니다. 임관 실패. <1>$date</>";
    } elseif($nation['level'] == 0 && $gencount >= 10) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>은(는) 임관이 제한되고 있습니다. 임관 실패.";
    } elseif($admin['year'] < $admin['startyear']+3 && $gencount >= 10) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>은(는) 임관이 제한되고 있습니다. 임관 실패.";
    } elseif($nation['scout'] == 1 && $general['npc'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>은(는) 임관이 금지되어 있습니다. 임관 실패.";
    } elseif($general['makelimit'] > 0 && $general['npc'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야가 된지 12턴이 지나야 합니다. 임관 실패. <1>$date</>";
    } elseif(strpos($general['nations'], ",{$nation['nation']},") > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 임관했었던 국가입니다. 임관 실패. <1>$date</>";
    } else {
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$nation['name']}</b></>에 <S>임관</>했습니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D>{$nation['name']}</>에 임관했습니다. <1>$date</>";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>에 임관");

        if($gencount < 10) { $exp = 700; }
        else { $exp = 100; }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 군주가 있는 곳으로 이동
        $query = "select city from general where nation='{$nation['nation']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($result);

        // NPC초반시 임관기록 추가 안함
        if($general['npc'] > 1 && $admin['year'] < $admin['startyear']+3) {
        } else {
            $general['nations'] .= "{$nation['nation']},";
        }

        // 국적 바꾸고 등급 일반으로        // 명성 상승
        $query = "update general set resturn='SUCCESS',nation='{$nation['nation']}',nations='{$general['nations']}',level='1',experience=experience+'$exp',city='{$king['city']}',belong=1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //국가 기술력 그대로
        $query = "select no from general where nation='{$nation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $gennum = $gencount;
        if($gencount < 10) $gencount = 10;

        $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($where < 99) {
            $log = uniqueItem($connect, $general, $log);
        } else {
            $log = uniqueItem($connect, $general, $log, 2);
        }
    }

    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_26($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $troop = getTroop($connect, $general['troop']);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no,name,nation,city from general where troop='{$general['troop']}' and no!='{$general['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    if($general['nation'] != $city['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 집합 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 집합 실패. <1>$date</>";
    } elseif($general['no'] != $troop['no']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:부대장이 아닙니다. 집합 실패. <1>$date</>";
    } elseif($gencount == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:집합 가능한 부대원이 없습니다. 집합 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>에서 집합을 실시했습니다. <1>$date</>";
        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        //부대원에게 로그 전달
        $genlog[count($genlog)] = "<C>●</><S>{$troop['name']}</>의 부대원들은 <G><b>{$city['name']}</b></>(으)로 집합되었습니다.";

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
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_27($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select gold,rice,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $who = $command[2];
    $where = $command[1];

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no,name,nation,level from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $you = MYDB_fetch_array($result);

    $query = "select name,nation,supply from city where city='$where'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    if(!$you) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 장수입니다. 발령 실패. <1>$date</>";
    } elseif($general['no'] == $who) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자기 자신입니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($destcity['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 도시가 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($general['nation'] != $you['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>{$you['name']}</>(을)를 <G><b>{$destcity['name']}</b></>(으)로 발령했습니다. <1>$date</>";
        $youlog[count($youlog)] = "<C>●</><Y>{$general['name']}</>에 의해 <G><b>{$destcity['name']}</b></>(으)로 발령됐습니다. <1>$date</>";
        $exp = 1;
        $ded = 1;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 발령
        $query = "update general set city='$where' where no='{$you['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp',dedication=dedication+'$ded' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//        $log = checkAbility($connect, $general, $log);
    }

    pushGenLog($general, $log);
    pushGenLog($you, $youlog);
}

function process_28($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select level,capital from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:방랑군입니다. 귀환 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 귀환 실패. <1>$date</>";
    } elseif(($general['level'] == 1 || $general['level'] >= 5) && $general['city'] == $nation['capital']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 수도입니다. 귀환 실패. <1>$date</>";
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

        $log[count($log)] = "<C>●</>{$admin['month']}월:<G>{$city['name']}</>(으)로 귀환했습니다. <1>$date</>";
        $exp = 70;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 귀환
        $query = "update general set city='{$city['city']}' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승        // 명성,공헌 상승
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',experience=experience+'$exp',dedication=dedication+'$ded' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }

    pushGenLog($general, $log);
    pushGenLog($you, $youlog);
}

function process_29($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost,npccount,turnterm,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,level,gennum,scout from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 인재탐색 실패. <1>$date</>";
    } elseif($nation['level'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:방랑군입니다. 인재탐색 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['gennum'] >= 10) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>은(는) 탐색이 제한되고 있습니다. 인재탐색 실패.";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 인재탐색 실패. <1>$date</>";
    } else {
        $query = "select no from general where nation='{$general['nation']}' and npc<2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        $query = "select no from general where nation='{$general['nation']}' and npc=3";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($result);

        $query = "select no from general where nation!='{$general['nation']}' and npc=3";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $otherNpccount = MYDB_num_rows($result);
        $otherNpccount = round(sqrt($otherNpccount + 1)) - 1;
        
        if($gencount <= 0) { $gencount = 1; }
        if($npccount <= 0) { $npccount = 1; }
        $criteria = $gencount * $npccount + $otherNpccount;

        // 탐색 실패
        if(rand() % $criteria > 0) {
            $exp = 100;
            $ded = 70;
            switch(rand()%3) {
            case 0: $general[leader2] += 1; break;
            case 1: $general[power2] += 1; break;
            case 2: $general[intel2] += 1; break;
            }
            $log[count($log)] = "<C>●</>{$admin['month']}월:인재를 찾을 수 없었습니다. <1>$date</>";
        } else {
            // 탐색 성공
            $exp = 200;
            $ded = 300;
            switch(rand()%3) {
            case 0: $general[leader2] += 3; break;
            case 1: $general[power2] += 3; break;
            case 2: $general[intel2] += 3; break;
            }

            $name = getRandGenName();
            $name = 'ⓜ'.$name;
            //중복장수 처리
            $query = "select no from general where name like '{$name}%'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);
            $count++;
            if($count > 1) {
                $name = "{$name}{$count}";
            }

            if($nation['scout'] != 0) {
                $scoutType = "발견";
                $scoutLevel = 0;
                $scoutNation = 0;
            } else {
                $scoutType = "영입";
                $scoutLevel = 1;
                $scoutNation = $nation['nation'];
            }
            
            $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>$name</>(이)라는 <C>인재</>를 {$scoutType}하였습니다! <1>$date</>";
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <Y>$name</>(이)라는 <C>인재</>를 {$scoutType}하였습니다!";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>$name</>(이)라는 <C>인재</>를 {$scoutType}");

            $query = "select max(leader+power+intel) as lpi, avg(dedication) as ded,avg(experience) as exp, avg(dex0) as dex0, avg(dex10) as dex10, avg(dex20) as dex20, avg(dex30) as dex30, avg(dex40) as dex40 from general where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $avgGen = MYDB_fetch_array($result);

            // 체섭시 무장 20%, 지장 20%, 무지장 60%
            // 마이너 무장 40%, 지장 40%, 무지장 20%
            $type = rand() % 10;
            if($admin['scenario'] == 0) {
                switch($type) {
                case 0: case 1:
                    $leader = 65 + rand()%11;
                    $intel = 10 + rand()%6;
                    $power = 150 - $leader - $intel;
                    break;
                case 2: case 3:
                    $leader = 65 + rand()%11;
                    $power = 10 + rand()%6;
                    $intel = 150 - $leader - $power;
                    break;
                case 4: case 5: case 6: case 7: case 8: case 9:
                    $leader = 10 + rand()%6;
                    $power = 65 + rand()%11;
                    $intel = 150 - $leader - $power;
                    break;
                }
            } else {
                switch($type) {
                case 0: case 1: case 2: case 3:
                    $leader = 65 + rand()%11;
                    $intel = 10 + rand()%6;
                    $power = 150 - $leader - $intel;
                    break;
                case 4: case 5: case 6: case 7:
                    $leader = 65 + rand()%11;
                    $power = 10 + rand()%6;
                    $intel = 150 - $leader - $power;
                    break;
                case 8: case 9:
                    $leader = 10 + rand()%6;
                    $power = 65 + rand()%11;
                    $intel = 150 - $leader - $power;
                    break;
                }
            }
            // 국내 최고능치 기준으로 랜덤성 스케일링
            if($avgGen['lpi'] > 210) {
                $leader = round($leader * $avgGen['lpi'] / 150 * (60+rand()%31)/100);
                $power = round($power * $avgGen['lpi'] / 150 * (60+rand()%31)/100);
                $intel = round($intel * $avgGen['lpi'] / 150 * (60+rand()%31)/100);
            } elseif($avgGen['lpi'] > 180) {
                $leader = round($leader * $avgGen['lpi'] / 150 * (75+rand()%21)/100);
                $power = round($power * $avgGen['lpi'] / 150 * (75+rand()%21)/100);
                $intel = round($intel * $avgGen['lpi'] / 150 * (75+rand()%21)/100);
            } else {
                $leader = round($leader * $avgGen['lpi'] / 150 * (90+rand()%11)/100);
                $power = round($power * $avgGen['lpi'] / 150 * (90+rand()%11)/100);
                $intel = round($intel * $avgGen['lpi'] / 150 * (90+rand()%11)/100);
            }
            $over1 = 0;
            $over2 = 0;
            $over3 = 0;
            // 너무 높은 능치는 다른 능치로 분산
            if($leader > 90) {
                $over1 = rand() % ($leader - 90) + 5;
                $leader -= $over1;
            }
            if($power > 90) {
                $over2 = rand() % ($power - 90) + 5;
                $power -= $over2;
            }
            if($intel > 90) {
                $over3 = rand() % ($intel - 90) + 5;
                $intel -= $over3;
            }
            // 낮은 능치쪽으로 합산
            if($type == 0) {
                $intel = $intel + $over1 + $over2 + $over3;
            } else {
                $power = $power + $over1 + $over2 + $over3;
            }
            // 너무 높은 능치는 제한
            if($leader > 95) {
                $leader = 95;
            }
            if($power > 95) {
                $power = 95;
            }
            if($intel > 95) {
                $intel = 95;
            }

            //인재추가
            $npc = 3;
            $npcid = $admin['npccount'];
            $npccount = 10000 + $npcid;
            $npcmatch = rand() % 150 + 1;
            $genid = "gen{$npccount}";
            $pw = md5("18071807");
            $picture = 'default.jpg';
            $turntime = getRandTurn($admin['turnterm']);
            $personal = rand() % 10;
            $bornyear = $admin['year'];
            $deadyear = $admin['year'] + 3;
            $age = 20;
            $specage = round((80 - $age)/12) + $age;
            $specage2 = round((80 - $age)/3) + $age;
            //$specage = $age + 1 + rand() % 3;
            //$specage2 = $age + 5 + rand() % 5;
            // 10년 ~ 50년
            $killturn = rand()%480 + 120;

            @MYDB_query("
                insert into general (
                    npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,
                    city,leader,power,intel,experience,dedication,
                    level,gold,rice,crew,crewtype,train,atmos,tnmt,
                    weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
                    makelimit,bornyear,deadyear,
                    dex0, dex10, dex20, dex30, dex40
                ) values (
                    '$npccount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','$scoutNation',
                    '{$general['city']}','$leader','$power','$intel','{$avgGen['exp']}','{$avgGen['ded']}',
                    '$scoutLevel','100','100','0','0','0','0','0',
                    '0','0','0','$turntime','$killturn','$age','1','$personal','0','$specage','0','$specage2','',
                    '0','$bornyear','$deadyear',
                    '$avgGen[dex0]','$avgGen[dex10]','$avgGen[dex20]','$avgGen[dex30]','$avgGen[dex40]'
                )",
                $connect
            ) or Error(__LINE__.MYDB_error($connect),"");

            $npcid++;

            //npccount
            $query = "update game set npccount={$npcid} where no='1'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            //국가 기술력 그대로
            $query = "select no from general where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
            $gennum = $gencount;
            if($gencount < 10) $gencount = 10;

            // 국가보정
            if($nation['type'] == 11) { $term3 = round($term3 / 2); }
            if($nation['type'] == 12) { $term3 = $term3 * 2; }

            //국가 기술력 그대로
            $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        }

        //기술로 가격
        $gold = $general['gold'] - $admin['develcost'];

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 자금 감소        // 경험치 상승        // 공헌도, 명성 상승
        $query = "update general set resturn='SUCCESS',term='0',gold='$gold',leader2='$general[leader2]',power2='$general[power2]',intel2='$general[intel2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
        $log = uniqueItem($connect, $general, $log);
    }

    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_30($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select path from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 3);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select name from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $cost = $admin['develcost'] * 5;
    
    if($dist[$destination] > 3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:거리가 멉니다. <G><b>{$destcity['name']}</b></>(으)로 강행 실패. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 부족합니다. <G><b>{$destcity['name']}</b></>(으)로 강행 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(으)로 강행했습니다. <1>$date</>";
        $exp = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);

        // 이동, 경험치 상승, 명성 상승, 병력/사기/훈련 감소
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',gold=gold-'$cost',city='$destination',crew=crew*0.95,atmos=atmos*0.9,train=train*0.95,leader2='$general[leader2]',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        if($general['level'] == 12) {
            $query = "select level from nation where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);

            if($nation['level'] == 0) {
                $query = "update general set city='$destination' where nation='{$general['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                $query = "select no,name from general where nation='{$general['nation']}' and level<'12'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $gencount = MYDB_num_rows($result);
                $genlog[0] = "<C>●</>방랑군 세력이 <G><b>{$destcity['name']}</b></>(으)로 강행했습니다.";
                for($j=0; $j < $gencount; $j++) {
                    $gen = MYDB_fetch_array($result);
                    pushGenLog($gen, $genlog);
                }
            }
        }

        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_31($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 2);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if(!$city) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 도시입니다. 첩보 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
    } elseif($general['nation'] == $city['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
//    } elseif($dist[$destination] > 3) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:너무 멉니다. <G><b>{$city['name']}</b></>에 첩보 실패. <1>$date</>";
    } else {
        $query = "select crew,crewtype from general where city='$destination' and nation='{$city['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $crew = 0;
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            if($gen['crew'] != 0) { $typecount[$gen['crewtype']]++; $crew += $gen['crew']; }
        }
        if($dist[$destination] > 2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>(을)를 살피는 것 같습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 소문만 들을 수 있었습니다. <1>$date</>";
            $log[count($log)] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:{$city['rate']}, 장수:$gencount, 병력:$crew";
        } elseif($dist[$destination] == 2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>(을)를 살피는 것 같습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 어느정도 정보를 얻었습니다. <1>$date</>";
            $log[count($log)] = "【<M>첩보</>】농업:{$city['agri']}, 상업:{$city['comm']}, 치안:{$city['secu']}, 수비:{$city['def']}, 성벽:{$city['wall']}";
            $log[count($log)] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:{$city['rate']}, 장수:$gencount, 병력:$crew";
        } else {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$city['name']}</b></>(을)를 살피는 것 같습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$city['name']}</b></>의 많은 정보를 얻었습니다. <1>$date</>";
            $msg[count($msg)] = "【<S>병종</>】";
            for($i=0;  $i <= 5;  $i++) { if($typecount[$i] != 0) { $msg[count($msg)] = _String::SubStr(getTypename($i), 0, 2).":$typecount[$i]"; } }
            for($i=10; $i <= 14; $i++) { if($typecount[$i] != 0) { $msg[count($msg)] = _String::SubStr(getTypename($i), 0, 2).":$typecount[$i]"; } }
            for($i=20; $i <= 27; $i++) { if($typecount[$i] != 0) { $msg[count($msg)] = _String::SubStr(getTypename($i), 0, 2).":$typecount[$i]"; } }
            for($i=30; $i <= 38; $i++) { if($typecount[$i] != 0) { $msg[count($msg)] = _String::SubStr(getTypename($i), 0, 2).":$typecount[$i]"; } }
            for($i=40; $i <= 43; $i++) { if($typecount[$i] != 0) { $msg[count($msg)] = _String::SubStr(getTypename($i), 0, 2).":$typecount[$i]"; } }

            $count = ceil(count($msg) / 8) * 8;
            for($i=$count; $i > 0; $i-=8) {
                $log[count($log)] = "{$msg[$i-8]} {$msg[$i-7]} {$msg[$i-6]} {$msg[$i-5]} {$msg[$i-4]} {$msg[$i-3]} {$msg[$i-2]} {$msg[$i-1]}";
            }
            $log[count($log)] = "【<M>첩보</>】농업:{$city['agri']}, 상업:{$city['comm']}, 치안:{$city['secu']}, 수비:{$city['def']}, 성벽:{$city['wall']}";
            $log[count($log)] = "【<G>{$city['name']}</>】주민:{$city['pop']}, 민심:{$city['rate']}, 장수:$gencount, 병력:$crew";

            if($general['nation'] != 0 && $city['nation'] != 0) {
                $query = "select name,tech from nation where nation='{$city['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $yourTech = MYDB_fetch_array($result);

                $query = "select tech from nation where nation='{$general['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $myTech = MYDB_fetch_array($result);

                $diff = $yourTech['tech'] - $myTech['tech'];   // 차이
                if($diff >= 1000) {      $log[count($log)] = "【<O>{$yourTech['name']}</>】아국대비기술:<M>↑</>압도"; }
                elseif($diff >=  250) {  $log[count($log)] = "【<O>{$yourTech['name']}</>】아국대비기술:<Y>▲</>우위"; }
                elseif($diff >= -250) {  $log[count($log)] = "【<O>{$yourTech['name']}</>】아국대비기술:<W>↕</>대등"; }
                elseif($diff >= -1000) { $log[count($log)] = "【<O>{$yourTech['name']}</>】아국대비기술:<G>▼</>열위"; }
                else {                   $log[count($log)] = "【<O>{$yourTech['name']}</>】아국대비기술:<C>↓</>미미"; }
            }
        }

        // 자금 하락        // 경험치 상승        // 공헌도, 명성 상승
        $exp = rand() % 100 + 1;
        $ded = rand() % 70 + 1;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general[leader2]++;
        $general['gold'] -= $admin['develcost']*3;
        $general['rice'] -= $admin['develcost']*3;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select spy from nation where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);
        if($nation['spy'] != "") { $citys = explode("|", $nation['spy']); }
        for($i=0; $i < count($citys); $i++) {
            if(floor($citys[$i]/10) == $destination) {
                $exist = 1;
                break;
            }
        }
        // 기존 첩보 목록에 없으면 새로 등록, 있으면 갱신
        if($exist == 0) {
            $citys[count($citys)] = $destination * 10 + 3;
        } else {
            $citys[$i] = $destination * 10 + 3;
        }
        $spy = implode("|", $citys);
        $query = "update nation set spy='$spy' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_32($connect, &$general) {
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 도시입니다. 화계 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } else {
        $query = "select intel,book from general where city='$destination' and nation='{$destcity['nation']}' order by intel desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $intelgen = MYDB_fetch_array($result);

        $ratio = round((($general['intel']+getBookEff($general['book']) - $intelgen['intel']-getBookEff($intelgen['book'])) / $_firing - ($destcity['secu']/$destcity[secu2])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general[special2] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($nation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(이)가 불타고 있습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 화계가 성공했습니다. <1>$date</>";

            $destcity['agri'] -= rand() % $_firingpower + $_firingbase;
            $destcity['comm'] -= rand() % $_firingpower + $_firingbase;
            if($destcity['agri'] < 0) { $destcity['agri'] = 0; }
            if($destcity['comm'] < 0) { $destcity['comm'] = 0; }
            $query = "update city set state=32,agri='{$destcity['agri']}',comm='{$destcity['comm']}' where city='$destination'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set firenum=firenum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            TrickInjury($connect, $destination);
            $exp = rand() % 100 + 201;
            $ded = rand() % 70 + 141;
        } else {
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 화계가 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general[intel2]++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',intel2='$general[intel2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_33($connect, &$general) {
    global $_firing, $_basefiring, $_firingpower;
    //global $_basegold, $_baserice; //TODO : 버그로 보여서 지웠는데, 진짜로 지워도 되는지 확인
    //탈취는 0까지 무제한
    $_basegold = 0; $_baserice = 0;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select name,level,nation,secu,secu2 from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select gold,rice from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $mynation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 도시입니다. 탈취 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } else {
        $query = "select power,weap from general where city='$destination' and nation='{$destcity['nation']}' order by power desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $powergen = MYDB_fetch_array($result);

        $ratio = round((($general['power']+getWeapEff($general['weap']) - $powergen['power']-getWeapEff($powergen['weap'])) / $_firing - ($destcity['secu']/$destcity[secu2])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general[special2] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에서 금과 쌀을 도둑맞았습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 탈취가 성공했습니다. <1>$date</>";

            // 탈취 최대 400 * 8
            $gold = (rand() % $_firingpower + $_firingbase) * $destcity['level'];
            $rice = (rand() % $_firingpower + $_firingbase) * $destcity['level'];

            $nation['gold'] -= $gold;
            $nation['rice'] -= $rice;
            if($nation['gold'] < $_basegold) { $gold += ($nation['gold'] - $_basegold); $nation['gold'] = $_basegold; }
            if($nation['rice'] < $_baserice) { $rice += ($nation['rice'] - $_baserice); $nation['rice'] = $_baserice; }
            $query = "update nation set gold='{$nation['gold']}',rice='{$nation['rice']}' where nation='{$destcity['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update city set state=34 where city='$destination'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set firenum=firenum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 본국으로 회수, 재야이면 본인이 소유
            if($general['nation'] != 0) {
                $query = "select gold,rice from nation where nation='{$general['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);
                $nation['gold'] += $gold;
                $nation['rice'] += $rice;
                $query = "update nation set gold='{$nation['gold']}',rice='{$nation['rice']}' where nation='{$general['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $general['gold'] += $gold;
                $general['rice'] += $rice;
                $query = "update general set gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
            $log[count($log)] = "<C>●</>금<C>$gold</> 쌀<C>$rice</>을 획득했습니다.";

//            TrickInjury($connect, $destination);
            $exp = rand() % 100 + 201;
            $ded = rand() % 70 + 141;
        } else {
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 탈취가 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general[power2]++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',power2='$general[power2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_34($connect, &$general) {
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select name,nation,def,wall,secu,secu2 from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $mynation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 도시입니다. 파괴 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } else {
        $query = "select power,weap from general where city='$destination' and nation='{$destcity['nation']}' order by power desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $powergen = MYDB_fetch_array($result);

        $ratio = round((($general['power']+getWeapEff($general['weap']) - $powergen['power']-getWeapEff($powergen['weap'])) / $_firing - ($destcity['secu']/$destcity[secu2])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general[special2] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$destcity['name']}</b></>의 성벽을 허물었습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 파괴가 성공했습니다. <1>$date</>";

            // 파괴
            $destcity['def'] -= rand() % $_firingpower + $_firingbase;
            $destcity['wall'] -= rand() % $_firingpower + $_firingbase;
            if($destcity['def'] < 100) { $destcity['def'] = 100; }
            if($destcity['wall'] < 100) { $destcity['wall'] = 100; }
            $query = "update city set state=34,def='{$destcity['def']}',wall='{$destcity['wall']}' where city='$destination'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set firenum=firenum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            TrickInjury($connect, $destination);
            $exp = rand() % 100 + 201;
            $ded = rand() % 70 + 141;
        } else {
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 파괴가 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general[power2]++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',power2='$general[power2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_35($connect, &$general) {
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select name,nation,rate,secu,secu2 from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $mynation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $mynation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $mynation['level'];
    } else {
        $lbonus = 0;
    }

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 도시입니다. 선동 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } else {
        $query = "select ROUND(leader*(100-injury)/100)+horse as sum,horse from general where city='$destination' and nation='{$destcity['nation']}' order by sum desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen = MYDB_fetch_array($result);

        $ratio = round(((floor($general['leader'] * (100 - $general['injury'])/100)+getHorseEff($general['horse'])+$lbonus - ($gen['sum']-$gen['horse']+getHorseEff($gen['horse']))) / $_firing - ($destcity['secu']/$destcity[secu2])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general[special2] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>의 백성들이 동요하고 있습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 선동이 성공했습니다. <1>$date</>";

            // 선동 최대 10
            $destcity['secu'] -= rand() % round($_firingpower/2) + $_firingbase;
            $destcity['rate'] -= rand() % round($_firingpower/50) + $_firingbase/50;
            if($destcity['secu'] < 0) { $destcity['secu'] = 0; }
            if($destcity['rate'] < 0) { $destcity['rate'] = 0; }
            $query = "update city set state=32,rate='{$destcity['rate']}',secu='{$destcity['secu']}' where city='$destination'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set firenum=firenum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            TrickInjury($connect, $destination);
            $exp = rand() % 100 + 201;
            $ded = rand() % 70 + 141;
        } else {
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 선동이 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general[leader2]++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_36($connect, &$general) {
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general[turn0]);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select level,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $mynation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $mynation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $mynation['level'];
    } else {
        $lbonus = 0;
    }

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 도시입니다. 기습 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } else {
        $query = "select ROUND((leader+intel+power)*(100-injury)/100)+weap+horse+book as sum,weap,horse,book from general where city='$destination' and nation='{$destcity['nation']}' order by sum desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen = MYDB_fetch_array($result);

        $ratio = round(((floor(($general['leader']+$general['intel']+$general['power']) * (100 - $general['injury'])/100)+getWeapEff($general['weap'])+getHorseEff($general['horse'])+$lbonus+getBookEff($general['book']) - ($gen['sum']-$gen['weap']-$gen['horse']-$gen['book']+getWeapEff($gen['weap'])+getHorseEff($gen['horse'])+getBookEff($gen['book']))) / $_firing - ($destcity['secu']/$destcity[secu2])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[count($log)] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general[special2] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(이)가 누군가에게 공격 받았습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 기습이 성공했습니다. <1>$date</>";

            // 기습
            $destcity['agri'] -= rand() % round($_firingpower/2) + $_firingbase;
            $destcity['comm'] -= rand() % round($_firingpower/2) + $_firingbase;
            $destcity['secu'] -= rand() % round($_firingpower/4) + $_firingbase;
            $destcity['def'] -= rand() % round($_firingpower/2) + $_firingbase;
            $destcity['wall'] -= rand() % round($_firingpower/2) + $_firingbase;
            $destcity['rate'] -= rand() % round($_firingpower/50) + $_firingbase/50;
            if($destcity['agri'] < 0) { $destcity['agri'] = 0; }
            if($destcity['comm'] < 0) { $destcity['comm'] = 0; }
            if($destcity['secu'] < 0) { $destcity['secu'] = 0; }
            if($destcity['def'] < 0) { $destcity['def'] = 0; }
            if($destcity['wall'] < 0) { $destcity['wall'] = 0; }
            if($destcity['rate'] < 0) { $destcity['rate'] = 0; }
            $query = "update city set state=32,agri='{$destcity['agri']}',comm='{$destcity['comm']}',secu='{$destcity['secu']}',def='{$destcity['def']}',wall='{$destcity['wall']}',rate='{$destcity['rate']}' where city='$destination'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set firenum=firenum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            TrickInjury($connect, $destination);
            $exp = rand() % 100 + 201;
            $ded = rand() % 70 + 141;
        } else {
            $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 기습이 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general[leader2]++;
        $general[intel2]++;
        $general[power2]++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',leader2='$general[leader2]',intel2='$general[intel2]',power2='$general[power2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_41($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $ratio = rand() % 100;
    $exp = $general['crew'] / 400;
    $crewexp = $general['crew'] * $general['train'] * $general['atmos'] / 20 / 10000;
    // 랜덤치
    $exp = round($exp * (80 + rand() % 41)/100);   // 80 ~ 120%
    $crewexp = round($crewexp * (80 + rand() % 41)/100);   // 80 ~ 120%

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ratio = CharCritical($ratio, $general['personal']);

    if($general['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 단련 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 단련 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. 단련 실패. <1>$date</>";
    } elseif($general['train'] < 40) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:훈련이 너무 낮습니다. 단련 실패. <1>$date</>";
    } elseif($general['atmos'] < 40) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:사기가 너무 낮습니다. 단련 실패. <1>$date</>";
    } elseif($crewexp == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:병사가 모자랍니다. 단련 실패. <1>$date</>";
    } else {
        $type = floor($general['crewtype'] / 10) * 10;
        switch($type) {
        case 0: $crewstr = '보병'; break;
        case 1: $crewstr = '궁병'; break;
        case 2: $crewstr = '기병'; break;
        case 3: $crewstr = '귀병'; break;
        case 4: $crewstr = '차병'; break;
        }

        if($ratio < 33) {
            // 숙련도 증가
            addGenDex($connect, $general['no'], $general['crewtype'], $crewexp);
            $log[count($log)] = "<C>●</>{$admin['month']}월:$crewstr 숙련도 향상이 <O>지지부진</>했습니다. <1>$date</>";
        } elseif($ratio < 66) {
            $exp = $exp * 2;
            // 숙련도 증가
            addGenDex($connect, $general['no'], $general['crewtype'], $crewexp * 2);
            $log[count($log)] = "<C>●</>{$admin['month']}월:$crewstr 숙련도가 향상되었습니다. <1>$date</>";
        } else {
            $exp = $exp * 3;
            // 숙련도 증가
            addGenDex($connect, $general['no'], $general['crewtype'], $crewexp * 3);
            $log[count($log)] = "<C>●</>{$admin['month']}월:$crewstr 숙련도가 <S>일취월장</>했습니다. <1>$date</>";
        }

        // 경험치 상승    // 명성 상승
        $query = "update general set resturn='SUCCESS',gold=gold-'{$admin['develcost']}',rice=rice-'{$admin['develcost']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    pushGenLog($general, $log);
}

function process_42($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $type = rand() % 27 + 1;
    $exp = 30;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    $exp2 = $exp * 2;

    switch($type) {
    case 1:
        $log[count($log)] = "<C>●</>{$admin['month']}월:지나가는 행인에게서 금을 <C>300</> 받았습니다. <1>$date</>";
        // 자금 상승        // 명성 상승
        $query = "update general set resturn='SUCCESS',gold=gold+300,experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 2:
        $log[count($log)] = "<C>●</>{$admin['month']}월:지나가는 행인에게서 쌀을 <C>300</> 받았습니다. <1>$date</>";
        // 군량 상승        // 명성 상승
        $query = "update general set resturn='SUCCESS',rice=rice+300,experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 3:
        $log[count($log)] = "<C>●</>{$admin['month']}월:어느 명사와 설전을 벌여 멋지게 이겼습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[intel2] += 2;
        $query = "update general set resturn='SUCCESS',intel2='$general[intel2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 4:
        $log[count($log)] = "<C>●</>{$admin['month']}월:명사와 설전을 벌였으나 망신만 당했습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 5:
        $log[count($log)] = "<C>●</>{$admin['month']}월:동네 장사와 힘겨루기를 하여 멋지게 이겼습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[power2] += 2;
        $query = "update general set resturn='SUCCESS',power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 6:
        $log[count($log)] = "<C>●</>{$admin['month']}월:동네 장사와 힘겨루기를 했지만 망신만 당했습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 7:
        $log[count($log)] = "<C>●</>{$admin['month']}월:산적과 싸워 금 <C>300</>을 빼앗았습니다. <1>$date</>";
        // 자금 상승        // 경험치 상승        // 명성 상승
        $general[power2] += 2;
        $query = "update general set resturn='SUCCESS',gold=gold+300,power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 8:
        $log[count($log)] = "<C>●</>{$admin['month']}월:산적을 만나 금 <C>200</>을 빼앗겼습니다. <1>$date</>";
        $general['gold'] -= 200;
        if($general['gold'] <= 0) { $general['gold'] = 0; }
        // 자금 하락        // 경험 상승
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 9:
        $log[count($log)] = "<C>●</>{$admin['month']}월:호랑이를 잡아 고기 <C>300</>을 얻었습니다. <1>$date</>";
        // 군량 상승        // 경험치 상승
        $general[power2] += 2;
        $query = "update general set resturn='SUCCESS',rice=rice+300,power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 10:
        $log[count($log)] = "<C>●</>{$admin['month']}월:호랑이에게 물려 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 10 + 10;
        $general[power2]--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='$general[power2]',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 11:
        $log[count($log)] = "<C>●</>{$admin['month']}월:곰을 잡아 고기 <C>300</>을 얻었습니다. <1>$date</>";
        // 군량 상승        // 경험치 상승        // 명성 상승
        $general[power2] += 2;
        $query = "update general set resturn='SUCCESS',rice=rice+300,power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 12:
        $log[count($log)] = "<C>●</>{$admin['month']}월:곰에게 할퀴어 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 10 + 10;
        $general[power2]--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='$general[power2]',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 13:
        $log[count($log)] = "<C>●</>{$admin['month']}월:주점에서 사람들과 어울려 술을 마셨습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 14:
        $log[count($log)] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구해주었습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 15:
        $log[count($log)] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구해주다가 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 10 + 10;
        $general[power2]--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 16:
        $log[count($log)] = "<C>●</>{$admin['month']}월:돈을 빌려주었다가 이자 <C>300</>을 받았습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[intel2]++;
        $query = "update general set resturn='SUCCESS',gold=gold+300,intel2='$general[intel2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 17:
        $log[count($log)] = "<C>●</>{$admin['month']}월:돈을 <C>200</> 빌려주었다가 떼어먹혔습니다. <1>$date</>";
        $general['gold'] -= 200;
        if($general['gold'] <= 0) { $general['gold'] = 0; }
        // 명성 상승
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 18:
        $log[count($log)] = "<C>●</>{$admin['month']}월:쌀을 빌려주었다가 이자 <C>300</>을 받았습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[intel2]++;
        $query = "update general set resturn='SUCCESS',rice=rice+300,intel2='$general[intel2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 19:
        $log[count($log)] = "<C>●</>{$admin['month']}월:쌀을 <C>200</> 빌려주었다가 떼어먹혔습니다. <1>$date</>";
        $general['rice'] -= 200;
        if($general['rice'] <= 0) { $general['rice'] = 0; }
        // 명성 상승
        $query = "update general set resturn='SUCCESS',rice='{$general['rice']}',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 20:
        $log[count($log)] = "<C>●</>{$admin['month']}월:거리에서 글 모르는 아이들을 모아 글을 가르쳤습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[intel2] += 2;
        $query = "update general set resturn='SUCCESS',intel2='$general[intel2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 21:
        $log[count($log)] = "<C>●</>{$admin['month']}월:백성들에게 현인의 가르침을 설파했습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[leader2] += 2;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 22:
        $log[count($log)] = "<C>●</>{$admin['month']}월:어느 집의 무너진 울타리를 고쳐주었습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[power2] += 2;
        $query = "update general set resturn='SUCCESS',power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 23:
        $log[count($log)] = "<C>●</>{$admin['month']}월:어느 집의 도망친 가축을 되찾아 주었습니다. <1>$date</>";
        // 경험치 상승        // 명성 상승
        $general[leader2] += 2;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 24:
        $log[count($log)] = "<C>●</>{$admin['month']}월:호랑이에게 물려 크게 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 30 + 20;
        $general[power2]--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='$general[power2]',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 25:
        $log[count($log)] = "<C>●</>{$admin['month']}월:곰에게 할퀴어 크게 다쳤습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 30 + 20;
        $general[power2]--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='$general[power2]',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 26:
        $log[count($log)] = "<C>●</>{$admin['month']}월:위기에 빠진 사람을 구하다가 죽을뻔 했습니다. <1>$date</>";
        // 경험치 하락        // 명성 상승
        $injury = rand() % 50 + 30;
        $general[power2]--;
        $query = "update general set resturn='SUCCESS',injury=injury+'$injury',power2='$general[power2]',experience=experience+'$exp2' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    default:
        $log[count($log)] = "<C>●</>{$admin['month']}월:아무일도 일어나지 않았습니다. <1>$date</>";
        // 명성 상승
        $query = "update general set resturn='SUCCESS',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    }

    $log = checkAbility($connect, $general, $log);
    pushGenLog($general, $log);
}

function process_43($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $command = DecodeCommand($general[turn0]);
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
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 장수입니다. 증여 실패. <1>$date</>";
    } elseif($what == 1 && $general['gold'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 없습니다. 증여 실패. <1>$date</>";
    } elseif($what == 2 && $general['rice'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 없습니다. 증여 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야는 불가능합니다. 증여 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 증여 실패. <1>$date</>";
    } elseif($general['nation'] != $gen['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. 증여 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 증여 실패. <1>$date</>";
    } else {
        $genlog[count($genlog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게서 $dtype <C>$amount</>을 증여 받았습니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>{$gen['name']}</>에게 $dtype <C>$amount</>을 증여했습니다. <1>$date</>";

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
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
    pushGenLog($gen, $genlog);
}

function process_44($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name,gold,rice from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general[turn0]);
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
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 헌납 실패. <1>$date</>";
    } elseif($what == 1 && $general['gold'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 없습니다. 헌납 실패. <1>$date</>";
    } elseif($what == 2 && $general['rice'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 없습니다. 헌납 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 증여 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 증여 실패. <1>$date</>";
    } else {
//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<D><b>{$nation['name']}</b></>에서 장수들이 재산을 헌납 하고 있습니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월: $dtype <C>$amount</>을 헌납했습니다. <1>$date</>";

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
        $general[leader2]++;
        $query = "update general set resturn='SUCCESS',leader2='$general[leader2]',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_45($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name,chemi from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야입니다. 하야 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중 하야는 불가능합니다. 하야 실패. <1>$date</>";
    } elseif($general['level'] == 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주입니다. 하야 실패. <1>$date</>";
    } else {
        $exp = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no from diplomacy where me='{$general['nation']}' and state>='3' and state<='4'";
        $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $dipcount1 = MYDB_num_rows($dipresult);

        $query = "select no from diplomacy where me='{$general['nation']}' and state>='5' and state<='6'";
        $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $dipcount2 = MYDB_num_rows($dipresult);

        $gold = 0;
        $rice = 0;
        // 금쌀1000이상은 남김
        if($general['gold'] > 1000) {
            $gold = $general['gold'] - 1000;
            $general['gold'] = 1000;
        }
        if($general['rice'] > 1000) {
            $rice = $general['rice'] - 1000;
            $general['rice'] = 1000;
        }

        if($dipcount1 > 0) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 통합에 반대하며 <D><b>{$nation['name']}</b></>(을)를 <R>떠났</>습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:통합에 반대하며 <D><b>{$nation['name']}</b></>에서 떠났습니다. <1>$date</>";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:통합에 반대하며 <D><b>{$nation['name']}</b></>(을)를 떠남");

            // 국적 바꾸고 등급 재야로
            $query = "update general set resturn='SUCCESS',belong=0,nation=0,level=0,makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($dipcount2 > 0) {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 합병에 반대하며 <D><b>{$nation['name']}</b></>(을)를 <R>떠났</>습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:합병에 반대하며 <D><b>{$nation['name']}</b></>에서 떠났습니다. <1>$date</>";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:합병에 반대하며 <D><b>{$nation['name']}</b></>(을)를 떠남");

            // 국적 바꾸고 등급 재야로
            $query = "update general set resturn='SUCCESS',belong=0,nation=0,level=0,makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$nation['name']}</b></>에서 <R>하야</>했습니다.";
            $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$nation['name']}</b></>에서 하야했습니다. <1>$date</>";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>에서 하야");

            // 국적 바꾸고 등급 재야로        // 명성/공헌 N*10% 감소
            $query = "update general set resturn='SUCCESS',belong=0,betray=betray+1,nation=0,level=0,experience=experience*(1-0.1*betray),dedication=dedication*(1-0.1*betray),makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        //도시의 태수, 군사, 시중직도 초기화
        $query = "update city set gen1='0' where gen1='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen2='0' where gen2='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen3='0' where gen3='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select no from troop where troop='{$general['troop']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $troop = MYDB_fetch_array($result);

        //부대장일 경우
        if($troop['no'] == $general['no']) {
            // 모두 탈퇴
            $query = "update general set troop='0' where troop='{$general['troop']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 부대 삭제
            $query = "delete from troop where troop='{$general['troop']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set troop='0' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //국가 기술력 그대로
        $query = "select no from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $gennum = $gencount;
        if($gencount < 10) $gencount = 10;

        $nation['chemi'] -= 1;
        if($nation['chemi'] < 0) { $nation['chemi'] = 0; }

        $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum',chemi='{$nation['chemi']}',gold=gold+'$gold',rice=rice+'$rice' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_46($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    $query = "select nation from nation where name='{$general['makenation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);

    $command = DecodeCommand($general[turn0]);
    $color = $command[1];
    $type = $command[2];    // 1 ~ 13

    $colors = GetNationColors();
    if($color >= count($colors)) { $color = 0; }
    $color = $colors[$color];

    if($type < 1) { $type = 9; }
    elseif($type > 13) { $type = 9; }

    if($gencount < 2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수하 장수가 부족합니다. 건국 실패. <1>$date</>";
    } elseif($admin['year'] >= $admin['startyear']+2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:건국 기간이 지났습니다. 건국 실패. <1>$date</>";
    } elseif($city['nation'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지가 아닙니다. 건국 실패. <1>$date</>";
    } elseif($nationcount > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:존재하는 국가명입니다. 건국 실패. <1>$date</>";
    } elseif($general['makelimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야가 된지 12시간이 지나야 합니다. 건국 실패. <1>$date</>";
    } elseif($general['level'] != 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주가 아닙니다. 건국 실패. <1>$date</>";
    } elseif($city['level'] != 5 && $city['level'] != 6) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:중, 소 도시에서만 가능합니다. 건국 실패. <1>$date</>";
    } else {
        $query = "update nation set name='{$general['makenation']}',color='$color',level=1,type='$type',capital='{$general['city']}' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select nation,name,history from nation where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);

        // 현 도시 소속지로
        $query = "update city set nation='{$nation['nation']}',conflict='',conflict2='' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$nation['name']}</b></>(을)를 건국하였습니다. <1>$date</>";
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$city['name']}</b></>에 국가를 건설하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【건국】</b></>".getNationType($type)." <D><b>{$nation['name']}</b></>(이)가 새로이 등장하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>(을)를 건국");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$nation['name']}</b></>(을)를 건국");

        $exp = 1000;
        $ded = 1000;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 명성 상승
        $query = "update general set resturn='SUCCESS',dedication=dedication+'$ded', experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = uniqueItem($connect, $general, $log, 3);
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_47($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name,level from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    //현재 외교 진행중(평시, 불가침만 제외)일때
    $query = "select state from diplomacy where me='{$general['nation']}' and state!='2' and state!='7'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);


    if($general['level'] != 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주가 아닙니다. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한중에는 방랑이 불가능합니다. 방랑 실패.";
    } elseif($dipcount != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:방랑할 수 없는 외교상태입니다. 방랑 실패.";
    } elseif($nation['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 방랑군입니다. 방랑 실패.";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:영토를 버리고 방랑의 길을 떠납니다. <1>$date</>";
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 방랑의 길을 떠납니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>(을)를 버리고 방랑");

        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【방랑】</b></><D><b>{$general['name']}</b></>은(는) <R>방랑</>의 길을 떠납니다.";

        //분쟁기록 모두 지움
        DeleteConflict($connect, $general['nation']);
        // 국명, 색깔 바꿈 국가 레벨 0, 성향리셋, 기술0
        $query = "update nation set name='{$general['name']}',color='330000',level='0',type='0',tech='0',totaltech='0',capital='0' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 본인 빼고 건국/임관제한
        $query = "update general set makelimit='12' where no!='{$general['no']}' and nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 건국/임관제한
        $query = "update general set resturn='SUCCESS',makelimit='12' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 관직장수 일반으로
        $query = "update general set level=1 where nation='{$general['nation']}' and level <= 11";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 전 도시 공백지로
        $query = "update city set nation='0',front='0',gen1='0',gen2='0',gen3='0',conflict='',conflict2='' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 리셋
        $query = "update diplomacy set state='2',term='0' where me='{$general['nation']}' or you='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushHistory($connect, $history);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_48($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $city = getCity($connect, $general['city']);

    $command = DecodeCommand($general[turn0]);
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
        $log[count($log)] = "<C>●</>{$admin['month']}월:도시에 상인이 없습니다. 장비매매 실패. <1>$date</>";
    } elseif($city['secu']/1000 < $type) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이 도시에서는 구할 수 없었습니다. 구입 실패. <1>$date</>";
    } elseif($type > 6 || $type < 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:구입할 수 있는 물건이 아닙니다. 구입 실패. <1>$date</>";
    } elseif($general['gold'] < $cost && $type != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 구입 실패. <1>$date</>";
    } elseif($general['weap'] == 0 && $isweap == 0 && $type == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:무기가 없습니다. 판매 실패. <1>$date</>";
    } elseif($general['book'] == 0 && $isweap == 1 && $type == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:서적이 없습니다. 판매 실패. <1>$date</>";
    } elseif($general['horse'] == 0 && $isweap == 2 && $type == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:명마가 없습니다. 판매 실패. <1>$date</>";
    } elseif($general['item'] == 0 && $isweap == 3 && $type == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:도구가 없습니다. 판매 실패. <1>$date</>";
    } else {
        if($isweap == 0) {
            if($type != 0) {
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getWeapName($type)."</>(을)를 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',weap='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = round(getItemCost($general['weap']) / 2, 0);
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getWeapName($general['weap'])."</>(을)를 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',weap='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        } elseif($isweap == 1) {
            if($type != 0) {
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getBookName($type)."</>(을)를 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',book='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = round(getItemCost($general['book']) / 2, 0);
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getBookName($general['book'])."</>(을)를 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',book='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        } elseif($isweap == 2) {
            if($type != 0) {
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getHorseName($type)."</>(을)를 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',horse='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = round(getItemCost($general['horse']) / 2, 0);
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getHorseName($general['horse'])."</>(을)를 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',horse='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        } elseif($isweap == 3) {
            if($type != 0) {
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getItemName($type)."</>(을)를 구입했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',item='$type',gold=gold-'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $cost = round(getItemCost2($general['item']) / 2, 0);
                $log[count($log)] = "<C>●</>{$admin['month']}월:<C>".getItemName($general['item'])."</>(을)를 판매했습니다. <1>$date</>";
                $query = "update general set resturn='SUCCESS',item='0',gold=gold+'$cost' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }

    $exp = 10;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    // 명성 상승
    $query = "update general set experience=experience+'$exp' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushGenLog($general, $log);
}

function process_49($connect, &$general) {
    global $_taxrate;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select level from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $city = getCity($connect, $general['city']);

    $command = DecodeCommand($general[turn0]);
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
        $tax = $cost * $_taxrate;
        $cost = $cost - $tax;
    } elseif($type == 2) {
        $dtype = "군량 구입";
        $cost = $amount * $city['trade'] / 100;
        //특기 보정 : 거상
        if($general['special'] == 30 && $city['trade'] < 100) { $cost = $amount * (6 * $city['trade']/100 - 5); } // 이익인 경우 5배 이득
        if($general['special'] == 30 && $city['trade'] > 100) { $cost = $amount * (0.2 * $city['trade']/100 + 0.8); } // 손해인 경우 1/5배 손해
        $tax = $cost * $_taxrate;
        $cost = $cost + $tax;
        if($general['gold'] < $cost) {
            $cost = $general['gold'];
            $tax = $cost * $_taxrate;
            $amount = ($cost-$tax) * 100 / $city['trade'];
            //특기 보정 : 거상
            if($general['special'] == 30 && $city['trade'] < 100) { $amount = ($cost-$tax) / (6 * $city['trade']/100 - 5); } // 이익인 경우 5배 이득
            if($general['special'] == 30 && $city['trade'] > 100) { $amount = ($cost-$tax) / (0.2 * $city['trade']/100 + 0.8); } // 손해인 경우 1/5배 손해
        }
    }

    $cost = round($cost);
    $amount = round($amount);
    $tax = round($tax);

    if($city['trade'] == 0 && $general['special'] != 30 && $general['npc'] < 2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:도시에 상인이 없습니다. $dtype 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $nation['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. $dtype 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. $dtype 실패. <1>$date</>";
    } elseif($type == 1 && $general['rice'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군량이 없습니다. $dtype 실패. <1>$date</>";
    } elseif($type == 2 && $general['gold'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:자금이 없습니다. $dtype 실패. <1>$date</>";
    } else {
        // 판매
        if($type == 1) {
            $log[count($log)] = "<C>●</>{$admin['month']}월:군량 <C>$amount</>을 팔아 자금 <C>$cost</>을 얻었습니다. <1>$date</>";
            // 군량 감소
            $query = "update general set rice=rice-{$amount} where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 자금 증가
            $query = "update general set gold=gold+{$cost} where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 구입
        } elseif($type == 2) {
            $log[count($log)] = "<C>●</>{$admin['month']}월:군량 <C>$amount</>을 사서 자금 <C>$cost</>을 썼습니다. <1>$date</>";
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
        switch(rand()%3) {
        case 0:
            $general[leader2]++;
            $query = "update general set leader2='$general[leader2]' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            break;
        case 1:
            $general[power2]++;
            $query = "update general set power2='$general[power2]' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            break;
        case 2:
            $general[intel2]++;
            $query = "update general set intel2='$general[intel2]' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            break;
        }

        $log = checkAbility($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_50($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $log[count($log)] = "<C>●</>{$admin['month']}월:건강 회복을 위해 요양합니다. <1>$date</>";
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

function process_51($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select name,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,dip0,dip0_type,dip0_who,dip0_when,dip1,dip1_type,dip1_who,dip1_when,dip2,dip2_type,dip2_who,dip2_when,dip3,dip3_type,dip3_who,dip3_when,dip4,dip4_type,dip4_who,dip4_when from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 권고 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 권고 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 권고 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 권고 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>으로 항복 권고 서신을 보냈습니다.<1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 상대에게 발송
        //먼저 삭제된 칸 중 가장 오래된 칸 찾음
        $deleted = 4;
        for($i=0; $i < 5; $i++) {
            if($younation["dip{$i}"] == "") { $deleted = $i; }
        }
        //기존 메세지 한칸씩 뒤로 미룸
        for($i=$deleted-1; $i >=0; $i--) {
            moveMsg($connect, "nation", "dip", $i+1, $younatin["dip{$i}"], $younation["dip{$i}_type"], $younation["dip{$i}_who"], $younation["dip{$i}_when"], "nation", $younation['nation']);
        }
        //권고 서신시 장수번호/상대국 번호
        $me = $general['no'] * 10000 + $younation['nation'];
        $date = date('Y-m-d H:i:s');
        $query = "update nation set dip0='{$nation['name']}의 항복 권고 서신',dip0_type='4',dip0_who='$me',dip0_when='$date' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//        $log = checkAbility($connect, $general, $log);
    }

    pushGenLog($general, $log);
}

function process_52($connect, &$general) {
    global $_basegold, $_baserice;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,level,gold,rice,surlimit,history,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $mynation = MYDB_fetch_array($result);

    $command = DecodeCommand($mynation["l{$general['level']}turn0"]);
    $rice = $command[3];
    $gold = $command[2];
    $which = $command[1];
    $rice *= 1000;
    $gold *= 1000;
    $limit = $mynation['level'] * 10000;
    
    if($gold < 0) { $gold = 0; }
    if($rice < 0) { $rice = 0; }

    $query = "select nation,name,gold,rice,surlimit,history from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($gold > $mynation['gold']-$_basegold) { $gold = $mynation['gold'] - $_basegold; }
    if($rice > $mynation['rice']-$_baserice) { $rice = $mynation['rice'] - $_baserice; }

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 원조 실패. <1>$date</>";
    } elseif($gold == 0 && $rice == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:보낼 물자가 부족합니다. 원조 실패. <1>$date</>";
    } elseif($gold < 0 || $rice < 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:보낼 물자가 부족합니다. 원조 실패. <1>$date</>";
    } elseif($gold > $limit || $rice > $limit) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:작위 제한량 이상은 보낼 수 없습니다. 원조 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 원조 실패. <1>$date</>";
    } elseif($mynation['surlimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:외교제한중입니다. 원조 실패. <1>$date</>";
    } elseif($younation['surlimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:상대국이 외교제한중입니다. 원조 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 원조 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 원조 실패. <1>$date</>";
    } else {
        // 본국 자원 감소
        $query = "update nation set gold=gold-'$gold',rice=rice-'$rice',surlimit=surlimit+12 where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 상대국 자원 증가
        $query = "update nation set gold=gold+'$gold',rice=rice+'$rice' where nation='$which'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //아국 수뇌부에게 로그 전달
        $query = "select no,name,nation from general where nation='{$general['nation']}' and level>='9'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>(으)로 금<C>$gold</> 쌀<C>$rice</>을 지원했습니다.";
            pushGenLog($gen, $genlog);
        }
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>(으)로 금<C>$gold</> 쌀<C>$rice</>을 지원");
        $mynation = addNationHistory($connect, $mynation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>(으)로 금<C>$gold</> 쌀<C>$rice</>을 지원");
        $younation = addNationHistory($connect, $younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>(으)로부터 금<C>$gold</> 쌀<C>$rice</>을 지원 받음");

        //상대국 수뇌부에게 로그 전달
        $query = "select no,name,nation from general where nation='$which' and level>='9'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            $genlog[0] = "<C>●</><D><b>{$mynation['name']}</b></>에서 금<C>$gold</> 쌀<C>$rice</>을 원조 했습니다.";
            pushGenLog($gen, $genlog);
        }

        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【원조】</b></><D><b>{$mynation['name']}</b></>에서 <D><b>{$younation['name']}</b></>(으)로 물자를 지원합니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>(으)로 물자를 지원합니다. <1>$date</>";

        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 경험치 상승        // 공헌도, 명성 상승
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//      $log = checkAbility($connect, $general, $log);
    }
    pushHistory($connect, $history);
    pushGenLog($general, $log);
}

function process_53($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select name,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,dip0,dip0_type,dip0_who,dip0_when,dip1,dip1_type,dip1_who,dip1_when,dip2,dip2_type,dip2_who,dip2_when,dip3,dip3_type,dip3_who,dip3_when,dip4,dip4_type,dip4_who,dip4_when from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 제의 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 제의 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>(으)로 통합 제의 서신을 보냈습니다.<1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 상대에게 발송
        //먼저 삭제된 칸 중 가장 오래된 칸 찾음
        $deleted = 4;
        for($i=0; $i < 5; $i++) {
            if($younation["dip{$i}"] == "") { $deleted = $i; }
        }
        //기존 메세지 한칸씩 뒤로 미룸
        for($i=$deleted-1; $i >=0; $i--) {
            moveMsg($connect, "nation", "dip", $i+1, $younatin["dip{$i}"], $younation["dip{$i}_type"], $younation["dip{$i}_who"], $younation["dip{$i}_when"], "nation", $younation['nation']);
        }
        //권고 서신시 장수번호/상대국 번호
        $me = $general['no'] * 10000 + $younation['nation'];
        $date = date('Y-m-d H:i:s');
        $query = "update nation set dip0='{$nation['name']}의 통합 제의 서신',dip0_type='5',dip0_who='$me',dip0_when='$date' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_54($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $command = DecodeCommand($general[turn0]);
    $who = $command[1];

    $query = "select no,name,nation,history from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nextruler = MYDB_fetch_array($result);

    $query = "select nation,name,history from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    //현재 외교 진행중(통합제의중)일때
    $query = "select state from diplomacy where me='{$general['nation']}' and state='4'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($nextruler['name'] == "") {
        $log[count($log)] = "<C>●</>{$admin['month']}월:잘못된 장수입니다. 선양 실패. <1>$date</>";
    } elseif($general['level'] != 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주가 아닙니다. 선양 실패. <1>$date</>";
    } elseif($nextruler['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:잘못된 장수입니다. 선양 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 통합 진행중입니다. 선양 실패.";
    } else {
        //군주 교체
        $query = "update general set level='12' where no='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 태수,군사,시중이었다면 해제
        $query = "update city set gen1='0' where gen1='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen2='0' where gen2='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen3='0' where gen3='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set resturn='SUCCESS',level='1',experience=experience*0.7 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【선양】</b></><Y>{$general['name']}</>(이)가 <D><b>{$nation['name']}</b></>의 군주 자리를 <Y>{$nextruler['name']}</>에게 선양했습니다.";
        $log[count($log)] = "<C>●</>{$admin['month']}월:<Y>{$nextruler['name']}</>에게 군주의 자리를 물려줍니다. <1>$date</>";
        $youlog[count($youlog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게서 군주의 자리를 물려받습니다.";

        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주자리를 <Y>{$nextruler['name']}</>에게 선양");
        $nextruler = addHistory($connect, $nextruler, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주자리를 물려 받음");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <Y>{$nextruler['name']}</>에게 선양");
    }
    pushGenLog($general, $log);
    pushGenLog($nextruler, $youlog);
    pushHistory($connect, $history);
}

function process_55($connect, &$general) {
    global $_baserice;
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation from nation where name='{$general['name']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);

    if($nationcount > 0) { $makename = _String::SubStr("$nationcount".$general['name'], 0, 6); }
    else { $makename = $general['name']; }

    if($general['level'] != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야가 아닙니다. 거병 실패. <1>$date</>";
    } elseif($admin['year'] >= $admin['startyear']+2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:거병 기간이 지났습니다. 거병 실패. <1>$date</>";
    } elseif($general['makelimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야가 된지 12시간이 지나야 합니다. 거병 실패. <1>$date</>";
    } else {
        $query = "insert into nation (name, color, gold, rice, rate, bill, tricklimit, surlimit, type, gennum) values ('$makename', '330000', '0', '$_baserice', '20' ,'100', '36', '72', '0', '1')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select nation,name,history from nation where name='$makename'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);

        $exp = 100;
        $ded = 100;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 명성 상승
        // 군주로
        // 현 국가 소속으로
        $query = "update general set resturn='SUCCESS',belong=1,level=12,nation='{$nation['nation']}',dedication=dedication+'$ded', experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</>{$admin['month']}월:거병에 성공하였습니다. <1>$date</>";
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$city['name']}</b></>에서 거병하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【거병】</b></><D><b>{$general['name']}</b></>(이)가 세력을 결성하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$city['name']}</b></>에서 거병");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$city['name']}</b></>에서 거병");

        // 외교테이블 추가
        $query = "select nation from nation where nation!='{$nation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nationcount = MYDB_num_rows($result);

        for($i=0; $i < $nationcount; $i++) {
            $younation = MYDB_fetch_array($result);
            $query = "insert into diplomacy (me, you, state, term) values ('{$nation['nation']}', '{$younation['nation']}', '2', '0')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "insert into diplomacy (me, you, state, term) values ('{$younation['nation']}', '{$nation['nation']}', '2', '0')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_56($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select city from city where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    if($general['level'] != 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주가 아닙니다. <1>$date</>";
    } elseif($citycount != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:방랑군이 아닙니다. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:세력을 해산했습니다. <1>$date</>";
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 세력을 해산했습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>(을)를 해산");

        $query = "select no from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $genCount = MYDB_num_rows($result);

        // 수동 해산인 국가 페널티, 자금, 군량
        if($genCount > 1) {
            $query = "update general set resturn='SUCCESS' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set gold=1000 where nation='{$general['nation']}' and gold>1000";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set rice=1000 where nation='{$general['nation']}' and rice>1000";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        //분쟁기록 모두 지움
        DeleteConflict($connect, $general['nation']);
        deleteNation($connect, $general);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_57($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,killturn from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,history from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select npc,no,name,killturn,history from general where nation='{$general['nation']}' and level='12'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $ruler = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:재야 입니다. 모반 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부 이상만 가능합니다. 모반 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 모반 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 모반 실패. <1>$date</>";
    } elseif($general['level'] == 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이미 군주 입니다. 모반 실패. <1>$date</>";
    } elseif($ruler['killturn'] >= $admin['killturn']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주가 활동중입니다. 모반 실패. <1>$date</>";
    } elseif($ruler['npc'] >= 2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:군주가 NPC입니다. 모반 실패. <1>$date</>";
    } else {
        //군주 교체
        $query = "update general set resturn='SUCCESS',level='12' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 태수,군사,시중이었다면 해제
        $query = "update city set gen1='0' where gen1='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen2='0' where gen2='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen3='0' where gen3='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set level='1',experience=experience*0.7 where no='{$ruler['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</>{$admin['month']}월:모반에 성공했습니다. <1>$date</>";
        $youlog[count($youlog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게서 군주의 자리를 뺏겼습니다.";
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <M>모반</>에 성공했습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【모반】</b></><Y>{$general['name']}</>(이)가 <D><b>{$nation['name']}</b></>의 군주 자리를 찬탈했습니다.";

        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:모반으로 <D><b>{$nation['name']}</b></>의 군주자리를 찬탈");
        $ruler = addHistory($connect, $ruler, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$general['name']}</b></>의 모반으로 인해 <D><b>{$nation['name']}</b></>의 군주자리를 박탈당함");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <Y>{$ruler['name']}</>에게서 군주자리를 찬탈");
    }
    pushGenLog($general, $log);
    pushGenLog($ruler, $youlog);
    pushAllLog($alllog);
    pushHistory($connect, $history);
}

function process_61($connect, &$general) {
    global $_basegold, $_baserice;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost,turnterm from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select gold,rice,name,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $when = $command[2];
    $which = $command[1];

    if($when < 1) { $when = 1; }
    elseif($when > 20) { $when = 20; }

    $query = "select nation,name,dip0,dip0_type,dip0_who,dip0_when,dip1,dip1_type,dip1_who,dip1_when,dip2,dip2_type,dip2_who,dip2_when,dip3,dip3_type,dip3_who,dip3_when,dip4,dip4_type,dip4_who,dip4_when from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 제의 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 제의 실패. <1>$date</>";
//    } elseif($nation['gold']-$_basegold < $admin['develcost'] || $nation['rice']-$_baserice < $admin['develcost']) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:증정할 물자가 부족합니다. 제의 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>으로 불가침 제의 서신을 보냈습니다.<1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 상대에게 발송
        //먼저 삭제된 칸 중 가장 오래된 칸 찾음
        $deleted = 4;
        for($i=0; $i < 5; $i++) {
            if($younation["dip{$i}"] == "") { $deleted = $i; }
        }
        //기존 메세지 한칸씩 뒤로 미룸
        for($i=$deleted-1; $i >=0; $i--) {
            moveMsg($connect, "nation", "dip", $i+1, $younatin["dip{$i}"], $younation["dip{$i}_type"], $younation["dip{$i}_who"], $younation["dip{$i}_when"], "nation", $younation['nation']);
        }
        //권고 서신시 장수번호/상대국 번호
        $me = $general['no'] * 10000 + $younation['nation'];
        $type = $when * 100 + 6;
        $date = date('Y-m-d H:i:s');
        $query = "update nation set dip0='{$nation['name']}의 {$when}년 불가침 제의 서신',dip0_type='$type',dip0_who='$me',dip0_when='$date' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 3턴후
        $date = addTurn($date, $admin['turnterm']);
        $date = addTurn($date, $admin['turnterm']);
        $date = addTurn($date, $admin['turnterm']);
        //조건 표시기한 설정
        $query = "update diplomacy set showing='{$date}' where me='{$general['nation']}' and you='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // $admin['develcost']*10씩 차감
//        $amount = $admin['develcost'];
//        $query = "update nation set gold=gold-'$amount',rice=rice-'$amount' where nation='{$general['nation']}'";
//        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_62($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,history,l{$general['level']}turn0,color from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,color,history from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    $query = "select * from general where nation='{$younation['nation']}' and level='12'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $king = MYDB_fetch_array($result);

    //아국과의 관계
    $query = "select state from diplomacy where me='{$nation['nation']}' and you='{$younation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);
    //대상국이 외교 진행중(합병수락중,통합수락중)일때
    $query = "select state from diplomacy where me='{$younation['nation']}' and (state='3' or state='5')";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 선포 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 선포 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 선포 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 선포 실패. <1>$date</>";
    } elseif($dip['state'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국과 이미 교전중입니다. 선포 실패. <1>$date</>";
    } elseif($dip['state'] == 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국과 이미 선포중입니다. 선포 실패. <1>$date</>";
    } elseif($dip['state'] == 7) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국과 불가침중입니다. 선포 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:상대국이 외교 진행중입니다. 선포 실패. <1>$date</>";
    } elseif(!isClose($connect, $nation['nation'], $younation['nation'])) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:인접하지 않았습니다. 선포 실패. <1>$date</>";
    } elseif($admin['year'] <= $admin['startyear']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:초반제한 해제 2년전부터 가능합니다. 선포 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>으로 선전 포고 했습니다.<1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$younation['name']}</b></>에 <M>선전 포고</> 하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【선포】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$younation['name']}</b></>에 선전 포고 하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>에 선전 포고");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$younation['name']}</b></>에 선전 포고");
        $younation = addNationHistory($connect, $younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 아국에 선전 포고");

        //외교 변경
        $query = "update diplomacy set state='1',term='24' where me='{$nation['nation']}' and you='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error("ally ".MYDB_error($connect),"");
        $query = "update diplomacy set state='1',term='24' where me='{$younation['nation']}' and you='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error("ally ".MYDB_error($connect),"");

        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //국메로 저장
        $msg = "【외교】{$admin['year']}년 {$admin['month']}월:{$younation['name']}에 선전포고";
        $youmsg = "【외교】{$admin['year']}년 {$admin['month']}월:{$nation['name']}에서 선전포고";

        PushMsg(2, $nation['nation'], $general['picture'], $general['imgsvr'], "{$general['name']}:{$nation['name']}▶", $nation['color'], $younation['name'], $younation['color'], $msg);
        PushMsg(3, $younation['nation'], $general['picture'], $general['imgsvr'], "{$general['name']}:{$nation['name']}▶", $nation['color'], $younation['name'], $younation['color'], $youmsg);
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_63($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select name,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,dip0,dip0_type,dip0_who,dip0_when,dip1,dip1_type,dip1_who,dip1_when,dip2,dip2_type,dip2_who,dip2_when,dip3,dip3_type,dip3_who,dip3_when,dip4,dip4_type,dip4_who,dip4_when from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 제의 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 제의 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>으로 종전 제의 서신을 보냈습니다. <1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 상대에게 발송
        //먼저 삭제된 칸 중 가장 오래된 칸 찾음
        $deleted = 4;
        for($i=0; $i < 5; $i++) {
            if($younation["dip{$i}"] == "") { $deleted = $i; }
        }
        //기존 메세지 한칸씩 뒤로 미룸
        for($i=$deleted-1; $i >=0; $i--) {
            moveMsg($connect, "nation", "dip", $i+1, $younatin["dip{$i}"], $younation["dip{$i}_type"], $younation["dip{$i}_who"], $younation["dip{$i}_when"], "nation", $younation['nation']);
        }
        //권고 서신시 장수번호/상대국 번호
        $me = $general['no'] * 10000 + $younation['nation'];
        $date = date('Y-m-d H:i:s');
        $query = "update nation set dip0='{$nation['name']}의 종전 제의 서신',dip0_type='7',dip0_who='$me',dip0_when='$date' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_64($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select name,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,dip0,dip0_type,dip0_who,dip0_when,dip1,dip1_type,dip1_who,dip1_when,dip2,dip2_type,dip2_who,dip2_when,dip3,dip3_type,dip3_who,dip3_when,dip4,dip4_type,dip4_who,dip4_when from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($younation['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 제의 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 제의 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 제의 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>으로 파기 제의 서신을 보냈습니다. <1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 상대에게 발송
        //먼저 삭제된 칸 중 가장 오래된 칸 찾음
        $deleted = 4;
        for($i=0; $i < 5; $i++) {
            if($younation["dip{$i}"] == "") { $deleted = $i; }
        }
        //기존 메세지 한칸씩 뒤로 미룸
        for($i=$deleted-1; $i >=0; $i--) {
            moveMsg($connect, "nation", "dip", $i+1, $younatin["dip{$i}"], $younation["dip{$i}_type"], $younation["dip{$i}_who"], $younation["dip{$i}_when"], "nation", $younation['nation']);
        }
        //권고 서신시 장수번호/상대국 번호
        $me = $general['no'] * 10000 + $younation['nation'];
        $date = date('Y-m-d H:i:s');
        $query = "update nation set dip0='{$nation['name']}의 불가침 파기 서신',dip0_type='8',dip0_who='$me',dip0_when='$date' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//        $log = checkAbility($connect, $general, $log);
    }
    pushGenLog($general, $log);
}

function process_65($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select city from city where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $query = "select nation,capital,name,surlimit,history,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation,pop,gen1,gen2,gen3 from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    //아국이 외교중(교전, 선포, 합병, 통합 // 즉 !통상, !불가침)일때
    $query = "select state from diplomacy where me='{$nation['nation']}' and (state!='2' and state!='7')";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 초토화 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 초토화 실패. <1>$date</>";
    } elseif($nation['capital'] == $destcity['city']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수도입니다. 초토화 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 초토화 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 영토가 아닙니다. 초토화 실패. <1>$date</>";
    } elseif($citycount <= 4) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:더이상 물러날 수 없습니다. 초토화 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:평시에만 가능합니다. 초토화 실패. <1>$date</>";
    } elseif($nation['surlimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:제한 턴이 있습니다. 초토화 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(을)를 초토화했습니다. <1>$date</>";
        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>의 <R>초토화</>를 명령하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【초토화】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>(을)를 <R>초토화</>하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>의 <R>초토화</>를 명령");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>의 <R>초토화</>를 명령");

        //외교제한 24
        $amount = round($destcity['pop'] * 0.1);
        $query = "update nation set surlimit='24',gold=gold+'$amount',rice=rice+'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //직위해제
        $query = "update general set level=1 where no='$destcity[gen1]' or no='$destcity[gen2]' or no='$destcity[gen3]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //성 공백지로
        $query = "update city set pop=pop*0.1,rate=50,agri=agri*0.1,comm=comm*0.1,secu=secu*0.1,nation='0',front='0',gen1='0',gen2='0',gen3='0',conflict='',conflict2='' where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //전장수 10% 삭감
        $query = "update general set experience=experience*0.9,dedication=dedication*0.9 where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_66($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,gold,rice,surlimit,history,l{$general['level']}term,l{$general['level']}turn0,capital,capset from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $dist = distance($connect, $nation['capital'], 1);
    $amount = $admin['develcost'] * 10;

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 66) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 66;
    } else {
        $term = 1;
        $code = 100 + 66;
    }

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 천도 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 천도 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 천도 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 영토가 아닙니다. 천도 실패. <1>$date</>";
    } elseif($dist[$destcity['city']] != 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:인접도시가 아닙니다. 천도 실패. <1>$date</>";
    } elseif($nation['capset'] == 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:다음 분기에 가능합니다. 천도 실패. <1>$date</>";
    } elseif($nation['gold']-$_basegold < $amount || $nation['rice']-$_baserice < $amount) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:물자가 부족합니다. 천도 실패. <1>$date</>";
    } elseif($term < 3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:천도중... ({$term}/3) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(으)로 천도했습니다. <1>$date</>";
        $exp = 15;
        $ded = 15;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>(으)로 <R>천도</>를 명령하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<S><b>【천도】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>(으)로 천도하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>(으)로 천도 명령");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>(으)로 천도 명령");

        //수도 변경
        $query = "update nation set l{$general['level']}term='0',capital='{$destcity['city']}',capset='1',gold=gold-'$amount',rice=rice-'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_67($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,gold,rice,surlimit,history,l{$general['level']}term,l{$general['level']}turn0,capital,capset from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation,level from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $amount = $admin['develcost'] * 500 + 60000;   // 7만~13만

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 67) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 67;
    } else {
        $term = 1;
        $code = 100 + 67;
    }

    if($nation['capital'] != $general['city']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수도에서 실행해야 합니다. 증축 실패. <1>$date</>";
    } elseif($nation['capital'] != $destcity['city']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수도만 가능합니다. 증축 실패. <1>$date</>";
    } elseif($destcity['level'] <= 3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수진, 진, 관문은 불가능합니다. 증축 실패. <1>$date</>";
    } elseif($destcity['level'] >= 8) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:더이상 증축할 수 없습니다. 증축 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 증축 실패. <1>$date</>";
    } elseif($nation['capset'] == 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:다음 분기에 가능합니다. 증축 실패. <1>$date</>";
    } elseif($nation['gold']-$_basegold < $amount || $nation['rice']-$_baserice < $amount) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:물자가 부족합니다. 증축 실패. <1>$date</>";
    } elseif($term < 6) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:증축중... ({$term}/6) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(을)를 증축했습니다. <1>$date</>";
        $exp = 5 * 6;
        $ded = 5 * 6;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>(을)를 <C>증축</>하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【증축】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>(을)를 증축하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>(을)를 증축");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>(을)를 증축");

        //물자 감소
        $query = "update nation set l{$general['level']}term='0',capset='1',gold=gold-'$amount',rice=rice-'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //수도 증축
        $query = "update city set upgrading=upgrading+1,level=level+1,pop2=pop2+100000,agri2=agri2+2000,comm2=comm2+2000,def2=def2+2000,wall2=wall2+2000,secu2=secu2+2000 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_68($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,gold,rice,surlimit,history,l{$general['level']}term,l{$general['level']}turn0,capital,capset from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation,level,pop,agri,comm,def,wall,secu,upgrading from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $amount = $admin['develcost'] * 500 + 30000;   // 4만~10만

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 68) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 68;
    } else {
        $term = 1;
        $code = 100 + 68;
    }

    if($nation['capital'] != $general['city']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수도에서 실행해야 합니다. 감축 실패. <1>$date</>";
    } elseif($nation['capital'] != $destcity['city']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수도만 가능합니다. 감축 실패. <1>$date</>";
    } elseif($destcity['level'] <= 3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수진, 진, 관문은 불가능합니다. 감축 실패. <1>$date</>";
    } elseif($destcity['level'] <= 6) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:더이상 감축할 수 없습니다. 감축 실패. <1>$date</>";
    } elseif($destcity['upgrading'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:증축된 도시가 아닙니다. 감축 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 감축 실패. <1>$date</>";
    } elseif($nation['capset'] == 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:다음 분기에 가능합니다. 감축 실패. <1>$date</>";
//    } elseif($nation['gold']-$_basegold < $amount || $nation['rice']-$_baserice < $amount) {
//        $log[count($log)] = "<C>●</>{$admin['month']}월:물자가 부족합니다. 감축 실패. <1>$date</>";
    } elseif($term < 6) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:감축중... ({$term}/6) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(을)를 감축했습니다. <1>$date</>";
        $exp = 5 * 6;
        $ded = 5 * 6;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>(을)를 <M>감축</>하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【감축】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>(을)를 감축하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>(을)를 감축");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>(을)를 감축");

        //물자 증가
        $query = "update nation set l{$general['level']}term='0',capset='1',gold=gold+'$amount',rice=rice+'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $pop  = $destcity['pop']  - 100000;
        $agri = $destcity['agri'] - 2000;
        $comm = $destcity['comm'] - 2000;
        $def  = $destcity['def']  - 2000;
        $wall = $destcity['wall'] - 2000;
        $secu = $destcity['secu'] - 2000;
        if($pop  < 30000) { $pop  = 30000; }
        if($agri < 0)  { $agri = 0;  }
        if($comm < 0)  { $comm = 0;  }
        if($def  < 0)  { $def  = 0;  }
        if($wall < 0)  { $wall = 0;  }
        if($secu < 0)  { $secu = 0;  }
        //수도 감축
        $query = "update city set upgrading=upgrading-1,level=level-1,pop2=pop2-100000,agri2=agri2-2000,comm2=comm2-2000,def2=def2-2000,wall2=wall2-2000,secu2=secu2-2000,pop='$pop',agri='$agri',comm='$comm',def='$def',wall='$wall',secu='$secu' where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_71($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    //$term2 = round($genCount / 10);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = round(sqrt($genCount*8)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 71) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 71;
    } else {
        $term = 1;
        $code = 100 + 71;
    }

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 필사즉생 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 필사즉생 실패. <1>$date</>";
    } elseif($dipCount == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전쟁중이 아닙니다. 필사즉생 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 필사즉생 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:필사즉생 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:필사즉생 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <M>필사즉생</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <M>필사즉생</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <M>필사즉생</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <M>필사즉생</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<M>필사즉생</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <M>필사즉생</>(을)를 발동");

        //전장수 훈사100
        $query = "update general set atmos=100,train=100 where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_72($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    //$term2 = round($genCount / 20);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 1;
    $term3 = round(sqrt($genCount*4)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 72) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 72;
    } else {
        $term = 1;
        $code = 100 + 72;
    }

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 백성동원 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 백성동원 실패. <1>$date</>";
    } elseif($dipCount == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전쟁중이 아닙니다. 백성동원 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 백성동원 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국 도시만 가능합니다. 백성동원 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:백성동원 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:백성동원 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>백성동원</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>백성동원</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>에 <M>백성동원</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>에 <M>백성동원</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>에 <M>백성동원</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>백성동원</>(을)를 발동");

        //도시 성수 80%
        $query = "update city set def=def2*0.8,wall=wall2*0.8 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_73($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select nation,name,history from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    //$term2 = round($genCount / 20);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = round(sqrt($genCount*4)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 73) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 73;
    } else {
        $term = 1;
        $code = 100 + 73;
    }

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 수몰 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 수몰 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. 수몰 실패. <1>$date</>";
    } elseif($destcity['nation'] == $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:대상도시가 아국입니다. 수몰 실패. <1>$date</>";
    } elseif($dip['state'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전쟁중인 상대국에만 가능합니다. 수몰 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 수몰 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수몰 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수몰 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destcity['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><G><b>{$destcity['name']}</b></>에 <M>수몰</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>의 <G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동");
        $destnation = addNationHistory($connect, $destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 아국의 <G><b>{$destcity['name']}</b></>에 <M>수몰</>(을)를 발동");

        //도시 성수 80% 감소
        $query = "update city set def=def*0.2,wall=wall*0.2 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_74($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select nation,name,history from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    //$term2 = round($genCount / 20);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 2;
    $term3 = round(sqrt($genCount*4)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 74) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 74;
    } else {
        $term = 1;
        $code = 100 + 74;
    }

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 허보 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 허보 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:공백지입니다. 허보 실패. <1>$date</>";
    } elseif($destcity['nation'] == $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:대상도시가 아국입니다. 허보 실패. <1>$date</>";
    } elseif($dip['state'] > 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:선포,전쟁중인 상대국에만 가능합니다. 허보 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 허보 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:허보 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:허보 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>의 <G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동");
        $destnation = addNationHistory($connect, $destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 아국의 <G><b>{$destcity['name']}</b></>에 <M>허보</>(을)를 발동");

        //상대국 도시 전부 검색
        $query = "select city from city where nation='{$destcity['nation']}' and supply=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cityCount = MYDB_num_rows($result);
        for($i=0; $i < $cityCount; $i++) {
            $dCity = MYDB_fetch_array($result);
            $citys[$i] = $dCity['city'];
        }
        //상대국 유저 랜덤 배치
        $query = "select no,name from general where nation='{$destcity['nation']}' and city='{$destcity['city']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        $opplog[0] = "<C>●</>상대국의 허보에 당했다! <1>$date</>";
        for($i=0; $i < $count; $i++) {
            $gen = MYDB_fetch_array($result);
            $selCity = $citys[rand() % $cityCount];
            //현재도시이면 한번 다시 랜덤추첨
            if($selCity == $destcity['city']) { $selCity = $citys[rand() % $cityCount]; }

            $query = "update general set city={$selCity} where no='{$gen['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($gen, $opplog);
        }

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_75($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,history from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    //$term2 = round($genCount / 40);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = round(sqrt($genCount*2)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 75) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 75;
    } else {
        $term = 1;
        $code = 100 + 75;
    }

    if(!$destnation) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 국가입니다. 피장파장 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 피장파장 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 피장파장 실패. <1>$date</>";
    } elseif($dip['state'] > 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:선포,전쟁중인 상대국에만 가능합니다. 피장파장 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 피장파장 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:피장파장 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:피장파장 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>피장파장</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destnation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</>아국에 <M>피장파장</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>피장파장</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$destnation['name']}</b></>에 <M>피장파장</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$destnation['name']}</b></>에 <M>피장파장</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$destnation['name']}</b></>에 <M>피장파장</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>피장파장</>(을)를 발동");
        $destnation = addNationHistory($connect, $destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 아국에 <M>피장파장</>(을)를 발동");

        //전략기한+60
        $query = "update nation set tricklimit=tricklimit+60 where nation='{$destnation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한, 최소72
        if($term3 < 72) { $term3 = 72; }
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_76($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select startyear,year,month,develcost,npccount,turnterm from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    //$term2 = round($genCount / 10);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = round(sqrt($genCount*10)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 76) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 76;
    } else {
        $term = 1;
        $code = 100 + 76;
    }

    if($admin['year'] < $admin['startyear']+3) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 초반 제한중입니다. 의병모집 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 의병모집 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 의병모집 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 의병모집 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:의병모집 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:의병모집 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <M>의병모집</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <M>의병모집</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <M>의병모집</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <M>의병모집</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<M>의병모집</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <M>의병모집</>(을)를 발동");

        $query = "select avg(gennum) as gennum from nation";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $avgNation = MYDB_fetch_array($result);
        $gencount = 5 + round($avgNation['gennum'] / 10);

        $query = "select avg(age) as age, max(leader+power+intel) as lpi, avg(dedication) as ded,avg(experience) as exp, avg(dex0) as dex0, avg(dex10) as dex10, avg(dex20) as dex20, avg(dex30) as dex30, avg(dex40) as dex40 from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $avgGen = MYDB_fetch_array($result);

        //의병추가
        $npc = 4;
        $npcid = $admin['npccount'];
        for($i=0; $i < $gencount; $i++) {
            // 무장 40%, 지장 40%, 무지장 20%
            $type = rand() % 10;
            switch($type) {
            case 0: case 1: case 2: case 3:
                $leader = 65 + rand()%11;
                $intel = 10 + rand()%6;
                $power = 150 - $leader - $intel;
                break;
            case 4: case 5: case 6: case 7:
                $leader = 65 + rand()%11;
                $power = 10 + rand()%6;
                $intel = 150 - $leader - $power;
                break;
            case 8: case 9:
                $leader = 10 + rand()%6;
                $power = 65 + rand()%11;
                $intel = 150 - $leader - $power;
                break;
            }
            // 국내 최고능치 기준으로 랜덤성 스케일링
            if($avgGen['lpi'] > 210) {
                $leader = round($leader * $avgGen['lpi'] / 150 * (60+rand()%31)/100);
                $power = round($power * $avgGen['lpi'] / 150 * (60+rand()%31)/100);
                $intel = round($intel * $avgGen['lpi'] / 150 * (60+rand()%31)/100);
            } elseif($avgGen['lpi'] > 180) {
                $leader = round($leader * $avgGen['lpi'] / 150 * (75+rand()%21)/100);
                $power = round($power * $avgGen['lpi'] / 150 * (75+rand()%21)/100);
                $intel = round($intel * $avgGen['lpi'] / 150 * (75+rand()%21)/100);
            } else {
                $leader = round($leader * $avgGen['lpi'] / 150 * (90+rand()%11)/100);
                $power = round($power * $avgGen['lpi'] / 150 * (90+rand()%11)/100);
                $intel = round($intel * $avgGen['lpi'] / 150 * (90+rand()%11)/100);
            }
            $over1 = 0;
            $over2 = 0;
            $over3 = 0;
            // 너무 높은 능치는 다른 능치로 분산
            if($leader > 90) {
                $over1 = rand() % ($leader - 90) + 5;
                $leader -= $over1;
            }
            if($power > 90) {
                $over2 = rand() % ($power - 90) + 5;
                $power -= $over2;
            }
            if($intel > 90) {
                $over3 = rand() % ($intel - 90) + 5;
                $intel -= $over3;
            }
            // 낮은 능치쪽으로 합산
            if($type == 0) {
                $intel = $intel + $over1 + $over2 + $over3;
            } else {
                $power = $power + $over1 + $over2 + $over3;
            }
            // 너무 높은 능치는 제한
            if($leader > 95) {
                $leader = 95;
            }
            if($power > 95) {
                $power = 95;
            }
            if($intel > 95) {
                $intel = 95;
            }

            $npccount = 10000 + $npcid;
            $npcmatch = rand() % 150 + 1;
            $genid = "gen{$npccount}";
            $pw = md5("18071807");
            $name = "ⓖ의병장{$npcid}";
            $picture = 'default.jpg';
            $turntime = getRandTurn($admin['turnterm']);
            $personal = rand() % 10;
            $bornyear = $admin['year'];
            $deadyear = $admin['year'] + 3;
            $killturn = 64 + rand()%7;

            @MYDB_query("
                insert into general (
                    npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,
                    city,leader,power,intel,experience,dedication,
                    level,gold,rice,crew,crewtype,train,atmos,tnmt,
                    weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
                    makelimit,bornyear,deadyear,
                    dex0, dex10, dex20, dex30, dex40
                ) values (
                    '$npccount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','{$nation['nation']}',
                    '{$general['city']}','$leader','$power','$intel','{$avgGen['exp']}','{$avgGen['ded']}',
                    '1','100','100','0','0','0','0','0',
                    '0','0','0','$turntime','$killturn','{$avgGen['age']}','1','$personal','0','0','0','0','',
                    '0','$bornyear','$deadyear',
                    '$avgGen[dex0]','$avgGen[dex10]','$avgGen[dex20]','$avgGen[dex30]','$avgGen[dex40]'
                )",
                $connect
            ) or Error(__LINE__.MYDB_error($connect),"");

            $npcid++;
        }
        //npccount
        $query = "update game set npccount={$npcid} where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //국가 기술력 그대로
        $query = "select no from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $gennum = $gencount;
        if($gencount < 10) $gencount = 10;

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한, 국가 기술력 그대로
        $query = "update nation set tricklimit={$term3},totaltech=tech*'$gencount',gennum='$gennum' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_77($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,history from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state,term from diplomacy where me='{$general['nation']}' and you='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    $term2 = 1;
    $term3 = round(sqrt($genCount*16)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 77) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 77;
    } else {
        $term = 1;
        $code = 100 + 77;
    }

    if(!$destnation) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 국가입니다. 이호경식 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 이호경식 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 이호경식 실패. <1>$date</>";
    } elseif($dip['state'] > 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:선포,전쟁중인 상대국에만 가능합니다. 이호경식 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 이호경식 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이호경식 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:이호경식 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>이호경식</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destnation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</>아국에 <M>이호경식</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>이호경식</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$destnation['name']}</b></>에 <M>이호경식</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$destnation['name']}</b></>에 <M>이호경식</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$destnation['name']}</b></>에 <M>이호경식</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>이호경식</>(을)를 발동");
        $destnation = addNationHistory($connect, $destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 아국에 <M>이호경식</>(을)를 발동");

        //선포+3개월
        if($dip['state'] == 0) {
            $query = "update diplomacy set state=1,term=3 where (me='{$general['nation']}' and you='{$destnation['nation']}') or (you='{$general['nation']}' and me='{$destnation['nation']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update diplomacy set term=term+3 where (me='{$general['nation']}' and you='{$destnation['nation']}') or (you='{$general['nation']}' and me='{$destnation['nation']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_78($connect, &$general) {
    global $_basegold, $_baserice;

    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,history from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state,term from diplomacy where me='{$general['nation']}' and you='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < 10) { $genCount = 10; }

    $term2 = 1;
    $term3 = round(sqrt($genCount*16)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 78) {
        $term = floor($code/100) + 1;
        $code = $term * 100 + 78;
    } else {
        $term = 1;
        $code = 100 + 78;
    }

    if(!$destnation) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:없는 국가입니다. 급습 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 급습 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 급습 실패. <1>$date</>";
    } elseif($dip['state'] != 1) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:선포중인 상대국에만 가능합니다. 급습 실패. <1>$date</>";
    } elseif($dip['term'] < 12) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:선포 12개월 이상인 상대국에만 가능합니다. 급습 실패. <1>$date</>";
    } elseif($nation['tricklimit'] > 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 급습 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:급습 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:급습 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>급습</>(을)를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destnation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</>아국에 <M>급습</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>급습</>(을)를 발동하였습니다.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$destnation['name']}</b></>에 <M>급습</>(을)를 발동하였습니다.";
        $tricklog[count($tricklog)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>(이)가 <D><b>{$destnation['name']}</b></>에 <M>급습</>(을)를 발동하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$destnation['name']}</b></>에 <M>급습</>(을)를 발동");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <D><b>{$destnation['name']}</b></>에 <M>급습</>(을)를 발동");
        $destnation = addNationHistory($connect, $destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 아국에 <M>급습</>(을)를 발동");

        //선포-3개월
        $query = "update diplomacy set term=term-3 where (me='{$general['nation']}' and you='{$destnation['nation']}') or (you='{$general['nation']}' and me='{$destnation['nation']}')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set tricklimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushHistory($connect, $history);
//    pushAllLog($alllog);
    pushTrickLog($connect, $tricklog);
    pushGenLog($general, $log);
}

function process_81($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,colset,name,type,tricklimit,history,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];
    $colors = GetNationColors();
    if($which >= count($colors)) { $which = 0; }
    $color = $colors[$which];

    if($city['nation'] != $general['nation']) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 국기변경 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 국기변경 실패. <1>$date</>";
    } elseif($nation['colset'] <= 0) {
        $log[count($log)] = "<C>●</>{$admin['month']}월:더이상 변경이 불가능합니다. 국기변경 실패. <1>$date</>";
    } else {
        $log[count($log)] = "<C>●</>{$admin['month']}월:<font color={$color}><b>국기</b></font>를 변경합니다. <1>$date</>";
        $exp = 10;
        $ded = 10;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><Y>{$general['name']}</>(이)가 <font color={$color}><b>국기</b></font>를 변경합니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
        }

        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <font color={$color}><b>국기</b></font>를 변경하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【국기변경】</b></><D><b>{$nation['name']}</b></>(이)가 <font color={$color}><b>국기</b></font>를 변경하였습니다.";
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<font color={$color}><b>국기</b></font>를 변경");
        $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>(이)가 <font color={$color}><b>국기</b></font>를 변경");

        //국기변경
        $query = "update nation set color='$color',colset=colset-1 where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushHistory($connect, $history);
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_99($connect, &$general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $log[count($log)] = "<C>●</>{$admin['month']}월:아직 구현되지 않았습니다. <1>$date</>";

    $exp = 100;

    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    $ded = CharDedication($ded, $general['personal']);

    // 명성 상승
    $query = "update general set experience=experience+'$exp' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushGenLog($general, $log);
}

