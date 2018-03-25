<?php
namespace sammo;

include "lib.php";
include "func.php";

if(Session::getUserGrade(true) < 5){
    die('관리자 아님');
}

?>
<!DOCTYPE html>
<html>
<head>
<title>설치</title>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<script src="../e_lib/jquery-3.2.1.min.js"></script>
<script src="../e_lib/bootstrap.bundle.min.js"></script>
<script src="../e_lib/jquery.validate.min.js"></script>
<script src="js/common.js"></script>
<script src="js/install.js"></script>
<link rel="stylesheet" href="css/normalize.css" >
<link rel="stylesheet" href="../e_lib/bootstrap.min.css">
<link rel="stylesheet" href="css/install.css" >
</head>
<body>
    <div class="container">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiD 리셋</h1>
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
                        <label for="db_id" class="col-sm-3 col-form-label">DB계정명</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="db_id" id="db_id"  placeholder="DB계정"/>
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

        <div class="card" id="game_form_card">
            <h3 class="card-header">
                설치(환경 설정)
                <!--턴시간, 시간동기화, 시나리오, 상성, 확장장수, NPC유저, 이미지설정-->
            </h3> 
            <div class="card-body">
                <form id="game_form" method="post" action="#">
                    <div class="form-group row">
                        <label for="turnterm" class="col-sm-3 col-form-label">턴 시간(분)</label>
                        <div class="col-sm-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="120">120
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="turnterm" value="60" checked>60
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="30">30
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="20">20
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="10">10
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="5">5
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="2">2
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="turnterm" value="1">1
                                </label>
                            </div>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                        <label for="sync" class="col-sm-3 col-form-label">시간 동기화</label>
                        <div class="col-sm-9" style="display: inline-flex;">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="sync" value="1" checked>Y
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="sync" value="0">N
                                </label>
                                
                            </div>
                            <small class="text-muted">
                                시간 단위에 맞게 년/월이 설정됩니다. <br>예: 120분(오전1시=1월), 60분(오전/오후 1시=1월)
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="confirm_password" class="col-sm-3 col-form-label">시나리오 선택</label>
                        <div class="col-sm-9">
                        <select class="form-control" size="1">
                            <optgroup label="공백지">
                                <option value="0">공백지</option>
                            </optgroup>
                            <optgroup label="역사모드">
                                <option value="1">황건적의 난</option>
                            </optgroup>
                        </select>
                        <table class="table table-sm table-striped">
                            <colgroup><col style="width:100px;"><col></colgroup>
                            <thead><tr><th>설정</th><th>값</th></thead>
                            <tbody>
                                <tr><th>일자</th><td ><span id="scenario_begin">180년 1월</span><small id="scenario_begin_with_sync"></small></td></tr>
                                <tr><th>NPC 수</th><td><span id="scenario_npc"></span><span id="scenario_npc_extend"></span></td></tr>
                                <tr><th>국가 수</th><td><span id="scenario_nation"></span></td></tr>
                            </tbody>
                            <tfoot>
                                <tr><td colspan="2">
                                    
                                </td></tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="fiction" class="col-sm-3 col-form-label">NPC 상성</label>
                        <div class="col-sm-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="fiction" value="0" checked>연의
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="fiction" value="1">가상
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="extend" class="col-sm-3 col-form-label">확장 NPC</label>
                        <div class="col-sm-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="extend" value="1" checked>포함
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="extend" value="0">미포함
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="npcmode" class="col-sm-3 col-form-label">NPC 빙의</label>
                        <div class="col-sm-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="npcmode" value="1">가능
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="npcmode" value="0" checked>불가
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="img" class="col-sm-3 col-form-label">이미지 표기</label>
                        <div class="col-sm-9">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="img" value="0">안함
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="img" value="1">전콘
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="img" value="2" checked>전콘, 병종
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="img" value="3">전콘, 병종, NPC
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <button type="submit" class="btn btn-primary btn-lg btn-block login-button">리셋 개시</button>
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