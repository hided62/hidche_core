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
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['startyear','year','month','turnterm','scenario','gold_rate','rice_rate']);
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
    if(DecodeCommand($general['turn0'])[0] != 0) {
        return;
    }

    $query = "select city,region,nation,level,path,rate,gen1,gen2,gen3,pop,supply,front from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("processAI02 ".MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,level,tech,gold,rice,rate,type,color,name,war from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error("processAI03 ".MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result)??[
        'nation'=>0,
        'color'=>'#000000',
        'name'=>'재야',
        'level'=>0,
        'gold'=>0,
        'rice'=>0,
        'tech'=>0,
    ];

    $coreCommand = array();
    if($general['level'] >= 5) {
        $query = "select l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error("processAI03 ".MYDB_error($connect),"");
        $coreCommand = MYDB_fetch_array($result);
    }

    $cityCount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i AND supply=1 AND front=1', $general['nation']);
    // 공격가능도시 있으면 1
    $attackable = $cityCount > 0;

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

    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term<=5";
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
    $resrc = $tech * 700;//XXX: 왜 700이지?

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
    $term = $admin['turnterm'];
    if($general['npcmsg'] && Util::randBool($term / (6*60))) {
        $src = new MessageTarget(
            $general['no'], 
            $general['name'],
            $general['nation'],
            $nation['name'],
            $nation['color'],
            GetImageURL($general['imgsvr'], $general['picture'])
        );
        $msg = new Message(
            Message::MSGTYPE_PUBLIC, 
            $src,
            $src,
            $general['npcmsg'],
            new \DateTime(),
            new \DateTime('9999-12-31'),
            []
        );
        $msg->send();
    }

    //재야인경우
    if($general['npc'] == 5 && $general['level'] == 0) {
        // 오랑캐는 바로 임관

        $rulerNation = $db->queryFirstField('SELECT nation FROM general WHERE `level`=12 AND npc=5 and nation not in %li ORDER BY RAND() limit 1', Json::decode($general['nations']));
        
        if($ruler) {
            $command = EncodeCommand(0, 0, $rulerNation, 25); //임관
        } else {
            $command = EncodeCommand(0, 0, 0, 42); //견문
        }
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI07 ".MYDB_error($connect),"");
        return;
    } elseif($general['npc'] < 5 && $general['level'] == 0) {
        switch(Util::choiceRandomUsingWeight([11.4, 40, 20, 28.6])) {
        //임관 10%
        case 0:

            $available = true;

            if($admin['startyear']+3 > $admin['year']){
                //초기 임관 기간에서는 국가가 적을수록 임관 시도가 적음
                $nationCnt = $db->queryFirstField('SELECT count(nation) FROM nation');
                $notFullNationCnt = $db->queryFirstField('SELECT count(nation) FROM nation WHERE gennum < %i', GameConst::$initialNationGenLimit);
                if($nationCnt == 0 || $notFullNationCnt == 0){
                    $available = false;
                }
                else if(Util::randBool(pow(1 / $nationCnt / pow($notFullNationCnt, 3), 1/4))){
                    //국가가 1개일 경우에는 '임관하지 않음'
                    $available = false;
                }
            }

            if($general['affinity'] == 999 || !$available){
                $command = EncodeCommand(0, 0, 0, 42); //견문
            }
            else{
                //랜임 커맨드 입력.
                $command = EncodeCommand(0, 0, 99, 25); //임관
            }
            break;
        case 1: //거병이나 견문 40%
            // 초반이면서 능력이 좋은놈 위주로 1.4%확률로 거병
            $prop = Util::randF() * (GameConst::$defaultStatNPCMax + GameConst::$chiefStatMin) / 2;
            $ratio = ($general['leader'] + $general['power'] + $general['intel']) / 3;
            if($admin['startyear']+2 > $admin['year'] && $prop < $ratio && Util::randBool(0.014) && $general['makelimit'] == 0) {
                //거병
                $command = EncodeCommand(0, 0, 0, 55);
            } else {
                //견문
                $command = EncodeCommand(0, 0, 0, 42);
            }
            break;
        case 2: //이동 20%
            $paths = explode('|', $city['path']);
            $command = EncodeCommand(0, 0, $paths[rand()%count($paths)], 21);
            break;
        default:
            $command = EncodeCommand(0, 0, 0, 42);
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
        if($general['npc'] == 5 && $dipState == 0 && !$attackable) {
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
                $paths = explode('|', $city['path']);
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

            do{
                if($dipState != 0){
                    break;
                }
                if($attackable){
                    break;
                }

                SetNationFront($nation['nation']);

                $frontCount = $db->queryFirstField('SELECT count(city) FROM city WHERE nation=%i AND front=1', $general['nation']);
                if($frontCount > 0){
                    break;
                }

                $devRate = $db->queryFirstField('SELECT (sum(pop/10)+sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(pop2/10)+sum(agri2)+sum(comm2)+sum(secu2)+sum(def2)+sum(wall2)) from city where nation=%i', $general['nation']);
                if($devRate < 0.8){
                    break;
                }

                $nations = [];
                foreach ($db->queryAllLists('SELECT nation, power FROM nation WHERE level>0') as [$youNationID, $youNationPower]) {
                    if(!isNeighbor($general['nation'], $youNationID)){
                        continue;
                    }
                    $nations[$youNationID] = 1/sqrt($youNationPower+1);
                }
                if(!$nations){
                    break;
                }
                $youNationID = Util::choiceRandomUsingWeight($nations);
                $command = EncodeCommand(0, 0, $youNationID, 62);
                $db->update('nation', [
                    'l12turn0'=>$command
                ], 'nation=%i', $general['nation']);
                $rulerCommand = 1;
            }while(false);
        }
    }

    // 입력된 턴이 있으면
    if(!empty($coreCommand) && ($coreCommand["l{$general['level']}turn0"] != EncodeCommand(0, 0, 0, 99))) {
        $rulerCommand = 1;
    }

    //방랑군 아니고, 입력된 턴이 없을때 수뇌부가 할일
    if($nation['level'] != 0 && $general['level'] >= 5 && $rulerCommand == 0) {
        $command = NPCStaffWork($general, $nation, $dipState);
        if($command){
            $db->update('nation', [
                "l{$general['level']}turn0"=>$command
            ], 'nation=%i', $general['nation']);
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

        if($general['leader'] < 40){
            //무지장인데
            
            if(
                (($nation['rice'] - GameConst::$baserice) * 3 <= $general['rice'] && $general['rice'] >= $resrc + 2100) ||
                ($general['rice'] >= 11000)
            ){
                //쌀을 많이 들고 있다면
                $amount = $general['rice'] * 0.9;
                $amount = intdiv(Util::valueFit($amount, $resrc + 1000, 10000), 100);

                $command = EncodeCommand(0, 2, $amount, 44);  //헌납
                $db->update('general', [
                    'turn0'=>$command
                ], 'no=%i',$general['no']);
                return;
            }

            if(
                (($nation['gold'] - GameConst::$basegold) * 3 <= $general['gold'] && $general['gold'] >= $resrc + 2100) ||
                ($general['gold'] >= 12000)
            ){
                //금을 많이 들고 있다면
                $amount = $general['gold'] * 0.9;
                $amount = intdiv(Util::valueFit($amount, $resrc + 1000, 10000), 100);
                
                $command = EncodeCommand(0, 1, $amount, 44);  //헌납
                $db->update('general', [
                    'turn0'=>$command
                ], 'no=%i',$general['no']);
                return;
            }
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
// 500┃쌀┣━━━━┓
//    ┃팜┃        ┃
// 100┣━┫  내정   ┣━━━
//    ┃조┃        ┃쌀삼
//   0┗━┻━━━━┻━━━> G
//       100       500
        
        $target = array();
        // 평시거나 초반아니면서 공격가능 없으면서 병사 있으면 해제(25%)
        if($dipState == 0 && $isStart == 0 && !$attackable && $general['crew'] > 0 && rand()% 100 < 25) {
            $command = EncodeCommand(0, 0, 0, 17);    //소집해제
        } elseif($dipState <= 1 || $isStart == 1) {
        //평시이거나 선포있어도 초반이면
            if($general['gold'] + $general['rice'] < 200) { $command = EncodeCommand(0, 0, 0, 9); } //금쌀없으면 조달9
            elseif($general['rice'] > 100 && $city['rate'] < 95) { $command = EncodeCommand(0, 0, 0, 4); } //우선 선정
            elseif($general['gold'] < 100) {                                      //금없으면 쌀팜
                $amount = intdiv(($general['rice'] - $general['gold'])/2, 100);   // 100단위
                $command = EncodeCommand(0, 1, $amount, 49);                    //팜
            } elseif($general['gold'] < 500 && $general['rice'] < 500) { $command = EncodeCommand(0, 0, 0, 1); } //금쌀되면 내정
            elseif($general['rice'] < 100) {                                      //쌀없으면 쌀삼
                $amount = intdiv(($general['gold'] - $general['rice'])/2, 100);  // 100단위
                $command = EncodeCommand(0, 2, $amount, 49);                    //삼
            } elseif($genType >= 2) { $command = EncodeCommand(0, 0, 0, 1); } //내정장일때 내정
            else {
                //현도시가 전방이면 공격 가능성 체크
                if($city['front'] > 0) {
                    //주변도시 체크
                    $paths = explode('|', $city['path']);
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
                    if($general['crew'] < 1000 && $general['gold'] >= $resrc && $general['rice'] >= $resrc) { //자원되고, 병사없을때
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
                    } elseif($general['crew'] >= 1000 && $general['train'] < 90) {
                        $command = EncodeCommand(0, 0, 0, 13);  //훈련
                    } elseif($general['crew'] >= 1000 && $general['atmos'] < 90) {
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
//1000t┣━╋━━━━━
//     ┃조┃ 쌀삼
//    0┗━┻━━━━━> G
//       1000t

        //전시일때
            if($general['gold'] + $general['rice'] < $resrc*2) { $command = EncodeCommand(0, 0, 0, 9); } //금쌀없으면 조달
            elseif($general['rice'] > $resrc && $city['rate'] < 95 && $city['front'] == 0) { $command = EncodeCommand(0, 0, 0, 4); }  // 우선 선정
            elseif($general['rice'] > $resrc && $city['rate'] < 50 && $city['front'] == 1) { $command = EncodeCommand(0, 0, 0, 4); }  // 우선 선정
            elseif($general['gold'] < $resrc || ($general['gold'] < $resrc *2 && $general['rice'] > $resrc * 6)) {                                   // 금없으면 쌀팜
                $amount = intdiv(($general['rice'] - $general['gold'])/2, 100);   // 100단위
                if($amount > 0) { $command = EncodeCommand(0, 1, $amount, 49); }// 팜
                else { $command = EncodeCommand(0, 0, 0, 9); }                  // 조달
            } elseif($general['rice'] < $resrc || ($general['rice'] < $resrc *2 && $general['gold'] > $resrc * 6)) {                                 // 쌀없으면 쌀삼
                $amount = intdiv(($general['gold'] - $general['rice'])/2, 100);   // 100단위
                if($amount > 0) { $command = EncodeCommand(0, 2, $amount, 49); }// 팜
                else { $command = EncodeCommand(0, 0, 0, 9); }                  // 조달
            } elseif($genType >= 2) { $command = EncodeCommand(0, 0, 0, 1); } //내정장일때 내정
            elseif($general['crew'] < 1000 && $general['gold'] >= $resrc && $general['rice'] >= $resrc) {
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
            } elseif($general['crew'] >= 1000 && $general['train'] < 90) {
                if($general['atmos'] >= 90 && $general['train'] >= 60 && $general['mode'] == 0) {
                    $query = "update general set mode=1 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
                }
                $command = EncodeCommand(0, 0, 0, 13);  //훈련
            } elseif($general['crew'] >= 1000 && $general['atmos'] < 90) {
                if($general['atmos'] >= 60 && $general['train'] >= 90 && $general['mode'] == 0) {
                    $query = "update general set mode=1 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
                }
                $command = EncodeCommand(0, 0, 0, 14);  //사기진작
            } elseif($dipState <= 3) {
                $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1);   // 준비는 됐으나 아직 선포중이면 내정, 조달
            } else {
                //공격 & 내정
                $paths = explode('|', $city['path']);
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
    case EncodeCommand(0, 0, 0, 1): //내정
        SetDevelop($genType, $general['no'], $general['city'], $nation['tech']);
        return;
    case EncodeCommand(0, 0, 0, 11): //징병
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


function NPCStaffWork($general, $nation, $dipState){
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['startyear','year','month','turnterm','scenario','gold_rate','rice_rate', 'develcost']);

    $nationCities = [];
    $frontCitiesID = [];
    $frontImportantCitiesID = [];
    $supplyCitiesID = [];
    $backupCitiesID = [];

    $tech = getTechCost($nation['tech']);
    
    foreach ($db->query('SELECT * FROM city WHERE nation = %i', $general['nation']) as $nationCity) {
        $nationCity['generals'] = [];
        $cityID = $nationCity['city'];
        $dev = 
            ($nationCity['agri'] + $nationCity['comm'] + $nationCity['secu'] + $nationCity['def'] + $nationCity['wall'])/
            ($nationCity['agri'] + $nationCity['comm'] + $nationCity['secu'] + $nationCity['def'] + $nationCity['wall']);
        $dev += $nationCity['pop'] / $nationCity['pop2'];
        $dev /= 50;

        $nationCity['dev'] = $dev;

        $nationCities[$cityID] = $nationCity;
        
        if($nationCity['supply']){
            $supplyCitiesID[] = $cityID;
            if($nationCity['front']){
                $frontCitiesID[] = $cityID;
                if($nationCity['gen1']){
                    $frontImportantCitiesID[] = $cityID;
                }
            }
            else{
                $backupCitiesID[] = $cityID;
            }
        }
    }
    Util::shuffle_assoc($nationCities);
    shuffle($frontCitiesID);
    shuffle($supplyCitiesID);

    $nationGenerals = [];
    $lostGeneralsID = [];

    $userGeneralsID = [];
    $npcWarGeneralsID = [];
    $npcCivilGeneralsID = [];


    $commandList = [];

    foreach($db->query('SELECT `no`, nation, city, npc, `gold`, `rice`, leader, `power`, intel, killturn, crew, train, atmos, `level` FROM general WHERE nation = %i', $general['nation']) as $nationGeneral) {
        $cityID = $nationGeneral['city'];
        $generalID = $nationGeneral['no'];

        if($generalID == $general['no']){
            continue;
        }

        if(key_exists($cityID, $nationCities)){
            $nationCities[$cityID]['generals'][] = $generalID;
            if(!$nationCities[$cityID]['supply']){
                $lostGeneralsID[] = $generalID;    
            }
        }
        else{
            $lostGeneralsID[] = $generalID;
        }

        if($nationGeneral['npc']<2 && $nationGeneral['killturn'] >= 5){
            $userGeneralsID[] = $generalID;
        }
        else if($nationGeneral['leader']>=40 && $nationGeneral['killturn'] >= 5){
            $npcWarGeneralsID[] = $generalID;
        }
        else{
            //삭턴이 몇 안남은 장수는 '내정장 npc'로 처리
            $npcCivilGeneralsID[] = $generalID;
        }

        $nationGenerals[$generalID] = $nationGeneral;
    }
    Util::shuffle_assoc($nationGenerals);
    shuffle($lostGeneralsID);

    uasort($nationCities, function($lhs, $rhs){ 
        //키 순서를 지키지 않지만, 원래부터 random order를 목표로 하므로 크게 신경쓰지 않는다.
        return count($lhs['generals']) - count($rhs['generals']);
    });


    //타 도시에 있는 '유저장' 발령
    foreach($lostGeneralsID as $lostGeneralID){
        $lostGeneral = $nationGenerals[$lostGeneralID];
        if($lostGeneral['npc'] < 2){
            if($dipState >= 3 && $frontCitiesID){
                $selCityID = Util::choiceRandom($frontCitiesID);
            }
            else{
                $selCityID = Util::choiceRandom($supplyCitiesID);
            }
            $commandList[EncodeCommand(0, $lostGeneralID, $selCityID, 27)] = 200;
        }
    }

    
    $resBaseType = [['gold', 1], ['rice', 2]];
    [$resName, $resType] = Util::choiceRandom($resBaseType);

    usort($userGeneralsID, function($lhs, $rhs) use ($nationGenerals, $resName){
        return $nationGenerals[$lhs][$resName] - $nationGenerals[$rhs][$resName];
    });

    usort($npcWarGeneralsID, function($lhs, $rhs) use ($nationGenerals, $resName){
        return $nationGenerals[$lhs][$resName] - $nationGenerals[$rhs][$resName];
    });

    usort($npcCivilGeneralsID, function($lhs, $rhs) use ($nationGenerals, $resName){
        return $nationGenerals[$lhs][$resName] - $nationGenerals[$rhs][$resName];
    });

    $avgUserRes = 0;
    foreach ($userGeneralsID as $id){
        $avgUserRes += $nationGenerals[$id][$resName];
    }
    $avgUserRes /= max(1, count($userGeneralsID));

    $avgNpcWarRes = 0;
    foreach ($npcWarGeneralsID as $id){
        $avgNpcWarRes += $nationGenerals[$id][$resName];
    }
    $avgNpcWarRes /= max(1, count($npcWarGeneralsID));

    $avgNpcCivilRes = 0;
    foreach ($npcCivilGeneralsID as $id){
        $avgNpcCivilRes += $nationGenerals[$id][$resName];
    }
    $avgNpcCivilRes /= max(1, count($npcCivilGeneralsID));
    


    //금쌀이 부족한 '유저장' 먼저 포상
    while ($nation[$resName] > ($resName=='gold'?1:2)*3000 && $userGeneralsID) {
        $isWarUser = null;
        
        foreach($userGeneralsID as $userGeneralID){
            $compUser = $nationGenerals[$userGeneralID];
            if($compUser['leader'] >= 50){
                $isWarUser = true;
                break;
            }
            if(Util::randBool(0.2)){
                $isWarUser = false;
                break;
            }
        }

        if($isWarUser === null){
            break;
        }

        $compRes = $compUser[$resName];

        $work = false;
        if(!$isWarUser){
            $work = false;
        } else if ($compRes < $avgNpcWarRes*3) {
            $work = true;
        } elseif ($compRes < $avgNpcCivilRes * 4) {
            $work = true;
        }
        
        if((($isWarUser || $resName == 'gold') && $compUser[$resName] < 21000) || ($compUser[$resName] < 5000)){
            if($work){
                $amount = min(100, intdiv(($nation[$resName]-($resName=='rice'?(GameConst::$baserice):(GameConst::$basegold))), 3000)*10 + 10);
                $commandList[EncodeCommand($resType, $userGeneralsID[0], $amount, 23)] = 10;    // 금,쌀 1000단위 포상
            }
            else{
                $amount = min(100, intdiv(($nation[$resName]-($resName=='rice'?(GameConst::$baserice):(GameConst::$basegold))), 5000)*10 + 10);
                $commandList[EncodeCommand($resType, $userGeneralsID[0], $amount, 23)] = 1;    // 금,쌀 1000단위 포상
            }
            
        }
        break;
    }

    $minRes = $admin['develcost'] * 24 * $tech;

    if($nation[$resName] < ($resName=='gold'?1:2)*3000) {  // 몰수
        // 몰수 대상
        $compUser = $userGeneralsID?$nationGenerals[end($userGeneralsID)]:null;
        $compNpcWar = $npcWarGeneralsID?$nationGenerals[end($npcWarGeneralsID)]:null; 
        $compNpcCivil = $npcCivilGeneralsID?$nationGenerals[end($npcCivilGeneralsID)]:null;
        
        $compUserRes = $compUser[$resName]??0;
        $compNpcWarRes = $compNpcWar[$resName]*5??0;
        $compNpcCivilRes = $compNpcCivil[$resName]*10??0;

        [$compRes, $compGenID] = max(
            [$compNpcCivilRes, $compNpcCivil['no']??null],
            [$compNpcWarRes, $compNpcWar['no']??null],
            [$compUserRes, $compUser['no']??null]
        );

        if($compGenID){
            $targetGeneral = $nationGenerals[$compGenID];
            if($compGenID === ($compNpcCivil['no']??null)){
                $amount = intdiv($targetGeneral[$resName] - $minRes * 3, 100);
            }
            else{
                $amount = min(100, intdiv($targetGeneral[$resName], 5000)*10 + 10);
            }
            
            if($amount > 0){
                $commandList[EncodeCommand($resType, $compGenID, $amount, 24)] = 3;
            }
            
        }
    } else{    // 포상
        $compNpcWar = $npcWarGeneralsID?$nationGenerals[$npcWarGeneralsID[0]]:null; 
        $compNpcCivil = $npcCivilGeneralsID?$nationGenerals[$npcCivilGeneralsID[0]]:null;

        if($compNpcWar && $compNpcWar[$resName] < 21000){
            $amount = min(100, intdiv(($nation[$resName]-($resName=='rice'?(GameConst::$baserice):(GameConst::$basegold))), 5000)*10 + 10);
            $commandList[EncodeCommand($resType, $compNpcWar['no'], $amount, 23)] = 3;
        }
        if($compNpcCivil && $compNpcCivil[$resName] < $minRes){
            $amount = intdiv($minRes+99, 100);
            $commandList[EncodeCommand($resType, $compNpcCivil['no'], $amount, 23)] = 2;
        }
    }

    //고립 도시 장수 발령
    foreach($lostGeneralsID as $lostGeneralID){
        $lostGeneral = $nationGenerals[$lostGeneralID];
        if($lostGeneral['npc']<2){
            //고립 유저 장수는 이미 세팅했음
            continue;
        }
        if($dipState >= 3 && $frontCitiesID){
            $selCityID = Util::choiceRandom($frontCitiesID);
        }
        else{
            $selCityID = Util::choiceRandom($supplyCitiesID);
        }
        //고립된 장수가 많을 수록 발령 확률 증가
        $commandList[EncodeCommand(0, $lostGeneralID, $selCityID, 27)] = sqrt(count($lostGeneralsID)) * 10;
    }

    // 평시엔 균등 발령만
    if($dipState <= 1 && count($supplyCitiesID) > 1) {
        $targetCity = null;
        $minCity = null;
        $maxCity = null;
        $maxDevCity = null;
        foreach($nationCities as $nationCity){
            if($nationCity['dev']>=95){
                continue;
            }
            if($nationCity['supply']){
                $minCity = $nationCity;
                break;
            }
            
        }

        //reverse_order T_T
        $maxCity = end($nationCities);
        if(!$minCity){
            $minCity = $maxCity;
        }
        while($maxCity['city'] !== $minCity['city']){
            if($nationCity['supply']){
                break;
            }
            $maxCity = prev($nationCities);
        }

        foreach($nationCities as $nationCity){
            if($nationCity['city'] == $maxCity['city']){
                break;
            }
            if(!$nationCity['supply']){
                continue;
            }
            if($nationCity['dev'] < 70){
                $targetCity = $nationCity;
                break;
            }
        }

        foreach ($nationCities as $nationCity) {
            if(!$nationCity['supply']){
                continue;
            }
            if(count($nationCity['generals']) == 0){
                continue;
            }
            if($maxDevCity === null || $maxDevCity['dev'] < $nationCity['dev']){
                $maxDevCity = $nationCity;
            }
        }

        if($targetCity === null || (count($targetCity['generals']) >= count($maxCity['generals']) - 1)){
            $targetCity = $minCity;
        }

        if($maxDevCity['dev'] >= 95 && $targetCity['city'] != $maxDevCity['city'] && $targetCity['dev'] <= 70){
            $targetGeneral = $nationGenerals[Util::choiceRandom($maxDevCity['generals'])];
            $commandList[EncodeCommand(0, $targetGeneral['no'], $targetCity['city'], 27)] = 2;
        }

        if(count($targetCity['generals']) < count($maxCity['generals']) - 2){
            //세명 이상 차이나야 함
            $targetGeneral = $nationGenerals[Util::choiceRandom($maxCity['generals'])];
            if($targetGeneral['npc']>=2 || $maxCity['dev'] >= 95){
                //유저장은 의도가 있을 것이므로 삽나지 않는 이상 발령 안함!
                $commandList[EncodeCommand(0, $targetGeneral['no'], $targetCity['city'], 27)] = 5;
            }
            
        }
    }

    // 병사있고 쌀있고 후방에 있는 장수
    if($frontCitiesID){
        $workRemain = 3;
        foreach($nationGenerals as $nationGeneral){
            $generalCity = $nationCities[$nationGeneral['city']]??null;
            if(!$generalCity){
                continue;
            }
            if($nationGeneral['crew'] < 2000){
                continue;
            }
            if($nationGeneral['rice'] < 700 * $tech){
                continue;
            }
            if($generalCity['front']){
                continue;
            }
            if($nationGeneral['train'] * $nationGeneral['atmos'] < 75 * 75){
                continue;
            }
    
            $score = 5;
            if($nationGeneral['npc']<2){
                $score *= 4;
            }
    
            if(Util::randBool(0.3) && $frontImportantCitiesID){
                $targetCityID = Util::choiceRandom($frontImportantCitiesID);
            }
            else{
                $targetCityID = Util::choiceRandom($frontCitiesID);
            }
            
            $command = EncodeCommand(0, $nationGeneral['no'], $targetCityID, 27);

            if($nationGeneral['npc']<2 && ($workRemain&2)){
                $workRemain ^= 2;
                $commandList[$command] = $score;
            }
            else if($nationGeneral['npc']>=2 && ($workRemain&1)){
                $workRemain ^= 1;
                $commandList[$command] = $score;
            }

            if($workRemain <= 0){
                break;
            }
        }
    }

    //병사 없고 인구없는 전방에 있는 장수
    if($frontCitiesID && $backupCitiesID){
        $workRemain = 3;
        foreach($nationGenerals as $nationGeneral){
            $generalCity = $nationCities[$nationGeneral['city']]??null;
            if(!$generalCity){                
                continue;
            }
            if($nationGeneral['crew'] >= 1000){
                continue;
            }
            if($nationGeneral['rice'] < 700 * $tech){
                continue;
            }
            if(!$generalCity['front']){
                continue;
            }
            if($generalCity['pop'] - 33000 > $nationGeneral['leader']){
                continue;
            }
    
            $score = 5;
            if($nationGeneral['npc']<2){
                $score *= 4;
            }
    
            $popTrial = 5;
            for($popTrial = 0; $popTrial < 5; $popTrial++){
                $targetCity = $nationCities[Util::choiceRandom($backupCitiesID)];
                if($targetCity['pop'] < 33000 + $nationGeneral['leader']){
                    continue;
                }
                if (Util::randBool($targetCity['pop'] / $targetCity['pop2'])) {
                    break;
                }
            }
            
            
            $command = EncodeCommand(0, $nationGeneral['no'], $targetCity['city'], 27);

            if($nationGeneral['npc']<2 && ($workRemain&2)){
                $workRemain ^= 2;
                $commandList[$command] = $score;
            }
            else if($nationGeneral['npc']>=2 && ($workRemain&1)){
                $workRemain ^= 1;
                $commandList[$command] = $score;
            }

            if($workRemain <= 0){
                break;
            }
        }
    }

    if(!$commandList)return 0;
    return Util::choiceRandomUsingWeight($commandList);
}

//종전하기, 지급율
//$command = $fourth * 100000000 + $type * 100000 + $crew * 100 + 11;

function Promotion($nation, $level) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $lv = getNationChiefLevel($level);

    $admin = $gameStor->getValues(['scenario', 'killturn']);

    //우선 수뇌 해제 (승상 뺴고)
    $query = "update general set level=1 where level<11 and level>4 and nation='$nation'";
    MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");

    $maxBelong = $db->queryFirstField('SELECT max(belong) FROM `general` WHERE nation=%i', $nation);
    $maxBelong = min($maxBelong - 1, 3);

    //유저 후보 선택
    $query = "select no from general where nation='$nation' and npc<2 and level=1 and belong>=$maxBelong and killturn>='{$admin['killturn']}' order by rand() limit 0,1";
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

