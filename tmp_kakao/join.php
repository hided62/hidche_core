<?php
require('_common.php');
require(ROOT.'/f_func/class._Time.php');
require_once(__dir__.'/../d_setting/conf.php');

use utilphp\util as util;

session_start();

$access_token = util::array_get($_SESSION['access_token']);
if(!$access_token){
    header('Location:oauth_fail.html');
}

$canJoin = getRootDB()->queryFirstField('SELECT REG FROM `SYSTEM` WHERE `NO` = 1');
if($canJoin != 'Y'){
    die('현재는 가입이 금지되어있습니다!');
}



//session_write_close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>회원가입</title>
    <script src="../e_lib/jquery-3.2.1.min.js"></script>
    <script src="../e_lib/bootstrap.bundle.min.js"></script>
    <script src="../e_lib/jquery.validate.min.js"></script>
    <script src="../e_lib/sha512.min.js"></script>
    <script src="join.js"></script>
    <link type="text/css" rel="stylesheet" href="../e_lib/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="join.css">
</head>
<body>
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiD</h1>
        <div class="row justify-content-md-center">
        <div class="col col-12 col-md-10 col-lg-7">
        <div class="card">
            <h3 class="card-header">
                회원가입
            </h3> 
            <div class="card-body">
                
                <form id="main_form" method="post" action="#">
                        
                    

                    <div class="form-group row">
                        <label for="username" class="col-sm-3 col-form-label">계정명</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="username" id="username"  placeholder="계정명"/>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="password" class="col-sm-3 col-form-label">비밀번호</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="password" id="password"  placeholder="비밀번호"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="confirm_password" class="col-sm-3 col-form-label">비밀번호 확인</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password"  placeholder="비밀번호 확인"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="nickname" class="col-sm-3 col-form-label">닉네임</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="nickname" id="nickname"  placeholder="닉네임"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="username" class="col-sm-3 col-form-label">약관</label>
                        <div class="col-sm-9">
                            <div class="card">
                                <div class="card-body terms" id="terms">
                                    
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="secret_agree" name="secret_agree">
                                <label class="custom-control-label" for="secret_agree">동의합니다.</label>
                                <div class="invalid-feedback">
                                    동의해야만 가입하실 수 있습니다.
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="input_hash_salt1" name="global_salt" value="<?=getGlobalSalt()?>">
                    <div class="form-group row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <button type="submit" class="btn btn-primary btn-lg btn-block login-button">가입</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>
        </div>
    </div>
    <?=_Time::DatetimeNow()?>
    <?=util::array_get($_SESSION['access_token'])?><br>
<?=util::array_get($_SESSION['expires'])?><br>
<?=util::array_get($_SESSION['refresh_token'])?><br>
<?=util::array_get($_SESSION['refresh_token_expires'])?><br>
    <?=$_SESSION['tmpx']?>
</body>
</html>
