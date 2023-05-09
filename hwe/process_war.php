<?php

namespace sammo;

use sammo\Enums\EventTarget;

function processWar(string $warSeed, General $attackerGeneral, array $rawAttackerNation, array $rawDefenderCity)
{

    $rng = new RandUtil(new LiteHashDRBG($warSeed));
    $db = DB::db();

    $attackerNationID = $attackerGeneral->getNationID();
    $defenderNationID = $rawDefenderCity['nation'];

    if ($defenderNationID == 0) {
        $rawDefenderNation =  [
            'nation' => 0,
            'name' => '재야',
            'capital' => 0,
            'level' => 0,
            'gold' => 0,
            'rice' => 10000,
            'type' => GameConst::$neutralNationType,
            'tech' => 0,
            'gennum' => 1
        ];
    } else {
        $rawDefenderNation = $db->queryFirstRow('SELECT nation,`level`,`name`,capital,gennum,tech,`type`,gold,rice FROM nation WHERE nation = %i', $defenderNationID);
    }

    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$startYear, $year, $month, $joinMode] = $gameStor->getValuesAsArray(['startyear', 'year', 'month', 'join_mode']);

    $attacker = new WarUnitGeneral($rng, $attackerGeneral,$rawAttackerNation, true);

    $city = new WarUnitCity($rng, $rawDefenderCity, $rawDefenderNation, $year, $month, $startYear);

    $defenderIDList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND city=%i AND nation!=0 and crew > 0 and rice>(crew/100) and train>=defence_train and atmos>=defence_train', $city->getVar('nation'), $city->getVar('city'));
    $defenderGeneralList = General::createGeneralObjListFromDB($defenderIDList, null, 2);

    /** @var WarUnit[] */
    $defenderList = [];
    foreach($defenderGeneralList as $defenderGeneral){
        $defenderList[] = new WarUnitGeneral($rng, $defenderGeneral, $rawDefenderNation, false);
    }

    $defenderList[] = $city;

    usort($defenderList, function (WarUnit $lhs, WarUnit $rhs) use ($attacker) {
        return - (extractBattleOrder($lhs, $attacker) <=> extractBattleOrder($rhs, $attacker));
    });

    $iterDefender = new \ArrayIterator($defenderList);
    $iterDefender->rewind();

    $getNextDefender = function (?WarUnit $prevDefender, bool $reqNext) use ($iterDefender, $db, $attacker) {
        if ($prevDefender !== null) {
            $prevDefender->applyDB($db);
        }

        if (!$reqNext) {
            return null;
        }

        if (!$iterDefender->valid()) {
            return null;
        }

        /** @var WarUnit */
        $nextDefender = $iterDefender->current();
        if (extractBattleOrder($nextDefender, $attacker) <= 0) {
            return null;
        }
        $iterDefender->next();
        return $nextDefender;
    };

    $conquerCity = processWar_NG($warSeed, $attacker, $getNextDefender, $city);

    $attacker->applyDB($db);

    //NOTE: $rawAttackerNation과 $rawDefenderNation은 전투중에 '바뀌지 않음'을 전제로 하고 있다. (현재 구현도 그렇게 되어있음)

    $rawDefenderCity = $city->getRaw();
    $updateAttackerNation = [];
    $updateDefenderNation = [];

    if ($city->getVar('supply')) {
        if ($city->getPhase() > 0) {
            $rice = $city->getKilled() / 100 * 0.8;
            $rice *= $city->getCrewType()->rice;
            $rice *= getTechCost($rawDefenderNation['tech']);
            $rice *= $city->getCityTrainAtmos() / 100 - 0.2;
            Util::setRound($rice);

            $updateDefenderNation['rice'] = max(0, $rawDefenderNation['rice'] - $rice);
        } else if ($conquerCity) {
            if ($rawDefenderNation['capital'] == $rawDefenderCity['city']) {
                $updateDefenderNation['rice'] = $rawDefenderNation['rice'] + 1000;
            } else {
                $updateDefenderNation['rice'] = $rawDefenderNation['rice'] + 500;
            }
        }
    }

    $totalDead = $attacker->getKilled() + $attacker->getDead();

    $db->update('city', [
        'dead' => $db->sqleval('dead + %i', $totalDead * 0.4)
    ], 'city=%i', $attackerGeneral->getCityID());

    $db->update('city', [
        'dead' => $db->sqleval('dead + %i', $totalDead * 0.6)
    ], 'city=%i', $rawDefenderCity['city']);

    $attackerIncTech = buildNationTypeClass($rawAttackerNation['type'])->onCalcDomestic('기술', 'score', $attacker->getDead() * 0.012);
    $defenderIncTech = buildNationTypeClass($rawDefenderNation['type'])->onCalcDomestic('기술', 'score', $attacker->getKilled() * 0.009);

    $attackerGenCnt = $rawAttackerNation['gennum'];
    $defenderGenCnt = $rawDefenderNation['gennum'];
    $attackerGenCnt_eff = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc != 5', $rawAttackerNation['nation']);
    $defenderGenCnt_eff = $db->queryFirstField('SELECT count(no) FROM general WHERE nation=%i AND npc != 5', $rawDefenderNation['nation']);

    if ($attackerGenCnt_eff < GameConst::$initialNationGenLimit) {
        $attackerGenCnt = GameConst::$initialNationGenLimit;
        $attackerGenCnt_eff = GameConst::$initialNationGenLimit;
    }

    if ($defenderGenCnt_eff < GameConst::$initialNationGenLimit) {
        $defenderGenCnt = GameConst::$initialNationGenLimit;
        $defenderGenCnt_eff = GameConst::$initialNationGenLimit;
    }

    if ($attackerGenCnt != $attackerGenCnt_eff) {
        $attackerIncTech *= $attackerGenCnt / $attackerGenCnt_eff;
    }

    if ($defenderGenCnt != $defenderGenCnt_eff) {
        $defenderIncTech *= $defenderGenCnt / $defenderGenCnt_eff;
    }


    if (TechLimit($startYear, $year, $rawAttackerNation['tech'])) {
        $attackerIncTech /= 4;
    }
    if (TechLimit($startYear, $year, $rawDefenderNation['tech'])) {
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
        'dead' => $db->sqleval('dead + %i', $attacker->getDead())
    ], 'me = %i and you = %i', $attackerNationID, $defenderNationID);

    $db->update('diplomacy', [
        'dead' => $db->sqleval('dead + %i', $attacker->getKilled())
    ], 'me = %i and you = %i', $defenderNationID, $attackerNationID);

    if (!$conquerCity) {
        return;
    }

    ConquerCity([
        'startyear' => $startYear,
        'year' => $year,
        'month' => $month,
        'join_mode' => $joinMode,
    ], $attacker->getGeneral(), $city->getRaw());
}

