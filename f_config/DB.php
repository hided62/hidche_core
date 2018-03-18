<?php
require_once('_common.php');
require_once(ROOT.'/f_config/SETTING.php');

if($SETTING->isExists()) {
    require_once($SETTING->getSettingFile());
} else {
    Error('설정 파일이 없습니다. 설정을 먼저 하십시요!');
}
