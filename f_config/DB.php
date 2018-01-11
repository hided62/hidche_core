<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._DB.php');
require_once(ROOT.W.F_CONFIG.W.SETTING.PHP);

if($SETTING->IsExist()) {
    $DB = new _DB($SETTING->DBHost(), $SETTING->DBId(), $SETTING->DBPw(), $SETTING->DBName());
} else {
    Error('설정 파일이 없습니다. 설정을 먼저 하십시요!');
}

?>
