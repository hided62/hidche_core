<?php
namespace sammo;


function processWar($general, $city) {
    '@phan-var array<string,int|string|null> $general';

    $db = DB::db();
    $connect=$db->get();

    $templates = new \League\Plates\Engine('templates');

    global $_maximumatmos, $_maximumtrain, $_dexLimit;
    $date = substr($general['turntime'],11,5);

    $query = "select * from game limit 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);

    $year = $game['year'];
    $month = $game['month'];

    $log = [];
    $batlog = [];
    $batres = [];

    $opplog = [];
    $oppbatlog = [];
    $oppbatres = [];

    $alllog = [];
    $history = [];

    $deadAmount = [
        'att'=>0,
        'def'=>0
    ];

    $warphase = GameUnitConst::byID($general['crewtype'])->speed;   //병종간 페이즈 수 얻기

    // 특기보정 : 돌격
    if($general['special2'] == 60) { $warphase += 1; }

    $genAtmos = 0;
    if($general['item'] == 3) {
        //탁주 사용
        $genAtmos += 3;
        $query = "update general set item=0 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
        $general['item'] = 0;
    } elseif($general['item'] >= 14 && $general['item'] <= 16) {
        //의적주, 두강주, 보령압주 사용
        $genAtmos += 5;
    } elseif($general['item'] >= 19 && $general['item'] <= 20) {
        //춘화첩, 초선화 사용
        $genAtmos += 7;
    }
    $genTrain = 0;
    if($general['item'] == 4) {
        //청주 사용
        $genTrain += 3;
        $query = "update general set item=0 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
        $general['item'] = 0;
    } elseif($general['item'] >= 12 && $general['item'] <= 13) {
        //과실주, 이강주 사용
        $genTrain += 5;
    } elseif($general['item'] >= 17 && $general['item'] <= 18) {
        //철벽서, 단결도 사용
        $genTrain += 7;
    }

    // 우선 스케일링
    $city['def'] *= 10;
    $city['wall'] *= 10;

    $query = "select level from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $originCity = MYDB_fetch_array($result);

    $query = "select nation,level,name,capital,tech,type from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,level,name,rice,capital,tech,type from nation where nation='{$city['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    //장수수 구함
    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    if($gencount < 10) $gencount = 10;
    //장수수 구함
    $query = "select no from general where nation='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destgencount = MYDB_num_rows($result);
    if($destgencount < 10) $destgencount = 10;

    $genAtmosBonus = 0;   $genTrainBonus = 0;
    $oppAtmosBonus = 0;   $oppTrainBonus = 0;
    //공격자 수도보정
    if($nation['capital'] == $general['city']) { $genAtmosBonus += 5; }
    //방어자 수도보정
    //if($destnation['capital'] == $city['city']) { $oppTrainBonus += 5; }
    //관,진,수진 보정
    if($city['level']       == 1) { $oppTrainBonus += 5; }   // 방어도시가 수진이면 방어자 방어보정
    if($originCity['level'] == 2) { $genAtmosBonus += 5; }   // 출병도시가 진이면 공격자 공격보정
    if($city['level']       == 3) { $oppTrainBonus += 5; }   // 방어도시가 관이면 방어자 방어보정

    $alllog[] = "<C>●</>{$month}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <G><b>{$city['name']}</b></>(으)로 진격합니다.";
    $log[] = "<C>●</>{$month}월:<G><b>{$city['name']}</b></>(으)로 <M>진격</>합니다. <1>$date</>";

    // 목표 도시내에 목표 국가 소속 장수 중, 병사가 있는 능력치합+병사수 순으로 훈,사 60, 80 이상
    $query = "select no,name,turntime,personal,special2,crew,crewtype,atmos,train,intel,intel2,book,power,power2,weap,injury,leader,leader2,horse,item,explevel,level,rice,leader+power+intel+weap+horse+book+crew/100 as sum,dex0,dex10,dex20,dex30,dex40 from general where city='{$city['city']}' and nation='{$city['nation']}' and nation!=0 and crew>'0' and rice>round(crew/100) and ((train>=60 and atmos>=60 and mode=1) or (train>=80 and atmos>=80 and mode=2)) order by sum desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $opposecount = MYDB_num_rows($result);

    $exp = 0;   //병사 소진 시킨 만큼
    $opexp = 0;
    $exp2 = 1;  //능력경험치
    $phase = 0;
    while($phase < $warphase) {
        // 장수가 없어서 도시 공격하려했으나 병량없을시
        if($opposecount == 0 && $destnation['nation'] > 0 && $destnation['rice'] <= 0 && $city['supply'] == 1) {
            $general['train'] += 1; //훈련 상승
            if($general['train'] > $_maximumtrain) { $general['train'] = $_maximumtrain; }
            $query = "update general set recwar='{$general['turntime']}',train='{$general['train']}',warnum=warnum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $alllog[] = "<C>●</>{$month}월:병량 부족으로 <G><b>{$city['name']}</b></>의 수비병들이 <R>패퇴</>합니다.";
            $history[] = "<C>●</>{$year}년 {$month}월:<M><b>【패퇴】</b></><D><b>{$destnation['name']}</b></>(이)가 병량 부족으로 <G><b>{$city['name']}</b></>(을)를 뺏기고 말았습니다.";
            pushGenLog($general, $log);
            pushGeneralPublicRecord($alllog, $year, $month);
            pushWorldHistory($history);
            unset($log);
            unset($alllog);
            unset($history);

            //패퇴시 병량보충
            $destnation['rice'] += 500;
            $query = "update nation set rice='{$destnation['rice']}' where nation='{$destnation['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //내정 피폐
            $query = "update city set agri=agri*0.5,comm=comm*0.5,secu=secu*0.5 where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $city = addConflict($city, $general['nation'], 1);

            ConquerCity($game, $general, $city, $nation, $destnation);
            break;
        // 장수가 없어서 도시 공격
        } elseif($opposecount == 0) {
            $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>(이)가 ".GameUnitConst::byId($general['crewtype'])->name."(으)로 성벽을 공격합니다.";
            $log[] = "<C>●</>".GameUnitConst::byId($general['crewtype'])->name."(으)로 성벽을 <M>공격</>합니다.";

            $general['train'] += 1; //훈련 상승
            if($general['train'] > $_maximumtrain) { $general['train'] = $_maximumtrain; }
            $query = "update general set recwar='{$general['turntime']}',train='{$general['train']}',warnum=warnum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $mykillnum = 0; $mydeathnum = 0;
            while($phase < $warphase) {
                $phase++;

                if($general['level'] == 12) {
                    $lbonus = $nation['level'] * 2;
                } elseif($general['level'] >= 5) {
                    $lbonus = $nation['level'];
                } else {
                    $lbonus = 0;
                }
                $myAtt = getAtt($general, $nation['tech'], $lbonus);
                $myDef = getDef($general, $nation['tech']);
                $cityAtt = getCityAtt($city);
                $cityDef = getCityDef($city);

                // 감소할 병사 수
                $cityCrew = GameConst::$armperphase + $myAtt - $cityDef;
                $myCrew = GameConst::$armperphase + $cityAtt - $myDef;
                if($cityCrew <= 0) { $cityCrew = rand() % 90 + 10; }
                if($myCrew <= 0) { $myCrew = rand() % 90 + 10; }

                //훈련 사기따라
                $myCrew = getCrew($myCrew, $game['city_rate']+$oppAtmosBonus, CharTrain($general['train']+$genTrain+$genTrainBonus, $general['personal']));
                $cityCrew = getCrew($cityCrew, CharAtmos($general['atmos']+$genAtmos+$genAtmosBonus, $general['personal']), $game['city_rate']+$oppTrainBonus);
                //숙련도 따라
                $genDexAtt = getGenDex($general, $general['crewtype']);
                $genDexDef = getGenDex($general, 40);
                $cityCrew *= getDexLog($genDexAtt, ($game['city_rate']-60)*7200);
                $myCrew *= getDexLog(($game['city_rate']-60)*7200, $genDexDef);

                $avoid = 1;
                // 병종간 특성
                if(intdiv($general['crewtype'], 10) == 3) {   // 귀병
                    $int = Util::round(getGeneralIntel($general, true, true, true, false));
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

                    // 특기보정 : 신산
                    if($general['special2'] == 41) { $ratio2 += 200; }

                    $ratio = rand() % 1000; // 0~999
                    if($ratio <= $ratio2) {
                        $ratio = rand() % 100; // 0~99
                        // 특기보정 : 귀병, 신산, 환술, 신중
                        if($general['special2'] == 40) { $ratio += 20; }
                        if($general['special2'] == 41) { $ratio += 20; }
                        if($general['special2'] == 42) { $ratio += 10; }
                        if($general['special2'] == 44) { $ratio += 100; }
                        if($ratio >= 30) {
                            // 특기보정 : 환술, 집중
                            if($general['special2'] == 42) { $cityCrew *= 1.3; }
                            if($general['special2'] == 43) { $cityCrew *= 1.5; }
                            $type = rand() % 3;
                            switch($type) {
                            case 0:
                                $batlog[] = "<C>●</><D>급습</>을 <C>성공</>했다!";
                                $cityCrew *= 1.2;
                                break;
                            case 1:
                                $batlog[] = "<C>●</><D>위보</>를 <C>성공</>했다!";
                                $cityCrew *= 1.4;
                                break;
                            case 2:
                                $batlog[] = "<C>●</><D>혼란</>을 <C>성공</>했다!";
                                $cityCrew *= 1.6;
                                break;
                            }
                        } else {
                            $type = rand() % 3;
                            switch($type) {
                            case 0:
                                $batlog[] = "<C>●</><D>급습</>을 <R>실패</>했다!";
                                $cityCrew /= 1.2;   $myCrew *= 1.2;
                                break;
                            case 1:
                                $batlog[] = "<C>●</><D>위보</>를 <R>실패</>했다!";
                                $cityCrew /= 1.4;   $myCrew *= 1.4;
                                break;
                            case 2:
                                $batlog[] = "<C>●</><D>혼란</>을 <R>실패</>했다!";
                                $cityCrew /= 1.6;   $myCrew *= 1.6;
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
                //군주 공격 보정 10%
                if($general['level'] == 12) {
                    $cityCrew = $cityCrew * 1.10;
                //참모, 장군 공격 보정 5%
                } elseif($general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) {
                    $cityCrew = $cityCrew * 1.05;
                }
                //레벨 보정
                $cityCrew = $cityCrew * (100 + $general['explevel']/6)/100;

                // 특기보정 : 공성, 기병, 돌격, 무쌍, 보병, 견고, 의술(청낭서,태평청령)
                if($general['special2'] == 53) { $cityCrew *= 2.00; }
                if($general['special2'] == 52) { $cityCrew *= 1.20; }
                if($general['special2'] == 60) { $cityCrew *= 1.10; }
                if($general['special2'] == 61) { $cityCrew *= 1.10; }
                if($general['special2'] == 50) { $myCrew *= 0.90; }
                if($general['special2'] == 62) { $myCrew *= 0.90; }
                $ratio = rand() % 100; // 0 ~ 99
                if(($general['special2'] == 73 || $general['item'] == 23 || $general['item'] == 24) && $ratio > 80 && $avoid == 1) {
                    $batlog[] = "<C>●</><C>치료</>했다!</>";
                    $myCrew /= 1.5; // 66%만 소모
                    $avoid = 0;
                }

                //크리
                $rd = rand() % 100; // 0 ~ 99
                $ratio = CriticalRatio3($general);
                // 특기보정 : 무쌍, 필살
                if($general['special2'] == 61) { $ratio += 10; }
                if($general['special2'] == 71) { $ratio += 20; }
                if($ratio >= $rd && $avoid == 1) {
                    $batlog[] = "<C>●</><C>필살</>공격!</>";
                    $cityCrew = CriticalScore2($cityCrew);
                    $avoid = 0;
                }
                //회피
                $ratio = rand() % 100; // 0 ~ 99
                $ratio2 = GameUnitConst::byID($general['crewtype'])->avoid;   //회피율
                //특기보정 : 궁병
                if($general['special2'] == 51) { $ratio2 += 20; }
                //도구 보정 : 둔갑천서, 태평요술
                if($general['item'] == 26 || $general['item'] == 25) { $ratio2 += 20; }
                if($ratio < $ratio2 && $avoid == 1) {
                    $batlog[] = "<C>●</><C>회피</>했다!</>";
                    $myCrew /= 10; // 10%만 소모
                    $avoid = 0;
                }

                //랜타추가
                $cityCrew *= (rand() % 21 + 90)/100; // 90~110%
                $myCrew *= (rand() % 21 + 90)/100; // 90~110%

                //특기보정 : 위압
                if($general['special2'] == 63 && $phase == 1 && $general['crew'] >= 1000 && $general['atmos'] >= 90 && $general['train'] >= 90) {
                    $batlog[] = "<C>●</>상대에게 <C>위압</>을 줬다!</>";
                    $myCrew = 0;
                }

                $general['crew'] -= Util::round($myCrew);
                $city['def'] -= Util::round($cityCrew);
                $city['wall'] -= Util::round($cityCrew);

                $tempMyCrew = $myCrew; $tempCityCrew = $cityCrew;
                $tempGeneralCrew = $general['crew']; $tempCityDef = $city['def'];

                if($city['wall'] <= 0) { $city['wall'] = 0; }

                if($general['crew'] <= 0 && $city['def'] <= 0) {
                    $r1 = $tempGeneralCrew / $tempMyCrew;
                    $r2 = $tempCityDef / $tempCityCrew;

                    if($r1 > $r2) {
                        $offset = Util::round($tempCityDef*$tempMyCrew/$tempCityCrew);
                        $myCrew += $offset;
                        $general['crew'] -= $offset;
                        $cityCrew += $tempCityDef;
                        $city['def'] = 0;
                    } else {
                        $offset = Util::round($tempGeneralCrew*$tempCityCrew/$tempMyCrew);
                        $cityCrew += $offset;
                        $city['def'] -= $offset;
                        $myCrew += $tempGeneralCrew;
                        $general['crew'] = 0;
                    }
                } elseif($general['crew'] * $city['def'] <= 0) {
                    if($city['def'] < 0) {
                        $offset = Util::round($tempCityDef*$tempMyCrew/$tempCityCrew);
                        $myCrew += $offset;
                        $general['crew'] -= $offset;
                        $cityCrew += $tempCityDef;
                        $city['def'] = 0;
                    }
                    if($general['crew'] < 0) {
                        $offset = Util::round($tempGeneralCrew*$tempCityCrew/$tempMyCrew);
                        $cityCrew += $offset;
                        $city['def'] -= $offset;
                        $myCrew += $tempGeneralCrew;
                        $general['crew'] = 0;
                    }
                }

                $exp += $cityCrew;
                $opexp += $myCrew;
                $general['crew'] = Util::round($general['crew']);
                $cityCrew = Util::round($cityCrew);
                $myCrew = Util::round($myCrew);
                $batlog[] = "<C>●</> $phase : <Y1>【{$general['name']}】</> <C>{$general['crew']} (-$myCrew)</> VS <C>{$city['def']} (-$cityCrew)</> <Y1>【{$city['name']}】</>";

                $mykillnum += $cityCrew; $mydeathnum += $myCrew;

                // 중간 쌀 체크
                $myRice = Util::round($exp / 50);
                // 성격 보정
                $myRice = CharExperience($myRice, $general['personal']);
                // 쌀 소모
                $myRice = ($myRice * 5 * getCrewtypeRice($general['crewtype'], $nation['tech']));
                // 결과 쌀
                $myRice = $general['rice'] - $myRice;

                if($myRice <= Util::round($general['crew']/100)) { break; }

                if($city['def'] <= 0) { break; }
                if($general['crew'] <= 0) { break; }
            }

            $render_attacker = [
                'crewtype' => mb_substr(GameUnitConst::byId($general['crewtype'])->name, 0, 2),
                'name'=> $general['name'],
                'remain_crew' => $general['crew'],
                'killed_crew' => -$mydeathnum
            ];
            $render_defender = [
                'crewtype' => '성벽',
                'name'=> $city['name'],
                'remain_crew' => $city['def'],
                'killed_crew' => -$mykillnum
            ];

            $res = str_replace(["\r\n", "\r", "\n"], '', $templates->render('small_war_log',[
                'year'=>$year,
                'month'=>$month,
                'war_type'=>'siege',
                'war_type_str'=>'성',
                'me' => $render_attacker,
                'you' => $render_defender,
            ]));

            $log[] = $res;//TODO: $log를 출력할 때 date에 대해선 숨겨야 함.
            $batlog[] = $res;
            $batres[] = $res;
            $deadAmount['att'] = $deadAmount['att'] + $mydeathnum;
            $deadAmount['def'] = $deadAmount['def'] + $mykillnum;

            // 도시쌀 소모 계산
            $opexp = Util::round($opexp / 50 * 0.8);
            $rice = Util::round($opexp * 5 * getCrewtypeRice(0, 0) * ($game['city_rate']/100 - 0.2));
            $destnation['rice'] -= $rice;
            if($destnation['rice'] < 0) { $destnation['rice'] = 0; }
            $query = "update nation set rice='{$destnation['rice']}' where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushAdminLog(["성벽 쌀 소모 : $rice"]);

            //원래대로 스케일링
            $city['def'] = Util::round($city['def'] / 10);
            $city['wall'] = Util::round($city['wall'] / 10);
            //내정 감소
            $dec = Util::round($cityCrew / 10);
            $city['agri'] -= $dec;
            $city['comm'] -= $dec;
            $city['secu'] -= $dec;
            if($city['agri'] < 0) { $city['agri'] = 0; }
            if($city['comm'] < 0) { $city['comm'] = 0; }
            if($city['secu'] < 0) { $city['secu'] = 0; }
            // 병사수 변경
            $query = "update general set crew='{$general['crew']}',killcrew=killcrew+'$mykillnum',deathcrew=deathcrew+'$mydeathnum' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 숙련도 증가
            addGenDex($general['no'], $general['crewtype'], $mykillnum);
            addGenDex($general['no'], 40, $mydeathnum);
            // 죽은수 기술로 누적
            $num = Util::round($mydeathnum * 0.01);
            // 국가보정
            if($nation['type'] == 3 || $nation['type'] == 13)                                                                   { $num *= 1.1; }
            if($nation['type'] == 5 || $nation['type'] == 6 || $nation['type'] == 7 || $nation['type'] == 8 || $nation['type'] == 12) { $num *= 0.9; }
            // 부드러운 기술 제한
            if(TechLimit($game['startyear'], $year, $nation['tech'])) { $num = intdiv($num, 4); }
            $query = "update nation set totaltech=totaltech+'$num',tech=totaltech/'$gencount' where nation='{$nation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 죽은수 기술로 누적
            $num = Util::round($mykillnum * 0.01);
            // 국가보정
            if($destnation['type'] == 3 || $destnation['type'] == 13)                                                                               { $num *= 1.1; }
            if($destnation['type'] == 5 || $destnation['type'] == 6 || $destnation['type'] == 7 || $destnation['type'] == 8 || $destnation['type'] == 12) { $num *= 0.9; }
            // 부드러운 기술 제한
            if(TechLimit($game['startyear'], $year, $destnation['tech'])) { $num = intdiv($num, 4); }
            $query = "update nation set totaltech=totaltech+'$num',tech=totaltech/'$destgencount' where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //양국 평균 기술가격
            //$techRatio = (getTechCost($nation['tech']) + getTechCost($destnation['tech'])) / 2;
            $techRatio = 1.0;
            // 죽은수 도시 재정으로 누적 60%
            $num = Util::round(($mykillnum+$mydeathnum) * 0.6 * $techRatio);
            // 국가보정
            if($destnation['type'] == 1)                            { $num *= 1.1; }
            if($destnation['type'] == 9 || $destnation['type'] == 10) { $num *= 0.9; }
            $query = "update city set dead=dead+'$num',def='{$city['def']}',wall='{$city['wall']}',agri='{$city['agri']}',comm='{$city['comm']}',secu='{$city['secu']}' where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 죽은수 도시 재정으로 누적 40%
            $num = Util::round(($mykillnum+$mydeathnum) * 0.4 * $techRatio);
            // 국가보정
            if($nation['type'] == 1)                        { $num *= 1.1; }
            if($nation['type'] == 9 || $nation['type'] == 10) { $num *= 0.9; }
            $query = "update city set dead=dead+'$num' where city='{$general['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            //분쟁현황에 추가
            $city = addConflict($city, $general['nation'], $mykillnum);

            // 병사 소진시 성 함락
            if($city['def'] <= 0) {
                $exp += 1000;
                $exp2++;

                pushGenLog($general, $log);
                pushBatLog($general, $batlog);
                pushBatRes($general, $batres);
                pushGeneralPublicRecord($alllog, $year, $month);
                pushWorldHistory($history, $year, $month);
                $log = [];
                $batlog = [];
                $batres = [];
                $alllog = [];
                $history = [];

                ConquerCity($game, $general, $city, $nation, $destnation);
                break;
            // 공격 장수 병사 소진시 실패 처리
            } elseif($general['crew'] <= 0) {
                $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(이)가 퇴각했습니다.";
                $log[] = "<C>●</>퇴각했습니다.";

                $query = "update general set deathnum=deathnum+1 where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                break;
            } elseif($myRice <= Util::round($general['crew']/100)) {
                $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(이)가 퇴각했습니다.";
                $log[] = "<C>●</>군량 부족으로 퇴각합니다.";

                $query = "update general set deathnum=deathnum+1 where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                break;
            }
        // 장수 대결
        } else {
            $oppose = MYDB_fetch_array($result);
            $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(와)과 <Y>{$oppose['name']}</>의 ".GameUnitConst::byId($oppose['crewtype'])->name."(이)가 대결합니다.";
            $log[] = "<C>●</>".GameUnitConst::byId($general['crewtype'])->name."(으)로 <Y>{$oppose['name']}</>의 ".GameUnitConst::byId($oppose['crewtype'])->name."(을)를 <M>공격</>합니다.";
            $opplog[] = "<C>●</>".GameUnitConst::byId($oppose['crewtype'])->name."(으)로 <Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(을)를 <M>수비</>합니다.";

            $oppAtmos = 0;
            if($oppose['item'] == 3) {
                //탁주 사용
                $oppAtmos += 3;
                $query = "update general set item=0 where no='{$oppose['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $opplog[] = "<C>●</><C>".getItemName($oppose['item'])."</>(을)를 사용!";
                $oppose['item'] = 0;
            } elseif($oppose['item'] >= 14 && $oppose['item'] <= 16) {
                //의적주, 두강주, 보령압주 사용
                $oppAtmos += 5;
            } elseif($oppose['item'] >= 19 && $oppose['item'] <= 20) {
                //춘화첩, 초선화 사용
                $oppAtmos += 7;
            }
            $oppTrain = 0;
            if($oppose['item'] == 4) {
                //청주 사용
                $oppTrain += 3;
                $query = "update general set item=0 where no='{$oppose['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $opplog[] = "<C>●</><C>".getItemName($oppose['item'])."</>(을)를 사용!";
                $oppose['item'] = 0;
            } elseif($oppose['item'] >= 12 && $oppose['item'] <= 13) {
                //과실주, 이강주 사용
                $oppTrain += 5;
            } elseif($oppose['item'] >= 17 && $oppose['item'] <= 18) {
                //철벽서, 단결도 사용
                $oppTrain += 7;
            }

            $general['train'] += 1; //훈련 상승
            if($general['train'] > $_maximumtrain) { $general['train'] = $_maximumtrain; }
            $oppose['train'] += 1; //훈련 상승
            if($oppose['train'] > $_maximumtrain) { $oppose['train'] = $_maximumtrain; }

            $query = "update general set recwar='{$general['turntime']}',train='{$general['train']}',warnum=warnum+1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set recwar='{$general['turntime']}',train='{$oppose['train']}',warnum=warnum+1 where no='{$oppose['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $ratio = rand() % 100;
            //특기보정 : 저격(수극), 활무기저격
            if(($general['special2'] == 70 && $ratio <= 33) || ($general['item'] == 2 && $ratio <= 20) || (($general['weap'] == 10 || $general['weap'] == 14 || $general['weap'] == 18 || $general['weap'] == 22) && $ratio <= 20)) {
                //수극 사용
                if($general['item'] == 2) {
                    $query = "update general set item=0 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $log[] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용!";
                    $general['item'] = 0;
                } elseif($general['weap'] == 10 || $general['weap'] == 14 || $general['weap'] == 18 || $general['weap'] == 22) {
//                    $log[] = "<C>●</><C>".getWeapName($general['weap'])."</>(을)를 사용!";
                }
                $log[] = "<C>●</>상대를 <C>저격</>했다!";
                $batlog[] = "<C>●</>상대를 <C>저격</>했다!";
                $opplog[] = "<C>●</>상대에게 <R>저격</>당했다!";
                $oppbatlog[] = "<C>●</>상대에게 <R>저격</>당했다!";
                // 부상
                $oppose['injury'] += rand() % 41 + 20;   // 20 ~ 60
                if($oppose['injury'] > 80) { $oppose['injury'] = 80; }
            } else if($general['item'] == 2 && $ratio <= 40) {
                $query = "update general set item=0 where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $batlog[] = "<C>●</><C>".getItemName($general['item'])."</>(이)가 빗나갑니다!";
                $general['item'] = 0;
            }
            $ratio = rand() % 100;
            //특기보정 : 저격(수극), 활무기저격
            if(($oppose['special2'] == 70 && $ratio <= 33) || ($oppose['item'] == 2 && $ratio <= 20) || (($oppose['weap'] == 10 || $oppose['weap'] == 14 || $oppose['weap'] == 18 || $oppose['weap'] == 22) && $ratio <= 20)) {
                //수극 사용
                if($oppose['item'] == 2) {
                    $query = "update general set item=0 where no='{$oppose['no']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $opplog[] = "<C>●</><C>".getItemName($oppose['item'])."</>(을)를 사용!";
                    $oppose['item'] = 0;
                } elseif($oppose['weap'] == 10 || $oppose['weap'] == 14 || $oppose['weap'] == 18 || $oppose['weap'] == 22) {
//                    $opplog[] = "<C>●</><C>".getWeapName($oppose['weap'])."</>(을)를 사용!";
                }
                $oppbatlog[] = "<C>●</>상대를 <C>저격</>했다!";
                $opplog[] = "<C>●</>상대를 <C>저격</>했다!";
                $batlog[] = "<C>●</>상대에게 <R>저격</>당했다!";
                $log[] = "<C>●</>상대에게 <R>저격</>당했다!";
                // 부상
                $general['injury'] += rand() % 41 + 20;   // 20 ~ 60
                if($general['injury'] > 80) { $general['injury'] = 80; }
            } else if($oppose['item'] == 2 && $ratio <= 40) {
                $query = "update general set item=0 where no='{$oppose['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $oppbatlog[] = "<C>●</><C>".getItemName($oppose['item'])."</>(이)가 빗나갑니다!";
                $oppose['item'] = 0;
            }

            $mykillnum = 0; $mydeathnum = 0;
            $opkillnum = 0; $opdeathnum = 0;
            while($phase < $warphase) {
                $phase++;

                if($general['level'] == 12) {
                    $lbonus = $nation['level'] * 2;
                } elseif($general['level'] >= 5) {
                    $lbonus = $nation['level'];
                } else {
                    $lbonus = 0;
                }
                $myAtt = getAtt($general, $nation['tech'], $lbonus);
                $myDef = getDef($general, $nation['tech']);

                if($oppose['level'] == 12) {
                    $opplbonus = $destnation['level'] * 2;
                } elseif($oppose['level'] >= 5) {
                    $opplbonus = $destnation['level'];
                } else {
                    $opplbonus = 0;
                }
                $opAtt = getAtt($oppose, $destnation['tech'], $opplbonus);
                $opDef = getDef($oppose, $destnation['tech']);
                // 감소할 병사 수
                $myCrew = GameConst::$armperphase + $opAtt - $myDef;
                $opCrew = GameConst::$armperphase + $myAtt - $opDef;
                if($myCrew <= 0) { $myCrew = rand() % 90 + 10; }
                if($opCrew <= 0) { $opCrew = rand() % 90 + 10; }
                //훈련 사기따라
                $myCrew = getCrew($myCrew, CharAtmos($oppose['atmos']+$oppAtmos+$oppAtmosBonus, $oppose['personal']), CharTrain($general['train']+$genTrain+$genTrainBonus, $general['personal']));
                $opCrew = getCrew($opCrew, CharAtmos($general['atmos']+$genAtmos+$genAtmosBonus, $general['personal']), CharTrain($oppose['train']+$oppTrain+$oppTrainBonus, $oppose['personal']));
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
                    $int = Util::round(getGeneralIntel($general, true, true, true, false));
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

                    // 특기보정 : 신산
                    if($general['special2'] == 41) { $ratio2 += 200; }

                    $ratio = rand() % 1000; // 0~999
                    if($ratio <= $ratio2) {
                        $ratio = rand() % 100;
                        // 특기보정 : 귀병, 신산, 환술, 신중
                        if($general['special2'] == 40) { $ratio += 20; }
                        if($general['special2'] == 41) { $ratio += 20; }
                        if($general['special2'] == 42) { $ratio += 10; }
                        if($general['special2'] == 44) { $ratio += 100; }
                        if($ratio >= 30) {
                            // 특기보정 : 환술, 집중
                            if($general['special2'] == 42) { $opCrew *= 1.3; }
                            if($general['special2'] == 43) { $opCrew *= 1.5; }
                            $type = rand() % 5; // 0~4
                            switch($type) {
                            case 0:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($oppose['special2'] == 45 && $ratio > 70) {
                                    $batlog[] = "<C>●</><D>위보</>를 <R>역으로</> 당했다!";
                                    $oppbatlog[] = "<C>●</><C>반계</>로 상대의 <D>위보</>를 되돌렸다!";
                                    $myCrew *= 1.2;
                                } else {
                                    $batlog[] = "<C>●</><D>위보</>를 <C>성공</>했다!";
                                    $oppbatlog[] = "<C>●</><D>위보</>에 당했다!";
                                    $opCrew *= 1.2;
                                }
                                break;
                            case 1:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($oppose['special2'] == 45 && $ratio > 70) {
                                    $batlog[] = "<C>●</><D>매복</>을 <R>역으로</> 당했다!";
                                    $oppbatlog[] = "<C>●</><C>반계</>로 상대의 <D>매복</>을 되돌렸다!";
                                    $myCrew *= 1.4;
                                } else {
                                    $batlog[] = "<C>●</><D>매복</>을 <C>성공</>했다!";
                                    $oppbatlog[] = "<C>●</><D>매복</>에 당했다!";
                                    $opCrew *= 1.4;
                                }
                                break;
                            case 2:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($oppose['special2'] == 45 && $ratio > 70) {
                                    $batlog[] = "<C>●</><D>반목</>을 <R>역으로</> 당했다!";
                                    $oppbatlog[] = "<C>●</><C>반계</>로 상대의 <D>반목</>을 되돌렸다!";
                                    $myCrew *= 1.6;
                                } else {
                                    $batlog[] = "<C>●</><D>반목</>을 <C>성공</>했다!";
                                    $oppbatlog[] = "<C>●</><D>반목</>에 당했다!";
                                    $opCrew *= 1.6;
                                    // 특기보정 : 반계
                                    if($general['special2'] == 45) { $opCrew *= 2; }
                                }
                                break;
                            case 3:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($oppose['special2'] == 45 && $ratio > 70) {
                                    $batlog[] = "<C>●</><D>화계</>를 <R>역으로</> 당했다!";
                                    $oppbatlog[] = "<C>●</><C>반계</>로 상대의 <D>화계</>를 되돌렸다!";
                                    $myCrew *= 1.8;
                                } else {
                                    $batlog[] = "<C>●</><D>화계</>를 <C>성공</>했다!";
                                    $oppbatlog[] = "<C>●</><D>화계</>에 당했다!";
                                    $opCrew *= 1.8;
                                }
                                break;
                            case 4:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($oppose['special2'] == 45 && $ratio > 70) {
                                    $batlog[] = "<C>●</><D>혼란</>을 <R>역으로</> 당했다!";
                                    $oppbatlog[] = "<C>●</><C>반계</>로 상대의 <D>혼란</>을 되돌렸다!";
                                    $myCrew *= 2.0;
                                } else {
                                    $batlog[] = "<C>●</><D>혼란</>을 <C>성공</>했다!";
                                    $oppbatlog[] = "<C>●</><D>혼란</>에 당했다!";
                                    $opCrew *= 2.0;
                                }
                                break;
                            }
                        } else {
                            $type = rand() % 5; // 0~4
                            switch($type) {
                            case 0:
                                $batlog[] = "<C>●</><D>위보</>를 <R>실패</>했다!";
                                $oppbatlog[] = "<C>●</><D>위보</>를 간파했다!";
                                $opCrew /= 1.1;   $myCrew *= 1.1;
                                break;
                            case 1:
                                $batlog[] = "<C>●</><D>매복</>을 <R>실패</>했다!";
                                $oppbatlog[] = "<C>●</><D>매복</>을 간파했다!";
                                $opCrew /= 1.2;   $myCrew *= 1.2;
                                break;
                            case 2:
                                $batlog[] = "<C>●</><D>반목</>을 <R>실패</>했다!";
                                $oppbatlog[] = "<C>●</><D>반목</>을 간파했다!";
                                $opCrew /= 1.3;   $myCrew *= 1.3;
                                break;
                            case 3:
                                $batlog[] = "<C>●</><D>화계</>를 <R>실패</>했다!";
                                $oppbatlog[] = "<C>●</><D>화계</>를 간파했다!";
                                $opCrew /= 1.4;   $myCrew *= 1.4;
                                break;
                            case 4:
                                $batlog[] = "<C>●</><D>혼란</>을 <R>실패</>했다!";
                                $oppbatlog[] = "<C>●</><D>혼란</>을 간파했다!";
                                $opCrew /= 1.5;   $myCrew *= 1.5;
                                break;
                            }
                        }
                    }
                }

                // 상대 장수 병종간 특성
                if(intdiv($oppose['crewtype'], 10) == 3) {   // 귀병
                    $int = Util::round(getGeneralIntel($oppose, true, true, true, false));
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

                    // 특기보정 : 신산
                    if($oppose['special2'] == 41) { $ratio2 += 200; }

                    $ratio = rand() % 1000; // 0~999
                    if($ratio <= $ratio2) {
                        $ratio = rand() % 100;
                        // 특기보정 : 귀병, 신산, 환술, 신중
                        if($oppose['special2'] == 40) { $ratio += 20; }
                        if($oppose['special2'] == 41) { $ratio += 20; }
                        if($oppose['special2'] == 42) { $ratio += 10; }
                        if($oppose['special2'] == 44) { $ratio += 100; }
                        if($ratio >= 30) {
                            // 특기보정 : 환술, 집중
                            if($oppose['special2'] == 42) { $myCrew *= 1.3; }
                            if($oppose['special2'] == 43) { $myCrew *= 1.5; }
                            $type = rand() % 5; // 0~4
                            switch($type) {
                            case 0:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($general['special2'] == 45 && $ratio > 70) {
                                    $oppbatlog[] = "<C>●</><D>위보</>를 <R>역으로</> 당했다!";
                                    $batlog[] = "<C>●</><C>반계</>로 상대의 <D>위보</>를 되돌렸다!";
                                    $opCrew *= 1.2;
                                } else {
                                    $oppbatlog[] = "<C>●</><D>위보</>를 <C>성공</>했다!";
                                    $batlog[] = "<C>●</><D>위보</>에 당했다!";
                                    $myCrew *= 1.2;
                                }
                                break;
                            case 1:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($general['special2'] == 45 && $ratio > 70) {
                                    $oppbatlog[] = "<C>●</><D>매복</>을 <R>역으로</> 당했다!";
                                    $batlog[] = "<C>●</><C>반계</>로 상대의 <D>매복</>을 되돌렸다!";
                                    $opCrew *= 1.4;
                                } else {
                                    $oppbatlog[] = "<C>●</><D>매복</>을 <C>성공</>했다!";
                                    $batlog[] = "<C>●</><D>매복</>에 당했다!";
                                    $myCrew *= 1.4;
                                }
                                break;
                            case 2:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($general['special2'] == 45 && $ratio > 70) {
                                    $oppbatlog[] = "<C>●</><D>반목</>을 <R>역으로</> 당했다!";
                                    $batlog[] = "<C>●</><C>반계</>로 상대의 <D>반목</>을 되돌렸다!";
                                    $opCrew *= 1.6;
                                } else {
                                    $oppbatlog[] = "<C>●</><D>반목</>을 <C>성공</>했다!";
                                    $batlog[] = "<C>●</><D>반목</>에 당했다!";
                                    $myCrew *= 1.6;
                                    // 특기보정 : 반계
                                    if($oppose['special2'] == 45) { $myCrew *= 2; }
                                }
                                break;
                            case 3:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($general['special2'] == 45 && $ratio > 70) {
                                    $oppbatlog[] = "<C>●</><D>화계</>를 <R>역으로</> 당했다!";
                                    $batlog[] = "<C>●</><C>반계</>로 상대의 <D>화계</>을 되돌렸다!";
                                    $opCrew *= 1.8;
                                } else {
                                    $oppbatlog[] = "<C>●</><D>화계</>를 <C>성공</>했다!";
                                    $batlog[] = "<C>●</><D>화계</>에 당했다!";
                                    $myCrew *= 1.8;
                                }
                                break;
                            case 4:
                                $ratio = rand() % 100;
                                // 특기보정 : 반계
                                if($general['special2'] == 45 && $ratio > 70) {
                                    $oppbatlog[] = "<C>●</><D>혼란</>을 <R>역으로</> 당했다!";
                                    $batlog[] = "<C>●</><C>반계</>로 상대의 <D>혼란</>을 되돌렸다!";
                                    $opCrew *= 2.0;
                                } else {
                                    $oppbatlog[] = "<C>●</><D>혼란</>을 <C>성공</>했다!";
                                    $batlog[] = "<C>●</><D>혼란</>에 당했다!";
                                    $myCrew *= 2.0;
                                }
                                break;
                            }
                        } else {
                            $type = rand() % 5; // 0~4
                            switch($type) {
                            case 0:
                                $oppbatlog[] = "<C>●</><D>위보</>를 <R>실패</>했다!";
                                $batlog[] = "<C>●</><D>위보</>를 간파했다!";
                                $myCrew /= 1.1;   $opCrew *= 1.1;
                                break;
                            case 1:
                                $oppbatlog[] = "<C>●</><D>매복</>을 <R>실패</>했다!";
                                $batlog[] = "<C>●</><D>매복</>을 간파했다!";
                                $myCrew /= 1.2;   $opCrew *= 1.2;
                                break;
                            case 2:
                                $oppbatlog[] = "<C>●</><D>반목</>을 <R>실패</>했다!";
                                $batlog[] = "<C>●</><D>반목</>을 간파했다!";
                                $myCrew /= 1.3;   $opCrew *= 1.3;
                                break;
                            case 3:
                                $oppbatlog[] = "<C>●</><D>화계</>를 <R>실패</>했다!";
                                $batlog[] = "<C>●</><D>화계</>를 간파했다!";
                                $myCrew /= 1.4;   $opCrew *= 1.4;
                                break;
                            case 4:
                                $oppbatlog[] = "<C>●</><D>혼란</>을 <R>실패</>했다!";
                                $batlog[] = "<C>●</><D>혼란</>을 간파했다!";
                                $myCrew /= 1.5;   $opCrew *= 1.5;
                                break;
                            }
                        }
                    }
                }

                // 특기보정: 돌격
                if($oppose['crewtype'] == 43 && $general['special2'] != 60) { // 목우
                    $r = 0;
                    $r += $oppose['atmos'] + $oppAtmos + $oppAtmosBonus;
                    $r += $oppose['train'] + $oppTrain + $oppTrainBonus;
                    $ratio = rand() % 400;  // 최대 50% 저지
                    if($ratio < $r && $opAvoid == 1) {
                        $batlog[] = "<C>●</><R>저지</>당했다!</>";
                        $oppbatlog[] = "<C>●</>상대를 <C>저지</>했다!</>";
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

                //군주 공격 보정 10%
                if($general['level'] == 12) {
                    $opCrew = $opCrew * 1.10;
                //참모, 장군 공격 보정 5%
                } elseif($general['level'] == 11 || $general['level'] == 10 || $general['level'] == 8 || $general['level'] == 6) {
                    $opCrew = $opCrew * 1.05;
                }

                //상대장수 관직 보정
                //군주 방어 보정 10%
                if($oppose['level'] == 12) {
                    $opCrew = $opCrew * 0.90;
                //참모, 모사 방어 보정 5%
                } elseif($oppose['level'] == 11 || $oppose['level'] == 9 || $oppose['level'] == 7 || $oppose['level'] == 5) {
                    $opCrew = $opCrew * 0.95;
                } elseif($oppose['level'] == 4 && $oppose['no'] == $city['gen1']) { // 태수 보정
                    $opCrew = $opCrew * 0.95;
                } elseif($oppose['level'] == 3 && $oppose['no'] == $city['gen2']) { // 군사 보정
                    $opCrew = $opCrew * 0.95;
                } elseif($oppose['level'] == 2 && $oppose['no'] == $city['gen3']) { // 시중 보정
                    $opCrew = $opCrew * 0.95;
                }

                //레벨 보정
                $myCrew = $myCrew * (max(1, 100 - $general['explevel']/3)/100);
                $opCrew = $opCrew / (max(1, 100 - $general['explevel']/3)/100);
                $myCrew = $myCrew / (max(1, 100 -  $oppose['explevel']/3)/100);
                $opCrew = $opCrew * (max(1, 100 -  $oppose['explevel']/3)/100);

                // 특기보정 : 기병, 돌격, 무쌍, 보병, 견고, 척사, 의술(청낭서, 태평청령)
                if($general['special2'] == 52) { $opCrew *= 1.20; }
                if($general['special2'] == 60) { $opCrew *= 1.10; }
                if($general['special2'] == 61) { $opCrew *= 1.10; }
                if($general['special2'] == 50) { $myCrew *= 0.90; }
                if($general['special2'] == 62) { $myCrew *= 0.90; }
                if($general['special2'] == 75) {
                    if($oppose['crewtype'] != 0 && $oppose['crewtype'] != 10 && $oppose['crewtype'] != 20 &&
                        $oppose['crewtype'] != 30 && $oppose['crewtype'] != 40 && $oppose['crewtype'] != 41)
                    {
                        $opCrew *= 1.1; $myCrew *= 0.9;
                    }
                }
                $ratio = rand() % 100; // 0 ~ 99
                if(($general['special2'] == 73 || $general['item'] == 23 || $general['item'] == 24) && $ratio > 80 && $myAvoid == 1) {
                    $batlog[] = "<C>●</><C>치료</>했다!</>";
                    $myCrew /= 1.5; // 66%만 소모
                    $myAvoid = 0;
                }

                // 상대 특기보정 : 기병, 무쌍, 보병, 견고, 척사, 의술(청낭서, 태평청령)
                if($oppose['special2'] == 52) { $myCrew *= 1.10; }
                if($oppose['special2'] == 61) { $myCrew *= 1.10; }
                if($oppose['special2'] == 50) { $opCrew *= 0.80; }
                if($oppose['special2'] == 62) { $myCrew *= 1.10; }
                if($oppose['special2'] == 75) {
                    if($general['crewtype'] != 0 && $general['crewtype'] != 10 && $general['crewtype'] != 20 &&
                        $general['crewtype'] != 30 && $general['crewtype'] != 40 && $general['crewtype'] != 41)
                    {
                        $myCrew *= 1.1; $opCrew *= 0.9;
                    }
                }
                $ratio = rand() % 100; // 0 ~ 99
                if(($oppose['special2'] == 73 || $oppose['item'] == 23 || $oppose['item'] == 24) && $ratio > 80 && $opAvoid == 1) {
                    $oppbatlog[] = "<C>●</><C>치료</>했다!</>";
                    $opCrew /= 1.5; // 66%만 소모
                    $opAvoid = 0;
                }

                //크리
                $rd = rand() % 100; // 0 ~ 99
                $ratio = CriticalRatio3($general);
                // 특기보정 : 무쌍, 필살
                if($general['special2'] == 61) { $ratio += 10; }
                if($general['special2'] == 71) { $ratio += 20; }
                if($ratio >= $rd && $myAvoid == 1) {
                    $batlog[] = "<C>●</><C>필살</>공격!</>";
                    $oppbatlog[] = "<C>●</>상대의 <R>필살</>공격!</>";
                    $opCrew = CriticalScore2($opCrew);
                    $myAvoid = 0;

                    // 특기보정 : 격노
                    if($oppose['special2'] == 74) {
                        if(rand() % 100 < 50) {
                            $batlog[] = "<C>●</>필살 공격에 상대가 <R>격노</>했다!</>";
                            $oppbatlog[] = "<C>●</>상대의 필살 공격에 <C>격노</>했다!</>";
                            $myCrew = CriticalScore2($myCrew);
                            $opAvoid = 0;
                        }
                    }
                }
                //크리
                $rd = rand() % 100; // 0 ~ 99
                $ratio = CriticalRatio3($oppose);
                // 특기보정 : 필살
                if($oppose['special2'] == 71) { $ratio += 20; }
                if($ratio >= $rd && $opAvoid == 1) {
                    $oppbatlog[] = "<C>●</><C>필살</>공격!</>";
                    $batlog[] = "<C>●</>상대의 <R>필살</>공격!</>";
                    $myCrew = CriticalScore2($myCrew);
                    $opAvoid = 0;

                    // 특기보정 : 격노
                    if($general['special2'] == 74) {
                        if(rand() % 100 < 33) {
                            $oppbatlog[] = "<C>●</>필살 공격에 상대가 <R>진노</>했다!</>";
                            $batlog[] = "<C>●</>상대의 필살 공격에 <C>진노</>했다!</>";
                            $opCrew = CriticalScore2($opCrew);
                            $myAvoid = 0;
                            $warphase++;
                        } elseif(rand() % 100 < 33) {
                            $oppbatlog[] = "<C>●</>필살 공격에 상대가 <R>격노</>했다!</>";
                            $batlog[] = "<C>●</>상대의 필살 공격에 <C>격노</>했다!</>";
                            $opCrew = CriticalScore2($opCrew);
                            $myAvoid = 0;
                        }
                    }
                }

                //회피
                $ratio = rand() % 100; // 0 ~ 99
                $ratio2 = GameUnitConst::byID($general['crewtype'])->avoid;   //회피율
                //특기보정 : 돌격, 궁병
                if($oppose['special2'] == 60) { $ratio2 -= 100; }
                if($general['special2'] == 51) { $ratio2 += 20; }
                //도구 보정 : 둔갑천서, 태평요술
                if($general['item'] == 26 || $general['item'] == 25) { $ratio2 += 20; }
                if($ratio < $ratio2 && $myAvoid == 1) {
                    // 특기보정 : 격노
                    if($oppose['special2'] == 74 && rand() % 100 < 50) {
                        $batlog[] = "<C>●</>회피 시도에 상대가 <R>격노</>했다!</>";
                        $oppbatlog[] = "<C>●</>상대의 회피 시도에 <C>격노</>했다!</>";
                        $myCrew = CriticalScore2($myCrew);
                        $opAvoid = 0;
                    } else {
                        $batlog[] = "<C>●</><C>회피</>했다!</>";
                        $oppbatlog[] = "<C>●</>상대가 <R>회피</>했다!</>";
                        $myCrew /= 10; // 10%만 소모
                        $myAvoid = 0;
                    }
                }
                //회피
                $ratio = rand() % 100; // 0 ~ 99
                $ratio2 = GameUnitConst::byID($oppose['crewtype'])->defence;   //회피율
                // 특기보정 : 돌격, 궁병
                if($general['special2'] == 60) { $ratio2 -= 100; }
                if($oppose['special2'] == 51) { $ratio2 += 20; }
                //도구 보정 : 둔갑천서, 태평요술
                if($oppose['item'] == 26 || $oppose['item'] == 25) { $ratio2 += 20; }
                if($ratio < $ratio2 && $opAvoid == 1) {
                    // 특기보정 : 격노
                    if($general['special2'] == 74 && rand() % 100 < 33) {
                        $oppbatlog[] = "<C>●</>회피 시도에 상대가 <R>진노</>했다!</>";
                        $batlog[] = "<C>●</>상대의 회피 시도에 <C>진노</>했다!</>";
                        $opCrew = CriticalScore2($opCrew);
                        $myAvoid = 0;
                        $warphase++;
                    } elseif($general['special2'] == 74 && rand() % 100 < 33) {
                        $oppbatlog[] = "<C>●</>회피 시도에 상대가 <R>격노</>했다!</>";
                        $batlog[] = "<C>●</>상대의 회피 시도에 <C>격노</>했다!</>";
                        $opCrew = CriticalScore2($opCrew);
                        $myAvoid = 0;
                    } else {
                        $oppbatlog[] = "<C>●</><C>회피</>했다!</>";
                        $batlog[] = "<C>●</>상대가 <R>회피</>했다!</>";
                        $opCrew /= 10; // 10%만 소모
                        $opAvoid = 0;
                    }
                }

                //랜타추가
                $opCrew *= (rand() % 21 + 90)/100; // 90~110%
                $myCrew *= (rand() % 21 + 90)/100; // 90~110%

                //특기보정 : 위압
                if($general['special2'] == 63 && $phase == 1 && $general['crew'] >= 1000 && $general['atmos'] >= 90 && $general['train'] >= 90) {
                    $batlog[] = "<C>●</>상대에게 <C>위압</>을 줬다!</>";
                    $oppbatlog[] = "<C>●</>상대에게 <R>위압</>받았다!</>";
                    $myCrew = 0;
                }
                //특기보정: 위압
                if($oppose['special2'] == 63 && $phase == 1 && $oppose['crew'] >= 1000 && $oppose['atmos'] >= 90 && $oppose['train'] >= 90) {
                    $batlog[] = "<C>●</>상대에게 <R>위압</>받았다!</>";
                    $oppbatlog[] = "<C>●</>상대에게 <C>위압</>을 줬다!</>";
                    $opCrew = 0;
                }

                $general['crew'] -= Util::round($myCrew);
                $oppose['crew'] -= Util::round($opCrew);
                $tempMyCrew = $myCrew; $tempOpCrew = $opCrew;
                $tempGeneralCrew = $general['crew']; $tempOpposeCrew = $oppose['crew'];
                if($general['crew'] <= 0 && $oppose['crew'] <= 0) {
                    $r1 = $tempGeneralCrew / $tempMyCrew;
                    $r2 = $tempOpposeCrew / $tempOpCrew;

                    if($r1 > $r2) {
                        $offset = Util::round($tempOpposeCrew*$tempMyCrew/$tempOpCrew);
                        $myCrew += $offset;
                        $general['crew'] -= $offset;
                        $opCrew += $tempOpposeCrew;
                        $oppose['crew'] = 0;
                    } else {
                        $offset = Util::round($tempGeneralCrew*$tempOpCrew/$tempMyCrew);
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
                $batlog[] = "<C>●</> $phase : <Y1>【{$general['name']}】</> <C>{$general['crew']} (-$myCrew)</> VS <C>{$oppose['crew']} (-$opCrew)</> <Y1>【{$oppose['name']}】</>";
                $oppbatlog[] = "<C>●</> $phase : <Y1>【{$oppose['name']}】</> <C>{$oppose['crew']} (-$opCrew)</> VS <C>{$general['crew']} (-$myCrew)</> <Y1>【{$general['name']}】</>";

                $mykillnum += $opCrew; $mydeathnum += $myCrew;
                $opkillnum += $myCrew; $opdeathnum += $opCrew;

                // 중간 쌀 체크
                $myRice = Util::round($exp / 50);
                // 성격 보정
                $myRice = CharExperience($myRice, $general['personal']);
                // 쌀 소모
                $myRice = ($myRice * 5 * getCrewtypeRice($general['crewtype'], $nation['tech']));
                // 결과 쌀
                $myRice = $general['rice'] - $myRice;

                // 중간 쌀 체크
                $opRice = Util::round($opexp / 50 * 0.8);
                // 성격 보정
                $opRice = CharExperience($opRice, $oppose['personal']);
                // 쌀 소모
                $opRice = ($opRice * 5 * getCrewtypeRice($oppose['crewtype'], $destnation['tech']));
                // 결과 쌀
                $opRice = $oppose['rice'] - $opRice;

                if($opRice <= Util::round($oppose['crew']/100)) { break; }
                if($myRice <= Util::round($general['crew']/100)) { break; }

                if($oppose['crew'] <= 0) { break; }
                if($general['crew'] <= 0) { break; }
            }

            $render_attacker = [
                'crewtype' => mb_substr(GameUnitConst::byId($general['crewtype'])->name, 0, 2),
                'name'=> $general['name'],
                'remain_crew' => $general['crew'],
                'killed_crew' => -$mydeathnum
            ];
            $render_defender = [
                'crewtype' => mb_substr(GameUnitConst::byId($oppose['crewtype'])->name, 0, 2),
                'name'=> $oppose['name'],
                'remain_crew' => $oppose['crew'],
                'killed_crew' => -$opdeathnum
            ];

            $res = str_replace(["\r\n", "\r", "\n"], '', $templates->render('small_war_log',[
                'year'=>$year,
                'month'=>$month,
                'war_type'=>'attack',
                'war_type_str'=>'공',
                'me' => $render_attacker,
                'you' => $render_defender,
            ]));

            $oppres = str_replace(["\r\n", "\r", "\n"], '', $templates->render('small_war_log',[
                'year'=>$year,
                'month'=>$month,
                'war_type'=>'defense',
                'war_type_str'=>'수',
                'me' => $render_defender,
                'you' => $render_attacker,
            ]));

            $log[] = $res;
            $batlog[] = $res;
            $batres[] = $res;
            $opplog[] = $oppres;
            $oppbatlog[] = $oppres;
            $oppbatres[] = $oppres;

            $deadAmount['att'] = $deadAmount['att'] + $mydeathnum;
            $deadAmount['def'] = $deadAmount['def'] + $opdeathnum;

            // 상대장수 부상
            $ratio = rand() % 100;
            if($ratio >= 95) {
                $opplog[] = "<C>●</>전투중 <R>부상</>당했다!";
                $oppose['injury'] += rand() % 71 + 10;   // 10 ~ 80
                if($oppose['injury'] > 80) { $oppose['injury'] = 80; }
            }
            // 병사수 변경
            $query = "update general set injury='{$oppose['injury']}',crew='{$oppose['crew']}',killcrew=killcrew+'$opkillnum',deathcrew=deathcrew+'$opdeathnum' where no='{$oppose['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 숙련도 증가
            addGenDex($oppose['no'], $oppose['crewtype'], $opkillnum * 0.9);
            addGenDex($oppose['no'], $general['crewtype'], $opdeathnum * 0.9);
            // 죽은수 기술로 누적
            $num = Util::round($mydeathnum * 0.01);
            // 국가보정
            if($nation['type'] == 3 || $nation['type'] == 13)                                                                   { $num *= 1.1; }
            if($nation['type'] == 5 || $nation['type'] == 6 || $nation['type'] == 7 || $nation['type'] == 8 || $nation['type'] == 12) { $num *= 0.9; }
            // 부드러운 기술 제한
            if(TechLimit($game['startyear'], $year, $nation['tech'])) { $num = intdiv($num, 4); }
            $query = "update nation set totaltech=totaltech+'$num',tech=totaltech/'$gencount' where nation='{$nation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 장수 부상
            $ratio = rand() % 100;
            if($ratio >= 95) {
                $log[] = "<C>●</>전투중 <R>부상</>당했다!";
                $general['injury'] += rand() % 71 + 10;   // 10 ~ 80
                if($general['injury'] > 80) { $general['injury'] = 80; }
            }
            // 병사수 변경
            $query = "update general set injury='{$general['injury']}',crew='{$general['crew']}',killcrew=killcrew+'$mykillnum',deathcrew=deathcrew+'$mydeathnum' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 숙련도 증가
            addGenDex($general['no'], $general['crewtype'], $mykillnum);
            addGenDex($general['no'], $oppose['crewtype'], $mydeathnum);
            // 죽은수 기술로 누적
            $num = Util::round($opdeathnum * 0.01);
            // 국가보정
            if($destnation['type'] == 3 || $destnation['type'] == 13)                                                                               { $num *= 1.1; }
            if($destnation['type'] == 5 || $destnation['type'] == 6 || $destnation['type'] == 7 || $destnation['type'] == 8 || $destnation['type'] == 12) { $num *= 0.9; }
            // 부드러운 기술 제한
            if(TechLimit($game['startyear'], $year, $destnation['tech'])) { $num = intdiv($num, 4); }
            $query = "update nation set totaltech=totaltech+'$num',tech=totaltech/'$destgencount' where nation='{$destnation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //양국 평균 기술가격
            //$techRatio = (getTechCost($nation['tech']) + getTechCost($destnation['tech'])) / 2;
            $techRatio = 1.0;
            // 죽은수 도시 재정으로 누적 60%
            $num = Util::round(($mykillnum+$mydeathnum) * 0.6 * $techRatio);
            // 국가보정
            if($destnation['type'] == 1)                            { $num *= 1.1; }
            if($destnation['type'] == 9 || $destnation['type'] == 10) { $num *= 0.9; }
            $query = "update city set dead=dead+'$num' where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            // 죽은수 도시 재정으로 누적 40%
            $num = Util::round(($mykillnum+$mydeathnum) * 0.4 * $techRatio);
            // 국가보정
            if($nation['type'] == 1)                        { $num *= 1.1; }
            if($nation['type'] == 9 || $nation['type'] == 10) { $num *= 0.9; }
            $query = "update city set dead=dead+'$num' where city='{$general['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 상대 병사 소진이나 쌀 소진시 다음 장수
            if($oppose['crew'] <= 0 || ($opRice <= Util::round($oppose['crew']/100) && $general['crew'] > 0)) {
                if($opRice <= Util::round($oppose['crew']/100)) {
                    $alllog[] = "<C>●</>{$month}월:<Y>{$oppose['name']}</>의 ".GameUnitConst::byId($oppose['crewtype'])->name."(이)가 패퇴했습니다.";
                    $log[] = "<C>●</><Y>{$oppose['name']}</>의 ".GameUnitConst::byId($oppose['crewtype'])->name."(이)가 패퇴했습니다.";
                    $opplog[] = "<C>●</>군량 부족으로 패퇴합니다.";
                } else {
                    $alllog[] = "<C>●</>{$month}월:<Y>{$oppose['name']}</>의 ".GameUnitConst::byId($oppose['crewtype'])->name."(이)가 전멸했습니다.";
                    $log[] = "<C>●</><Y>{$oppose['name']}</>의 ".GameUnitConst::byId($oppose['crewtype'])->name."(이)가 전멸했습니다.";
                    $opplog[] = "<C>●</>전멸했습니다.";
                }
                $opposecount--;

                $general['atmos'] *= 1.1; //사기 증가
                if($general['atmos'] > $_maximumatmos) { $general['atmos'] = $_maximumatmos; }

                $query = "update general set atmos='{$general['atmos']}',killnum=killnum+1 where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                $exp2++;
                // 상대장수 경험 등등 증가
                $opexp = Util::round($opexp / 50 * 0.8);
                // 성격 보정
                $opexp = CharExperience($opexp, $oppose['personal']);
                // 쌀 소모
                $oppose['rice'] -= ($opexp * 5 * getCrewtypeRice($oppose['crewtype'], $destnation['tech']));
                if($oppose['rice'] < 0) { $oppose['rice'] = 0; }

                $query = "update general set deathnum=deathnum+1,rice='{$oppose['rice']}',experience=experience+'$opexp',dedication=dedication+'$opexp' where no='{$oppose['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $opexp = 0;

                pushGenLog($oppose, $opplog);
                pushBatLog($oppose, $oppbatlog);
                pushBatRes($oppose, $oppbatres);
                unset($oppose);
                unset($opplog);
                unset($oppbatlog);
                unset($oppbatres);
            // 공격 장수 병사 소진이나 쌀 소진시 실패 처리
            } elseif($general['crew'] <= 0 || $myRice <= Util::round($general['crew']/100)) {
                if($myRice <= Util::round($general['crew']/100)) {
                    $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(이)가 퇴각했습니다.";
                    $log[] = "<C>●</>군량 부족으로 퇴각합니다.";
                    $opplog[] = "<C>●</><Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(이)가 퇴각했습니다.";
                } else {
                    $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(이)가 퇴각했습니다.";
                    $log[] = "<C>●</>퇴각했습니다.";
                    $opplog[] = "<C>●</><Y>{$general['name']}</>의 ".GameUnitConst::byId($general['crewtype'])->name."(이)가 퇴각했습니다.";
                }

                // 경험치 상승
                if(intdiv($oppose['crewtype'], 10) == 3) {   // 귀병
                    $oppose['intel2']++;
                } elseif(intdiv($oppose['crewtype'], 10) == 4) {   // 차병
                    $oppose['leader2']++;
                } else {
                    $oppose['power2']++;
                }

                $oppose['atmos'] *= 1.1; //사기 증가
                if($oppose['atmos'] > $_maximumatmos) { $oppose['atmos'] = $_maximumatmos; }

                // 상대장수 경험 등등 증가
                $opexp = Util::round($opexp / 50 * 0.8);
                // 성격 보정
                $opexp = CharExperience($opexp, $oppose['personal']);
                // 쌀 소모
                $oppose['rice'] -= ($opexp * 5 * getCrewtypeRice($oppose['crewtype'], $destnation['tech']));
                if($oppose['rice'] < 0) { $oppose['rice'] = 0; }

                $query = "update general set rice='{$oppose['rice']}',leader2='{$oppose['leader2']}',power2='{$oppose['power2']}',intel2='{$oppose['intel2']}',atmos='{$oppose['atmos']}',experience=experience+'$opexp',dedication=dedication+'$opexp',killnum=killnum+1 where no='{$oppose['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $query = "update general set deathnum=deathnum+1 where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $opexp = 0;

                $opplog = checkAbility($oppose, $opplog);
                pushGenLog($oppose, $opplog);
                pushBatLog($oppose, $oppbatlog);
                pushBatRes($oppose, $oppbatres);
                unset($opplog);
                unset($oppbatlog);
                unset($oppbatres);
//                $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>(이)가 }<G>{$city['name']}</> 공략에 실패했습니다. <1>$date</>";
//                $log[] = "<C>●</> <G>{$city['name']}</> 공략에 실패했습니다. <1>$date</>";
                break;
            } else {
                // 무승부일때 로그 남김
                pushGenLog($oppose, $opplog);
                pushBatLog($oppose, $oppbatlog);
                pushBatRes($oppose, $oppbatres);
                unset($opplog);
                unset($oppbatlog);
                unset($oppbatres);
            }
        }
    }

    if(isset($oppose)){
        //마지막 페이즈에 장수가 전멸하지 않은 경우. 쌀 소모 후속 처리

        // 상대장수 경험 등등 증가(페이즈 초과시)
        $opexp = Util::round($opexp / 50 * 0.8);
        // 성격 보정
        $opexp = CharExperience($opexp, $oppose['personal']);
        // 쌀 소모
        $oppose['rice'] -= ($opexp * 5 * getCrewtypeRice($oppose['crewtype'], $destnation['tech']));
        if($oppose['rice'] < 0) { $oppose['rice'] = 0; }

        $query = "update general set rice='{$oppose['rice']}',experience=experience+'$opexp',dedication=dedication+'$opexp' where no='{$oppose['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    // 경험치 상승
    if(intdiv($general['crewtype'], 10) == 3) {   // 귀병
        $general['intel2'] += $exp2;
    } elseif(intdiv($general['crewtype'], 10) == 4) {   // 차병
        $general['leader2'] += $exp2;
    } else {
        $general['power2'] += $exp2;
    }
    $query = "update general set leader2='{$general['leader2']}',power2='{$general['power2']}',intel2='{$general['intel2']}' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 공헌, 명성 상승
    $exp = Util::round($exp / 50);
    // 성격 보정
    $exp = CharExperience($exp, $general['personal']);
    // 쌀 소모
    $general['rice'] -= ($exp * 5 * getCrewtypeRice($general['crewtype'], $nation['tech']));
    if($general['rice'] < 0) { $general['rice'] = 0; }

    $query = "update general set rice='{$general['rice']}',dedication=dedication+'$exp',experience=experience+'$exp' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $log = checkAbility($general, $log);
    pushGenLog($general, $log);
    pushBatLog($general, $batlog);
    pushBatRes($general, $batres);
    pushGeneralPublicRecord($alllog, $year, $month);
    pushWorldHistory($history);

    return $deadAmount;
}


function CriticalRatio3($general) {
    //  무장 무력 : 65 5%, 70 10%, 75 15%, 80 20%
    //  지장 지력 : 65 5%, 70  8%, 75 10%, 80 13%
    //충차장 통솔:  65 5%, 70  8%, 75 10%, 80 13%

    $crewtype = intdiv($general['crewtype'], 10);

    if($crewtype == 3){ //지장
        $mainstat = getGeneralIntel($general, false, true, true, false);
    }
    else if($crewtype == 4){ //병기
        $mainstat = getGeneralLeadership($general, false, true, true, false);
    }
    else{ //무장
        $mainstat = getGeneralPower($general, false, true, true, false);
    }

    $ratio = max($mainstat - 65, 0);

    if ($crewtype >= 3) {
       $ratio /= 2;
    }
    else{
        $ratio *= 0.7;
    }

    $ratio = round($ratio);
    $ratio += 5;

    if($ratio > 50) $ratio = 50;

    return $ratio;
}

function CriticalRatio2($leader, $power, $intel) {
    // 707010장수 9% 706515장수 17% 706020장수 25% xx50xx장수 57%
    if($leader <= $power && $leader <= $intel) {        // 통솔이 제일 낮은경우
        $ratio = sqrt($leader/($leader+$power+$intel))*150 - 30;
    } elseif($power < $leader && $power <= $intel) {    // 무력이 제일 낮은경우
        $ratio = sqrt($power /($leader+$power+$intel))*150 - 30;
    } elseif($intel < $leader && $intel < $power)  {    // 지력이 제일 낮은경우
        $ratio = sqrt($intel /($leader+$power+$intel))*150 - 30;
    }

    if($ratio > 50) $ratio = 50;
    return $ratio;
}

function CriticalScore2($score) {
    $score = Util::round($score * (rand()%8 + 13)/10);    // 1.3~2.0
    return $score;
}

//0 0 : 100 100 이면 최고 무한대 차이
//30 30 : 100 100 이면 최고 3.3배 차이
//60 60 : 100 100 이면 최고 1.6배 차이
function getCrew($crew, $youatmos, $mytrain) {
    $ratio = $youatmos / $mytrain;
    $crew = $crew * $ratio;

    return $crew;
}

function getCrewtypeRice($crewtype, $tech) {
    if(!$crewtype) { $crewtype = 0; }
    $cost = GameUnitConst::byID($crewtype)->rice / 10;
    return $cost * getTechCost($tech);
}

//////////////////////////////////////////////////////////////
// 표준 공 / 수 반환 수치는 약 0이 되게 (100~550)
//////////////////////////////////////////////////////////////

function getAtt($general, $tech, $lbonus) {
    $att = GameUnitConst::byID($general['crewtype'])->attack + getTechAbil($tech);
    
    $general['lbonus'] = $lbonus;

    if(intdiv($general['crewtype'], 10) == 3) {   // 귀병 지100%
        $ratio = getGeneralIntel($general, true, true, true)*2 - 40;
    } elseif(intdiv($general['crewtype'], 10) == 4) {   // 차병 통100%
        $ratio = getGeneralLeadership($general, true, true, true)*2 - 40;
    } else {
        $ratio = getGeneralPower($general, true, true, true)*2 - 40; //10일때 -20, 70일때 100, 100일때 160
    }
    if($ratio <  10) { $ratio = 10; }
    if($ratio > 100) { $ratio = 50 + $ratio/2; }    // 100보다 큰 경우는 상승률 1/2
    $att = $att * $ratio / 100;

    return $att;
}

function getDef($general, $tech) {
    $def = GameUnitConst::byID($general['crewtype'])->defence + getTechAbil($tech);

    $crew = ($general['crew'] / (7000 / 30)) + 70;    // 5000명일때 91점 7000명일때 100점 10000명일때 113점
    $def = $def * $crew / 100;

    return $def;
}

function getCityAtt($city) {
    $att = ($city['def']*0.1 + $city['wall']*0.9) / 500 + 200;    //50000일때 300점 100000일때 400점
    return $att;
}

function getCityDef($city) {
    $def = ($city['def']*0.1 + $city['wall']*0.9) / 500 + 200;
    return $def;
}

function getRate($game, $type, $dtype) {
    $t = "{$dtype}{$type}";
    return $game[$t];
}

function addConflict($city, $nationnum, $mykillnum) {
    $db = DB::db();
    $connect=$db->get();

    $nationlist = [];
    $killnum = [0];

    list($year, $month) = $db->queryFirstList('SELECT year, month FROM game LIMIT 1');

    if($city['conflict']){
        $nationlist = array_map('intval', explode("|", $city['conflict']));
        $killnum = array_map('intval', explode("|", $city['conflict2']));
    }
    else{
        $nationlist = [$nationnum];
        $killnum = [0];
    }

    for($i=0; $i < count($nationlist); $i++) {
        if($nationlist[$i] == $nationnum) break;
    }
    if($i != 0 && $i == count($nationlist)) { // 두번째 나라부터 분쟁 가담 메시지 출력
        $nation = getNationStaticInfo($nationnum);

        pushWorldHistory(["<C>●</>{$year}년 {$month}월:<M><b>【분쟁】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$city['name']}</b></> 공략에 가담하여 분쟁이 발생하고 있습니다."]);
    }

    $nationlist[$i] = $nationnum;

    if($i == 0 || $city['def'] == 0) {    // 선타, 막타 보너스
        $killnum[$i] += Util::round($mykillnum * 1.05);
    } else {
        $killnum[$i] += $mykillnum;
    }
    $city['conflict'] = implode("|", $nationlist);
    $city['conflict2'] = implode("|", $killnum);

    $query = "update city set conflict='{$city['conflict']}',conflict2='{$city['conflict2']}' where city='{$city['city']}'";
    MYDB_query($query, $connect) or Error("addConflict ".MYDB_error($connect),"");

    return $city;
}

function DeleteConflict($nation) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select city,conflict,conflict2 from city where conflict!=''";
    $result = MYDB_query($query, $connect) or Error("addConflict ".MYDB_error($connect),"");
    $cityNum = MYDB_num_rows($result);

    $nation = "$nation";
    for($k=0; $k < $cityNum; $k++) {
        $city = MYDB_fetch_array($result);

        if(strpos($city['conflict'], $nation)) {
            $nationlist = explode("|", $city['conflict']);
            $killnum = explode("|", $city['conflict2']);

            $count = count($nationlist);
            for($i=0; $i < $count; $i++) {
                if($nationlist[$i] == $nation) {
                    unset($nationlist[$i]);
                    unset($killnum[$i]);
                    break;
                }
            }
            $conflict = implode("|", $nationlist);
            $conflict2 = implode("|", $killnum);

            $query = "update city set conflict='$conflict',conflict2='$conflict2' where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error("addConflict ".MYDB_error($connect),"");
        }
    }
}

function getConquerNation($city) : int {
    $db = DB::db();
    $connect=$db->get();

    $nationlist = array_map('intval', explode("|", (string)$city['conflict']));
    $killnum = array_map('intval', explode("|", (string)$city['conflict2']));

    $max = 0;
    for($i=0; $i < count($nationlist); $i++) {
        if($max <= $killnum[$i]) {
            $max = $killnum[$i];
            $index = $i;
        }
    }
    return $nationlist[$index];
}

function ConquerCity($game, $general, $city, $nation, $destnation) {
    '@phan-var array<string,mixed> $city';
    $db = DB::db();
    $connect=$db->get();

    global $_maximumatmos;

    $alllog = [];
    $log = [];
    $history = [];

    if($destnation['nation'] > 0) {
        $destnationName = "<D><b>{$destnation['name']}</b></>의";
    } else {
        $destnationName = "공백지인";
    }

    $year = $game['year'];
    $month = $game['month'];

    $alllog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>(이)가 <G><b>{$city['name']}</b></> 공략에 <S>성공</>했습니다.";
    $log[] = "<C>●</><G><b>{$city['name']}</b></> 공략에 <S>성공</>했습니다.";
    $history[] = "<C>●</>{$year}년 {$month}월:<S><b>【지배】</b></><D><b>{$nation['name']}</b></>(이)가 <G><b>{$city['name']}</b></>(을)를 지배했습니다.";
    pushGeneralHistory($general, "<C>●</>{$year}년 {$month}월:<G><b>{$city['name']}</b></>(을)를 <S>함락</>시킴");
    pushNationHistory($nation, "<C>●</>{$year}년 {$month}월:<Y>{$general['name']}</>(이)가 {$destnationName} <G><b>{$city['name']}</b></>(을)를 <S>점령</>");
    pushNationHistory($destnation, "<C>●</>{$year}년 {$month}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>에 의해 <G><b>{$city['name']}</b></>(이)가 <span class='ev_highlight'>함락</span>");

    $query = "select city from city where nation='{$city['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    // 국가 멸망시
    //TODO: 국가 멸망 코드를 별도로 작성
    if($citycount == 1 && $city['nation'] != 0) {
        $query = "select nation,name,gold,rice from nation where nation='{$city['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $losenation = MYDB_fetch_array($result);

        $history[] = "<C>●</>{$year}년 {$month}월:<R><b>【멸망】</b></><D><b>{$losenation['name']}</b></>(이)가 멸망하였습니다.";
        pushNationHistory($nation, "<C>●</>{$year}년 {$month}월:<D><b>{$losenation['name']}</b></>(을)를 정복");

        $query = "select no, nation from general where nation='{$general['nation']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $ruler = MYDB_fetch_array($result);

        //다굴치는 나라들 전방설정을 위해 미리 얻어옴
        $query = "select you from diplomacy where me='{$losenation['nation']}' and state<2";
        $dipResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $dipCount = MYDB_num_rows($dipResult);

        $loseGeneralGold = 0;
        $loseGeneralRice = 0;
        //멸망국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation,npc,gold,rice from general where nation='{$city['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog = ["<C>●</><D><b>{$losenation['name']}</b></>(이)가 <R>멸망</>했습니다."];
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);

            $loseGold = intdiv($gen['gold'] * (rand()%30+20), 100);
            $loseRice = intdiv($gen['rice'] * (rand()%30+20), 100);
            $genlog[1] = "<C>●</>도주하며 금<C>$loseGold</> 쌀<C>$loseRice</>을 분실했습니다.";
            
            $query = "update general set gold=gold-{$loseGold},rice=rice-{$loseRice} where no={$gen['no']}";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            
            pushGenLog($gen, $genlog);
            
            pushGeneralHistory($gen, "<C>●</>{$year}년 {$month}월:<D><b>{$losenation['name']}</b></>(이)가 <R>멸망</>");

            $loseGeneralGold += $loseGold;
            $loseGeneralRice += $loseRice;
            
            //모두 등용장 발부
            if($nation['name'] == "강족" || $nation['name'] == "저족" || $nation['name'] == "흉노족"
                || $nation['name'] == "남만족" || $nation['name'] == "산월족" || $nation['name'] == "오환족"
                || $nation['name'] == "왜족") {
                //등용장 미발부
            } elseif(Util::randBool(0.5)) {
                sendScoutMsg([
                    'id' => $ruler['no'],
                    'nation_id' => $ruler['nation']
                ],[
                    'id' => $gen['no'],
                    'nation_id' => $gen['nation']
                ],$general['turntime']);
            }

            //NPC인 경우 10% 확률로 임관(엔장, 인재, 의병)
            if($gen['npc'] >= 2 && $gen['npc'] <= 4 && rand() % 100 < 10) {
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
        $query = "update city set nation='0',gen1='0',gen2='0',gen3='0',conflict='',conflict2='',term=0 where nation='{$city['nation']}'";
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
        // 국가 삭제
        $query = "delete from nation where nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아까 얻어온 다굴국들 전방설정
        for($i=0; $i < $dipCount; $i++) {
            $dip = MYDB_fetch_array($dipResult);
            //전방설정
            SetNationFront($dip['you']);
        }
    // 멸망이 아니면
    } else {
        // 태수,군사,시중은 일반으로...
        $query = "update general set level='1' where no='{$city['gen1']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set level='1' where no='{$city['gen2']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set level='1' where no='{$city['gen3']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //수도였으면 긴급 천도
        if($destnation['capital'] == $city['city']) {
            $distList = searchDistance($city['city'], 99, true);

            $cities = [];
            foreach(
                DB::db()->query(
                    'SELECT nation, city, pop FROM city WHERE nation=%i and city!=%i', 
                    $destnation['nation'], 
                    $city['city']
                ) as $row
            ){
                $cities[$row['city']] = $row;
            };

            $distKeys = array_keys($distList);
            sort($distKeys);

            $minCity = 0;

            foreach($distKeys as $dist){
                $hasTarget = false;
                $maxCityPop = 0;
                
                foreach($distList[$dist] as $cityID){
                    $city = $cities[$cityID];
                    '@phan-var array<string,mixed> $city';
                    if($city['nation'] != $destnation['nation']){
                        continue;
                    }

                    if($city['pop'] <= $maxCityPop){
                        continue;
                    }
                    
                    $hasTarget = true;
                    $minCity = $cityID;
                    $maxCityPop = $city['pop'];
                }

                if($hasTarget){
                    break;
                }
            }

            $minCityName = CityConst::byID($minCity)->name;

            $history[] = "<C>●</>{$year}년 {$month}월:<M><b>【긴급천도】</b></><D><b>{$destnation['name']}</b></>(이)가 수도가 함락되어 <G><b>$minCityName</b></>으로 긴급천도하였습니다.";

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

    $general['atmos'] *= 1.1; //사기 증가
    if($general['atmos'] > $_maximumatmos) { $general['atmos'] = $_maximumatmos; }

    $conquerNation = getConquerNation($city);

    if($conquerNation == $general['nation']) {
        // 이동 및 사기 변경
        $query = "update general set city='{$city['city']}',atmos='{$general['atmos']}',killnum=killnum+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        if($city['level'] > 3) {
            // 도시 소속 변경, 태수,군사,시중 초기화
            $query = "update city set supply=1,conflict='',term=0,conflict2='',agri=agri*0.7,comm=comm*0.7,secu=secu*0.7,def=1000,wall=1000,nation='{$general['nation']}',gen1=0,gen2=0,gen3=0 where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            // 도시 소속 변경, 태수,군사,시중 초기화
            $query = "update city set supply=1,conflict='',term=0,conflict2='',agri=agri*0.7,comm=comm*0.7,secu=secu*0.7,def=def2/2,wall=wall2/2,nation='{$general['nation']}',gen1=0,gen2=0,gen3=0 where city='{$city['city']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //전방설정
        SetNationFront($nation['nation']);
        SetNationFront($destnation['nation']);
    } else {
        $query = "select name,nation from nation where nation='$conquerNation'";
        $conquerResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $conquerNationArray = MYDB_fetch_array($conquerResult);
        


        $history[] = "<C>●</>{$year}년 {$month}월:<Y><b>【분쟁협상】</b></><D><b>{$conquerNationArray['name']}</b></>(이)가 영토분쟁에서 우위를 점하여 <G><b>{$city['name']}</b></>(을)를 양도받았습니다.";
        pushNationHistory($nation, "<C>●</>{$year}년 {$month}월:<G><b>{$city['name']}</b></>(을)를 <D><b>{$conquerNationArray['name']}</b></>에 <Y>양도</>");
        pushNationHistory($conquerNationArray, "<C>●</>{$year}년 {$month}월:<D><b>{$nation['name']}</b></>에서 <G><b>{$city['name']}</b></>(을)를 <S>양도</> 받음");
        // 이동X 및 사기 변경
        $query = "update general set atmos='{$general['atmos']}',killnum=killnum+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = [
            'supply'=>1,
            'term'=>0,
            'conflict2'=>'',
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

    pushGenLog($general, $log);
    pushGeneralPublicRecord($alllog, $year, $month);
    pushWorldHistory($history);
}

