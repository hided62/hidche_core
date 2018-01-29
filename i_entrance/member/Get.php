<?php
require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

use utilphp\util as util;

// 외부 파라미터
// $_GET['select'] : 정렬선택
$select = $_GET['select'];

$rs = $DB->Select('GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);
$donCumul = [];
$donDate = [];
if($member['GRADE'] < 6) {
    $response['result'] = 'FAIL';
    $response['msg'] = '운영자 권한이 없습니다.';
} else {
    $rs = $DB->Select('ID, CUMUL, DATE', 'DONATION', '1 ORDER BY NO');
    while($DB->HasNext($rs)) {
        $donation = $DB->Next($rs);

        $donCumul[$donation['ID']] = $donation['CUMUL'];
        $donDate[$donation['ID']]  = $donation['DATE'];
    }

    $whereStr = '';
    switch($select) {
        case 0: $whereStr = '1 ORDER BY NO'; break;
        case 1: $whereStr = '1 ORDER BY ID'; break;
        case 2: $whereStr = '1 ORDER BY NAME'; break;
        case 3: $whereStr = '1 ORDER BY PID'; break;
        case 4: $whereStr = '1 ORDER BY IP'; break;
        case 5: $whereStr = '1 ORDER BY GRADE DESC'; break;
        case 6: $whereStr = '1 ORDER BY REG_NUM DESC'; break;
        case 7: $whereStr = '1 ORDER BY REG_DATE DESC'; break;
    }

    $response['lists'] = '';
    $response['members'] = '<table id="EntranceMember_000500" class="bg0">';

    $rs = $DB->Select('NO, ID, PID, NAME, IP, PICTURE, IMGSVR, GRADE, REG_NUM, REG_DATE, BLOCK_NUM, BLOCK_DATE, QUIT', 'MEMBER', $whereStr);
    $count = $DB->Count($rs);
    while($DB->HasNext($rs)) {
        $member = $DB->Next($rs);
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
        <td class='EntranceMember_00050010'>".util::array_get($donCumul[$member['ID']],'0')."</td>
        <td class='EntranceMember_00050011'>".util::array_get($donDate[$member['ID']],'')."</td>
        <td class='EntranceMember_00050012'>{$member['PICTURE']}</td>
        <td class='EntranceMember_00050013'>{$member['IMGSVR']}</td>
        <td class='EntranceMember_00050014'>{$member['QUIT']}</td>
    </tr>";
    }
    $response['members'] .= '</table>';

    $response['count'] = "(0000/{$count})";

    $rs = $DB->Select('REG, LOGIN', 'SYSTEM', "NO='1'");
    $system = $DB->Get($rs);

    $response['state'] = "가입: {$system['REG']}, 로그인: {$system['LOGIN']}";

    $response['result'] = 'SUCCESS';
}

sleep(1);
echo json_encode($response);


