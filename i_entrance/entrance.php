<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');
require_once(ROOT.'/f_config/SESSION.php');

$db = getRootDB();
$system = $db->queryFirstRow('SELECT `NOTICE` FROM `SYSTEM` WHERE `NO`=1');
$member = $db->queryFirstRow('SELECT ID, GRADE FROM `MEMBER` WHERE `NO` = %i', $SESSION->NoMember());

?>
<!DOCTYPE html>
<html>

    <head>
        <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
        <title>서버목록</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href='../f_config/config.css'>
        <link type="text/css" rel="stylesheet" href='../f_config/app.css'>

        <link type="text/css" rel="stylesheet" href='../i_popup/Style.css'>
        <link type="text/css" rel="stylesheet" href='../i_entrance/Style.css'>

        <!-- 액션 -->
        <script type="text/javascript" src='../js/common.js'></script>
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src='../f_config/config.js'></script>
        <script type="text/javascript" src='../f_config/app.js'></script>
        <script type="text/javascript" src='../f_func/func.js'></script>

        <script type="text/javascript" src='../i_popup/Action.js'></script>
        <script type="text/javascript" src='../i_entrance/Action.js'></script>
        <script type="text/javascript">
$(document).ready(Entrance);

function Entrance() {
    ImportView("body", "../i_popup/Frame.php");
    //ImportView("body", FRAME);

    Popup_Import();
    Popup_Init();
    Popup_Update();

    Entrance_Import();
    Entrance_Init();
    Entrance_Update();
}
        </script>
    </head>
    <body>
<?php include(MANAGE.W.FRAME); ?>
<?php
if($member['GRADE'] >= 6) {
    include(MEMBER.W.FRAME);
}
?>

<div id="server_list_container">

<div id="Entrance_0007"><span style="color:orange;font-size:2em;"><?=$system['NOTICE'];?></span></div>

<?php /*XXX:div라지만 누가봐도 표면 그냥 table인게 낫지 않겠음?*/ ?>
<table id="server_list_table">
    <caption class="bg2 section_title with_border">서 버 선 택</caption>
    <thead>
        <tr>
            <th class="bg1" style="width:100px;">서 버</th>
            <th class="bg1">정 보</th>
            <th class="bg1" style="width:180px;">캐 릭 터</th>
            <th class="bg1" style="width:120px;">선 택</th>
        </tr>
    </thead>
    <tbody id="server_list">

    </tbody>
</table>

<div style="text-align:left;" class="bg0 with_border">
<pre><span class="Entrance_Alert">★ 1명이 2개 이상의 계정을 사용하거나 타 유저의 턴을 대신 입력하는 것이 적발될 경우 차단 될 수 있습니다.</span>
계정은 한번 등록으로 계속 사용합니다. 각 서버 리셋시 캐릭터만 새로 생성하면 됩니다.

<span class="Entrance_Che">체섭</span> : 메인서버입니다. 천하통일에 도전하여 왕조일람과 명예의전당에 올라봅시다! (주로 1턴=60분)
<span class="Entrance_Kwe">퀘섭</span> : 역사의 한 순간에 뛰어들어, 실제 장수가 되어 가상의 역사를 만들어 봅시다! (주로 1턴=30분)
<span class="Entrance_Pwe">풰섭</span> : 역사의 한 순간에 뛰어들어, 실제 장수들과 어울려 사실적 역사를 체험해 봅시다! (주로 1턴=20분)
<span class="Entrance_Twe">퉤섭</span> : 주로 패치사항 미리보기 테스트 서버입니다. 600여명의 NPC들과 경쟁해 보세요! (주로 1턴=10분)
<span class="Entrance_Hwe">훼섭</span> : 1일천하 서버. 또는 운영자 테스트용 서버입니다. (주로 1턴=1분)</pre>
    </div>
    
    <div id="user_info" class="bg0 with_border">
    <div class="bg2 section_title with_border">계 정 관 리</div>
    <div class="center_ordered_items">
        <input id="btn_user_manage" class="with_skin" type="button" value="비밀번호 & 전콘 & 탈퇴">
        <input id="btn_logout" class="with_skin" type="button" value="로 그 아 웃">
    </div>
    
</div>

</div>
<div id="Entrance_00" class="legacy_layout">



<div id="Entrance_0000" class="bg0" style="display:none;">
    <div id="Entrance_000000" class="bg2">서 버 선 택</div>
    <div id="Entrance_000001">
        <div id="Entrance_00000000" class="bg1">서 버</div>
        <div id="Entrance_00000001" class="bg1">정 보</div>
        <div id="Entrance_00000002" class="bg1">캐 릭 터</div>
        <div id="Entrance_00000003" class="bg1">선 택</div>
    </div>
    <div id="Entrance_000002">
    </div>
    
</div>


<?php
if($member['GRADE'] >= 6) {
include(ADMIN.INC);
}
?>

</div>

    </body>

</html>
