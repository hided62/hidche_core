<?php
namespace sammo;


function processWar(General $attackerGeneral, array $rawAttackerNation, array $rawDefenderCity){

    $db = DB::db();

    $attackerNationID = $attackerGeneral->getNationID();
    $defenderNationID = $rawDefenderCity['nation'];

    if($defenderNationID == 0){
        $rawDefenderNation =  [
            'nation'=>0,
            'name'=>'재야',
            'capital'=>0,
            'level'=>0,
            'gold'=>0,
            'rice'=>2000,
            'type'=>GameConst::$neutralNationType,
            'tech'=>0,
            'gennum'=>1     
        ];
    }
    else{
        $rawDefenderNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $defenderNationID);
    }

    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$startYear, $year, $month, $cityRate, $joinMode] = $gameStor->getValuesAsArray(['startyear', 'year', 'month', 'city_rate', 'join_mode']);

    $attacker = new WarUnitGeneral($attackerGeneral, $rawAttackerNation, true);

    $city = new WarUnitCity($rawDefenderCity, $rawDefenderNation, $year, $month, $cityRate);

    $defenderIDList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND city=%i AND nation!=0 and crew > 0 and rice>(crew/100) and train>=defence_train and atmos>=defence_train', $city->getVar('nation'), $city->getVar('city'));
    $defenderList = General::createGeneralObjListFromDB($defenderIDList, null, 2);

    usort($defenderList, function(General $lhs, General $rhs){
        return -(extractBattleOrder($lhs) <=> extractBattleOrder($rhs));
    });

    $iterDefender = new \ArrayIterator($defenderList);
    $iterDefender->rewind();

    $getNextDefender = function(?WarUnit $prevDefender, bool $reqNext) use ($iterDefender, $rawDefenderNation, $db) {
        if($prevDefender !== null){
            $prevDefender->applyDB($db);
        }

        if(!$reqNext){
            return null;
        }

        if(!$iterDefender->valid()){
            return null;
        }

        $nextDefender = $iterDefender->current();
        if(extractBattleOrder($nextDefender) <= 0){
            return null;
        }


        $retVal = new WarUnitGeneral(
            $nextDefender,
            $rawDefenderNation,
            false
        );
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
    ], 'city=%i', $attackerGeneral->getCityID());

    $db->update('city', [
        'dead' => $db->sqleval('dead + %i', $totalDead * 0.6)
    ], 'city=%i', $rawDefenderCity['city']);

    $attackerIncTech = buildNationTypeClass($rawAttackerNation['type'])->onCalcDomestic('기술', 'score', $attacker->getDead() * 0.01);
    $defenderIncTech = buildNationTypeClass($rawDefenderNation['type'])->onCalcDomestic('기술', 'score', $attacker->getKilled() * 0.01);

    $attackerGenCnt = $rawAttackerNation['gennum'];
    $defenderGenCnt = $rawDefenderNation['gennum'];
    $attackerGenCnt_eff = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc != 5', $rawAttackerNation['nation']);
    $defenderGenCnt_eff = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc != 5', $rawDefenderNation['nation']);

    if($attackerGenCnt_eff < GameConst::$initialNationGenLimit){
        $attackerGenCnt = GameConst::$initialNationGenLimit;
        $attackerGenCnt_eff = GameConst::$initialNationGenLimit;
    }

    if($defenderGenCnt_eff < GameConst::$initialNationGenLimit){
        $defenderGenCnt = GameConst::$initialNationGenLimit;
        $defenderGenCnt_eff = GameConst::$initialNationGenLimit;
    }

    if($attackerGenCnt != $attackerGenCnt_eff){
        $attackerIncTech *= $attackerGenCnt / $attackerGenCnt_eff;
    }

    if($defenderGenCnt != $defenderGenCnt_eff){
        $defenderIncTech *= $defenderGenCnt / $defenderGenCnt_eff;
    }


    if(TechLimit($startYear, $year, $rawAttackerNation['tech'])){
        $attackerIncTech /= 4;
    }
    if(TechLimit($startYear, $year, $rawDefenderNation['tech'])){
        $defenderIncTech /= 4;
    }

    $updateAttackerNation['tech'] = $db->sqleval(
        'tech + %d',
        $attackerIncTech / max(GameConst::$initialNationGenLimit, $rawAttackerNation['gennum'])
    );
    $updateDefenderNation['tech'] = $db->sqleval(
        'tech + %d',
        $defenderIncTech / max(GameConst::$initialNationGenLimit, $rawDefenderNation['gennum'])
    );

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
    $rawAttackerCity = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $attackerGeneral->getCityID());
    $rawAttackerNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $attackerNationID);

    if($defenderNationID !== 0){
        $rawDefenderNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $defenderNationID);
    }
    
    ConquerCity([
        'startyear'=>$startYear,
        'year'=>$year,
        'month'=>$month,
        'city_rate'=>$cityRate,
        'join_mode'=>$joinMode,
    ], $attacker->getRaw(), $city->getRaw(), $rawAttackerNation, $rawDefenderNation);
}

