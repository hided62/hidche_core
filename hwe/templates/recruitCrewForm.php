<script>
var currentTech = <?=$tech?>;
var leader = <?=$leader?>;
var currentCrewType = <?=$crewType?>;
var currentCrew = <?=$crew?>
var currentGold = <?=$gold?>;
var is모병 = <?=($commandName=='모병')?'true':'false'?>;

window.calc = function(id) {
    var $obj = $('#crewType{0}'.format(id));
    var crew = $obj.find('.form_double').val();
    var baseCost = $obj.data('cost');
    var $cost = $obj.find('.form_cost');

    var cost = crew * baseCost.val();
    if(is모병){
        cost *= 2;
    }
    $cost.val(Math.round(cost));
}
</script>

<font size=2>병사를 모집합니다. 
<?php if($commandName=='징병'): ?>
훈련과 사기치는 낮지만 가격이 저렴합니다.<br>
<?php else: ?>
훈련과 사기치는 높지만 자금이 많이 듭니다.
<?php endif; ?>
가능한 수보다 많게 입력하면 가능한 최대 병사를 모집합니다.<br>
이미 병사가 있는 경우 추가<?=$commandName?>되며, 병종이 다를경우는 기존의 병사는 소집해제됩니다.<br>
현재 <?=$commandName?> 가능한 병종은 <font color=green>녹색</font>으로 표시되며,<br>
현재 <?=$commandName?> 가능한 특수병종은 <font color=limegreen>초록색</font>으로 표시됩니다.<br>

<table class='tb_layout' style='margin:auto;'>
<thead>
    <?php if($commandName=='모병'): ?>
    <tr><td align=center colspan=10>모병은 가격 2배의 자금이 소요됩니다.</td></tr>
    <?php endif; ?>
    <tr>
        <td colspan=10 align=center class='bg2'>
            현재 기술력 : <?=$techLevelText?>
            현재 통솔 : <?=$leader?>
            현재 병종 : <?=$crewTypeName?>
            현재 병사 : <?=$crew?>
            현재 자금 : <?=$gold?>
        </td>
    </tr>
    <tr>
        <td width=64 align=center class='bg1'>사진</td>
        <td width=64 align=center class='bg1'>병종</td>
        <td width=40 align=center class='bg1'>공격</td>
        <td width=40 align=center class='bg1'>방어</td>
        <td width=40 align=center class='bg1'>기동</td>
        <td width=40 align=center class='bg1'>회피</td>
        <td width=40 align=center class='bg1'>군량</td>
        <td width=40 align=center class='bg1'>가격</td>
        <td width=130 align=center class='bg1'>병사수</td>
        <td width=300 align=center class='bg1'>특징</td>
    </tr>
</thead>
<tbody>
<?php foreach($armTypes as [$armName,$armTypeCrews]): ?>
    <tr><td colspan=10><?=$armName?> 계열</td></tr>
    <?php foreach($armTypeCrews as $crewObj): ?>
        <tr 
            id="crewType<?=$crewObj->id?>"
            style='height:64px;background-color:<?=$crewObj->bgcolor?>' 
            data-rice="<?=$crewObj->baseRice?>"
            data-cost="<?=$crewObj->baseCost?>"
        >
            <td style='background-no-repeat center url("<?=$crewObj->img?>");backround-size:64px;background-color:#222222;'></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->name?></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->attack?></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->defence?></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->speed?></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->avoid?></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->baseRiceShort?></td>
            <td style='text-align:center;vertical-align:middle;'><?=$crewObj->baseCostShort?></td>
            <td style='text-align:center;vertical-align:middle;'><form action=c_double.php>
                    <input type=text class=form_double name=double maxlength=3 size=3
                        value=<?=$maxCrew?> style=text-align:right;color:white;background-color:black
                    >00명<input type=button value=계산 onclick='calc(<?=$crewObj->id?>)'><br>
                    <input type=text class=form_cost name=cost maxlength=5 size=5 readonly 
                        style=text-align:right;color:white;background-color:black>원 <input type=submit value='<?=$commandName?>'>
                    <input type=hidden name=third value='<?=$crewObj->id?>'>
                    <input type=hidden name=command value='<?=$command?>'>
    
                    <?php for($j=0; $j < count($turn); $j++) : ?>
                        <input type=hidden name='turn[]' value='<?=$turn[$j]?>'>
                    <?php endfor; ?>
            </form></td>
            <td><?=$crewObj->info?></td>
        </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
</tbody>
</table>