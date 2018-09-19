<?php
namespace sammo;


function processWar(array $rawAttacker, array $rawDefenderCity){

    $db = DB::db();
    $rawAttackerCity = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $rawAttacker['city']);

    $attackerNationID = $rawAttacker['nation'];
    $defenderNationID = $rawDefenderCity['nation'];

    $rawAttackerNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,totaltech,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $attackerNationID);

    if($defenderNationID == 0){
        $rawDefenderNation =  [
            'nation'=>0,
            'name'=>'재야',
            'capital'=>0,
            'level'=>0,
            'gold'=>0,
            'rice'=>2000,
            'type'=>0,
            'tech'=>0,
            'totaltech'=>0,
            'gennum'=>1     
        ];
    }
    else{
        $rawDefenderNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,totaltech,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $defenderNationID);
    }

    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$startYear, $year, $month, $cityRate] = $gameStor->getValuesAsArray(['startyear', 'year', 'month', 'city_rate']);

    $attacker = new WarUnitGeneral($rawAttacker, $rawAttackerCity, $rawAttackerNation, true, $year, $month);

    $city = new WarUnitCity($rawDefenderCity, $rawDefenderNation, $year, $month, $cityRate);

    $defenderList = $db->query('SELECT no,name,nation,turntime,personal,special2,crew,crewtype,atmos,train,intel,intel2,book,power,power2,weap,injury,leader,leader2,horse,item,explevel,experience,dedication,level,gold,rice,dex0,dex10,dex20,dex30,dex40,warnum,killnum,deathnum,killcrew,deathcrew,recwar,mode FROM general WHERE nation=%i AND city=%i AND nation!=0 and crew > 0 and rice>(crew/100) and ((train>=60 and atmos>=60 and mode=1) or (train>=80 and atmos>=80 and mode=2))', $city->getVar('nation'), $city->getVar('city'));

    if(!$defenderList){
        $defenderList = [];
    }

    usort($defenderList, function($lhs, $rhs){
        return -(extractBattleOrder($lhs) <=> extractBattleOrder($rhs));
    });

    $iterDefender = new \ArrayIterator($defenderList);
    $iterDefender->rewind();

    $getNextDefender = function(?WarUnit $prevDefender, bool $reqNext) use ($iterDefender, $rawDefenderCity, $rawDefenderNation, $year, $month, $db) {
        if($prevDefender !== null){
            $prevDefender->applyDB($db);
        }

        if(!$reqNext){
            return null;
        }

        if(!$iterDefender->valid()){
            return null;
        }

        $rawGeneral = $iterDefender->current();
        if(extractBattleOrder($rawGeneral) <= 0){
            return null;
        }

        $retVal = new WarUnitGeneral($rawGeneral, $rawDefenderCity, $rawDefenderNation, false, $year, $month);
        $iterDefender->next();
        return $retVal;
    };

    $conquerCity = processWar_NG($attacker, $getNextDefender, $city, $year - $startYear);

    $attacker->applyDB($db);

    //NOTE: $rawAttackerNation과 $rawDefenderNation은 전투중에 '바뀌지 않음'을 전제로 하고 있다. (현재 구현도 그렇게 되어있음)

    $rawDefenderCity = $city->getRaw();
    $updateAttackerNation = [];
    $updateDefenderNation = [];

    if($city->getVar('supply')){
        if($city->getPhase() > 0){
            $rice = $city->getKilled() / 100 * 0.8;
            $rice *= $city->getCrewType()->rice;
            $rice *= getTechCost($rawDefenderNation['tech']);
            $rice *= $cityRate / 100 - 0.2;
            Util::setRound($rice);
    
            $updateDefenderNation['rice'] = max(0, $rawDefenderNation['rice'] - $rice);
        }
        else if($conquerCity){
            $updateDefenderNation['rice'] = $rawDefenderNation['rice'] + 500;
        }
    }

    $totalDead = $attacker->getKilled() + $attacker->getDead();

    $db->update('city', [
        'dead' => $db->sqleval('dead + %i', $totalDead * 0.4)
    ], 'city=%i', $rawAttackerCity['city']);

    $db->update('city', [
        'dead' => $db->sqleval('dead + %i', $totalDead * 0.6)
    ], 'city=%i', $rawDefenderCity['city']);

    $attackerIncTech = $attacker->getDead() * 0.01 * getNationTechMultiplier($rawAttackerNation['type']);
    $defenderIncTech = $attacker->getKilled() * 0.01 * getNationTechMultiplier($rawDefenderNation['type']);

    if(TechLimit($startYear, $year, $rawAttackerNation['tech'])){
        $attackerIncTech /= 4;
    }
    if(TechLimit($startYear, $year, $rawDefenderNation['tech'])){
        $defenderIncTech /= 4;
    }

    $attackerTotalTech = $rawAttackerNation['totaltech'] + $attackerIncTech;
    $defenderTotalTech = $rawDefenderNation['totaltech'] + $defenderIncTech;

    $updateAttackerNation['totaltech'] = Util::round($attackerTotalTech);
    $updateDefenderNation['totaltech'] = Util::round($defenderTotalTech);

    $updateAttackerNation['tech'] = Util::round($attackerTotalTech / max(GameConst::$initialNationGenLimit, $rawAttackerNation['gennum']));
    $updateDefenderNation['tech'] = Util::round($defenderTotalTech / max(GameConst::$initialNationGenLimit, $rawDefenderNation['gennum']));

    $db->update('nation', $updateAttackerNation, 'nation=%i', $attackerNationID);
    $db->update('nation', $updateDefenderNation, 'nation=%i', $defenderNationID);

    $db->update('diplomacy', [
        'dead'=>$db->sqleval('dead + %i', $attacker->getDead() * getTechCost($rawAttackerNation['tech']))
    ], 'me = %i and you = %i', $attackerNationID, $defenderNationID);

    $db->update('diplomacy', [
        'dead'=>$db->sqleval('dead + %i', $attacker->getKilled() * getTechCost($rawDefenderNation['tech']))
    ], 'me = %i and you = %i', $defenderNationID, $attackerNationID);

    if(!$conquerCity){
        return;
    }

    //XXX: 새 도시점령 코드 작성하기 전까지 유지
    $rawAttackerCity = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $rawAttacker['city']);
    $rawAttackerNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,totaltech,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $attackerNationID);

    if($defenderNationID !== 0){
        $rawDefenderNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,totaltech,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $defenderNationID);
    }
    
    ConquerCity([
        'startyear'=>$startYear,
        'year'=>$year,
        'month'=>$month,
        'city_rate'=>$cityRate
    ], $attacker->getRaw(), $city->getRaw(), $rawAttackerNation, $rawDefenderNation);
}

