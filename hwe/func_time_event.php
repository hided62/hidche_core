<?php
namespace sammo;

use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;
use Ds\Map;
/**
 * 시간 단위로 일어나는 이벤트들에 대한 함수 모음
 */


function processSumInheritPointRank(){
    $db = DB::db();

    $generals = General::createGeneralObjListFromDB(null, null, 2);

    $points = new Map();
    $points->allocate(count($generals));
    foreach($generals as $general){
        $generalID = $general->getID();
        $points[$generalID] = 0;
    }

    foreach(InheritanceKey::cases() as $key){
        if($key === InheritanceKey::previous){
            continue;
        }
        $subPoints = InheritancePointManager::getInstance()->getInheritancePointFromAll($generals, $key);
        foreach($generals as $general){
            $generalID = $general->getID();
            $points[$generalID] += $subPoints[$generalID] ?? 0;
        }
    }

    $pointsPairs = [];
    foreach($points as $generalID => $point){
        $pointsPairs[] = [
            'nation_id' => $generals[$generalID]->getNationID(),
            'general_id' => $generalID,
            'type' => RankColumn::inherit_point_earned_by_merge->value,
            'value' => $point,
        ];
    }
    //XXX: multiple batch update가 제공되지 않으므로..
    $db->delete('rank_data', '`type` = %s', RankColumn::inherit_point_earned_by_merge->value);
    $db->insert('rank_data', $pointsPairs);

    $db->query(
        'UPDATE `rank_data` D SET `value` = (SELECT SUM(`value`) FROM `rank_data` S WHERE S.general_id = D.general_id AND S.`type` IN %ls) WHERE D.`type` = %s',
        [RankColumn::inherit_point_earned_by_action->value, RankColumn::inherit_point_earned_by_merge->value],
        RankColumn::inherit_point_earned->value
    );

    $db->query(
        'UPDATE `rank_data` D SET `value` = (SELECT `value` FROM `rank_data` S WHERE S.general_id = D.general_id AND S.`type` = %s) WHERE D.`type` = %s',
        RankColumn::inherit_point_spent_dynamic->value,
        RankColumn::inherit_point_spent->value
    );
}

//1월마다 실행
function processSpring() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    // 1월엔 무조건 내정 1% 감소
    $db->update('city',[
        'dead'=>0,
        'agri'=>$db->sqleval('agri * 0.99'),
        'comm'=>$db->sqleval('comm * 0.99'),
        'secu'=>$db->sqleval('secu * 0.99'),
        'def'=>$db->sqleval('def * 0.99'),
        'wall'=>$db->sqleval('wall * 0.99'),
    ],true);

    //인구 증가
    popIncrease();

    // > 10000 유지비 3%, > 1000 유지비 1%
    // 유지비 1%
    $db->update('general', [
        'gold'=>$db->sqleval('IF(gold > 10000, gold * 0.97, gold * 0.99)')
    ], 'gold > 1000');

    // > 100000 유지비 5%, > 100000 유지비 3%, > 1000 유지비 1%
    $db->update('nation', [
        'gold'=>$db->sqleval('IF(gold > 100000, gold * 0.95, IF(gold > 10000, gold * 0.97, gold * 0.99))')
    ], 'gold > 1000');

    $admin = $gameStor->getValues(['year', 'month']);

    pushGlobalHistoryLog(["<R>★</>{$admin['year']}년 {$admin['month']}월: <S>모두들 즐거운 게임 하고 계신가요? ^^ <Y>매너 있는 플레이</> 부탁드리고, <M>지나친 훼접</>은 삼가주세요~</>"], $admin['year'], $admin['month']);
}

