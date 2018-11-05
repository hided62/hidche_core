<?php
namespace sammo;

function process_47(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['startyear', 'year', 'month']);

    $nation = getNationStaticInfo($general['nation']);

    //현재 외교 진행중(평시, 불가침만 제외)일때
    $query = "select state from diplomacy where me='{$general['nation']}' and state!='2' and state!='7'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);


    if($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. <1>$date</>";
    } elseif($admin['year'] < $admin['startyear']+3) {
        $log[] = "<C>●</>{$admin['month']}월:초반제한중에는 방랑이 불가능합니다. 방랑 실패.";
    } elseif($dipcount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:방랑할 수 없는 외교상태입니다. 방랑 실패.";
    } elseif($nation['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:이미 방랑군입니다. 방랑 실패.";
    } else {
        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:영토를 버리고 방랑의 길을 떠납니다. <1>$date</>";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 방랑의 길을 떠납니다.";
        $josaUl = JosaUtil::pick($nation['name'], '을');
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaUl} 버리고 방랑");

        $josaUn = JosaUtil::pick($general['name'], '은');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【방랑】</b></><D><b>{$general['name']}</b></>{$josaUn} <R>방랑</>의 길을 떠납니다.";

        //분쟁기록 모두 지움
        DeleteConflict($general['nation']);
        // 국명, 색깔 바꿈 국가 레벨 0, 성향리셋, 기술0
        $db->update('nation', [
            'name'=>$general['name'],
            'color'=>'#330000', //TODO: 기본 방랑군색 별도 지정
            'level'=>0,
            'type'=>0,
            'tech'=>0,
            'capital'=>0
        ], 'nation=%i', $general['nation']);
        // 본인 빼고 건국/임관제한
        $query = "update general set makelimit='12' where no!='{$general['no']}' and nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 건국/임관제한
        $query = "update general set resturn='SUCCESS',makelimit='12' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 관직장수 일반으로
        $query = "update general set level=1 where nation='{$general['nation']}' and level <= 11";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 전 도시 공백지로
        $query = "update city set nation='0',front='0',gen1='0',gen2='0',gen3='0',conflict='{}' where nation='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 리셋
        $query = "update diplomacy set state='2',term='0' where me='{$general['nation']}' or you='{$general['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushWorldHistory($history, $admin['year'], $admin['month']);

        refreshNationStaticInfo();
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}

