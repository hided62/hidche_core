<?php

namespace sammo;

include "lib.php";
include "func.php";

if (!class_exists('\sammo\DB')) {
    header('Location:install_db.php');
}

$session = Session::requireLogin();

$serverName = DB::prefix();
$serverAcl = $session->acl[$serverName] ?? [];
$allowReset = in_array('reset', $serverAcl);
$allowFullReset = in_array('fullReset', $serverAcl);
$allowReset = $allowReset || $allowFullReset;

if ($session->userGrade < 5 && !$allowReset) {
    die('관리자 아님');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>설치</title>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('css/install.css') ?>
    <?= WebUtil::printDist('vue', [], true) ?>
    <?= WebUtil::printDist('ts', ['common', 'install']) ?>
</head>

<body>
    <div class="container">
        <h1 class="row justify-content-lg-center">삼국지 모의전투 HiDCHe 리셋</h1>
        <div class="row justify-content-lg-center">

            <div class="col col-lg-8">
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
                                    <div id="turnterm" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_120" value="120"><label for="turnterm_120" class="btn btn-secondary">120</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_60" value="60" checked><label for="turnterm_60" class="btn btn-secondary">60</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_30" value="30"><label for="turnterm_30" class="btn btn-secondary">30</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_20" value="20"><label for="turnterm_20" class="btn btn-secondary">20</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_10" value="10"><label for="turnterm_10" class="btn btn-secondary">10</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_5" value="5"><label for="turnterm_5" class="btn btn-secondary">5</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_2" value="2"><label for="turnterm_2" class="btn btn-secondary">2</label>
                                        <input type="radio" class="btn-check" name="turnterm" id="turnterm_1" value="1"><label for="turnterm_1" class="btn btn-secondary">1</label>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label for="sync" class="col-sm-3 col-form-label">시간 동기화</label>
                                <div class="col-sm-9" style="display: inline-flex;">
                                    <div id="sync" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input class="btn-check" id="sync_1" type="radio" name="sync" value="1" checked><label class="btn btn-secondary" for="sync_1">Y</label>
                                        <input class="btn-check" id="sync_0" type="radio" name="sync" value="0"><label class="btn btn-secondary" for="sync_0">N</label>

                                    </div>
                                    <small class="text-muted">
                                        시간 단위에 맞게 년/월이 설정됩니다.<br>예: 120분(오전1시=1월), 60분(오전/오후 1시=1월)
                                    </small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="scenario_sel" class="col-sm-3 col-form-label">시나리오 선택</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="scenario" size="1" id="scenario_sel">
                                    </select>

                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="fiction" class="col-sm-3 col-form-label">NPC 상성</label>
                                <div class="col-sm-9">
                                    <div id="fiction" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input type="radio" id="fiction_0" class="btn-check" name="fiction" value="0"><label for="fiction_0" class="btn btn-secondary"> 연의 </label>
                                        <input type="radio" id="fiction_1" class="btn-check" name="fiction" value="1" checked><label for="fiction_1" class="btn btn-secondary"> 가상 </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="extend" class="col-sm-3 col-form-label">확장 NPC</label>
                                <div class="col-sm-9">
                                    <div id="extend" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input type="radio" id="extend_1" class="btn-check" name="extend" value="1" checked><label for="extend_1" class="btn btn-secondary">포함</label>
                                        <input type="radio" id="extend_0" class="btn-check" name="extend" value="0"><label for="extend_0" class="btn btn-secondary">미포함</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="block_general_create" class="col-sm-3 col-form-label">장수 임의 생성</label>
                                <div class="col-sm-9">
                                    <div id="block_general_create" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input type="radio" class="btn-check" id="block_general_create_0" name="block_general_create" value="0" checked><label for="block_general_create_0" class="btn btn-secondary">가능</label>
                                        <input type="radio" class="btn-check" id="block_general_create_2" name="block_general_create" value="2"><label for="block_general_create_2" class="btn btn-secondary">장수명무작위</label>
                                        <input type="radio" class="btn-check" id="block_general_create_1" name="block_general_create" value="1"><label for="block_general_create_1" class="btn btn-secondary">불가</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="npcmode" class="col-sm-3 col-form-label">NPC 빙의</label>
                                <div class="col-sm-9">
                                    <div id="npcmode" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input type="radio" id="npcmode_1" class="btn-check" name="npcmode" value="1"><label for="npcmode_1" class="btn btn-secondary"> 가능 </label>
                                        <input type="radio" id="npcmode_0" class="btn-check" name="npcmode" value="0" checked><label for="npcmode_0" class="btn btn-secondary"> 불가 </label>
                                        <input type="radio" id="npcmode_2" class="btn-check" name="npcmode" value="2"><label for="npcmode_2" class="btn btn-secondary"> 선택 생성 가능 </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label" id="autorun_set_label">휴식 턴 시 장수 턴</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                            <input type="checkbox" id="autorun_develop" class="autorun_user_chk btn-check" data-key="develop" name="autorun_user[]" value="develop" checked><label class="btn btn-secondary" for="autorun_develop">내정</label>
                                            <input type="checkbox" id="autorun_warp" class="autorun_user_chk btn-check" data-key="warp" name="autorun_user[]" value="warp" checked><label class="btn btn-secondary" for="autorun_warp">순간이동</label>
                                            <input type="checkbox" id="autorun_recruit" class="autorun_user_chk btn-check" data-key="recruit" name="autorun_user[]" value="recruit" checked><label class="btn btn-secondary" for="autorun_recruit">징병</label>
                                            <input type="checkbox" id="autorun_recruit_high" class="autorun_user_chk btn-check" data-key="recruit_high" name="autorun_user[]" value="recruit_high" checked><label class="btn btn-secondary" for="autorun_recruit_high">모병</label>
                                            <input type="checkbox" id="autorun_train" class="autorun_user_chk btn-check" data-key="train" name="autorun_user[]" value="train" checked><label class="btn btn-secondary" for="autorun_train">훈사</label>
                                            <input type="checkbox" id="autorun_battle" class="autorun_user_chk btn-check" data-key="battle" name="autorun_user[]" value="battle" checked><label class="btn btn-secondary" for="autorun_battle">출병</label>
                                            <input type="checkbox" id="autorun_chief" class="autorun_user_chk btn-check" data-key="chief" name="autorun_user[]" value="chief" checked><label class="btn btn-secondary" for="autorun_chief">기본 사령턴</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 offset-sm-3">
                                    <div class="input-group">
                                        <div class="input-group-text">유효 시간
                                        </div>
                                        <select class="custom-select" name="autorun_user_minutes" id="autorun_user_minutes">
                                            <option value="0" selected>꺼짐</option>
                                            <option value="43200">항상</option>
                                            <option value="10">10분</option>
                                            <option value="20">20분</option>
                                            <option value="30">30분</option>
                                            <option value="60">1시간</option>
                                            <option value="120">2시간</option>
                                            <option value="180">3시간</option>
                                            <option value="240">4시간</option>
                                            <option value="360">6시간</option>
                                            <option value="480">8시간</option>
                                            <option value="600">10시간</option>
                                            <option value="720">12시간</option>
                                            <option value="1440" selected>24시간</option>
                                            <option value="2160">36시간</option>
                                            <option value="2880">48시간</option>
                                            <option value="3600">60시간</option>
                                            <option value="4320">72시간</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="join_mode" class="col-sm-3 col-form-label">임관 모드</label>
                                <div class="col-sm-9">
                                    <div id="join_mode" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input class="btn-check" id="join_mode_full" type="radio" name="join_mode" value="full" checked><label class="btn btn-secondary" for="join_mode_full">일반</label>
                                        <input class="btn-check" id="join_mode_onlyRandom" type="radio" name="join_mode" value="onlyRandom"><label class="btn btn-secondary" for="join_mode_onlyRandom">랜덤 임관</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="show_img_level" class="col-sm-3 col-form-label">이미지 표기</label>
                                <div class="col-sm-9">
                                    <div id="show_img_level" class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                                        <input type="radio" id="show_img_level_0" class="btn-check" name="show_img_level" value="0"><label for="show_img_level_0" class="btn btn-secondary">안함</label>
                                        <input type="radio" id="show_img_level_1" class="btn-check" name="show_img_level" value="1"><label for="show_img_level_1" class="btn btn-secondary">전콘</label>
                                        <input type="radio" id="show_img_level_2" class="btn-check" name="show_img_level" value="2"><label for="show_img_level_2" class="btn btn-secondary">전콘, 병종</label>
                                        <input type="radio" id="show_img_level_3" class="btn-check" name="show_img_level" value="3" checked><label for="show_img_level_3" class="btn btn-secondary">전콘, 병종, NPC</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="tournament_trig" class="col-sm-3 col-form-label"><span class="text-nowrap">토너먼트 자동 시작</span></label>
                                <div class="col-sm-9" id="tournament_trig">
                                    <div id="tournament_trig" class="btn-group-toggle btn-group flex-wrap" data-bs-toggle="buttons">
                                        <input type="radio" class="btn-check" id="tournament_trig_0" name="tournament_trig" value="0"><label for="tournament_trig_0" class="btn btn-secondary w-25">수동</label>
                                        <input type="radio" class="btn-check" id="tournament_trig_1" name="tournament_trig" checked value="1"><label for="tournament_trig_1" class="btn btn-secondary w-25">자동</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="reserve_open" class="col-sm-3 col-form-label">오픈 예약</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="reserve_open" id="reserve_open" placeholder="YYYY-MM-DD hh:mm" value="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="pre_reserve_open" class="col-sm-3 col-form-label">가오픈 예약</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pre_reserve_open" id="pre_reserve_open" placeholder="YYYY-MM-DD hh:mm" value="" />
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 d-grid">
                                    <input type="submit" class="btn btn-primary btn-lg btn-block login-button" value="리셋 개시">
                                </div>
                            </div>
                        </form>
                    </div>
                    <h4 class="card-header">시나리오 정보</h4>
                    <table class="table table-striped">
                        <colgroup>
                            <col style="width:100px;">
                            <col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th>설정</th>
                                <th>값</th>
                        </thead>
                        <tbody>
                            <tr>
                                <th>시작 연도</th>
                                <td><span id="scenario_begin">180년</span><small id="scenario_begin_with_sync"></small>
                                </td>
                            </tr>
                            <tr>
                                <th>NPC 수</th>
                                <td><span id="scenario_npc"></span><span id="scenario_npc_extend"></span></td>
                            </tr>
                            <tr>
                                <th>국가</th>
                                <td><span id="scenario_nation"></span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">

                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!--GAME 폼 끝-->

            </div>
        </div>
    </div>
</body>

</html>