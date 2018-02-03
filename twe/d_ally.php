<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();

//$when = ($when + 1) * 2;

$query = "select year,month from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,level,history,picture,imgsvr from general where owner='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select no,nation,history,name from general where no='$gen'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$you = MYDB_fetch_array($result);

$query = "select name,surlimit,history,color from nation where nation='{$you['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$younation = MYDB_fetch_array($result);

$query = "select name,gold,rice,surlimit,color,dip{$num} as dipmsg from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$mynation = MYDB_fetch_array($result);

$query = "select pop from city where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$citycount = MYDB_num_rows($result);

$query = "select city from city where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$losecitynum = MYDB_num_rows($result);

$query = "select city from city where nation='{$you['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$wincitynum = MYDB_num_rows($result);

//아국과의 관계
$query = "select state,term from diplomacy where me='{$me['nation']}' and you='{$you['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$dip = MYDB_fetch_array($result);

//상대국의 제의조건
$query = "select reserved from diplomacy where me='{$you['nation']}' and you='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$dip2 = MYDB_fetch_array($result);

if($ok == "수락") {
    // 서신 보낸 후 멸망,선양 등 했을때.
    if($me['level'] < 5) {
        $mylog[count($mylog)] = "<C>●</>수뇌부가 아니므로 불가능합니다. 불가침 실패.";
    } elseif($dip['term'] == $when*12 && $dip['state'] == 7) {
        $mylog[count($mylog)] = "<C>●</>이미 수락했습니다. 불가침 실패.";
    } elseif($losecitynum == 0) {
        $mylog[count($mylog)] = "<C>●</>방랑군이므로 불가능합니다. 불가침 실패.";
    } elseif($wincitynum == 0) {
        $mylog[count($mylog)] = "<C>●</>상대가 방랑군이므로 불가능합니다. 불가침 실패.";
    } elseif($dip['state'] == 0) {
        $mylog[count($mylog)] = "<C>●</>아국과 교전중입니다. 불가침 실패.";
    } elseif($dip['state'] == 1) {
        $mylog[count($mylog)] = "<C>●</>아국과 선포중입니다. 불가침 실패.";
    } elseif($dip['state'] == 3 || $dip['state'] == 4) {
        $mylog[count($mylog)] = "<C>●</>아국과 합병중입니다. 불가침 실패.";
    } elseif($mynation['dipmsg'] == "") {
        $mylog[count($mylog)] = "<C>●</>이미 거절했습니다. 불가침 실패.";
    } else {
//        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$me['name']}</>(이)가 <D><b>{$younation['name']}</b></>(와)과 <M>불가침</>에 합의.";
//        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【불가침】</b></><D><b>{$mynation['name']}</b></>(이)가 <D><b>{$younation['name']}</b></>(와)과 불가침 조약을 체결했습니다.";

        $youlog[count($youlog)] = "<C>●</><D><b>{$mynation['name']}</b></>(와)과 <C>$when</>년 불가침에 성공했습니다.";
        $mylog[count($mylog)] = "<C>●</><D><b>{$younation['name']}</b></>(와)과 <C>$when</>년 불가침에 합의했습니다.";
        $you = addHistory($connect, $you, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>(와)과 {$when}년 불가침 성공");
        $me = addHistory($connect, $me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>(와)과 {$when}년 불가침 수락");

        //국메로 저장
        $msg = "【외교】{$admin['year']}년 {$admin['month']}월:{$younation['name']}(와)과 {$when}년 불가침 합의, 비고: {$dip2['reserved']}";
        $youmsg = "【외교】{$admin['year']}년 {$admin['month']}월:{$mynation['name']}(와)과 {$when}년 불가침 합의, 비고: {$dip2['reserved']}";

        PushMsg(2, $me['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$mynation['name']}▶", $mynation['color'], $younation['name'], $younation['color'], $msg);
        PushMsg(3, $you['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$mynation['name']}▶", $mynation['color'], $younation['name'], $younation['color'], $youmsg);

        $when = $when * 12;
        //외교 변경
        $query = "update diplomacy set state='7',term='$when',fixed='{$dip2['reserved']}' where me='{$me['nation']}' and you='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update diplomacy set state='7',term='$when',fixed='{$dip2['reserved']}' where me='{$you['nation']}' and you='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    //조건 지움
    $query = "update diplomacy set reserved='' where me='{$you['nation']}' and you='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //현 메세지 지움
    $query = "update nation set dip{$num}='',dip{$num}_who='0',dip{$num}_when='' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} else {
    $youlog[count($youlog)] = "<C>●</><Y>{$mynation['name']}</>(이)가 불가침을 거부했습니다.";
    $mylog[count($mylog)] = "<C>●</><D>{$younation['name']}</>(와)과 불가침을 거부했습니다.";

    //조건 지움
    $query = "update diplomacy set reserved='' where me='{$you['nation']}' and you='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //현 메세지 지움
    $query = "update nation set dip{$num}='',dip{$num}_who='0',dip{$num}_when='' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

pushGenLog($me, $mylog);
pushGenLog($you, $youlog);
//pushAllLog($alllog);
//pushHistory($connect, $history);

echo "<script>location.replace('msglist.php');</script>";

