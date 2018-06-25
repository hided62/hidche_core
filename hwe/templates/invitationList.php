<table class="tb_layout bg0" style="margin:auto;">
<thead>
    <tr><td colspan="2" style="text-align:center;" class="bg1">임관 권유 메시지</td></tr>
</thead>
<tbody>
<?php foreach($nationList as $nation): ?>
    <tr 
        data-nation-id="<?=$nation['nation']?>"
        style="color:<?=$nation['textColor']?>;background-color:<?=$nation['color']?>;"
    >
        <td style="width:130px;text-align:center;"
        ><?=$nation['name']?></td>
        <td><div style="width:870px;max-width:870px;max-height:200px;overflow:hidden;"
        ><?=$nation['scoutmsg']?:'-'?></div></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>