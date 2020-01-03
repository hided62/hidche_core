<?php
namespace sammo;

require(__dir__.'/vendor/autoload.php');

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

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>삼국지 모의전투 HiDCHe</title>
    <?=WebUtil::printJS('e_lib/jquery-3.3.1.min.js')?>
    <?=WebUtil::printJS('e_lib/bootstrap.bundle.min.js')?>
    <?=WebUtil::printJS('e_lib/jquery.validate.min.js')?>
    <?=WebUtil::printJS('e_lib/sha512.min.js')?>
    <?=WebUtil::printJS('js/common.js')?>
    <?=WebUtil::printJS('js/login.js')?>
    <?=WebUtil::printCSS('e_lib/bootstrap.min.css')?>
    <?=WebUtil::printCSS('css/login.css')?>
    <meta name="description" content="실시간으로 진행되는 삼국지 웹게임(삼모전)입니다">
    <meta name="keywords" content="삼국지,삼모전,웹게임,힏체,힏체섭,히데체,히데체섭,HiDCHe,체섭">
    <meta property="og:type" content="website">
    <meta property="og:title" content="삼국지 모의전투 HiDCHe">
    <meta property="og:description" content="실시간으로 진행되는 삼국지 웹게임(삼모전)입니다">
    <meta property="og:url" content="https://sam.hided.net">
    <script>

var oauthMode = null;

function getOAuthToken(mode, scope_list){
    if(mode === undefined){
        mode = 'login';
    }
    if(scope_list === undefined){
        scope_list = null;
    }
    oauthMode = mode;
    var url = 'https://kauth.kakao.com/oauth/authorize?'+
        'client_id=<?=KakaoKey::REST_KEY?>&'+
        'redirect_uri=<?=KakaoKey::REDIRECT_URI?>&'+
        'response_type=code';
    if(Array.isArray(scope_list)){
        url += '&scope='+scope_list.join(',');
    }
    else if(typeof scope_list === 'string' || scope_list instanceof String){
        url += '&scope='+scope_list;
    }

    window.open(url,"KakaoAccountLogin","width=600,height=450,resizable=yes,scrollbars=yes");
}

function sendTempPasswordToKakaoTalk(){
    $.post({
        url:'oauth_kakao/j_login_oauth.php',
        dataType:'json'
    }).then(function(obj){
        var t = $.Deferred();
        if(!obj.result){
            t.reject();
        }
        return $.post({
            url:'oauth_kakao/j_change_pw.php',
            dataType:'json'
        });
    }).then(function(obj){
        if(!obj.result){
            alert(obj.reason);
        }
        else{
            alert('임시 비밀번호가 카카오톡으로 전송되었습니다.');
        }
    });
}

function doLoginUsingOAuth(){
    $.post({
        url:'oauth_kakao/j_login_oauth.php',
        dataType:'json'
    }).then(function(obj){
        if(obj.result){
            window.location.href = "./";
            return;
        }
        if(!obj.reqOTP){
            alert(obj.reason);
            return;
        }
        $('#modalOTP').modal().on('shown.bs.modal', function(){
            $('#otp_code').focus();
        });

    });
}

function postOAuthResult(result){
    if(result == 'join'){
        window.location.href = 'oauth_kakao/join.php';
    }
    else if(result == 'req_email'){
        alert('이메일 정보 공유를 허가해 주셔야 합니다.');
    }
    else if(result == 'login'){
        console.log('로그인모드');
        if(oauthMode=='change_pw'){
            sendTempPasswordToKakaoTalk();
        }
        else{
            doLoginUsingOAuth();
        }
    }
    else{
        alert('예외 발생!');
    }
}
</script>
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top navbar-light bg-light">
  <a class="navbar-brand" href=".">삼국지 모의전투 HiDCHe</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
        <?=WebUtil::drawMenu(ROOT.'/d_shared/menu.json')?>
    </ul>
  </div>
</nav>
<div class="full-content">
    <div class="vertical-center">
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiDCHe</h1>
        <div class="row justify-content-md-center">
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
                            <input autocomplete="username" type="text" class="form-control"
                                name="username" id="username" autofocus="autofocus" placeholder="계정명"/>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="password" class="col-5 col-md-4 col-form-label">비밀번호</label>
                        <div class="col-7 col-md-8">
                            <input autocomplete="current-password" type="password" 
                                class="form-control" name="password" id="password"  placeholder="비밀번호"/>
                        </div>
                    </div>

                    <input type="hidden" id="global_salt" name="global_salt" value="<?=RootDB::getGlobalSalt()?>">
                    <div class="form-group row">
                        <div class="col-5 col-md-4 " style="position:relative;"><button type="button" onclick="getOAuthToken('login', ['account_email','talk_message']);" id="btn_kakao_login" title="카카오톡으로 가입&amp;로그인"></button></div>
                        <div class="col-7 col-md-8">
                            <div class="btn-group btn-group-lg d-flex login_btn_group" role="group">
                                <button type="submit" class="btn btn-primary login-button w-100">로그인</button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
                                        <a class="dropdown-item" href="javascript:getOAuthToken('change_pw', 'talk_message');">비밀번호 초기화</a>
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
    </div>
    </div>
    <div id="bottom_box">
    <div class="container"><a href="terms.2.html">개인정보처리방침</a> &amp; <a href="terms.1.html">이용약관</a><br>© 2020  •  HideD
    <br>크롬과 파이어폭스에 최적화되어있습니다.</div></div>
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
