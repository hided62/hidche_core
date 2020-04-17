<table align='center' id='general_list' class='tb_layout bg0'>
<thead>
    <tr>
        <td width=98 class='bg1 center'>이 름</td>
        <td width=98 class='bg1 center'>통무지</td>
        <td width=98 class='bg1 center'>부 대</td>
        <td width=53 class='bg1 center'>자 금</td>
        <td width=53 class='bg1 center'>군 량</td>
        <td width=48 class='bg1 center'>도시</td>
        <td width=28 class='bg1 center'>守</td>
        <td width=58 class='bg1 center'>병 종</td>
        <td width=63 class='bg1 center'>병 사</td>
        <td width=38 class='bg1 center'>훈련</td>
        <td width=38 class='bg1 center'>사기</td>
        <td width=213 class='bg1 center'>명 령</td>
        <td width=38 class='bg1 center'>삭턴</td>
        <td width=48 class='bg1 center'>턴</td>
    </tr>
</thead>
<tbody>
<?php foreach($generals as $general): ?>
<tr class='general_id_<?=$general['no']?>'
    data-general-id="<?=$general['no']?>"
    data-is-npc="<?=$general['npc']>=2?'true':'false'?>"
    data-general-wounded="<?=$general['injury']?>"
    data-general-name="<?=$general['name']?>"
    data-general-leadership="<?=$general['leadership']?>"
    data-general-strength="<?=$general['strength']?>"
    data-general-intel="<?=$general['intel']?>"
    data-general-exp-level="<?=$general['expLevelText']?>"
    data-general-leadership-bonus="<?=$general['lbonus']?>"
    data-general-defence-train="<?=$general['defence_train']?>"
    data-general-crew-type="<?=$general['crewtype']?>"
    data-general-crew="<?=$general['crew']?>"
    data-general-train="<?=$general['train']?>"
    data-general-atmos="<?=$general['atmos']?>"
>
    <td class='i_name center'><span class='t_name'><?=$general['nameText']?></span><br
    >Lv <span class='t_explevel'><?=$general['expLevelText']?></span></td>
    <td class='i_stat center'><?=$general['leadershipText']?>∥<?=$general['strengthText']?>∥<?=$general['intelText']?></td>
    <td class='i_troop center'><?=$general['troopText']?></td>
    <td class='i_gold center'><?=$general['gold']?></td>
    <td class='i_rice center'><?=$general['rice']?></td>
    <td class='i_city center'><?=$general['cityText']?></td>
    <td class='center'><?=$general['modeText']?></td>
    <td class='i_crewtype center'><?=$general['crewtypeText']?></td>
    <td class='i_crew center'><?=$general['crew']?></td>
    <td class='i_train center'><?=$general['train']?></td>
    <td class='i_atmos center'><?=$general['atmos']?></td>
    <td class='i_action'>
        <?php if($general['npc'] >= 2): ?>
            <font size=3>NPC 장수</font>
        <?php else: ?>
            <font size=1>
            <?=$general['turntext']?>
            </font>
        <?php endif; ?>
    </td>
    <td class='center'><?=$general['killturn']?></td>
    <td class='i_turntime center'><?=substr($general['turntime'], 14, 5)?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>