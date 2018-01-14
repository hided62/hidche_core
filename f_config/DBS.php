<?php
require('_common.php');
require(ROOT.W.F_FUNC.W.'class._DB.php');
require(ROOT.W.F_CONFIG.W.'SETTINGS'.PHP);

for($i=0; $i < $_serverCount; $i++) {
    if(!$SETTINGS[$i]->IsExist()) {
    } else {
        $DBS[$i] = new _DB($SETTINGS[$i]->DBHost(), $SETTINGS[$i]->DBId(), $SETTINGS[$i]->DBPw(), $SETTINGS[$i]->DBName());
    }
}


