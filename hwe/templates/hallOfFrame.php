<div class="rankView bg0" style="outline-style:solid;outline-width:1px;outline-color: gray;">
<h3 class="rankType bg1"><?=$typeName?></h3>
<ul>
<?php foreach($generals as $rank=>$general): ?><li class='<?=(key_exists("rankName", $general))?'no_value':''?> <?=(key_exists("serverName", $general))?'has_server':''?>'>
    <div class="hall_rank bg2 with_border">
        <?=$general['rankName']??(($rank+1).'위')?>
    </div>
    <div class="hall_img"><img width="64" height="64" class='generalIcon' src="<?=$general['pictureFullPath']?>"></div>
    <?php if(key_exists("serverName",$general)): ?>
    <div class="hall_server obj_tooltip" data-bs-toggle="tooltip" data-placement="top"><?=$general['serverName']?><?=$general['serverIdx']?>기
        <span class="tooltiptext">
            <?=$general['scenarioName']?><br>
            <?=substr($general['startTime'], 0, 10)?> ~ <?=substr($general['unitedTime'], 0, 10)?>
        </span>
    </div>
    <?php endif;?>
    <div class="hall_nation" style="background-color:<?=$general['bgColor']?>;color:<?=$general['fgColor']?>;"><?=$general['nationName']??'-'?></div>
    <div class="hall_name" style="background-color:<?=$general['bgColor']?>;color:<?=$general['fgColor']?>;"><p><?=$general['name']??'-'?>
        <?php if($general["ownerName"]??null): ?>
            <div class="hall_owner">(<?=$general['ownerName']?>)</div>
        <?php endif;?>
    </p></div>
    <div class="hall_value"><?=$general['printValue']??$general['value']?></div>
</li><?php endforeach; ?>
</ul>
</div>