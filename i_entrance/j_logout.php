<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터


$SESSION->logout();
unset($_SESSION['access_token']);
session_write_close();
setcookie("hello", "", time()-3600);

Json::die([
    'result'=>true
]);