function process_54(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $youlog = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $command = DecodeCommand($general['turn0']);
    $who = $command[1];

    $query = "select no,name,nation from general where no='$who'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nextruler = MYDB_fetch_array($result);

    $query = "select nation,name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    //현재 외교 진행중(통합제의중)일때
    $query = "select state from diplomacy where me='{$general['nation']}' and state='4'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($result);

    if($nextruler['name'] == "") {
        $log[] = "<C>●</>{$admin['month']}월:잘못된 장수입니다. 선양 실패. <1>$date</>";
    } elseif($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. 선양 실패. <1>$date</>";
    } elseif($nextruler['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:잘못된 장수입니다. 선양 실패. <1>$date</>";
    } elseif($dipcount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:현재 통합 진행중입니다. 선양 실패.";
    } else {
        //군주 교체
        $query = "update general set level='12' where no='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 태수,군사,시중이었다면 해제
        $query = "update city set gen1='0' where gen1='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen2='0' where gen2='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen3='0' where gen3='$who'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set resturn='SUCCESS',level='1',experience=experience*0.7 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $josaYi = JosaUtil::pick($general['name'], '이');
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【선양】</b></><Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>의 군주 자리를 <Y>{$nextruler['name']}</>에게 선양했습니다.";
        $log[] = "<C>●</>{$admin['month']}월:<Y>{$nextruler['name']}</>에게 군주의 자리를 물려줍니다. <1>$date</>";
        $youlog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게서 군주의 자리를 물려받습니다.";

        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주자리를 <Y>{$nextruler['name']}</>에게 선양");
        pushGeneralHistory($nextruler, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주자리를 물려 받음");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <Y>{$nextruler['name']}</>에게 선양");
    }
    pushGenLog($general, $log);
    pushGenLog($nextruler, $youlog);
    pushWorldHistory($history, $admin['year'], $admin['month']);
}

function process_56(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year','month']);

    $nation = getNationStaticInfo($general['nation']);

    $query = "select city from city where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    if($general['level'] != 12) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 아닙니다. <1>$date</>";
    } elseif($citycount != 0) {
        $log[] = "<C>●</>{$admin['month']}월:방랑군이 아닙니다. <1>$date</>";
    } else {
        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:세력을 해산했습니다. <1>$date</>";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} 세력을 해산했습니다.";
        $josaUl = JosaUtil::pick($nation['name'], '을');
        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>{$josaUl} 해산");

        $query = "select no from general where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $genCount = MYDB_num_rows($result);

        // 수동 해산인 국가 페널티, 자금, 군량
        if($genCount > 1) {
            $query = "update general set resturn='SUCCESS' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set gold=1000 where nation='{$general['nation']}' and gold>1000";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update general set rice=1000 where nation='{$general['nation']}' and rice>1000";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }

        //분쟁기록 모두 지움
        DeleteConflict($general['nation']);
        deleteNation($general);

        refreshNationStaticInfo();
    }
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushGenLog($general, $log);
}


function process_57(&$general) {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $log = [];
    $youlog = [];
    $alllog = [];
    $history = [];
    $date = substr($general['turntime'],11,5);

    $admin = $gameStor->getValues(['year', 'month', 'killturn']);

    $query = "select nation,name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select nation,supply from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select npc,no,name,killturn from general where nation='{$general['nation']}' and level='12'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $ruler = MYDB_fetch_array($result);

    if($general['level'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:재야 입니다. 모반 실패. <1>$date</>";
    } elseif($general['level'] < 5) {
        $log[] = "<C>●</>{$admin['month']}월:수뇌부 이상만 가능합니다. 모반 실패. <1>$date</>";
    } elseif($city['nation'] != $general['nation']) {
        $log[] = "<C>●</>{$admin['month']}월:아국이 아닙니다. 모반 실패. <1>$date</>";
    } elseif($city['supply'] == 0) {
        $log[] = "<C>●</>{$admin['month']}월:고립된 도시입니다. 모반 실패. <1>$date</>";
    } elseif($general['level'] == 12) {
        $log[] = "<C>●</>{$admin['month']}월:이미 군주 입니다. 모반 실패. <1>$date</>";
    } elseif($ruler['killturn'] >= $admin['killturn']) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 활동중입니다. 모반 실패. <1>$date</>";
    } elseif($ruler['npc'] >= 2) {
        $log[] = "<C>●</>{$admin['month']}월:군주가 NPC입니다. 모반 실패. <1>$date</>";
    } else {
        //군주 교체
        $query = "update general set resturn='SUCCESS',level='12' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 태수,군사,시중이었다면 해제
        $query = "update city set gen1='0' where gen1='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen2='0' where gen2='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update city set gen3='0' where gen3='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set level='1',experience=experience*0.7 where no='{$ruler['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $josaYi = JosaUtil::pick($general['name'], '이');
        $log[] = "<C>●</>{$admin['month']}월:모반에 성공했습니다. <1>$date</>";
        $youlog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>에게 군주의 자리를 뺏겼습니다.";
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <M>모반</>에 성공했습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【모반】</b></><Y>{$general['name']}</>{$josaYi} <D><b>{$nation['name']}</b></>의 군주 자리를 찬탈했습니다.";

        pushGeneralHistory($general, "<C>●</>{$admin['year']}년 {$admin['month']}월:모반으로 <D><b>{$nation['name']}</b></>의 군주자리를 찬탈");
        pushGeneralHistory($ruler, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$general['name']}</b></>의 모반으로 인해 <D><b>{$nation['name']}</b></>의 군주자리를 박탈당함");
        pushNationHistory($nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$general['name']}</>{$josaYi} <Y>{$ruler['name']}</>에게서 군주자리를 찬탈");
    }
    pushGenLog($general, $log);
    pushGenLog($ruler, $youlog);
    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
    pushWorldHistory($history, $admin['year'], $admin['month']);
}
