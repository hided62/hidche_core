<?php
namespace sammo;

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

    $query = "select city,name,nation,pop,officer4,officer3,officer2 from city where city='$which'";
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
        $query = "update general set level=1 where no='{$destcity['officer4']}' or no='{$destcity['officer3']}' or no='{$destcity['officer2']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //성 공백지로
        $query = "update city set pop=pop*0.1,trust=50,agri=agri*0.1,comm=comm*0.1,secu=secu*0.1,nation='0',front='0',officer4='0',officer3='0',officer2='0',conflict='{}' where city='{$destcity['city']}'";
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

    $amount = $admin['develcost'] * GameConst::$expandCityCostCoef + GameConst::$expandCityDefaultCost;   // 7만~13만

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
        $db->update('city', [
            'upgrading'=>$db->sqleval('upgrading+1'),
            'level'=>$db->sqleval('level+1'),
            'pop_max'=>$db->sqleval('pop_max + %i', GameConst::$expandCityPopIncreaseAmount),
            'agri_max'=>$db->sqleval('agri_max + %i', GameConst::$expandCityDevelIncreaseAmount),
            'comm_max'=>$db->sqleval('comm_max + %i', GameConst::$expandCityDevelIncreaseAmount),
            'secu_max'=>$db->sqleval('secu_max + %i', GameConst::$expandCityDevelIncreaseAmount),
            'def_max'=>$db->sqleval('def_max + %i', GameConst::$expandCityWallIncreaseAmount),
            'wall_max'=>$db->sqleval('wall_max + %i', GameConst::$expandCityWallIncreaseAmount),
        ], 'city=%i', $destcity['city']);
        
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

    $amount = $admin['develcost'] * GameConst::$expandCityCostCoef + GameConst::$expandCityDefaultCost / 2;   // 4만~10만

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

        $pop  = $destcity['pop']  - GameConst::$expandCityPopIncreaseAmount;
        $agri = $destcity['agri'] - GameConst::$expandCityDevelIncreaseAmount;
        $comm = $destcity['comm'] - GameConst::$expandCityDevelIncreaseAmount;
        $def  = $destcity['def']  - GameConst::$expandCityWallIncreaseAmount;
        $wall = $destcity['wall'] - GameConst::$expandCityWallIncreaseAmount;
        $secu = $destcity['secu'] - GameConst::$expandCityDevelIncreaseAmount;
        if($pop  < 30000) { $pop  = 30000; }
        if($agri < 0)  { $agri = 0;  }
        if($comm < 0)  { $comm = 0;  }
        if($def  < 0)  { $def  = 0;  }
        if($wall < 0)  { $wall = 0;  }
        if($secu < 0)  { $secu = 0;  }
        //수도 감축
        $db->update('city', [
            'upgrading'=>$db->sqleval('upgrading-1'),
            'level'=>$db->sqleval('level-1'),
            'pop_max'=>$db->sqleval('pop_max - %i', GameConst::$expandCityPopIncreaseAmount),
            'agri_max'=>$db->sqleval('agri_max - %i', GameConst::$expandCityDevelIncreaseAmount),
            'comm_max'=>$db->sqleval('comm_max - %i', GameConst::$expandCityDevelIncreaseAmount),
            'secu_max'=>$db->sqleval('secu_max - %i', GameConst::$expandCityDevelIncreaseAmount),
            'def_max'=>$db->sqleval('def_max - %i', GameConst::$expandCityWallIncreaseAmount),
            'wall_max'=>$db->sqleval('wall_max - %i', GameConst::$expandCityWallIncreaseAmount),
            'pop'=>$pop,
            'agri'=>$agri,
            'comm'=>$comm,
            'secu'=>$secu,
            'def'=>$def,
            'wall'=>$wall
        ], 'city=%i', $destcity['city']);

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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update city set def=def_max*0.8,wall=wall_max*0.8 where city='{$destcity['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update nation set strategic_cmd_limit=strategic_cmd_limit+60 where nation='{$destnation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한, 최소72
        if($term3 < 72) { $term3 = 72; }
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $admin = $gameStor->getValues(['startyear','year','month','develcost','npccount', 'turnterm', 'turntime']);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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

        $avgGenNum = $db->queryFirstField('SELECT avg(gennum) FROM nation');
        $addGenCount = 5 + Util::round($avgGenNum / 10);

        $avgGen = $db->queryFirstRow('SELECT max(leadership+strength+intel) as stat_sum, avg(dedication) as ded,avg(experience) as exp, avg(dex1+dex2+dex3+dex4) as dex_t, avg(age) as age, avg(dex5) as dex5 from general where npc < 5 and nation = %i', $general['nation']);

        $dexTotal = $avgGen['dex_t'];

        //의병추가
        $npc = 4;
        $npcid = Util::randRangeInt(0, 9999999);
        for($i=0; $i < $addGenCount; $i++) {
            //무장 50%, 지장 50%, 무지장 0%
            $stat_tier1 = GameConst::$defaultStatMax - 10 + rand()%11;
            $stat_tier3 = GameConst::$defaultStatMin + rand()%6;
            $stat_tier2 = GameConst::$defaultStatTotal - $stat_tier1 - $stat_tier3;
            $type = Util::choiceRandomUsingWeight([
                'strength0'=>1,
                'strength1'=>1,
                'strength_exp'=>1,
                'intel'=>3,
                'neutral'=>0
            ]);
            switch($type){
            case 'strength0':
                $leadership = $stat_tier1;
                $strength = $stat_tier2;
                $intel = $stat_tier3;
                $dexVal = [$dexTotal*5/8, $dexTotal/8, $dexTotal/8, $dexTotal/8];
                break;
            case 'strength1':
                $leadership = $stat_tier1;
                $strength = $stat_tier2;
                $intel = $stat_tier3;
                $dexVal = [$dexTotal/8, $dexTotal*5/8, $dexTotal/8, $dexTotal/8];
                break;
            case 'strength_exp':
                $leadership = $stat_tier1;
                $strength = $stat_tier2;
                $intel = $stat_tier3;
                $dexVal = [$dexTotal/8, $dexTotal/8, $dexTotal*5/8, $dexTotal/8];
                break;
            case 'intel':
                $leadership = $stat_tier1;
                $strength = $stat_tier3;
                $intel = $stat_tier2;
                $dexVal = [$dexTotal/8, $dexTotal/8, $dexTotal/8, $dexTotal*5/8];
                break;
            default:
                $leadership = $stat_tier3;
                $strength = $stat_tier1;
                $intel = $stat_tier2;
                $dexVal = [$dexTotal/4, $dexTotal/4, $dexTotal/4, $dexTotal/4];
                break;
            }
            // 국내 최고능치 기준으로 랜덤성 스케일링
            if($avgGen['stat_sum'] > 210) {
                $leadership = Util::round($leadership * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (60+rand()%31)/100);
                $strength = Util::round($strength * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (60+rand()%31)/100);
                $intel = Util::round($intel * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (60+rand()%31)/100);
            } elseif($avgGen['stat_sum'] > 180) {
                $leadership = Util::round($leadership * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (75+rand()%21)/100);
                $strength = Util::round($strength * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (75+rand()%21)/100);
                $intel = Util::round($intel * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (75+rand()%21)/100);
            } else {
                $leadership = Util::round($leadership * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (90+rand()%11)/100);
                $strength = Util::round($strength * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (90+rand()%11)/100);
                $intel = Util::round($intel * $avgGen['stat_sum'] / GameConst::$defaultStatTotal * (90+rand()%11)/100);
            }
            $over1 = 0;
            $over2 = 0;
            $over3 = 0;
            // 너무 높은 능치는 다른 능치로 분산
            if($leadership > 90) {
                $over1 = rand() % ($leadership - 90) + 5;
                $leadership -= $over1;
            }
            if($strength > 90) {
                $over2 = rand() % ($strength - 90) + 5;
                $strength -= $over2;
            }
            if($intel > 90) {
                $over3 = rand() % ($intel - 90) + 5;
                $intel -= $over3;
            }
            // 낮은 능치쪽으로 합산
            if($type == 'strength') {
                $intel = $intel + $over1 + $over2 + $over3;
            } else {
                $strength = $strength + $over1 + $over2 + $over3;
            }
            // 너무 높은 능치는 제한
            if($leadership > GameConst::$defaultStatNPCMax) {
                $leadership = GameConst::$defaultStatNPCMax;
            }
            if($strength > GameConst::$defaultStatNPCMax) {
                $strength = GameConst::$defaultStatNPCMax;
            }
            if($intel > GameConst::$defaultStatNPCMax) {
                $intel = GameConst::$defaultStatNPCMax;
            }

            $affinity = rand() % 150 + 1;
            $name = "ⓖ의병장{$npcid}";
            $picture = 'default.jpg';
            $turntime = getRandTurn($admin['turnterm'], new \DateTimeImmutable($admin['turntime']));
            $personal = rand() % 10;
            $bornyear = $admin['year'];
            $deadyear = $admin['year'] + 3;
            $killturn = 64 + rand()%7;

            @MYDB_query("
                insert into general (
                    npc,npc_org,affinity,name,picture,nation,
                    city,leadership,strength,intel,experience,dedication,
                    level,gold,rice,crew,crewtype,train,atmos,tnmt,
                    weapon,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
                    makelimit,bornyear,deadyear,
                    dex1, dex2, dex3, dex4, dex5
                ) values (
                    '$npc','$npc','$affinity','$name','$picture','{$nation['nation']}',
                    '{$general['city']}','$leadership','$strength','$intel','{$avgGen['exp']}','{$avgGen['ded']}',
                    '1','100','100','0','".GameUnitConst::DEFAULT_CREWTYPE."','0','0','0',
                    '0','0','0','$turntime','$killturn','{$avgGen['age']}','1','$personal','0','0','0','0','',
                    '0','$bornyear','$deadyear',
                    '{$dexVal[0]}','{$dexVal[1]}','{$dexVal[2]}','{$dexVal[3]}','{$avgGen['dex5']}'
                )",
                $connect
            ) or Error(__LINE__.MYDB_error($connect),"");

            $npcid++;
        }

        // 국가보정
        if($nation['type'] == 11) { $term3 = Util::round($term3 / 2); }
        if($nation['type'] == 12) { $term3 = $term3 * 2; }

        //전략기한
        $db->update('nation', [
            'strategic_cmd_limit'=>$term3,
            'gennum'=>$db->sqleval('gennum + %i', $addGenCount),
        ], 'nation=%i', $general['nation']);

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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $query = "select nation,gennum,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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

    $genCount = Util::valueFit($nation['gennum'], GameConst::$initialNationGenLimit);

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
    } elseif($nation['strategic_cmd_limit'] > 0) {
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
        $query = "update nation set strategic_cmd_limit={$term3} where nation='{$general['nation']}'";
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

    $query = "select nation,can_change_flag,name,type,strategic_cmd_limit,l{$general['level']}term,l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
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
