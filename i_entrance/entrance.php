<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

WebUtil::setHeaderNoCache();
$session = Session::requireLogin()->setReadOnly();

$templates = new \League\Plates\Engine('templates');

$db = RootDB::db();
$notice = $db->queryFirstField('SELECT `NOTICE` FROM `SYSTEM` WHERE `NO`=1');
$userGrade = $session->userGrade;
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>서버목록</title>

        <!-- 스타일 -->
        <link type="text/css" rel="stylesheet" href='../css/config.css'>

        <link type="text/css" rel="stylesheet" href='../css/entrance.css'>

        <!-- 액션 -->
        <script type="text/javascript" src='../js/common.js'></script>
        <script type="text/javascript" src='../e_lib/jquery-3.2.1.min.js'></script>
        <script type="text/javascript" src='../js/func.js'></script>
        <script type="text/javascript" src='../js/entrance.js'></script>

<?php if($userGrade >= 5): ?>
        <!-- 운영자 -->
        <link type="text/css" rel="stylesheet" href='../css/admin_server.css'>
        <script type="text/javascript" src='../js/admin_server.js'></script>
<?php endif;?>

    </head>
    <body>

<div id="server_list_container">

<?php
if($userGrade >= 5){
    echo $templates->render('global_panel',['notice'=>$notice]);
}
?>

<div id="server_notice"><span style="color:orange;font-size:2em;"><?=$notice?></span></div>

<table id="server_list_table">
    <caption class="bg2 section_title with_border">서 버 선 택</caption>
    <colgroup>
        <col style="width:100px;" />
        <col style="width:400px;" />
        <col style="width:66px;" />
        <col/>
        <col style="width:120px;" />
    </colgroup>
    <thead>
        <tr>
            <th class="bg1">서 버</th>
            <th class="bg1">정 보</th>
            <th class="bg1" colspan="2">캐 릭 터</th>
            <th class="bg1">선 택</th>
        </tr>
    </thead>
    <tbody id="server_list">

    </tbody>
    <tfoot>
        <tr>
<td colspan="100" class="bg0" style="text-align:left;">
<span class="Entrance_Alert">★ 1명이 2개 이상의 계정을 사용하거나 타 유저의 턴을 대신 입력하는 것이 적발될 경우 차단 될 수 있습니다.</span><br>
계정은 한번 등록으로 계속 사용합니다. 각 서버 리셋시 캐릭터만 새로 생성하면 됩니다.<br>
<br>
<span class="Entrance_Che">체섭</span> : 메인서버입니다. 천하통일에 도전하여 왕조일람과 명예의전당에 올라봅시다! (주로 1턴=60분)<br>
<span class="Entrance_Kwe">퀘섭</span> : 역사의 한 순간에 뛰어들어, 실제 장수가 되어 가상의 역사를 만들어 봅시다! (주로 1턴=30분)<br>
<span class="Entrance_Pwe">풰섭</span> : 역사의 한 순간에 뛰어들어, 실제 장수들과 어울려 사실적 역사를 체험해 봅시다! (주로 1턴=20분)<br>
<span class="Entrance_Twe">퉤섭</span> : 주로 패치사항 미리보기 테스트 서버입니다. 600여명의 NPC들과 경쟁해 보세요! (주로 1턴=10분)<br>
<span class="Entrance_Hwe">훼섭</span> : 1일천하 서버. 또는 운영자 테스트용 서버입니다. (주로 1턴=1분)<br>
</td>
        </tr>
    </tfoot>
</table>

<div id="user_info">
    <div class="bg2 section_title with_border">계 정 관 리</div>
    <div class="center_ordered_items with_border bg0">
        <a href="user_info.html"><button id="btn_user_manage" class="with_skin">비밀번호 &amp; 전콘 &amp; 탈퇴</button></a>
        <button id="btn_logout" class="with_skin">로 그 아 웃</button>
    </div>
</div>

<?php
if($userGrade >= 5){
    echo $templates->render('server_panel',[]);
}
?>

</div>



</div>

    </body>

</html>
