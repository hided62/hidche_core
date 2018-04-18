<?php
namespace sammo;

function SetDevelop($genType, $no, $city, $tech) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select rate,pop/pop2*100 as po,comm/comm2*100 as co,def/def2*100 as de,wall/wall2*100 as wa,secu/secu2*100 as se,agri/agri2*100 as ag from city where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    // 우선 선정
    if($city['rate'] < 95) {
        $command = EncodeCommand(0, 0, 0, 4);    // 우선 선정
        
        $query = "update general set turn0='$command' where no='$no'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return;
    }
    
    $prob = rand() % 100;
    $command = EncodeCommand(0, 0, 0, 9); //조달
    switch($genType) {
    case 0: //무장
    case 2: //무내정장
        if($prob < 30) {
            if($city['de'] < 99) { $command = EncodeCommand(0, 0, 0, 5); } //수비
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 60) {
            if($city['wa'] < 99) { $command = EncodeCommand(0, 0, 0, 6); } //성벽
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 90) {
            if($city['se'] < 99) { $command = EncodeCommand(0, 0, 0, 8); } //치안
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } else {
            $command = EncodeCommand(0, 0, 0, 29);
        }
        break;
    case 1: //지장
    case 3: //지내정장
        if($prob < 40) {
            if($city['ag'] < 99) { $command = EncodeCommand(0, 0, 0, 1); } //농업
            elseif($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3); } //기술
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 80) {
            if($city['co'] < 99) { $command = EncodeCommand(0, 0, 0, 2); } //상업
            elseif($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3); } //기술
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 90) {
            if($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3); } //기술
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } else {
            if($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3 + (rand() % 2) * 6); } //기술, 조달
            else { $command = EncodeCommand(0, 0, 0, 29); }
        }
        break;
    }

    // 장수수가 너무 많으면 탐색 확률 감소
    if($command == EncodeCommand(0, 0, 0, 29)) {
        $query = "select no from general";
        $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
        $genCount = MYDB_num_rows($result);

        $ratio = Util::round($genCount / 600 * 100);

        if(rand() % 100 < $ratio) {
            $command = EncodeCommand(0, 0, 0, 9);
        }
    }
    
    $query = "update general set turn0='$command' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    return;
}

function SetCrew($no, $personal, $gold, $leader, $genType, $tech, $region, $city, $dex0, $dex10, $dex20, $dex30, $dex40) {
    $db = DB::db();
    $connect=$db->get();

    $type = 0;
    switch($genType) {
    case 0: //무장
    case 2: //무내정장
        $dex0 = $dex0 + rand()%1000;
        $dex10 = $dex10 + rand()%1000;
        $dex20 = $dex20 + rand()%1000;
        $sel = 0;
        // 보궁기 선택
        if($dex0 > $dex10) {
            if($dex0 > $dex20) {
                $sel = 0;
            } else {
                $sel = 2;
            }
        } else {
            if($dex10 > $dex20) {
                $sel = 1;
            } else {
                $sel = 2;
            }
        }

        switch($sel) {
        case 0:
            $type = 0; //보병
                if($tech >= 3000 && $city   ==  3) { $type =  4; } //근위병
            elseif($tech >= 2000 && $city   == 64) { $type =  3; } //자객병
            elseif($tech >= 1000 && $region ==  2) { $type =  1; } //청주병
            elseif($tech >= 1000 && $region ==  5) { $type =  5; } //등갑병
            elseif($tech >= 1000 && $region ==  7) { $type =  2; } //수병
            break;
        case 1:
            $type = 10; //궁병
                if($tech >= 3000 && $city   ==  7) { $type = 14; } //석궁병
            elseif($tech >= 3000 && $city   ==  6) { $type = 13; } //강궁병
            elseif($tech >= 1000 && $region ==  4) { $type = 12; } //연노병
            elseif($tech >= 1000 && $region ==  8) { $type = 11; } //궁기병
            break;
        case 2:
            $type = 20; //기병
                if($tech >= 3000 && $city   ==  2) { $type = 27; } //호표기병
            elseif($tech >= 2000 && $city   == 63) { $type = 24; } //철기병
            elseif($tech >= 2000 && $city   == 67) { $type = 25; } //수렵기병
            elseif($tech >= 2000 && $city   == 65) { $type = 23; } //돌격기병
            elseif($tech >= 2000 && $city   == 66) { $type = 26; } //맹수병
            elseif($tech >= 1000 && $region ==  1) { $type = 21; } //백마병
            elseif($tech >= 1000 && $region ==  3) { $type = 22; } //중장기병
            break;
        }
        break;
    case 1: //지장
    case 3: //지내정장
        $type = 30; //귀병
            if($tech >= 3000 && $city   ==  4) { $type = 34; } //악귀병
        elseif($tech >= 3000 && $city   ==  5) { $type = 37; } //천귀병
        elseif($tech >= 3000 && $city   ==  1) { $type = 38; } //마귀병
        elseif($tech >= 2000 && $city   == 69) { $type = 33; } //흑귀병
        elseif($tech >= 2000 && $city   == 68) { $type = 32; } //백귀병
        elseif($tech >= 1000 && $region ==  6) { $type = 31; } //신귀병
        elseif($tech >= 3000 && $city   ==  3) { $type = 36; } //황귀병
        elseif($tech >= 1000 && rand()%100 < 50) { $type = 35; } //남귀병
        break;
    }

    $gold -= 200;   // 사기비용

    $cost = getCost($type) * getTechCost($tech);
    $cost = CharCost($cost, $personal);

    $crew = intdiv($gold, $cost);
    if($leader < $crew) { $crew = $leader; }
    $command = EncodeCommand(0, $type, $crew, 11);

    $query = "update general set turn0='$command' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    return;
}

