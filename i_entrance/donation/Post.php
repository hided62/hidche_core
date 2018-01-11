<?php
// 외부 파라미터
// $_POST['date'] : 일자
// $_POST['id'] : ID
// $_POST['name'] : 이름
// $_POST['rname'] : 입금자
// $_POST['subname'] : 닉네임
// $_POST['amount'] : 금액
$date = $_POST['date'];
$id = $_POST['id'];
$name = $_POST['name'];
$rname = $_POST['rname'];
$subname = $_POST['subname'];
$amount = $_POST['amount'];

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
    if($subname == "") {
        $rs = $DB->Select('NAME', 'MEMBER', "ID='{$id}'");
        $donator = $DB->Get($rs);
        $subname = $donator['NAME'];
    }

    $rs = $DB->Select('SUM(AMOUNT) AS CUM', 'DONATION', "ID='{$id}'");
    $donator = $DB->Get($rs);
    $cumul = $donator['CUM'] + $amount;

    $rs = $DB->Select('SUM(AMOUNT) AS TOT', 'DONATION');
    $donator = $DB->Get($rs);
    $total = $donator['TOT'] + $amount;

    $DB->InsertArray('DONATION', array(
        ID      =>  $id,
        NAME    =>  $name,
        RNAME   =>  $rname,
        SUBNAME =>  $subname,
        DATE    =>  $date,
        AMOUNT  =>  $amount,
        CUMUL   =>  $cumul,
        TOTAL   =>  $total
    ));

    $response['result'] = 'SUCCESS';
}

echo json_encode($response);

?>