function extractBattleOrder(General $general){
    if($general->getVar('crew') == 0){
        return 0;
    }

    if($general->getVar('rice') <= $general->getVar('crew') / 100){
        return 0;
    }

    $defence_train = $general->getVar('defence_train');
    if($general->getVar('train') < $defence_train){
        return 0;
    }

    if($general->getVar('atmos') < $defence_train){
        return 0;
    }

    $realStat = $general->getLeadership() + $general->getStrength() + $general->getIntel();
    $fullStat = $general->getLeadership(false) + $general->getStrength(false) + $general->getIntel(false);
    $totalStat = ($realStat + $fullStat) / 2;

    $totalCrew = $general->getVar('crew') / 1000000 * (($general->getVar('train') * $general->getVar('atmos')) ** 1.5);
    return $totalStat + $totalCrew / 100;
}

function processWar_NG(
    WarUnitGeneral $attacker,
    callable $getNextDefender, 
    WarUnitCity $city,
    int $relYear
):bool{
    $templates = new \League\Plates\Engine(__DIR__.'/templates');

    $logger = $attacker->getLogger();

    //$attacker->useBattleInitItem();
    
    $date = $attacker->getGeneral()->getTurnTime(General::TURNTIME_HM);

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
                $defender->heavyDecreaseWealth();

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

            $attackerName = $attacker->getName();
            $attackerCrewTypeName = $attacker->getCrewTypeName();

            if($defender instanceof WarUnitGeneral){
                $defenderName = $defender->getName();
                $defenderCrewTypeName = $defender->getCrewTypeName();

                $josaWa = JosaUtil::pick($attackerCrewTypeName, '와');
                $josaYi = JosaUtil::pick($defenderCrewTypeName, '이');
                $logger->pushGlobalActionLog("<Y>{$attackerName}</>의 {$attackerCrewTypeName}{$josaWa} <Y>{$defenderName}</>의 {$defenderCrewTypeName}{$josaYi} 대결합니다.");

                $josaRo = JosaUtil::pick($attackerCrewTypeName, '로');
                $josaUl = JosaUtil::pick($defenderCrewTypeName, '을');
                $attacker->getLogger()->pushGeneralActionLog("{$attackerCrewTypeName}{$josaRo} <Y>{$defenderName}</>의 {$defenderCrewTypeName}{$josaUl} <M>공격</>합니다.");

                $josaRo = JosaUtil::pick($defenderCrewTypeName, '로');
                $josaUl = JosaUtil::pick($attackerCrewTypeName, '을');
                $defender->getLogger()->pushGeneralActionLog("{$defenderCrewTypeName}{$josaRo} <Y>{$attackerName}</>의 {$attackerCrewTypeName}{$josaUl} <M>수비</>합니다.");
            }
            else{
                $josaYi = JosaUtil::pick($attackerName, '이');
                $josaRo = JosaUtil::pick($attackerCrewTypeName, '로');
                $logger->pushGlobalActionLog("<Y>{$attackerName}</>{$josaYi} {$attackerCrewTypeName}{$josaRo} 성벽을 공격합니다.");
                $logger->pushGeneralActionLog("{$attackerCrewTypeName}{$josaRo} 성벽을 <M>공격</>합니다.", ActionLogger::PLAIN);
            }

            //$defender->useBattleInitItem();

            $attacker->setOppose($defender);
            $defender->setOppose($attacker);

            $initCaller = $attacker->getGeneral()->getBattleInitSkillTriggerList($attacker);
            $initCaller->merge($defender->getGeneral()->getBattleInitSkillTriggerList($defender));

            $initCaller->fire([], [$attacker, $defender]);
        }

        $attacker->beginPhase();
        $defender->beginPhase();

        $battleCaller = $attacker->getGeneral()->getBattlePhaseSkillTriggerList($attacker);
        $battleCaller->merge($defender->getGeneral()->getBattlePhaseSkillTriggerList($defender));

        $battleCaller->fire([], [$attacker, $defender]);
        
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
    pushGeneralHistory($general['no'], ["<C>●</>{$year}년 {$month}월:<G><b>{$city['name']}</b></>{$josaUl} <S>함락</>시킴"]);
    pushNationHistory($nation['nation'], ["<C>●</>{$year}년 {$month}월:<Y>{$general['name']}</>{$josaYiGen} {$destnationName} <G><b>{$city['name']}</b></>{$josaUl} <S>점령</>"]);
    pushNationHistory($destnation['nation'], ["<C>●</>{$year}년 {$month}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>에 의해 <G><b>{$city['name']}</b></>{$josaYiCity} <span class='ev_highlight'>함락</span>"]);

    $citycount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation = %i', $city['nation']);
    $renewFront = false;

    // 국가 멸망시
    //TODO: 국가 멸망 코드를 별도로 작성
    if($citycount == 1 && $city['nation'] != 0) {
        $losenation = $destnation;

        $josaYi = JosaUtil::pick($losenation['name'], '이');
        $josaUl = JosaUtil::pick($losenation['name'], '을');
        $history[] = "<C>●</>{$year}년 {$month}월:<R><b>【멸망】</b></><D><b>{$losenation['name']}</b></>{$josaYi} 멸망하였습니다.";
        pushNationHistory($nation['nation'], ["<C>●</>{$year}년 {$month}월:<D><b>{$losenation['name']}</b></>{$josaUl} 정복"]);

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
            
            pushGenLog($gen['no'], $genlog);
            
            pushGeneralHistory($gen['no'], ["<C>●</>{$year}년 {$month}월:<D><b>{$losenation['name']}</b></>{$josaYi} <R>멸망</>"]);
            pushOldNationStop($gen['no'], $city['nation']);

            $loseGeneralGold += $loseGold;
            $loseGeneralRice += $loseRice;
            
            //모두 등용장 발부
            if($admin['join_mode'] != 'onlyRandom' && Util::randBool(0.5)) {
                $msg = ScoutMessage::buildScoutMessage($general['no'], $gen['no']);
                if($msg){
                    $msg->send(true);
                }
            }

            //NPC인 경우 10% 확률로 임관(엔장, 인재, 의병)
            if($admin['join_mode'] != 'onlyRandom' && $gen['npc'] >= 2 && $gen['npc'] <= 8 && $gen['npc'] != 5 && Util::randBool(0.1)) {
                setGeneralCommand($gen['no'], [0], 'che_임관', ['destNationID'=>$nation['nation']]);
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
        $query = "select no,name,nation from general where nation='{$general['nation']}' and officer_level>='9'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$losenation['name']}</b></> 정복으로 금<C>{$losenation['gold']}</> 쌀<C>{$losenation['rice']}</>을 획득했습니다.";
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen['no'], $genlog);
        }
        
        
        //분쟁기록 모두 지움
        DeleteConflict($city['nation']);
        // 전 장수 공헌 명성치 깎음
        //TODO: experience를 General에
        $query = "update general set dedication=dedication*0.5,experience=experience*0.9 where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 전 도시 공백지로
        $query = "update city set nation='0',conflict='{}',term=0 where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 전 장수 소속 무소속으로, 재야로, 부대 탈퇴
        $query = "update general set nation='0',belong='0',officer_level='0',officer_city=0,troop='0' where nation='{$city['nation']}'";
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
        $db->delete('nation_turn', 'nation_id=%i', $city['nation']);
        $renewFront = true;
    // 멸망이 아니면
    } else {
        // 태수,군사,종사은 일반으로...
        $db->update('general',[
            'officer_level'=>1,
            'officer_city'=>0,
        ], 'officer_city = %i',$city['city']);
        
        //수도였으면 긴급 천도
        if(isset($destnation['capital']) && $destnation['capital'] == $city['city']) {
            $minCity = findNextCapital($city['city'], $destnation['nation']);

            $minCityName = CityConst::byID($minCity)->name;

            $josaYi = JosaUtil::pick($destnation['name'], '이');
            $history[] = "<C>●</>{$year}년 {$month}월:<M><b>【긴급천도】</b></><D><b>{$destnation['name']}</b></>{$josaYi} 수도가 함락되어 <G><b>$minCityName</b></>으로 긴급천도하였습니다.";

            //아국 수뇌부에게 로그 전달
            $query = "select no,name,nation from general where nation='{$destnation['nation']}' and officer_level>='5'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
            $genlog = ["<C>●</>수도가 함락되어 <G><b>$minCityName</b></>으로 <M>긴급천도</>합니다."];
            for($i=0; $i < $gencount; $i++) {
                $gen = MYDB_fetch_array($result);
                pushGenLog($gen['no'], $genlog);
            }
            //천도
            $query = "update nation set capital='$minCity',gold=gold*0.5,rice=rice*0.5 where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //보급도시로 만듬
            $query = "update city set supply=1 where city='$minCity'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //수뇌부 이동
            $query = "update general set city='$minCity' where nation='{$destnation['nation']}' and officer_level>='5'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //장수 사기 감소
            $query = "update general set atmos=atmos*0.8 where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            refreshNationStaticInfo();
        }
    }

    $conquerNation = getConquerNation($city);

    if ($conquerNation == $general['nation']) {
        // 이동
        $db->update('general', [
            'city'=>$city['city']
        ], 'no=%i', $general['no']);
    }
    else{
        $conquerNationName = $db->queryFirstField('SELECT `name` FROM nation WHERE nation=%i', $conquerNation);

        $josaUl = JosaUtil::pick($city['name'], '을');
        $josaYi = JosaUtil::pick($conquerNationName, '이');
        $history[] = "<C>●</>{$year}년 {$month}월:<Y><b>【분쟁협상】</b></><D><b>{$conquerNationName}</b></>{$josaYi} 영토분쟁에서 우위를 점하여 <G><b>{$city['name']}</b></>{$josaUl} 양도받았습니다.";
        pushNationHistory($nation['nation'], ["<C>●</>{$year}년 {$month}월:<G><b>{$city['name']}</b></>{$josaUl} <D><b>{$conquerNationName}</b></>에 <Y>양도</>"]);
        pushNationHistory($conquerNation, ["<C>●</>{$year}년 {$month}월:<D><b>{$nation['name']}</b></>에서 <G><b>{$city['name']}</b></>{$josaUl} <S>양도</> 받음"]);
    }
    
    $query = [
        'supply'=>1,
        'term'=>0,
        'conflict'=>'{}',
        'agri'=>$db->sqleval('agri*0.7'),
        'comm'=>$db->sqleval('comm*0.7'),
        'secu'=>$db->sqleval('secu*0.7'),
        'nation'=>$conquerNation,
        'officer4set'=>0,
        'officer3set'=>0,
        'officer2set'=>0
    ];
    if($city['level'] > 3) {
        $query['def'] = 1000;
        $query['wall'] = 1000;
    } else {
        $query['def'] = $db->sqleval('def_max/2');
        $query['wall'] = $db->sqleval('wall_max/2');
    }
    
    $db->update('city', $query, 'city=%i', (int)$city['city']);
    //전방설정

    $nearNationsID = $db->queryFirstColumn(
        'SELECT distinct(nation) FROM city WHERE nation != 0 AND city IN %li',
        array_merge(array_keys(CityConst::byID($city['city'])->path), [$city['city']])
    );
    foreach($nearNationsID as $nationNationID){
        SetNationFront($nationNationID);
    }

    pushGenLog($general['no'], $log);
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