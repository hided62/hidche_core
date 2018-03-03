<?php
require_once('_common.php');
?>

<div id="EntranceManage_00" class="bg0">
    <div id="EntranceManage_0000" class="bg2 font4">계 정 관 리</div>
    <input id="EntranceManage_0001" type="button" value="돌아가기">

    <div id="EntranceManage_0002" class="bg1 font3">회 원 정 보</div>

    <div id="EntranceManage_0003" class="bg1 font2">ID</div>
    <div id="EntranceManage_0004"></div>
    <input id="EntranceManage_0019" type="button" value="탈퇴신청">
    <div id="EntranceManage_0005" class="bg1 font2">비밀번호</div>
    <div id="EntranceManage_0006">
        현재비번: <input id="EntranceManage_000600" type="password" maxlength="12">
        바꿀비번: <input id="EntranceManage_000601" type="password" maxlength="12">
        다시입력: <input id="EntranceManage_000602" type="password" maxlength="12">
        <input id="EntranceManage_000603" type="button" value="비밀번호 변경">
    </div>

    <div id="EntranceManage_0007" class="bg1 font2">닉네임</div>
    <div id="EntranceManage_0008"></div>
    <div id="EntranceManage_0009" class="bg1 font2">등급</div>
    <div id="EntranceManage_0010"></div>

    <div id="EntranceManage_0011" class="bg1 font2">-</div>
    <div id="EntranceManage_0012" class="bg1 font2">기존 / 신규</div>
    <div id="EntranceManage_0013" class="bg1 font2">전용아이콘 올리기</div>

    <div id="EntranceManage_0014" class="bg1 font2">전용사진</div>
    <div id="EntranceManage_0015">
        <img id="EntranceManage_001500">
        <img id="EntranceManage_001501">
    </div>
    <div id="EntranceManage_0016">
        <input id="EntranceManage_001600" type="text">
        <form id="formIcon" action="<?=ROOT.W.I.ENTRANCE.W.MANAGE.W;?>iconPost.php" method="POST" enctype="multipart/form-data">
        <input id="EntranceManage_001601" name="picture" type="file" size="15">
        </form>
        <input id="EntranceManage_001602" type="button" value="전콘변경">
        <input id="EntranceManage_001603" type="button" value="전콘제거">
    </div>

    <div id="EntranceManage_0017" class="bg1 font2">도움말</div>
    <div id="EntranceManage_0018">
<pre>jpg,png,gif 파일 64 x 64 크기만 가능합니다.
서버최적화를 위해 신규에서 기존으로 약 월1회 저장됩니다.
<font color=cyan>브라우저의 임시파일을 삭제하셔야 제대로 나옵니다.
새로 캐릭터를 생성할때부터 적용됩니다.</font>
<font color=magenta>탈퇴신청시 1개월간 정보가 보존되며,
1개월간 재가입이 불가능합니다.</font></pre>
    </div>
    <div id="EntranceManage_0020">
    </div>
</div>
