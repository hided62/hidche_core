<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

//로그인 검사

$isgen = Util::getReq('isgen');

$leader1 = Util::getReq('leader1', 'int', 0);
$power1 = Util::getReq('power1', 'int', 0);
$intel1 = Util::getReq('intel1', 'int', 0);
$type1 = Util::getReq('type1', 'int', 0);
$crew1 = Util::getReq('crew1', 'int', 0);
$train1 = Util::getReq('train1', 'int', 0);
$atmos1 = Util::getReq('atmos1', 'int', 0);
$level1 = Util::getReq('level1', 'int', 0);
$explevel1 = Util::getReq('explevel1', 'int', 0);
$tech1 = Util::getReq('tech1', 'int', 0);

$dex10 = Util::getReq('dex10', 'int', 0);
$dex110 = Util::getReq('dex110', 'int', 0);
$dex120 = Util::getReq('dex120', 'int', 0);
$dex130 = Util::getReq('dex130', 'int', 0);
$dex140 = Util::getReq('dex140', 'int', 0);

$dx10 = array_fill(0, 20, '');
$dx110 = array_fill(0, 20, '');
$dx120 = array_fill(0, 20, '');
$dx130 = array_fill(0, 20, '');
$dx140 = array_fill(0, 20, '');

$leader2 = Util::getReq('leader2', 'int', 0);
$power2 = Util::getReq('power2', 'int', 0);
$intel2 = Util::getReq('intel2', 'int', 0);
$type2 = Util::getReq('type2', 'int', 0);
$crew2 = Util::getReq('crew2', 'int', 0);
$train2 = Util::getReq('train2', 'int', 0);
$atmos2 = Util::getReq('atmos2', 'int', 0);
$level2 = Util::getReq('level2', 'int', 0);
$explevel2 = Util::getReq('explevel2', 'int', 0);
$tech2 = Util::getReq('tech2', 'int', 0);

$dex20 = Util::getReq('dex20', 'int', 0);
$dex210 = Util::getReq('dex210', 'int', 0);
$dex220 = Util::getReq('dex220', 'int', 0);
$dex230 = Util::getReq('dex230', 'int', 0);
$dex240 = Util::getReq('dex240', 'int', 0);

$dx20 = array_fill(0, 20, '');
$dx210 = array_fill(0, 20, '');
$dx220 = array_fill(0, 20, '');
$dx230 = array_fill(0, 20, '');
$dx240 = array_fill(0, 20, '');

$def = Util::getReq('def', 'int', 0);
$wall = Util::getReq('wall', 'int', 0);
$atmos3 = Util::getReq('atmos3', 'int', 0);
$train3 = Util::getReq('train3', 'int', 0);

$sellevel1 = array_fill(0, 13, '');
$sel1 = array_fill(0,44, '');
$tch1 = array_fill(0,11, '');

$sellevel2 = array_fill(0, 13, '');
$sel2 = array_fill(0,44, '');
$tch2 = array_fill(0,11, '');

$dec = 0;
$rice = 0;

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$connect=$db->get();
increaseRefresh("시뮬", 2);

$query = "select no,tournament,con,turntime from general where owner='{$userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$admin = $gameStor->getAll();

$con = checkLimit($me['con']);
if($con >= 2) { printLimitMsg($me['turntime']); exit(); }

if($session->userGrade < 3) {
    echo "특별회원이 아닙니다.";
    exit();
}

$mydeathnumSum = 0;
$mykillnumSum = 0;
$expSum = 0;
$ricingSum = 0;
$expSum2 = 0;
$ricingSum2 = 0;

if($isgen == "장수평균" || $isgen == "성벽평균") {
    $simulCount = 1000;
} else {
    $simulCount = 1;
}

$general = [
    'no'=>1
];
$oppose = [
    'no'=>2
];
$city = [
    'gen1'=>0,
    'gen2'=>0,
    'gen3'=>0,
];

if($isgen == "장수공격" || $isgen == "성벽공격" || $isgen == "장수평균" || $isgen == "성벽평균") {
    $msg2 = "";
    for($i=0; $i < $simulCount; $i++) {
        $general['leader'] = $leader1;
        $general['power'] = $power1;
        $general['intel'] = $intel1;
        $general['crewtype'] = $type1;
        $general['crew'] = $crew1;
        $general['train'] = $train1;
        $general['atmos'] = $atmos1;
        $general['level'] = $level1;
        $general['explevel'] = $explevel1;
        $general['dex0'] = $dex10;
        $general['dex10'] = $dex110;
        $general['dex20'] = $dex120;
        $general['dex30'] = $dex130;
        $general['dex40'] = $dex140;

        $general['injury'] = 0;
        $general['horse'] = 0;
        $general['weap'] = 0;
        $general['book'] = 0;

        $oppose['leader'] = $leader2;
        $oppose['power'] = $power2;
        $oppose['intel'] = $intel2;
        $oppose['crewtype'] = $type2;
        $oppose['crew'] = $crew2;
        $oppose['train'] = $train2;
        $oppose['atmos'] = $atmos2;
        $oppose['level'] = $level2;
        $oppose['explevel'] = $explevel2;
        $oppose['dex0'] = $dex20;
        $oppose['dex10'] = $dex210;
        $oppose['dex20'] = $dex220;
        $oppose['dex30'] = $dex230;
        $oppose['dex40'] = $dex240;

        $oppose['injury'] = 0;
        $oppose['horse'] = 0;
        $oppose['weap'] = 0;
        $oppose['book'] = 0;

        $city['def'] = $def;
        $city['wall'] = $wall;
        $city['agri'] = 0;
        $city['comm'] = 0;
        $city['secu'] = 0;

        if($isgen == "장수공격" || $isgen == "장수평균") {
            $opposecount = 1;
        } else {
            $opposecount = 0;
        }

        $warphase = getRate($admin, $general['crewtype'], "spd");   //병종간 페이즈 수 얻기

        // 우선 스케일링
        $city['def'] *= 10;
        $city['wall'] *= 10;

        $msg = "";
        $msg .= "<C>●</>1월:공격장수가 <R>공격</>합니다.<br>";

        $exp = 0;   //병사 소진 시킨 만큼
        $opexp = 0;
        $exp2 = 1;  //능력경험치
        $phase = 0;
        while($phase < $warphase) {
            // 장수가 없어서 도시 공격
            if($opposecount == 0) {
                $josaRo = JosaUtil::pick(GameUnitConst::byID($general['crewtype'])->name, '로');
                $msg .= "<C>●</>".GameUnitConst::byID($general['crewtype'])->name."{$josaRo} 성을 <M>공격</>합니다.<br>";

                $mykillnum = 0; $mydeathnum = 0;
                while($phase < $warphase) {
                    $phase++;
                    $myAtt = getAtt($general, $tech1, 0);
                    $myDef = getDef($general, $tech1);
                    $cityAtt = getCityAtt($city);
                    $cityDef = getCityDef($city);

                    // 감소할 병사 수
                    $cityCrew = GameConst::$armperphase + $myAtt - $cityDef;
                    $myCrew = GameConst::$armperphase + $cityAtt - $myDef;
                    $cityweight = $myAtt - $cityDef;
                    $myweight = $cityAtt - $myDef;

                    //훈련 사기따라
                    $myCrew = getCrew($myCrew, $atmos3, $general['train']);
                    $cityCrew = getCrew($cityCrew, $general['atmos'], $train3);
                    //숙련도 따라
                    $genDexAtt = getGenDex($general, $general['crewtype']);
                    $genDexDef = getGenDex($general, 40);
                    $cityCrew *= getDexLog($genDexAtt, ($train3-60)*7200);
                    $myCrew *= getDexLog(($atmos3-60)*7200, $genDexDef);

                    $avoid = 1;
                    // 병종간 특성
                    if(intdiv($general['crewtype'], 10) == 3) {   // 귀병
                        $int = $general['intel'] + getBookEff($general['book']);
                        if($general['crewtype'] == 30) {
                            $ratio2 = $int * 5;   // 0~500 즉 50%
                        } elseif($general['crewtype'] == 31) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 32) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 33) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 34) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 35) {
                            $ratio2 = $int * 8;   // 0~800 즉 80%
                        } elseif($general['crewtype'] == 36) {
                            $ratio2 = $int * 8;   // 0~800 즉 80%
                        } elseif($general['crewtype'] == 37) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 38) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        }
                        $ratio = rand() % 1000; // 0~999
                        if($ratio <= $ratio2) {
                            $ratio = rand() % 100; // 0~99
                            if($ratio >= 30) {
                                $type = rand() % 3;
                                switch($type) {
                                case 0:
                                    $msg .= "<C>●</><D>급습</>을 <C>성공</>했다!<br>";
                                    $cityCrew *= 1.2;
                                    break;
                                case 1:
                                    $msg .= "<C>●</><D>위보</>를 <C>성공</>했다!<br>";
                                    $cityCrew *= 1.4;
                                    break;
                                case 2:
                                    $msg .= "<C>●</><D>혼란</>을 <C>성공</>했다!<br>";
                                    $cityCrew *= 1.6;
                                    break;
                                }
                            } else {
                                $type = rand() % 3;
                                switch($type) {
                                case 0:
                                    $msg .= "<C>●</><D>급습</>을 <R>실패</>했다!<br>";
                                    $cityCrew /= 1.2;
                                    $myCrew *= 1.2;
                                    break;
                                case 1:
                                    $msg .= "<C>●</><D>위보</>를 <R>실패</>했다!<br>";
                                    $cityCrew /= 1.4;
                                    $myCrew *= 1.4;
                                    break;
                                case 2:
                                $msg .= "<C>●</><D>혼란</>을 <R>실패</>했다!<br>";
                                $cityCrew /= 1.6;
                                $myCrew *= 1.6;
                                    break;
                                }
                            }
                        }
                    } elseif($general['crewtype'] == 40) { // 정란
                        $cityCrew = $cityCrew * 1.5;
                    } elseif($general['crewtype'] == 41) { // 충차
                        $cityCrew = $cityCrew * 2.0;
                    } elseif($general['crewtype'] == 42) { // 벽력거
                        $cityCrew = $cityCrew * 1.5;
                    }
                    //군주, 참모, 장군 공격 보정 5%
                    if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) {
                        $cityCrew = $cityCrew * 1.05;
                    }
                    //레벨 보정
                    $cityCrew = $cityCrew * (100 + $general['explevel']/6)/100;

                    //크리
                    $rd = rand() % 100; // 0 ~ 99
                    $ratio = CriticalRatio3($general['leader'], $general['power'], $general['intel']);
                    if($ratio >= $rd && $avoid == 1) {
                        $msg .= "<C>●</><C>필살</>공격!</><br>";
                        $cityCrew = CriticalScore2($cityCrew);
                        $avoid = 0;
                    }
                    //회피
                    $ratio = rand() % 100; // 0 ~ 99
                    $ratio2 = getRate($admin, $general['crewtype'], "avd");   //회피율
                    if($ratio < $ratio2 && $avoid == 1) {
                        $msg .= "<C>●</><C>회피</>했다!</><br>";
                        $myCrew /= 10; // 10%만 소모
                        $avoid = 0;
                    }

                    //랜타추가
                    $cityCrew *= (rand() % 21 + 90)/100; // 90~110%
                    $myCrew *= (rand() % 21 + 90)/100; // 90~110%

                    $general['crew'] -= Util::round($myCrew);
                    $city['def'] -= Util::round($cityCrew);
                    $city['wall'] -= Util::round($cityCrew);

                    $tempMyCrew = $myCrew; $tempCityCrew = $cityCrew;
                    $tempGeneralCrew = $general['crew']; $tempCityDef = $city['def'];

                    if($city['wall'] <= 0) { $city['wall'] = 0; }

                    if($city['def'] < 0) {
                        $offset =  Util::round($tempCityDef*$tempMyCrew/$tempCityCrew);
                        $myCrew += $offset;
                        $general['crew'] -= $offset;
                        $cityCrew += $tempCityDef;
                        $city['def'] = 0;
                    }
                    if($general['crew'] < 0) {
                        $offset =  Util::round($tempGeneralCrew*$tempCityCrew/$tempMyCrew);
                        $cityCrew += $offset;
                        $city['def'] -= $offset;
                        $myCrew += $tempGeneralCrew;
                        $general['crew'] = 0;
                    }

                    $exp += $cityCrew;
                    $opexp += $myCrew;
                    $general['crew'] = Util::round($general['crew']);
                    $cityCrew = Util::round($cityCrew);
                    $myCrew =  Util::round($myCrew);
                    $myAtt = round($myAtt, 2);
                    $myDef = round($myDef, 2);
                    $cityAtt = round($cityAtt, 2);
                    $cityDef = round($cityDef, 2);
                    $msg .= "<C>●</> $phase : <Y1>【공격장수】</> <C>{$general['crew']} (-$myCrew)</> VS <C>{$city['def']} (-$cityCrew)</> <Y1>【성벽】</><br>";

                    $mykillnum += $cityCrew; $mydeathnum += $myCrew;

                    if($city['def'] <= 0) { break; }
                    if($general['crew'] <= 0) { break; }
                }

                // 도시쌀 소모 계산
                $opexp =  Util::round($opexp / 50);
                $rice = Util::round($opexp * 4 * getCrewtypeRice(0, 0) * ($train3/100 - 0.2));

                //원래대로 스케일링
                $city['def'] = Util::round($city['def'] / 10);
                $city['wall'] = Util::round($city['wall'] / 10);
                //내정 감소
                $dec =  Util::round($cityCrew / 10);
                $city['agri'] -= $dec;
                $city['comm'] -= $dec;
                $city['secu'] -= $dec;
                if($city['agri'] < 0) { $city['agri'] = 0; }
                if($city['comm'] < 0) { $city['comm'] = 0; }
                if($city['secu'] < 0) { $city['secu'] = 0; }
                $msg .= "<S>★</>병사수 변화 : <C>-$mydeathnum</> vs <C>-$mykillnum</><br>";
                $msg .= "<R>★</>【성벽】내정 감소량 : $dec 【성벽】쌀 소모 : $rice<br>";

