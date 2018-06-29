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
    <?=WebUtil::printJS('d_shared/menu.js')?>
    <?=WebUtil::printJS('js/common.js')?>
    <?=WebUtil::printJS('js/login.js')?>
    <?=WebUtil::printJS('js/title.js')?>
    <?=WebUtil::printCSS('e_lib/bootstrap.min.css')?>
    <?=WebUtil::printCSS('css/login.css')?>
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
        url:'j_change_pw.php',
        dataType:'json'
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
        if(!obj.result){
            alert(obj.reason);
        }
        else{
            window.location.href = "./";
        }
    });
}

function postOAuthResult(result){
    if(result == 'join'){
        window.location.href = 'oauth_kakao/join.php';
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
    </ul>
  </div>
</nav>
    <div class="vertical-center">
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiDCHe</h1>
        <div class="row justify-content-md-center">
        <div class="col" style="max-width:450px;">
        <div class="card">
            <h3 class="card-header">
                로그인
            </h3> 
            <div class="card-body">
                
                <form id="main_form" method="post" action="#">
                    <div class="form-group row">
                        <label for="username" class="col-sm-4 col-form-label">계정명</label>
                        <div class="col-sm-8">
                            <input autocomplete="username" type="text" class="form-control"
                                name="username" id="username" autofocus="autofocus" placeholder="계정명"/>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="password" class="col-sm-4 col-form-label">비밀번호</label>
                        <div class="col-sm-8">
                            <input autocomplete="current-password" type="password" 
                                class="form-control" name="password" id="password"  placeholder="비밀번호"/>
                        </div>
                    </div>

                    <input type="hidden" id="global_salt" name="global_salt" value="<?=RootDB::getGlobalSalt()?>">
                    <div class="form-group row">
                        <div class="col-sm-4" style="position:relative;"><button type="button" onclick="getOAuthToken('login');" id="btn_kakao_login" title="카카오톡으로 가입&amp;로그인"></button></div>
                        <div class="col-sm-8">
                            <button type="submit" class="btn btn-primary btn-lg btn-block login-button">로그인</button>
                        </div>
                    </div>
                </form>
<!--
                <div class="form-group row">
                    <div class="col-sm-4"><a href="javascript:getOAuthToken('change_pw', 'talk_message');">비밀번호 찾기<img src="oauth_kakao/kakao_to_me.png"></a></div>
                    <div class="col-sm-8">
                        
                    </div>
                </div>
-->
            </div>
        </div>
        </div>
        </div>
    </div>
    </div>
    <div id="bottom_box">
    <div class="container"><a href="terms.2.html">개인정보처리방침</a> &amp; <a href="terms.1.html">이용약관</a><br>© 2018  •  HideD
    <br>크롬과 파이어폭스에 최적화되어있습니다.</div></div>
</body>
</html>
