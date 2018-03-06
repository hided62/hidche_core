<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._Mail.php');
require_once(ROOT.W.F_CONFIG.W.SETTING.PHP);

if($SETTING->isExist()) {
    $MAIL = new _Mail();
} else {
    Error('설정 파일이 없습니다. 설정을 먼저 하십시요!');
}


