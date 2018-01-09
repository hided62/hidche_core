<?
// 외부 파라미터

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

$rs = $DB->Select('GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

if($member['GRADE'] < 6) {
    $response['result'] = 'FAIL';
    $response['msg'] = '운영자 권한이 없습니다.';
} else {
    //개인누적계산
    $rs = $DB->Select('ID', 'DONATION', '1', 'ID');
    while($DB->HasNext($rs)) {
        $donator = $DB->Next($rs);
        $cumul = 0;

        $rs2 = $DB->Select('NO, AMOUNT', 'DONATION', "ID='{$donator['ID']}' ORDER BY DATE, NO");
        while($DB->HasNext($rs2)) {
            $donation = $DB->Next($rs2);
            $cumul += $donation['AMOUNT'];

            $DB->Update('DONATION', "CUMUL='{$cumul}'", "NO='{$donation['NO']}'");
        }
    }

    $total = 0;
    //총누적계산
    $rs = $DB->Select('NO, AMOUNT', 'DONATION', '1 ORDER BY DATE, NO');
    while($DB->HasNext($rs)) {
        $donation = $DB->Next($rs);
        $total += $donation['AMOUNT'];

        $DB->Update('DONATION', "TOTAL='{$total}'", "NO='{$donation['NO']}'");
    }

    $response['result'] = 'SUCCESS';
}

echo json_encode($response);

?>
