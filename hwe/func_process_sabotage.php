<?php

namespace sammo;

function calcSabotageAttackScore(string $statType, array $general, array $nation):array{
    setLeadershipBonus($general, $nation['level']);

    if($statType === 'leader'){
        $genScore = getGeneralLeadership($general, true, true, true);
    }
    else if($statType === 'power'){
        $genScore = getGeneralPower($general, true, true, true);
    }
    else if($statType === 'intel'){
        $genScore = getGeneralIntel($general, true, true, true);
    }
    else{
        throw new MustNotBeReachedException();
    }

    $specialScore = 0;

    if($general['special'] == 31){
        //귀모
        $specialScore += 0.2;
    }
    if($general['special2'] == 41){
        //신산
        $specialScore += 0.1;
    }

    if($general['item'] == 21){
        //육도
        $specialScore += 0.2;
    }
    else if($general['item'] == 22){
        //삼략
        $specialScore += 0.2;
    }

    $itemScore = 0;
    if($general['item'] == 5){
        //이추
        $itemScore += 0.1;
    }
    else if($general['item'] == 6){
        //향낭
        $itemScore += 0.2;
    }
    

    $nationScore = 0;
    if($nation['type'] == 9){
        $nationScore = 0.1;
    }

    return [
        $genScore,
        $specialScore,
        $itemScore,
        $nationScore,
    ];
}

function checkSabotageFailCondition($general, $srcCity, $destCity, $reqGold, $reqRice, $dipState):?string{
    $srcNationID = $general['nation'];
    $destNationID = $destCity['nation'];

    if(!$destCity){
        return '없는 도시입니다.';
    }
    if($general['level'] == 0){
        return '재야입니다.';
    }
    if($srcNationID != $srcCity['nation']){
        return '아국이 아닙니다.';
    }
    if(!$srcCity['supply']){
        return '고립된 도시입니다.';
    }
    if($destNationID == 0){
        return '공백지입니다.';
    }
    if($general['gold'] < $reqGold){
        return '자금이 모자랍니다.';
    }
    if($general['rice'] < $reqRice){
        return '군량이 모자랍니다.';
    }
    if($srcNationID == $destNationID){
        return '아국입니다.';
    }
    if($dipState >= 7){
        return '불가침국입니다.';
    }
    return null;
}

function calcSabotageDefendScore(string $statType, array $generalList, array $city, array $nation):array{
    $maxGenScore = 0;

    foreach($generalList as $general){
        setLeadershipBonus($general, $nation['level']);
        
        if($statType === 'leader'){
            $maxGenScore = max($maxGenScore, getGeneralLeadership($general, true, true, true));
        }
        else if($statType === 'power'){
            $maxGenScore = max($maxGenScore, getGeneralPower($general, true, true, true));
        }
        else if($statType === 'intel'){
            $maxGenScore = max($maxGenScore, getGeneralIntel($general, true, true, true));
        }
        else{
            throw new MustNotBeReachedException();
        }
    }

    $cityScore = $city['secu'] / $city['secu2'] / 5;
    $supplyScore = $city['supply'] ? 0.1 : 0;
    
    return [
        ($maxGenScore / GameConst::$sabotageProbCoefByStat), 
        $city['secu'] / $city['secu2'] / 5,
        $supplyScore
    ];
}

