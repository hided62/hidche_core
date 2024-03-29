<?php

namespace sammo;

require(__DIR__ . '/../vendor/autoload.php');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title>유저 관리</title>

    <!-- 스타일 -->
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('../css/admin_member.css') ?>

    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printDist('gateway', 'admin_member', true) ?>

</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <a href="entrance.php"><button type="button" class="btn btn-primary" style="float:right;">돌아가기</button></a>
                <h3>회원 관리</h3>

            </div>
            <div class="card-body">
                가입 허용&nbsp;
                <div id="radios_allow_join" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                    <label class="btn btn-secondary">
                        <input type="radio" name="allow_join" value="1" id="allow_join_y" autocomplete="off">Y
                    </label>
                    <label class="btn btn-secondary">
                        <input type="radio" name="allow_join" value="0" id="allow_join_n" autocomplete="off">N
                    </label>
                </div>

                로그인 허용&nbsp;
                <div id="radios_allow_login" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                    <label class="btn btn-secondary">
                        <input type="radio" name="allow_login" value="1" id="allow_login_y" autocomplete="off">Y
                    </label>
                    <label class="btn btn-secondary">
                        <input type="radio" name="allow_login" value="0" id="allow_login_n" autocomplete="off">N
                    </label>
                </div>

                <div class="btn-group" role="group">
                    <button onclick="changeSystem('scrub_deleted');" type="button" class="btn btn-secondary">탈퇴 계정 정리(1개월+)</button>
                    <button onclick="changeSystem('scrub_icon');" type="button" class="btn btn-secondary">전콘 정리(1개월+)</button>
                </div>

                <button onclick="changeSystem('scrub_old_user');" type="button" class="btn btn-secondary">오래된 계정 정리(6개월+)</button>

            </div>
            <h6 class="card-header">회원 목록</h6>
            <table id="user_list_frame" class="table table-hover table-sm table-striped">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col style="width:200px;">
                </colgroup>
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">코드</th>
                        <th scope="col">유저명</th>
                        <th scope="col">EMAIL</th>
                        <th scope="col">등급</th>
                        <th scope="col">닉네임</th>
                        <th scope="col">전콘</th>
                        <th scope="col">장수명</th>
                        <th scope="col">가입<br>일자</th>
                        <th scope="col">최근<br>로그인</th>
                        <th scope="col">탈퇴<br>신청</th>
                        <th scope="col">명령</th>
                    </tr>
                </thead>
                <tbody id="user_list">
                </tbody>
            </table>
        </div>
    </div>
    </div>
</body>

</html>