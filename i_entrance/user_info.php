<?php
namespace sammo;
require(__dir__.'/../vendor/autoload.php');
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=1024" />
        <title>계정 관리</title>

        <!-- 스타일 -->
        <?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
        <?=WebUtil::printCSS('../d_shared/common.css')?>
        <?=WebUtil::printCSS('../css/config.css')?>
        <?=WebUtil::printCSS('../css/user_info.css')?>

        <!-- 액션 -->
        <?=WebUtil::printJS('../d_shared/common_path.js')?>
        <?=WebUtil::printJS('../js/common.js')?>
        <?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
        <?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
        <?=WebUtil::printJS('../e_lib/sha512.min.js')?>
        <?=WebUtil::printJS('../e_lib/moment.min.js')?>
        <?=WebUtil::printJS('../js/func.js')?>
        <?=WebUtil::printJS('../js/user_info.js')?>
        <?=WebUtil::printJS('../d_shared/menu.js')?>
        <?=WebUtil::printJS('../js/title.js')?>
    </head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="../">삼국지 모의전투 HiDCHe</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
        </ul>
    </div>
</nav>
<div id="user_info_container">
<table class="bg0" id="user_info_table">
    <caption class="bg2 section_title with_border">
        계 정 관 리
        <a href="entrance.php"><button id="btn_back1" class="with_skin">돌아가기</button></a>
    </caption>
    <colgroup>
        <col style="width:90px;min-width:90px;" />
        <col style="width:90px;min-width:90px;"/>
        <col style="width:90px;min-width:90px;"/>
        <col style="width:90px;min-width:90px;"/>
        <col style="width:90px;min-width:90px;"/>
        <col style="min-width:90px;"/>
    </colgroup>
    <thead>
        <tr>
            <th colspan="6" class="bg1">회 원 정 보</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="bg1">ID</th>
            <td colspan="5">
                <span id="slot_id"></span>
            </td>
        </tr>
        <tr>
            <th class="bg1">닉네임</th>
            <td colspan="5" style="height:36px;">
                <span id="slot_nickname"></span>
            </td>
        </tr>
        <tr>
            <th class="bg1">등급</th>
            <td colspan="2">
                <span id="slot_grade"></span>
            </td>
            <td colspan="3">
                <span id="slot_acl"></span>
            </td>
        </tr>
        <tr>
            <th class="bg1">가입일시</th>
            <td colspan="2">
                <span id="slot_join_date"></span>
            </td>
            <td colspan="3">개인정보 3자 제공 동의 : <span id="slot_third_use"></span><button type="button" id="third_use_disallow">철회</button></td>
        </tr>
        <tr>
            <th class="bg1">인증 방식</th>
            <td colspan="2">
                <span id="slot_oauth_type"></span>
            </td>
            <td colspan="3">
                <span id="slot_token_valid_until"></span>까지 유효<button type="button" id="expand_login_token">초기화</button></td>
        </tr>
        <tr>
            <th class="bg1"></th>
            <th class="bg1" colspan="2">회원 탈퇴</th>
            <th class="bg1" colspan="3">비밀번호 변경</th>
        </tr>
        <tr>
            <th class="bg1">정보<br>수정</th>
            <td colspan="2" style="position:relative;">
                <form name="delete_me_form" id="delete_me_form" method="post">
                        <label for="delete_pw">현재 비밀번호</label>
                        <input class="with_skin" type="password" autocomplete="current-password" name="delete_pw" id="delete_pw" style="width:120px;"><br>
                        <br>
                        <input class="with_skin" type="submit" id="btn_delete_me" value="탈퇴신청">
                </form>
            </td>
            <td colspan="3" style="text-align:right;">
                <form name="change_pw_form" id="change_pw_form" method="post">
                    <input type="hidden" id="global_salt" name="global_salt">
                    <input type="text" autocomplete="username" style="display:none;"><!--자동 완성툴을 위해-->
                    <label for="current_pw">현재 비밀번호</label>
                    <input class="with_skin" type="password" autocomplete="current-password" name="current_pw" id="current_pw" style="width:120px;"><br>
                    <label for="new_pw">새 비밀번호</label>
                    <input class="with_skin" type="password" autocomplete="new-password" name="new_pw" id="new_pw" style="width:120px;"><br>
                    <label for="new_pw_confirm">비밀번호 확인</label>
                    <input class="with_skin" type="password" autocomplete="new-password" name="new_pw_confirm" id="new_pw_confirm" style="width:120px;"><br>
                    <input class="with_skin" type="submit" id="change_pw" value="비밀번호 변경">
                </form>
            </td>
        </tr>
        <tr>
            <th class="bg1"></th>
            <th colspan="2" class="bg1">
                현재 / 신규
            </th>
            <th colspan="3" class="bg1">
                전용 아이콘 변경
            </th>
        </tr>
        <tr>
            <th class="bg1">전용<br>아이콘</th>
            <td colspan="2" style="height:64px;">
                <img width="64" height="64" id="slot_icon">
                <img width="64" height="64" id="slot_new_icon">
            </td>
            <td colspan="3" style="position:relative;" >
                <form name="change_icon_form" id="change_icon_form" method="post" enctype="multipart/form-data">
                    <input class="with_skin" type="text" readonly="readonly" id="image_upload_filename">
                    <button id="image_upload_fake_btn" class="with_skin">찾아보기</button>
                    <input type="file" id="image_upload" name="image_upload" accept=".jpg,.jpeg,.png,.gif"><br>
                    <input class="with_skin" id="btn_image_submit" type="submit" value="아이콘 변경"> <button id="btn_remove_icon" class="with_skin">아이콘 제거</button>
                </form>
                    
                    
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th class="bg1">도움말</th>
            <td colspan="5" style="text-align:left;padding:8px;">
                <p style="line-height:1.2em;">
                아이콘은 64 x 64픽셀 ~ 128 x 128픽셀 사이, 30KB 이하의 jpg, gif, png 파일만 가능합니다.</p>
                <p style="margin-top:1em;color:magenta;line-height:1.2em;">탈퇴시 1개월간 정보가 보존되며, 1개월간 재가입이 불가능합니다.</span>
                </p>
            </td>
        </tr>
    </tfoot>
</table>

</div>
</body>
</html>