function process_32(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $date = substr($general['turntime'],11,5);
    $sabotageName = '화계';
    $statType = 'intel';

    [$year, $month, $develCost] = $gameStor->getValuesAsArray(['year','month','develcost']);
    $logger = new ActionLogger($general['no'], $general['nation'], $year, $month);

    $reqGold = $develCost * 5;
    $reqRice = $develCost * 5;
    
    $srcCityID = $general['city'];
    $destCityID = DecodeCommand($general['turn0'])[1];

    $dist = searchDistance($srcCityID, 5, false);
    $srcCity = $db->queryFirstRow('SELECT city,nation,supply FROM city WHERE city=%i', $srcCityID);
    $destCity = $db->queryFirstRow('SELECT city,name,nation,supply,agri,comm FROM city WHERE city=%i',$destCityID);
    $destCityName = $destCity['name']??null;

    $srcNationID = $general['nation'];
    $destNationID = $destCity['nation'];

    $dipState = $db->queryFirstField('SELECT `state` FROM diplomacy WHERE me=%i AND you=%i', $srcNationID, $destNationID);

    $failReason = checkSabotageFailCondition($general, $srcCity, $destCity, $reqGold, $reqRice, $dipState);
    if($failReason !== null){
        $logger->pushGeneralActionLog("{$failReason} {$sabotageName} 실패. <1>{$date}</>");
        return;
    }

    $srcNation = getNationStaticInfo($srcNationID);
    $destNation = getNationStaticInfo($destNationID);

    $generalList = $db->query('SELECT `no`,leader,horse,power,weap,intel,book,injury,level FROM general WHERE city=%i and nation=%i', $city['city'], $city['nation']);

    [
        $srcGenScore,
        $srcSpecialScore,
        $srcItemScore,
        $srcNationScore,
    ] = calcSabotageAttackScore($statType, $general, $srcNation);

    [
        $destGenScore,
        $destCityScore,
        $destSupplyScore
    ] = calcSabotageDefendScore($statType, $destCity, $generalList, $destNation);

    $sabotageProb = (
        GameConst::$sabotageDefaultProb 
        + ($srcGenScore + $srcSpecialScore + $srcItemScore + $srcNationScore) 
        - ($destGenScore + $destCityScore + $destSupplyScore)
    );

    // 거리보정
    $sabotageProb /= Util::array_get($dist[$destCityID], 99);

    if(!Util::randBool($sabotageProb)){
        $josaYi = JosaUtil::pick($sabotageName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 실패했습니다. <1>$date</>");

        $exp = Util::randRangeInt(1, 100);
        $exp *= getCharExpMultiplier($general['personal']);
        $ded = Util::randRangeInt(1, 70);
        $ded *= getCharDedMultiplier($general['personal']);

        $general[$statType.'2'] += 1;
        $general['gold'] -= $reqGold;
        $general['rice'] -= $reqRice;
        $db->update('general', [
            ($statType.'2') => $general[$statType.'2'],
            'resturn'=>'SUCCESS',
            'gold'=>$general['gold'],
            'rice'=>$general['rice'],
            'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
            'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
        ], 'no=%i', $general['no']);

        checkAbilityEx($general['no'], $logger);
        return;
    }

    if($srcItemScore){
        $itemName = getItemName($general['item']);
        $josaUl = JosaUtil::pick($itemName, '을');
        $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        $general['item'] = 0;
    }

    $josaYi = JosaUtil::pick($destCityName, '이');
    $logger->pushGlobalActionLog("<G><b>{$destCityName}</b></>{$josaYi} 불타고 있습니다.");
    $josaYi = JosaUtil::pick($sabotageName, '이');
    $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 성공했습니다. <1>$date</>");

    $destCity['agri'] -= Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax);
    $destCity['comm'] -= Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax);
    if($destCity['agri'] < 0) { $destCity['agri'] = 0; }
    if($destCity['comm'] < 0) { $destCity['comm'] = 0; }

    $db->update('city', [
        'state'=>32,
        'agri'=>$destCity['agri'],
        'comm'=>$destCity['comm']
    ], 'city=%i', $destCityID);

    SabotageInjury($destCityID);

    $exp = Util::randRangeInt(201, 300);
    $exp *= getCharExpMultiplier($general['personal']);
    $ded = Util::randRangeInt(141, 210);
    $ded *= getCharDedMultiplier($general['personal']);

    $general[$statType.'2'] += 1;
    $general['gold'] -= $reqGold;
    $general['rice'] -= $reqRice;
    $db->update('general', [
        'firenum' => $db->sqleval('firenum + 1'),
        ($statType.'2') => $general[$statType.'2'],
        'resturn'=>'SUCCESS',
        'gold'=>$general['gold'],
        'rice'=>$general['rice'],
        'item'=>$general['item'],
        'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
        'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
    ], 'no=%i', $general['no']);

    checkAbilityEx($general['no'], $logger);
}

