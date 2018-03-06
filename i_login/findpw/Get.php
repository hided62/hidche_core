<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);

$registeredCount = getRootDB()->queryFirstField('SELECT COUNT(`NO`) AS CNT FROM MEMBER');
$response['registeredCount'] = $registeredCount;
$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