function extractBattleOrder($general){
    if($general['crew'] == 0){
        return 0;
    }

    if($general['rice'] <= $general['crew'] / 100){
        return 0;
    }

    if($general['mode'] == 0){
        return 0;
    }

    if($general['mode'] == 1 && ($general['train'] < 60 || $general['atmos'] < 60)){
        return 0;
    }

    if($general['mode'] == 2 && ($general['train'] < 80 || $general['atmos'] < 80)){
        return 0;
    }

    return (
        $general['leader'] +
        $general['power'] +
        $general['intel'] +
        $general['weap'] +
        $general['horse'] +
        $general['book'] +
        $general['crew'] / 100
    );
}

function processWar_NG(
    WarUnitGeneral $attacker,
    callable $getNextDefender, 
    WarUnitCity $city,
    int $relYear
):bool{
    $templates = new \League\Plates\Engine(__dir__.'/templates');

    $logger = $attacker->getLogger();

    $attacker->useBattleInitItem();
    
    $date = substr($attacker->getVar('turntime'),11,5);

    $attackerNationUpdate = [];
    $defenderNationUpdate = [];

    $defender = ($getNextDefender)(null, true);
    $conquerCity = false;
    
    $josaRo = JosaUtil::pick($city->getName(), '로');
    $josaYi = JosaUtil::pick($attacker->getName(), '이');

    $logger->pushGlobalActionLog("<D><b>{$attacker->getNationVar('name')}</b></>의 <Y>{$attacker->getName()}</>{$josaYi} <G><b>{$city->getName()}</b></>{$josaRo} 진격합니다.");
    $logger->pushGeneralActionLog("<G><b>{$city->getName()}</b></>{$josaRo} <M>진격</>합니다. <1>$date</>");

    for($currPhase = 0; $currPhase < $attacker->getMaxPhase(); $currPhase+=1){
        if($defender === null){
            $defender = $city;
            
            if($city->getNationVar('rice') <= 0 && $city->getVar('supply')){
                //병량 패퇴
                $attacker->setOppose($defender);
                $defender->setOppose($attacker);

                $attacker->addTrain(1);

                $attacker->addWin();
                $defender->addLose();
                $defender->heavyDecreseWealth();

                $logger->pushGlobalActionLog("병량 부족으로 <G><b>{$defender->getName()}</b></>의 수비병들이 <R>패퇴</>합니다.");
                $josaUl = JosaUtil::pick($defender->getName(), '을');
                $josaYi = JosaUtil::pick($defender->getNationVar('name'), '이');
                $logger->pushGlobalHistoryLog("<M><b>【패퇴】</b></><D><b>{$defender->getNationVar('name')}</b></>{$josaYi} 병량 부족으로 <G><b>{$defender->getName()}</b></>{$josaUl} 뺏기고 말았습니다.");

                $conquerCity = true;
                break;
            }
        }

        if($defender->getPhase() == 0){
            $defender->setPrePhase($currPhase);

            $attacker->addTrain(1);
            $defender->addTrain(1);

            if($defender instanceof WarUnitGeneral){
                $josaWa = JosaUtil::pick($attacker->getCrewTypeName(), '와');
                $josaYi = JosaUtil::pick($defender->getCrewTypeName(), '이');
                $logger->pushGlobalActionLog("<Y>{$attacker->getName()}</>의 {$attacker->getCrewTypeName()}{$josaWa} <Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 대결합니다.");
                $josaRo = JosaUtil::pick($attacker->getCrewTypeName(), '로');
                $josaUl = JosaUtil::pick($defender->getCrewTypeName(), '을');
                $attacker->getLogger()->pushGeneralActionLog("{$attacker->getCrewTypeName()}{$josaRo} <Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaUl} <M>공격</>합니다.");
                $josaRo = JosaUtil::pick($defender->getCrewTypeName(), '로');
                $josaUl = JosaUtil::pick($attacker->getCrewTypeName(), '을');
                $defender->getLogger()->pushGeneralActionLog("{$defender->getCrewTypeName()}{$josaRo} <Y>{$attacker->getName()}</>의 {$attacker->getCrewTypeName()}{$josaUl} <M>수비</>합니다.");
            }
            else{
                $josaYi = JosaUtil::pick($attacker->getName(), '이');
                $josaRo = JosaUtil::pick($attacker->getCrewTypeName(), '로');
                $logger->pushGlobalActionLog("<Y>{$attacker->getName()}</>{$josaYi} {$attacker->getCrewTypeName()}{$josaRo} 성벽을 공격합니다.");
                $logger->pushGeneralActionLog("{$attacker->getCrewTypeName()}{$josaRo} 성벽을 <M>공격</>합니다.", ActionLogger::PLAIN);
            }

            $defender->useBattleInitItem();

            $attacker->setOppose($defender);
            $defender->setOppose($attacker);

            foreach(Util::zip(
                $attacker->checkBattleBeginSkill(),
                $defender->checkBattleBeginSkill()
                ) as $b){
                //doNothing
            }

            $attacker->checkBattleBeginItem();
            $defender->checkBattleBeginItem();

            $attacker->applyBattleBeginSkillAndItem();
            $defender->applyBattleBeginSkillAndItem();
        }

        $attacker->beginPhase();
        $defender->beginPhase();

        foreach(Util::zip(
            $attacker->checkPreActiveSkill(),
            $defender->checkPreActiveSkill()
            ) as $b){
            //doNothing
        }

        foreach(Util::zip(
            $attacker->checkActiveSkill(),
            $defender->checkActiveSkill()
            ) as $b){
            //doNothing
        }
        //NOTE: 마법은 checkActiveSkill, checkPostActiveSkill 내에서 반영

        foreach(Util::zip(
            $attacker->checkPostActiveSkill(),
            $defender->checkPostActiveSkill()
            ) as $b){
            //doNothing
        }
        //NOTE: 반계류 등의 스킬을 post에서 반영

        foreach(Util::zip(
            $attacker->applyActiveSkill(),
            $defender->applyActiveSkill()
            ) as $b){
            //doNothing
        }

        $deadDefender = $attacker->calcDamage();
        $deadAttacker = $defender->calcDamage();

        $attackerHP = $attacker->getHP();
        $defenderHP = $defender->getHP();

        if($deadAttacker > $attackerHP || $deadDefender > $defenderHP){
            $deadAttackerRatio = $deadAttacker / max(1, $attackerHP);
            $deadDefenderRatio = $deadDefender / max(1, $defenderHP);

            if($deadDefenderRatio > $deadAttackerRatio){
                //수비자가 더 병력 부족
                $deadAttacker /= $deadDefenderRatio;
                $deadDefender = $defenderHP;
            }
            else{
                //공격자가 더 병력 부족
                $deadDefender /= $deadAttackerRatio;
                $deadAttacker = $attackerHP;
            }
        }

        $deadAttacker = min(ceil($deadAttacker), $attackerHP);
        $deadDefender = min(ceil($deadDefender), $defenderHP);        

        $attacker->decreaseHP($deadAttacker);
        $defender->decreaseHP($deadDefender);

        $attacker->increaseKilled($deadDefender);
        $defender->increaseKilled($deadAttacker);

        $phaseNickname = $currPhase + 1;

        if($deadAttacker > 0 || $deadDefender > 0){
            $attacker->getLogger()->pushGeneralBattleDetailLog(
                "$phaseNickname : <Y1>【{$attacker->getName()}】</> <C>{$attacker->getHP()} (-$deadAttacker)</> VS <C>{$defender->getHP()} (-$deadDefender)</> <Y1>【{$defender->getName()}】</>"
            );
    
            $defender->getLogger()->pushGeneralBattleDetailLog(
                "$phaseNickname : <Y1>【{$defender->getName()}】</> <C>{$defender->getHP()} (-$deadDefender)</> VS <C>{$attacker->getHP()} (-$deadAttacker)</> <Y1>【{$attacker->getName()}】</>"
            );
        }
        

        $attacker->addPhase();
        $defender->addPhase();

        if(!$attacker->continueWar($noRice)){
            $attacker->logBattleResult();
            $defender->logBattleResult();

            $attacker->addLose();
            $defender->addWin();
            
            $attacker->tryWound();
            $defender->tryWound();

            $josaYi = JosaUtil::pick($attacker->getCrewTypeName(), '이');
            $logger->pushGlobalActionLog("<Y>{$attacker->getName()}</>의 {$attacker->getCrewTypeName()}{$josaYi} 퇴각했습니다.");
            if($noRice){
                $attacker->getLogger()->pushGeneralActionLog("군량 부족으로 퇴각합니다.", ActionLogger::PLAIN);
            }
            else{
                $attacker->getLogger()->pushGeneralActionLog("퇴각했습니다.", ActionLogger::PLAIN);
            }
            $defender->getLogger()->pushGeneralActionLog("<Y>{$attacker->getName()}</>의 {$attacker->getCrewTypeName()}{$josaYi} 퇴각했습니다.", ActionLogger::PLAIN);

            break;
        }

        if(!$defender->continueWar($noRice)){

            $attacker->logBattleResult();
            $defender->logBattleResult();

            $attacker->addWin();
            $defender->addLose();

            $attacker->tryWound();
            $defender->tryWound();

            if($defender === $city){
                $attacker->addLevelExp(1000);
                $conquerCity = true;
                break;
            }

            $josaYi = JosaUtil::pick($defender->getCrewTypeName(), '이');
            
            if($noRice){
                $logger->pushGlobalActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 패퇴했습니다.");
            $attacker->getLogger()->pushGeneralActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 패퇴했습니다.", ActionLogger::PLAIN);
                $defender->getLogger()->pushGeneralActionLog("군량 부족으로 패퇴합니다.", ActionLogger::PLAIN);
            }
            else{
                $logger->pushGlobalActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 전멸했습니다.");
            $attacker->getLogger()->pushGeneralActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 전멸했습니다.", ActionLogger::PLAIN);
                $defender->getLogger()->pushGeneralActionLog("전멸했습니다.", ActionLogger::PLAIN);
            }

            if($currPhase + 1 == $attacker->getMaxPhase()){
                break;
            }

            $defender->finishBattle();
            $defender = ($getNextDefender)($defender, true);

            if($defender !== null && !($defender instanceof WarUnitGeneral)){
                throw new \RuntimeException('다음 수비자를 받아오는데 실패');
            }
            
        }
        
    }

    $attacker->finishBattle();
    $defender->finishBattle();

    if($currPhase == $attacker->getMaxPhase()){
        //마지막 페이즈의 전투 마무리
        $attacker->logBattleResult();
        $defender->logBattleResult();

        $attacker->tryWound();
        $defender->tryWound();
    }
    
    if($defender instanceof WarUnitCity){
        $newConflict = $defender->addConflict();
        if($newConflict){
            $nationName = $attacker->getNationVar('name');
            $josaYi = JosaUtil::pick($nationName, '이');
            $logger->pushGlobalHistoryLog("<M><b>【분쟁】</b></><D><b>{$nationName}</b></>{$josaYi} <G><b>{$defender->getName()}</b></> 공략에 가담하여 분쟁이 발생하고 있습니다.");
        }
    }

    ($getNextDefender)($defender, false);

    return $conquerCity;
}