function process_33(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $date = substr($general['turntime'],11,5);
    $sabotageName = '탈취';
    $statType = 'power';

    [$year, $month, $develCost] = $gameStor->getValuesAsArray(['year','month','develcost']);
    $logger = new ActionLogger($general['no'], $general['nation'], $year, $month);

    $reqGold = $develCost * 5;
    $reqRice = $develCost * 5;

    $srcCityID = $general['city'];
    $destCityID = DecodeCommand($general['turn0'])[1];

    $dist = searchDistance($srcCityID, 5, false);
    $srcCity = $db->queryFirstRow('SELECT city,nation,supply FROM city WHERE city=%i', $srcCityID);
    $destCity = $db->queryFirstRow('SELECT city,name,level,nation,secu,secu2,supply,agri,comm FROM city WHERE city=%i',$destCityID);
    $destCityName = $destCity['name']??null;

    $srcNationID = $general['nation'];
    $destNationID = $destCity['nation'];

    //탈취는 0까지 무제한

    $srcNation = getNationStaticInfo($srcNationID);
    $dipState = $db->queryFirstField('SELECT `state` FROM diplomacy WHERE me=%i AND you=%i', $srcNationID, $destNationID);

    $failReason = checkSabotageFailCondition($general, $srcCity, $destCity, $reqGold, $reqRice, $dipState);
    if($failReason !== null){
        $logger->pushGeneralActionLog("{$failReason} {$sabotageName} 실패. <1>{$date}</>");
        return;
    }

    $srcNation = getNationStaticInfo($srcNationID);
    $destNation = getNationStaticInfo($destNationID);

    $generalList = $db->query('SELECT `no`,leader,horse,power,weap,intel,book,injury,level FROM general WHERE city=%i and nation=%i', $city['city'], $city['nation']);

    [
        $srcGenScore,
        $srcSpecialScore,
        $srcItemScore,
        $srcNationScore,
    ] = calcSabotageAttackScore($statType, $general, $srcNation);

    [
        $destGenScore,
        $destCityScore,
        $destSupplyScore
    ] = calcSabotageDefendScore($statType, $destCity, $generalList, $destNation);

    $sabotageProb = (
        GameConst::$sabotageDefaultProb 
        + ($srcGenScore + $srcSpecialScore + $srcItemScore + $srcNationScore) 
        - ($destGenScore + $destCityScore + $destSupplyScore)
    );

    // 거리보정
    $sabotageProb /= Util::array_get($dist[$destCityID], 99);

    if(!Util::randBool($sabotageProb)){
        $josaYi = JosaUtil::pick($sabotageName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 실패했습니다. <1>$date</>");

        $exp = Util::randRangeInt(1, 100);
        $exp *= getCharExpMultiplier($general['personal']);
        $ded = Util::randRangeInt(1, 70);
        $ded *= getCharDedMultiplier($general['personal']);

        $general[$statType.'2'] += 1;
        $general['gold'] -= $reqGold;
        $general['rice'] -= $reqRice;
        $db->update('general', [
            ($statType.'2') => $general[$statType.'2'],
            'resturn'=>'SUCCESS',
            'gold'=>$general['gold'],
            'rice'=>$general['rice'],
            'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
            'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
        ], 'no=%i', $general['no']);

        checkAbilityEx($general['no'], $logger);
        return;
    }

    if($srcItemScore){
        $itemName = getItemName($general['item']);
        $josaUl = JosaUtil::pick($itemName, '을');
        $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        $general['item'] = 0;
    }

    $logger->pushGlobalActionLog("<G><b>{$destCityName}</b></>에서 금과 쌀을 도둑맞았습니다.");
    $josaYi = JosaUtil::pick($sabotageName, '이');
    $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 성공했습니다. <1>$date</>");

    // 탈취 최대 400 * 8
    $gold = Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax) * $destCity['level'];
    $rice = Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax) * $destCity['level'];

    if($destCity['supply']){
        [$destNationGold, $destNationRice] = $db->queryFirstList('SELECT gold,rice FROM nation WHERE nation=%i', $destCityID);

        $destNationGold -= $gold;
        $destNationRice -= $rice;

        if($destNationGold < GameConst::$minNationalGold) { 
            $gold += $destNationGold - GameConst::$minNationalGold;
            $destNationGold = GameConst::$minNationalGold;
        }
        if($destNationRice < GameConst::$minNationalRice) {
            $rice += $destNationRice - GameConst::$minNationalRice;
            $destNationRice = GameConst::$minNationalRice;
        }

        $db->update('nation', [
            'gold'=>$destNationGold,
            'rice'=>$destNationRice
        ], 'nation=%i', $destNationID);
        $db->update('city', [
            'state'=>34
        ], 'city=%i', $destCityID);
    }
    else{
        $db->update('city', [
            'comm'=>Util::valueFit($destCity['comm'] - $gold / 12, 0),
            'agri'=>Util::valueFit($destCity['agri'] - $rice / 12, 0),
            'state'=>34
        ], 'city=%i', $destCityID);
    }
    
    // 본국으로 회수, 재야이면 본인이 소유
    if($general['nation'] != 0) {
        $db->update('nation', [
            'gold' => $db->sqleval('gold + $i', $gold),
            'rice' => $db->sqleval('rice + %i', $rice)
        ], $srcNationID);
    } else {
        $general['gold'] += $gold;
        $general['rice'] += $rice;
    }

    $logger->pushGeneralActionLog("금<C>{$gold}</> 쌀<C>{$rice}</>을 획득했습니다.", ActionLogger::PLAIN);

    $exp = Util::randRangeInt(201, 300);
    $exp *= getCharExpMultiplier($general['personal']);
    $ded = Util::randRangeInt(141, 210);
    $ded *= getCharDedMultiplier($general['personal']);

    $general[$statType.'2'] += 1;
    $general['gold'] -= $reqGold;
    $general['rice'] -= $reqRice;
    $db->update('general', [
        'firenum' => $db->sqleval('firenum + 1'),
        ($statType.'2') => $general[$statType.'2'],
        'resturn'=>'SUCCESS',
        'gold'=>$general['gold'],
        'rice'=>$general['rice'],
        'item'=>$general['item'],
        'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
        'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
    ], 'no=%i', $general['no']);

    checkAbilityEx($general['no'], $logger);
}

