<?php
require_once('_common.php');
?>

<script type="text/javascript">
ImportStyle("<?=ROOT;?>"+W+I+ENTRANCE+W+DONATION+W+STYLE);
ImportAction("<?=ROOT;?>"+W+I+ENTRANCE+W+DONATION+W+ACTION);
EntranceDonation_Import();
EntranceDonation_Init();
</script>

<div id="EntranceDonation_00">
    <div id="EntranceDonation_0000" class="bg2 font4">
        참 여 기 록
    </div>
    <input id="EntranceDonation_0001" type="button" value="돌아가기">
    <div id="EntranceDonation_0002" class="bg0">
        <input id="EntranceDonation_000200" type="button" value="통계계산">
    </div>
    <div id="EntranceDonation_0003" class="bg0">
        <div id="EntranceDonation_000300">-</div>
        <input id="EntranceDonation_000301" type="text">
        <input id="EntranceDonation_000302" type="text">
        <input id="EntranceDonation_000303" type="text">
        <input id="EntranceDonation_000304" type="text">
        <input id="EntranceDonation_000305" type="text">
        <input id="EntranceDonation_000306" type="text">
        <div id="EntranceDonation_000307">-</div>
        <input id="EntranceDonation_000308" type="button" value="기록">
    </div>
    <div id="EntranceDonation_0004" class="bg1">
        <div id="EntranceDonation_000400">순번</div>
        <div id="EntranceDonation_000401">일자</div>
        <div id="EntranceDonation_000402">ID</div>
        <div id="EntranceDonation_000403">이름</div>
        <div id="EntranceDonation_000404">입금자</div>
        <div id="EntranceDonation_000405">닉네임</div>
        <div id="EntranceDonation_000406">금액</div>
        <div id="EntranceDonation_000407">개인누적</div>
        <div id="EntranceDonation_000408">총누적</div>
    </div>
    <div id="EntranceDonation_0005">
    </div>
</div>
