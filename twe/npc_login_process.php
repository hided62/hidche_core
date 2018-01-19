<?php
include "lib.php";
include "func.php";
$connect = dbConn();

$id = $_POST['id'];
$pw = $_POST['pw'];
$pw = md5($pw.$pw);

DeleteSession();

//회원 테이블에서 정보확인
$query="select no from general where user_id='$id'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me2 = MYDB_fetch_array($result);

//회원 테이블에서 정보확인
$query="select no,name,nation,block,killturn from general where user_id='$id' and password='$pw'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

if(!$me2) {
    MessageBox("캐릭터가 없습니다!!!");
    //echo "<script>location.replace('index.php');</script>";
    echo 'index.php';//TODO:debug all and replace
} elseif(!$me) {
    MessageBox("아이디나 암호가 올바르지 않습니다!!!");
    //echo "<script>location.replace('index.php');</script>";
    echo 'index.php';//TODO:debug all and replace
} else {
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

    $_SESSION['p_id']     = $id;
    $_SESSION['p_name']   = $me['name'];
    $_SESSION['p_nation'] = $me['nation'];
    $_SESSION['p_time']   = time();

    $date = date('Y-m-d H:i:s');

    $query="update general set logcnt=logcnt+1,ip='{$_SESSION['p_ip']}',lastconnect='$date',conmsg='$conmsg' where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //echo "<script>window.top.location.replace('./');</script>";
    echo './';//TODO:debug all and replace

    $date2 = substr($date, 0, 10);
    $fp = fopen("logs/_{$date2}-login.txt", "a");
    $msg = _String::Fill2($date,20," ")._String::Fill2($id,13," ")._String::Fill2($me['name'],13," ")._String::Fill2($_SESSION['p_ip'],16," ");
    fwrite($fp, $msg."\r\n");
    fclose($fp);
}

function DeleteSession() {
    $session_path = "data/session";  // 세션이저장된 디렉토리
    if(!$dir=@opendir($session_path)) echo "디렉토리를 열지못했습니다.";

    while($file=@readdir($dir)) {
        if(!strstr($file,'sess_')) continue;
        if(strpos($file,'sess_')!=0) continue;
        if (!$atime=@fileatime("$session_path/$file")) continue;
        if (time() > $atime + 86400) {  // 10대시 지난시간을 초로 계산해서 적어주시면 됩니다.
    //        $return = (@unlink("$session_path/$file"));
            @unlink("$session_path/$file");
        }
    }
    closedir($dir);
}

MYDB_close($connect);
