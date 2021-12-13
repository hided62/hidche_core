<?php

namespace sammo;

require(__DIR__ . '/../vendor/autoload.php');

?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>설치</title>
    <?php /* NOTE: 설치 이전에는 ServConfig가 준비되어있지 않으므로 예외로 수동 지정 */ ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('../dist_js/gateway/vendors.js') ?>
    <?= WebUtil::printCSS('../dist_js/gateway/vendor.css') ?>
    <?= WebUtil::printJS('../dist_js/gateway/common_ts.js') ?>
    <?= WebUtil::printCSS('../dist_js/gateway/common_ts.css') ?>
    <?= WebUtil::printJS('../dist_js/gateway/install.js') ?>
    <?= WebUtil::printCSS('../dist_js/gateway/install.css') ?>

    <?= WebUtil::printCSS('../css/install.css') ?>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
</head>

<body>
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiDCHe 설치</h1>
        <div class="row justify-content-md-center">

            <div class="col col-12 col-md-10 col-lg-7">
                <div class="card" id="db_form_card" style="display:none">
                    <h3 class="card-header">
                        설치(DB 설정)
                    </h3>
                    <div class="card-body">
                        <form id="db_form" method="post" action="#">
                            <div class="form-group row">
                                <label for="db_host" class="col-sm-4 col-form-label">DB호스트</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="db_host" id="db_host" placeholder="호스트" value="localhost" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="db_port" class="col-sm-4 col-form-label">DB포트</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="db_port" id="db_port" placeholder="접속 포트" value="3306" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="db_id" class="col-sm-4 col-form-label">DB계정명</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="db_id" id="db_id" placeholder="DB계정" />
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="db_pw" class="col-sm-4 col-form-label">DB비밀번호</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" name="db_pw" id="db_pw" placeholder="DB비밀번호" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="db_name" class="col-sm-4 col-form-label">DB명</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="db_name" id="db_name" placeholder="DB명(예:sammo)" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="serv_host" class="col-sm-4 col-form-label">접속 경로</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="serv_host" id="serv_host" placeholder="접속경로(예:http://www.example.com)" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="shared_icon_path" class="col-sm-4 col-form-label">공용 아이콘 주소</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="shared_icon_path" id="shared_icon_path" placeholder="공용 아이콘 주소(웹 주소, 또는 접속 경로에 따른 상대 주소)" value="../image/icons" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="game_image_path" class="col-sm-4 col-form-label">게임 이미지 주소</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="game_image_path" id="game_image_path" placeholder="게임 이미지 주소(웹 주소, 또는 접속 경로에 따른 상대 주소)" value="../image/game" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="image_request_key" class="col-sm-4 col-form-label">이미지 갱신 키</label>
                                <div class="input-group col-sm-8">
                                    <input type="text" class="form-control" name="image_request_key" id="image_request_key" placeholder="이미지 서버의 hook/HashKey.php의 값과 동일하게" value="" />
                                    <div class="input-group-text">
                                        <button id="btn_random_generate_key" class="btn btn-secondary" type="button">랜덤 생성</button>
                                    </div>
                                </div>
                            </div>

                            <hr style="width:100%; border-top: 1px solid lightgray;">

                            <div class="form-group row">
                                <label for="kakao_rest_key" class="col-sm-4 col-form-label">카카오 API Rest Key</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="kakao_rest_key" id="kakao_rest_key" placeholder="카카오톡 API의 Rest Key" value="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="kakao_admin_key" class="col-sm-4 col-form-label">카카오 API Admin Key</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="kakao_admin_key" id="kakao_admin_key" placeholder="카카오톡 API의 Admin Key" value="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4"></div>
                                <div class="col-sm-8">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block login-button">설정 파일 생성</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--DB 폼 끝-->

                <div class="card" id="admin_form_card" style="display:none">
                    <h3 class="card-header">
                        설치(관리자 생성)
                    </h3>
                    <div class="card-body">
                        <form id="admin_form" method="post" action="#">
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
                                    <input type="text" class="form-control" name="nickname" id="nickname" placeholder="닉네임" value="운영자" />
                                </div>
                            </div>

                            <input type="hidden" id="global_salt" name="global_salt">

                            <div class="form-group row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block login-button">관리자 계정 생성</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--ADMIN 폼 끝-->

                <!-- TODO: conf_kakao.php -->

            </div>
        </div>
    </div>
</body>

</html>