function processGoldIncome() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
    $adminLog = [];


    $nationList = $db->query('SELECT name,nation,capital,gold,level,rate_tmp,bill,type from nation');
    $cityListByNation = Util::arrayGroupBy($db->query('SELECT * FROM city'), 'nation');
    $generalRawListByNation = Util::arrayGroupBy($db->query('SELECT no,name,nation,gold,officer_level,dedication,city FROM general WHERE npc != 5'), 'nation');

    //국가별 처리
    foreach($nationList as $nation) {
        $nationID = $nation['nation'];

        $generalRawList = $generalRawListByNation[$nationID];
        $income = getGoldIncome($nationID, $nation['level'], $nation['rate_tmp'], $nation['capital'], $nation['type'], $cityListByNation[$nationID]??[]);
        $originoutcome = getOutcome(100, $generalRawList);
        $outcome= Util::round($nation['bill'] / 100 * $originoutcome);

        // 실제 지급량 계산
        $nation['gold'] += $income;
        // 기본량도 안될경우
        if($nation['gold'] < GameConst::$basegold) {
            $realoutcome = 0;
            // 실지급률
            $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
        } elseif($nation['gold'] - GameConst::$basegold < $outcome) {
            $realoutcome = $nation['gold'] - GameConst::$basegold;
            $nation['gold'] = GameConst::$basegold;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        } else {
            $realoutcome = $outcome;
            $nation['gold'] -= $realoutcome;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        }
        $nation['gold'] = Util::valueFit($nation['gold'], GameConst::$basegold);
        $adminLog[] = StringUtil::padStringAlignRight((string)$nation['name'],12," ")
            ." // 세금 : ".StringUtil::padStringAlignRight((string)$income,6," ")
            ." // 세출 : ".StringUtil::padStringAlignRight((string)$originoutcome,6," ")
            ." // 실제 : ".tab2((string)$realoutcome,6," ")
            ." // 지급률 : ".tab2((string)round($ratio*100,2),5," ")
            ." % // 결과금 : ".tab2((string)$nation['gold'],6," ");

        $incomeText = number_format($income);
        $incomeLog = "이번 수입은 금 <C>$incomeText</>입니다.";
        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $nationStor->prev_income_gold = $income;

        $db->update('nation', [
            'gold'=>$nation['gold']
        ], 'nation=%i', $nationID);

        // 각 장수들에게 지급
        foreach ($generalRawList as $rawGeneral) {
            $generalObj = new General($rawGeneral, null, null, null, $year, $month, false);
            $gold = Util::round(getBill($generalObj->getVar('dedication'))*$ratio);
            $generalObj->increaseVar('gold', $gold);

            $logger = $generalObj->getLogger();
            if($generalObj->getVar('officer_level') > 4){
                $logger->pushGeneralActionLog($incomeLog, $logger::PLAIN);
            }

            $goldText = number_format($gold);
            $logger->pushGeneralActionLog("봉급으로 금 <C>$goldText</>을 받았습니다.", $logger::PLAIN);
            $generalObj->applyDB($db);
        }
    }

    $logger = new ActionLogger(0, 0, $year, $month);
    $logger->pushGlobalHistoryLog('<W><b>【지급】</b></>봄이 되어 봉록에 따라 자금이 지급됩니다.');
    $logger->flush();

    pushAdminLog($adminLog);
}

function popIncrease() {
    $db = DB::db();

    $nationList = $db->queryAllLists('SELECT nation,rate_tmp,type FROM nation');

    // 인구 및 민심

    $db->update('city', [
        'trust'=>50,
        'agri'=>$db->sqleval('agri * 0.99'),
        'comm'=>$db->sqleval('comm * 0.99'),
        'secu'=>$db->sqleval('secu * 0.99'),
        'def'=>$db->sqleval('def * 0.99'),
        'wall'=>$db->sqleval('wall * 0.99'),
    ], 'nation=0');

    foreach($nationList as [$nationID, $taxRate, $nationType]){
        $nationTypeObj = buildNationTypeClass($nationType);


        $popRatio = (30 - $taxRate)/200;  // 20일때 5% 5일때 12.5% 50일때 -10%
        $popRatio = $nationTypeObj->onCalcNationalIncome('pop', $popRatio);

        $updateVar = [];
        if($popRatio >= 0){
            $updateVar['pop'] = $db->sqleval('least(pop_max, %i + pop * (1 + %d * (1 + secu / secu_max / 10)))', GameConst::$basePopIncreaseAmount, $popRatio);
        }
        else{
            $updateVar['pop'] = $db->sqleval('least(pop_max, %i + pop * (1 + %d * (1 - secu / secu_max / 10)))', GameConst::$basePopIncreaseAmount, $popRatio);
        }

        $genericRatio = (20 - $taxRate) / 200; // 20일때 0% 0일때 10% 100일때 -40%
        foreach(['agri', 'comm', 'secu', 'def', 'wall'] as $key){
            $updateVar[$key] = $db->sqleval('least(%b, %b * (1 + %d))', $key.'_max', $key, $genericRatio);
        }

        $trustDiff = 20 - $taxRate;
        $updateVar['trust'] = $db->sqleval('greatest(0, least(100, trust + %i))', $trustDiff);

        $db->update('city', $updateVar, 'nation = %i AND supply = 1', $nationID);
    }
}

