<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_config/DB.php');

$registeredCount = getRootDB()->queryFirstField('SELECT COUNT(`NO`) FROM MEMBER');
$response['registeredCount'] = $registeredCount['CNT'];
$response['result'] = 'SUCCESS';

echo json_encode($response);


