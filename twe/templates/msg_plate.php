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
    <?php elseif($msgType == 'public'): ?>
        bgcolor="#000055" 
    <?php else: /*$msgType == 'national'*/?>
        bgcolor="#336600"   
    <?php endif; ?>
    style="font-size:13;table-layout:fixed;word-break:break-all;"
    data-num="<?=$num?>"  <?php /*NOTE: 사용되지 않을 num인데 필요한가? */ ?>
>
    <tbody><tr>
        <td width="64px" height="64px">
            <?php if ($src['iconPath'] !== NULL): ?>
                <img src="<?=urlencode($src['iconPath'])?>">
            <?php else: ?>
                <img src="/image/default.jpg"> <?php /*NOTE: image 폴더는 어느 단에서 다뤄야하는가? */?>
            <?php endif; ?>
        </td>
        <td width="434px" valign="top">
            <?php if($msgType == 'private'): ?>
                <b>[
                    <font color="<?=$src['color']?>"><?=$src['name']?>:<?=$src['nation']?>
                ▶
                    </font><font color="<?=$dest['color']?>"><?=$dest['name']?>:<?=$dest['nation']?></font>
                ]</b>
            <?php else: ?>
                <b>[
                    <font color="<?=$src['color']?>"><?=$src['name']?>:<?=$src['nation']?>
                ]</b>
            <?php endif; ?>
            <font size="1">&lt;<?=$datetime?>&gt;</font>
            <br>
            <?=$msg?>
        </td>
    </tr></tbody>
</table>