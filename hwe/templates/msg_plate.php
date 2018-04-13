<?php
//TODO: style을 css로 분리
//TODO: msg를 div로 변경
?>
<table 
    width="498px" 
    border="1" 
    bordercolordark="gray" 
    bordercolorlight="black" 
    cellpadding="0" 
    cellspacing="0"
    <?php if($msgType == 'private'): ?>
        bgcolor="#CC6600" 
    <?php elseif($msgType == 'national'): ?>
        bgcolor="#336600" 
    <?php else: /*$msgType == 'public'*/?>
        bgcolor="#000055"   
    <?php endif; ?>
    style="font-size:13px;table-layout:fixed;word-break:break-all;"
    data-id="<?=$id?>"
>
    <tbody><tr>
        <td width="64" height="64">
            <?php if ($src['iconPath'] !== null): ?>
                <img width='64' height='64' src="<?=$this->e(urlencode($src['iconPath']))?>">
            <?php else: ?>
                <img width='64' height='64' src="<?=$this->imagePath?>/default.jpg">
            <?php endif; ?>
        </td>
        <td width="434px" valign="top">
            <?php if($msgType == 'private'): ?>
                <b>[
                    <font color="<?=$src['color']?>"><?=$this->e($src['name'])?>:<?=$this->e($src['nation'])?></font>
                ▶
                    <font color="<?=$dest['color']?>"><?=$this->e($dest['name'])?>:<?=$this->e($dest['nation'])?></font>
                ]</b>
            <?php elseif($msgType == 'national'): ?>
                <b>[
                    <font color="<?=$src['color']?>"><?=$this->e($src['name'])?>:<?=$this->e($src['nation'])?></font>
                ▶
                    <font color="<?=$dest['color']?>"><?=$this->e($dest['nation'])?></font>
                ]</b>
            <?php else: ?>
                <b>[
                    <font color="<?=$src['color']?>"><?=$this->e($src['name'])?>:<?=$this->e($src['nation'])?>
                ]</b>
            <?php endif; ?>
            <font size="1">&lt;<?=$datetime?>&gt;</font>
            <br>
            <?=$this->e($message)?>
        </td>
    </tr></tbody>
</table>