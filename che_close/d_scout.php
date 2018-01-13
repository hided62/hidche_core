<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();

$query = "select startyear,year,month,killturn from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,nations,level,troop,history,npc,gold,rice from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select no,nation,history from general where no='$gen'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$you = MYDB_fetch_array($result);

$query = "select name,nation,level,capital,scout from nation where nation='$you[nation]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$nation = MYDB_fetch_array($result);

if($ok == "수락" && $me[level] < 12 && $nation[level] > 0 && $nation[scout] == 0 && $me[nation] != $nation[nation] && $admin[year] >= $admin[startyear]+3 && strpos($me['nations'], ",{$nation['nation']},") === false) {
    $youlog[count($youlog)] = "<C>●</><Y>$me[name]</> 등용에 성공했습니다.";
    $alllog[count($alllog)] = "<C>●</>{$admin[month]}월:<Y>$me[name]</>(이)가 <D><b>$nation[name]</b></>(으)로 <S>망명</>하였습니다.";
    $mylog[count($mylog)] = "<C>●</><D>$nation[name]</>(으)로 망명하여 수도로 이동합니다.";
    $you = addHistory($connect, $you, "<C>●</>$admin[year]년 $admin[month]월:<Y>$me[name]</> 등용에 성공");
    $me = addHistory($connect, $me, "<C>●</>$admin[year]년 $admin[month]월:<D>$nation[name]</>(으)로 망명");

    // 임관내역 추가
    $me['nations'] .= "{$nation['nation']},";

    // 국가 변경, 도시 변경, 일반으로, 수도로
    $query = "update general set killturn='$admin[killturn]',belong=1,nation='$you[nation]',nations='{$me['nations']}',level=1,city='$nation[capital]' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 재야가 아니면 명성N*10% 공헌N*10%감소
    if($me[level] > 0) {
        // 1000 1000 남기고 환수
        if($me[gold] > 1000) { $gold = $me[gold] - 1000; $me[gold] = 1000; } else { $gold = 0; }
        if($me[rice] > 1000) { $rice = $me[rice] - 1000; $me[rice] = 1000; } else { $rice = 0; }
        $query = "update general set gold='$me[gold]',rice='$me[rice]',betray=betray+1,dedication=dedication*(1-0.1*betray),experience=experience*(1-0.1*betray) where no='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update nation set gold=gold+'$gold',rice=rice+'$rice' where nation='$me[nation]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
    //재야이면 100 100 증가
        $query = "update general set dedication=dedication+100,experience=experience+100 where no='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    //유저이면 삭턴 리셋
    if($me[npc] < 2) {
        $query = "update general set killturn='$admin[killturn]' where no='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    //태수 군사 시중 해제
    switch($me[level]) {
    case 4:
        $query = "update city set gen1=0 where gen1='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 3:
        $query = "update city set gen2=0 where gen2='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    case 2:
        $query = "update city set gen3=0 where gen3='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        break;
    }

    //등용자 명성, 공헌 상승
    $query = "update general set dedication=dedication+100,experience=experience+100 where no='$you[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 부대 처리
    $query = "select no from troop where troop='$me[troop]'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $troop = MYDB_fetch_array($result);

    //부대장일 경우
    if($troop[no] == $me[no]) {
        // 모두 탈퇴
        $query = "update general set troop='0' where troop='$me[troop]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대 삭제
        $query = "delete from troop where troop='$me[troop]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $query = "update general set troop='0' where no='$me[no]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    //국가 기술력 그대로
    $query = "select no from general where nation='$nation[nation]'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    $gennum = $gencount;
    if($gencount < 10) $gencount = 10;

    $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='$nation[nation]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //기존 국가 기술력 그대로
    $query = "select no from general where nation='$me[nation]'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    $gennum = $gencount;
    if($gencount < 10) $gencount = 10;

    $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='$me[nation]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //현 메세지 수정
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 수락',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($me[level] == 12) {
    $mylog[count($mylog)] = "<C>●</>군주입니다. 등용 수락 불가.";

    //현 메세지 지움
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 수락 불가',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($nation[level] == 0) {
    $mylog[count($mylog)] = "<C>●</>없는 국가이거나 방랑군입니다. 등용 수락 불가.";

    //현 메세지 지움
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 수락 불가',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($nation[scout] != 0) {
    $mylog[count($mylog)] = "<C>●</>임관 금지중입니다. 등용 수락 불가.";

    //현 메세지 지움
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 수락 불가',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($admin[year] < $admin[startyear]+3) {
    $mylog[count($mylog)] = "<C>●</>초반 제한중입니다. 등용 수락 불가.";

    //현 메세지 지움
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 수락 불가',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif(strpos($me['nations'], ",{$nation['nation']},") > 0) {
    $mylog[count($mylog)] = "<C>●</>이미 임관했었던 국가입니다. 등용 수락 불가.";

    //현 메세지 지움
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 수락 불가',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} else {
    $youlog[count($youlog)] = "<C>●</><Y>$me[name]</>(이)가 등용을 거부했습니다.";
    $mylog[count($mylog)] = "<C>●</><D>$nation[name]</>(으)로 망명을 거부했습니다.";

    //현 메세지 지움
    $query = "update general set msg{$num}='$nation[name](으)로 등용 제의 거부',msg{$num}_type='10' where no='$me[no]'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

pushGenLog($me, $mylog);
pushGenLog($you, $youlog);
pushAllLog($alllog);
pushHistory($connect, $history);

echo "<script>location.replace('msglist.php');</script>";
