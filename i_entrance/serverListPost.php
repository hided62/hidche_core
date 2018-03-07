<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._JSON.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

// 외부 파라미터
$response['serverCount'] = count($serverList);
$response['servers'] = '';

foreach($serverList as $serverInfo){
    list($serverKorName, $serverColor, $setting) = $serverInfo;

    $serverText = "<span style='font-weight:bold;font-size:1.4em;color:{$serverColor}'>{$serverKorName}</span>";
    if(!$serverInfo->isExist()) {
        $response['servers'] .= "
<div class='Entrance_ServerList'>
    <div class='Entrance_ServerListServer'><br>{$serverText}</div>
    <div class='Entrance_ServerListDown'><br>- 폐 쇄 중 -</div>
</div>
";
    } else {
        $response['servers'] .= "
<div class='Entrance_ServerList ServerActive' data-server='{$serverInfo->getServerName()}>
    <div class='Entrance_ServerListServer'><br>{$serverText}</div>
</div>";
        //TODO: 이에 대해 동적으로 j_server_basic_info.php 를 불러서 기록해야함.
    }
}

$response['result'] = 'SUCCESS';
echo json_encode($response);


