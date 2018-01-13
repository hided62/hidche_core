<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);

$rs = $DB->Select('COUNT(*) AS CNT', 'MEMBER');

$registeredCount = $DB->Get($rs);
$response['registeredCount'] = $registeredCount['CNT'];
$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


