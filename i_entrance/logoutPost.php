<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터


$SESSION->logout();

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