function processAI($no) {
    $db = DB::db();
    $connect=$db->get();

    $query = "select startyear,year,month,turnterm,scenario,gold_rate,rice_rate from game limit 1";
    $result = MYDB_query($query, $connect) or Error("processAI00 ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    // 초반 여부
    if($admin['startyear']+2 > $admin['year'] || ($admin['startyear']+2 == $admin['year'] && $admin['month'] < 5)) {
        $isStart = 1;
    } else {
        $isStart = 0;
    }

    $query = "select no,turn0,npcid,name,nation,nations,city,level,npcmsg,personal,leader,intel,power,gold,rice,crew,train,atmos,npc,affinity,mode,injury,picture,imgsvr,killturn,makelimit,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error("processAI01 ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    // 입력된 턴이 있으면 그것 실행
    if($general['turn0'] != "00000000000000") {
        return;
    }

    $query = "select city,region,nation,level,path,rate,gen1,gen2,gen3,pop,supply,front from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("processAI02 ".MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,level,tech,gold,rice,rate,type,color,name,war from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error("processAI03 ".MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $coreCommand = array();
    if($general['level'] >= 5) {
        $query = "select l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error("processAI03 ".MYDB_error($connect),"");
        $coreCommand = MYDB_fetch_array($result);
    }

    $attackable = 0;
    $query = "select city from city where nation='{$general['nation']}' and supply='1' and front=1";
    $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    // 공격가능도시 있으면 1
    if($cityCount > 0) { $attackable = 1; }

    $dipState = 0;
    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term>8";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 선포중이면 1상태
    if($dipCount > 0) { $dipState = 1; }

    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term<=8";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 전쟁준비 선포중이면 2상태
    if($dipCount > 0) { $dipState = 2; }

    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term<=3";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 교전 직전이면 3상태
    if($dipCount > 0) { $dipState = 3; }

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 교전중이면 4상태
    if($dipCount > 0) { $dipState = 4; }

    //무장
    if($general['power'] >= $general['intel']) {
        $genType = 0;
        if($general['intel'] >= $general['power'] * 0.8) {  //무지장
            switch(rand() % 5) {
            case 0: case 1: case 2: case 3: $genType = 0; break;
            case 4:                         $genType = 1; break;
            }
        }
    //지장
    } else {
        $genType = 1;
        if($general['power'] >= $general['intel'] * 0.8) {  //지무장
            switch(rand() % 5) {
            case 0:                         $genType = 0; break;
            case 1: case 2: case 3: case 4: $genType = 1; break;
            }
        }
    }

    //내정장
    if($general['leader'] < 40) {
        $genType += 2;
        //$genType = 2; // 무내정장
        //$genType = 3; // 지내정장
    }

    $tech = getTechCost($nation['tech']);

    if($general['atmos'] >= 90 && $general['train'] >= 90) {
        if($general['mode'] == 0) {
            $query = "update general set mode=1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
        }
    } else {
        if($general['mode'] == 1) {
            $query = "update general set mode=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
        }
    }

    //운영자메시지 출력 하루 6번..?
    //특별 메세지 있는 경우 출력 하루 4번
    switch($admin['turnterm']) {
    case 0: $term = 1; break;
    case 1: $term = 1; break;
    case 2: $term = 2; break;
    case 3: $term = 3; break;
    case 4: $term = 6; break;
    case 5: $term = 12; break;
    case 6: $term = 30; break;
    case 7: $term = 60; break;
    }
    if($general['npcid'] == 2000 && rand()%(24*$term) < 6) {
        PushMsg(1, 0, $general['picture'], $general['imgsvr'], "{$general['name']}:", $nation['color'], $nation['name'], $nation['color'], $general['npcmsg']);
    } elseif($general['npcmsg'] != "" && rand()%(24*$term) < 3) {
        PushMsg(1, 0, $general['picture'], $general['imgsvr'], "{$general['name']}:", $nation['color'], $nation['name'], $nation['color'], $general['npcmsg']);
    }

    //재야인경우
    if($general['npc'] == 5 && $general['level'] == 0) {
        // 오랑캐는 바로 임관
        $query = "select nation from general where level=12 and npc=5 and nation not in (0{$general['nations']}0) order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
        $rulerCount = MYDB_num_rows($result);
        if($rulerCount > 0) {
            $ruler = MYDB_fetch_array($result);
            $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
        } else {
            $command = EncodeCommand(0, 0, 0, 42); //견문
        }
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI07 ".MYDB_error($connect),"");
        return;
    } elseif($general['npc'] < 5 && $general['level'] == 0) {
        switch(rand()%5) {
        //임관 40%
        case 0: case 1:
            if($admin['scenario'] == 0 || $admin['scenario'] >= 20) {
                // 가상모드엔 랜덤임관, 초반엔 부상 적은 군주 우선 70%
                if($admin['startyear']+3 > $admin['year'] && rand()%100 < 70) {
                    $query = "select nation from general where level=12 and nation not in (0{$general['nations']}0) order by injury,rand() limit 0,1";
                } else {
                    $query = "select nation from general where level=12 and nation not in (0{$general['nations']}0) order by rand() limit 0,1";
                }
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $rulerCount = MYDB_num_rows($result);
                if($rulerCount > 0 && $general['affinity'] != 999 && $general['makelimit'] == 0) {
                    $ruler = MYDB_fetch_array($result);
                    $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
                } else {
                    $command = EncodeCommand(0, 0, 0, 42); //견문
                }
            } else {
                $query = "select nation from general where level=12 and npc=0";
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $nonCount = MYDB_num_rows($result);
                $query = "select nation from general where level=12 and npc>0";
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $npcCount = MYDB_num_rows($result);
                $ratio = Util::round($npcCount / ($nonCount + $npcCount) * 100);
                $ratio = Util::round($ratio * 1.0);
                //NPC우선임관
                $query = "select nation,ABS(IF(ABS(affinity-'{$general['affinity']}')>75,150-ABS(affinity-'{$general['affinity']}'),ABS(affinity-'{$general['affinity']}'))) as npcmatch2 from general where level=12 and npc>0 and nation not in (0{$general['nations']}0) order by npcmatch2,rand() limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $rulerCount = MYDB_num_rows($result);
                if($rulerCount > 0 && $general['affinity'] != 999 && rand()%100 < $ratio && $general['makelimit'] == 0) {  // 엔국 비율대로 임관(50% : 50%)
                    $ruler = MYDB_fetch_array($result);
                    $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
                } elseif($general['affinity'] != 999 && $general['makelimit'] == 0) {  // NPC국가 없으면 유저국 임관
                    $query = "select nation from general where level=12 and npc=0 order by rand() limit 0,1";
                    $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                    $ruler = MYDB_fetch_array($result);
                    $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
                } else {
                    $command = EncodeCommand(0, 0, 0, 42); //견문
                }
            }
            break;
        case 2: case 3: //거병이나 견문 40%
            // 초반이면서 능력이 좋은놈 위주로 1%확률로 거병 (300명 재야시 2년간 약 10개 거병 예상)
            $prop = rand() % 100;
            $ratio = Util::round(($general['leader'] + $general['power'] + $general['intel']) / 3);
            if($admin['startyear']+2 > $admin['year'] && $prop < $ratio && rand()%100 < 1 && $general['makelimit'] == 0) {
                //거병
                $command = EncodeCommand(0, 0, 0, 55);
            } else {
                //견문
                $command = EncodeCommand(0, 0, 0, 42);
            }
            break;
        case 4: //이동 20%
            $paths = explode("|", $city['path']);
            $command = EncodeCommand(0, 0, $paths[rand()%count($paths)], 21);
            break;
        }
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI07 ".MYDB_error($connect),"");
        return;
    }

    $rulerCommand = 0;
    //군주가 할일
    if($general['level'] == 12) {
        //오랑캐인데 공격 못하면 바로 방랑/해산
        if($general['npc'] == 5 && $dipState == 0 && $attackable == 0) {
            //방랑군이냐 아니냐
            if($nation['level'] == 0) {
                // 해산
                $command = EncodeCommand(0, 0, 0, 56);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            } else {
                // 방랑
                $command = EncodeCommand(0, 0, 0, 47);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            }
        }
        //분기마다
        if($admin['month'] == 1 || $admin['month'] == 4 || $admin['month'] == 7 || $admin['month'] == 10) {
            //관직임명
            Promotion($general['nation'], $nation['level']);
        } elseif($admin['month'] == 12) {
            //세율
            $nation['rate'] = TaxRate($general['nation']);
            //지급율
            GoldBillRate($nation['nation'], $nation['rate'], $admin['gold_rate'], $nation['type'], $nation['gold']);
        } elseif($admin['month'] == 6) {
            //세율
            $nation['rate'] = TaxRate($general['nation']);
            //지급율
            RiceBillRate($nation['nation'], $nation['rate'], $admin['rice_rate'], $nation['type'], $nation['rice']);
        }

        //방랑군이냐 아니냐
        if($nation['level'] == 0) {
            if($admin['startyear']+2 <= $admin['year']) {
                // 해산
                $command = EncodeCommand(0, 0, 0, 56);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            } elseif($city['nation'] == 0 && ($city['level'] == 5 || $city['level'] == 6)) {
                $type = rand()%9 + 1;
                $colors = GetNationColors();
                $color = rand() % count($colors);
                $command = EncodeCommand(0, $type, $color, 46);
                $nationName = "㉿".mb_substr($general['name'], 1);
                //건국
                $query = "update general set turn0='$command',makenation='$nationName' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI08 ".MYDB_error($connect),"");
                return;
            } elseif(rand()%4 > 0) {
                //이동
                $paths = explode("|", $city['path']);
                $command = EncodeCommand(0, 0, $paths[rand()%count($paths)], 21);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            } else {
                //조달
                $command = EncodeCommand(0, 0, 0, 9);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            }
        } else {
            //외교 평시에 선포
            if($dipState == 0 && $attackable == 0) {
                //전방 체크 먼저
                SetNationFront($nation['nation']);

                $query = "select city from city where nation='{$general['nation']}' and front=1 limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI02 ".MYDB_error($connect),"");
                $frontCount = MYDB_num_rows($result);
                //근접 공백지 없을때
                if($frontCount == 0) {
                    $query = "select (sum(pop/10)+sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(pop2/10)+sum(agri2)+sum(comm2)+sum(secu2)+sum(def2)+sum(wall2))*100 as dev from city where nation='{$general['nation']}'";
                    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $devRate = MYDB_fetch_array($result);
                    //내정이 80% 이상일때
                    if($devRate['dev'] > 80) {
                        $query = "select nation from nation where level>0 order by rand()";
                        $result = MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                        $nationCount = MYDB_num_rows($result);
                        for($i=0; $i < $nationCount; $i++) {
                            $youNation = MYDB_fetch_array($result);

                            if(isClose($general['nation'], $youNation['nation'])) {
                                $command = EncodeCommand(0, 0, $youNation['nation'], 62);
                                $query = "update nation set l12turn0='$command' where nation='{$general['nation']}'";
                                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                                $rulerCommand = 1;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    // 입력된 턴이 있으면
    if(!empty($coreCommand) && ($coreCommand["l{$general['level']}turn0"] != EncodeCommand(0, 0, 0, 99))) {
        $rulerCommand = 1;
    }

    //방랑군 아니고, 입력된 턴이 없을때 수뇌부가 할일
    if($nation['level'] != 0 && $general['level'] >= 5 && $rulerCommand == 0) {
        $query = "select A.no,A.name,A.nation,B.nation from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation!='{$general['nation']}' and A.no!='{$general['no']}' order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("processAI11 ".MYDB_error($connect),"");
        $curGen = MYDB_fetch_array($result);

        if($curGen['no'] != 0) {          // 타도시에 있는 경우 국내로 발령
            if($dipState >= 3) {
                $query = "select city from city where nation='{$general['nation']}' and front=1 and supply=1 order by rand() limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                $selCity = MYDB_fetch_array($result);
                if($selCity['city'] > 0) {
                    // 발령
                    $command = EncodeCommand(0, $curGen['no'], $selCity['city'], 27);
                } else {
                    // 발령
                    $command = EncodeCommand(0, $curGen['no'], $city['city'], 27);
                }
            } else {
                // 발령
                $command = EncodeCommand(0, $curGen['no'], $city['city'], 27);
            }
            $query = "update nation set l{$general['level']}turn0='$command' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
        } elseif($dipState <= 1) {      // 평시엔 균등 발령만
            //발령, 최소장수 도시 선택, 최다장수도시의 장수 선택
            $query = "select B.city,count(*) as cnt,((B.agri+B.comm+B.secu+B.def+B.wall)/(B.agri2+B.comm2+B.secu2+B.def2+B.wall2)+(B.pop/B.pop2))/2*100 as dev from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation='{$general['nation']}' and B.supply=1 group by A.city";
            $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
            $cityCount = MYDB_num_rows($result);
            //도시 2개 이상일때만
            if($cityCount > 1) {
                $min = 500; $minCity = 0;
                $max = 0;   $maxCity = 0;
                $devCity = 0;
                for($i=0; $i < $cityCount; $i++) {
                    $curCity = MYDB_fetch_array($result);
                    if($curCity['cnt'] >= $max) { $max = $curCity['cnt']; $maxCity = $curCity['city']; }
                    if($curCity['cnt'] <= $min) { $min = $curCity['cnt']; $minCity = $curCity['city']; }
                    if($curCity['dev'] < 70) { $devCity = $curCity['city']; }    // 개발이 안된 곳 우선
                }
                if($devCity != 0) { $minCity = $devCity; }
                if($maxCity != $minCity) {
                    $query = "select no from general where city='$maxCity' and nation='{$general['nation']}' and no!='{$general['no']}' and npc>=2 limit 0,1";
                    $result = MYDB_query($query, $connect) or Error("processAI11 ".MYDB_error($connect),"");
                    $curGen = MYDB_fetch_array($result);

                    if($curGen['no'] != 0) {
                        // 발령
                        $command = EncodeCommand(0, $curGen['no'], $minCity, 27);
                        $query = "update nation set l{$general['level']}turn0='$command' where nation='{$general['nation']}'";
                        MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                    }
                    //계속 진행
                }
            }
        } else {
            // 병사있고 쌀있고 후방에 있는 장수
            $query = "select A.no from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation='{$general['nation']}' and B.front=0 and A.crew>700 and A.rice>700*{$tech} order by A.npc,A.crew desc limit 0,1";
            $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
            $selGen = MYDB_fetch_array($result);
            // 전방 도시, 30% 확률로 태수 있는 전방으로 발령
            if(rand()%100 < 30) {
                $query = "select city from city where nation='{$general['nation']}' and front=1 and supply=1 order by gen1 desc,rand() limit 0,1";
            } else {
                $query = "select city from city where nation='{$general['nation']}' and front=1 and supply=1 order by rand() limit 0,1";
            }
            $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
            $selCity = MYDB_fetch_array($result);
            if($selGen['no'] > 0 && $selCity['city'] > 0 && rand() % 100 < 80) {    // 80% 확률
                // 발령
                $command = EncodeCommand(0, $selGen['no'], $selCity['city'], 27);
            } else {
                //병사 없고 인구없는 전방에 있는 장수
                $query = "select A.no from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation='{$general['nation']}' and B.pop<40000 and B.front=1 and A.crew<700 order by A.npc,A.crew limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                $selGen = MYDB_fetch_array($result);
                // 인구많은도시
                $query = "select city from city where nation='{$general['nation']}' and supply=1 order by pop desc limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                $selCity = MYDB_fetch_array($result);
                if($selGen['no'] > 0 && $selCity['city'] > 0 && rand() % 100 < 80) {    // 80% 확률
                    // 발령
                    $command = EncodeCommand(0, $selGen['no'], $selCity['city'], 27);
                } else {
                    // 발령할 장수 없으면 몰포
                    if(rand() % 2 == 0) { $type = "gold"; $type2 = 1; }
                    else { $type = "rice"; $type2 = 2; }

                    if($nation[$type] < 1000) {  // 몰수
                        // 몰수 대상
                        $query = "select no,{$type} from general where nation='{$general['nation']}' and no!='{$general['no']}' and {$type}>3000 order by {$type} desc limit 0,1";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $SelGen = MYDB_fetch_array($result);
                        if($SelGen['no'] != 0) {
                            $amount = intdiv($SelGen[$type], 5000)*10 + 10;
                            if($amount > 100) $amount = 100;
                            // 몰수
                            $command = EncodeCommand($type2, $SelGen['no'], $amount, 24);    // 금,쌀 1000단위 몰수
                        }
                    } else {    // 포상
                        // 포상 대상
                        $query = "select no from general where nation='{$general['nation']}' and no!='{$general['no']}' and killturn>=5 order by {$type} limit 0,1";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $SelGen = MYDB_fetch_array($result);
                        if($SelGen['no'] != 0) {
                            $amount = intdiv(($nation[$type]-GameConst::$baserice), 5000)*10 + 10;
                            if($amount > 100) $amount = 100;
                            // 포상
                            $command = EncodeCommand($type2, $SelGen['no'], $amount, 23);    // 금 1000단위 포상
                        }
                    }
                }
            }
            $query = "update nation set l{$general['level']}turn0='$command' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
        }
    }

    $command = EncodeCommand(0, 0, 0, 1);
    //일반 할일
    if($general['killturn'] < 5) {
        if($general['gold'] + $general['rice'] == 0) {
            $command = EncodeCommand(0, 0, 0, 9); //조달
        } elseif($general['gold'] > $general['rice']) {
            $command = EncodeCommand(0, 1, 100, 44); //헌납
        } else {
            $command = EncodeCommand(0, 2, 100, 44); //헌납
        }
    } elseif($general['injury'] > 10) {
    // 부상 2달 이상이면 요양
        $command = EncodeCommand(0, 0, 0, 50);  //요양
    } elseif($nation['level'] == 0) {
    //방랑군일때
        if($admin['startyear']+3 <= $admin['year']) {
            $command = EncodeCommand(0, 0, 0, 45); //하야
        } else {
            switch(rand()%5) {
            case 0:
                $command = EncodeCommand(0, 0, 0, 42); break; //견문 20%
            case 1: case 2: case 3: case 4:
                $command = EncodeCommand(0, 0, 0, 9); break; //조달 80%
            }
        }
    } else {
    //국가일때
        //아국땅 아니면 귀환
        if($general['nation'] != $city['nation'] || $city['supply'] == 0) {
            $command = EncodeCommand(0, 0, 0, 28);  //귀환
            $query = "update general set turn0='$command' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
            return;
        }
        //국가 병량이 없을때 바로 헌납
        if($nation['rice'] < 2000 && $general['rice'] > 200) {
            $amount = intdiv($general['rice'] - 200, 100) + 1;
            if($amount > 20) { $amount = 20; }
            $command = EncodeCommand(0, 2, $amount, 44);  //헌납
            $query = "update general set turn0='$command' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
            return;
        }

//   R
//    ┃  ┃     공격/내정
// 700┃쌀┣━━━━┓
//    ┃팜┃        ┃
// 100┣━┫  내정  ┣━━━
//    ┃조┃        ┃쌀삼
//   0┗━┻━━━━┻━━━> G
//       100       700
        $resrc = $tech * 700;
        $target = array();
        // 평시거나 초반아니면서 공격가능 없으면서 병사 있으면 해제(25%)
        if($dipState == 0 && $isStart == 0 && $attackable == 0 && $general['crew'] > 0 && rand()% 100 < 25) {
            $command = EncodeCommand(0, 0, 0, 17);    //소집해제
        } elseif($dipState <= 1 || $isStart == 1) {
        //평시이거나 선포있어도 초반이면
            if($general['gold'] + $general['rice'] < 200) { $command = EncodeCommand(0, 0, 0, 9); } //금쌀없으면 조달9
            elseif($general['rice'] > 100 && $city['rate'] < 95) { $command = EncodeCommand(0, 0, 0, 4); } //우선 선정
            elseif($general['gold'] < 100) {                                      //금없으면 쌀팜
                $amount = intdiv(($general['rice'] - $general['gold'])/2, 100);   // 100단위
                $command = EncodeCommand(0, 1, $amount, 49);                    //팜
            } elseif($general['gold'] < 700 && $general['rice'] < 700) { $command = EncodeCommand(0, 0, 0, 1); } //금쌀되면 내정
            elseif($general['rice'] < 100) {                                      //쌀없으면 쌀삼
                $amount = intdiv(($general['gold'] - $general['rice'])/2, 100);  // 100단위
                $command = EncodeCommand(0, 2, $amount, 49);                    //삼
            } elseif($genType >= 2) { $command = EncodeCommand(0, 0, 0, 1); } //내정장일때 내정
            else {
                //현도시가 전방이면 공격 가능성 체크
                if($city['front'] > 0) {
                    //주변도시 체크
                    $paths = explode("|", $city['path']);
                    for($i=0; $i < count($paths); $i++) {
                        $query = "select city,nation from city where city='$paths[$i]'";
                        $result = MYDB_query($query, $connect) or Error("processAI20 ".MYDB_error($connect),"");
                        $targetCity = MYDB_fetch_array($result);
                        //공백지이면 타겟에 포함
                        if($targetCity['nation'] == 0) { $target[] = $targetCity['city']; }
                    }
                    if(count($target) == 0 || $isStart == 1 || $nation['war'] == 1) { $command = EncodeCommand(0, 0, 0, 1); } //공격 가능도시가 없으면 내정
                    else { $command = EncodeCommand(0, 0, $target[rand()%count($target)], 16); }  //있으면 공격
                } else {
                    //전방 아니면 내정
                    $command = EncodeCommand(0, 0, 0, 1);
                }

                if($command == EncodeCommand(0, 0, 0, 1)) {     // 공격아닌 경우
                    $query = "select city,(pop/10+agri+comm+secu+def+wall)/(pop2/10+agri2+comm2+secu2+def2+wall2)*100 as dev from city where city='{$general['city']}'";
                    $result = MYDB_query($query, $connect) or Error("processAI19 ".MYDB_error($connect),"");
                    $selCity = MYDB_fetch_array($result);

                    $sel = rand() % 10;
                    if($selCity['dev'] > 95) { $sel = 9; }
                    elseif($selCity['dev'] < 70) { $sel = 0; }
                    switch($sel) {
                    case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7: // 그대로 내정 80 %
                        $command = EncodeCommand(0, 0, 0, 1);
                        break;
                    case 8: case 9: // 저개발 도시로 워프 20%
                        //도시 선택, 30% 확률로 군사 있는 곳으로 워프
                        if(rand()%100 < 30) {
                            $query = "select city,(pop/10+agri+comm+secu+def+wall)/(pop2/10+agri2+comm2+secu2+def2+wall2)*100 as dev from city where nation='{$general['nation']}' and supply='1' order by gen2 desc,dev limit 0,1";
                        } else {
                            $query = "select city,(pop/10+agri+comm+secu+def+wall)/(pop2/10+agri2+comm2+secu2+def2+wall2)*100 as dev from city where nation='{$general['nation']}' and supply='1' order by dev limit 0,1";
                        }
                        $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                        $selCity = MYDB_fetch_array($result);
                        //이미 그 도시이거나, 그 도시도 고개발이면 내정
                        if($selCity['city'] == $general['city'] || $selCity['dev'] > 95) {
                            $command = EncodeCommand(0, 0, 0, 1);
                        } else {
                            //워프
                            $query = "update general set city='{$selCity['city']}' where no='{$general['no']}'";
                            MYDB_query($query, $connect) or Error("processAI18 ".MYDB_error($connect),"");

                            $command = EncodeCommand(0, 0, 0, 50);  //요양
                            $query = "update general set turn0='$command' where no='{$general['no']}'";
                            MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
                            return;
                        }
                        break;
                    }

//     ┃        ┃
//     ┃  쌀팜  ┃ 공격
//     ┃        ┃
// 700t┣━━━━╋━━━━━━━━━
//     ┃내조  ↗┃
//     ┃    ↗  ┃
//     ┣━┓내조┃  쌀삼
//     ┃**┃    ┃
//   0 ┗━┻━━━━━━━> G
//              700t

                } else {                // 공격인 경우
                    if($general['crew'] < 700 && $general['gold'] >= $resrc && $general['rice'] >= $resrc) { //자원되고, 병사없을때
                        if($city['pop'] > 40000) { $command = EncodeCommand(0, 0, 0, 11); }
                        else { $command = EncodeCommand(0, 0, 0, 1); }
                    } elseif($general['rice'] < $resrc && $general['rice'] <= $general['gold']) {
                        //금이 더 많으면 매매
                        $amount = intdiv(($general['gold'] - $general['rice']) / 2, 100);  // 100단위
                        if($amount > 0) { $command = EncodeCommand(0, 2, $amount, 49); }//삼
                        else { $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1); }   // 내정, 조달
                    } elseif($general['gold'] < $resrc && $general['rice'] > $general['gold']) {
                        //쌀이 더 많으면 매매
                        $amount = intdiv(($general['rice'] - $general['gold']) / 2, 100);  // 100단위
                        if($amount > 0) { $command = EncodeCommand(0, 1, $amount, 49); }//팜
                        else { $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1); }   // 내정, 조달
                    //자원, 병사 모두 충족
                    } elseif($general['crew'] >= 700 && $general['train'] < 90) {
                        $command = EncodeCommand(0, 0, 0, 13);  //훈련
                    } elseif($general['crew'] >= 700 && $general['atmos'] < 90) {
                        $command = EncodeCommand(0, 0, 0, 14);  //사기진작
                    } else {
                        //공격
                        //$command = $target[rand()%count($target)] * 100 + 16;   //있으면 공격
                    }
                }
            }
        } else {
//     R
//     ┃  ┃
//     ┃쌀┃
//     ┃팜┃ 공격
// 700t┣━╋━━━━━
//     ┃조┃ 쌀삼
//    0┗━┻━━━━━> G
//        700t

        //전시일때
            if($general['gold'] + $general['rice'] < $resrc*2) { $command = EncodeCommand(0, 0, 0, 9); } //금쌀없으면 조달
            elseif($general['rice'] > $resrc && $city['rate'] < 95 && $city['front'] == 0) { $command = EncodeCommand(0, 0, 0, 4); }  // 우선 선정
            elseif($general['rice'] > $resrc && $city['rate'] < 50 && $city['front'] == 1) { $command = EncodeCommand(0, 0, 0, 4); }  // 우선 선정
            elseif($general['gold'] < $resrc) {                                   // 금없으면 쌀팜
                $amount = intdiv(($general['rice'] - $general['gold'])/2, 100);   // 100단위
                if($amount > 0) { $command = EncodeCommand(0, 1, $amount, 49); }// 팜
                else { $command = EncodeCommand(0, 0, 0, 9); }                  // 조달
            } elseif($general['rice'] < $resrc) {                                 // 쌀없으면 쌀삼
                $amount = intdiv(($general['gold'] - $general['rice'])/2, 100);   // 100단위
                if($amount > 0) { $command = EncodeCommand(0, 2, $amount, 49); }// 팜
                else { $command = EncodeCommand(0, 0, 0, 9); }                  // 조달
            } elseif($genType >= 2) { $command = EncodeCommand(0, 0, 0, 1); } //내정장일때 내정
            elseif($general['crew'] < 700 && $general['gold'] >= $resrc && $general['rice'] >= $resrc) {
                $query = "select no from general where nation='{$general['nation']}'";
                $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                $genCount = MYDB_num_rows($result);

                $query = "select no from general where nation='{$general['nation']}' and city='{$general['city']}'";
                $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                $genCount2 = MYDB_num_rows($result);

                $query = "select sum(pop) as sum from city where nation='{$general['nation']}' and supply='1'";
                $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                $sumCity = MYDB_fetch_array($result);
                // 현도시 인구 비율
                $ratio  = Util::round($city['pop'] / $sumCity['sum'] * 100);
                // 현도시 장수 비율
                $ratio2 = Util::round($genCount2 / $genCount * 100);
                $ratio3 = rand() % 100;
                // 전체 인구 대비 확률로 현지에서 징병
                if($city['pop'] > 40000 && 100 + $ratio - $ratio2 > $ratio3) {
                    $command = EncodeCommand(0, 0, 0, 11);  //인구 되면 징병
                } else {
                    // 인구 안되면 4만 이상인 도시로 워프
                    $query = "select city from city where nation='{$general['nation']}' and pop>40000 and supply='1' order by rand() limit 0,1";
                    $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                    $cityCount = MYDB_num_rows($result);
                    if($cityCount > 0) {
                        $selCity = MYDB_fetch_array($result);
                        //워프
                        $query = "update general set city='{$selCity['city']}' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI18 ".MYDB_error($connect),"");

                        $command = EncodeCommand(0, 0, 0, 50);  //요양
                        $query = "update general set turn0='$command' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
                        return;
                    } else {
                        $command = EncodeCommand(0, 0, 0, 7);  //인구 안되면 정장
                    }
                }
            } elseif($general['crew'] >= 700 && $general['train'] < 90) {
                if($general['atmos'] >= 90 && $general['train'] >= 60 && $general['mode'] == 0) {
                    $query = "update general set mode=1 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
                }
                $command = EncodeCommand(0, 0, 0, 13);  //훈련
            } elseif($general['crew'] >= 700 && $general['atmos'] < 90) {
                if($general['atmos'] >= 60 && $general['train'] >= 90 && $general['mode'] == 0) {
                    $query = "update general set mode=1 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
                }
                $command = EncodeCommand(0, 0, 0, 14);  //사기진작
            } elseif($dipState <= 3) {
                $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1);   // 준비는 됐으나 아직 선포중이면 내정, 조달
            } else {
                //공격 & 내정
                $paths = explode("|", $city['path']);
                for($i=0; $i < count($paths); $i++) {
                    $query = "select city,nation from city where city='$paths[$i]'";
                    $result = MYDB_query($query, $connect) or Error("processAI21 ".MYDB_error($connect),"");
                    $targetCity = MYDB_fetch_array($result);
                    //소유국이 있는 경우
                    if($targetCity['nation'] != 0 && $targetCity['nation'] != $general['nation']) {
                        $query = "select state from diplomacy where me='{$general['nation']}' and you='{$targetCity['nation']}'";
                        $dipResult = MYDB_query($query, $connect) or Error("processAI22 ".MYDB_error($connect),"");
                        $dip = MYDB_fetch_array($dipResult);
                        //전쟁중인 국가이면 타겟에 포함
                        if($dip['state'] == 0) $target[] = $targetCity['city'];
                    }
                }
                if(count($target) == 0) {
                    //전방 도시 선택, 30% 확률로 태수 있는 전방으로 워프
                    if(rand()%100 < 30) {
                        $query = "select city from city where nation='{$general['nation']}' and supply='1' and front=1 order by gen1 desc,rand() limit 0,1";
                    } else {
                        $query = "select city from city where nation='{$general['nation']}' and supply='1' and front=1 order by rand() limit 0,1";
                    }
                    $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                    $cityCount = MYDB_num_rows($result);
                    if($cityCount == 0) {
                        //도시 수, 랜덤(상위 20%) 선택, 저개발 도시 선택
                        $query = "select city from city where nation='{$general['nation']}' and supply='1'";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $cityCount = MYDB_num_rows($result);
                        $citySelect = rand() % (Util::round($cityCount/5) + 1);

                        $query = "select city,(def+wall)/(def2+wall2) as dev from city where nation='{$general['nation']}' and supply='1' order by dev limit {$citySelect},1";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $selCity = MYDB_fetch_array($result);
                    } else {
                        $selCity = MYDB_fetch_array($result);
                    }

                    if($general['city'] != $selCity['city']) {
                        //워프
                        $query = "update general set city='{$selCity['city']}' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI18 ".MYDB_error($connect),"");

                        $command = EncodeCommand(0, 0, 0, 50);  //요양
                        $query = "update general set turn0='$command' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
                        return;
                    } else {
                        $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1); //공격 가능도시가 없고 워프도 안되면 내정, 조달
                    }
                } elseif($nation['war'] == 1) {
                    //전금이면 내정, 조달
                    $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1);   //내정, 조달
                } else { $command = EncodeCommand(0, 0, $target[rand()%count($target)], 16); }  //있으면 공격
            }
        }
    }

    switch($command) {
    case "00000000000001": //내정
        SetDevelop($genType, $general['no'], $general['city'], $nation['tech']);
        return;
    case "00000000000011": //징병
        $query = "select region from city where nation='{$general['nation']}' order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
        $selRegion = MYDB_fetch_array($result);

        $selCity['city'] = 0;
        // 90% 확률로 이민족 또는 특성병
        if(rand()%100 < 90) {
            $query = "select city from city where nation='{$general['nation']}' and (level='4' or level='8') order by rand() limit 0,1";
            $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
            $selCity = MYDB_fetch_array($result);
        }
        // 특병 없으면 원래대로
        if($selCity['city'] == 0) {
            $selCity['city'] = $general['city'];
        }
        SetCrew($general['no'], $general['personal'], $general['gold'], $general['leader'], $genType, $nation['tech'], $selRegion['region'], $selCity['city'], $general['dex0'], $general['dex10'], $general['dex20'], $general['dex30'], $general['dex40']);
        return;
    default:
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
        return;
    }
}
//종전하기, 지급율
//$command = $fourth * 100000000 + $type * 100000 + $crew * 100 + 11;

