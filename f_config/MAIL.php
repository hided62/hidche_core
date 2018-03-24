<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_func/class._Mail.php');
require_once(ROOT.'/f_config/SETTING.php');

if($SETTING->isExists()) {
    $MAIL = new _Mail();
} else {
    Error('설정 파일이 없습니다. 설정을 먼저 하십시요!');
}


