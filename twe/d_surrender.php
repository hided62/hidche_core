<?php
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();

$query = "select year,month from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,level,history,picture,imgsvr from general where no_member='{$_SESSION['noMember']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$query = "select no,nation,history from general where no='$gen'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$you = MYDB_fetch_array($result);

$query = "select nation,name,power,gold,rice,surlimit,history,color from nation where nation='{$you['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$younation = MYDB_fetch_array($result);

$query = "select nation,name,power,surlimit,color,dip{$num} as dipmsg from nation where nation='{$me['nation']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$mynation = MYDB_fetch_array($result);

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
//대상국이 외교 진행중(선포중,교전중,합병수락중,통합수락중)일때
$query = "select state from diplomacy where me='{$you['nation']}' and (state='0' or state='1' or state='3' or state='5')";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$dipcount = MYDB_num_rows($result);
//대상국B이 아국C과 교전중인 국가A와 불가침중일때 불가(A=B 불가침, A=C(아국) 교전, B<-C(아국) 항복)
$query = "select you,state from diplomacy where me='{$me['nation']}' and (state='0' or state='1')";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$warcount = MYDB_num_rows($result);
// 아국과 교전중인 국가 골라냄
$valid = 1;
for($i=0; $i < $warcount; $i++) {
    $acdip = MYDB_fetch_array($result);
    //교전중 국가A와 대상국B 외교 확인
    $query = "select state from diplomacy where me='{$acdip['you']}' and you='{$you['nation']}'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $abdip = MYDB_fetch_array($dipresult);

    if($abdip['state'] == 7) {
        $valid = 0;
    }
}

if($ok == "수락") {
    // 서신 보낸 후 멸망 했을때.
    if($me['level'] < 5) {
        $mylog[count($mylog)] = "<C>●</>수뇌부가 아니므로 불가능합니다. 투항 실패.";
    } elseif($mynation['surlimit'] > 0) {
        $mylog[count($mylog)] = "<C>●</>본국이 외교제한이 지나지 않았습니다. 투항 실패.";
    } elseif($younation['surlimit'] > 0) {
        $mylog[count($mylog)] = "<C>●</>상대국이 외교제한이 지나지 않았습니다. 투항 실패.";
//    } elseif($losecitynum == 0) {
//        $mylog[count($mylog)] = "<C>●</>방랑군이므로 불가능합니다. 투항 실패.";
    } elseif($wincitynum == 0) {
        $mylog[count($mylog)] = "<C>●</>상대가 방랑군이므로 불가능합니다. 투항 실패.";
    } elseif($younation['power'] / $mynation['power'] <= 3) {
        $mylog[count($mylog)] = "<C>●</>아국과 상대국의 국력차이가 크지 않습니다. 투항 실패.";
    } elseif($losecitynum != 0 && !isClose($connect, $younation['nation'], $mynation['nation'])) {
        $mylog[count($mylog)] = "<C>●</>인접한 국가가 아니므로 불가능합니다. 투항 실패.";
    } elseif($dip['state'] == 0) {
        $mylog[count($mylog)] = "<C>●</>아국과 교전중입니다. 투항 실패.";
    } elseif($dipcount != 0) {
        $mylog[count($mylog)] = "<C>●</>상대국이 외교 진행중입니다. 투항 실패.";
    } elseif($valid == 0) {
        $mylog[count($mylog)] = "<C>●</>상대국이 아국의 교전국과 불가침중입니다. 투항 실패.";
    } elseif($mynation['dipmsg'] == "") {
        $mylog[count($mylog)] = "<C>●</>이미 거절했습니다. 불가침 실패.";
    } else {
        $youlog[count($youlog)] = "<C>●</><D><b>{$mynation['name']}</b></>(이)가 합병에 동의했습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【합병시도】</b></><D><b>{$mynation['name']}</b></>(와)과 <D><b>{$younation['name']}</b></>(이)가 합병을 시도합니다.";
        $mylog[count($mylog)] = "<C>●</><D><b>{$younation['name']}</b></>(와)과 합병에 동의했습니다.";
        $alllog[count($alllog)] = "<C>●</>{$admin['month']}월:<Y>{$me['name']}</>(이)가 <D><b>{$younation['name']}</b></>(와)과 <M>합병</>에 동의하였습니다.";
        $you = addHistory($connect, $you, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>(와)과 합병 시도");
        $me = addHistory($connect, $me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>(와)과 합병 시도");

        //외교 변경
        $query = "update diplomacy set state='5',term='24' where me='{$mynation['nation']}' and you='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update diplomacy set state='6',term='24' where me='{$younation['nation']}' and you='{$mynation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        //국메로 저장
        $msg = "【외교】{$admin['year']}년 {$admin['month']}월:{$younation['name']}(와)과 합병에 동의";
        $youmsg = "【외교】{$admin['year']}년 {$admin['month']}월:{$mynation['name']}(와)과 합병에 동의";

        PushMsg(2, $mynation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$mynation['name']}▶", $mynation['color'], $younation['name'], $younation['color'], $msg);
        PushMsg(3, $younation['nation'], $me['picture'], $me['imgsvr'], "{$me['name']}:{$mynation['name']}▶", $mynation['color'], $younation['name'], $younation['color'], $youmsg);
    }

    //현 메세지 지움
    $query = "update nation set dip{$num}='',dip{$num}_who='0',dip{$num}_when='' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} else {
    $youlog[count($youlog)] = "<C>●</><Y>{$mynation['name']}</>(이)가 항복을 거부했습니다.";
    $mylog[count($mylog)] = "<C>●</><D>{$younation['name']}</>(으)로 항복을 거부했습니다.";

    //현 메세지 지움
    $query = "update nation set dip{$num}='',dip{$num}_who='0',dip{$num}_when='' where nation='{$me['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

pushGenLog($me, $mylog);
pushGenLog($you, $youlog);
pushAllLog($alllog);
pushHistory($connect, $history);

echo "<script>location.replace('msglist.php');</script>";

