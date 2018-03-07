<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');

$system = getRootDB()->queryFirstRow('SELECT `REG`, `LOGIN` FROM `SYSTEM` WHERE `NO`=1');
?>

<?php include(FINDPW.W.FRAME); ?>
<?php include(JOIN.W.FRAME); ?>

<div id="Login_00">

    <div id="Login_0000">심의</div>
<?php $banner_id = 'none'; ?>
    <div id="Login_0002">
<?php include(ROOT.'/i_banner/banner.php'); ?>
    </div>
    <div id="Login_0003">
<?php include(ROOT.'/i_banner/banner.php'); ?>
    </div>

    <div class="Login_TopBottomButtons">
        <input type="button" value="게시판메인" class="Login_TopBottomButton Login_Bbs">
        <input type="button" value="삼모게시판" class="Login_TopBottomButton Login_Free">
        <input type="button" value="삼국일보"   class="Login_TopBottomButton Login_News">
        <input type="button" value="레퍼런스"   class="Login_TopBottomButton Login_Reference">
        <input type="button" value="패치게시판" class="Login_TopBottomButton Login_Patch">
        <input type="button" value="참여게시판" class="Login_TopBottomButton Login_Donation">
        <input type="button" value="튜토리얼"   class="Login_TopBottomButton Login_Tutorial">
        <input type="button" value="왕조일람"   class="Login_TopBottomButton Login_Emperior")>
        <input type="button" value="접속량정보" class="Login_TopBottomButton Login_Traffic">
    </div>
    <div id="Login_0001" style="background-image: url('<?=IMAGES.W.'back7.jpg';?>');">
        <div id="Login_000100" class="bg0">
            <div id="Login_00010000" class="bg1">삼모전 유기체서버</div>
            <div id="Login_00010001" class="bg1">통합관리</div>
            <div id="Login_00010002">ID&nbsp;</div>
            <input id="Login_00010003" type="text" maxlength="12">
            <div id="Login_00010004">PW&nbsp;</div>
            <input id="Login_00010005" type="password" maxlength="12">
            <input id="token_login" type="hidden" value="<?=md5(rand()%100000000);?>">
            <div id="Login_00010006">접속장소&nbsp;</div>
            <input id="Login_00010007" type="text" maxlength="35">
            <input id="Login_00010008" type="button" value="비번&#13;찾기">
<?php
if($system['REG'] == 'Y') {
    echo '
            <input id="Login_00010009" type="button" value="회원&#13;가입">
    ';
} else {
    echo '
            <input id="Login_00010009" type="button" value="가입&#13;금지중" disabled>
    ';
}
?>
<?php
if($system['LOGIN'] == 'Y') {
    echo '
            <input id="Login_00010010" type="button" value="로그인">
    ';
} else {
    echo '
            <input id="Login_00010010" type="button" value="로그인&#13;금지중">
    ';
}
?>

            <div id="Login_00010011" class="Login_Alert">
<b><pre>접속장소 예문 : 형/동생, 삼모대학 컴퓨터실
원칙적으로 1IP/1인이며 예외가 필요한 경우
접속장소를 상세히 적어주셔야 합니다.
(제대로 입력 안할시 블럭될 수 있습니다)</pre></b>
            </div>
            <div id="Login_00010012" class="bg0"><b>※체섭은 맑은고딕체를 권장합니다.</b></div>
            <input id="Login_00010013" type="button" value="튜토리얼">
            <input id="Login_00010014" type="button" value="스크린샷">
        </div>
        <div id="Login_000101" class="bg0">
<b><pre>삼국지 모의전투 유기체서버
제작자 : 유기체(jwh1807@gmail.com)</pre></b>
        </div>
    </div>
    <div class="Login_TopBottomButtons">
        <input type="button" value="게시판메인" class="Login_TopBottomButton Login_Bbs">
        <input type="button" value="삼모게시판" class="Login_TopBottomButton Login_Free">
        <input type="button" value="삼국일보"   class="Login_TopBottomButton Login_News">
        <input type="button" value="레퍼런스"   class="Login_TopBottomButton Login_Reference">
        <input type="button" value="패치게시판" class="Login_TopBottomButton Login_Patch">
        <input type="button" value="참여게시판" class="Login_TopBottomButton Login_Donation">
        <input type="button" value="튜토리얼"   class="Login_TopBottomButton Login_Tutorial">
        <input type="button" value="왕조일람"   class="Login_TopBottomButton Login_Emperior")>
        <input type="button" value="접속량정보" class="Login_TopBottomButton Login_Traffic">
    </div>
</div>
