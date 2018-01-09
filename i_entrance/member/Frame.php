<?
require_once('_common.php');
?>

<script type="text/javascript">
ImportStyle("<?=ROOT;?>"+W+I+ENTRANCE+W+MEMBER+W+STYLE);
ImportAction("<?=ROOT;?>"+W+I+ENTRANCE+W+MEMBER+W+ACTION);
EntranceMember_Import();
EntranceMember_Init();
</script>

<div id="EntranceMember_00">
    <div id="EntranceMember_0000" class="bg2 font4">
        회 원 정 보
        <font id="EntranceMember_000000">(0/0)</font>
    </div>
    <input id="EntranceMember_0001" type="button" value="돌아가기">
    <div id="EntranceMember_0006" class="bg2 font2">
        <div id="EntranceMember_000600"></div>
        <input id="EntranceMember_000601" type="button" value="가입허용">
        <input id="EntranceMember_000602" type="button" value="가입금지">
        <input id="EntranceMember_000603" type="button" value="로그인허용">
        <input id="EntranceMember_000604" type="button" value="로그인금지">
        <input id="EntranceMember_000605" type="button" value="탈퇴처리(1개월)">
        <input id="EntranceMember_000606" type="button" value="오래된계정(6개월)">
        <input id="EntranceMember_000607" type="button" value="이미지업로드">
    </div>
    <div id="EntranceMember_0002" class="bg2 font2">
        선택:
        <select id="EntranceMember_000200" size="1">
            <option value="0">순서</option>
            <option value="1">ID</option>
            <option value="2">장수</option>
            <option value="3">민번</option>
            <option value="4">IP</option>
            <option value="5">등급</option>
            <option value="6">등록</option>
            <option value="7">최근</option>
        </select>
        <input id="EntranceMember_000201" type="button" value="정렬">
    </div>
    <div id="EntranceMember_0003" class="bg2 font2">
        선택:
        <select id="EntranceMember_000300" size="1">
        </select>
        <input id="EntranceMember_000301" type="button" value="블럭회원">
        <input id="EntranceMember_000302" type="button" value="일반회원">
        <input id="EntranceMember_000303" type="button" value="참여회원">
        <input id="EntranceMember_000304" type="button" value="유효회원">
        <input id="EntranceMember_000305" type="button" value="특별회원">
        <input id="EntranceMember_000306" type="button" value="전콘제거">
        <input id="EntranceMember_000307" type="button" value="비번초기화">
        <input id="EntranceMember_000308" type="button" value="회원삭제">
    </div>
    <div id="EntranceMember_0004" class="bg1">
        <div id="EntranceMember_000400">순번</div>
        <div id="EntranceMember_000401">ID</div>
        <div id="EntranceMember_000402">민번</div>
        <div id="EntranceMember_000403">닉네임</div>
        <div id="EntranceMember_000404">IP</div>
        <div id="EntranceMember_000405">블럭</div>
        <div id="EntranceMember_000406">최근블럭일</div>
        <div id="EntranceMember_000407">등록</div>
        <div id="EntranceMember_000408">최근등록일</div>
        <div id="EntranceMember_000409">등급</div>
        <div id="EntranceMember_000410">총참여</div>
        <div id="EntranceMember_000411">최근참여일</div>
        <div id="EntranceMember_000412">사진</div>
        <div id="EntranceMember_000413">SVR</div>
        <div id="EntranceMember_000414">탈퇴</div>
    </div>
    <div id="EntranceMember_0005">
    </div>
</div>
