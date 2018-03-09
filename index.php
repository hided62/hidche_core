<?php
require('_common.php');
require(ROOT.'/f_config/SETTING.php');

if($SETTING->isExist()){
    header ("Location:i_login/login.php");
}
else{
    header ('Location:install.php');
}
