<?php
require('conf.php');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>카카오 로그인하기</title>
</head>
<body>
    
    <a href="https://kauth.kakao.com/oauth/authorize?client_id=<?=KakaoKey::REST_KEY?>&redirect_uri=<?=KakaoKey::REDIRECT_URI?>&response_type=code"><img src="kakao_btn.png"></a>
</body>
</html>