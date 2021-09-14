<?php

namespace sammo;

require(__DIR__ . '/vendor/autoload.php');

if (!class_exists('\\sammo\\RootDB')) {
    header('Location:install.php');
    die();
}


$session = Session::getInstance();

use \kakao\KakaoKey as KakaoKey;

if ($session->isLoggedIn()) {
    header('Location:i_entrance/entrance.php');
    die();
}

$runningServer = null;

foreach (ServConfig::getServerList() as $setting) {
    if (!$setting->isExists()) {
        continue;
    }
    if (!$setting->isRunning()) {
        continue;
    }
    $runningServer = [
        'color' => $setting->getColor(),
        'korName' => $setting->getKorName(),
        'name' => $setting->getShortName(),
        'exists' => $setting->isExists(),
        'enable' => $setting->isRunning()
    ];
    break;
}

?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>삼국지 모의전투 HiDCHe</title>
    <script>
        var kakao_oauth_client_id = '<?= KakaoKey::REST_KEY ?>';
        var kakao_oauth_redirect_uri = '<?= KakaoKey::REDIRECT_URI ?>';
    </script>
    <?= WebUtil::printJS('d_shared/common_path.js') ?>
    <?= WebUtil::printJS('dist_js/vendors.js') ?>
    <?= WebUtil::printJS('dist_js/login.js') ?>

    <?= WebUtil::printCSS('d_shared/common.css') ?>
    <?= WebUtil::printCSS('e_lib/bootstrap.min.css') ?>
    <?= WebUtil::printCSS('css/login.css') ?>
    <meta name="description" content="실시간으로 진행되는 삼국지 웹게임(삼모전)입니다">
    <meta name="keywords" content="삼국지,삼모전,웹게임,힏체,힏체섭,히데체,히데체섭,HiDCHe,체섭">
    <meta property="og:type" content="website">
    <meta property="og:title" content="삼국지 모의전투 HiDCHe">
    <meta property="og:description" content="실시간으로 진행되는 삼국지 웹게임(삼모전)입니다">
    <meta property="og:url" content="https://sam.hided.net">

</head>

<body>
    <nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark">
        <a class="navbar-brand" href=".">삼국지 모의전투 HiDCHe</a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?= WebUtil::drawMenu(ROOT . '/d_shared/menu.json') ?>
            </ul>
        </div>
    </nav>
    <div class="container" style="margin-top:120px;">
        <h1 class="row justify-content-center">삼국지 모의전투 HiDCHe</h1>
        <div class="row justify-content-center">
            <div class="col" style="max-width:450px;">
                <div class="card" id="login_card">
                    <h3 class="card-header">
                        로그인
                    </h3>
                    <div class="card-body">

                        <form id="main_form" method="post" action="#">
                            <div class="form-group row">
                                <label for="username" class="col-5 col-md-4 col-form-label">계정명</label>
                                <div class="col-7 col-md-8">
                                    <input autocomplete="username" type="text" class="form-control" name="username" id="username" autofocus="autofocus" placeholder="계정명" />
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="password" class="col-5 col-md-4 col-form-label">비밀번호</label>
                                <div class="col-7 col-md-8">
                                    <input autocomplete="current-password" type="password" class="form-control" name="password" id="password" placeholder="비밀번호" />
                                </div>
                            </div>

                            <input type="hidden" id="global_salt" name="global_salt" value="<?= RootDB::getGlobalSalt() ?>">
                            <div class="form-group row">
                                <div class="col-5 col-md-4 " style="position:relative;"><button type="button" id="btn_kakao_login" title="카카오톡으로 가입&amp;로그인"></button></div>
                                <div class="col-7 col-md-8">
                                    <div class="btn-group btn-group-lg d-flex login_btn_group" role="group">
                                        <button type="submit" class="btn btn-primary login-button w-100">로그인</button>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">추가 기능</span></button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" id='oauth_change_pw' href="#">비밀번호 초기화</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($runningServer) : ?>
            <div class="d-flex justify-content-center" id="map-subframe-p" style='margin-top:20px;'>
                <div id="map-subframe">
                    <iframe id="running_map" src="<?= $runningServer['name'] ?>/recent_map.php"></iframe>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="bottom_box">
        <div class="container"><a href="terms.2.html">개인정보처리방침</a> &amp; <a href="terms.1.html">이용약관</a><br>© 2021 • HideD
            <br>크롬과 파이어폭스에 최적화되어있습니다.
        </div>
    </div>

    <div class="modal fade" id="modalOTP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="otp_form" method="post" action="#">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">인증 코드 필요</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            인증 코드가 필요합니다.<br><br>
                            카카오톡의 '나와의 채팅'란을 확인해 주세요.<br>
                            (별도의 알림[소리, 진동, 숫자]이 발생하지 않습니다.)
                        </div>
                        <div class="input-group mt-4" role="group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">인증 코드</span>
                            </div>
                            <input type="number" class="form-control" name='otp' id="otp_code" placeholder="인증 코드">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
                        <button type="submit" class="btn btn-primary">제출</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>