function calcCityWarGoldIncome(array $rawCity, iAction $nationType):int{
    if($rawCity['supply'] == 0){
        return 0;
    }

    $warIncome = $rawCity['dead'] / 10;
    $warIncome = Util::round($nationType->onCalcNationalIncome('gold', $warIncome));
    return $warIncome;
}

function calcCityGoldIncome(array $rawCity, int $officerCnt, bool $isCapital, int $nationLevel, iAction $nationType):int{
    if($rawCity['supply'] == 0){
        return 0;
    }

    $trustRatio = $rawCity['trust'] / 200 + 0.5;//0.5 ~ 1

    $cityIncome = $rawCity['pop'] * $rawCity['comm'] / $rawCity['comm_max'] * $trustRatio / 30;
    $cityIncome *= 1 + $rawCity['secu']/$rawCity['secu_max']/10;
    $cityIncome *= pow(1.05, $officerCnt);
    if($isCapital){
        $cityIncome *= 1 + (1/3/$nationLevel);
    }
    $cityIncome = Util::round($nationType->onCalcNationalIncome('gold', $cityIncome));

    return $cityIncome;
}

function calcCityRiceIncome(array $rawCity, int $officerCnt, bool $isCapital, int $nationLevel, iAction $nationType):int{
    if($rawCity['supply'] == 0){
        return 0;
    }

    $trustRatio = $rawCity['trust'] / 200 + 0.5;//0.5 ~ 1

    $cityIncome = $rawCity['pop'] * $rawCity['agri'] / $rawCity['agri_max'] * $trustRatio / 30;
    $cityIncome *= 1 + $rawCity['secu']/$rawCity['secu_max']/10;
    $cityIncome *= pow(1.05, $officerCnt);
    if($isCapital){
        $cityIncome *= 1 + (1/3/$nationLevel);
    }
    $cityIncome = Util::round($nationType->onCalcNationalIncome('rice', $cityIncome));

    return $cityIncome;
}

function calcCityWallRiceIncome(array $rawCity, int $officerCnt, bool $isCapital, int $nationLevel, iAction $nationType):int{
    if($rawCity['supply'] == 0){
        return 0;
    }

    $wallIncome = $rawCity['def'] * $rawCity['wall'] / $rawCity['wall_max'] / 3;
    $wallIncome *= 1 + $rawCity['secu']/$rawCity['secu_max']/10;
    $wallIncome *= pow(1.05, $officerCnt);
    if($isCapital){
        $wallIncome *= 1 + 1/(3*$nationLevel);
    }

    $wallIncome = Util::round($nationType->onCalcNationalIncome('rice', $wallIncome));

    return $wallIncome;
}

function getGoldIncome(int $nationID, int $nationLevel, float $taxRate, int $capitalID, string $nationType, ?array $cityList){
    if(!$cityList){
        return 0;
    }

    $db = DB::db();

    $officersCnt = [];
    foreach($db->queryAllLists('SELECT officer_city, count(*) FROM general WHERE nation = %i AND officer_level IN (2,3,4) AND city = officer_city GROUP BY officer_city', $nationID) as [$cityID, $cnt]){
        $officersCnt[$cityID] = $cnt;
    }

    $nationTypeObj = buildNationTypeClass($nationType);

    $cityIncome = 0;
    foreach($cityList as $rawCity){
        $cityID = $rawCity['city'];
        $cityIncome += calcCityGoldIncome($rawCity, $officersCnt[$cityID]??0, $capitalID == $cityID, $nationLevel, $nationTypeObj);
    }

    $cityIncome *= ($taxRate / 20);

    return $cityIncome;
}

function processWarIncome() {
    $db = DB::db();

    $cityListByNation = Util::arrayGroupBy($db->query('SELECT * FROM city'), 'nation');

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        $nationID = $nation['nation'];
        $income = getWarGoldIncome($nation['type'], $cityListByNation[$nationID]??[]);
        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $income)
        ], 'nation=%i', $nationID);
    }

    // 10%수입, 20%부상병
    $db->update('city', [
        'pop'=>$db->sqleval('pop + dead * %d', 0.2),
        'dead'=>0
    ], true);
}

function getWarGoldIncome(string $nationType, array $cityList){
    $nationTypeObj = buildNationTypeClass($nationType);

    $cityIncome = 0;
    foreach($cityList as $rawCity){
        $cityIncome += calcCityWarGoldIncome($rawCity, $nationTypeObj);
    }

    return $cityIncome;
}



