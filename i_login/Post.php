<?
// 외부 파라미터
// $_POST['id'] : ID
// $_POST['pw'] : PW
// $_POST['conmsg'] : 접속장소
$id = $_POST['id'];
$pw = $_POST['pw'];
$conmsg = $_POST['conmsg'];

require_once('_common.php');
require_once(ROOT.W.F_FUNC.W.'class._JSON.php');
require_once(ROOT.W.F_FUNC.W.'class._Session.php');
require_once(ROOT.W.F_FUNC.W.'class._String.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);

$id = _String::NoSpecialCharacter($id);
$pw = substr($pw, 0, 32);

$response['result'] = 'FAIL';

_Session::TrashSession();

$SESSION = new _Session();

$rs = $DB->Select('NO, GRADE, QUIT', 'MEMBER', "ID='{$id}' AND PW='{$pw}'");
if($DB->Count($rs) == 1) {
    $member = $DB->Get($rs);

    if($member['QUIT'] != 'Y') {
        $rs = $DB->Select('LOGIN', 'SYSTEM', "NO='1'");
        $system = $DB->Get($rs);

        if($system['LOGIN'] == Y || $member['GRADE'] >= 5) {
            $SESSION->Login($member['NO']);
            $DB->Update('MEMBER', "CONMSG='{$conmsg}', IP='{$_SERVER['REMOTE_ADDR']}'", "NO='{$member['NO']}'");

            $response['result'] = 'SUCCESS';
        } else {
            $response['result'] = 'FAIL';
            $response['msg'] = '로그인 실패! 현재는 로그인이 금지되어있습니다.';
        }
    } else {
        $response['result'] = 'FAIL';
        $response['msg'] = '탈퇴 신청중인 계정입니다!';
    }
} else {
    $response['result'] = 'FAIL';
    $response['msg'] = '로그인 실패! ID, PW를 확인해주세요.';
}

sleep(1);
echo json_encode($response);

?>
