<?php
namespace sammo;

require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
$session = Session::requireLogin();



// 외부 파라미터
// $_GET['select'] : 정렬선택
$select = $_GET['select'];

$db = RootDB::db();
$userGrade = $session->userGrade;
if($userGrade < 6) {
    Json::die([
        'result'=>'FAIL',
        'msg'=>'운영자 권한이 없습니다.'
    ]);
}

$orderByStr = '';
switch($select) {
    case 0: $orderByStr = 'ORDER BY NO'; break;
    case 1: $orderByStr = 'ORDER BY ID'; break;
    case 2: $orderByStr = 'ORDER BY NAME'; break;
    case 3: $orderByStr = 'ORDER BY PID'; break;
    case 4: $orderByStr = 'ORDER BY IP'; break;
    case 5: $orderByStr = 'ORDER BY GRADE DESC'; break;
    case 6: $orderByStr = 'ORDER BY REG_NUM DESC'; break;
    case 7: $orderByStr = 'ORDER BY REG_DATE DESC'; break;
}

$response['lists'] = '';
$response['members'] = '<table id="EntranceMember_000500" class="bg0">';

$members = $db->queryFirstRow('SELECT NO, ID, PID, NAME, IP, PICTURE, IMGSVR, GRADE, REG_NUM, REG_DATE, BLOCK_NUM, BLOCK_DATE, QUIT FROM MEMBER %l', $orderByStr);
$count = count($members);
foreach($members as $member){
    $member['PID'] = substr($member['PID'], 0, 8);
    $member['PICTURE'] = substr($member['PICTURE'], -8);

    if($member['GRADE'] == 2) { $member['GRADE'] = "<font color=skyblue>{$member['GRADE']}</font>"; }
    if($member['GRADE'] == 3) { $member['GRADE'] = "<font color=limegreen>{$member['GRADE']}</font>"; }
    if($member['GRADE'] == 4) { $member['GRADE'] = "<font color=cyan>{$member['GRADE']}</font>"; }
    if($member['GRADE'] == 5) { $member['GRADE'] = "<font color=blue>{$member['GRADE']}</font>"; }
    if($member['GRADE'] == 6) { $member['GRADE'] = "<font color=blue>{$member['GRADE']}</font>"; }

    $response['lists'] .= "<option value='{$member['NO']}'>{$member['NAME']}</option>";

    $response['members'] .= "
<tr>
    <td class='EntranceMember_00050000'>{$member['NO']}</td>
    <td class='EntranceMember_00050001'>{$member['ID']}</td>
    <td class='EntranceMember_00050002'>{$member['PID']}</td>
    <td class='EntranceMember_00050003'>{$member['NAME']}</td>
    <td class='EntranceMember_00050004'>{$member['IP']}</td>
    <td class='EntranceMember_00050005'>{$member['BLOCK_NUM']}</td>
    <td class='EntranceMember_00050006'>{$member['BLOCK_DATE']}</td>
    <td class='EntranceMember_00050007'>{$member['REG_NUM']}</td>
    <td class='EntranceMember_00050008'>{$member['REG_DATE']}</td>
    <td class='EntranceMember_00050009'>{$member['GRADE']}</td>
    <td class='EntranceMember_00050012'>{$member['PICTURE']}</td>
    <td class='EntranceMember_00050013'>{$member['IMGSVR']}</td>
    <td class='EntranceMember_00050014'>{$member['QUIT']}</td>
</tr>";
}
$response['members'] .= '</table>';

$response['count'] = "(0000/{$count})";

$system = RootDB::db()->queryFirstRow('SELECT `REG`, `LOGIN` FROM `SYSTEM` WHERE `NO`=1');

$response['state'] = "가입: {$system['REG']}, 로그인: {$system['LOGIN']}";

$response['result'] = 'SUCCESS';

Json::die($response);


