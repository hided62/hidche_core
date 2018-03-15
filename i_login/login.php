<?php
require_once('_common.php');
require_once(ROOT.'/f_func/class._Session.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/tmp_kakao/conf.php');//TODO: kakao 가입 정식 적용하면 코드 변경
$SESSION = new _Session();
if($SESSION->isLoggedIn()){
    header(ROOT.'/i_entrance/Frame.php');
    die();
}
$system = getRootDB()->queryFirstRow('SELECT `REG`, `LOGIN` FROM `SYSTEM` WHERE `NO`=1');
?>

<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>로그인</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href='../f_config/config.css'>
        <link type="text/css" rel="stylesheet" href='../f_config/app.css'>

        <link type="text/css" rel="stylesheet" href='../i_popup/Style.css'>
        <link type="text/css" rel="stylesheet" href='../i_login/Style.css'>

        <!-- 액션 -->
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src='../e_lib/md5-min.js'></script>
        <script type="text/javascript" src='../f_config/config.js'></script>
        <script type="text/javascript" src='../f_config/app.js'></script>
        <script type="text/javascript" src='../f_func/func.js'></script>

        <script type="text/javascript" src='../i_popup/Action.js'></script>
        <script type="text/javascript" src='../i_login/Action.js'></script>
        <script type="text/javascript">
$(document).ready(Login);

function Login() {
    //ImportView("body", "<?=ROOT;?>"+W+I+POPUP+W+FRAME);
    //ImportView("body", FRAME);

    Popup_Import();
    Popup_Init();
    Popup_Update();

    Login_Import();
    Login_Init();
    Login_Update();
}

var rootStatus = <?=json_encode([
    'reg'=>($system['REG']=='Y'),
    'login'=>($system['LOGIN']=='Y')
], JSON_UNESCAPED_UNICODE)?>;

        </script>
    </head>

    <body style="background-image:url('<?=IMAGES.'/back7.jpg'?>');">

<?php include('findpw/Frame.php'); ?>
<?php include('join/Frame.php'); ?>

<div class="Login_TopButtons">
    <input type="button" value="게시판메인" class="Login_TopBottomButton Login_Bbs">
    <input type="button" value="삼모게시판" class="Login_TopBottomButton Login_Free">
    <input type="button" value="삼국일보"   class="Login_TopBottomButton Login_News">
    <input type="button" value="레퍼런스"   class="Login_TopBottomButton Login_Reference">
    <input type="button" value="패치게시판" class="Login_TopBottomButton Login_Patch">
    <input type="button" value="참여게시판" class="Login_TopBottomButton Login_Donation">
    <input type="button" value="튜토리얼"   class="Login_TopBottomButton Login_Tutorial">
    <input type="button" value="왕조일람"   class="Login_TopBottomButton Login_Emperior")>
    <input type="button" value="접속량정보" class="Login_TopBottomButton Login_Traffic">
</div>

    <div id='login_form' class='bg0 with_border'>
        
        <div id="login_form_title" class="bg1 with_border">삼국지 모의전투 HiD서버</div>
        <form>
            <div>
                <label for="id" style="width:30%;">ID</label>
                <input type="text" name="id">
            </div>
            <div>
                <label for="password" style="width:30%;">PW</label>
                <input type="password" name="password">
            </div>
            <div>
                <input id="login_button" type="submit" value="로그인">
            </div>
        </form>
        <div>
            <a href="https://kauth.kakao.com/oauth/authorize?client_id=<?=KakaoKey::REST_KEY?>&redirect_uri=<?=KakaoKey::REDIRECT_URI?>&response_type=code"><img src="../tmp_kakao/kakao_btn.png"></a>
        </div>
        <div>
            <a href="../i_other/help.php" target="_blank"><button id="button_tutorial">튜토리얼</button></a>
            <a href="../i_other/screenshot.php" target="_blank"><button id="button_screenshot">스크린샷</button></a>
        </div>
        
    </div>
        <div id="Login_000100" class="bg0">
            <div id="Login_00010000" class="bg1">삼모전 HiD서버</div>
            <div id="Login_00010001" class="bg1">통합관리</div>
            <div id="Login_00010002">ID&nbsp;</div>
            <input id="Login_00010003" type="text" maxlength="12">
            <div id="Login_00010004">PW&nbsp;</div>
            <input id="Login_00010005" type="password" maxlength="12">
            <input id="global_salt" type="hidden" value="<?=getGlobalSalt()?>">
            <input id="Login_00010008" type="button" value="비번&#13;찾기">
            <input id="Login_00010009" type="button" value="회원&#13;가입">
            <input id="Login_00010010" type="button" value="로그인">
            
            <div id="Login_00010012" class="bg0"><b>※체섭은 맑은고딕체를 권장합니다.</b></div>
            <input id="Login_00010013" type="button" value="튜토리얼">
            <input id="Login_00010014" type="button" value="스크린샷">
        </div>
        <div id="Login_000101" class="bg0">
<b><pre>삼국지 모의전투 유기체서버
제작자 : 유기체(jwh1807@gmail.com)</pre></b>
        </div>
    
<div class="Login_BottomButtons">
    <input type="button" value="게시판메인" class="Login_TopBottomButton Login_Bbs">
    <input type="button" value="삼모게시판" class="Login_TopBottomButton Login_Free">
    <input type="button" value="삼국일보"   class="Login_TopBottomButton Login_News">
    <input type="button" value="레퍼런스"   class="Login_TopBottomButton Login_Reference">
    <input type="button" value="패치게시판" class="Login_TopBottomButton Login_Patch">
    <input type="button" value="참여게시판" class="Login_TopBottomButton Login_Donation">
    <input type="button" value="튜토리얼"   class="Login_TopBottomButton Login_Tutorial">
    <input type="button" value="왕조일람"   class="Login_TopBottomButton Login_Emperior")>
    <input type="button" value="접속량정보" class="Login_TopBottomButton Login_Traffic">
</div>

<div id="Popup_00">
</div>
<div id="Popup_01">
    <div id="Popup_0100" class="bg1">알 림</div>
    <div id="Popup_0101" class="bg0"></div>
    <input id="Popup_0102" type="button" value="확인">
</div>

<div id="Popup_02">
    <div id="Popup_0200" class="bg1">선 택</div>
    <div id="Popup_0201" class="bg0"></div>
    <input id="Popup_0202" type="button" value="예">
    <input id="Popup_0203" type="button" value="아니요">
</div>

<div id="Popup_03">
    <div id="Popup_0300" class="bg1">처 리 중</div>
    <div id="Popup_0301" class="bg0"></div>
    <div id="Popup_0302">.</div>
    <input id="Popup_0303" type="button" value="확인">
</div>


    </body>

</html>
