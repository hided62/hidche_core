<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._Mail.php');
require_once(ROOT.W.F_CONFIG.W.SETTING.PHP);

if($SETTING->IsExist()) {
    $MAIL = new _Mail($SETTING->MailHost(), $SETTING->MailPort(), $SETTING->MailId(), $SETTING->MailPw(), $SETTING->MailAddr());
} else {
    Error('설정 파일이 없습니다. 설정을 먼저 하십시요!');
}

?>
