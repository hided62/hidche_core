<tr
    data-is-our-general="<?=$ourGeneral?'true':'false'?>"
    data-is-npc="<?=$isNPC?'true':'false'?>"
    data-general-wounded="<?=$wounded?>"
    data-general-name="<?=$this->e($name)?>"
    data-general-leadership="<?=$leadership?>"
    data-general-power="<?=$power?>"
    data-general-intel="<?=$intel?>"
    data-general-level="<?=$level?>"
    data-general-leadership-bonus="<?=$leadershipBonus?>"
<?php if($ourGeneral): ?>
    data-general-defence-mode="<?=$defenceMode?>"
    data-general-crew-type="<?=$crewType?>"
    data-general-crew="<?=$crew?>"
    data-general-train="<?=$train?>"
    data-general-atmos="<?=$atmos?>"
<?php else: ?>
    data-general-crew="<?=$crew?>"
<?php endif; ?>
    data-general-nation="<?=$nation?>"
    data-general-nation-name="<?=$nationName?>"
>
    <td height="64"></td>
    <td><?=$nameText?></td>
    <td><?=$leadershipText?><?=$leadershipBonusText?></td>
    <td><?=$powerText?></td>
    <td><?=$intelText?></td>
    <td class="general_level"><?=$levelText?></td>
<?php if($ourGeneral): ?>
    <td><?=$defenceModeText?></td>
    <td class="general_crew_type"><?=$crewTypeText?></td>
    <td><?=$crew?></td>
    <td><?=$train?></td>
    <td><?=$atmos?></td>
    <?php if($isNPC): ?>
    <td>NPC 장수</td>    
    <?php else: ?>
    <td class="general_turn_text"><?=$turnText?></td>
    <?php endif; ?>
<?php else: ?>
    <td>?</td>
    <td class="general_crew_type">?</td>
    <td><?=$crew?></td>
    <td>?</td>
    <td>?</td>
    <?php if($nation!==0): ?>
    <td>【<?=$nationName?>】 장수</td>
    <?php else: ?>
    <td>재 야</td>
    <?php endif; ?>
<?php endif; ?>
    
</tr>