<?
require_once('_common.php');
require_once(ROOT.W.F_CONFIG.W.DB.PHP);
require_once(ROOT.W.F_CONFIG.W.SESSION.PHP);

$rs = $DB->Select('NOTICE', 'SYSTEM', "NO='1'");
$system = $DB->Get($rs);

$rs = $DB->Select('ID, GRADE', 'MEMBER', "NO='{$SESSION->NoMember()}'");
$member = $DB->Get($rs);

?>

<? include(MANAGE.W.FRAME); ?>
<?
if($member['GRADE'] >= 6) {
    include(DONATION.W.FRAME);
    include(MEMBER.W.FRAME);
}
?>

<div id="Entrance_00">

    <div id="Entrance_0007"><font color=orange size=6><?=$system['NOTICE'];?></font></div>
    <div id="Entrance_0004">심의</div>
<? $banner_id = $member['ID']; ?>
    <div id="Entrance_0005">
<? include(ROOT.W.'i_banner/banner.php'); ?>
    </div>
    <div id="Entrance_0006">
<? include(ROOT.W.'i_banner/banner.php'); ?>
    </div>

    <div id="Entrance_0000" class="bg0">
        <div id="Entrance_000000" class="bg2">서 버 선 택</div>
        <div id="Entrance_000001">
            <div id="Entrance_00000000" class="bg1">서 버</div>
            <div id="Entrance_00000001" class="bg1">정 보</div>
            <div id="Entrance_00000002" class="bg1">캐 릭 터</div>
            <div id="Entrance_00000003" class="bg1">선 택</div>
        </div>
        <div id="Entrance_000002">
        </div>
        <div id="Entrance_000003">
<pre><font class="Entrance_Alert">★ 1명이 1개 넘는 계정을 접속하거나 대턴(대신 턴 입력)행위는 당연히 불법입니다.</font>
<font class="Entrance_Alert">★ 접속장소를 못적고 로그인 한 경우, 바로 다시 잘 적고 로그인 하시면 문제없습니다.</font>
계정은 한번 등록으로 계속 사용합니다. 각 서버 리셋시 캐릭터만 새로 생성하면 됩니다.

<font class="Entrance_Che">체섭</font> : 메인서버입니다. 천하통일에 도전하여 왕조일람과 명예의전당에 올라봅시다! (주로 1턴=60분)
<font class="Entrance_Kwe">퀘섭</font> : 역사의 한 순간에 뛰어들어, 실제 장수가 되어 가상의 역사를 만들어 봅시다! (주로 1턴=30분)
<font class="Entrance_Pwe">풰섭</font> : 역사의 한 순간에 뛰어들어, 실제 장수들과 어울려 사실적 역사를 체험해 봅시다! (주로 1턴=20분)
<font class="Entrance_Twe">퉤섭</font> : 주로 패치사항 미리보기 테스트 서버입니다. 600여명의 NPC들과 경쟁해 보세요! (주로 1턴=10분)
<font class="Entrance_Hwe">훼섭</font> : 1일천하 서버. 또는 운영자 테스트용 서버입니다. (주로 1턴=1분)</pre>
        </div>
    </div>
    <div id="Entrance_0001" class="bg0">
        <div id="Entrance_000100" class="bg2">계 정 관 리</div>
        <input id="Entrance_000101" type="button" value="비밀번호 & 전콘 & 탈퇴">
        <input id="Entrance_000102" type="button" value="로 그 아 웃">
    </div>

<?
if($member['GRADE'] >= 6) {
    include(ADMIN.INC);
}
?>

</div>

