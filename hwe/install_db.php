<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin();

if($session->userGrade < 5){
    die('관리자 아님');
}
if($session->userGrade == 5){
    die('DB 리셋 권한을 가지고 있지 않습니다.');
}

?>
<!DOCTYPE html>
<html>
<head>
<title>설치</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?=WebUtil::printJS('../e_lib/jquery-3.3.1.min.js')?>
<?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
<?=WebUtil::printJS('../e_lib/jquery.validate.min.js')?>
<?=WebUtil::printJS('js/common.js')?>
<?=WebUtil::printJS('js/install_db.js')?>
<?=WebUtil::printCSS('css/normalize.css')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('css/install.css')?>
</head>
<body>
    <div class="container" style="min-width:720px;">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiDCHe 리셋</h1>
        <div class="row justify-content-md-center">

        <div class="col col-lg-8" >
        <div class="card" id="db_form_card">
            <h3 class="card-header">
                설치(DB 설정)
            </h3> 
            <div class="card-body">
                <form id="db_form" method="post" action="#">
<?php if(class_exists('\\sammo\\DB')): ?>
                    <div class="form-group row">
                        <label for="full_reset" class="col-sm-3 col-form-label">이전 DB 초기화</label>
                        <div class="col-sm-9" style="display: inline-flex;">
                            <div id="full_reset" class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="full_reset" value="1">Y
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="full_reset" value="0" checked>N
                                </label>
                                
                            </div>
                        </div>
                    </div>
<?php else: ?>
                    <input type="hidden" name="full_reset" value="0">
<?php endif; ?>
                    <div class="form-group row">
                        <label for="db_host" class="col-sm-3 col-form-label">DB호스트</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_host" id="db_host"  placeholder="호스트" value="localhost" />
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="db_port" class="col-sm-3 col-form-label">DB포트</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_port" id="db_port"  placeholder="접속 포트" value="3306" />
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="db_id" class="col-sm-3 col-form-label">DB계정</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_id" id="db_id"  placeholder="DB계정"/>
                            <small>관리 DB의 계정과는 다른 계정을 권장합니다.</small>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="db_pw" class="col-sm-3 col-form-label">DB비밀번호</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="db_pw" id="db_pw"  placeholder="DB비밀번호"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="db_name" class="col-sm-3 col-form-label">DB명</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_name" id="db_name"  placeholder="DB명(예:sammo_che)"/>
                            <small>관리 DB와는 다른 DB를 사용해야합니다.</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <button type="submit" class="btn btn-primary btn-lg btn-block login-button">설정 파일 생성</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!--DB 폼 끝-->

        </div>
        </div>
    </div>
</body>
</html>