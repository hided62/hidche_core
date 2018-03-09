<?php
require_once('_common.php');
require_once(ROOT.'/f_config/DB.php');

$system = getRootDB()->queryFirstRow('SELECT `REG`, `LOGIN` FROM `SYSTEM` WHERE `NO`=1');
?>

<?php include('findpw/Frame.php'); ?>
<?php include('join/Frame.php'); ?>

<div class="Login_TopButtons">
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

    
        <div id="Login_000100" class="bg0">
            <div id="Login_00010000" class="bg1">삼모전 HiD서버</div>
            <div id="Login_00010001" class="bg1">통합관리</div>
            <div id="Login_00010002">ID&nbsp;</div>
            <input id="Login_00010003" type="text" maxlength="12">
            <div id="Login_00010004">PW&nbsp;</div>
            <input id="Login_00010005" type="password" maxlength="12">
            <input id="global_salt" type="hidden" value="<?=getGlobalSalt()?>">
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

            
            <div id="Login_00010012" class="bg0"><b>※체섭은 맑은고딕체를 권장합니다.</b></div>
            <input id="Login_00010013" type="button" value="튜토리얼">
            <input id="Login_00010014" type="button" value="스크린샷">
        </div>
        <div id="Login_000101" class="bg0">
<b><pre>삼국지 모의전투 유기체서버
제작자 : 유기체(jwh1807@gmail.com)</pre></b>
        </div>
    
<div class="Login_BottomButtons">
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