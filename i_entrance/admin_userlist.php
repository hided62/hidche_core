<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

$db = getRootDB();
$userGrade = $SESSION->getGrade();

session_write_close();

if($userGrade < 6){
    header('Location:../');
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
        <link type="text/css" rel="stylesheet" href='member/Style.css'>

        <script type="text/javascript" src='../js/common.js'></script>
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src="../e_lib/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src='../f_func/func.js'></script>

        <script type="text/javascript" src='../i_popup/Action.js'></script>
        <script type="text/javascript" src='member/Action.js'></script>
<script type="text/javascript">
$(function(){
    EntranceMember_Import();
    EntranceMember_Init();
})
</script>
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
                    <input type="radio" name="allow_join" value="Y" id="allow_join_y" autocomplete="off">Y
                </label>
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_join" value="N" id="allow_join_n" autocomplete="off">N
                </label>
            </div>

            로그인 허용&nbsp;
            <div id="radios_allow_login" class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_login" value="Y" id="allow_login_y" autocomplete="off">Y
                </label>
                <label class="btn btn-secondary">
                    <input type="radio" name="allow_login" value="N" id="allow_login_n" autocomplete="off">N
                </label>
            </div>

            <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-secondary">탈퇴 계정 정리(1개월+)</button>
                <button type="button" class="btn btn-secondary">오래된 전콘 정리(1개월+)</button>
                
            </div>

            <button type="button" class="btn btn-secondary">오래된 계정 정리(6개월+)</button>

        </div>
        <h6 class="card-header">회원 목록</h6>
        <table class="table table-hover table-sm table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">코드</th>
                    <th scope="col">유저명</th>
                    <th scope="col">가입 방식</th>
                    <th scope="col">EMAIL</th>
                    <th scope="col">등급</th>
                    <th scope="col">닉네임</th>
                    <th scope="col">전콘</th>
                    <th scope="col">가입일자</th>
                    <th scope="col">최근 로그인</th>
                    <th scope="col">탈퇴 신청</th>
                    <th scope="col">명령</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">ㅁ</th>
                    <td>ㅁ</td>
                </tr>
                <tr>
                    <th scope="row">ㅁ</th>
                    <td>ㅁ</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div id="EntranceMember_00">
    <div id="EntranceMember_0000" class="bg2 font4">
        회 원 정 보
        <font id="EntranceMember_000000">(0/0)</font>
    </div>
    <input id="EntranceMember_0001" type="button" value="돌아가기">
    <div id="EntranceMember_0006" class="bg2 font2">
        <div id="EntranceMember_000600"></div>
        <input id="btn_allow_join" type="button" value="가입허용">
        <input id="btn_deny_join" type="button" value="가입금지">
        <input id="btn_allow_login" type="button" value="로그인허용">
        <input id="btn_deny_login" type="button" value="로그인금지">
        <input id="btn_process_withdraw" type="button" value="탈퇴처리(1개월)">
        <input id="btn_process_scrub_olduser" type="button" value="오래된계정(6개월)">
    </div>
    <div id="EntranceMember_0002" class="bg2 font2">
        선택:
        <select id="EntranceMember_000200" size="1">
            <option value="0">순서</option>
            <option value="1">ID</option>
            <option value="2">장수</option>
            <option value="3">민번</option>
            <option value="4">IP</option>
            <option value="5">등급</option>
            <option value="6">등록</option>
            <option value="7">최근</option>
        </select>
        <input id="EntranceMember_000201" type="button" value="정렬">
    </div>
    <div id="EntranceMember_0003" class="bg2 font2">
        선택:
        <select id="EntranceMember_000300" size="1">
        </select>
        <input id="EntranceMember_000301" type="button" value="블럭회원">
        <input id="EntranceMember_000302" type="button" value="일반회원">
        <input id="EntranceMember_000303" type="button" value="참여회원">
        <input id="EntranceMember_000304" type="button" value="유효회원">
        <input id="EntranceMember_000305" type="button" value="특별회원">
        <input id="EntranceMember_000306" type="button" value="전콘제거">
        <input id="EntranceMember_000307" type="button" value="비번초기화">
        <input id="EntranceMember_000308" type="button" value="회원삭제">
    </div>
    <div id="EntranceMember_0004" class="bg1">
        <div id="EntranceMember_000400">순번</div>
        <div id="EntranceMember_000401">ID</div>
        <div id="EntranceMember_000402">민번</div>
        <div id="EntranceMember_000403">닉네임</div>
        <div id="EntranceMember_000404">IP</div>
        <div id="EntranceMember_000405">블럭</div>
        <div id="EntranceMember_000406">최근블럭일</div>
        <div id="EntranceMember_000407">등록</div>
        <div id="EntranceMember_000408">최근등록일</div>
        <div id="EntranceMember_000409">등급</div>
        <div id="EntranceMember_000412">사진</div>
        <div id="EntranceMember_000413">SVR</div>
        <div id="EntranceMember_000414">탈퇴</div>
    </div>
    <div id="EntranceMember_0005">
    </div>
</div>
</body>
</html>