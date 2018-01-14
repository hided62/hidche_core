<?php
// 외부 파라미터

require('_common.php');
require(ROOT.W.F_FUNC.W.'class._JSON.php');
require(ROOT.W.F_CONFIG.W.DB.PHP);
require(ROOT.W.F_CONFIG.W.SESSION.PHP);

$SESSION->Logout();

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


