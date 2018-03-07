<?php
require('_common.php');
require(ROOT.'/f_config/SETTING.php');

if($SETTING->isExist()){
    header ("Location:i_entrance/entrance.php");
}
else{
    header ('Location:install.php');
}