//                $msg2 .= "<S>★</>병사수 변화 : <C>-$mydeathnum</> vs <C>-$mykillnum</>　　　";
//                $msg2 .= "<R>★</>【성벽】내정 감소량 : $dec 【성벽】쌀 소모 : $rice<br>";

                if($city['def'] == 0 || $general['crew'] == 0) {
                    break;
                }
            // 장수 대결
            } else {
                $josaUl = JosaUtil::pick(GameUnitConst::byID($oppose['crewtype'])->name, '을');
                $josaRo = JosaUtil::pick(GameUnitConst::byID($general['crewtype'])->name, '로');
                $msg .= "<C>●</>".GameUnitConst::byID($general['crewtype'])->name."{$josaRo} <Y>수비장수</>의 ".GameUnitConst::byID($oppose['crewtype'])->name."{$josaUl} 공격합니다.<br>";

                $mykillnum = 0; $mydeathnum = 0;
                while($phase < $warphase) {
                    $phase++;

                    $myAtt = getAtt($general, $tech1, 0);
                    $myDef = getDef($general, $tech1);
                    $opAtt = getAtt($oppose, $tech2, 0);
                    $opDef = getDef($oppose, $tech2);
                    // 감소할 병사 수
                    $myCrew = GameConst::$armperphase + $opAtt - $myDef;
                    $opCrew = GameConst::$armperphase + $myAtt - $opDef;
                    //훈련 사기따라
                    $myCrew = getCrew($myCrew, $oppose['atmos'], $general['train']);
                    $opCrew = getCrew($opCrew, $general['atmos'], $oppose['train']);
                    //숙련도 따라
                    $genDexAtt = getGenDex($general, $general['crewtype']);
                    $genDexDef = getGenDex($general, $oppose['crewtype']);
                    $oppDexAtt = getGenDex($oppose, $oppose['crewtype']);
                    $oppDexDef = getGenDex($oppose, $general['crewtype']);
                    $opCrew *= getDexLog($genDexAtt, $oppDexDef);
                    $myCrew *= getDexLog($oppDexAtt, $genDexDef);

                    $myAvoid = 1;
                    $opAvoid = 1;
                    // 병종간 특성
                    if(intdiv($general['crewtype'], 10) == 3) {   // 귀병
                        $int = $general['intel'] + getBookEff($general['book']);
                        if($general['crewtype'] == 30) {
                            $ratio2 = $int * 5;   // 0~500 즉 50%
                        } elseif($general['crewtype'] == 31) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 32) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 33) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 34) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 35) {
                            $ratio2 = $int * 8;   // 0~800 즉 80%
                        } elseif($general['crewtype'] == 36) {
                            $ratio2 = $int * 8;   // 0~800 즉 80%
                        } elseif($general['crewtype'] == 37) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($general['crewtype'] == 38) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        }
                        $ratio = rand() % 1000; // 0~999
                        if($ratio <= $ratio2) {
                            $ratio = rand() % 100;
                            if($ratio >= 30) {
                                $type = rand() % 5; // 0~4
                                switch($type) {
                                case 0:
                                    $msg .= "<C>●</><D>위보</>를 <C>성공</>했다!<br>";
                                    $opCrew *= 1.2;
                                    break;
                                case 1:
                                    $msg .= "<C>●</><D>매복</>을 <C>성공</>했다!<br>";
                                    $opCrew *= 1.4;
                                    break;
                                case 2:
                                    $msg .= "<C>●</><D>반목</>을 <C>성공</>했다!<br>";
                                    $opCrew *= 1.6;
                                    break;
                                case 3:
                                    $msg .= "<C>●</><D>화계</>를 <C>성공</>했다!<br>";
                                    $opCrew *= 1.8;
                                    break;
                                case 4:
                                    $msg .= "<C>●</><D>혼란</>을 <C>성공</>했다!<br>";
                                    $opCrew *= 2.0;
                                    break;
                                }
                            } else {
                                $type = rand() % 5; // 0~4
                                switch($type) {
                                case 0:
                                    $msg .= "<C>●</><D>위보</>를 <R>실패</>했다!<br>";
                                    $opCrew /= 1.1;
                                    $myCrew *= 1.1;
                                    break;
                                case 1:
                                    $msg .= "<C>●</><D>매복</>을 <R>실패</>했다!<br>";
                                    $opCrew /= 1.2;
                                    $myCrew *= 1.2;
                                    break;
                                case 2:
                                    $msg .= "<C>●</><D>반목</>을 <R>실패</>했다!<br>";
                                    $opCrew /= 1.3;
                                    $myCrew *= 1.3;
                                    break;
                                case 3:
                                    $msg .= "<C>●</><D>화계</>를 <R>실패</>했다!<br>";
                                    $opCrew /= 1.4;
                                    $myCrew *= 1.4;
                                    break;
                                case 4:
                                    $msg .= "<C>●</><D>혼란</>을 <R>실패</>했다!<br>";
                                    $opCrew /= 1.5;
                                    $myCrew *= 1.5;
                                    break;
                                }
                            }
                        }
                    }

                    // 상대 장수 병종간 특성
                    if(intdiv($oppose['crewtype'], 10) == 3) {   // 귀병
                        $int = $oppose['intel'] + getBookEff($oppose['book']);
                        if($oppose['crewtype'] == 30) {
                            $ratio2 = $int * 5;   // 0~500 즉 50%
                        } elseif($oppose['crewtype'] == 31) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($oppose['crewtype'] == 32) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($oppose['crewtype'] == 33) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($oppose['crewtype'] == 34) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($oppose['crewtype'] == 35) {
                            $ratio2 = $int * 8;   // 0~800 즉 80%
                        } elseif($oppose['crewtype'] == 36) {
                            $ratio2 = $int * 8;   // 0~800 즉 80%
                        } elseif($oppose['crewtype'] == 37) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        } elseif($oppose['crewtype'] == 38) {
                            $ratio2 = $int * 6;   // 0~600 즉 60%
                        }
                        $ratio = rand() % 1000; // 0~999
                        if($ratio <= $ratio2) {
                            $ratio = rand() % 100;
                            if($ratio >= 30) {
                                $type = rand() % 5; // 0~4
                                switch($type) {
                                case 0:
                                    $msg .= "<C>●</><D>위보</>에 당했다!<br>";
                                    $myCrew *= 1.2;
                                    break;
                                case 1:
                                    $msg .= "<C>●</><D>매복</>에 당했다!<br>";
                                    $myCrew *= 1.4;
                                    break;
                                case 2:
                                    $msg .= "<C>●</><D>반목</>에 당했다!<br>";
                                    $myCrew *= 1.6;
                                    break;
                                case 3:
                                    $msg .= "<C>●</><D>화계</>에 당했다!<br>";
                                    $myCrew *= 1.8;
                                    break;
                                case 4:
                                    $msg .= "<C>●</><D>혼란</>에 당했다!<br>";
                                    $myCrew *= 2.0;
                                    break;
                                }
                            } else {
                                $type = rand() % 5; // 0~4
                                switch($type) {
                                case 0:
                                    $msg .= "<C>●</><D>위보</>를 간파했다!<br>";
                                    $myCrew /= 1.1;
                                    $opCrew *= 1.1;
                                    break;
                                case 1:
                                    $msg .= "<C>●</><D>매복</>을 간파했다!<br>";
                                    $myCrew /= 1.2;
                                    $opCrew *= 1.2;
                                    break;
                                case 2:
                                    $msg .= "<C>●</><D>반목</>을 간파했다!<br>";
                                    $myCrew /= 1.3;
                                    $opCrew *= 1.3;
                                    break;
                                case 3:
                                    $msg .= "<C>●</><D>화계</>를 간파했다!<br>";
                                    $myCrew /= 1.4;
                                    $opCrew *= 1.4;
                                    break;
                                case 4:
                                    $msg .= "<C>●</><D>혼란</>을 간파했다!<br>";
                                    $myCrew /= 1.5;
                                    $opCrew *= 1.5;
                                    break;
                                }
                            }
                        }
                    }

                    if($oppose['crewtype'] == 43) { // 목우
                        $r = 0;
                        $r += $oppose['atmos'];
                        $r += $oppose['train'];
                        $ratio = rand() % 400;  // 최대 50% 저지
                        if($ratio < $r && $opAvoid == 1) {
                            $msg .= "<C>●</><R>저지</>당했다!</><br>";
                            $opAvoid = 0;
                            continue;
                        }
                    }

                    // my 입장 상성
                    // 보병계열 > 궁병계열
                    if(intdiv($general['crewtype'], 10) == 0 && intdiv($oppose['crewtype'], 10) == 1) {
                        $myCrew *= 0.8;
                        $opCrew *= 1.2;
                    }
                    // 궁병계열 > 기병계열
                    if(intdiv($general['crewtype'], 10) == 1 && intdiv($oppose['crewtype'], 10) == 2) {
                        $myCrew *= 0.8;
                        $opCrew *= 1.2;
                    }
                    // 기병계열 > 보병계열
                    if(intdiv($general['crewtype'], 10) == 2 && intdiv($oppose['crewtype'], 10) == 0) {
                        $myCrew *= 0.8;
                        $opCrew *= 1.2;
                    }
                    // 차병계열
                    if(intdiv($general['crewtype'], 10) == 4) {
                        $myCrew *= 1.2;
                        $opCrew *= 0.8;
                    }

                    // op 입장 상성
                    // 보병계열 > 궁병계열
                    if(intdiv($oppose['crewtype'], 10) == 0 && intdiv($general['crewtype'], 10) == 1) {
                        $opCrew *= 0.8;
                        $myCrew *= 1.2;
                    }
                    // 궁병계열 > 기병계열
                    if(intdiv($oppose['crewtype'], 10) == 1 && intdiv($general['crewtype'], 10) == 2) {
                        $opCrew *= 0.8;
                        $myCrew *= 1.2;
                    }
                    // 기병계열 > 보병계열
                    if(intdiv($oppose['crewtype'], 10) == 2 && intdiv($general['crewtype'], 10) == 0) {
                        $opCrew *= 0.8;
                        $myCrew *= 1.2;
                    }
                    // 차병계열
                    if(intdiv($oppose['crewtype'], 10) == 4) {
                        $opCrew *= 1.2;
                        $myCrew *= 0.8;
                    }

                    //군주, 참모, 장군 공격 보정 5%
                    if($general['level'] == 12 || $general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) {
                        $opCrew = $opCrew * 1.05;
                    }
                    //상대장수 관직 보정
                    //군주, 참모, 모사 방어 보정 5%
                    if($oppose['level'] == 12 || $oppose['level'] == 11 || $oppose['level'] == 9 || $oppose['level'] == 7 || $oppose['level'] == 5) {
                        $opCrew = $opCrew * 0.95;
                    } elseif($oppose['level'] == 4 && $oppose['no'] == $city['gen1']) { // 태수 보정
                        $opCrew = $opCrew * 0.95;
                    } elseif($oppose['level'] == 3 && $oppose['no'] == $city['gen2']) { // 군사 보정
                        $opCrew = $opCrew * 0.95;
                    } elseif($oppose['level'] == 2 && $oppose['no'] == $city['gen3']) { // 시중 보정
                        $opCrew = $opCrew * 0.95;
                    }

                    //레벨 보정
                    $myCrew = $myCrew * ((100 - $general['explevel']/3)/100);
                    $opCrew = $opCrew / ((100 - $general['explevel']/3)/100);
                    $myCrew = $myCrew / ((100 -  $oppose['explevel']/3)/100);
                    $opCrew = $opCrew * ((100 -  $oppose['explevel']/3)/100);

                    //크리
                    $rd = rand() % 100; // 0 ~ 99
                    $ratio = CriticalRatio3($general['leader'], $general['power'], $general['intel']);
                    if($ratio >= $rd && $myAvoid == 1) {
                        $msg .= "<C>●</><C>필살</>공격!</><br>";
                        $opCrew = CriticalScore2($opCrew);
                        $myAvoid = 0;
                    }
                    //크리
                    $rd = rand() % 100; // 0 ~ 99
                    $ratio = CriticalRatio3($oppose['leader'], $oppose['power'], $oppose['intel']);
                    if($ratio >= $rd && $opAvoid == 1) {
                        $msg .= "<C>●</>상대의 <R>필살</>공격!</><br>";
                        $myCrew = CriticalScore2($myCrew);
                        $opAvoid = 0;
                    }
                    //회피
                    $ratio = rand() % 100; // 0 ~ 99
                    $ratio2 = getRate($admin, $general['crewtype'], "avd");   //회피율
                    if($ratio < $ratio2 && $myAvoid == 1) {
                        $msg .= "<C>●</><C>회피</>했다!</><br>";
                        $myCrew /= 10; // 10%만 소모
                        $myAvoid = 0;
                    }
                    //회피
                    $ratio = rand() % 100; // 0 ~ 99
                    $ratio2 = getRate($admin, $oppose['crewtype'], "avd");   //회피율
                    if($ratio < $ratio2 && $opAvoid == 1) {
                        $msg .= "<C>●</>상대가 <R>회피</>했다!</><br>";
                        $opCrew /= 10; // 10%만 소모
                        $opAvoid = 0;
                    }

                    //랜타추가
                    $opCrew *= (rand() % 21 + 90)/100; // 90~110%
                    $myCrew *= (rand() % 21 + 90)/100; // 90~110%

                    $general['crew'] -=  Util::round($myCrew);
                    $oppose['crew'] -=  Util::round($opCrew);
                    $tempMyCrew = $myCrew; $tempOpCrew = $opCrew;
                    $tempGeneralCrew = $general['crew']; $tempOpposeCrew = $oppose['crew'];
                    if($general['crew'] <= 0 && $oppose['crew'] <= 0) {
                        $r1 = $tempGeneralCrew / $tempMyCrew;
                        $r2 = $tempOpposeCrew / $tempOpCrew;

                        if($r1 > $r2) {
                            $offset =  Util::round($tempOpposeCrew*$tempMyCrew/$tempOpCrew);
                            $myCrew += $offset;
                            $general['crew'] -= $offset;
                            $opCrew += $tempOpposeCrew;
                            $oppose['crew'] = 0;
                        } else {
                            $offset =  Util::round($tempGeneralCrew*$tempOpCrew/$tempMyCrew);
                            $opCrew += $offset;
                            $oppose['crew'] -= $offset;
                            $myCrew += $tempGeneralCrew;
                            $general['crew'] = 0;
                        }
                    } elseif($general['crew'] * $oppose['crew'] <= 0) {
                        if($oppose['crew'] < 0) {
                            $offset = Util::round($tempOpposeCrew*$tempMyCrew/$tempOpCrew);
                            $myCrew += $offset;
                            $general['crew'] -= $offset;
                            $opCrew += $tempOpposeCrew;
                            $oppose['crew'] = 0;
                        }
                        if($general['crew'] < 0) {
                            $offset = Util::round($tempGeneralCrew*$tempOpCrew/$tempMyCrew);
                            $opCrew += $offset;
                            $oppose['crew'] -= $offset;
                            $myCrew += $tempGeneralCrew;
                            $general['crew'] = 0;
                        }
                    }

                    $exp += $opCrew;
                    $opexp += $myCrew;
                    $general['crew'] = Util::round($general['crew']);
                    $oppose['crew'] = Util::round($oppose['crew']);
                    $myCrew = Util::round($myCrew);
                    $opCrew = Util::round($opCrew);
                    $myAtt = round($myAtt, 2);
                    $myDef = round($myDef, 2);
                    $opAtt = round($opAtt, 2);
                    $opDef = round($opDef, 2);
                    $msg .= "<C>●</> $phase : <Y1>【공격장수】</> <C>{$general['crew']} (-$myCrew)</> VS <C>{$oppose['crew']} (-$opCrew)</> <Y1>【수비장수】</><br>";

                    $mykillnum += $opCrew; $mydeathnum += $myCrew;

                    if($oppose['crew'] <= 0) { break; }
                    if($general['crew'] <= 0) { break; }
                }

                $msg .= "<S>★</>병사수 변화 : <C>-$mydeathnum</> vs <C>-$mykillnum</><br>";
