<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');

WebUtil::setHeaderNoCache();
$session = Session::requireLogin()->setReadOnly();

$templates = new \League\Plates\Engine(__dir__.'/templates');

$db = RootDB::db();
$notice = $db->queryFirstField('SELECT `NOTICE` FROM `system` WHERE `NO`=1');
$userGrade = $session->userGrade;
$acl = $session->acl;
?>
<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <title>서버목록</title>

        <!-- 스타일 -->
        <?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
        <?=WebUtil::printCSS('../d_shared/common.css')?>
        <?=WebUtil::printCSS('../css/config.css')?>
        <?=WebUtil::printCSS('../css/entrance.css')?>

        <!-- 액션 -->
        <?=WebUtil::printJS('../js/common.js')?>
        <?=WebUtil::printJS('../e_lib/jquery-3.2.1.min.js')?>
        <?=WebUtil::printJS('../e_lib/bootstrap.bundle.min.js')?>
        <?=WebUtil::printJS('../js/func.js')?>
        <?=WebUtil::printJS('../js/entrance.js')?>
        <?=WebUtil::printJS('../d_shared/menu.js')?>
        <?=WebUtil::printJS('../js/title.js')?>

<?php if ($userGrade >= 5 || $acl): ?>
        <!-- 운영자 -->
        <?=WebUtil::printCSS('../css/admin_server.css')?>
        <?=WebUtil::printJS('../js/admin_server.js')?>
<?php endif; ?>

    </head>
    <body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="../">삼국지 모의전투 HiDCHe</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
    </ul>
  </div>
</nav>

<div id="server_list_container">

<?php
if ($userGrade >= 5) {
    echo $templates->render('global_panel', ['notice'=>$notice]);
}
?>

<div id="server_notice"><span style="color:orange;font-size:2em;"><?=$notice?></span></div>

<table id="server_list_table">
    <caption class="bg2 section_title with_border">서 버 선 택</caption>
    <colgroup>
        <col style="width:100px;" />
        <col style="width:375px;" />
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
<span class="Entrance_Kwe">퀘섭</span> : 마이너 서버 그룹1. 비교적 느린 시간으로 운영됩니다.<br>
<span class="Entrance_Pwe">풰섭</span> : 마이너 서버 그룹1. 비교적 느린 시간으로 운영됩니다.<br>
<span class="Entrance_Twe">퉤섭</span> : 마이너 서버 그룹2. 비교적 빠른 시간으로 운영됩니다.<br>
<span class="Entrance_Nya">냐섭</span> : 마이너 서버 그룹3. 독특한 컨셉 위주로 운영됩니다.<br>
<span class="Entrance_Pya">퍄섭</span> : 마이너 서버 그룹3. 독특한 컨셉 위주로 운영됩니다.<br>
<span class="Entrance_Hwe">훼섭</span> : 운영자 테스트 서버입니다. 기습적으로 열리고, 닫힐 수 있습니다.<br>
</td>
        </tr>
    </tfoot>
</table>

<div id="user_info">
    <div class="bg2 section_title with_border">계 정 관 리</div>
    <div class="center_ordered_items with_border bg0">
        <a href="user_info.php"><button id="btn_user_manage" class="with_skin">비밀번호 &amp; 전콘 &amp; 탈퇴</button></a>
        <button id="btn_logout" class="with_skin">로 그 아 웃</button>
    </div>
</div>


<?php
if ($userGrade >= 5 || $acl) {
    echo $templates->render('server_panel', []);
}
?>

</div>



</div>

    </body>

</html>