function process_34(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $date = substr($general['turntime'],11,5);
    $sabotageName = '파괴';
    $statType = 'power';

    [$year, $month, $develCost] = $gameStor->getValuesAsArray(['year','month','develcost']);
    $logger = new ActionLogger($general['no'], $general['nation'], $year, $month);

    $reqGold = $develCost * 5;
    $reqRice = $develCost * 5;

    $srcCityID = $general['city'];
    $destCityID = DecodeCommand($general['turn0'])[1];

    $dist = searchDistance($srcCityID, 5, false);
    $srcCity = $db->queryFirstRow('SELECT city,nation,supply FROM city WHERE city=%i', $srcCityID);
    $destCity = $db->queryFirstRow('SELECT city,name,nation,def,wall,secu,secu2,supply FROM city WHERE city=%i',$destCityID);
    $destCityName = $destCity['name']??null;

    $srcNationID = $general['nation'];
    $destNationID = $destCity['nation'];

    $srcNation = getNationStaticInfo($srcNationID);
    $dipState = $db->queryFirstField('SELECT `state` FROM diplomacy WHERE me=%i AND you=%i', $srcNationID, $destNationID);

    $failReason = checkSabotageFailCondition($general, $srcCity, $destCity, $reqGold, $reqRice, $dipState);
    if($failReason !== null){
        $logger->pushGeneralActionLog("{$failReason} {$sabotageName} 실패. <1>{$date}</>");
        return;
    }    

    $srcNation = getNationStaticInfo($srcNationID);
    $destNation = getNationStaticInfo($destNationID);

    $generalList = $db->query('SELECT `no`,leader,horse,power,weap,intel,book,injury,level FROM general WHERE city=%i and nation=%i', $city['city'], $city['nation']);

    [
        $srcGenScore,
        $srcSpecialScore,
        $srcItemScore,
        $srcNationScore,
    ] = calcSabotageAttackScore($statType, $general, $srcNation);

    [
        $destGenScore,
        $destCityScore,
        $destSupplyScore
    ] = calcSabotageDefendScore($statType, $destCity, $generalList, $destNation);

    $sabotageProb = (
        GameConst::$sabotageDefaultProb 
        + ($srcGenScore + $srcSpecialScore + $srcItemScore + $srcNationScore) 
        - ($destGenScore + $destCityScore + $destSupplyScore)
    );

    // 거리보정
    $sabotageProb /= Util::array_get($dist[$destCityID], 99);

    if(!Util::randBool($sabotageProb)){
        $josaYi = JosaUtil::pick($sabotageName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 실패했습니다. <1>$date</>");

        $exp = Util::randRangeInt(1, 100);
        $exp *= getCharExpMultiplier($general['personal']);
        $ded = Util::randRangeInt(1, 70);
        $ded *= getCharDedMultiplier($general['personal']);

        $general[$statType.'2'] += 1;
        $general['gold'] -= $reqGold;
        $general['rice'] -= $reqRice;
        $db->update('general', [
            ($statType.'2') => $general[$statType.'2'],
            'resturn'=>'SUCCESS',
            'gold'=>$general['gold'],
            'rice'=>$general['rice'],
            'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
            'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
        ], 'no=%i', $general['no']);

        checkAbilityEx($general['no'], $logger);
        return;
    }

    if($srcItemScore){
        $itemName = getItemName($general['item']);
        $josaUl = JosaUtil::pick($itemName, '을');
        $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        $general['item'] = 0;
    }

    $logger->pushGlobalActionLog("누군가가 <G><b>{$destCityName}</b></>의 성벽을 허물었습니다.");
    $josaYi = JosaUtil::pick($sabotageName, '이');
    $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 성공했습니다. <1>$date</>");

    // 파괴
    $destCity['def'] -= Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax);
    $destCity['wall'] -= Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax);
    if($destCity['def'] < 100) { $destCity['def'] = 100; }
    if($destCity['wall'] < 100) { $destCity['wall'] = 100; }

    $db->update('city', [
        'state'=>32,
        'def'=>$destCity['def'],
        'wall'=>$destCity['wall']
    ], 'city=%i', $destCityID);

    SabotageInjury($destCityID);

    $exp = Util::randRangeInt(201, 300);
    $exp *= getCharExpMultiplier($general['personal']);
    $ded = Util::randRangeInt(141, 210);
    $ded *= getCharDedMultiplier($general['personal']);

    $general[$statType.'2'] += 1;
    $general['gold'] -= $reqGold;
    $general['rice'] -= $reqRice;
    $db->update('general', [
        'firenum' => $db->sqleval('firenum + 1'),
        ($statType.'2') => $general[$statType.'2'],
        'resturn'=>'SUCCESS',
        'gold'=>$general['gold'],
        'rice'=>$general['rice'],
        'item'=>$general['item'],
        'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
        'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
    ], 'no=%i', $general['no']);

    checkAbilityEx($general['no'], $logger);
}

