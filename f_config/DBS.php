<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._DB.php');
require_once(ROOT.W.F_CONFIG.W.'SETTINGS'.PHP);

for($i=0; $i < $_serverCount; $i++) {
    if(!$SETTINGS[$i]->IsExist()) {
    } else {
        $DBS[$i] = new _DB($SETTINGS[$i]->DBHost(), $SETTINGS[$i]->DBId(), $SETTINGS[$i]->DBPw(), $SETTINGS[$i]->DBName());
    }
}

?>
