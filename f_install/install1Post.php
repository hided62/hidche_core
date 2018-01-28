<?php
require_once('_common.php');
// dbHost, dbId, dbPw, dbName, mailHost, mailPort, mailId, mailPw
$dbHost = $_POST['dbHost'];
$dbId = $_POST['dbId'];
$dbPw = $_POST['dbPw'];
$dbName = $_POST['dbName'];
$mailHost = $_POST['mailHost'];
$mailPort = $_POST['mailPort'];
$mailId = $_POST['mailId'];
$mailPw = $_POST['mailPw'];
$mailAddr = $_POST['mailAddr'];


require_once(ROOT.W.F_FUNC.W.'class._DB.php');
require_once(ROOT.W.F_FUNC.W.'class._String.php');

if(file_exists(ROOT.W.D_SETTING.W.SET.PHP)) ErrorToScreen('이미 설치되어 있습니다. 재설치하려면 설정 파일을 지우세요.');
if(fileperms(ROOT.W.D_SETTING.W) != 040707 && fileperms(ROOT.W.D_SETTING.W) != 040777) ErrorToScreen('설정 디렉토리 권한을 707 또는 777로 설정해주세요.');

$DB = new _DB($dbHost, $dbId, $dbPw, $dbName);

//로그 등 삭제

$tables = array(
    'SYSTEM',
    'MEMBER',
    'DONATION',
    'EMAIL'
);

//테이블 삭제
for($i=0; $i < count($tables); $i++) {
    $query = "DROP TABLE {$tables[$i]}";
    $DB->QueryNoError($query);
}

//테이블 생성
$fp = @fopen('common_schema.sql', 'r');
$file = @fread($fp, filesize('common_schema.sql'));
@fclose($fp);

$querys = explode(';', $file);
foreach($querys as $query) {
    $query = trim($query);
    if($query != "") {
        $DB->Query($query);
    }
}

// 파일로 DB 정보 저장
$file = @fopen(ROOT.W.D_SETTING.W.SET.PHP, 'w') or ErrorToScreen('설정 실패. 디렉토리의 퍼미션을 707로 주십시요');
@fwrite($file, "<?php /*\n{$dbHost}\n{$dbId}\n{$dbPw}\n{$dbName}\n{$mailHost}\n{$mailPort}\n{$mailId}\n{$mailPw}\n{$mailAddr}\n */\n") or ErrorToScreen('설정 실패. 디렉토리의 퍼미션을 707로 주십시요');
@fclose($file);
@chmod(ROOT.W.D_SETTING.W.SET.PHP, 0604);

?>

<!--<meta HTTP-EQUIV="refresh" CONTENT="0; URL=install2.php">-->
Go to install2.php<br>
Todo : Back to replace code
