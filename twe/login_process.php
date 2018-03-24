<?php
namespace sammo;

include "lib.php";
include "func.php";

//FIXME: 이 프로세스 전체가 필요없을 수 있다. session 디렉토리를 관리하지 않거나, 자동 로그인을 처리하는 방법을 생각할 것.



$db = getDB();

$userID = Session::getUserID();

//회원 테이블에서 정보확인
$me= $db->queryFirstRow('select no,name,nation,block,killturn from general where owner= %s', $userID);
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);


if(!$me) {
    MessageBox("캐릭터가 없습니다!!!");
    //TODO:login_process를 rest 형태로 처리
    //header ("Location: index.php");
    exit(0);
}


switch($me['block']) {
case 1:
    MessageBox("비매너 발언으로 인해, 발언권이 제한됩니다."); break;
case 2:
    MessageBox("현재 블럭된 계정입니다. 턴 실행이 제한됩니다.");
    MessageBox("절대 1계정만 사용하십시오! {$me['killturn']}시간 후 재등록 가능합니다."); break;
case 3:
    MessageBox("현재 블럭된 계정입니다. 발언권과 턴 실행이 제한됩니다.");
    MessageBox("절대 1계정만 사용하십시오! {$me['killturn']}시간 후 재등록 가능합니다."); break;
}

$_SESSION[getServPrefix().'p_no']     = toInt($me['no']);
$_SESSION[getServPrefix().'p_name']   = $me['name'];
$_SESSION['p_time']   = time();

$date = date('Y-m-d H:i:s');

//
$query="update general set logcnt=logcnt+1,ip='{$_SESSION['p_ip']}',lastconnect='$date',conmsg='$conmsg' where owner='{$_SESSION['userID']}'";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$date = date('Y_m_d H:i:s');
$date2 = substr($date, 0, 10);
$fp = fopen("logs/_{$date2}_login.txt", "a");
$msg = StringUtil::Fill2($date,20," ").tab2($id,13," ").StringUtil::Fill2($me['name'],13," ").StringUtil::Fill2($_SESSION['p_ip'],16," ");
fwrite($fp, $msg."\n");
fclose($fp);

header ("Location: index.php");

MYDB_close($connect);