function extractBattleOrder(WarUnit $defender, WarUnit $attacker)
{
    if($defender instanceof WarUnitCity){
        return -1;
    }

    $general = $defender->getGeneral();
    if ($general->getVar('crew') == 0) {
        return 0;
    }

    if ($general->getVar('rice') <= $general->getVar('crew') / 100) {
        return 0;
    }

    $defence_train = $general->getVar('defence_train');
    if ($general->getVar('train') < $defence_train) {
        return 0;
    }

    if ($general->getVar('atmos') < $defence_train) {
        return 0;
    }

    $realStat = $general->getLeadership() + $general->getStrength() + $general->getIntel();
    $fullStat = $general->getLeadership(false) + $general->getStrength(false) + $general->getIntel(false);
    $totalStat = ($realStat + $fullStat) / 2;

    $totalCrew = $general->getVar('crew') / 1000000 * (($general->getVar('train') * $general->getVar('atmos')) ** 1.5);
    return $totalStat + $totalCrew / 100;
}

function processWar_NG(
    string $warSeed,
    WarUnitGeneral $attacker,
    callable $getNextDefender,
    WarUnitCity $city,
): bool {
    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    $logger = $attacker->getLogger();

    //$attacker->useBattleInitItem();

    $date = $attacker->getGeneral()->getTurnTime(General::TURNTIME_HM);

    $attackerNationUpdate = [];
    $defenderNationUpdate = [];

    $defender = ($getNextDefender)(null, true);
    $conquerCity = false;

    $josaRo = JosaUtil::pick($city->getName(), '로');
    $josaYi = JosaUtil::pick($attacker->getName(), '이');

    $logger->pushGlobalActionLog("<D><b>{$attacker->getNationVar('name')}</b></>의 <Y>{$attacker->getName()}</>{$josaYi} <G><b>{$city->getName()}</b></>{$josaRo} 진격합니다.<span class='hidden_but_copyable'>(전투시드: {$warSeed})</span>");
    $logger->pushGeneralActionLog("<G><b>{$city->getName()}</b></>{$josaRo} <M>진격</>합니다.<span class='hidden_but_copyable'>(전투시드: {$warSeed})</span> <1>$date</>");

    $logWritten = false;

    while($attacker->getPhase() < $attacker->getMaxPhase()){
        $logWritten = false;
        if ($defender === null) {
            $defender = $city;

            if ($city->getNationVar('rice') <= 0 && $city->getVar('supply')) {
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

        if ($defender->getPhase() == 0 && $defender->getOppose() == null) {
            $defender->setPrePhase($attacker->getPhase());

            $attacker->addTrain(1);
            $defender->addTrain(1);

            $attackerCrewTypeCoef = $attacker->getCrewType()->getAttackCoef($defender->getCrewType()) * $defender->getCrewType()->getDefenceCoef($attacker->getCrewType());
            $defenderCrewTypeCoef = $defender->getCrewType()->getAttackCoef($attacker->getCrewType()) * $attacker->getCrewType()->getDefenceCoef($defender->getCrewType());
            /*
            if($attackerCrewTypeCoef > $defenderCrewTypeCoef && $attacker instanceof WarUnitGeneral){
                $attacker->getGeneral()->increaseInheritancePoint(InheritanceKey::snipe_combat, 1);
            }
            if($defenderCrewTypeCoef > $attackerCrewTypeCoef && $defender instanceof WarUnitGeneral){
                $defender->getGeneral()->increaseInheritancePoint(InheritanceKey::snipe_combat, 1);
            }
            */

            $attackerName = $attacker->getName();
            $attackerCrewTypeName = $attacker->getCrewTypeName();

            if ($defender instanceof WarUnitGeneral) {
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
            } else {
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

            $initCaller->fire($attacker->rng, [], [$attacker, $defender]);
        }

        $attacker->beginPhase();
        $defender->beginPhase();

        $battleCaller = $attacker->getGeneral()->getBattlePhaseSkillTriggerList($attacker);
        $battleCaller->merge($defender->getGeneral()->getBattlePhaseSkillTriggerList($defender));

        $battleCaller->fire($attacker->rng, [], [$attacker, $defender]);

        $deadDefender = $attacker->calcDamage();
        $deadAttacker = $defender->calcDamage();

        $attackerHP = $attacker->getHP();
        $defenderHP = $defender->getHP();

        if ($deadAttacker > $attackerHP || $deadDefender > $defenderHP) {
            $deadAttackerRatio = $deadAttacker / max(1, $attackerHP);
            $deadDefenderRatio = $deadDefender / max(1, $defenderHP);

            if ($deadDefenderRatio > $deadAttackerRatio) {
                //수비자가 더 병력 부족
                $deadAttacker /= $deadDefenderRatio;
                $deadDefender = $defenderHP;
            } else {
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

        if($defender->getPhase() < 0){
            $phaseNickname = '先';
        }
        else{
            $currPhase = $attacker->getPhase() + 1;
            $phaseNickname = "{$currPhase} ";
        }


        if ($deadAttacker > 0 || $deadDefender > 0) {
            $attacker->getLogger()->pushGeneralBattleDetailLog(
                "$phaseNickname: <Y1>【{$attacker->getName()}】</> <C>{$attacker->getHP()} (-$deadAttacker)</> VS <C>{$defender->getHP()} (-$deadDefender)</> <Y1>【{$defender->getName()}】</>"
            );

            $defender->getLogger()->pushGeneralBattleDetailLog(
                "$phaseNickname: <Y1>【{$defender->getName()}】</> <C>{$defender->getHP()} (-$deadDefender)</> VS <C>{$attacker->getHP()} (-$deadAttacker)</> <Y1>【{$attacker->getName()}】</>"
            );
        }


        $attacker->addPhase();
        $defender->addPhase();

        if (!$attacker->continueWar($noRice)) {
            $logWritten = true;

            $attacker->logBattleResult();
            $defender->logBattleResult();

            $attacker->addLose();
            $defender->addWin();

            $attacker->tryWound();
            $defender->tryWound();

            $josaYi = JosaUtil::pick($attacker->getCrewTypeName(), '이');
            $logger->pushGlobalActionLog("<Y>{$attacker->getName()}</>의 {$attacker->getCrewTypeName()}{$josaYi} 퇴각했습니다.");
            if ($noRice) {
                $attacker->getLogger()->pushGeneralActionLog("군량 부족으로 퇴각합니다.", ActionLogger::PLAIN);
            } else {
                $attacker->getLogger()->pushGeneralActionLog("퇴각했습니다.", ActionLogger::PLAIN);
            }
            $defender->getLogger()->pushGeneralActionLog("<Y>{$attacker->getName()}</>의 {$attacker->getCrewTypeName()}{$josaYi} 퇴각했습니다.", ActionLogger::PLAIN);

            break;
        }

        if (!$defender->continueWar($noRice)) {
            $logWritten = true;

            $attacker->logBattleResult();
            $defender->logBattleResult();

            $attacker->addWin();
            $defender->addLose();

            $attacker->tryWound();
            $defender->tryWound();

            if ($defender === $city) {
                $attacker->addLevelExp(1000);
                $conquerCity = true;
                break;
            }

            $josaYi = JosaUtil::pick($defender->getCrewTypeName(), '이');

            if ($noRice) {
                $logger->pushGlobalActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 패퇴했습니다.");
                $attacker->getLogger()->pushGeneralActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 패퇴했습니다.", ActionLogger::PLAIN);
                $defender->getLogger()->pushGeneralActionLog("군량 부족으로 패퇴합니다.", ActionLogger::PLAIN);
            } else {
                $logger->pushGlobalActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 전멸했습니다.");
                $attacker->getLogger()->pushGeneralActionLog("<Y>{$defender->getName()}</>의 {$defender->getCrewTypeName()}{$josaYi} 전멸했습니다.", ActionLogger::PLAIN);
                $defender->getLogger()->pushGeneralActionLog("전멸했습니다.", ActionLogger::PLAIN);
            }

            if ($attacker->getPhase() >= $attacker->getMaxPhase()) {
                break;
            }

            $defender->finishBattle();
            $defender = ($getNextDefender)($defender, true);

            if ($defender !== null && !($defender instanceof WarUnitGeneral)) {
                throw new \RuntimeException('다음 수비자를 받아오는데 실패');
            }
        }
    }

    if (!$logWritten) {
        //마지막 페이즈의 전투 마무리
        $attacker->logBattleResult();
        $defender->logBattleResult();

        $attacker->tryWound();
        $defender->tryWound();
    }

    $attacker->finishBattle();
    $defender->finishBattle();

    if ($defender instanceof WarUnitCity) {
        $newConflict = $defender->addConflict();
        if ($newConflict) {
            $nationName = $attacker->getNationVar('name');
            $josaYi = JosaUtil::pick($nationName, '이');
            $logger->pushGlobalHistoryLog("<M><b>【분쟁】</b></><D><b>{$nationName}</b></>{$josaYi} <G><b>{$defender->getName()}</b></> 공략에 가담하여 분쟁이 발생하고 있습니다.");
        }
    }

    ($getNextDefender)($defender, false);

    return $conquerCity;
}

function DeleteConflict($nation)
{
    $db = DB::db();

    foreach ($db->queryAllLists('SELECT city, conflict FROM city WHERE conflict!=%s', '{}') as list($cityID, $rawConflict)) {
        $conflict = Json::decode($rawConflict);

        if (!$conflict || !is_array($conflict)) {
            continue;
        }
        if (!key_exists($nation, $conflict)) {
            continue;
        }

        unset($conflict[$nation]);

        $db->update('city', [
            'conflict' => Json::encode($conflict)
        ], 'city=%i', $cityID);
    }
}

function getConquerNation($city): int
{
    $conflict = Json::decode($city['conflict']);
    return Util::array_first_key($conflict);
}

function ConquerCity(array $admin, General $general, array $city)
{
    $db = DB::db();

    $year = $admin['year'];
    $month = $admin['month'];

    $attackerID = $general->getID();
    $attackerNationID = $general->getNationID();
    $attackerGeneralName = $general->getName();
    $attackerNationName = $general->getStaticNation()['name'];
    $attackerLogger = $general->getLogger();

    $cityID = $city['city'];
    $cityName = $city['name'];

    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
        UniqueConst::$hiddenSeed,
        'ConquerCity',
        $year,
        $month,
        $attackerNationID,
        $attackerID,
        $cityID
    )));

    $defenderNationID = $city['nation'];
    $defenderStaticNation = getNationStaticInfo($defenderNationID);
    $defenderNationName = $defenderStaticNation['name'];

    $defenderNationLogger = new ActionLogger(0, $defenderNationID, $year, $month);

    if ($defenderNationID) {
        $defenderNationDecoration = "<D><b>{$defenderNationName}</b></>의";
    } else {
        $defenderNationDecoration = "공백지인";
    }

    $josaUl = JosaUtil::pick($cityName, '을');
    $josaYiNation = JosaUtil::pick($attackerNationName, '이');
    $josaYiGen = JosaUtil::pick($attackerGeneralName, '이');
    $josaYiCity = JosaUtil::pick($cityName, '이');
    $attackerLogger->pushGeneralActionLog("<G><b>{$cityName}</b></> 공략에 <S>성공</>했습니다.", ActionLogger::PLAIN);
    $attackerLogger->pushGeneralHistoryLog("<G><b>{$cityName}</b></>{$josaUl} <S>점령</>");
    $attackerLogger->pushGlobalActionLog("<Y>{$attackerGeneralName}</>{$josaYiGen} <G><b>{$cityName}</b></> 공략에 <S>성공</>했습니다.");
    $attackerLogger->pushGlobalHistoryLog("<S><b>【지배】</b></><D><b>{$attackerNationName}</b></>{$josaYiNation} <G><b>{$cityName}</b></>{$josaUl} 지배했습니다.");
    $attackerLogger->pushNationalHistoryLog("<Y>{$attackerGeneralName}</>{$josaYiGen} {$defenderNationDecoration} <G><b>{$cityName}</b></> {$josaUl} <S>점령</>");

    $defenderNationLogger->pushNationalHistoryLog("<D><b>{$attackerNationName}</b></>의 <Y>{$attackerGeneralName}</>에 의해 <G><b>{$cityName}</b></>{$josaYiCity} <O>함락</>");

    $gameStor = KVStorage::getStorage($db, 'game_env');

    // 이벤트 핸들러 동작
    if(TurnExecutionHelper::runEventHandler($db, $gameStor, EventTarget::OccupyCity)){
        $gameStor->cacheAll();
    }


    // 국가 멸망시
    if ($defenderNationID && $db->queryFirstField('SELECT count(city) FROM city WHERE nation = %i', $defenderNationID) === 1) {
        $defenderNationLogger->flush();
        unset($defenderNationLogger);

        $loseNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation = %i', $defenderNationID);

        $lord = new General($db->queryFirstRow(
            'SELECT %l FROM general WHERE nation = %i AND officer_level = %i LIMIT 1',
            Util::formatListOfBackticks(General::mergeQueryColumn(['npc', 'gold', 'rice', 'experience', 'explevel', 'belong', 'dedication', 'dedlevel', 'aux'], 1)[0]),
            $defenderNationID,
            12
        ), null, $city, $loseNation, $year, $month, false);

        $josaUl = JosaUtil::pick($defenderNationName, '을');
        $attackerLogger->pushNationalHistoryLog("<D><b>{$defenderNationName}</b></>{$josaUl} 정복");
        $attackerLogger->flush();
        $oldNationGenerals = deleteNation($lord, false);

        $loseGeneralGold = 0;
        $loseGeneralRice = 0;
        foreach ($oldNationGenerals as $oldGeneral) {
            $loseGold = Util::toInt($oldGeneral->getVar('gold') * $rng->nextRange(0.2, 0.5));
            $loseRice = Util::toInt($oldGeneral->getVar('rice') * $rng->nextRange(0.2, 0.5));
            $oldGeneral->getLogger()->pushGeneralActionLog(
                "도주하며 금<C>$loseGold</> 쌀<C>$loseRice</>을 분실했습니다.",
                ActionLogger::PLAIN
            );
            $oldGeneral->increaseVar('gold', -$loseGold);
            $oldGeneral->increaseVar('rice', -$loseRice);
            $oldGeneral->addExperience(-$oldGeneral->getVar('experience') * 0.1, false);
            $oldGeneral->addDedication(-$oldGeneral->getVar('dedication') * 0.5, false);

            $loseGeneralGold += $loseGold;
            $loseGeneralRice += $loseRice;

            $oldGeneral->applyDB($db);

            //모두 등용장 발부
            if ($admin['join_mode'] != 'onlyRandom' && $rng->nextBool(0.5)) {
                $msg = ScoutMessage::buildScoutMessage($attackerID, $oldGeneral->getID());
                if ($msg) {
                    $msg->send(true);
                }
            }

            //NPC인 경우 일정 확률로 임관(엔장, 인재, 의병)
            $npcType = $oldGeneral->getNPCType();
            if ($admin['join_mode'] != 'onlyRandom' && 2 <= $npcType && $npcType <= 8 && $npcType != 5 && $rng->nextBool(GameConst::$joinRuinedNPCProp)) {
                $cmd = buildGeneralCommandClass('che_임관', $oldGeneral, $admin, [
                    'destNationID' => $attackerNationID
                ]);
                $joinTurn = $rng->nextRangeInt(0, 12);
                if ($joinTurn) {
                    _setGeneralCommand(buildGeneralCommandClass('che_견문', $oldGeneral, $admin), iterator_to_array(Util::range($joinTurn)));
                }
                _setGeneralCommand($cmd, [$joinTurn]);
            }
        }

        // 승전국 보상
        $loseNationGold = Util::valueFit($loseNation['gold'] - GameConst::$basegold, 0);
        $loseNationRice = Util::valueFit($loseNation['rice'] - GameConst::$baserice, 0);

        $loseNationGold += $loseGeneralGold;
        $loseNationRice += $loseGeneralRice;

        $loseNationGold = intdiv($loseNationGold, 2);
        $loseNationRice = intdiv($loseNationRice, 2);

        // 기본량 제외 금쌀50% + 장수들 분실 금쌀50% 흡수
        $db->update('nation', [
            'gold' => $db->sqleval('gold + %i', $loseNationGold),
            'rice' => $db->sqleval('rice + %i', $loseNationRice),
        ], 'nation=%i', $attackerNationID);

        //아국 수뇌부에게 로그 전달
        $loseNationGoldText = number_format($loseNationGold);
        $loseNationRiceText = number_format($loseNationRice);
        $resourceLog = "<D><b>{$defenderNationName}</b></> 정복으로 금<C>{$loseNationGoldText}</> 쌀<C>{$loseNationRiceText}</>을 획득했습니다.";
        foreach ($db->queryFirstColumn(
            'SELECT no FROM general WHERE nation=%i AND officer_level>=5',
            $attackerNationID
        ) as $attackerChiefID) {
            if ($attackerChiefID == $attackerID) {
                $attackerLogger->pushGeneralActionLog($resourceLog, ActionLogger::PLAIN);
            } else {
                $chiefLogger = new ActionLogger($attackerChiefID, $attackerNationID, $year, $month);
                $chiefLogger->pushGeneralActionLog($resourceLog, ActionLogger::PLAIN);
                $chiefLogger->flush();
            }
        }

        // 이벤트 핸들러 동작
        TurnExecutionHelper::runEventHandler($db, $gameStor, EventTarget::DestroyNation);

        // 멸망이 아니면
    } else {
        // 태수,군사,종사는 일반으로...
        $db->update('general', [
            'officer_level' => 1,
            'officer_city' => 0,
        ], 'officer_city = %i', $cityID);

        //수도였으면 긴급 천도
        if ($defenderNationID && $defenderStaticNation['capital'] == $cityID) {
            $minCity = findNextCapital($cityID, $defenderNationID);

            $minCityName = CityConst::byID($minCity)->name;

            $josaYi = JosaUtil::pick($defenderNationName, '이');
            $attackerLogger->pushGlobalHistoryLog("<M><b>【긴급천도】</b></><D><b>{$defenderNationName}</b></>{$josaYi} 수도가 함락되어 <G><b>$minCityName</b></>으로 긴급천도하였습니다.");

            $moveLog = "수도가 함락되어 <G><b>$minCityName</b></>으로 <M>긴급천도</>합니다.";
            //아국 수뇌부에게 로그 전달
            foreach ($db->queryFirstColumn(
                'SELECT no FROM general WHERE nation=%i AND officer_level>=5',
                $defenderNationID
            ) as $defenderChiefID) {
                $chiefLogger = new ActionLogger($defenderChiefID, $defenderNationID, $year, $month);
                $chiefLogger->pushGeneralActionLog($moveLog, ActionLogger::PLAIN);
                $chiefLogger->flush();
            }

            //천도
            $db->update('nation', [
                'capital' => $minCity,
                'gold' => $db->sqleval('gold * 0.5'),
                'rice' => $db->sqleval('rice * 0.5'),
            ], 'nation=%i', $defenderNationID);
            //보급도시로 만듬
            $db->update('city', [
                'supply' => 1
            ], 'city=%i', $minCity);
            //수뇌부 이동
            $db->update('general', [
                'city' => $minCity
            ], 'nation=%i AND officer_level>=5', $defenderNationID);
            //장수 사기 감소
            $db->update('general', [
                'atmos' => $db->sqleval('atmos*0.8')
            ], 'nation=%i', $defenderNationID);

            refreshNationStaticInfo();
        }
    }

    $conquerNation = getConquerNation($city);

    if ($conquerNation == $attackerNationID) {
        // 이동
        $db->update('general', [
            'city' => $cityID
        ], 'no=%i', $attackerID);
    } else {
        $conquerNationName = getNationStaticInfo($conquerNation)['name'];
        $conquerNationLogger = new ActionLogger(0, $conquerNation, $year, $month);

        $josaUl = JosaUtil::pick($cityName, '을');
        $josaYi = JosaUtil::pick($conquerNationName, '이');
        $attackerLogger->pushGlobalHistoryLog(
            "<Y><b>【분쟁협상】</b></><D><b>{$conquerNationName}</b></>{$josaYi} 영토분쟁에서 우위를 점하여 <G><b>{$cityName}</b></>{$josaUl} 양도받았습니다."
        );
        $conquerNationLogger->pushNationalHistoryLog("<D><b>{$attackerNationName}</b></>에서 <G><b>{$city['name']}</b></>{$josaUl} <S>양도</> 받음");
        $attackerLogger->pushNationalHistoryLog("<G><b>{$city['name']}</b></>{$josaUl} <D><b>{$conquerNationName}</b></>에 <Y>양도</>");
    }

    $query = [
        'supply' => 1,
        'term' => 0,
        'conflict' => '{}',
        'agri' => $db->sqleval('agri*0.7'),
        'comm' => $db->sqleval('comm*0.7'),
        'secu' => $db->sqleval('secu*0.7'),
        'nation' => $conquerNation,
        'officer_set' => 0,
    ];
    if ($city['level'] > 3) {
        $query['def'] = 1000;
        $query['wall'] = 1000;
    } else {
        $query['def'] = $db->sqleval('def_max/2');
        $query['wall'] = $db->sqleval('wall_max/2');
    }

    $db->update('city', $query, 'city=%i', $cityID);
    //전방설정

    $nearNationsID = $db->queryFirstColumn(
        'SELECT distinct(nation) FROM city WHERE nation != 0 AND city IN %li',
        array_merge(array_keys(CityConst::byID($cityID)->path), [$cityID])
    );

    $nearNationsID[] = $conquerNation;
    $nearNationsID = array_unique($nearNationsID);
    foreach ($nearNationsID as $nationNationID) {
        SetNationFront($nationNationID);
    }
}

function findNextCapital(int $capitalID, int $nationID): int
{
    $distList = searchDistance($capitalID, 99, true);

    $cities = [];
    foreach (DB::db()->query(
            'SELECT city, pop FROM city WHERE nation=%i and city!=%i',
            $nationID,
            $capitalID
        ) as $row) {
        $cities[$row['city']] = $row['pop'];
    };

    foreach ($distList as $dist => $distSubList) {
        $maxCityPop = 0;
        $minCity = 0;

        foreach ($distSubList as $cityID) {
            if (!key_exists($cityID, $cities)) {
                continue;
            }
            $cityPop = $cities[$cityID];

            if ($cityPop < $maxCityPop) {
                continue;
            }
            $minCity = $cityID;
            $maxCityPop = $cityPop;
        }

        if ($minCity) {
            return $minCity;
        }
    }
    throw new \RuntimeException('도시가 남지 않았는데 긴천을 시도하고 있습니다');
}
