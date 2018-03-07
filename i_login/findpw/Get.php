<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_config/DB.php');

$registeredCount = getRootDB()->queryFirstField('SELECT COUNT(`NO`) AS CNT FROM MEMBER');
$response['registeredCount'] = $registeredCount;
$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


