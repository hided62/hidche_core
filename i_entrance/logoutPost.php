<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터


$SESSION->logout();
unset($_SESSION['access_token']);
setcookie("hello", "", time()-3600);

echo json_encode([
    'result'=>'SUCCESS'
]);