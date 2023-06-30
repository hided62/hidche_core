<?php

namespace sammo;

require(__DIR__ . '/../vendor/autoload.php');


WebUtil::setHeaderNoCache();
$session = Session::getInstance()->setReadOnly();

$access_token = $session->access_token;
if (!$access_token) {
    header('Location:oauth_fail.html');
}

$canJoin = RootDB::db()->queryFirstField('SELECT REG FROM `system` WHERE `NO` = 1');
if ($canJoin != 'Y') {
    die('현재는 가입이 금지되어있습니다!');
}



//session_write_close();
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>회원가입</title>
    <?= WebUtil::printCSS('../css/login.css') ?>
    <?= WebUtil::printDist('gateway', ['join']) ?>
</head>

<body>
    <div class="container">
        <h1 class="row justify-content-lg-center">삼국지 모의전투 HiDCHe</h1>
        <div class="row justify-content-lg-center">
            <div class="col col-12 col-lg-12 col-lg-12">
                <div class="card">
                    <h3 class="card-header">
                        회원가입
                    </h3>
                    <div class="card-body">

                        <form id="main_form" method="post" action="#">



                            <div class="form-group row">
                                <label for="username" class="col-sm-3 col-form-label">계정명</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="username" id="username" placeholder="계정명" />
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="password" class="col-sm-3 col-form-label">비밀번호</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" name="password" id="password" placeholder="비밀번호" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="confirm_password" class="col-sm-3 col-form-label">비밀번호 확인</label>
                                <div class="col-sm-9">
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="비밀번호 확인" />
                                </div>

                            </div>

                            <div class="form-group row">
                                <label for="nickname" class="col-sm-3 col-form-label">닉네임</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="nickname" id="nickname" placeholder="닉네임" />
                                    <small id="nicknameHelp" class="form-text text-muted">깃수가 종료될때 공개됩니다. 장수명과는 다르게 닉네임은 계속해서 고정되니 신중하게 정해주세요.</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="username" class="col-sm-3 col-form-label">이용 약관</label>
                                <div class="col-sm-9">
                                    <div class="card">
                                        <div class="card-body terms" id="terms1">

                                        </div>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="secret_agree" name="secret_agree">
                                        <label class="custom-control-label" for="secret_agree">동의합니다.</label>
                                        <div class="invalid-feedback">
                                            동의해야만 가입하실 수 있습니다.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="username" class="col-sm-3 col-form-label">개인정보 제공<br>및<br>이용에 대한 동의</label>
                                <div class="col-sm-9">
                                    <div class="card">
                                        <div class="card-body terms" id="terms2">

                                        </div>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="secret_agree2" name="secret_agree2">
                                        <label class="custom-control-label" for="secret_agree2">동의합니다.</label>
                                        <div class="invalid-feedback">
                                            동의해야만 가입하실 수 있습니다.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="username" class="col-sm-3 col-form-label">개인정보의 제3자 수집<br>이용 제공에 대한 동의<br>(선택)</label>
                                <div class="col-sm-9">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="third_use" name="third_use">
                                        <label class="custom-control-label" for="third_use">동의합니다.</label>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" id="global_salt" name="global_salt" value="<?= RootDB::getGlobalSalt() ?>">
                            <div class="form-group row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block login-button">가입</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>