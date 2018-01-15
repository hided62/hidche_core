<?php
// 외부 파라미터

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

$rs = $DB->Select('ID, NAME, GRADE, PICTURE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

$response['id'] = $member['ID'];
$response['name'] = $member['NAME'];
if($member['GRADE'] == 6) {
    $response['grade'] = '운영자';
} elseif($member['GRADE'] == 5) {
    $response['grade'] = '부운영자';
} elseif($member['GRADE'] == 4) {
    $response['grade'] = '특별회원';
} elseif($member['GRADE'] == 3) {
    $response['grade'] = '참여회원';
} elseif($member['GRADE'] == 2) {
    $response['grade'] = '참여회원';
} elseif($member['GRADE'] == 1) {
    $response['grade'] = '일반회원';
} elseif($member['GRADE'] == 0) {
    $response['grade'] = '블럭회원';
}
if($member['PICTURE'] == '') {
    $response['picture0'] = IMAGE.W.'default.jpg';
    $response['picture1'] = IMAGE.W.'default.jpg';
} else {
    $response['picture0'] = IMAGE.W.$member['PICTURE'];
    $response['picture1'] = '../d_pic/'.$member['PICTURE'];
}

$response['donation'] = '';

$rs = $DB->Select('DATE, AMOUNT, CUMUL', 'DONATION', "ID='{$member['ID']}'");
$count = $DB->Count($rs);
if($count > 0) {
    $response['donation'] = '
        <table id="EntranceManage_002000" class="bg0">
            <th id="EntranceManage_00200000" class="bg2" colspan="4">참 여 기 록</th>
            <tr>
                <td class="EntranceManage_Sequence bg1">순번</td>
                <td class="EntranceManage_Date bg1">일자</td>
                <td class="EntranceManage_Amount bg1">금액</td>
                <td class="EntranceManage_Cumul bg1">누적</td>
            </tr>
    ';

    $i = 1;
    while($DB->HasNext($rs)) {
        $donation = $DB->Next($rs);

        $response['donation'] .= "
            <tr>
                <td class='EntranceManage_Sequence'>{$i}</td>
                <td class='EntranceManage_Date'>{$donation['DATE']}</td>
                <td class='EntranceManage_Amount'>{$donation['AMOUNT']}</td>
                <td class='EntranceManage_Cumul'>{$donation['CUMUL']}</td>
            </tr>
        ";
        $i++;
    }

    $response['donation'] .= '
        </table>
    ';
}

$response['result'] = 'SUCCESS';

sleep(1);
echo json_encode($response);


