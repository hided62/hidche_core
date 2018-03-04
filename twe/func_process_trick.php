<?php


function process_32($connect, &$general) {
    $log = array();
    $alllog = array();
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $nation = getNationStaticInfo($general['nation']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[] = "<C>●</>{$admin['month']}월:없는 도시입니다. 화계 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $nation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 화계 실패. <1>$date</>";
    } else {
        $query = "select leader,horse,power,weap,intel,book,injury from general where city='$destination' and nation='{$destcity['nation']}' order by intel desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $intelgen = MYDB_fetch_array($result);

        $ratio = round(((getGeneralIntel($general, true, true, true, false) - getGeneralIntel($intelgen, true, true, true, false)) / $_firing - ($destcity['secu']/$destcity['secu2'])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general['special2'] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($nation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(이)가 불타고 있습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 화계가 성공했습니다. <1>$date</>";

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
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 화계가 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general['intel2']++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',intel2='{$general['intel2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_33($connect, &$general) {
    $log = array();
    $alllog = array();
    global $_firing, $_basefiring, $_firingpower;
    //탈취는 0까지 무제한
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select name,level,nation,secu,secu2 from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select gold,rice from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $mynation = getNationStaticInfo($general['nation']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[] = "<C>●</>{$admin['month']}월:없는 도시입니다. 탈취 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 탈취 실패. <1>$date</>";
    } else {
        $query = "select leader,horse,power,weap,intel,book,injury from general where city='$destination' and nation='{$destcity['nation']}' order by power desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $powergen = MYDB_fetch_array($result);

        $ratio = round(((getGeneralPower($general, true, true, true, false) - getGeneralPower($powergen, true, true, true, false)) / $_firing - ($destcity['secu']/$destcity['secu2'])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general['special2'] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에서 금과 쌀을 도둑맞았습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 탈취가 성공했습니다. <1>$date</>";

            // 탈취 최대 400 * 8
            $gold = (rand() % $_firingpower + $_firingbase) * $destcity['level'];
            $rice = (rand() % $_firingpower + $_firingbase) * $destcity['level'];

            $nation['gold'] -= $gold;
            $nation['rice'] -= $rice;
            if($nation['gold'] < GameConst::minNationalgold) { $gold += ($nation['gold'] - GameConst::minNationalgold); $nation['gold'] = GameConst::minNationalgold; }
            if($nation['rice'] < GameConst::minNationalrice) { $rice += ($nation['rice'] - GameConst::minNationalrice); $nation['rice'] = GameConst::minNationalrice; }
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
            $log[] = "<C>●</>금<C>$gold</> 쌀<C>$rice</>을 획득했습니다.";

//            TrickInjury($connect, $destination);
            $exp = rand() % 100 + 201;
            $ded = rand() % 70 + 141;
        } else {
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 탈취가 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general['power2']++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',power2='{$general['power2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_34($connect, &$general) {
    $log = array();
    $alllog = array();
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select name,nation,def,wall,secu,secu2 from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $mynation = getNationStaticInfo($general['nation']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[] = "<C>●</>{$admin['month']}월:없는 도시입니다. 파괴 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 파괴 실패. <1>$date</>";
    } else {
        $query = "select leader,horse,power,weap,intel,book,injury from general where city='$destination' and nation='{$destcity['nation']}' order by power desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $powergen = MYDB_fetch_array($result);

        $ratio = round(((getGeneralPower($general, true, true, true, false) - getGeneralPower($powergen, true, true, true, false)) / $_firing - ($destcity['secu']/$destcity['secu2'])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general['special2'] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[] = "<C>●</>{$admin['month']}월:누군가가 <G><b>{$destcity['name']}</b></>의 성벽을 허물었습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 파괴가 성공했습니다. <1>$date</>";

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
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 파괴가 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general['power2']++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',power2='{$general['power2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_35($connect, &$general) {
    $log = array();
    $alllog = array();
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select name,nation,rate,secu,secu2 from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $mynation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $mynation['level']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[] = "<C>●</>{$admin['month']}월:없는 도시입니다. 선동 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 선동 실패. <1>$date</>";
    } else {
        $query = "select leader,horse,power,weap,intel,book,injury from general where city='$destination' and nation='{$destcity['nation']}' order by sum desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen = MYDB_fetch_array($result);

        $ratio = round(((getGeneralLeadership($general, true, true, true) - getGeneralLeadership($gen, true, true, true)) / $_firing - ($destcity['secu']/$destcity['secu2'])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general['special2'] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>의 백성들이 동요하고 있습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 선동이 성공했습니다. <1>$date</>";

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
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 선동이 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general['leader2']++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',leader2='{$general['leader2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}

function process_36($connect, &$general) {
    return process_99($connect, $general);
    $log = array();
    $alllog = array();
    global $_firing, $_basefiring, $_firingpower;
    $date = substr($general['turntime'],11,5);

    $query = "select year,month,develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $dist = distance($connect, $general['city'], 5);
    $command = DecodeCommand($general['turn0']);
    $destination = $command[1];

    $query = "select * from city where city='$destination'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $mynation = getNationStaticInfo($general['nation']);

    $lbonus = setLeadershipBonus($general, $mynation['level']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    if(!$destcity) {
        $log[] = "<C>●</>{$admin['month']}월:없는 도시입니다. 기습 실패. <1>$date</>";
    } elseif($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['nation'] != $city['nation'] && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($city['supply'] == 0 && $mynation['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['gold'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['rice'] < $admin['develcost']*5) {
        $log[] = "<C>●</>{$admin['month']}월:군량이 모자랍니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($general['nation'] == $destcity['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } elseif($dip['state'] >= 7) {
        $log[] = "<C>●</>{$admin['month']}월:불가침국입니다. <G><b>{$destcity['name']}</b></>에 기습 실패. <1>$date</>";
    } else {
        $query = "select ROUND((leader+intel+power)*(100-injury)/100)+weap+horse+book as sum,weap,horse,book from general where city='$destination' and nation='{$destcity['nation']}' order by sum desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen = MYDB_fetch_array($result);

        $generalStatAll = getGeneralLeadership($general, true, true, true)
         + getGeneralPower($general, true, true, true)
         + getGeneralIntel($general, true, true, true);
        $ratio = round((($generalStatAll - ($gen['sum']+getWeapEff($gen['weap'])+getHorseEff($gen['horse'])+getBookEff($gen['book']))) / $_firing - ($destcity['secu']/$destcity['secu2'])/5 + $_basefiring)*100);
        $ratio2 = rand() % 100;

        if($general['item'] == 5) {
            // 이추 사용
            $ratio += 10;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] == 6) {
            // 향낭 사용
            $ratio += 20;
            $query = "update general set item=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
            $general['item'] = 0;
        } elseif($general['item'] >= 21 && $general['item'] <= 22) {
            // 육도, 삼략 사용
            $ratio += 20;
        }

        // 특기보정 : 신산, 귀모
        if($general['special2'] == 41) { $ratio += 10; }
        if($general['special'] == 31) { $ratio += 20; }

        // 국가보정
        if($mynation['type'] == 9) { $ratio += 10; }

        // 거리보정
        $ratio /= $dist[$destination];

        if($ratio > $ratio2) {
            $alllog[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>(이)가 누군가에게 공격 받았습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 기습이 성공했습니다. <1>$date</>";

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
            $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>에 기습이 실패했습니다. <1>$date</>";
            $exp = rand() % 100 + 1;
            $ded = rand() % 70 + 1;
        }

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $general['leader2']++;
        $general['intel2']++;
        $general['power2']++;
        $general['gold'] -= $admin['develcost']*5;
        $general['rice'] -= $admin['develcost']*5;
        $query = "update general set resturn='SUCCESS',gold='{$general['gold']}',rice='{$general['rice']}',leader2='{$general['leader2']}',intel2='{$general['intel2']}',power2='{$general['power2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($connect, $general, $log);
    }
    pushAllLog($alllog);
    pushGenLog($general, $log);
}
