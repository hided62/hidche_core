<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
$session = Session::requireLogin();

// 외부 파라미터


$session->logout();
unset($_SESSION['access_token']);
session_write_close();
setcookie("hello", "", time()-3600);

Json::die([
    'result'=>true
]);