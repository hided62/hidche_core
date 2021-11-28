<div style='width:100%;' class='tb_layout bg2 mainCityInfo'>
    <div class="cityNamePanel" style="color:<?= $nationTextColor ?>;background-color:<?= $nationColor ?>;"><div>【 <?= $region ?> | <?= $levelText ?> 】 <?= $name ?></div></div>
    <div class="nationNamePanel" style="color:<?= $nationTextColor ?>;background-color:<?= $nationColor ?>;"><?= $nationName ? "지배 국가 【 {$nationName} 】" : '공 백 지' ?></div>
    <div class="gPanel popPanel">
        <div class="gHead bg1">주민</div>
        <div class="gBody"><?= $this->bar($pop / $pop_max * 100) ?><?= $pop ?>/<?= $pop_max ?></div>
    </div>
    <div class="gPanel trustPanel">
        <div class="gHead bg1">민심</div>
        <div class="gBody"><?= $this->bar($trust) ?><?= round($trust, 1) ?></div>
    </div>
    <div class="gPanel agriPanel">
        <div class="gHead bg1">농업</div>
        <div class="gBody"><?= $this->bar($agri / $agri_max * 100) ?><?= $agri ?>/<?= $agri_max ?></div>
    </div>
    <div class="gPanel commPanel">
        <div class="gHead bg1">상업</div>
        <div class="gBody"><?= $this->bar($comm / $comm_max * 100) ?><?= $comm ?>/<?= $comm_max ?></div>
    </div>
    <div class="gPanel secuPanel">
        <div class="gHead bg1">치안</div>
        <div class="gBody"><?= $this->bar($secu / $secu_max * 100) ?><?= $secu ?>/<?= $secu_max ?></div>
    </div>
    <div class="gPanel defPanel">
        <div class="gHead bg1">수비</div>
        <div class="gBody"><?= $this->bar($def / $def_max * 100) ?><?= $def ?>/<?= $def_max ?></div>
    </div>
    <div class="gPanel wallPanel">
        <div class="gHead bg1">성벽</div>
        <div class="gBody"><?= $this->bar($wall / $wall_max * 100) ?><?= $wall ?>/<?= $wall_max ?></div>
    </div>
    <div class="gPanel tradePanel">
        <div class="gHead bg1">시세</div>
        <div class="gBody"><?= $this->bar(($trade - 95) * 10) ?><?= $trade ? "{$trade}%" : '상인없음' ?></div>
    </div>
    <div class="gPanel officer4Panel">
        <div class="gHead bg1">태수</div>
        <div class="gBody"><?= $officerName[4] ?></div>
    </div>
    <div class="gPanel officer3Panel">
        <div class="gHead bg1">군사</div>
        <div class="gBody"><?= $officerName[3] ?></div>
    </div>
    <div class="gPanel officer2Panel">
        <div class="gHead bg1">종사</div>
        <div class="gBody"><?= $officerName[2] ?></div>
    </div>
</div>