//7월마다 실행
function processFall() {
    $db = DB::db();

    // 7월엔 무조건 내정 1% 감소
    $db->update('city',[
        'dead'=>0,
        'agri'=>$db->sqleval('agri * 0.99'),
        'comm'=>$db->sqleval('comm * 0.99'),
        'secu'=>$db->sqleval('secu * 0.99'),
        'def'=>$db->sqleval('def * 0.99'),
        'wall'=>$db->sqleval('wall * 0.99'),
    ],true);

    //인구 증가
    popIncrease();

    // > 10000 유지비 3%, > 1000 유지비 1%
    // 유지비 1%
    $db->update('general', [
        'rice'=>$db->sqleval('IF(rice > 10000, rice * 0.97, rice * 0.99)')
    ], 'rice > 1000');

    // > 100000 유지비 5%, > 100000 유지비 3%, > 1000 유지비 1%
    $db->update('nation', [
        'rice'=>$db->sqleval('IF(rice > 100000, rice * 0.95, IF(rice > 10000, rice * 0.97, rice * 0.99))')
    ], 'rice > 1000');
}

function processRiceIncome() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
    $adminLog = [];

    $nationList = $db->query('SELECT name,level,nation,capital,rice,rate_tmp,bill,type from nation');
    $cityListByNation = Util::arrayGroupBy($db->query('SELECT * FROM city'), 'nation');
    $generalRawListByNation = Util::arrayGroupBy($db->query('SELECT no,name,nation,rice,officer_level,dedication,city FROM general WHERE npc != 5'), 'nation');

    //국가별 처리
    foreach($nationList as $nation) {
        $nationID = $nation['nation'];

        $generalRawList = $generalRawListByNation[$nationID];
        $income = getRiceIncome($nation['nation'], $nation['level'], $nation['rate_tmp'], $nation['capital'], $nation['type'], $cityListByNation[$nationID]??[]);
        $income += getWallIncome($nation['nation'], $nation['level'], $nation['rate_tmp'], $nation['capital'], $nation['type'], $cityListByNation[$nationID]??[]);
        $originoutcome = getOutcome(100, $generalRawList);
        $outcome= Util::round($nation['bill'] / 100 * $originoutcome);

        // 실제 지급량 계산
        $nation['rice'] += $income;
        // 기본량도 안될경우
        if($nation['rice'] < GameConst::$baserice) {
            $realoutcome = 0;
            // 실지급률
            $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
        } elseif($nation['rice'] - GameConst::$baserice < $outcome) {
            $realoutcome = $nation['rice'] - GameConst::$baserice;
            $nation['rice'] = GameConst::$baserice;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        } else {
            $realoutcome = $outcome;
            $nation['rice'] -= $realoutcome;
            // 실지급률
            $ratio = $realoutcome / $originoutcome;
        }
        $nation['rice'] = Util::valueFit($nation['rice'], GameConst::$baserice);
        $adminLog[] = StringUtil::padStringAlignRight($nation['name'],12," ")
            ." // 세곡 : ".StringUtil::padStringAlignRight((string)$income,6," ")
            ." // 세출 : ".StringUtil::padStringAlignRight((string)$originoutcome,6," ")
            ." // 실제 : ".tab2((string)$realoutcome,6," ")
            ." // 지급률 : ".tab2((string)round($ratio*100,2),5," ")
            ." % // 결과곡 : ".tab2((string)$nation['rice'],6," ");

        $incomeText = number_format($income);
        $incomeLog = "이번 수입은 쌀 <C>$incomeText</>입니다.";
        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $nationStor->prev_income_rice = $income;

        $db->update('nation', [
            'rice'=>$nation['rice']
        ], 'nation=%i', $nationID);

        // 각 장수들에게 지급
        foreach ($generalRawList as $rawGeneral) {
            $generalObj = new General($rawGeneral, null, null, null, $year, $month, false);
            $rice = Util::round(getBill($generalObj->getVar('dedication'))*$ratio);
            $generalObj->increaseVar('rice', $rice);

            $logger = $generalObj->getLogger();
            if($generalObj->getVar('officer_level') > 4){
                $logger->pushGeneralActionLog($incomeLog, $logger::PLAIN);
            }
            $riceText = number_format($rice);
            $logger->pushGeneralActionLog("봉급으로 쌀 <C>$riceText</>을 받았습니다.", $logger::PLAIN);
            $generalObj->applyDB($db);
        }
    }

    $logger = new ActionLogger(0, 0, $year, $month);
    $logger->pushGlobalHistoryLog('<W><b>【지급】</b></>가을이 되어 봉록에 따라 군량이 지급됩니다.');
    $logger->flush();

    pushAdminLog($adminLog);
}

