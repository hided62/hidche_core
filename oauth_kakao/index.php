<?php
namespace sammo;

use \kakao\KakaoKey as KakaoKey;

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>카카오 로그인하기</title>
    <script src="../e_lib/jquery-3.2.1.min.js"></script>
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
        url:'j_login_oauth.php',
        dataType:'json'
    }).then(function(obj){
        if(!obj.result){
            alert(obj.reason);
        }
        else{
            window.location.href = "../";
        }
    });
}

function postOAuthResult(result){
    if(result == 'join'){
        window.location.href = 'join.php';
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
    
    <a href="javascript:getOAuthToken('login');"><img src="kakao_btn.png"></a><br>
<br>
    비밀번호 찾기<br>
    <a href="javascript:getOAuthToken('change_pw', 'talk_message');"><img src="kakao_to_me.png"></a>
<input type='hidden' name="login_mode" id="login_mode">
</body>
</html>