<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터


$SESSION->Logout();

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


