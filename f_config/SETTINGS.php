<?php
require('_common.php');
require(ROOT.W.F_FUNC.W.'class._Setting.php');

$i = 0;
foreach($_serverDirs as $serverDir) {
    $SETTINGS[$i] = new _Setting(ROOT.W.$serverDir.W.D_SETTING.W.SET.PHP);
    $i++;
}