function getRiceIncome(int $nationID, int $nationLevel, float $taxRate, int $capitalID, string $nationType, ?array $cityList) {
    if(!$cityList){
        return 0;
    }

    $db = DB::db();

    $officersCnt = [];
    foreach($db->queryAllLists('SELECT officer_city, count(*) FROM general WHERE nation = %i AND officer_level IN (2,3,4) AND city = officer_city GROUP BY officer_city', $nationID) as [$cityID, $cnt]){
        $officersCnt[$cityID] = $cnt;
    }

    $nationTypeObj = buildNationTypeClass($nationType);

    $cityIncome = 0;
    foreach($cityList as $rawCity){
        $cityID = $rawCity['city'];

        $cityIncome += calcCityRiceIncome($rawCity, $officersCnt[$cityID]??0, $capitalID == $cityID, $nationLevel, $nationTypeObj);
    }

    $cityIncome *= ($taxRate / 20);

    return $cityIncome;
}

function getWallIncome(int $nationID, int $nationLevel, float $taxRate, int $capitalID, string $nationType, ?array $cityList) {
    if(!$cityList){
        return 0;
    }

    $db = DB::db();

    $officersCnt = [];
    foreach($db->queryAllLists('SELECT officer_city, count(*) FROM general WHERE nation = %i AND officer_level IN (2,3,4) AND city = officer_city GROUP BY officer_city', $nationID) as [$cityID, $cnt]){
        $officersCnt[$cityID] = $cnt;
    }

    $nationTypeObj = buildNationTypeClass($nationType);

    $cityIncome = 0;
    foreach($cityList as $rawCity){
        $cityID = $rawCity['city'];

        $cityIncome += calcCityWallRiceIncome($rawCity, $officersCnt[$cityID]??0, $capitalID == $cityID, $nationLevel, $nationTypeObj);
    }

    $cityIncome *= ($taxRate / 20);

    return $cityIncome;
}

function getOutcome(float $billRate, array $generalList) {
    //총 지출 구함
    $outcome = 0;
    foreach($generalList as $general){
        $outcome += getBill($general['dedication']);
    }

    $outcome = Util::round($outcome * $billRate / 100);

    return $outcome;
}

function tradeRate(RandUtil $rng) {
    $db = DB::db();

    foreach($db->query('SELECT city,level FROM city') as $city){
        //시세
        $prob = [
            1=>0,
            2=>0,
            3=>0,
            4=>0.2,
            5=>0.4,
            6=>0.6,
            7=>0.8,
            8=>1
        ][$city['level']];
        if($prob > 0 && $rng->nextBool($prob)) {
            $trade = $rng->nextRangeInt(95, 105);
        } else {
            $trade = null;
        }
        $db->update('city', [
            'trade'=>$trade
        ], 'city=%i', $city['city']);
    }
}

