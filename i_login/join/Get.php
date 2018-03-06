<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);

$registeredCount = getRootDB()->queryFirstField('SELECT COUNT(`NO`) FROM MEMBER');
$response['registeredCount'] = $registeredCount['CNT'];
$response['result'] = 'SUCCESS';

echo json_encode($response);


