<?php
namespace sammo;

function process_23(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $genlog = [];
    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select gold,rice,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $what = $command[3];
    $who = $command[2];
    $amount = $command[1];
    $amount *= 100;    // 100~10000까지

    if($amount > 10000) { $amount = 10000; }
    if($amount < 100) { $amount = 100; }
    if($what == 1) {
        $dtype = "금";
        if($nation['gold']-GameConst::$basegold < $amount) { $amount = $nation['gold'] - GameConst::$basegold; }
    } elseif($what == 2) {
        $dtype = "쌀";
        if($nation['rice']-GameConst::$baserice < $amount) { $amount = $nation['rice'] - GameConst::$baserice; }
    } else {
        $what = 2;
        $dtype = "쌀";
        if($nation['rice']-GameConst::$baserice < $amount) { $amount = $nation['rice'] - GameConst::$baserice; }
    }

    $query = "select no,nation,level,name,gold,rice from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if(!$gen) {
        $log[] = "<C>●</>{$admin['month']}월:없는 장수입니다. 포상 실패. <1>$date</>";
    } elseif($general['no'] == $who) {
        $log[] = "<C>●</>{$admin['month']}월:자기 자신입니다. 포상 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 포상 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 포상 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 포상 실패. <1>$date</>";
    } elseif($what == 1 && $amount <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:국고가 부족합니다. 포상 실패. <1>$date</>";
    } elseif($what == 2 && $amount <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:병량이 부족합니다. 포상 실패. <1>$date</>";
    } elseif($gen['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. 포상 실패. <1>$date</>";
    } else {
        $genlog[] = "<C>●</>$dtype <C>$amount</>을 포상으로 받았습니다.";
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$gen['name']}</>에게 $dtype <C>$amount</>을 수여했습니다. <1>$date</>";

        if($what == 1) {
            $gen['gold'] += $amount;
            $query = "update general set gold='{$gen['gold']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['gold'] -= $amount;
            $query = "update nation set gold='{$nation['gold']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($what == 2) {
            $gen['rice'] += $amount;
            $query = "update general set rice='{$gen['rice']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['rice'] -= $amount;
            $query = "update nation set rice='{$nation['rice']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        $query = "update general set resturn='SUCCESS' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
    pushGenLog($gen, $genlog);
}


function process_24(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $genlog = [];
    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','scenario','startyear']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,color,gold,rice,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $what = $command[3];
    $who = $command[2];
    $amount = $command[1];
    $amount *= 100;    // 100~10000까지

    $query = "select no,nation,level,name,gold,rice,npc,picture,imgsvr from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if($amount > 10000) { $amount = 10000; }
    if($amount < 100) { $amount = 100; }
    if($what == 1) {
        $dtype = "금";
        if($gen['gold'] < $amount) { $amount = $gen['gold']; }
    } elseif($what == 2) {
        $dtype = "쌀";
        if($gen['rice'] < $amount) { $amount = $gen['rice']; }
    } else {
        $what = 2;
        $dtype = "쌀";
        if($gen['rice'] < $amount) { $amount = $gen['rice']; }
    }

    if(!$gen) {
        $log[] = "<C>●</>{$admin['month']}월:없는 장수입니다. 몰수 실패. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:현재 초반 제한중입니다. 몰수 실패. <1>$date</>";
    } elseif($general['no'] == $who) {
        $log[] = "<C>●</>{$admin['month']}월:자기 자신입니다. 몰수 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 몰수 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 몰수 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 몰수 실패. <1>$date</>";
    } elseif($gen['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. 몰수 실패. <1>$date</>";
    } else {
        if($gen['npc'] >= 2 && rand()%100 == 0) {
            switch(rand()%5) {
            case 0: $str = "몰수를 하다니... 이것이 윗사람이 할 짓이란 말입니까..."; break;
            case 1: $str = "사유재산까지 몰수해가면서 이 나라가 잘 될거라 믿습니까? 정말 이해할 수가 없군요..."; break;
            case 2: $str = "내 돈 내놔라! 내 돈! 몰수가 왠 말이냐!"; break;
            case 3: $str = "몰수해간 내 자금... 언젠가 몰래 다시 빼내올 것이다..."; break;
            case 4: $str = "몰수로 인한 사기 저하는 몰수로 얻은 물자보다 더 손해란걸 모른단 말인가!"; break;
            }

            $src = new MessageTarget(
                $gen['no'], 
                $gen['name'],
                $gen['nation'],
                $nation['name'],
                $nation['color'],
                GetImageURL($gen['imgsvr'], $gen['picture'])
            );
            $msg = new Message(
                Message::MSGTYPE_PUBLIC, 
                $src,
                $src,
                $str,
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send();
        }

        $genlog[] = "<C>●</>$dtype {$amount}을 몰수 당했습니다.";
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$gen['name']}</>에게서 $dtype <C>$amount</>을 몰수했습니다. <1>$date</>";

        if($what == 1) {
            $gen['gold'] -= $amount;
            $query = "update general set gold='{$gen['gold']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['gold'] += $amount;
            $query = "update nation set gold='{$nation['gold']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif($what == 2) {
            $gen['rice'] -= $amount;
            $query = "update general set rice='{$gen['rice']}' where no='$who'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $nation['rice'] += $amount;
            $query = "update nation set rice='{$nation['rice']}' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 경험치 상승
        $query = "update general set resturn='SUCCESS' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//        $log = checkAbility($general, $log);
    }
    pushGenLog($general, $log);
    pushGenLog($gen, $genlog);
}


function process_27(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $youlog = [];
    $date = substr($general['turntime'],11,5);

    $query = "select gold,rice,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $who = $command[2];
    $where = $command[1];

    $admin = $gameStor->getValues(['year','month']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select no,name,nation,level from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $you = MYDB_fetch_array($result);

    $query = "select name,nation,supply from city where city='$where'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    if(!$you) {
        $log[] = "<C>●</>{$admin['month']}월:없는 장수입니다. 발령 실패. <1>$date</>";
    } elseif($general['no'] == $who) {
        $log[] = "<C>●</>{$admin['month']}월:자기 자신입니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($destcity['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 도시가 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } elseif($general['nation'] != $you['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 장수가 아닙니다. <Y>{$you['name']}</> 발령 실패. <1>$date</>";
    } else {
        $josaUl = JosaUtil::pick($you['name'], '을');
        $josaRo = JosaUtil::pick($destcity['name'], '로');
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$you['name']}</>{$josaUl} <G><b>{$destcity['name']}</b></>{$josaRo} 발령했습니다. <1>$date</>";
        $youlog[] = "<C>●</><Y>{$general['name']}</>에 의해 <G><b>{$destcity['name']}</b></>{$josaRo} 발령됐습니다. <1>$date</>";

        // 발령
        $query = "update general set city='$where' where no='{$you['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 경험치 상승
        $query = "update general set resturn='SUCCESS' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//        $log = checkAbility($general, $log);
    }

    pushGenLog($general, $log);
    pushGenLog($you, $youlog);
}


function process_51(&$general) {

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $date = substr($general['turntime'],11,5);
    list($year, $month, $turnterm) = $gameStor->getValuesAsArray(['year','month','turnterm']);

    if($general['level'] < 5 || $general['nation']==0) {
        pushGenLog($general, ["<C>●</>{$month}월:수뇌부가 아닙니다. 권고 실패. <1>$date</>"]);
        return;
    }

    $supply = $db->queryFirstField('SELECT supply FROM city WHERE city=%i AND nation=%i', $general['city'], $general['nation']);
    $myTurn = $db->queryFirstField('SELECT %b FROM nation WHERE nation=%i', "l{$general['level']}turn0", $general['nation']);
    
    $command = DecodeCommand($myTurn);
    $which = $command[1];

    $srcNation = getNationStaticInfo($general['nation']);
    $destNation = getNationStaticInfo($which);

    if($destNation['nation'] == 0) {
        pushGenLog($general, ["<C>●</>{$month}월:멸망한 국가입니다. 권고 실패. <1>$date</>"]);
        return;
    } 
    
    if($supply === null) {
        pushGenLog($general, ["<C>●</>{$month}월:아국이 아닙니다. 권고 실패. <1>$date</>"]);
        return;
    } 
    
    if($supply === 0) {
        pushGenLog($general, ["<C>●</>{$month}월:고립된 도시입니다. 권고 실패. <1>$date</>"]);
        return;
    }
    
    // 상대에게 발송
    $src = new MessageTarget(
        $general['no'], 
        $general['name'], 
        $srcNation['nation'], 
        $srcNation['name'], 
        $srcNation['color'], 
        GetImageURL($general['imgsvr'], $general['picture'])
    );

    $dest = new MessageTarget(
        0,
        '',
        $destNation['nation'],
        $destNation['name'],
        $destNation['color']
    );

    $now = new \DateTime($date);
    $validUntil = new \DateTime($date);
    $validMinutes = max(30, $turnterm*3);
    $validUntil->add(new \DateInterval("PT{$validMinutes}M"));
    
    $msg = new DiplomaticMessage(
        Message::MSGTYPE_DIPLOMACY,
        $src,
        $dest,
        "{$srcNation['name']}의 항복 권고 서신",
        $now,
        $validUntil,
        ['action'=>DiplomaticMessage::TYPE_SURRENDER]
    );
    $msg->send();

    pushGenLog($general, ["<C>●</>{$month}월:<D><b>{$destNation['name']}</b></>으로 항복 권고 서신을 보냈습니다.<1>$date</>"]);
}

function process_52(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,level,gold,rice,surlimit,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $mynation = MYDB_fetch_array($result);

    $command = DecodeCommand($mynation["l{$general['level']}turn0"]);
    $rice = $command[3];
    $gold = $command[2];
    $which = $command[1];
    $rice *= 1000;
    $gold *= 1000;
    $limit = $mynation['level'] * 10000;
    
    if($gold < 0) { $gold = 0; }
    if($rice < 0) { $rice = 0; }

    $query = "select nation,name,gold,rice,surlimit from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    if($gold > $mynation['gold']-GameConst::$basegold) { $gold = $mynation['gold'] - GameConst::$basegold; }
    if($rice > $mynation['rice']-GameConst::$baserice) { $rice = $mynation['rice'] - GameConst::$baserice; }

    if($younation['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 원조 실패. <1>$date</>";
    } elseif($gold == 0 && $rice == 0) {
        $log[] = "<C>●</>{$admin['month']}월:보낼 물자가 부족합니다. 원조 실패. <1>$date</>";
    } elseif($gold < 0 || $rice < 0) {
        $log[] = "<C>●</>{$admin['month']}월:보낼 물자가 부족합니다. 원조 실패. <1>$date</>";
    } elseif($gold > $limit || $rice > $limit) {
        $log[] = "<C>●</>{$admin['month']}월:작위 제한량 이상은 보낼 수 없습니다. 원조 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 원조 실패. <1>$date</>";
    } elseif($mynation['surlimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:외교제한중입니다. 원조 실패. <1>$date</>";
    } elseif($younation['surlimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:상대국이 외교제한중입니다. 원조 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 원조 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 원조 실패. <1>$date</>";
    } else {
        // 본국 자원 감소
        $query = "update nation set gold=gold-'$gold',rice=rice-'$rice',surlimit=surlimit+12 where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 상대국 자원 증가
        $query = "update nation set gold=gold+'$gold',rice=rice+'$rice' where nation='$which'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //아국 수뇌부에게 로그 전달
        $query = "select no,name,nation from general where nation='{$general['nation']}' and level>='9'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        $josaRo = JosaUtil::pick($younation['name'], '로');
        $genlog = ["<C>●</><D><b>{$younation['name']}</b></>{$josaRo} 금<C>$gold</> 쌀<C>$rice</>을 지원했습니다."];
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
        }
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>{$josaRo} 금<C>$gold</> 쌀<C>$rice</>을 지원");
        pushNationHistory($mynation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>{$josaRo} 금<C>$gold</> 쌀<C>$rice</>을 지원");
        pushNationHistory($younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>{$josaRo}부터 금<C>$gold</> 쌀<C>$rice</>을 지원 받음");

        //상대국 수뇌부에게 로그 전달
        $query = "select no,name,nation from general where nation='$which' and level>='9'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            $genlog[0] = "<C>●</><D><b>{$mynation['name']}</b></>에서 금<C>$gold</> 쌀<C>$rice</>을 원조 했습니다.";
            pushGenLog($gen, $genlog);
        }

        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【원조】</b></><D><b>{$mynation['name']}</b></>에서 <D><b>{$younation['name']}</b></>{$josaRo} 물자를 지원합니다.";
        $log[] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>{$josaRo} 물자를 지원합니다. <1>$date</>";

        $exp = 5;
        $ded = 5;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        // 경험치 상승        // 공헌도, 명성 상승
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

//      $log = checkAbility($general, $log);
    }
    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_53(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $date = substr($general['turntime'],11,5);
    list($year, $month, $turnterm) = $gameStor->getValuesAsArray(['year','month','turnterm']);

    if($general['level'] < 5 || $general['nation']==0) {
        pushGenLog($general, ["<C>●</>{$month}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }
    
    $supply = $db->queryFirstField('SELECT supply FROM city WHERE city=%i AND nation=%i', $general['city'], $general['nation']);
    $myTurn = $db->queryFirstField('SELECT %b FROM nation WHERE nation=%i', "l{$general['level']}turn0", $general['nation']);

    $command = DecodeCommand($myTurn);
    $which = $command[1];

    $srcNation = getNationStaticInfo($general['nation']);
    $destNation = getNationStaticInfo($which);

    if($destNation['nation'] == 0) {
        pushGenLog($general, ["<C>●</>{$month}월:멸망한 국가입니다. 제의 실패. <1>$date</>"]);
        return;
    } 
    
    if($supply === null) {
        pushGenLog($general, ["<C>●</>{$month}월:아국이 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }
    
    if($supply === 0) {
        pushGenLog($general, ["<C>●</>{$month}월:고립된 도시입니다. 제의 실패. <1>$date</>"]);
        return;
    }

    // 상대에게 발송
    $src = new MessageTarget(
        $general['no'], 
        $general['name'], 
        $srcNation['nation'], 
        $srcNation['name'], 
        $srcNation['color'], 
        GetImageURL($general['imgsvr'], $general['picture'])
    );

    $dest = new MessageTarget(
        0,
        '',
        $destNation['nation'],
        $destNation['name'],
        $destNation['color']
    );

    $now = new \DateTime($date);
    $validUntil = new \DateTime($date);
    $validMinutes = max(30, $turnterm*3);
    $validUntil->add(new \DateInterval("PT{$validMinutes}M"));
    
    $msg = new DiplomaticMessage(
        Message::MSGTYPE_DIPLOMACY,
        $src,
        $dest,
        "{$srcNation['name']}의 통합 제의 서신",
        $now,
        $validUntil,
        ['action'=>DiplomaticMessage::TYPE_MERGE]
    );
    $msg->send();

    $josaRo = JosaUtil::pick($destNation['name'], '로');
    pushGenLog($general, ["<C>●</>{$month}월:<D><b>{$destNation['name']}</b></>{$josaRo} 통합 제의 서신을 보냈습니다.<1>$date</>"]);
}


function process_61(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $date = substr($general['turntime'],11,5);
    list($year, $month, $turnterm) = $gameStor->getValuesAsArray(['year','month','turnterm']);

    if($general['level'] < 5 || $general['nation']==0) {
        pushGenLog($general, ["<C>●</>{$month}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }

    $supply = $db->queryFirstField('SELECT supply FROM city WHERE city=%i AND nation=%i', $general['city'], $general['nation']);
    $myTurn = $db->queryFirstField('SELECT %b FROM nation WHERE nation=%i', "l{$general['level']}turn0", $general['nation']);

    $command = DecodeCommand($myTurn);
    $which = $command[1];
    $when = Util::valueFit($command[2], 1, 20);

    $srcNation = getNationStaticInfo($general['nation']);
    $destNation = getNationStaticInfo($which);

    if($destNation['nation'] == 0) {
        pushGenLog($general, ["<C>●</>{$month}월:멸망한 국가입니다. 제의 실패. <1>$date</>"]);
        return;
    } 
    
    if($supply === null) {
        pushGenLog($general, ["<C>●</>{$month}월:아국이 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }
    
    if($supply === 0) {
        pushGenLog($general, ["<C>●</>{$month}월:고립된 도시입니다. 제의 실패. <1>$date</>"]);
        return;
    }
    
    // 상대에게 발송
    $src = new MessageTarget(
        $general['no'], 
        $general['name'], 
        $srcNation['nation'], 
        $srcNation['name'], 
        $srcNation['color'], 
        GetImageURL($general['imgsvr'], $general['picture'])
    );

    $dest = new MessageTarget(
        0,
        '',
        $destNation['nation'],
        $destNation['name'],
        $destNation['color']
    );

    $now = new \DateTime($date);
    $validUntil = new \DateTime($date);
    $validMinutes = max(30, $turnterm*3);
    $validUntil->add(new \DateInterval("PT{$validMinutes}M"));

    $msg = new DiplomaticMessage(
        Message::MSGTYPE_DIPLOMACY,
        $src,
        $dest,
        "{$srcNation['name']}의 {$when}년 불가침 제의 서신",
        $now,
        $validUntil,
        [
            'action'=>DiplomaticMessage::TYPE_NO_AGGRESSION,
            'year'=>$when
        ]
    );
    $msg->send();

    pushGenLog($general, ["<C>●</>{$month}월:<D><b>{$destNation['name']}</b></>으로 불가침 제의 서신을 보냈습니다.<1>$date</>"]);
}

function process_62(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','startyear']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,l{$general['level']}turn0,color from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name,color from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $younation = MYDB_fetch_array($result);

    $query = "select * from general where nation='{$younation['nation']}' and level='12'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $king = MYDB_fetch_array($result);

    //아국과의 관계
    $query = "select state from diplomacy where me='{$nation['nation']}' and you='{$younation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);
    //대상국이 외교 진행중(합병수락중,통합수락중)일때
    $query = "select state from diplomacy where me='{$younation['nation']}' and (state='3' or state='5')";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($younation['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:멸망한 국가입니다. 선포 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 선포 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 선포 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 선포 실패. <1>$date</>";
    } elseif($dip['state'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:아국과 이미 교전중입니다. 선포 실패. <1>$date</>";
    } elseif($dip['state'] == 1) {
        $log[] = "<C>●</>{$admin['month']}월:아국과 이미 선포중입니다. 선포 실패. <1>$date</>";
    } elseif($dip['state'] == 7) {
        $log[] = "<C>●</>{$admin['month']}월:아국과 불가침중입니다. 선포 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:상대국이 외교 진행중입니다. 선포 실패. <1>$date</>";
    } elseif(!isNeighbor($nation['nation'], $younation['nation'])) {
        $log[] = "<C>●</>{$admin['month']}월:인접하지 않았습니다. 선포 실패. <1>$date</>";
    } elseif($admin['year'] <= $admin['startyear']) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한 해제 2년전부터 가능합니다. 선포 실패. <1>$date</>";
    } else {
        $log[] = "<C>●</>{$admin['month']}월:<D><b>{$younation['name']}</b></>으로 선전 포고 했습니다.<1>$date</>";

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$younation['name']}</b></>에 <M>선전 포고</> 하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【선포】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$younation['name']}</b></>에 선전 포고 하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>에 선전 포고");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$younation['name']}</b></>에 선전 포고");
        pushNationHistory($younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} 아국에 선전 포고");

        //외교 변경
        $query = "update diplomacy set state='1',term='24' where me='{$nation['nation']}' and you='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error("ally ".MYDB_error($connect),"");
        $query = "update diplomacy set state='1',term='24' where me='{$younation['nation']}' and you='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error("ally ".MYDB_error($connect),"");

        //국메로 저장
        $text = "【외교】{$admin['year']}년 {$admin['month']}월:{$nation['name']}에서 {$younation['name']}에 선전포고";

        $src = new MessageTarget(
            $general['no'], 
            $general['name'],
            $general['nation'],
            $nation['name'],
            $nation['color'],
            GetImageURL($general['imgsvr'], $general['picture'])
        );
        $dest = new MessageTarget(
            0,
            '',
            $younation['nation'],
            $younation['name'],
            $younation['color']
        );
        $msg = new Message(
            Message::MSGTYPE_NATIONAL, 
            $src,
            $dest,
            $text,
            new \DateTime($general['turntime']),
            new \DateTime('9999-12-31'),
            []
        );
        $msg->send();
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_63(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $date = substr($general['turntime'],11,5);
    list($year, $month, $turnterm) = $gameStor->getValuesAsArray(['year','month','turnterm']);

    if($general['level'] < 5 || $general['nation']==0) {
        pushGenLog($general, ["<C>●</>{$month}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }

    $supply = $db->queryFirstField('SELECT supply FROM city WHERE city=%i AND nation=%i', $general['city'], $general['nation']);
    $myTurn = $db->queryFirstField('SELECT %b FROM nation WHERE nation=%i', "l{$general['level']}turn0", $general['nation']);

    $command = DecodeCommand($myTurn);
    $which = $command[1];

    $srcNation = getNationStaticInfo($general['nation']);
    $destNation = getNationStaticInfo($which);

    if($destNation['nation'] == 0) {
        pushGenLog($general, ["<C>●</>{$month}월:멸망한 국가입니다. 제의 실패. <1>$date</>"]);
        return;
    } 
    
    if($supply === null) {
        pushGenLog($general, ["<C>●</>{$month}월:아국이 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }
    
    if($supply === 0) {
        pushGenLog($general, ["<C>●</>{$month}월:고립된 도시입니다. 제의 실패. <1>$date</>"]);
        return;
    }

    // 상대에게 발송
    $src = new MessageTarget(
        $general['no'], 
        $general['name'], 
        $srcNation['nation'], 
        $srcNation['name'], 
        $srcNation['color'], 
        GetImageURL($general['imgsvr'], $general['picture'])
    );

    $dest = new MessageTarget(
        0,
        '',
        $destNation['nation'],
        $destNation['name'],
        $destNation['color']
    );

    $now = new \DateTime($date);
    $validUntil = new \DateTime($date);
    $validMinutes = max(30, $turnterm*3);
    $validUntil->add(new \DateInterval("PT{$validMinutes}M"));

    $msg = new DiplomaticMessage(
        Message::MSGTYPE_DIPLOMACY,
        $src,
        $dest,
        "{$srcNation['name']}의 종전 제의 서신",
        $now,
        $validUntil,
        ['action'=>DiplomaticMessage::TYPE_STOP_WAR]
    );
    $msg->send();

    pushGenLog($general, ["<C>●</>{$month}월:<D><b>{$destNation['name']}</b></>으로 종전 제의 서신을 보냈습니다. <1>$date</>"]);
}

function process_64(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $date = substr($general['turntime'],11,5);
    list($year, $month, $turnterm) = $gameStor->getValuesAsArray(['year','month','turnterm']);

    if($general['level'] < 5 || $general['nation']==0) {
        pushGenLog($general, ["<C>●</>{$month}월:수뇌부가 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }

    $supply = $db->queryFirstField('SELECT supply FROM city WHERE city=%i AND nation=%i', $general['city'], $general['nation']);
    $myTurn = $db->queryFirstField('SELECT %b FROM nation WHERE nation=%i', "l{$general['level']}turn0", $general['nation']);

    $command = DecodeCommand($myTurn);
    $which = $command[1];

    $srcNation = getNationStaticInfo($general['nation']);
    $destNation = getNationStaticInfo($which);

    if($destNation['nation'] == 0) {
        pushGenLog($general, ["<C>●</>{$month}월:멸망한 국가입니다. 제의 실패. <1>$date</>"]);
        return;
    } 
    
    if($supply === null) {
        pushGenLog($general, ["<C>●</>{$month}월:아국이 아닙니다. 제의 실패. <1>$date</>"]);
        return;
    }
    
    if($supply === 0) {
        pushGenLog($general, ["<C>●</>{$month}월:고립된 도시입니다. 제의 실패. <1>$date</>"]);
        return;
    }

    // 상대에게 발송
    $src = new MessageTarget(
        $general['no'], 
        $general['name'], 
        $srcNation['nation'], 
        $srcNation['name'], 
        $srcNation['color'], 
        GetImageURL($general['imgsvr'], $general['picture'])
    );

    $dest = new MessageTarget(
        0,
        '',
        $destNation['nation'],
        $destNation['name'],
        $destNation['color']
    );

    $now = new \DateTime($date);
    $validUntil = new \DateTime($date);
    $validMinutes = max(30, $turnterm*3);
    $validUntil->add(new \DateInterval("PT{$validMinutes}M"));

    $msg = new DiplomaticMessage(
        Message::MSGTYPE_DIPLOMACY,
        $src,
        $dest,
        "{$srcNation['name']}의 불가침 파기 제의 서신",
        $now,
        $validUntil,
        ['action'=>DiplomaticMessage::TYPE_CANCEL_NA]
    );
    $msg->send();

    $josaRo = JosaUtil::pick($destNation['name'], '로');
    pushGenLog($general, ["<C>●</>{$month}월:<D><b>{$destNation['name']}</b></>{$josaRo} 불가침 파기 제의 서신을 보냈습니다.<1>$date</>"]);
}

function process_65(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select city from city where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $query = "select nation,capital,name,surlimit,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation,pop,gen1,gen2,gen3 from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    //아국이 외교중(교전, 선포, 합병, 통합 // 즉 !통상, !불가침)일때
    $query = "select state from diplomacy where me='{$nation['nation']}' and (state!='2' and state!='7')";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 초토화 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 초토화 실패. <1>$date</>";
    } elseif($nation['capital'] == $destcity['city']) {
        $log[] = "<C>●</>{$admin['month']}월:수도입니다. 초토화 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 초토화 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 영토가 아닙니다. 초토화 실패. <1>$date</>";
    } elseif($citycount <= 4) {
        $log[] = "<C>●</>{$admin['month']}월:더이상 물러날 수 없습니다. 초토화 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:평시에만 가능합니다. 초토화 실패. <1>$date</>";
    } elseif($nation['surlimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:제한 턴이 있습니다. 초토화 실패. <1>$date</>";
    } else {
        $josaUl = JosaUtil::pick($destcity['name'], '을');
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaUl} 초토화했습니다. <1>$date</>";

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>의 <R>초토화</>를 명령하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【초토화】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>{$josaUl} <R>초토화</>하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>의 <R>초토화</>를 명령");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>의 <R>초토화</>를 명령");

        //외교제한 24
        $amount = Util::round($destcity['pop'] * 0.1);
        $query = "update nation set surlimit='24',gold=gold+'$amount',rice=rice+'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //직위해제
        $query = "update general set level=1 where no='{$destcity['gen1']}' or no='{$destcity['gen2']}' or no='{$destcity['gen3']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //성 공백지로
        $query = "update city set pop=pop*0.1,rate=50,agri=agri*0.1,comm=comm*0.1,secu=secu*0.1,nation='0',front='0',gen1='0',gen2='0',gen3='0',conflict='{}' where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //전장수 10% 삭감
        $query = "update general set experience=experience*0.9,dedication=dedication*0.9 where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_66(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,gold,rice,surlimit,l{$general['level']}term,l{$general['level']}turn0,capital,capset from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $nearCities = searchDistance($nation['capital'], 1, false);
    $amount = $admin['develcost'] * 10;

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 66) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 66;
    } else {
        $term = 1;
        $code = 100 + 66;
    }

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 천도 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 천도 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 천도 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 영토가 아닙니다. 천도 실패. <1>$date</>";
    } elseif($destcity['city'] == $nation['capital'] || !key_exists($destcity['city'], $nearCities)){
        $log[] = "<C>●</>{$admin['month']}월:인접도시가 아닙니다. 천도 실패. <1>$date</>";
    } elseif($nation['capset'] == 1) {
        $log[] = "<C>●</>{$admin['month']}월:다음 분기에 가능합니다. 천도 실패. <1>$date</>";
    } elseif($nation['gold']-GameConst::$basegold < $amount || $nation['rice']-GameConst::$baserice < $amount) {
        $log[] = "<C>●</>{$admin['month']}월:물자가 부족합니다. 천도 실패. <1>$date</>";
    } elseif($term < 3) {
        $log[] = "<C>●</>{$admin['month']}월:천도중... ({$term}/3) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $josaRo = JosaUtil::pick($destcity['name'], '로');
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaRo} 천도했습니다. <1>$date</>";

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>{$josaRo} <R>천도</>를 명령하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<S><b>【천도】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>{$josaRo} 천도하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaRo} 천도 명령");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>{$josaRo} 천도 명령");

        //수도 변경
        $query = "update nation set l{$general['level']}term='0',capital='{$destcity['city']}',capset='1',gold=gold-'$amount',rice=rice-'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        refreshNationStaticInfo();
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_67(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,gold,rice,surlimit,l{$general['level']}term,l{$general['level']}turn0,capital,capset from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation,level from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $amount = $admin['develcost'] * 500 + 60000;   // 7만~13만

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 67) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 67;
    } else {
        $term = 1;
        $code = 100 + 67;
    }

    if($nation['capital'] != $general['city']) {
        $log[] = "<C>●</>{$admin['month']}월:수도에서 실행해야 합니다. 증축 실패. <1>$date</>";
    } elseif($nation['capital'] != $destcity['city']) {
        $log[] = "<C>●</>{$admin['month']}월:수도만 가능합니다. 증축 실패. <1>$date</>";
    } elseif($destcity['level'] <= 3) {
        $log[] = "<C>●</>{$admin['month']}월:수진, 진, 관문은 불가능합니다. 증축 실패. <1>$date</>";
    } elseif($destcity['level'] >= 8) {
        $log[] = "<C>●</>{$admin['month']}월:더이상 증축할 수 없습니다. 증축 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 증축 실패. <1>$date</>";
    } elseif($nation['capset'] == 1) {
        $log[] = "<C>●</>{$admin['month']}월:다음 분기에 가능합니다. 증축 실패. <1>$date</>";
    } elseif($nation['gold']-GameConst::$basegold < $amount || $nation['rice']-GameConst::$baserice < $amount) {
        $log[] = "<C>●</>{$admin['month']}월:물자가 부족합니다. 증축 실패. <1>$date</>";
    } elseif($term < 6) {
        $log[] = "<C>●</>{$admin['month']}월:증축중... ({$term}/6) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $josaUl = JosaUtil::pick($destcity['name'], '을');
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaUl} 증축했습니다. <1>$date</>";
        $exp = 5 * 6;
        $ded = 5 * 6;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>{$josaUl} <C>증축</>하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【증축】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>{$josaUl} 증축하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaUl} 증축");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>{$josaUl} 증축");

        //물자 감소
        $query = "update nation set l{$general['level']}term='0',capset='1',gold=gold-'$amount',rice=rice-'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //수도 증축
        $query = "update city set upgrading=upgrading+1,level=level+1,pop2=pop2+100000,agri2=agri2+2000,comm2=comm2+2000,def2=def2+2000,wall2=wall2+2000,secu2=secu2+2000 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_68(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,gold,rice,surlimit,l{$general['level']}term,l{$general['level']}turn0,capital,capset from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation,level,pop,agri,comm,def,wall,secu,upgrading from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $amount = $admin['develcost'] * 500 + 30000;   // 4만~10만

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 68) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 68;
    } else {
        $term = 1;
        $code = 100 + 68;
    }

    if($nation['capital'] != $general['city']) {
        $log[] = "<C>●</>{$admin['month']}월:수도에서 실행해야 합니다. 감축 실패. <1>$date</>";
    } elseif($nation['capital'] != $destcity['city']) {
        $log[] = "<C>●</>{$admin['month']}월:수도만 가능합니다. 감축 실패. <1>$date</>";
    } elseif($destcity['level'] <= 3) {
        $log[] = "<C>●</>{$admin['month']}월:수진, 진, 관문은 불가능합니다. 감축 실패. <1>$date</>";
    } elseif($destcity['level'] <= 6) {
        $log[] = "<C>●</>{$admin['month']}월:더이상 감축할 수 없습니다. 감축 실패. <1>$date</>";
    } elseif($destcity['upgrading'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:증축된 도시가 아닙니다. 감축 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 감축 실패. <1>$date</>";
    } elseif($nation['capset'] == 1) {
        $log[] = "<C>●</>{$admin['month']}월:다음 분기에 가능합니다. 감축 실패. <1>$date</>";
    } elseif($term < 6) {
        $log[] = "<C>●</>{$admin['month']}월:감축중... ({$term}/6) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $josaUl = JosaUtil::pick($destcity['name'], '을');
        $log[] = "<C>●</>{$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaUl} 감축했습니다. <1>$date</>";
        $exp = 5 * 6;
        $ded = 5 * 6;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>{$josaUl} <M>감축</>하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【감축】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>{$josaUl} 감축하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>{$josaUl} 감축");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>{$josaUl} 감축");

        //물자 증가
        $query = "update nation set l{$general['level']}term='0',capset='1',gold=gold+'$amount',rice=rice+'$amount' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $pop  = $destcity['pop']  - 100000;
        $agri = $destcity['agri'] - 2000;
        $comm = $destcity['comm'] - 2000;
        $def  = $destcity['def']  - 2000;
        $wall = $destcity['wall'] - 2000;
        $secu = $destcity['secu'] - 2000;
        if($pop  < 30000) { $pop  = 30000; }
        if($agri < 0)  { $agri = 0;  }
        if($comm < 0)  { $comm = 0;  }
        if($def  < 0)  { $def  = 0;  }
        if($wall < 0)  { $wall = 0;  }
        if($secu < 0)  { $secu = 0;  }
        //수도 감축
        $query = "update city set upgrading=upgrading-1,level=level-1,pop2=pop2-100000,agri2=agri2-2000,comm2=comm2-2000,def2=def2-2000,wall2=wall2-2000,secu2=secu2-2000,pop='$pop',agri='$agri',comm='$comm',def='$def',wall='$wall',secu='$secu' where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_71(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    //$term2 = Util::round($genCount / 10);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = Util::round(sqrt($genCount*8)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 71) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 71;
    } else {
        $term = 1;
        $code = 100 + 71;
    }

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 필사즉생 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 필사즉생 실패. <1>$date</>";
    } elseif($dipCount == 0) {
        $log[] = "<C>●</>{$admin['month']}월:전쟁중이 아닙니다. 필사즉생 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 필사즉생 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:필사즉생 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:필사즉생 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <M>필사즉생</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <M>필사즉생</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <M>필사즉생</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <M>필사즉생</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<M>필사즉생</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <M>필사즉생</>을 발동");

        //전장수 훈사100
        $query = "update general set atmos=100,train=100 where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_72(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    //$term2 = Util::round($genCount / 20);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 1;
    $term3 = Util::round(sqrt($genCount*4)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 72) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 72;
    } else {
        $term = 1;
        $code = 100 + 72;
    }

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 백성동원 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 백성동원 실패. <1>$date</>";
    } elseif($dipCount == 0) {
        $log[] = "<C>●</>{$admin['month']}월:전쟁중이 아닙니다. 백성동원 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 백성동원 실패. <1>$date</>";
    } elseif($destcity['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국 도시만 가능합니다. 백성동원 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:백성동원 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:백성동원 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>백성동원</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>백성동원</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>에 <M>백성동원</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>에 <M>백성동원</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>에 <M>백성동원</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>백성동원</>을 발동");

        //도시 성수 80%
        $query = "update city set def=def2*0.8,wall=wall2*0.8 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_73(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select nation,name from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    //$term2 = Util::round($genCount / 20);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = Util::round(sqrt($genCount*4)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 73) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 73;
    } else {
        $term = 1;
        $code = 100 + 73;
    }

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 수몰 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 수몰 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. 수몰 실패. <1>$date</>";
    } elseif($destcity['nation'] == $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:대상도시가 아국입니다. 수몰 실패. <1>$date</>";
    } elseif($dip['state'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전쟁중인 상대국에만 가능합니다. 수몰 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 수몰 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:수몰 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:수몰 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destcity['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><G><b>{$destcity['name']}</b></>에 <M>수몰</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>의 <G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동");
        pushNationHistory($destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} 아국의 <G><b>{$destcity['name']}</b></>에 <M>수몰</>을 발동");

        //도시 성수 80% 감소
        $query = "update city set def=def*0.2,wall=wall*0.2 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_74(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select city,name,nation from city where city='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destcity = MYDB_fetch_array($result);

    $query = "select nation,name from nation where nation='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destcity['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    //$term2 = Util::round($genCount / 20);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 2;
    $term3 = Util::round(sqrt($genCount*4)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 74) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 74;
    } else {
        $term = 1;
        $code = 100 + 74;
    }

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 허보 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 허보 실패. <1>$date</>";
    } elseif($destcity['nation'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:공백지입니다. 허보 실패. <1>$date</>";
    } elseif($destcity['nation'] == $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:대상도시가 아국입니다. 허보 실패. <1>$date</>";
    } elseif($dip['state'] > 1) {
        $log[] = "<C>●</>{$admin['month']}월:선포,전쟁중인 상대국에만 가능합니다. 허보 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 허보 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:허보 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:허보 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>의 <G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동");
        pushNationHistory($destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} 아국의 <G><b>{$destcity['name']}</b></>에 <M>허보</>를 발동");

        //상대국 도시 전부 검색
        $query = "select city from city where nation='{$destcity['nation']}' and supply=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cityCount = MYDB_num_rows($result);
        $cities = [];
        for($i=0; $i < $cityCount; $i++) {
            $dCity = MYDB_fetch_array($result);
            $cities[$i] = $dCity['city'];
        }
        //상대국 유저 랜덤 배치
        $query = "select no,name from general where nation='{$destcity['nation']}' and city='{$destcity['city']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        $opplog = ["<C>●</>상대국의 허보에 당했다! <1>$date</>"];
        for($i=0; $i < $count; $i++) {
            $gen = MYDB_fetch_array($result);
            $selCity = $cities[rand() % $cityCount];
            //현재도시이면 한번 다시 랜덤추첨
            if($selCity == $destcity['city']) { $selCity = $cities[rand() % $cityCount]; }

            $query = "update general set city={$selCity} where no='{$gen['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($gen, $opplog);
        }

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_75(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state from diplomacy where me='{$general['nation']}' and you='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    //$term2 = Util::round($genCount / 40);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = Util::round(sqrt($genCount*2)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 75) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 75;
    } else {
        $term = 1;
        $code = 100 + 75;
    }

    if(!$destnation) {
        $log[] = "<C>●</>{$admin['month']}월:없는 국가입니다. 피장파장 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 피장파장 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 피장파장 실패. <1>$date</>";
    } elseif($dip['state'] > 1) {
        $log[] = "<C>●</>{$admin['month']}월:선포,전쟁중인 상대국에만 가능합니다. 피장파장 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 피장파장 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:피장파장 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:피장파장 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>피장파장</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destnation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</>아국에 <M>피장파장</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>피장파장</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$destnation['name']}</b></>에 <M>피장파장</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$destnation['name']}</b></>에 <M>피장파장</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$destnation['name']}</b></>에 <M>피장파장</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>피장파장</>을 발동");
        pushNationHistory($destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} 아국에 <M>피장파장</>을 발동");

        //전략기한+60
        $query = "update nation set sabotagelimit=sabotagelimit+60 where nation='{$destnation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한, 최소72
        if($term3 < 72) { $term3 = 72; }
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_76(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear','year','month','develcost','npccount','turnterm']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    //$term2 = Util::round($genCount / 10);
    //if($term2 == 0) { $term2 = 1; }
    $term2 = 3;
    $term3 = Util::round(sqrt($genCount*10)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 76) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 76;
    } else {
        $term = 1;
        $code = 100 + 76;
    }

    if($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:현재 초반 제한중입니다. 의병모집 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 의병모집 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 의병모집 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 의병모집 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:의병모집 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:의병모집 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <M>의병모집</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <M>의병모집</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <M>의병모집</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <M>의병모집</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<M>의병모집</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <M>의병모집</>을 발동");

        $query = "select avg(gennum) as gennum from nation";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $avgNation = MYDB_fetch_array($result);
        $gencount = 5 + Util::round($avgNation['gennum'] / 10);

        $query = "select avg(age) as age, max(leader+power+intel) as lpi, avg(dedication) as ded,avg(experience) as exp, avg(dex0) as dex0, avg(dex10) as dex10, avg(dex20) as dex20, avg(dex30) as dex30, avg(dex40) as dex40 from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $avgGen = MYDB_fetch_array($result);

        //의병추가
        $npc = 4;
        $npcid = $admin['npccount'];
        for($i=0; $i < $gencount; $i++) {
            // 무장 40%, 지장 40%, 무지장 20%
            $type = rand() % 10;
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
            if($leader > GameConst::$defaultStatNPCMax) {
                $leader = GameConst::$defaultStatNPCMax;
            }
            if($power > GameConst::$defaultStatNPCMax) {
                $power = GameConst::$defaultStatNPCMax;
            }
            if($intel > GameConst::$defaultStatNPCMax) {
                $intel = GameConst::$defaultStatNPCMax;
            }

            $npccount = 10000 + $npcid;
            $affinity = rand() % 150 + 1;
            $name = "ⓖ의병장{$npcid}";
            $picture = 'default.jpg';
            $turntime = getRandTurn($admin['turnterm']);
            $personal = rand() % 10;
            $bornyear = $admin['year'];
            $deadyear = $admin['year'] + 3;
            $killturn = 64 + rand()%7;

            @MYDB_query("
                insert into general (
                    npcid,npc,npc_org,affinity,name,picture,nation,
                    city,leader,power,intel,experience,dedication,
                    level,gold,rice,crew,crewtype,train,atmos,tnmt,
                    weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
                    makelimit,bornyear,deadyear,
                    dex0, dex10, dex20, dex30, dex40
                ) values (
                    '$npccount','$npc','$npc','$affinity','$name','$picture','{$nation['nation']}',
                    '{$general['city']}','$leader','$power','$intel','{$avgGen['exp']}','{$avgGen['ded']}',
                    '1','100','100','0','".GameUnitConst::DEFAULT_CREWTYPE."','0','0','0',
                    '0','0','0','$turntime','$killturn','{$avgGen['age']}','1','$personal','0','0','0','0','',
                    '0','$bornyear','$deadyear',
                    '{$avgGen['dex0']}','{$avgGen['dex10']}','{$avgGen['dex20']}','{$avgGen['dex30']}','{$avgGen['dex40']}'
                )",
                $connect
            ) or Error(__LINE__.MYDB_error($connect),"");

            $npcid++;
        }
        //npccount
        $gameStor->npccount=$npcid;

        //국가 기술력 그대로
        $query = "select no from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $gennum = $gencount;
        if($gencount < GameConst::$initialNationGenLimit) $gencount = GameConst::$initialNationGenLimit;

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한, 국가 기술력 그대로
        $query = "update nation set sabotagelimit={$term3},totaltech=tech*'$gencount',gennum='$gennum' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_77(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state,term from diplomacy where me='{$general['nation']}' and you='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    $term2 = 1;
    $term3 = Util::round(sqrt($genCount*16)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 77) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 77;
    } else {
        $term = 1;
        $code = 100 + 77;
    }

    if(!$destnation) {
        $log[] = "<C>●</>{$admin['month']}월:없는 국가입니다. 이호경식 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 이호경식 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 이호경식 실패. <1>$date</>";
    } elseif($dip['state'] > 1) {
        $log[] = "<C>●</>{$admin['month']}월:선포,전쟁중인 상대국에만 가능합니다. 이호경식 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 이호경식 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:이호경식 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:이호경식 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>이호경식</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destnation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</>아국에 <M>이호경식</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>이호경식</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$destnation['name']}</b></>에 <M>이호경식</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$destnation['name']}</b></>에 <M>이호경식</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$destnation['name']}</b></>에 <M>이호경식</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>이호경식</>을 발동");
        pushNationHistory($destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} 아국에 <M>이호경식</>을 발동");

        //선포+3개월
        if($dip['state'] == 0) {
            $query = "update diplomacy set state=1,term=3 where (me='{$general['nation']}' and you='{$destnation['nation']}') or (you='{$general['nation']}' and me='{$destnation['nation']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update diplomacy set term=term+3 where (me='{$general['nation']}' and you='{$destnation['nation']}') or (you='{$general['nation']}' and me='{$destnation['nation']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_78(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $sabotagelog = [];

    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month','develcost']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];

    $query = "select nation,name from nation where nation='$which'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $destnation = MYDB_fetch_array($result);

    $query = "select state,term from diplomacy where me='{$general['nation']}' and you='{$destnation['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dip = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);
    if($genCount < GameConst::$initialNationGenLimit) { $genCount = GameConst::$initialNationGenLimit; }

    $term2 = 1;
    $term3 = Util::round(sqrt($genCount*16)*10);

    $code = $nation["l{$general['level']}term"];
    if($code%100 == 78) {
        $term = intdiv($code, 100) + 1;
        $code = $term * 100 + 78;
    } else {
        $term = 1;
        $code = 100 + 78;
    }

    if(!$destnation) {
        $log[] = "<C>●</>{$admin['month']}월:없는 국가입니다. 급습 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 급습 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 급습 실패. <1>$date</>";
    } elseif($dip['state'] != 1) {
        $log[] = "<C>●</>{$admin['month']}월:선포중인 상대국에만 가능합니다. 급습 실패. <1>$date</>";
    } elseif($dip['term'] < 12) {
        $log[] = "<C>●</>{$admin['month']}월:선포 12개월 이상인 상대국에만 가능합니다. 급습 실패. <1>$date</>";
    } elseif($nation['sabotagelimit'] > 0) {
        $log[] = "<C>●</>{$admin['month']}월:전략기한이 남았습니다. 급습 실패. <1>$date</>";
    } elseif($term < $term2) {
        $log[] = "<C>●</>{$admin['month']}월:급습 수행중... ({$term}/{$term2}) <1>$date</>";

        $query = "update nation set l{$general['level']}term={$code} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $log[] = "<C>●</>{$admin['month']}월:급습 발동! <1>$date</>";
        $exp = 5 * $term2;
        $ded = 5 * $term2;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</><Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>급습</>을 발동하였습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

        $query = "select no,name from general where nation='{$destnation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $alllog[0] = "<C>●</>아국에 <M>급습</>이 발동되었습니다.";
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $alllog);
        }

//        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>급습</>을 발동하였습니다.";
//        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$destnation['name']}</b></>에 <M>급습</>을 발동하였습니다.";
        $sabotagelog[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【전략】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <D><b>{$destnation['name']}</b></>에 <M>급습</>을 발동하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$destnation['name']}</b></>에 <M>급습</>을 발동");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <D><b>{$destnation['name']}</b></>에 <M>급습</>을 발동");
        pushNationHistory($destnation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>{$josaYi} 아국에 <M>급습</>을 발동");

        //선포-3개월
        $query = "update diplomacy set term=term-3 where (me='{$general['nation']}' and you='{$destnation['nation']}') or (you='{$general['nation']}' and me='{$destnation['nation']}')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set sabotagelimit={$term3} where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

//    pushWorldHistory($history, $admin['year'], $admin['month']);
//    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushSabotageLog($sabotagelog);
    pushGenLog($general, $log);
}

function process_81(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $query = "select nation from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,can_change_flag,name,type,sabotagelimit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $command = DecodeCommand($nation["l{$general['level']}turn0"]);
    $which = $command[1];
    $colors = GetNationColors();
    if($which >= count($colors)) { $which = 0; }
    $color = $colors[$which];

    if($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 국기변경 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부가 아닙니다. 국기변경 실패. <1>$date</>";
    } elseif($nation['can_change_flag'] <= 0) {
        $log[] = "<C>●</>{$admin['month']}월:더이상 변경이 불가능합니다. 국기변경 실패. <1>$date</>";
    } else {
        $log[] = "<C>●</>{$admin['month']}월:<font color={$color}><b>국기</b></font>를 변경합니다. <1>$date</>";
        $exp = 10;
        $ded = 10;

        // 성격 보정
        $exp = CharExperience($exp, $general['personal']);
        $ded = CharDedication($ded, $general['personal']);

        $josaYi = JosaUtil::pick($general['name'], '이');
        $josaYiNation = JosaUtil::pick($nation['name'], '이');

        $query = "select no,name from general where nation='{$general['nation']}' and no!='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cnt = MYDB_num_rows($result);
        $genlog = ["<C>●</><Y>{$general['name']}</>{$josaYi} <font color={$color}><b>국기</b></font>를 변경합니다."];
        for($i=0; $i < $cnt; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
        }

        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <font color={$color}><b>국기</b></font>를 변경하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【국기변경】</b></><D><b>{$nation['name']}</b></>{$josaYiNation} <font color={$color}><b>국기</b></font>를 변경하였습니다.";
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<font color={$color}><b>국기</b></font>를 변경");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <font color={$color}><b>국기</b></font>를 변경");

        //국기변경
        $query = "update nation set color='$color',can_change_flag=can_change_flag-1 where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //경험치, 공헌치
        $query = "update general set dedication=dedication+'$ded',experience=experience+'$exp' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        refreshNationStaticInfo();
    }

    pushWorldHistory($history, $admin['year'], $admin['month']);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}
