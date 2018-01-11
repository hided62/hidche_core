<?php
// 외부 파라미터

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_FUNC.W.'class._Time.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

$rs = $DB->Select('GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

if($member['GRADE'] < 6) {
    $response['result'] = 'FAIL';
    $response['msg'] = '운영자 권한이 없습니다.';
} else {
    $response['donations'] = '<table id="EntranceDonation_000500" class="bg0">';

    $rs = $DB->Select('NO, ID, NAME, RNAME, SUBNAME, DATE, AMOUNT, CUMUL, TOTAL', 'DONATION', '1 ORDER BY DATE DESC, NO DESC');
    while($DB->HasNext($rs)) {
        $donation = $DB->Next($rs);

        $response['donations'] .= "
    <tr>
        <td class='EntranceDonation_00050000'>{$donation['NO']}</td>
        <td class='EntranceDonation_00050001'>{$donation['DATE']}</td>
        <td class='EntranceDonation_00050002'>{$donation['ID']}</td>
        <td class='EntranceDonation_00050003'>{$donation['NAME']}</td>
        <td class='EntranceDonation_00050004'>{$donation['RNAME']}</td>
        <td class='EntranceDonation_00050005'>{$donation['SUBNAME']}</td>
        <td class='EntranceDonation_00050006'>{$donation['AMOUNT']}</td>
        <td class='EntranceDonation_00050007'>{$donation['CUMUL']}</td>
        <td class='EntranceDonation_00050008'>{$donation['TOTAL']}</td>
    </tr>";
    }
    $response['donations'] .= '</table>';
    $response['date'] = _Time::DateToday();

    $response['result'] = 'SUCCESS';
}

sleep(1);
echo json_encode($response);

?>