function process_35(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $date = substr($general['turntime'],11,5);
    $sabotageName = '선동';
    $statType = 'leader';

    [$year, $month, $develCost] = $gameStor->getValuesAsArray(['year','month','develcost']);
    $logger = new ActionLogger($general['no'], $general['nation'], $year, $month);

    $reqGold = $develCost * 5;
    $reqRice = $develCost * 5;

    $srcCityID = $general['city'];
    $destCityID = DecodeCommand($general['turn0'])[1];

    $dist = searchDistance($srcCityID, 5, false);
    $srcCity = $db->queryFirstRow('SELECT city,nation,supply FROM city WHERE city=%i', $srcCityID);
    $destCity = $db->queryFirstRow('SELECT city,name,nation,rate,secu,secu2,supply FROM city WHERE city=%i',$destCityID);
    $destCityName = $destCity['name']??null;

    $srcNationID = $general['nation'];
    $destNationID = $destCity['nation'];

    $srcNation = getNationStaticInfo($srcNationID);
    $dipState = $db->queryFirstField('SELECT `state` FROM diplomacy WHERE me=%i AND you=%i', $srcNationID, $destNationID);

    $lbonus = setLeadershipBonus($general, $srcNation['level']);

    $failReason = checkSabotageFailCondition($general, $srcCity, $destCity, $reqGold, $reqRice, $dipState);
    if($failReason !== null){
        $logger->pushGeneralActionLog("{$failReason} {$sabotageName} 실패. <1>{$date}</>");
        return;
    }

    $srcNation = getNationStaticInfo($srcNationID);
    $destNation = getNationStaticInfo($destNationID);

    $generalList = $db->query('SELECT `no`,leader,horse,power,weap,intel,book,injury,level FROM general WHERE city=%i and nation=%i', $city['city'], $city['nation']);

    [
        $srcGenScore,
        $srcSpecialScore,
        $srcItemScore,
        $srcNationScore,
    ] = calcSabotageAttackScore($statType, $general, $srcNation);

    [
        $destGenScore,
        $destCityScore,
        $destSupplyScore
    ] = calcSabotageDefendScore($statType, $destCity, $generalList, $destNation);

    $sabotageProb = (
        GameConst::$sabotageDefaultProb 
        + ($srcGenScore + $srcSpecialScore + $srcItemScore + $srcNationScore) 
        - ($destGenScore + $destCityScore + $destSupplyScore)
    );

    // 거리보정
    $sabotageProb /= Util::array_get($dist[$destCityID], 99);

    if(!Util::randBool($sabotageProb)){
        $josaYi = JosaUtil::pick($sabotageName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 실패했습니다. <1>$date</>");

        $exp = Util::randRangeInt(1, 100);
        $exp *= getCharExpMultiplier($general['personal']);
        $ded = Util::randRangeInt(1, 70);
        $ded *= getCharDedMultiplier($general['personal']);

        $general[$statType.'2'] += 1;
        $general['gold'] -= $reqGold;
        $general['rice'] -= $reqRice;
        $db->update('general', [
            ($statType.'2') => $general[$statType.'2'],
            'resturn'=>'SUCCESS',
            'gold'=>$general['gold'],
            'rice'=>$general['rice'],
            'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
            'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
        ], 'no=%i', $general['no']);

        checkAbilityEx($general['no'], $logger);
        return;
    }

    if($srcItemScore){
        $itemName = getItemName($general['item']);
        $josaUl = JosaUtil::pick($itemName, '을');
        $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        $general['item'] = 0;
    }

    $logger->pushGlobalActionLog("<G><b>{$destCityName}</b></>의 백성들이 동요하고 있습니다.");
    $josaYi = JosaUtil::pick($sabotageName, '이');
    $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$sabotageName}{$josaYi} 성공했습니다. <1>$date</>");

    // 선동 최대 10
    $destCity['secu'] -= Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax);
    $destCity['rate'] -= Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax) / 50;
    if($destCity['secu'] < 0) { $destCity['secu'] = 0; }
    if($destCity['rate'] < 0) { $destCity['rate'] = 0; }
    
    $db->update('city', [
        'state'=>32,
        'secu'=>$destCity['secu'],
        'rate'=>$destCity['rate']
    ], 'city=%i', $destCityID);

    SabotageInjury($destCityID);

    $exp = Util::randRangeInt(201, 300);
    $exp *= getCharExpMultiplier($general['personal']);
    $ded = Util::randRangeInt(141, 210);
    $ded *= getCharDedMultiplier($general['personal']);

    $general[$statType.'2'] += 1;
    $general['gold'] -= $reqGold;
    $general['rice'] -= $reqRice;
    $db->update('general', [
        'firenum' => $db->sqleval('firenum + 1'),
        ($statType.'2') => $general[$statType.'2'],
        'resturn'=>'SUCCESS',
        'gold'=>$general['gold'],
        'rice'=>$general['rice'],
        'item'=>$general['item'],
        'experience'=>$db->sqleval('experience + %i', Util::round($exp)),
        'dedication'=>$db->sqleval('dedication + %i', Util::round($ded))
    ], 'no=%i', $general['no']);

    checkAbilityEx($general['no'], $logger);
}