//                $msg2 .= "<S>★</>병사수 변화 : <C>-$mydeathnum</> vs <C>-$mykillnum</>　　　";
            }
        }

        // 공헌, 명성 상승
        $exp = Util::round($exp / 50);
        $ricing = ($exp * 5 * getCrewtypeRice($general['crewtype'], $tech1));
        $msg .= "★ 【공격장수】공헌 상승 : $exp 쌀 소비 : {$exp}x5x".getCrewtypeRice($general['crewtype'], $tech1)." = $ricing<br>";
//        $msg2 .= "★ 【공격장수】공헌 상승 : $exp 쌀 소비 : {$exp}x5x".getCrewtypeRice($general['crewtype'], $tech1)." = $ricing<br>";

        $msg = ConvertLog($msg, 1);

        $mydeathnumSum += $mydeathnum;
        $mykillnumSum += $mykillnum;
        $expSum += $exp;
        $ricingSum += $ricing;
        $expSum2 += $dec;
        $ricingSum2 += $rice;
    }

    $mydeathnumSum /= $simulCount;
    $mykillnumSum /= $simulCount;
    $expSum /= $simulCount;
    $ricingSum /= $simulCount;
    $expSum2 /= $simulCount;
    $ricingSum2 /= $simulCount;
    if($isgen == "성벽평균") {
        $msg2 .= "{$simulCount}회 평균<br>";
        $msg2 .= "<S>★</>병사수 변화 : <C>-$mydeathnumSum</> vs <C>-$mykillnumSum</>　　　";
        $msg2 .= "<R>★</>【성벽】내정 감소량 : $expSum2 【성벽】쌀 소모 : $ricingSum2<br>";
        $msg2 .= "★ 【공격장수】공헌 상승 : $expSum 쌀 소비 : {$expSum}x5x".getCrewtypeRice($general['crewtype'], $tech1)." = $ricingSum<br>";
    } elseif($isgen == "장수평균") {
        $msg2 .= "{$simulCount}회 평균<br>";
        $msg2 .= "<S>★</>병사수 변화 : <C>-$mydeathnumSum</> vs <C>-$mykillnumSum</>　　　";
        $msg2 .= "★ 【공격장수】공헌 상승 : $expSum 쌀 소비 : {$expSum}x5x".getCrewtypeRice($general['crewtype'], $tech1)." = $ricingSum<br>";
    }

    $msg2 = ConvertLog($msg2, 1);
} else {
    $leader1 = 70;
    $power1 = 70;
    $intel1 = 10;
    $type1 = 0;
    $crew1 = 7000;
    $train1 = 100;
    $atmos1 = 100;
    $level1 = 1;
    $explevel1 = 20;

    $leader2 = 70;
    $power2 = 70;
    $intel2 = 10;
    $type2 = 0;
    $crew2 = 7000;
    $train2 = 100;
    $atmos2 = 100;
    $level2 = 1;
    $explevel2 = 20;

    $def = 7000;
    $wall = 7000;
    $train3 = $admin['city_rate'];
    $atmos3 = $admin['city_rate'];
}

