<?php

namespace sammo;

require(__DIR__ . '/../vendor/autoload.php');

WebUtil::requireAJAX();

$session = Session::requireLogin(null);



// 외부 파라미터
// $_POST['action'] : 'notice', 'open', 'close', 'reset', 'reset_full'
// $_POST['notice'] : 공지
// $_POST['server'] : 서버 인덱스

$action = Util::getPost('action', 'string', '');
$notice = Util::getPost('notice', 'string', '');
$server = Util::getPost('server', 'string', '');

$db = RootDB::db();
$userGrade = $session->userGrade;
$acl = $session->acl;
$session->setReadOnly();

if ($userGrade < 5 && !$acl) {
    Json::die([
        'result' => false,
        'reason' => '운영자 권한이 없습니다.'
    ]);
}

function doServerModeSet($server, $action, Session $session): array
{

    $serverList = ServConfig::getServerList();
    $settingObj = $serverList[$server];
    $serverAcl = $session->acl[$server] ?? [];
    $userGrade = $session->userGrade;

    $serverDir = $settingObj->getShortName();
    $serverPath = $settingObj->getBasePath();
    $realServerPath = realpath(dirname(__FILE__)) . '/' . $serverPath;

    if ($action == 'close') { //폐쇄
        $doClose = false;
        if ($userGrade >= 5) {
            $doClose = true;
        } else if (in_array('openClose', $serverAcl)) {
            $doClose = true;
        }

        if (!$doClose && in_array('reset', $serverAcl) && file_exists($serverPath . '/d_setting/DB.php')) {
            require($serverPath . '/lib.php');
            $localGameStorage = KVStorage::getStorage(DB::db(), 'game_env');
            //천통 이후, 오픈 직후는 닫을 수 있음
            $localGameStorage->cacheValues(['isunited', 'startyear', 'year']);

            if ($localGameStorage->isunited) {
                $doClose = true;
            } else if ($localGameStorage->year < $localGameStorage->startyear + 2) {
                $doClose = true;
            } else{
                return [
                    'result' => false,
                    'reason' => '서버 시작 직후, 또는 천하통일 이후에만 닫을 수 있습니다.'
                ];
            }
        }

        if (!$doClose) {
            return [
                'result' => false,
                'reason' => '서버 닫기 권한이 부족합니다.'
            ];
        }
        if (!$settingObj->closeServer()) {
            return [
                'result' => false,
                'reason' => '닫기 실패'
            ];
        }
        return [
            'result' => true,
            'reason' => 'success'
        ];
    }

    if ($action == 'reset' && $userGrade >= 6) { //리셋
        //FIXME: reset, reset_full 구현
        if (file_exists($serverPath . '/d_setting/DB.php')) {
            @unlink($serverPath . '/d_setting/DB.php');
        }

        return [
            'result' => true,
            'reason' => 'success',
            'installURL' => $serverDir . "/install.php"
        ];
    }

    if ($action == 'open') { //오픈

        if($userGrade < 5 && !in_array('openClose', $serverAcl)){
            return [
                'result' => false,
                'reason' => '서버 열기 권한이 부족합니다.'
            ];
        }

        if (!$settingObj->openServer()) {
            return [
                'result' => false,
                'reason' => '오픈 실패'
            ];
        }
        return [
            'result' => true,
            'reason' => 'success'
        ];
    }

    return [
        'result' => false,
        'reason' => '올바르지 않은 요청입니다'
    ];
}

function doAdminPost($action, $notice, $server, Session $session): array
{
    $response = ['result' => false];

    $globalAcl = $session->acl['global'] ?? [];
    $userGrade = $session->userGrade;

    if ($action == 'notice' && ($userGrade >= 5 || in_array('notice', $globalAcl))) {
        RootDB::db()->update('system', ['NOTICE' => $notice], true);
        return [
            'result' => true,
            'reason' => 'success',
        ];
    }

    return doServerModeSet($server, $action, $session);
}

$response = doAdminPost($action, $notice, $server, $session);

Json::die($response);
