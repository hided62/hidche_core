<?php
namespace sammo;

function process_22(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear','year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general['turn0']);
    $who = $command[1];

    $query = "select * from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $you = MYDB_fetch_array($result);

    $cost = Util::round($admin['develcost'] + ($you['experience'] + $you['dedication'])/1000) * 10;

    if(!$you) {
        $log[] = "<C>●</>{$admin['month']}월:없는 장수입니다. 등용 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:초반 제한중입니다. 등용 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 등용 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 등용 실패. <1>$date</>";
    } elseif($general['gold'] < $cost) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 등용 실패. <1>$date</>";
    } else {
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$you['name']}</>에게 등용 권유 서신을 보냈습니다. <1>$date</>";
        $exp = 100;
        $ded = 200;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $msg = ScoutMessage::buildScoutMessage($general['no'], $who, $reason);
        if($msg){
            $msg->send(true);
        }
        
        
        $general['intel2']++;
        $query = "update general set resturn='SUCCESS',gold=gold-'$cost',intel2='{$general['intel2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
}

function process_25(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'scenario', 'fiction']);

    $query = "select nation from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $command = DecodeCommand($general['turn0']);
    $where = $command[1];

    $nation = null;

    $joinedNations = Json::decode($general['nations']);

    // 랜덤임관인 경우
    if($general['npc'] > 2 && $where >= 98 && ($admin['scenario'] < 100 || $admin['scenario'] >= 2000 || !$admin['fiction'])){
        //'사실' 모드에서는 '성향'에 우선을 두되, 장수수, 랜덤에 비중을 둠
        $nations = $db->query(
            'SELECT nation.`name` as `name`,nation.nation as nation,scout,nation.`level` as `level`,gennum,`affinity` FROM nation join general on general.nation = nation.nation and general.level = 12 WHERE nation.nation not in %li and gennum < %i and scout = 0',
            $joinedNations,
            ($admin['year'] < $admin['startyear']+3)?GameConst::$initialNationGenLimit:GameConst::$defaultMaxGeneral
        );
        shuffle($nations);

        $allGen = array_sum(array_map(function($item) { 
            return $item['gennum']; 
        }, $nations));

        $maxScore = 1<<30;

        foreach($nations as $testNation){
            $affinity = abs($general['affinity'] - $testNation['affinity']);
            $affinity = min($affinity, abs($affinity - 150));

            $score = log($affinity + 1, 2);//0~

            //쉐킷쉐킷
            $score += Util::randF();

            $score += sqrt($testNation['gennum']/$allGen);

            if($score < $maxScore){
                $maxScore = $score;
                $nation = $testNation;
            }
        }
            
    }
    else if($where >= 98) {
        //랜임
        $generals = [];
        foreach($db->queryAllLists('SELECT count(no), nation FROM general WHERE npc <= 2 AND nation > 0 GROUP BY nation') as list($cnt, $nation)){
            $generals[$nation] = $cnt;
        }

        $allGen = array_sum($generals);

        $nations = $db->query(
            'SELECT nation.`name` as `name`,nation.nation as nation,scout,nation.`level` as `level`,gennum,`injury` FROM nation join general on general.nation = nation.nation and general.level = 12 WHERE nation.nation not in %li and gennum < %i and scout = 0',
            $joinedNations,
            ($admin['year'] < $admin['startyear']+3)?GameConst::$initialNationGenLimit:GameConst::$defaultMaxGeneral
        );
        shuffle($nations);

        $randVals = [];
        foreach($nations as $idx=>$testNation){
            // 임관금지없음 국가, 방랑군 제외
            if($where == 98 && $testNation['level'] == 0){
                continue;
            }

            $score = 1;
            if($admin['startyear']+3 > $admin['year'] && $general['npc'] > 2){
                $score *= sqrt((100-max(30, $testNation['injury']))/100);
            }

            $score *= sqrt($allGen/$generals[$testNation['nation']]);
            $randVals[$idx] = $score;
        }

        if($randVals){
            $nation = $nations[Util::choiceRandomUsingWeight($randVals)];
        }

    } else {
        $nation = $db->queryFirstRow('SELECT `name`,nation,scout,`level` FROM nation WHERE nation=%i', $where);
    }

    if($nation){
        $gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE nation=%i', $nation['nation']);
        $josaUn = JosaUtil::pick($nation['name'], '은');
    }
    
    if(!$nation) {
        $log[] = "<C>●</>{$admin['month']}월:임관할 국가가 없습니다. 임관 실패. <1>$date</>";
    } elseif($general['nation'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야가 아닙니다. 임관 실패. <1>$date</>";
    } elseif($nation['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:없는 국가입니다. 임관 실패. <1>$date</>";
    } elseif($nation['level'] == 0 && $gencount >= GameConst::$initialNationGenLimit) {
        $log[] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>{$josaUn} 임관이 제한되고 있습니다. 임관 실패.";
    } elseif($admin['year'] < $admin['startyear']+3 && $gencount >= GameConst::$initialNationGenLimit) {
        $log[] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>{$josaUn} 임관이 제한되고 있습니다. 임관 실패.";
    } elseif($nation['scout'] == 1 && $general['npc'] != 9) {
        $log[] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>{$josaUn} 임관이 금지되어 있습니다. 임관 실패.";
    } elseif($general['makelimit'] > 0 && $general['npc'] != 9) {
        $log[] = "<C>●</>{$admin['month']}월:재야가 된지 12턴이 지나야 합니다. 임관 실패. <1>$date</>";
    } elseif(in_array($nation['nation'], $joinedNations)) {
        $log[] = "<C>●</>{$admin['month']}월:이미 임관했었던 국가입니다. 임관 실패. <1>$date</>";
    } else {
        $josaYi = JosaUtil::pick($general['name'], '이');
        if($where == 99 || $where == 98) {
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 어쩌다보니 <D><b>{$nation['name']}</b></>에 <S>임관</>했습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<D>{$nation['name']}</>에 랜덤으로 임관했습니다. <1>$date</>";
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>에 임관");
        } else {
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>에 <S>임관</>했습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<D>{$nation['name']}</>에 임관했습니다. <1>$date</>";
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>에 임관");
        }

        if($gencount < GameConst::$initialNationGenLimit) { $exp = 700; }
        else { $exp = 100; }
        $ded = 0;
        
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
            $joinedNations[] = $nation['nation'];
        }

        // 국적 바꾸고 등급 일반으로        // 명성 상승
        $db->update('general', [
            'resturn'=>'SUCCESS',
            'nation'=>$nation['nation'],
            'nations'=>Json::encode($joinedNations),
            'level'=>1,
            'experience'=>$db->sqleval('experience + %i', $exp),
            'city'=>$king['city'],
            'belong'=>1
        ], 'no=%i', $general['no']);

        $db->update('nation', [
            'gennum'=>$gencount + 1,
        ], 'nation=%i', $nation['nation']);

        if($where < 99) {
            $log = uniqueItem($general, $log);
        } else {
            $log = uniqueItem($general, $log, 2);
        }
    }

    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_29(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear','year','month','develcost','npccount','turnterm','scenario']);

    $query = "select nation,name,level,gennum,scout from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $josaUn = JosaUtil::pick($nation['name'], '은');
    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 인재탐색 실패. <1>$date</>";
    } elseif($nation['level'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:방랑군입니다. 인재탐색 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3 && $nation['gennum'] >= GameConst::$initialNationGenLimit) {
        $log[] = "<C>●</>{$admin['month']}월:현재 <D>{$nation['name']}</>{$josaUn} 탐색이 제한되고 있습니다. 인재탐색 실패.";
    } elseif($general['gold'] < $admin['develcost']) {
        $log[] = "<C>●</>{$admin['month']}월:자금이 모자랍니다. 인재탐색 실패. <1>$date</>";
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
        $otherNpccount = Util::round(sqrt($otherNpccount + 1)) - 1;
        
        if($gencount <= 0) { $gencount = 1; }
        if($npccount <= 0) { $npccount = 1; }
        $criteria = $gencount * $npccount + $otherNpccount;

        // 탐색 실패
        if(rand() % $criteria > 0) {
            $exp = 100;
            $ded = 70;
            switch(Util::choiceRandomUsingWeight([$general['leader'], $general['power'], $general['intel']])) {
            case 0: $general['leader2'] += 1; break;
            case 1: $general['power2'] += 1; break;
            case 2: $general['intel2'] += 1; break;
            }
            $log[] = "<C>●</>{$admin['month']}월:인재를 찾을 수 없었습니다. <1>$date</>";
        } else {
            // 탐색 성공
            $exp = 200;
            $ded = 300;
            switch(Util::choiceRandomUsingWeight([$general['leader'], $general['power'], $general['intel']])) {
            case 0: $general['leader2'] += 3; break;
            case 1: $general['power2'] += 3; break;
            case 2: $general['intel2'] += 3; break;
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
            
            $josaYi = JosaUtil::pick($general['name'], '이');
            $josaRa = JosaUtil::pick($name, '라');
            $log[] = "<C>●</>{$admin['month']}월:<Y>$name</>{$josaRa}는 <C>인재</>를 {$scoutType}하였습니다! <1>$date</>";
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <Y>$name</>{$josaRa}는 <C>인재</>를 {$scoutType}하였습니다!";
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>$name</>{$josaRa}는 <C>인재</>를 {$scoutType}");

            $query = "select max(leader+power+intel) as lpi, avg(dedication) as ded,avg(experience) as exp, avg(dex0) as dex0, avg(dex10) as dex10, avg(dex20) as dex20, avg(dex30) as dex30, avg(dex40) as dex40 from general where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $avgGen = MYDB_fetch_array($result);

            // 체섭시 무장 20%, 지장 20%, 무지장 60%
            // 마이너 무장 40%, 지장 40%, 무지장 20%
            $type = rand() % 10;
            if($admin['scenario'] < 100) {
                switch($type) {
                case 0: case 1:
                    $leader = GameConst::$defaultStatMax - 10 + rand()%11;
                    $intel = GameConst::$defaultStatMin + rand()%6;
                    $power = GameConst::$defaultStatTotal - $leader - $intel;
                    break;
                case 2: case 3:
                    $leader = GameConst::$defaultStatMax - 10 + rand()%11;
                    $power = GameConst::$defaultStatMin + rand()%6;
                    $intel = GameConst::$defaultStatTotal - $leader - $power;
                    break;
                case 4: case 5: case 6: case 7: case 8: case 9:
                    $leader = GameConst::$defaultStatMin + rand()%6;
                    $power = GameConst::$defaultStatMax - 10 + rand()%11;
                    $intel = GameConst::$defaultStatTotal - $leader - $power;
                    break;
                }
            } else {
                switch($type) {
                case 0: case 1: case 2: case 3:
                    $leader = GameConst::$defaultStatMax - 10 + rand()%11;
                    $intel = GameConst::$defaultStatMin + rand()%6;
                    $power = GameConst::$defaultStatTotal - $leader - $intel;
                    break;
                case 4: case 5: case 6: case 7:
                    $leader = GameConst::$defaultStatMax - 10 + rand()%11;
                    $power = GameConst::$defaultStatMin + rand()%6;
                    $intel = GameConst::$defaultStatTotal - $leader - $power;
                    break;
                case 8: case 9:
                    $leader = GameConst::$defaultStatMin + rand()%6;
                    $power = GameConst::$defaultStatMax - 10 + rand()%11;
                    $intel = GameConst::$defaultStatTotal - $leader - $power;
                    break;
                }
            }
            // 국내 최고능치 기준으로 랜덤성 스케일링
            if($avgGen['lpi'] > 210) {
                $leader = Util::round($leader * $avgGen['lpi'] / GameConst::$defaultStatTotal * (60+rand()%31)/100);
                $power = Util::round($power * $avgGen['lpi'] / GameConst::$defaultStatTotal * (60+rand()%31)/100);
                $intel = Util::round($intel * $avgGen['lpi'] / GameConst::$defaultStatTotal * (60+rand()%31)/100);
            } elseif($avgGen['lpi'] > 180) {
                $leader = Util::round($leader * $avgGen['lpi'] / GameConst::$defaultStatTotal * (75+rand()%21)/100);
                $power = Util::round($power * $avgGen['lpi'] / GameConst::$defaultStatTotal * (75+rand()%21)/100);
                $intel = Util::round($intel * $avgGen['lpi'] / GameConst::$defaultStatTotal * (75+rand()%21)/100);
            } else {
                $leader = Util::round($leader * $avgGen['lpi'] / GameConst::$defaultStatTotal * (90+rand()%11)/100);
                $power = Util::round($power * $avgGen['lpi'] / GameConst::$defaultStatTotal * (90+rand()%11)/100);
                $intel = Util::round($intel * $avgGen['lpi'] / GameConst::$defaultStatTotal * (90+rand()%11)/100);
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
            $affinity = rand() % 150 + 1;
            $picture = 'default.jpg';
            $turntime = getRandTurn($admin['turnterm']);
            $personal = rand() % 10;
            $bornyear = $admin['year'];
            $deadyear = $admin['year'] + 3;
            $age = 20;
            $specage = Util::round((80 - $age)/12) + $age;
            $specage2 = Util::round((80 - $age)/3) + $age;
            //$specage = $age + 1 + rand() % 3;
            //$specage2 = $age + 5 + rand() % 5;
            // 10년 ~ 50년
            $killturn = rand()%480 + 120;

            $db->insert('general', [
                'npcid'=>$npccount,
                'npc'=>$npc,
                'npc_org'=>$npc,
                'affinity'=>$affinity,
                'name'=>$name,
                'picture'=>$picture,
                'nation'=>$scoutNation,
                'city'=>$general['city'],
                'leader'=>$leader,
                'power'=>$power,
                'intel'=>$intel,
                'experience'=>$experience,
                'dedication'=>$dedication,
                'level'=>$scoutLevel,
                'gold'=>100,
                'rice'=>100,
                'crew'=>0,
                'crewtype'=>GameUnitConst::DEFAULT_CREWTYPE,
                'train'=>0,
                'atmos'=>0,
                'tnmt'=>0,
                'weap'=>0,
                'book'=>0,
                'horse'=>0,
                'turntime'=>$turntime,
                'killturn'=>$killturn,
                'age'=>$age,
                'belong'=>1,
                'personal'=>$personal,
                'special'=>0,
                'specage'=>$specage,
                'special2'=>0,
                'specage2'=>$specage2,
                'npcmsg'=>'',
                'makelimit'=>0,
                'bornyear'=>$bornyear,
                'deadyear'=>$deadyear,
                'dex0'=>$avgGen['dex0'],
                'dex10'=>$avgGen['dex10'],
                'dex20'=>$avgGen['dex20'],
                'dex30'=>$avgGen['dex30'],
                'dex40'=>$avgGen['dex40'],
            ]);

            $npcid++;

            //npccount
            $gameStor->npccount=$npcid;

            $db->update('nation', [
                'gennum'=>$db->sqleval('gennum + 1'),
            ], 'nation=%i', $general['nation']);

        }

        //기술로 가격
        $gold = $general['gold'] - $admin['develcost'];

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 자금 감소        // 경험치 상승        // 공헌도, 명성 상승
        $query = "update general set resturn='SUCCESS',term='0',gold='$gold',leader2='{$general['leader2']}',power2='{$general['power2']}',intel2='{$general['intel2']}',dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = checkAbility($general, $log);
        $log = uniqueItem($general, $log);
    }

    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_45(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    $query = "select name,chemi from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야입니다. 하야 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중 하야는 불가능합니다. 하야 실패. <1>$date</>";
    } elseif($general['level'] == 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주입니다. 하야 실패. <1>$date</>";
    } else {

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
            $josaYi = JosaUtil::pick($general['name'], '이');
            $josaUl = JosaUtil::pick($nation['name'], '을');
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 통합에 반대하며 <D><b>{$nation['name']}</b></>{$josaUl} <R>떠났</>습니다.";
            $log[] = "<C>●</>{$admin['month']}월:통합에 반대하며 <D><b>{$nation['name']}</b></>에서 떠났습니다. <1>$date</>";
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:통합에 반대하며 <D><b>{$nation['name']}</b></>{$josaUl} 떠남");

            // 국적 바꾸고 등급 재야로
            $query = "update general set resturn='SUCCESS',belong=0,nation=0,level=0,makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($dipcount2 > 0) {
            $josaYi = JosaUtil::pick($general['name'], '이');
            $josaUl = JosaUtil::pick($nation['name'], '을');
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 합병에 반대하며 <D><b>{$nation['name']}</b></>{$josaUl} <R>떠났</>습니다.";
            $log[] = "<C>●</>{$admin['month']}월:합병에 반대하며 <D><b>{$nation['name']}</b></>에서 떠났습니다. <1>$date</>";
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:합병에 반대하며 <D><b>{$nation['name']}</b></>{$josaUl} 떠남");

            // 국적 바꾸고 등급 재야로
            $query = "update general set resturn='SUCCESS',belong=0,nation=0,level=0,makelimit='12',gold='{$general['gold']}',rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $josaYi = JosaUtil::pick($general['name'], '이');
            $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>에서 <R>하야</>했습니다.";
            $log[] = "<C>●</>{$admin['month']}월:<D><b>{$nation['name']}</b></>에서 하야했습니다. <1>$date</>";
            pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>에서 하야");

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

        $nation['chemi'] -= 1;
        if($nation['chemi'] < 0) { $nation['chemi'] = 0; }

        $db->update('nation', [
            'gennum'=>$db->sqleval('gennum - 1'),
            'chemi'=>$nation['chemi'],
            'gold'=>$db->sqleval('gold + %i', $gold),
            'rice'=>$db->sqleval('rice + %i', $rice),
        ], 'nation=%i', $general['nation']);
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_46(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    $query = "select * from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    $query = "select nation from nation where nation!={$general['nation']} AND name='{$general['makenation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);

    $command = DecodeCommand($general['turn0']);
    $color = $command[1];
    $type = $command[2];    // 1 ~ 13

    $colors = GetNationColors();
    if($color >= count($colors)) { $color = 0; }
    $color = $colors[$color];

    if($type < 1) { $type = 9; }
    elseif($type > 13) { $type = 9; }

    if($gencount < 2) {
        $log[] = "<C>●</>{$admin['month']}월:수하 장수가 부족합니다. 건국 실패. <1>$date</>";
    } elseif($admin['year'] >= $admin['startyear']+2) {
        $log[] = "<C>●</>{$admin['month']}월:건국 기간이 지났습니다. 건국 실패. <1>$date</>";
    } elseif($city['nation'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지가 아닙니다. 건국 실패. <1>$date</>";
    } elseif($nationcount > 0) {
        $log[] = "<C>●</>{$admin['month']}월:존재하는 국가명입니다. 건국 실패. <1>$date</>";
    } elseif($general['makelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야가 된지 12시간이 지나야 합니다. 건국 실패. <1>$date</>";
    } elseif($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. 건국 실패. <1>$date</>";
    } elseif($city['level'] != 5 && $city['level'] != 6) {
        $log[] = "<C>●</>{$admin['month']}월:중, 소 도시에서만 가능합니다. 건국 실패. <1>$date</>";
    } else {
        $db->update('nation', [
            'name'=>$general['makenation'],
            'color'=>$color,
            'level'=>1,
            'type'=>$type,
            'capital'=>$general['city'],
        ], 'nation=%i', $general['nation']);

        refreshNationStaticInfo();
        $nation = getNationStaticInfo($general['nation']);

        // 현 도시 소속지로
        $query = "update city set nation='{$nation['nation']}',conflict='{}' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaUl = JosaUtil::pick($nation['name'], '을');
        $log[] = "<C>●</>{$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaUl} 건국하였습니다. <1>$date</>";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$city['name']}</b></>에 국가를 건설하였습니다.";
        $josaYi = JosaUtil::pick($nation['name'], '이');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【건국】</b></>".getNationType($type)." <D><b>{$nation['name']}</b></>{$josaYi} 새로이 등장하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaUl} 건국");
        $josaYi = JosaUtil::pick($general['name'], '이');
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>{$josaUl} 건국");

        $exp = 1000;
        $ded = 1000;

        

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 명성 상승
        $query = "update general set resturn='SUCCESS',dedication=dedication+'$ded', experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log = uniqueItem($general, $log, 3);
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_47(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    //현재 외교 진행중(평시, 불가침만 제외)일때
    $query = "select state from diplomacy where me='{$general['nation']}' and state!='2' and state!='7'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);


    if($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중에는 방랑이 불가능합니다. 방랑 실패.";
    } elseif($dipcount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:방랑할 수 없는 외교상태입니다. 방랑 실패.";
    } elseif($nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:이미 방랑군입니다. 방랑 실패.";
    } else {
        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:영토를 버리고 방랑의 길을 떠납니다. <1>$date</>";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 방랑의 길을 떠납니다.";
        $josaUl = JosaUtil::pick($nation['name'], '을');
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaUl} 버리고 방랑");

        $josaUn = JosaUtil::pick($general['name'], '은');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【방랑】</b></><D><b>{$general['name']}</b></>{$josaUn} <R>방랑</>의 길을 떠납니다.";

        //분쟁기록 모두 지움
        DeleteConflict($general['nation']);
        // 국명, 색깔 바꿈 국가 레벨 0, 성향리셋, 기술0
        $db->update('nation', [
            'name'=>$general['name'],
            'color'=>'#330000', //TODO: 기본 방랑군색 별도 지정
            'level'=>0,
            'type'=>0,
            'tech'=>0,
            'capital'=>0
        ], 'nation=%i', $general['nation']);
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
        $query = "update city set nation='0',front='0',gen1='0',gen2='0',gen3='0',conflict='{}' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 리셋
        $query = "update diplomacy set state='2',term='0' where me='{$general['nation']}' or you='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushWorldHistory($history, $admin['year'], $admin['month']);

        refreshNationStaticInfo();
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_54(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $youlog = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $command = DecodeCommand($general['turn0']);
    $who = $command[1];

    $query = "select no,name,nation from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nextruler = MYDB_fetch_array($result);

    $query = "select nation,name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    //현재 외교 진행중(통합제의중)일때
    $query = "select state from diplomacy where me='{$general['nation']}' and state='4'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($nextruler['name'] == "") {
        $log[] = "<C>●</>{$admin['month']}월:잘못된 장수입니다. 선양 실패. <1>$date</>";
    } elseif($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. 선양 실패. <1>$date</>";
    } elseif($nextruler['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:잘못된 장수입니다. 선양 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:현재 통합 진행중입니다. 선양 실패.";
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

        $josaYi = JosaUtil::pick($general['name'], '이');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【선양】</b></><Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>의 군주 자리를 <Y>{$nextruler['name']}</>에게 선양했습니다.";
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$nextruler['name']}</>에게 군주의 자리를 물려줍니다. <1>$date</>";
        $youlog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게서 군주의 자리를 물려받습니다.";

        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주자리를 <Y>{$nextruler['name']}</>에게 선양");
        pushGeneralHistory($nextruler, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주자리를 물려 받음");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <Y>{$nextruler['name']}</>에게 선양");
    }
    pushGenLog($general, $log);
    pushGenLog($nextruler, $youlog);
    pushWorldHistory($history, $admin['year'], $admin['month']);
}

function process_55(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    $query = "select name from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation from nation where name='{$general['name']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);

    if($nationcount > 0) { $makename = mb_substr("$nationcount".$general['name'], 0, 6); }
    else { $makename = $general['name']; }

    if($general['level'] != 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야가 아닙니다. 거병 실패. <1>$date</>";
    } elseif($admin['year'] >= $admin['startyear']+2) {
        $log[] = "<C>●</>{$admin['month']}월:거병 기간이 지났습니다. 거병 실패. <1>$date</>";
    } elseif($general['makelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야가 된지 12시간이 지나야 합니다. 거병 실패. <1>$date</>";
    } else {

        DB::db()->insert('nation', [
            'name'=>$makename,
            'color'=>'#330000', 
            'gold'=>0, 
            'rice'=>GameConst::$baserice, 
            'rate'=>20, 
            'bill'=>100, 
            'sabotagelimit'=>36, 
            'surlimit'=>72, 
            'type'=>0, 
            'gennum'=>1
        ]);
        $nationID = DB::db()->insertId();

        refreshNationStaticInfo();
        $nation = getNationStaticInfo($nationID);

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

        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:거병에 성공하였습니다. <1>$date</>";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$city['name']}</b></>에서 거병하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【거병】</b></><D><b>{$general['name']}</b></>{$josaYi} 세력을 결성하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$city['name']}</b></>에서 거병");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$city['name']}</b></>에서 거병");

        // 외교테이블 추가
        

        foreach(getAllNationStaticInfo() as $younation){
            if($nation['nation'] == $younation['nation']){
                continue;
            }

            //FIXME: 쿼리 개선. foreach문은 굳이 필요없을것
            $query = "insert into diplomacy (me, you, state, term) values ('{$nation['nation']}', '{$younation['nation']}', '2', '0')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "insert into diplomacy (me, you, state, term) values ('{$younation['nation']}', '{$nation['nation']}', '2', '0')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_56(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $nation = getNationStaticInfo($general['nation']);

    $query = "select city from city where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    if($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. <1>$date</>";
    } elseif($citycount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:방랑군이 아닙니다. <1>$date</>";
    } else {
        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:세력을 해산했습니다. <1>$date</>";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 세력을 해산했습니다.";
        $josaUl = JosaUtil::pick($nation['name'], '을');
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaUl} 해산");

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
        DeleteConflict($general['nation']);
        deleteNation($general);

        refreshNationStaticInfo();
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}


function process_57(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $youlog = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month', 'killturn']);

    $query = "select nation,name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select npc,no,name,killturn from general where nation='{$general['nation']}' and level='12'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $ruler = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야 입니다. 모반 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부 이상만 가능합니다. 모반 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 모반 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 모반 실패. <1>$date</>";
    } elseif($general['level'] == 12) {
        $log[] = "<C>●</>{$admin['month']}월:이미 군주 입니다. 모반 실패. <1>$date</>";
    } elseif($ruler['killturn'] >= $admin['killturn']) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 활동중입니다. 모반 실패. <1>$date</>";
    } elseif($ruler['npc'] >= 2) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 NPC입니다. 모반 실패. <1>$date</>";
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

        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:모반에 성공했습니다. <1>$date</>";
        $youlog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게 군주의 자리를 뺏겼습니다.";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <M>모반</>에 성공했습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【모반】</b></><Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>의 군주 자리를 찬탈했습니다.";

        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:모반으로 <D><b>{$nation['name']}</b></>의 군주자리를 찬탈");
        pushGeneralHistory($ruler, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$general['name']}</b></>의 모반으로 인해 <D><b>{$nation['name']}</b></>의 군주자리를 박탈당함");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <Y>{$ruler['name']}</>에게서 군주자리를 찬탈");
    }
    pushGenLog($general, $log);
    pushGenLog($ruler, $youlog);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushWorldHistory($history, $admin['year'], $admin['month']);
}
