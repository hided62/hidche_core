<?php
require('_common.php');
require(ROOT.'/f_config/SETTING.php');

if(!$SETTING->isExists()){
    header ('Location:install.php');
    die();
}

require(ROOT.'/f_config/DB.php');
require(ROOT.'/f_func/class._Session.php');
require(ROOT.'/d_setting/conf_kakao.php');

$SESSION = new _SESSION();

use utilphp\util as util;

if($SESSION->isLoggedIn()){
    header ('Location:i_entrance/entrance.php');
    die();
}

$access_token = $SESSION->get('access_token');


?>


<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>삼국지 모의전투 HiD 서버</title>
    <script src="e_lib/jquery-3.2.1.min.js"></script>
    <script src="e_lib/bootstrap.bundle.min.js"></script>
    <script src="e_lib/jquery.validate.min.js"></script>
    <script src="e_lib/sha512.min.js"></script>
    <script src="js/login.js"></script>
    <link type="text/css" rel="stylesheet" href="e_lib/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="css/login.css">
    <script>

var oauthMode = null;

function getOAuthToken(mode='login', scope_list = null){
    oauthMode = mode;
    var url = 'https://kauth.kakao.com/oauth/authorize?client_id=<?=KakaoKey::REST_KEY?>&redirect_uri=<?=KakaoKey::REDIRECT_URI?>&response_type=code';
    if(Array.isArray(scope_list)){
        url += '&scope='+scope_list.join(',');
    }
    else if(typeof scope_list === 'string' || scope_list instanceof String){
        url += '&scope='+scope_list;
    }

    window.open(url,"KakaoAccountLogin","width=600,height=450");

}

function sendTempPasswordToKakaoTalk(retry=false){
    $.post({
        url:'j_change_pw.php',
        dataType:'json'
    }).then(function(obj){
        if (!obj.result && retry) {
            getOAuthToken('change_pw', 'talk_message');
        }
        else if(!obj.result){
            alert(obj.reason);
        }
        else{
            alert('임시 비밀번호가 카카오톡으로 전송되었습니다.');
        }
    });
}

function doLoginUsingOAuth(retry=false){
    $.post({
        url:'oauth_kakao/j_login_oauth.php',
        dataType:'json'
    }).then(function(obj){
        if(!obj.result){
            if(obj.noRetry || !retry){
                alert(obj.reason);
            }
            else{
                getOAuthToken('login');
            }
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
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiD</h1>
        <div class="row justify-content-md-center">
        <div class="col col-12 col-md-10 col-lg-7">
        <div class="card">
            <h3 class="card-header">
                로그인
            </h3> 
            <div class="card-body">
                
                <form id="main_form" method="post" action="#">
                        
                    

                    <div class="form-group row">
                        <label for="username" class="col-sm-3 col-form-label">계정명</label>
                        <div class="col-sm-9">
                            <input autocomplete="username" type="text" class="form-control" name="username" id="username"  placeholder="계정명"/>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="password" class="col-sm-3 col-form-label">비밀번호</label>
                        <div class="col-sm-9">
                            <input autocomplete="current-password" type="password" class="form-control" name="password" id="password"  placeholder="비밀번호"/>
                        </div>
                    </div>

                    <input type="hidden" id="global_salt" name="global_salt" value="<?=getGlobalSalt()?>">
                    <div class="form-group row">
                        <div class="col-sm-3" style="position:relative;"><a href="javascript:doLoginUsingOAuth(true);"><img style="height:46px;margin-top:6px;" src="oauth_kakao/kakao_btn.png"></a></div>
                        <div class="col-sm-9">
                            <button type="submit" class="btn btn-primary btn-lg btn-block login-button">로그인</button>
                        </div>
                    </div>
                </form>

                <div class="form-group row">
                    <div class="col-sm-3"><!--<a href="javascript:sendTempPasswordToKakaoTalk(true);">비밀번호 찾기<img src="oauth_kakao/kakao_to_me.png"></a>--></div>
                    <div class="col-sm-9">
                        
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>
    </div>
</body>
</html>