switch($level1) {
    case 12: $sellevel1[12] = "selected"; break;
    case 11: $sellevel1[11] = "selected"; break;
    case 10: $sellevel1[10] = "selected"; break;
    case 9: $sellevel1[9] = "selected"; break;
    case 8: $sellevel1[8] = "selected"; break;
    case 7: $sellevel1[7] = "selected"; break;
    case 6: $sellevel1[6] = "selected"; break;
    case 5: $sellevel1[5] = "selected"; break;
    case 1: $sellevel1[1] = "selected"; break;
}
switch($level2) {
    case 12: $sellevel2[12] = "selected"; break;
    case 11: $sellevel2[11] = "selected"; break;
    case 10: $sellevel2[10] = "selected"; break;
    case 9: $sellevel2[9] = "selected"; break;
    case 8: $sellevel2[8] = "selected"; break;
    case 7: $sellevel2[7] = "selected"; break;
    case 6: $sellevel2[6] = "selected"; break;
    case 5: $sellevel2[5] = "selected"; break;
    case 1: $sellevel2[1] = "selected"; break;
}

switch($type1) {
    case 0: $sel1[0] = "selected"; break;
    case 1: $sel1[1] = "selected"; break;
    case 2: $sel1[2] = "selected"; break;
    case 3: $sel1[3] = "selected"; break;
    case 4: $sel1[4] = "selected"; break;
    case 5: $sel1[5] = "selected"; break;
    case 10: $sel1[10] = "selected"; break;
    case 11: $sel1[11] = "selected"; break;
    case 12: $sel1[12] = "selected"; break;
    case 13: $sel1[13] = "selected"; break;
    case 14: $sel1[14] = "selected"; break;
    case 20: $sel1[20] = "selected"; break;
    case 21: $sel1[21] = "selected"; break;
    case 22: $sel1[22] = "selected"; break;
    case 23: $sel1[23] = "selected"; break;
    case 24: $sel1[24] = "selected"; break;
    case 25: $sel1[25] = "selected"; break;
    case 26: $sel1[26] = "selected"; break;
    case 27: $sel1[27] = "selected"; break;
    case 30: $sel1[30] = "selected"; break;
    case 31: $sel1[31] = "selected"; break;
    case 32: $sel1[32] = "selected"; break;
    case 33: $sel1[33] = "selected"; break;
    case 34: $sel1[34] = "selected"; break;
    case 35: $sel1[35] = "selected"; break;
    case 36: $sel1[36] = "selected"; break;
    case 37: $sel1[37] = "selected"; break;
    case 38: $sel1[38] = "selected"; break;
    case 40: $sel1[40] = "selected"; break;
    case 41: $sel1[41] = "selected"; break;
    case 42: $sel1[42] = "selected"; break;
    case 43: $sel1[43] = "selected"; break;
}
switch($type2) {
    case 0: $sel2[0] = "selected"; break;
    case 1: $sel2[1] = "selected"; break;
    case 2: $sel2[2] = "selected"; break;
    case 3: $sel2[3] = "selected"; break;
    case 4: $sel2[4] = "selected"; break;
    case 5: $sel2[5] = "selected"; break;
    case 10: $sel2[10] = "selected"; break;
    case 11: $sel2[11] = "selected"; break;
    case 12: $sel2[12] = "selected"; break;
    case 13: $sel2[13] = "selected"; break;
    case 14: $sel2[14] = "selected"; break;
    case 20: $sel2[20] = "selected"; break;
    case 21: $sel2[21] = "selected"; break;
    case 22: $sel2[22] = "selected"; break;
    case 23: $sel2[23] = "selected"; break;
    case 24: $sel2[24] = "selected"; break;
    case 25: $sel2[25] = "selected"; break;
    case 26: $sel2[26] = "selected"; break;
    case 27: $sel2[27] = "selected"; break;
    case 30: $sel2[30] = "selected"; break;
    case 31: $sel2[31] = "selected"; break;
    case 32: $sel2[32] = "selected"; break;
    case 33: $sel2[33] = "selected"; break;
    case 34: $sel2[34] = "selected"; break;
    case 35: $sel2[35] = "selected"; break;
    case 36: $sel2[36] = "selected"; break;
    case 37: $sel2[37] = "selected"; break;
    case 38: $sel2[38] = "selected"; break;
    case 40: $sel2[40] = "selected"; break;
    case 41: $sel2[41] = "selected"; break;
    case 42: $sel2[42] = "selected"; break;
    case 43: $sel2[43] = "selected"; break;
}
switch($tech1) {
    case     0: $tch1[0]  = "selected"; break;
    case  1000: $tch1[1]  = "selected"; break;
    case  2000: $tch1[2]  = "selected"; break;
    case  3000: $tch1[3]  = "selected"; break;
    case  4000: $tch1[4]  = "selected"; break;
    case  5000: $tch1[5]  = "selected"; break;
    case  6000: $tch1[6]  = "selected"; break;
    case  7000: $tch1[7]  = "selected"; break;
    case  8000: $tch1[8]  = "selected"; break;
    case  9000: $tch1[9]  = "selected"; break;
    case 10000: $tch1[10]  = "selected"; break;
}
switch($tech2) {
    case     0: $tch2[0]  = "selected"; break;
    case  1000: $tch2[1]  = "selected"; break;
    case  2000: $tch2[2]  = "selected"; break;
    case  3000: $tch2[3]  = "selected"; break;
    case  4000: $tch2[4]  = "selected"; break;
    case  5000: $tch2[5]  = "selected"; break;
    case  6000: $tch2[6]  = "selected"; break;
    case  7000: $tch2[7]  = "selected"; break;
    case  8000: $tch2[8]  = "selected"; break;
    case  9000: $tch2[9]  = "selected"; break;
    case 10000: $tch2[10]  = "selected"; break;
}
switch($dex10) {
    case      0: $dx10[0]  = "selected"; break;
    case   2500: $dx10[1]  = "selected"; break;
    case   7500: $dx10[2]  = "selected"; break;
    case  15000: $dx10[3]  = "selected"; break;
    case  25000: $dx10[4]  = "selected"; break;
    case  37500: $dx10[5]  = "selected"; break;
    case  52500: $dx10[6]  = "selected"; break;
    case  70000: $dx10[7]  = "selected"; break;
    case  90000: $dx10[8]  = "selected"; break;
    case 112500: $dx10[9]  = "selected"; break;
    case 137500: $dx10[10] = "selected"; break;
    case 165000: $dx10[11] = "selected"; break;
    case 195000: $dx10[12] = "selected"; break;
    case 227500: $dx10[13] = "selected"; break;
    case 262500: $dx10[14] = "selected"; break;
    case 300000: $dx10[15] = "selected"; break;
    case 340000: $dx10[16] = "selected"; break;
    case 382500: $dx10[17] = "selected"; break;
    case 427500: $dx10[18] = "selected"; break;
}
switch($dex110) {
    case      0: $dx110[0]  = "selected"; break;
    case   2500: $dx110[1]  = "selected"; break;
    case   7500: $dx110[2]  = "selected"; break;
    case  15000: $dx110[3]  = "selected"; break;
    case  25000: $dx110[4]  = "selected"; break;
    case  37500: $dx110[5]  = "selected"; break;
    case  52500: $dx110[6]  = "selected"; break;
    case  70000: $dx110[7]  = "selected"; break;
    case  90000: $dx110[8]  = "selected"; break;
    case 112500: $dx110[9]  = "selected"; break;
    case 137500: $dx110[10] = "selected"; break;
    case 165000: $dx110[11] = "selected"; break;
    case 195000: $dx110[12] = "selected"; break;
    case 227500: $dx110[13] = "selected"; break;
    case 262500: $dx110[14] = "selected"; break;
    case 300000: $dx110[15] = "selected"; break;
    case 340000: $dx110[16] = "selected"; break;
    case 382500: $dx110[17] = "selected"; break;
    case 427500: $dx110[18] = "selected"; break;
}
switch($dex120) {
    case      0: $dx120[0]  = "selected"; break;
    case   2500: $dx120[1]  = "selected"; break;
    case   7500: $dx120[2]  = "selected"; break;
    case  15000: $dx120[3]  = "selected"; break;
    case  25000: $dx120[4]  = "selected"; break;
    case  37500: $dx120[5]  = "selected"; break;
    case  52500: $dx120[6]  = "selected"; break;
    case  70000: $dx120[7]  = "selected"; break;
    case  90000: $dx120[8]  = "selected"; break;
    case 112500: $dx120[9]  = "selected"; break;
    case 137500: $dx120[10] = "selected"; break;
    case 165000: $dx120[11] = "selected"; break;
    case 195000: $dx120[12] = "selected"; break;
    case 227500: $dx120[13] = "selected"; break;
    case 262500: $dx120[14] = "selected"; break;
    case 300000: $dx120[15] = "selected"; break;
    case 340000: $dx120[16] = "selected"; break;
    case 382500: $dx120[17] = "selected"; break;
    case 427500: $dx120[18] = "selected"; break;
}
switch($dex130) {
    case      0: $dx130[0]  = "selected"; break;
    case   2500: $dx130[1]  = "selected"; break;
    case   7500: $dx130[2]  = "selected"; break;
    case  15000: $dx130[3]  = "selected"; break;
    case  25000: $dx130[4]  = "selected"; break;
    case  37500: $dx130[5]  = "selected"; break;
    case  52500: $dx130[6]  = "selected"; break;
    case  70000: $dx130[7]  = "selected"; break;
    case  90000: $dx130[8]  = "selected"; break;
    case 112500: $dx130[9]  = "selected"; break;
    case 137500: $dx130[10] = "selected"; break;
    case 165000: $dx130[11] = "selected"; break;
    case 195000: $dx130[12] = "selected"; break;
    case 227500: $dx130[13] = "selected"; break;
    case 262500: $dx130[14] = "selected"; break;
    case 300000: $dx130[15] = "selected"; break;
    case 340000: $dx130[16] = "selected"; break;
    case 382500: $dx130[17] = "selected"; break;
    case 427500: $dx130[18] = "selected"; break;
}
switch($dex140) {
    case      0: $dx140[0]  = "selected"; break;
    case   2500: $dx140[1]  = "selected"; break;
    case   7500: $dx140[2]  = "selected"; break;
    case  15000: $dx140[3]  = "selected"; break;
    case  25000: $dx140[4]  = "selected"; break;
    case  37500: $dx140[5]  = "selected"; break;
    case  52500: $dx140[6]  = "selected"; break;
    case  70000: $dx140[7]  = "selected"; break;
    case  90000: $dx140[8]  = "selected"; break;
    case 112500: $dx140[9]  = "selected"; break;
    case 137500: $dx140[10] = "selected"; break;
    case 165000: $dx140[11] = "selected"; break;
    case 195000: $dx140[12] = "selected"; break;
    case 227500: $dx140[13] = "selected"; break;
    case 262500: $dx140[14] = "selected"; break;
    case 300000: $dx140[15] = "selected"; break;
    case 340000: $dx140[16] = "selected"; break;
    case 382500: $dx140[17] = "selected"; break;
    case 427500: $dx140[18] = "selected"; break;
}
switch($dex20) {
    case      0: $dx20[0]  = "selected"; break;
    case   2500: $dx20[1]  = "selected"; break;
    case   7500: $dx20[2]  = "selected"; break;
    case  15000: $dx20[3]  = "selected"; break;
    case  25000: $dx20[4]  = "selected"; break;
    case  37500: $dx20[5]  = "selected"; break;
    case  52500: $dx20[6]  = "selected"; break;
    case  70000: $dx20[7]  = "selected"; break;
    case  90000: $dx20[8]  = "selected"; break;
    case 112500: $dx20[9]  = "selected"; break;
    case 137500: $dx20[10] = "selected"; break;
    case 165000: $dx20[11] = "selected"; break;
    case 195000: $dx20[12] = "selected"; break;
    case 227500: $dx20[13] = "selected"; break;
    case 262500: $dx20[14] = "selected"; break;
    case 300000: $dx20[15] = "selected"; break;
    case 340000: $dx20[16] = "selected"; break;
    case 382500: $dx20[17] = "selected"; break;
    case 427500: $dx20[18] = "selected"; break;
}
switch($dex210) {
    case      0: $dx210[0]  = "selected"; break;
    case   2500: $dx210[1]  = "selected"; break;
    case   7500: $dx210[2]  = "selected"; break;
    case  15000: $dx210[3]  = "selected"; break;
    case  25000: $dx210[4]  = "selected"; break;
    case  37500: $dx210[5]  = "selected"; break;
    case  52500: $dx210[6]  = "selected"; break;
    case  70000: $dx210[7]  = "selected"; break;
    case  90000: $dx210[8]  = "selected"; break;
    case 112500: $dx210[9]  = "selected"; break;
    case 137500: $dx210[10] = "selected"; break;
    case 165000: $dx210[11] = "selected"; break;
    case 195000: $dx210[12] = "selected"; break;
    case 227500: $dx210[13] = "selected"; break;
    case 262500: $dx210[14] = "selected"; break;
    case 300000: $dx210[15] = "selected"; break;
    case 340000: $dx210[16] = "selected"; break;
    case 382500: $dx210[17] = "selected"; break;
    case 427500: $dx210[18] = "selected"; break;
}
switch($dex220) {
    case      0: $dx220[0]  = "selected"; break;
    case   2500: $dx220[1]  = "selected"; break;
    case   7500: $dx220[2]  = "selected"; break;
    case  15000: $dx220[3]  = "selected"; break;
    case  25000: $dx220[4]  = "selected"; break;
    case  37500: $dx220[5]  = "selected"; break;
    case  52500: $dx220[6]  = "selected"; break;
    case  70000: $dx220[7]  = "selected"; break;
    case  90000: $dx220[8]  = "selected"; break;
    case 112500: $dx220[9]  = "selected"; break;
    case 137500: $dx220[10] = "selected"; break;
    case 165000: $dx220[11] = "selected"; break;
    case 195000: $dx220[12] = "selected"; break;
    case 227500: $dx220[13] = "selected"; break;
    case 262500: $dx220[14] = "selected"; break;
    case 300000: $dx220[15] = "selected"; break;
    case 340000: $dx220[16] = "selected"; break;
    case 382500: $dx220[17] = "selected"; break;
    case 427500: $dx220[18] = "selected"; break;
}
switch($dex230) {
    case      0: $dx230[0]  = "selected"; break;
    case   2500: $dx230[1]  = "selected"; break;
    case   7500: $dx230[2]  = "selected"; break;
    case  15000: $dx230[3]  = "selected"; break;
    case  25000: $dx230[4]  = "selected"; break;
    case  37500: $dx230[5]  = "selected"; break;
    case  52500: $dx230[6]  = "selected"; break;
    case  70000: $dx230[7]  = "selected"; break;
    case  90000: $dx230[8]  = "selected"; break;
    case 112500: $dx230[9]  = "selected"; break;
    case 137500: $dx230[10] = "selected"; break;
    case 165000: $dx230[11] = "selected"; break;
    case 195000: $dx230[12] = "selected"; break;
    case 227500: $dx230[13] = "selected"; break;
    case 262500: $dx230[14] = "selected"; break;
    case 300000: $dx230[15] = "selected"; break;
    case 340000: $dx230[16] = "selected"; break;
    case 382500: $dx230[17] = "selected"; break;
    case 427500: $dx230[18] = "selected"; break;
}
switch($dex240) {
    case      0: $dx240[0]  = "selected"; break;
    case   2500: $dx240[1]  = "selected"; break;
    case   7500: $dx240[2]  = "selected"; break;
    case  15000: $dx240[3]  = "selected"; break;
    case  25000: $dx240[4]  = "selected"; break;
    case  37500: $dx240[5]  = "selected"; break;
    case  52500: $dx240[6]  = "selected"; break;
    case  70000: $dx240[7]  = "selected"; break;
    case  90000: $dx240[8]  = "selected"; break;
    case 112500: $dx240[9]  = "selected"; break;
    case 137500: $dx240[10] = "selected"; break;
    case 165000: $dx240[11] = "selected"; break;
    case 195000: $dx240[12] = "selected"; break;
    case 227500: $dx240[13] = "selected"; break;
    case 262500: $dx240[14] = "selected"; break;
    case 300000: $dx240[15] = "selected"; break;
    case 340000: $dx240[16] = "selected"; break;
    case 382500: $dx240[17] = "selected"; break;
    case 427500: $dx240[18] = "selected"; break;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>전투시뮬레이션</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<style type="text/css">
select { background-color:black;color:white; }
input { background-color:black;color:white; }
</style>

</head>

<body>
<form method=post action=_simul.php>
<table align=center width=1000 class='tb_layout bg0'>
    <tr id=bg1>
        <td>공격장수</td>
        <td>상대장수</td>
        <td>상대성벽</td>
    </tr>
    <tr>
        <td>관직
            <select name=level1 size=1>
                <option <?=$sellevel1[1]?> value=1>일반</option>
                <option <?=$sellevel1[5]?> value=5>제3모사</option>
                <option <?=$sellevel1[6]?> value=6>제3장군</option>
                <option <?=$sellevel1[7]?> value=7>제2모사</option>
                <option <?=$sellevel1[8]?> value=8>제2장군</option>
                <option <?=$sellevel1[9]?> value=9>제1모사</option>
                <option <?=$sellevel1[10]?> value=10>제1장군</option>
                <option <?=$sellevel1[11]?> value=11>참모</option>
                <option <?=$sellevel1[12]?> value=12>군주</option>
            </select>
        </td>
        <td>관직
            <select name=level2 size=1>
                <option <?=$sellevel2[1]?> value=1>일반</option>
                <option <?=$sellevel2[5]?> value=5>제3모사</option>
                <option <?=$sellevel2[6]?> value=6>제3장군</option>
                <option <?=$sellevel2[7]?> value=7>제2모사</option>
                <option <?=$sellevel2[8]?> value=8>제2장군</option>
                <option <?=$sellevel2[9]?> value=9>제1모사</option>
                <option <?=$sellevel2[10]?> value=10>제1장군</option>
                <option <?=$sellevel2[11]?> value=11>참모</option>
                <option <?=$sellevel2[12]?> value=12>군주</option>
            </select>
        </td>
        <td>-</td>
    <tr>
        <td>Lv <input size=2 maxlength=2 name=explevel1 value=<?=$explevel1?>></td>
        <td>Lv <input size=2 maxlength=2 name=explevel2 value=<?=$explevel2?>></td>
        <td>-</td>
    </tr>
    <tr>
        <td>통솔 <input size=3 maxlength=3 name=leader1 value=<?=$leader1?>></td>
        <td>통솔 <input size=3 maxlength=3 name=leader2 value=<?=$leader2?>></td>
        <td>-</td>
    </tr>
    <tr>
        <td>무력 <input size=3 maxlength=3 name=power1 value=<?=$power1?>></td>
        <td>무력 <input size=3 maxlength=3 name=power2 value=<?=$power2?>></td>
        <td>-</td>
    </tr>
    <tr>
        <td>지력 <input size=3 maxlength=3 name=intel1 value=<?=$intel1?>></td>
        <td>지력 <input size=3 maxlength=3 name=intel2 value=<?=$intel2?>></td>
        <td>-</td>
    </tr>
    <tr>
        <td>병종
            <select name=type1 size=1>
                <option value=0>--------</option>
                <option <?=$sel1[0]?> value=0>보병</option>
                <option <?=$sel1[1]?> value=1>청주병</option>
                <option <?=$sel1[2]?> value=2>수병</option>
                <option <?=$sel1[3]?> value=3>자객병</option>
                <option <?=$sel1[4]?> value=4>근위병</option>
                <option <?=$sel1[5]?> value=5>등갑병</option>
                <option value=0>--------</option>
                <option <?=$sel1[10]?> value=10>궁병</option>
                <option <?=$sel1[11]?> value=11>궁기병</option>
                <option <?=$sel1[12]?> value=12>연노병</option>
                <option <?=$sel1[13]?> value=13>강궁병</option>
                <option <?=$sel1[14]?> value=14>석궁병</option>
                <option value=0>--------</option>
                <option <?=$sel1[20]?> value=20>기병</option>
                <option <?=$sel1[21]?> value=21>백마병</option>
                <option <?=$sel1[22]?> value=22>중장기병</option>
                <option <?=$sel1[23]?> value=23>돌격기병</option>
                <option <?=$sel1[24]?> value=24>철기병</option>
                <option <?=$sel1[25]?> value=25>수렵기병</option>
                <option <?=$sel1[26]?> value=26>맹수병</option>
                <option <?=$sel1[27]?> value=27>호표기병</option>
                <option value=0>--------</option>
                <option <?=$sel1[30]?> value=30>귀병</option>
                <option <?=$sel1[31]?> value=31>신귀병</option>
                <option <?=$sel1[32]?> value=32>백귀병</option>
                <option <?=$sel1[33]?> value=33>흑귀병</option>
                <option <?=$sel1[34]?> value=34>악귀병</option>
                <option <?=$sel1[35]?> value=35>남귀병</option>
                <option <?=$sel1[36]?> value=36>황귀병</option>
                <option <?=$sel1[37]?> value=37>천귀병</option>
                <option <?=$sel1[38]?> value=38>마귀병</option>
                <option value=0>--------</option>
                <option <?=$sel1[40]?> value=40>정란</option>
                <option <?=$sel1[41]?> value=41>충차</option>
                <option <?=$sel1[42]?> value=42>벽력거</option>
                <option <?=$sel1[43]?> value=43>목우</option>
            </select>
        </td>
        <td>병종
            <select name=type2 size=1>
                <option value=0>--------</option>
                <option <?=$sel2[0]?> value=0>보병</option>
                <option <?=$sel2[1]?> value=1>청주병</option>
                <option <?=$sel2[2]?> value=2>수병</option>
                <option <?=$sel2[3]?> value=3>자객병</option>
                <option <?=$sel2[4]?> value=4>근위병</option>
                <option <?=$sel2[5]?> value=5>등갑병</option>
                <option value=0>--------</option>
                <option <?=$sel2[10]?> value=10>궁병</option>
                <option <?=$sel2[11]?> value=11>궁기병</option>
                <option <?=$sel2[12]?> value=12>연노병</option>
                <option <?=$sel2[13]?> value=13>강궁병</option>
                <option <?=$sel2[14]?> value=14>석궁병</option>
                <option value=0>--------</option>
                <option <?=$sel2[20]?> value=20>기병</option>
                <option <?=$sel2[21]?> value=21>백마병</option>
                <option <?=$sel2[22]?> value=22>중장기병</option>
                <option <?=$sel2[23]?> value=23>돌격기병</option>
                <option <?=$sel2[24]?> value=24>철기병</option>
                <option <?=$sel2[25]?> value=25>수렵기병</option>
                <option <?=$sel2[26]?> value=26>맹수병</option>
                <option <?=$sel2[27]?> value=27>호표기병</option>
                <option value=0>--------</option>
                <option <?=$sel2[30]?> value=30>귀병</option>
                <option <?=$sel2[31]?> value=31>신귀병</option>
                <option <?=$sel2[32]?> value=32>백귀병</option>
                <option <?=$sel2[33]?> value=33>흑귀병</option>
                <option <?=$sel2[34]?> value=34>악귀병</option>
                <option <?=$sel2[35]?> value=35>남귀병</option>
                <option <?=$sel2[36]?> value=36>황귀병</option>
                <option <?=$sel2[37]?> value=37>천귀병</option>
                <option <?=$sel2[38]?> value=38>마귀병</option>
                <option value=0>--------</option>
                <option <?=$sel2[40]?> value=40>정란</option>
                <option <?=$sel2[41]?> value=41>충차</option>
                <option <?=$sel2[42]?> value=42>벽력거</option>
                <option <?=$sel2[43]?> value=43>목우</option>
            </select>
        </td>
        <td>성벽 <input size=4 maxlength=4 name=wall value=<?=$wall?>></td>
    </tr>
    <tr>
        <td>병력 <input size=5 maxlength=5 name=crew1 value=<?=$crew1?>></td>
        <td>병력 <input size=5 maxlength=5 name=crew2 value=<?=$crew2?>></td>
        <td>수비 <input size=4 maxlength=4 name=def value=<?=$def?>></td>
    </tr>
    <tr>
        <td>훈련 <input size=3 maxlength=3 name=train1 value=<?=$train1?>></td>
        <td>훈련 <input size=3 maxlength=3 name=train2 value=<?=$train2?>></td>
        <td>훈련 <input size=3 maxlength=3 name=train3 value=<?=$train3?>></td>
    </tr>
    <tr>
        <td>사기 <input size=3 maxlength=3 name=atmos1 value=<?=$atmos1?>></td>
        <td>사기 <input size=3 maxlength=3 name=atmos2 value=<?=$atmos2?>></td>
        <td>사기 <input size=3 maxlength=3 name=atmos3 value=<?=$atmos3?>></td>
    </tr>
    <tr>
        <td>기술
            <select name=tech1 size=1>
                <option <?=$tch1[0]?>     value=0>0등급</option>
                <option <?=$tch1[1]?>  value=1000>1등급</option>
                <option <?=$tch1[2]?>  value=2000>2등급</option>
                <option <?=$tch1[3]?>  value=3000>3등급</option>
                <option <?=$tch1[4]?>  value=4000>4등급</option>
                <option <?=$tch1[5]?>  value=5000>5등급</option>
                <option <?=$tch1[6]?>  value=6000>6등급</option>
                <option <?=$tch1[7]?>  value=7000>7등급</option>
                <option <?=$tch1[8]?>  value=8000>8등급</option>
                <option <?=$tch1[9]?>  value=9000>9등급</option>
                <option <?=$tch1[10]?> value=10000>10등급</option>
            </select>
        </td>
        <td>기술
            <select name=tech2 size=1>
                <option <?=$tch2[0]?>     value=0>0등급</option>
                <option <?=$tch2[1]?>  value=1000>1등급</option>
                <option <?=$tch2[2]?>  value=2000>2등급</option>
                <option <?=$tch2[3]?>  value=3000>3등급</option>
                <option <?=$tch2[4]?>  value=4000>4등급</option>
                <option <?=$tch2[5]?>  value=5000>5등급</option>
                <option <?=$tch2[6]?>  value=6000>6등급</option>
                <option <?=$tch2[7]?>  value=7000>7등급</option>
                <option <?=$tch2[8]?>  value=8000>8등급</option>
                <option <?=$tch2[9]?>  value=9000>9등급</option>
                <option <?=$tch2[10]?> value=10000>10등급</option>
            </select>
        </td>
        <td>-</td>
    </tr>
    <tr>
        <td>보
            <select name=dex10 size=1>
                <option <?=$dx10[0]?>       value=0>F</option>
                <option <?=$dx10[1]?>    value=2500>E-</option>
                <option <?=$dx10[2]?>    value=7500>E</option>
                <option <?=$dx10[3]?>   value=15000>E+</option>
                <option <?=$dx10[4]?>   value=25000>D-</option>
                <option <?=$dx10[5]?>   value=37500>D</option>
                <option <?=$dx10[6]?>   value=52500>D+</option>
                <option <?=$dx10[7]?>   value=70000>C-</option>
                <option <?=$dx10[8]?>   value=90000>C</option>
                <option <?=$dx10[9]?>  value=112500>C+</option>
                <option <?=$dx10[10]?> value=137500>B-</option>
                <option <?=$dx10[11]?> value=165000>B</option>
                <option <?=$dx10[12]?> value=195000>B+</option>
                <option <?=$dx10[13]?> value=227500>A-</option>
                <option <?=$dx10[14]?> value=262500>A</option>
                <option <?=$dx10[15]?> value=300000>A+</option>
                <option <?=$dx10[16]?> value=340000>S</option>
                <option <?=$dx10[17]?> value=382500>SS</option>
                <option <?=$dx10[18]?> value=427500>SSS</option>
            </select>
            궁
            <select name=dex110 size=1>
                <option <?=$dx110[0]?>       value=0>F</option>
                <option <?=$dx110[1]?>    value=2500>E-</option>
                <option <?=$dx110[2]?>    value=7500>E</option>
                <option <?=$dx110[3]?>   value=15000>E+</option>
                <option <?=$dx110[4]?>   value=25000>D-</option>
                <option <?=$dx110[5]?>   value=37500>D</option>
                <option <?=$dx110[6]?>   value=52500>D+</option>
                <option <?=$dx110[7]?>   value=70000>C-</option>
                <option <?=$dx110[8]?>   value=90000>C</option>
                <option <?=$dx110[9]?>  value=112500>C+</option>
                <option <?=$dx110[10]?> value=137500>B-</option>
                <option <?=$dx110[11]?> value=165000>B</option>
                <option <?=$dx110[12]?> value=195000>B+</option>
                <option <?=$dx110[13]?> value=227500>A-</option>
                <option <?=$dx110[14]?> value=262500>A</option>
                <option <?=$dx110[15]?> value=300000>A+</option>
                <option <?=$dx110[16]?> value=340000>S</option>
                <option <?=$dx110[17]?> value=382500>SS</option>
                <option <?=$dx110[18]?> value=427500>SSS</option>
            </select>
            기
            <select name=dex120 size=1>
                <option <?=$dx120[0]?>       value=0>F</option>
                <option <?=$dx120[1]?>    value=2500>E-</option>
                <option <?=$dx120[2]?>    value=7500>E</option>
                <option <?=$dx120[3]?>   value=15000>E+</option>
                <option <?=$dx120[4]?>   value=25000>D-</option>
                <option <?=$dx120[5]?>   value=37500>D</option>
                <option <?=$dx120[6]?>   value=52500>D+</option>
                <option <?=$dx120[7]?>   value=70000>C-</option>
                <option <?=$dx120[8]?>   value=90000>C</option>
                <option <?=$dx120[9]?>  value=112500>C+</option>
                <option <?=$dx120[10]?> value=137500>B-</option>
                <option <?=$dx120[11]?> value=165000>B</option>
                <option <?=$dx120[12]?> value=195000>B+</option>
                <option <?=$dx120[13]?> value=227500>A-</option>
                <option <?=$dx120[14]?> value=262500>A</option>
                <option <?=$dx120[15]?> value=300000>A+</option>
                <option <?=$dx120[16]?> value=340000>S</option>
                <option <?=$dx120[17]?> value=382500>SS</option>
                <option <?=$dx120[18]?> value=427500>SSS</option>
            </select>
            귀
            <select name=dex130 size=1>
                <option <?=$dx130[0]?>       value=0>F</option>
                <option <?=$dx130[1]?>    value=2500>E-</option>
                <option <?=$dx130[2]?>    value=7500>E</option>
                <option <?=$dx130[3]?>   value=15000>E+</option>
                <option <?=$dx130[4]?>   value=25000>D-</option>
                <option <?=$dx130[5]?>   value=37500>D</option>
                <option <?=$dx130[6]?>   value=52500>D+</option>
                <option <?=$dx130[7]?>   value=70000>C-</option>
                <option <?=$dx130[8]?>   value=90000>C</option>
                <option <?=$dx130[9]?>  value=112500>C+</option>
                <option <?=$dx130[10]?> value=137500>B-</option>
                <option <?=$dx130[11]?> value=165000>B</option>
                <option <?=$dx130[12]?> value=195000>B+</option>
                <option <?=$dx130[13]?> value=227500>A-</option>
                <option <?=$dx130[14]?> value=262500>A</option>
                <option <?=$dx130[15]?> value=300000>A+</option>
                <option <?=$dx130[16]?> value=340000>S</option>
                <option <?=$dx130[17]?> value=382500>SS</option>
                <option <?=$dx130[18]?> value=427500>SSS</option>
            </select>
            차
            <select name=dex140 size=1>
                <option <?=$dx140[0]?>       value=0>F</option>
                <option <?=$dx140[1]?>    value=2500>E-</option>
                <option <?=$dx140[2]?>    value=7500>E</option>
                <option <?=$dx140[3]?>   value=15000>E+</option>
                <option <?=$dx140[4]?>   value=25000>D-</option>
                <option <?=$dx140[5]?>   value=37500>D</option>
                <option <?=$dx140[6]?>   value=52500>D+</option>
                <option <?=$dx140[7]?>   value=70000>C-</option>
                <option <?=$dx140[8]?>   value=90000>C</option>
                <option <?=$dx140[9]?>  value=112500>C+</option>
                <option <?=$dx140[10]?> value=137500>B-</option>
                <option <?=$dx140[11]?> value=165000>B</option>
                <option <?=$dx140[12]?> value=195000>B+</option>
                <option <?=$dx140[13]?> value=227500>A-</option>
                <option <?=$dx140[14]?> value=262500>A</option>
                <option <?=$dx140[15]?> value=300000>A+</option>
                <option <?=$dx140[16]?> value=340000>S</option>
                <option <?=$dx140[17]?> value=382500>SS</option>
                <option <?=$dx140[18]?> value=427500>SSS</option>
            </select>
        </td>
        <td>보
            <select name=dex20 size=1>
                <option <?=$dx20[0]?>       value=0>F</option>
                <option <?=$dx20[1]?>    value=2500>E-</option>
                <option <?=$dx20[2]?>    value=7500>E</option>
                <option <?=$dx20[3]?>   value=15000>E+</option>
                <option <?=$dx20[4]?>   value=25000>D-</option>
                <option <?=$dx20[5]?>   value=37500>D</option>
                <option <?=$dx20[6]?>   value=52500>D+</option>
                <option <?=$dx20[7]?>   value=70000>C-</option>
                <option <?=$dx20[8]?>   value=90000>C</option>
                <option <?=$dx20[9]?>  value=112500>C+</option>
                <option <?=$dx20[10]?> value=137500>B-</option>
                <option <?=$dx20[11]?> value=165000>B</option>
                <option <?=$dx20[12]?> value=195000>B+</option>
                <option <?=$dx20[13]?> value=227500>A-</option>
                <option <?=$dx20[14]?> value=262500>A</option>
                <option <?=$dx20[15]?> value=300000>A+</option>
                <option <?=$dx20[16]?> value=340000>S</option>
                <option <?=$dx20[17]?> value=382500>SS</option>
                <option <?=$dx20[18]?> value=427500>SSS</option>
            </select>
            궁
            <select name=dex210 size=1>
                <option <?=$dx210[0]?>       value=0>F</option>
                <option <?=$dx210[1]?>    value=2500>E-</option>
                <option <?=$dx210[2]?>    value=7500>E</option>
                <option <?=$dx210[3]?>   value=15000>E+</option>
                <option <?=$dx210[4]?>   value=25000>D-</option>
                <option <?=$dx210[5]?>   value=37500>D</option>
                <option <?=$dx210[6]?>   value=52500>D+</option>
                <option <?=$dx210[7]?>   value=70000>C-</option>
                <option <?=$dx210[8]?>   value=90000>C</option>
                <option <?=$dx210[9]?>  value=112500>C+</option>
                <option <?=$dx210[10]?> value=137500>B-</option>
                <option <?=$dx210[11]?> value=165000>B</option>
                <option <?=$dx210[12]?> value=195000>B+</option>
                <option <?=$dx210[13]?> value=227500>A-</option>
                <option <?=$dx210[14]?> value=262500>A</option>
                <option <?=$dx210[15]?> value=300000>A+</option>
                <option <?=$dx210[16]?> value=340000>S</option>
                <option <?=$dx210[17]?> value=382500>SS</option>
                <option <?=$dx210[18]?> value=427500>SSS</option>
            </select>
            기
            <select name=dex220 size=1>
                <option <?=$dx220[0]?>       value=0>F</option>
                <option <?=$dx220[1]?>    value=2500>E-</option>
                <option <?=$dx220[2]?>    value=7500>E</option>
                <option <?=$dx220[3]?>   value=15000>E+</option>
                <option <?=$dx220[4]?>   value=25000>D-</option>
                <option <?=$dx220[5]?>   value=37500>D</option>
                <option <?=$dx220[6]?>   value=52500>D+</option>
                <option <?=$dx220[7]?>   value=70000>C-</option>
                <option <?=$dx220[8]?>   value=90000>C</option>
                <option <?=$dx220[9]?>  value=112500>C+</option>
                <option <?=$dx220[10]?> value=137500>B-</option>
                <option <?=$dx220[11]?> value=165000>B</option>
                <option <?=$dx220[12]?> value=195000>B+</option>
                <option <?=$dx220[13]?> value=227500>A-</option>
                <option <?=$dx220[14]?> value=262500>A</option>
                <option <?=$dx220[15]?> value=300000>A+</option>
                <option <?=$dx220[16]?> value=340000>S</option>
                <option <?=$dx220[17]?> value=382500>SS</option>
                <option <?=$dx220[18]?> value=427500>SSS</option>
            </select>
            귀
            <select name=dex230 size=1>
                <option <?=$dx230[0]?>       value=0>F</option>
                <option <?=$dx230[1]?>    value=2500>E-</option>
                <option <?=$dx230[2]?>    value=7500>E</option>
                <option <?=$dx230[3]?>   value=15000>E+</option>
                <option <?=$dx230[4]?>   value=25000>D-</option>
                <option <?=$dx230[5]?>   value=37500>D</option>
                <option <?=$dx230[6]?>   value=52500>D+</option>
                <option <?=$dx230[7]?>   value=70000>C-</option>
                <option <?=$dx230[8]?>   value=90000>C</option>
                <option <?=$dx230[9]?>  value=112500>C+</option>
                <option <?=$dx230[10]?> value=137500>B-</option>
                <option <?=$dx230[11]?> value=165000>B</option>
                <option <?=$dx230[12]?> value=195000>B+</option>
                <option <?=$dx230[13]?> value=227500>A-</option>
                <option <?=$dx230[14]?> value=262500>A</option>
                <option <?=$dx230[15]?> value=300000>A+</option>
                <option <?=$dx230[16]?> value=340000>S</option>
                <option <?=$dx230[17]?> value=382500>SS</option>
                <option <?=$dx230[18]?> value=427500>SSS</option>
            </select>
            차
            <select name=dex240 size=1>
                <option <?=$dx240[0]?>       value=0>F</option>
                <option <?=$dx240[1]?>    value=2500>E-</option>
                <option <?=$dx240[2]?>    value=7500>E</option>
                <option <?=$dx240[3]?>   value=15000>E+</option>
                <option <?=$dx240[4]?>   value=25000>D-</option>
                <option <?=$dx240[5]?>   value=37500>D</option>
                <option <?=$dx240[6]?>   value=52500>D+</option>
                <option <?=$dx240[7]?>   value=70000>C-</option>
                <option <?=$dx240[8]?>   value=90000>C</option>
                <option <?=$dx240[9]?>  value=112500>C+</option>
                <option <?=$dx240[10]?> value=137500>B-</option>
                <option <?=$dx240[11]?> value=165000>B</option>
                <option <?=$dx240[12]?> value=195000>B+</option>
                <option <?=$dx240[13]?> value=227500>A-</option>
                <option <?=$dx240[14]?> value=262500>A</option>
                <option <?=$dx240[15]?> value=300000>A+</option>
                <option <?=$dx240[16]?> value=340000>S</option>
                <option <?=$dx240[17]?> value=382500>SS</option>
                <option <?=$dx240[18]?> value=427500>SSS</option>
            </select>
        </td>
        <td>-</td>
    </tr>
    <tr>
        <td>-</td>
        <td>
            <input type=submit name=isgen value=장수공격>
            <?='<input type=submit name=isgen value=장수평균>'?>
        </td>
        <td>
            <input type=submit name=isgen value=성벽공격>
            <?='<input type=submit name=isgen value=성벽평균>'?>
        </td>
    </tr>
    <tr><td colspan=3>
<?php
if($isgen == "장수공격" || $isgen == "성벽공격") {
    echo $msg;
} elseif($isgen == "장수평균" || $isgen == "성벽평균") {
    echo $msg2;
}
?>
    </td></tr>
</table>
<table align=center width=1000 class='tb_layout bg0'>
    <tr id=bg1>
        <td align=right></td>
        <td align=center>공격</td>
        <td align=center>방어</td>
        <td align=center>기동</td>
        <td align=center>회피</td>
        <td align=center>가격</td>
        <td align=center>군량</td>
        <td width=500 align=center>-</td>
    </tr>

<?php
    for($i=0; $i <= 5; $i++) {
        printSimul($admin, $i);
    }
    echo "
    <tr><td height=5 colspan=8 id=bg1></td></tr>";

    for($i=10; $i <= 14; $i++) {
        printSimul($admin, $i);
    }
    echo "
    <tr><td height=5 colspan=8 id=bg1></td></tr>";

    for($i=20; $i <= 27; $i++) {
        printSimul($admin, $i);
    }
    echo "
    <tr><td height=5 colspan=8 id=bg1></td></tr>";

    for($i=30; $i <= 38; $i++) {
        printSimul($admin, $i);
    }
    echo "
    <tr><td height=5 colspan=8 id=bg1></td></tr>";

    for($i=40; $i <= 43; $i++) {
        printSimul($admin, $i);
    }
    echo "
    <tr><td height=5 colspan=8 id=bg1></td></tr>";
?>
</table>
</form>
</body>
</html>
<?php

function printSimul($admin, $i) {
    $att = $admin["att{$i}"];
    $def = $admin["def{$i}"];
    $spd = $admin["spd{$i}"];
    $avd = $admin["avd{$i}"];
    $cst = $admin["cst{$i}"];
    $ric = $admin["ric{$i}"];
    echo "
    <tr>
        <td align=right>".GameUnitConst::byId($i)->name."</td>
        <td align=center>$att</td>
        <td align=center>$def</td>
        <td align=center>$spd</td>
        <td align=center>$avd</td>
        <td align=center>$cst</td>
        <td align=center>$ric</td>
        <td align=center>-</td>
    </tr>";
}


