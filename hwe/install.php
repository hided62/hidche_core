<?php
namespace sammo;

include "lib.php";
include "func.php";

if(!class_exists('\sammo\DB')){
    header('Location:install_db.php');
}

$session = Session::requireLogin();

$serverName = DB::prefix();
$serverAcl = $session->acl[$serverName]??[];
$allowReset = in_array('reset', $serverAcl);
$allowFullReset = in_array('fullReset',$serverAcl);
$allowReset |= $allowFullReset;

if($session->userGrade < 5 && !$allowReset){
    die('관리자 아님');
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
<?=WebUtil::printJS('js/install.js')?>
<?=WebUtil::printCSS('css/normalize.css')?>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('css/install.css')?>
</head>
<body>
    <div class="container" style="min-width:720px;">
        <h1 class="row justify-content-md-center">삼국지 모의전투 HiDCHe 리셋</h1>
        <div class="row justify-content-md-center">

        <div class="col col-lg-8" >
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
                            <div id="turnterm" class="btn-group btn-group-toggle" data-toggle="buttons">
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
                            <div id="sync" class="btn-group btn-group-toggle" data-toggle="buttons">
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
                        <select class="form-control" name="scenario" size="1" id="scenario_sel">
                        </select>
                        
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="fiction" class="col-sm-3 col-form-label">NPC 상성</label>
                        <div class="col-sm-9">
                            <div id="fiction" class="btn-group btn-group-toggle" data-toggle="buttons">
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
                            <div id="extend" class="btn-group btn-group-toggle" data-toggle="buttons">
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
                            <div id="npcmode" class="btn-group btn-group-toggle" data-toggle="buttons">
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
                        <label for="show_img_level" class="col-sm-3 col-form-label">이미지 표기</label>
                        <div class="col-sm-9">
                            <div id="show_img_level" class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary">
                                    <input type="radio" name="show_img_level" value="0">안함
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="show_img_level" value="1">전콘
                                </label>
                                <label class="btn btn-secondary">
                                    <input type="radio" name="show_img_level" value="2">전콘, 병종
                                </label>
                                <label class="btn btn-secondary active">
                                    <input type="radio" name="show_img_level" value="3" checked>전콘, 병종, NPC
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="tournament_trig" class="col-sm-3 col-form-label">토너먼트 자동 시작<br>경기 단위</label>
                        <div class="col-sm-9" id="tournament_trig">
                            <div id="tournament_trig" class="btn-group-toggle btn-group flex-wrap" data-toggle="buttons">
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="0">안함
                                </label>
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="1">12분(총 5일)
                                </label>
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="2">7분(총 10시간)
                                </label>
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="3">3분(총 4시간)
                                </label>
                                <label class="btn btn-secondary w-25 active">
                                    <input type="radio" name="tournament_trig" value="4" checked>1분(총 82분)
                                </label>
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="5">30초(총 41분)
                                </label>
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="6">15초(총 21분)
                                </label>
                                <label class="btn btn-secondary w-25">
                                    <input type="radio" name="tournament_trig" value="7">5초(총 7분)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="reserve_open" class="col-sm-3 col-form-label">오픈 예약</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="reserve_open" id="reserve_open"  placeholder="YYYY-MM-DD hh:mm" value="" />
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <input type="submit" class="btn btn-primary btn-lg btn-block login-button" value="리셋 개시">
                        </div>
                    </div>
                </form>
            </div>
            <h4 class="card-header">시나리오 정보</h4>
            <table class="table table-striped">
                <colgroup><col style="width:100px;"><col></colgroup>
                <thead>
                    <tr><th>설정</th><th>값</th>
                </thead>
                <tbody>
                    <tr><th>시작 연도</th><td ><span id="scenario_begin">180년</span><small id="scenario_begin_with_sync"></small></td></tr>
                    <tr><th>NPC 수</th><td><span id="scenario_npc"></span><span id="scenario_npc_extend"></span></td></tr>
                    <tr><th>국가</th><td><span id="scenario_nation"></span></td></tr>
                </tbody>
                <tfoot>
                    <tr><td colspan="2">
                        
                    </td></tr>
                </tfoot>
            </table>
        </div><!--GAME 폼 끝-->

        </div>
        </div>
    </div>
</body>
</html>