function DeleteConflict($nation) {
    $db = DB::db();

    foreach($db->queryAllLists('SELECT city, conflict FROM city WHERE conflict!=%s', '{}') as list($cityID, $rawConflict)){
        $conflict = Json::decode($rawConflict);

        if(!$conflict || !is_array($conflict)){
            continue;
        }
        if(!key_exists($nation, $conflict)){
            continue;
        }

        unset($conflict[$nation]);

        $db->update('city', [
            'conflict'=>Json::encode($conflict)
        ], 'city=%i', $cityID);
    }
}

function getConquerNation($city) : int {
    $conflict = Json::decode($city['conflict']);
    return Util::array_first_key($conflict);
}

function ConquerCity($admin, $general, $city, $nation, $destnation) {
    '@phan-var array<string,mixed> $city';
    $db = DB::db();
    $connect=$db->get();

    $alllog = [];
    $log = [];
    $history = [];

    if($destnation['nation'] > 0) {
        $destnationName = "<D><b>{$destnation['name']}</b></>의";
    } else {
        $destnationName = "공백지인";
    }

    $year = $admin['year'];
    $month = $admin['month'];

    $josaUl = JosaUtil::pick($city['name'], '을');
    $josaYiNation = JosaUtil::pick($nation['name'], '이');
    $josaYiGen = JosaUtil::pick($general['name'], '이');
    $josaYiCity = JosaUtil::pick($city['name'], '이');
    $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>{$josaYiGen} <G><b>{$city['name']}</b></> 공략에 <S>성공</>했습니다.";
    $log[] = "<C>●</><G><b>{$city['name']}</b></> 공략에 <S>성공</>했습니다.";
    $history[] = "<C>●</>{$year}년 {$month}월:<S><b>【지배】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$city['name']}</b></>{$josaUl} 지배했습니다.";
    pushGeneralHistory($general, "<C>●</>{$year}년 {$month}월:<G><b>{$city['name']}</b></>{$josaUl} <S>함락</>시킴");
    pushNationHistory($nation, "<C>●</>{$year}년 {$month}월:<Y>{$general['name']}</>{$josaYiGen} {$destnationName} <G><b>{$city['name']}</b></>{$josaUl} <S>점령</>");
    pushNationHistory($destnation, "<C>●</>{$year}년 {$month}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>에 의해 <G><b>{$city['name']}</b></>{$josaYiCity} <span class='ev_highlight'>함락</span>");

    $citycount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation = %i', $city['nation']);
    $renewFront = false;

    // 국가 멸망시
    //TODO: 국가 멸망 코드를 별도로 작성
    if($citycount == 1 && $city['nation'] != 0) {
        $losenation = $destnation;

        $josaYi = JosaUtil::pick($losenation['name'], '이');
        $josaUl = JosaUtil::pick($losenation['name'], '을');
        $history[] = "<C>●</>{$year}년 {$month}월:<R><b>【멸망】</b></><D><b>{$losenation['name']}</b></>{$josaYi} 멸망하였습니다.";
        pushNationHistory($nation, "<C>●</>{$year}년 {$month}월:<D><b>{$losenation['name']}</b></>{$josaUl} 정복");

        $loseGeneralGold = 0;
        $loseGeneralRice = 0;
        //멸망국 장수들 역사 기록 및 로그 전달
        $josaYi = JosaUtil::pick($losenation['name'], '이');
        $genlog = ["<C>●</><D><b>{$losenation['name']}</b></>{$josaYi} <R>멸망</>했습니다."];


        // 국가 백업
        $oldNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation=%i', $city['nation']);
        $oldNationGenerals = $db->query('SELECT * FROM general WHERE nation=%i', $city['nation']);
        $oldNation['generals'] = array_map(function($gen){
            //다른 코드와는 다르게 공용으로 쓰므로 남겨둠
            return $gen['no'];
        }, $oldNationGenerals);
        $oldNation['aux'] = Json::decode($oldNation['aux']);

        foreach($oldNationGenerals as $gen){

            $loseGold = intdiv($gen['gold'] * (rand()%30+20), 100);
            $loseRice = intdiv($gen['rice'] * (rand()%30+20), 100);
            $genlog[1] = "<C>●</>도주하며 금<C>$loseGold</> 쌀<C>$loseRice</>을 분실했습니다.";
            
            $query = "update general set gold=gold-{$loseGold},rice=rice-{$loseRice} where no={$gen['no']}";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            
            pushGenLog($gen, $genlog);
            
            pushGeneralHistory($gen, "<C>●</>{$year}년 {$month}월:<D><b>{$losenation['name']}</b></>{$josaYi} <R>멸망</>");
            pushOldNationStop($gen['no'], $city['nation']);

            $loseGeneralGold += $loseGold;
            $loseGeneralRice += $loseRice;
            
            //모두 등용장 발부
            if(Util::randBool(0.5)) {
                $msg = ScoutMessage::buildScoutMessage($general['no'], $gen['no']);
                if($msg){
                    $msg->send(true);
                }
            }

            //NPC인 경우 10% 확률로 임관(엔장, 인재, 의병)
            if($gen['npc'] >= 2 && $gen['npc'] <= 8 && rand() % 100 < 10) {
                $commissionCommand = EncodeCommand(0, 0, $nation['nation'], 25); //임관
                $query = "update general set turn0='$commissionCommand' where no={$gen['no']}";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
        unset($genlog[1]);
        
        // 승전국 보상
        $losenation['gold'] -= GameConst::$basegold;
        $losenation['rice'] -= GameConst::$baserice;
        if($losenation['gold'] < 0) { $losenation['gold'] = 0; }
        if($losenation['rice'] < 0) { $losenation['rice'] = 0; }
        
        $losenation['gold'] += $loseGeneralGold;
        $losenation['rice'] += $loseGeneralRice;
        
        $losenation['gold'] = intdiv($losenation['gold'], 2);
        $losenation['rice'] = intdiv($losenation['gold'], 2);
        
        // 기본량 제외 금쌀50% + 장수들 분실 금쌀50% 흡수
        $query = "update nation set gold=gold+'{$losenation['gold']}',rice=rice+'{$losenation['rice']}' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        
        //아국 수뇌부에게 로그 전달
        $query = "select no,name,nation from general where nation='{$general['nation']}' and level>='9'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$losenation['name']}</b></> 정복으로 금<C>{$losenation['gold']}</> 쌀<C>{$losenation['rice']}</>을 획득했습니다.";
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
        }
        
        
        //분쟁기록 모두 지움
        DeleteConflict($city['nation']);
        // 전 장수 공헌 명성치 깎음
        $query = "update general set dedication=dedication*0.5,experience=experience*0.9 where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 전 도시 공백지로
        $query = "update city set nation='0',gen1='0',gen2='0',gen3='0',conflict='{}',term=0 where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 전 장수 소속 무소속으로, 재야로, 부대 탈퇴
        $query = "update general set nation='0',belong='0',level='0',troop='0' where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대도 삭제
        $query = "delete from troop where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 삭제
        $query = "delete from diplomacy where me='{$city['nation']}' or you='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        
        $db->insert('ng_old_nations', [
            'server_id'=>UniqueConst::$serverID,
            'nation'=>$city['nation'],
            'data'=>Json::encode($oldNation)
        ]);
        // 국가 삭제
        $query = "delete from nation where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $renewFront = true;
    // 멸망이 아니면
    } else {
        // 태수,군사,시중은 일반으로...
        $db->update('general',[
            'level'=>1
        ], 'no IN %li',[$city['gen1'], $city['gen2'], $city['gen3']]);
        
        //수도였으면 긴급 천도
        if(isset($destnation['capital']) && $destnation['capital'] == $city['city']) {
            $minCity = findNextCapital($city['city'], $destnation['nation']);

            $minCityName = CityConst::byID($minCity)->name;

            $josaYi = JosaUtil::pick($destnation['name'], '이');
            $history[] = "<C>●</>{$year}년 {$month}월:<M><b>【긴급천도】</b></><D><b>{$destnation['name']}</b></>{$josaYi} 수도가 함락되어 <G><b>$minCityName</b></>으로 긴급천도하였습니다.";

            //아국 수뇌부에게 로그 전달
            $query = "select no,name,nation from general where nation='{$destnation['nation']}' and level>='5'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
            $genlog = ["<C>●</>수도가 함락되어 <G><b>$minCityName</b></>으로 <M>긴급천도</>합니다."];
            for($i=0; $i < $gencount; $i++) {
                $gen = MYDB_fetch_array($result);
                pushGenLog($gen, $genlog);
            }
            //천도
            $query = "update nation set capital='$minCity',gold=gold*0.5,rice=rice*0.5 where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //보급도시로 만듬
            $query = "update city set supply=1 where city='$minCity'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //수뇌부 이동
            $query = "update general set city='$minCity' where nation='{$destnation['nation']}' and level>='5'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //장수 사기 감소
            $query = "update general set atmos=atmos*0.8 where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            refreshNationStaticInfo();
        }
    }

    $conquerNation = getConquerNation($city);

    if($conquerNation == $general['nation']) {
        // 이동
        $query = "update general set city='{$city['city']}' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        if($city['level'] > 3) {
            // 도시 소속 변경, 태수,군사,시중 초기화
            $query = "update city set supply=1,conflict='{}',term=0,agri=agri*0.7,comm=comm*0.7,secu=secu*0.7,def=1000,wall=1000,nation='{$general['nation']}',gen1=0,gen2=0,gen3=0 where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            // 도시 소속 변경, 태수,군사,시중 초기화
            $query = "update city set supply=1,conflict='{}',term=0,agri=agri*0.7,comm=comm*0.7,secu=secu*0.7,def=def2/2,wall=wall2/2,nation='{$general['nation']}',gen1=0,gen2=0,gen3=0 where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //전방설정
        SetNationFront($nation['nation']);
        SetNationFront($destnation['nation']);
    } else {
        $query = "select name,nation from nation where nation='$conquerNation'";
        $conquerResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $conquerNationArray = MYDB_fetch_array($conquerResult);

        $josaUl = JosaUtil::pick($city['name'], '을');
        $josaYi = JosaUtil::pick($conquerNationArray['name'], '이');
        $history[] = "<C>●</>{$year}년 {$month}월:<Y><b>【분쟁협상】</b></><D><b>{$conquerNationArray['name']}</b></>{$josaYi} 영토분쟁에서 우위를 점하여 <G><b>{$city['name']}</b></>{$josaUl} 양도받았습니다.";
        pushNationHistory($nation, "<C>●</>{$year}년 {$month}월:<G><b>{$city['name']}</b></>{$josaUl} <D><b>{$conquerNationArray['name']}</b></>에 <Y>양도</>");
        pushNationHistory($conquerNationArray, "<C>●</>{$year}년 {$month}월:<D><b>{$nation['name']}</b></>에서 <G><b>{$city['name']}</b></>{$josaUl} <S>양도</> 받음");

        $query = [
            'supply'=>1,
            'term'=>0,
            'conflict'=>'{}',
            'agri'=>$db->sqleval('agri*0.7'),
            'comm'=>$db->sqleval('comm*0.7'),
            'secu'=>$db->sqleval('secu*0.7'),
            'nation'=>$conquerNation,
            'gen1'=>0,
            'gen2'=>0,
            'gen3'=>0
        ];
        if($city['level'] > 3) {
            $query['def'] = 1000;
            $query['wall'] = 1000;
        } else {
            // 도시 소속 변경, 태수,군사,시중 초기화
            $query['def'] = $db->sqleval('def2/2');
            $query['wall'] = $db->sqleval('wall2/2');
        }
        $db->update('city', $query, 'city=%i', (int)$city['city']);
        //전방설정
        SetNationFront($destnation['nation']);
        SetNationFront($conquerNation);
    }

    if($renewFront){
        foreach(getAllNationStaticInfo() as $nation){
            if($nation['level'] <= 0){
                continue;
            }
            SetNationFront($nation['nation']);
        }
    }

    pushGenLog($general, $log);
    pushGeneralPublicRecord($alllog, $year, $month);
    pushWorldHistory($history);
}

function findNextCapital(int $capitalID, int $nationID):int{
    $distList = searchDistance($capitalID, 99, true);

    $cities = [];
    foreach(
        DB::db()->query(
            'SELECT city, pop FROM city WHERE nation=%i and city!=%i', 
            $nationID, 
            $capitalID
        ) as $row
    ){
        $cities[$row['city']] = $row['pop'];
    };

    

    foreach($distList as $dist=>$distSubList){
        $maxCityPop = 0;
        $minCity = 0;
        
        foreach($distSubList as $cityID){
            if(!key_exists($cityID, $cities)){
                continue;
            }
            $cityPop = $cities[$cityID];

            if($cityPop < $maxCityPop){
                continue;
            }
            $minCity = $cityID;
            $maxCityPop = $cityPop;
        }

        if($minCity){
            return $minCity;
        }
    }
    throw new \RuntimeException('도시가 남지 않았는데 긴천을 시도하고 있습니다');
}