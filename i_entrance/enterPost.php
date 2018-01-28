<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

// 외부 파라미터
// $_POST['serverDir'] : 로그인할 서버 디렉토리
//$serverDir = $_POST['serverDir']; //TODO:쓸모 없어보이는데?


$rs = $DB->Select('ID, PW, CONMSG', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

$response['id'] = $member['ID'];
$response['pw'] = $member['PW'].md5(rand()%100000000);;
$response['conmsg'] = $member['CONMSG'];

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);
