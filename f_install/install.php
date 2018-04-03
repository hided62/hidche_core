<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>설치</title>
    <script src="../e_lib/jquery-3.2.1.min.js"></script>
    <script src="../e_lib/bootstrap.bundle.min.js"></script>
    <script src="../e_lib/jquery.validate.min.js"></script>
    <script src="../e_lib/sha512.min.js"></script>
    <script src="../js/install.js"></script>
    <link type="text/css" rel="stylesheet" href="../e_lib/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="../css/install.css">
</head>
<body>
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiD 설치</h1>
        <div class="row justify-content-md-center">

        <div class="col col-12 col-md-10 col-lg-7">
        <div class="card" id="db_form_card" style="display:none">
            <h3 class="card-header">
                설치(DB 설정)
            </h3> 
            <div class="card-body">
                <form id="db_form" method="post" action="#">
                    <div class="form-group row">
                        <label for="db_host" class="col-sm-3 col-form-label">DB호스트</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_host" id="db_host" 
                             placeholder="호스트" value="localhost" />
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="db_port" class="col-sm-3 col-form-label">DB포트</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_port" id="db_port" 
                             placeholder="접속 포트" value="3306" />
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="db_id" class="col-sm-3 col-form-label">DB계정명</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_id" id="db_id"  placeholder="DB계정"/>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="db_pw" class="col-sm-3 col-form-label">DB비밀번호</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="db_pw" id="db_pw" 
                             placeholder="DB비밀번호"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="db_name" class="col-sm-3 col-form-label">DB명</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_name" id="db_name" 
                             placeholder="DB명(예:sammo)"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="serv_host" class="col-sm-3 col-form-label">접속 경로</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="serv_host" id="serv_host" 
                            placeholder="접속경로(예:http://www.example.com)"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <button type="submit" 
                            class="btn btn-primary btn-lg btn-block login-button">설정 파일 생성</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!--DB 폼 끝-->

        <div class="card" id="admin_form_card" style="display:none">
            <h3 class="card-header">
                설치(관리자 생성)
            </h3> 
            <div class="card-body">
                <form id="admin_form" method="post" action="#">
                    <div class="form-group row">
                        <label for="username" class="col-sm-3 col-form-label">계정명</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="username" id="username"
                              placeholder="계정명"/>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="password" class="col-sm-3 col-form-label">비밀번호</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="password" id="password" 
                             placeholder="비밀번호"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="confirm_password" class="col-sm-3 col-form-label">비밀번호 확인</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" 
                             placeholder="비밀번호 확인"/>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="nickname" class="col-sm-3 col-form-label">닉네임</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="nickname" id="nickname" 
                             placeholder="닉네임" value="운영자"/>
                        </div>
                    </div>

                    <input type="hidden" id="global_salt" name="global_salt">

                    <div class="form-group row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <button type="submit"
                             class="btn btn-primary btn-lg btn-block login-button">관리자 계정 생성</button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!--ADMIN 폼 끝-->

        <!-- TODO: conf_kakao.php -->

        </div>
        </div>
    </div>
</body>
</html>
