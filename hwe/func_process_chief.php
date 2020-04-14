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
