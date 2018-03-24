<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();

$query = "select year,month from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,level,picture,imgsvr from general where owner='{$_SESSION['userID']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select no,nation from general where no='$gen'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$you = MYDB_fetch_array($result);

$query = "select nation,name,surlimit,color from nation where nation='{$you['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$younation = MYDB_fetch_array($result);

$query = "select nation,name,gold,rice,surlimit,color,dip{$num} as dipmsg from nation where nation='{$me['nation']}'";
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
$query = "select state from diplomacy where me='{$me['nation']}' and you='{$you['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$dip = MYDB_fetch_array($result);

if($ok == "수락") {
    // 서신 보낸 후 멸망,선양 등 했을때.
    if($me['level'] < 5) {
        $mylog[] = "<C>●</>수뇌부가 아니므로 불가능합니다. 종전 실패.";
    } elseif($losecitynum == 0) {
        $mylog[] = "<C>●</>방랑군이므로 불가능합니다. 종전 실패.";
    } elseif($wincitynum == 0) {
        $mylog[] = "<C>●</>상대가 방랑군이므로 불가능합니다. 종전 실패.";
    } elseif($dip['state'] != 0 && $dip['state'] != 1) {
        $mylog[] = "<C>●</>아국과 교전중이 아닙니다. 종전 실패.";
    } elseif($mynation['dipmsg'] == "") {
        $mylog[] = "<C>●</>이미 거절했습니다. 불가침 실패.";
    } else {
        $alllog[] = "<C>●</>{$admin['month']}월:<Y>{$me['name']}</>(이)가 <D><b>{$younation['name']}</b></>(와)과 <M>종전 합의</> 하였습니다.";
        $history[] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【종전】</b></><D><b>{$mynation['name']}</b></>(이)가 <D><b>{$younation['name']}</b></>(와)과 <M>종전 합의</> 하였습니다.";
        $youlog[] = "<C>●</><D><b>{$mynation['name']}</b></>(와)과 종전에 성공했습니다.";
        $mylog[] = "<C>●</><D><b>{$younation['name']}</b></>(와)과 종전에 합의했습니다.";
        addHistory($you, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>(와)과 종전 성공");
        addHistory($me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>(와)과 종전 수락");

        //외교 변경
        $query = "update diplomacy set state='2',term='0' where me='{$mynation['nation']}' and you='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update diplomacy set state='2',term='0' where me='{$younation['nation']}' and you='{$mynation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //외교제한
        $query = "update nation set surlimit=24 where nation='{$mynation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update nation set surlimit=24 where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //국메로 저장
        $msg = "【외교】{$admin['year']}년 {$admin['month']}월:{$younation['name']}(와)과 종전 동의";
        $youmsg = "【외교】{$admin['year']}년 {$admin['month']}월:{$mynation['name']}(와)과 종전 동의";

        PushMsg(2, $mynation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$mynation['name']}▶", $mynation['color'], $younation['name'], $younation['color'], $msg);
        PushMsg(3, $younation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$mynation['name']}▶", $mynation['color'], $younation['name'], $younation['color'], $youmsg);
    }

    //현 메세지 지움
    $query = "update nation set dip{$num}='',dip{$num}_who='0',dip{$num}_when='' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} else {
    $youlog[] = "<C>●</><Y>{$mynation['name']}</>(이)가 종전을 거부했습니다.";
    $mylog[] = "<C>●</><D>{$younation['name']}</>(와)과 종전을 거부했습니다.";

    //현 메세지 지움
    $query = "update nation set dip{$num}='',dip{$num}_who='0',dip{$num}_when='' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

pushGenLog($me, $mylog);
pushGenLog($you, $youlog);
pushAllLog($alllog);
pushHistory($history);

echo "<script>location.replace('msglist.php');</script>";