function disaster(RandUtil $rng) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    [$startYear, $year, $month] = $gameStor->getValuesAsArray(['startyear', 'year', 'month']);

    //재난표시 초기화
    $db->update('city',[
        'state'=>0,
    ], 'state <= 10');

    // 초반 3년은 스킵
    if($startYear + 3 > $year) return;

    $boomingRate = [
        1=>0,
        4=>0.25,
        7=>0.25,
        10=>0
    ];

    $isGood = $rng->nextBool($boomingRate[$month]);


    $targetCityList = [];

    foreach($db->query('SELECT city,name,secu,secu_max FROM city') as $city){
        //호황 발생 도시 선택 ( 기본 2% )
        //재해 발생 도시 선택 ( 기본 6% )
        if($isGood){
            $raiseProp = 0.02 + ($city['secu'] / $city['secu_max']) * 0.05; // 2 ~ 7%
        }
        else {
            $raiseProp = 0.06 - ($city['secu'] / $city['secu_max']) * 0.05; // 1 ~ 6%
        }

        if($rng->nextBool($raiseProp)) {
            $targetCityList[] = $city;
        }
    }

    if(!$targetCityList){
        return;
    }

    $targetCityNames = "<G><b>".join(' ', Util::squeezeFromArray($targetCityList, 'name'))."</b></>";
    $disasterTextList = [
        1 => [
            ['<M><b>【재난】</b></>', 4, '역병이 발생하여 도시가 황폐해지고 있습니다.'],
            ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
            ['<M><b>【재난】</b></>', 3, '추위가 풀리지 않아 얼어죽는 백성들이 늘어나고 있습니다.'],
            ['<M><b>【재난】</b></>', 9, '황건적이 출현해 도시를 습격하고 있습니다.'],
        ],
        4 => [
            ['<M><b>【재난】</b></>', 7, '홍수로 인해 피해가 급증하고 있습니다.'],
            ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
            ['<M><b>【재난】</b></>', 6, '태풍으로 인해 피해가 속출하고 있습니다.'],
        ],
        7 => [
            ['<M><b>【재난】</b></>', 8, '메뚜기 떼가 발생하여 도시가 황폐해지고 있습니다.'],
            ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
            ['<M><b>【재난】</b></>', 8, '흉년이 들어 굶어죽는 백성들이 늘어나고 있습니다.'],
        ],
        10 => [
            ['<M><b>【재난】</b></>', 3, '혹한으로 도시가 황폐해지고 있습니다.'],
            ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
            ['<M><b>【재난】</b></>', 3, '눈이 많이 쌓여 도시가 황폐해지고 있습니다.'],
            ['<M><b>【재난】</b></>', 9, '황건적이 출현해 도시를 습격하고 있습니다.'],
        ]
    ];

    $boomingTextList = [
        1 => null,
        4 => [
            ['<C><b>【호황】</b></>', 2, '호황으로 도시가 번창하고 있습니다.'],
        ],
        7 => [
            ['<C><b>【풍작】</b></>', 1, '풍작으로 도시가 번창하고 있습니다.'],
        ],
        10 => null
    ];

    [$logTitle, $stateCode, $logBody] = $rng->choice(($isGood?$boomingTextList:$disasterTextList)[$month]);

    $logger = new ActionLogger(0, 0, $year, $month, false);

    $logger->pushGlobalHistoryLog("{$logTitle}{$targetCityNames}에 {$logBody}");
    $logger->flush();

    if (!$isGood) {
        $generalListByCity = Util::arrayGroupBy($db->query('SELECT no, name, nation, city, officer_level, injury, leadership, strength, intel, horse, weapon, book, item, crew, crewtype, atmos, train, special, special2 FROM general WHERE city IN %li', Util::squeezeFromArray($targetCityList, 'city')), 'city');
        //NOTE: 쿼리 1번이지만 복잡하기 vs 쿼리 여러번이지만 조금 더 깔끔하기
        foreach ($targetCityList as $city) {
            $affectRatio = Util::valueFit($city['secu'] / $city['secu_max'] / 0.8, 0, 1);
            $affectRatio = 0.8 + $affectRatio * 0.15;

            $db->update('city', [
                'state'=>$stateCode,
                'pop'=>$db->sqleval('pop * %d', $affectRatio),
                'trust'=>$db->sqleval('trust * %d', $affectRatio),
                'agri'=>$db->sqleval('agri * %d', $affectRatio),
                'comm'=>$db->sqleval('comm * %d', $affectRatio),
                'secu'=>$db->sqleval('secu * %d', $affectRatio),
                'def'=>$db->sqleval('def * %d', $affectRatio),
                'wall'=>$db->sqleval('wall * %d', $affectRatio),
            ], 'city = %i', $city['city']);

            $generalList = array_map(
                function($rawGeneral) use ($city, $year, $month){
                    return new General($rawGeneral, null, $city, null, $year, $month, false);
                },
                $generalListByCity[$city['city']]??[]
            );

            SabotageInjury($rng, $generalList, '재난');
        }
    }
    else{
        foreach ($targetCityList as $city) {
            $affectRatio = Util::valueFit($city['secu'] / $city['secu_max'] / 0.8, 0, 1);
            $affectRatio = 1.01 + $affectRatio * 0.04;

            $db->update('city', [
                'state'=>$stateCode,
                'pop'=>$db->sqleval('least(pop * %d, pop_max)', $affectRatio),
                'trust'=>$db->sqleval('least(trust * %d, 100)', $affectRatio),
                'agri'=>$db->sqleval('least(agri * %d, agri_max)', $affectRatio),
                'comm'=>$db->sqleval('least(comm * %d, comm_max)', $affectRatio),
                'secu'=>$db->sqleval('least(secu * %d, secu_max)', $affectRatio),
                'def'=>$db->sqleval('least(def * %d, def_max)', $affectRatio),
                'wall'=>$db->sqleval('least(wall * %d, wall_max)', $affectRatio),
            ], 'city = %i', $city['city']);


        }
    }

}
