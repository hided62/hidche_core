<?php
namespace sammo;

require_once('_common.php');

$session = Session::requireLogin();
$db = RootDB::db();
$userGrade = $session->userGrade;

$session->setReadOnly();

if($userGrade < 6){
    header('Location:'.ROOT);
    die();
}
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>유저 관리</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href="../e_lib/bootstrap.min.css">
        <link type="text/css" rel="stylesheet" href='memberAdmin.css'>

        <script type="text/javascript" src='../js/common.js'></script>
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src="../e_lib/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src='../js/func.js'></script>

        <script type="text/javascript" src='memberAdmin.js'></script>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            회원 관리
        </div>
        <div class="card-body">
            가입 허용&nbsp;
            <div id="radios_allow_join" class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_join" value="1" id="allow_join_y" autocomplete="off">Y
                </label>
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_join" value="0" id="allow_join_n" autocomplete="off">N
                </label>
            </div>

            로그인 허용&nbsp;
            <div id="radios_allow_login" class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_login" value="1" id="allow_login_y" autocomplete="off">Y
                </label>
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_login" value="0" id="allow_login_n" autocomplete="off">N
                </label>
            </div>

            <div class="btn-group" role="group">
                <button onclick="changeSystem('scrub_deleted');" type="button" class="btn btn-secondary">탈퇴 계정 정리(1개월+)</button>
                <button onclick="changeSystem('scrub_icon');"type="button" class="btn btn-secondary">전콘 정리(1개월+)</button>
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
                <tr data-id="2">
                    <th scope="row">2</th>
                    <td>hide_d</td>
                    <td class="small">hided62<br>@gmail.com<br>(Kakao)</td>
                    <td>일반<p class="small blocked" style="margin:0;">18-03-25<br>15:39:00</p></td>
                    <td>Hide_D</td>
                    <td><img src="../d_pic/186200d.jpg" width="64px" height="64px"></td>
                    <td class="small">체:가나다라마바<br>퀘:가나다라마바<br>풰:가나다라마바<br>퉤:가나다라마바<br>훼:가나다라마바</td>
                    <td class="small">18-03-25<br>15:39:00</td>
                    <td class="small">18-03-25<br>15:39:00</td>
                    <td class="small">18-03-25<br>15:39:00</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="act_delete_user btn btn-danger btn-sm">강제<br>탈퇴</button>
                            <button type="button" class="act_reset_pw btn btn-info btn-sm">암호<br>변경</button>
                            <button type="button" class="act_block_user btn btn-warning btn-sm">유저<br>차단</button>
                            <button type="button" class="act_unblock_user btn btn-secondary btn-sm">차단<br>해제</button>
                            <button type="button" class="act_set_userlevel btn btn-primary btn-sm">별도<br>권한</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>