function Promotion($nation, $level) {
    $db = DB::db();
    $connect=$db->get();

    $lv = getNationChiefLevel($level);

    $query = "select scenario,killturn from game limit 1";
    $result = MYDB_query($query, $connect) or Error("processAI00 ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //우선 수뇌 해제 (승상 뺴고)
    $query = "update general set level=1 where level<11 and level>4 and nation='$nation'";
    MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");

    //유저 후보 선택
    $query = "select no from general where nation='$nation' and npc<2 and level=1 and belong>=3 and killturn>='{$admin['killturn']}' order by rand() limit 0,1";
    $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
    $userCandidate = MYDB_fetch_array($result);
    // 유저수뇌 안함
    //$userCandidate['no'] = 0;
    
    //NPC 후보 선택
    $query = "select no from general where nation='$nation' and npc>=2 and level=1 order by intel desc limit 0,1";
    $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
    $npcCandidate = MYDB_fetch_array($result);

    //현재 참모
    $query = "select no,intel,npc,killturn from general where nation='$nation' and level=11";
    $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
    $level11 = MYDB_fetch_array($result);

    //공석이거나 삭턴 유저 참모인 경우
    if($level11['no'] == 0 || ($level11['npc'] < 2 && $level11['killturn'] < $admin['killturn'])) {
        if($userCandidate['no'] > 0) {
            //기존 참모 해임
            $query = "update general set level=1 where nation='$nation' and level=11";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
            //유저 후보 있으면 임명
            $query = "update general set level=11 where no='{$userCandidate['no']}'";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        } elseif($npcCandidate['no'] > 0) {
            //기존 참모 해임
            $query = "update general set level=1 where nation='$nation' and level=11";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
            //NPC 후보 있으면 임명
            $query = "update general set level=11 where no='{$npcCandidate['no']}'";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        }
    } elseif($level11['npc'] >= 2 && $userCandidate['no'] > 0) {
        //NPC 참모인데 삭턴 아닌 유저장이 있는 경우
        //기존 참모 해임
        $query = "update general set level=1 where nation='$nation' and level=11";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        //유저 후보 있으면 임명
        $query = "update general set level=11 where no='{$userCandidate['no']}'";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
    }

    for($i=10; $i >= $lv; $i-=2) {
        $i1 = $i;   $i2 = $i - 1;
        //무관임명
        $query = "select no from general where nation='$nation' and level=1 order by power desc limit 0,1";
        $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
        $level = MYDB_fetch_array($result);
        $query = "update general set level={$i1} where no='{$level['no']}'";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        //문관임명
        $query = "select no from general where nation='$nation' and level=1 order by intel desc limit 0,1";
        $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
        $level = MYDB_fetch_array($result);
        $query = "update general set level={$i2} where no='{$level['no']}'";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
    }
}

function TaxRate($nation) {
    $db = DB::db();
    $connect=$db->get();

    //도시
    $query = "select city from city where nation='$nation'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);

    if($cityCount == 0) {
        $query = "update nation set war=0,rate=15 where nation='$nation'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return 15;
    } else {
        $query = "select sum(pop)/sum(pop2)*100 as rate,(sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(agri2)+sum(comm2)+sum(secu2)+sum(def2)+sum(wall2))*100 as dev from city where nation='$nation'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $devRate = MYDB_fetch_array($result);

        $avg = ($devRate['rate'] + $devRate['dev']) / 2;

        if($avg > 95) $rate = 25;
        elseif($avg > 70) $rate = 20;
        elseif($avg > 50) $rate = 15;
        else $rate = 10;

        $query = "update nation set war=0,rate='$rate' where nation='$nation'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return $rate;
    }
}

function GoldBillRate($nation, $rate, $gold_rate, $type, $gold) {
    $db = DB::db();
    $connect=$db->get();

    $incomeList = getGoldIncome($nation, $rate, $gold_rate, $type);
    $income = $gold + $incomeList[0] + $incomeList[1];
    $outcome = getGoldOutcome($nation, 100);    // 100%의 지급량
    $bill = intval($income / $outcome * 90); // 수입의 90% 만 지급

    if($bill < 20)  { $bill = 20; }
    if($bill > 200) { $bill = 200; }

    $query = "update nation set bill='$bill' where nation='$nation'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function RiceBillRate($nation, $rate, $rice_rate, $type, $rice) {
    $db = DB::db();
    $connect=$db->get();

    $incomeList = getRiceIncome($nation, $rate, $rice_rate, $type);
    $income = $rice + $incomeList[0] + $incomeList[1];
    $outcome = getRiceOutcome($nation, 100);    // 100%의 지급량
    $bill = intval($income / $outcome * 90); // 수입의 90% 만 지급

    if($bill < 20)  { $bill = 20; }
    if($bill > 200) { $bill = 200; }

    $query = "update nation set bill='$bill' where nation='$nation'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

